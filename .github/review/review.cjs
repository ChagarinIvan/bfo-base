const fs = require('node:fs')
const path = require('node:path')
const { execFileSync } = require('node:child_process')
const ROLES = ['architecture', 'ddd', 'security', 'tests', 'frontend', 'uiux']
const schema = require('./report.schema.json')

// Validate again at the publication boundary; structured model output is untrusted.
function check(value, rule) {
    if (rule.type === 'object') {
        if (!value || typeof value !== 'object' || Array.isArray(value))
            throw Error('Expected object')
        for (const key of rule.required)
            if (!(key in value)) throw Error('Missing field')
        for (const key of Object.keys(value)) {
            if (!rule.properties[key]) throw Error('Unexpected field')
            check(value[key], rule.properties[key])
        }
    } else if (rule.type === 'array') {
        if (!Array.isArray(value) || value.length > rule.maxItems)
            throw Error('Invalid array')
        value.forEach((item) => check(item, rule.items))
    } else if (rule.type === 'string') {
        if (
            typeof value !== 'string' ||
            value.length < (rule.minLength || 0) ||
            value.length > rule.maxLength
        )
            throw Error('Invalid string')
        if (rule.pattern && !new RegExp(rule.pattern).test(value))
            throw Error('Invalid format')
    } else if (rule.type === 'integer') {
        if (
            !Number.isSafeInteger(value) ||
            value < rule.minimum ||
            value > rule.maximum
        )
            throw Error('Invalid line')
    } else throw Error('Unsupported schema')
    if (rule.enum && !rule.enum.includes(value)) throw Error('Invalid enum')
}
function validate(value, role, context) {
    check(value, schema)
    if (
        value.role !== role ||
        value.baseSha !== context.baseSha ||
        value.headSha !== context.headSha
    )
        throw Error('Report identity mismatch')
    if (value.status === 'not_applicable' && value.findings.length)
        throw Error('Contradictory status')
    if (value.status === 'partial' && !value.limitations.length)
        throw Error('Missing partial coverage explanation')
    for (const item of value.findings) {
        if (
            !context.changedPaths.includes(item.path) ||
            item.path.split('/').includes('..') ||
            item.path.startsWith('/') ||
            item.endLine < item.startLine
        )
            throw Error('Invalid location')
    }
    return value
}
function aggregate(reports) {
    const findings = new Map()
    for (const report of reports)
        for (const item of report.findings) {
            const key = JSON.stringify([
                item.path,
                item.startLine,
                item.endLine,
                item.title.trim().toLowerCase(),
            ])
            const existing = findings.get(key)
            if (existing) {
                if (!existing.roles.includes(report.role))
                    existing.roles.push(report.role)
                if (item.priority < existing.priority) {
                    existing.priority = item.priority
                    existing.body = item.body
                }
            } else findings.set(key, { ...item, roles: [report.role] })
        }
    return [...findings.values()].sort(
        (a, b) =>
            a.priority.localeCompare(b.priority) ||
            a.path.localeCompare(b.path) ||
            a.startLine - b.startLine,
    )
}
function escape(value) {
    return String(value)
        .replace(/[\x00-\x1f\x7f]/g, ' ')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/@/g, '&#64;')
        .replace(/[\\`*_{}\[\]()#+!|~]/g, '\\$&')
}
function render(reports, context, runUrl, run) {
    let incomplete = false
    let body = `<!-- bfo-agent-review run=${run} -->\n## Agent review\n\nHead: \`${context.headSha}\`\nBase: \`${context.baseSha}\`\n[Workflow run](${runUrl})\n\nРекомендательное ревью. UI/UX проверяется по исходникам, без браузерной визуальной проверки.\n\n| Роль | Статус | Результат |\n| --- | --- | --- |\n`
    const valid = []
    for (const role of ROLES) {
        let report
        try {
            report = validate(reports[role], role, context)
        } catch {
            /* Missing/invalid output must never look clean. */
        }
        if (!report) {
            incomplete = true
            body += `| ${role} | error | Нет валидного результата: ключ, доступ, таймаут или ошибка задания. |\n`
            continue
        }
        if (context.coverageLimited) {
            report = {
                ...report,
                status: 'partial',
                limitations: [
                    ...report.limitations,
                    'Diff превышает лимит 150 файлов или 1 MiB; полное покрытие не подтверждено.',
                ],
            }
        }
        valid.push(report)
        if (report.status === 'partial') incomplete = true
        body += `| ${role} | ${report.status} | ${escape(report.summary)} |\n`
        for (const limitation of report.limitations)
            body += `| ${role} | limitation | ${escape(limitation)} |\n`
    }
    body += incomplete
        ? '\n**Проверка неполная.** Смотрите статусы ролей и workflow log.\n'
        : '\nВсе шесть ролей завершены.\n'
    const findings = aggregate(valid)
    if (!findings.length)
        body +=
            '\nВ полученных результатах замечаний нет. Это не подтверждение отсутствия дефектов.\n'
    for (const item of findings) {
        const block = `\n### ${item.priority}: ${escape(item.title)}\n\n${escape(item.path)}:${item.startLine}–${item.endLine} · ${item.roles.join(', ')}\n\n${escape(item.body)}\n`
        if (body.length + block.length > 59000) {
            incomplete = true
            body +=
                '\n**Отчёт сокращён по лимиту комментария.** Полные результаты в artifacts этого запуска.\n'
            break
        }
        body += block
    }
    return { body, incomplete }
}
async function publish(github, target, context, body, run) {
    const fresh = async () => {
        const { data: pr } = await github.rest.pulls.get({
            owner: target.owner,
            repo: target.repo,
            pull_number: target.issue_number,
        })
        return (
            pr.state === 'open' &&
            !pr.draft &&
            pr.base.sha === context.baseSha &&
            pr.head.sha === context.headSha
        )
    }
    if (!(await fresh())) return false
    const comments = await github.paginate(github.rest.issues.listComments, {
        ...target,
        per_page: 100,
    })
    const existing = comments.find(
        (item) =>
            item.user?.login === 'github-actions[bot]' &&
            /^<!-- bfo-agent-review run=\d+\.\d+ -->/.test(item.body || ''),
    )
    if (existing) {
        const previous = existing.body
            .match(/run=(\d+)\.(\d+)/)
            .slice(1)
            .map(BigInt)
        const current = run.split('.').map(BigInt)
        if (
            previous[0] > current[0] ||
            (previous[0] === current[0] && previous[1] > current[1])
        )
            return false
    }
    if (!(await fresh())) return false
    if (existing)
        await github.rest.issues.updateComment({
            owner: target.owner,
            repo: target.repo,
            comment_id: existing.id,
            body,
        })
    else await github.rest.issues.createComment({ ...target, body })
    return true
}
function prepare() {
    const {
        REVIEW_ROLE: role,
        BASE_SHA: baseSha,
        HEAD_SHA: headSha,
        CANDIDATE_DIR: candidate,
        RUNNER_TEMP: temp,
    } = process.env
    if (
        !ROLES.includes(role) ||
        ![baseSha, headSha].every((sha) => /^[a-f0-9]{40}$/.test(sha))
    )
        throw Error('Invalid review context')
    const git = (...args) =>
        execFileSync('git', ['-C', candidate, ...args], {
            maxBuffer: 32 * 1024 * 1024,
        })
    const changedPaths = git(
        'diff',
        '--name-only',
        '-z',
        `${baseSha}...${headSha}`,
    )
        .toString()
        .split('\0')
        .filter(Boolean)
    const patch = git(
        'diff',
        '--no-ext-diff',
        '--no-textconv',
        '--unified=5',
        `${baseSha}...${headSha}`,
    ).toString()
    const partial =
        changedPaths.length > 150 || Buffer.byteLength(patch) > 1024 * 1024
    const prompt = [
        fs.readFileSync(path.join(__dirname, 'common.md'), 'utf8'),
        fs.readFileSync(path.join(__dirname, 'roles', `${role}.md`), 'utf8'),
        `Role: ${role}\nbaseSha: ${baseSha}\nheadSha: ${headSha}\nCandidate source directory (untrusted data): ${candidate}\nDiff file: ${temp}/review.patch\nChanged paths: ${temp}/review-paths.json`,
        partial
            ? 'Coverage limit exceeded (>150 files or >1 MiB). Return partial and explain this limitation; inspect relevant files but do not claim complete coverage.'
            : 'Review the full relevant diff and related source context.',
    ].join('\n\n')
    fs.writeFileSync(
        `${temp}/${role}-context.json`,
        JSON.stringify({
            role,
            baseSha,
            headSha,
            fileCount: changedPaths.length,
            patchBytes: Buffer.byteLength(patch),
        }),
    )
    fs.writeFileSync(`${temp}/review.patch`, patch)
    fs.writeFileSync(`${temp}/review-paths.json`, JSON.stringify(changedPaths))
    fs.writeFileSync(`${temp}/review-prompt.md`, prompt)
}
module.exports = { ROLES, validate, aggregate, render, publish, prepare }
if (require.main === module) prepare()

async function runPublisher({ github, context, core }) {
    const event = context.payload.pull_request
    const reviewContext = {
        baseSha: event.base.sha,
        headSha: event.head.sha,
        changedPaths: [],
    }
    const target = { ...context.repo, issue_number: event.number }
    const files = await github.paginate(github.rest.pulls.listFiles, {
        ...context.repo,
        pull_number: event.number,
        per_page: 100,
    })
    reviewContext.changedPaths = files.map((file) => file.filename)
    const reports = {}
    for (const role of ROLES) {
        const file = path.join(
            process.env.REPORTS_DIR,
            `review-${role}`,
            `${role}.json`,
        )
        try {
            const stat = fs.lstatSync(file)
            if (!stat.isFile() || stat.size > 128 * 1024)
                throw Error('Invalid artifact')
            const metadataFile = path.join(
                path.dirname(file),
                `${role}-context.json`,
            )
            const metadataStat = fs.lstatSync(metadataFile)
            if (!metadataStat.isFile() || metadataStat.size > 4096)
                throw Error('Invalid coverage metadata')
            const metadata = JSON.parse(fs.readFileSync(metadataFile, 'utf8'))
            if (
                metadata.role !== role ||
                metadata.baseSha !== reviewContext.baseSha ||
                metadata.headSha !== reviewContext.headSha ||
                !Number.isSafeInteger(metadata.fileCount) ||
                metadata.fileCount < 0 ||
                !Number.isSafeInteger(metadata.patchBytes) ||
                metadata.patchBytes < 0
            )
                throw Error('Coverage identity mismatch')
            if (metadata.fileCount > 150 || metadata.patchBytes > 1024 * 1024)
                reviewContext.coverageLimited = true
            reports[role] = JSON.parse(fs.readFileSync(file, 'utf8'))
        } catch {
            core.warning(`No usable report for ${role}`)
        }
    }
    const run = `${context.runId}.${process.env.GITHUB_RUN_ATTEMPT}`
    const url = `${process.env.GITHUB_SERVER_URL}/${context.repo.owner}/${context.repo.repo}/actions/runs/${context.runId}`
    const result = render(reports, reviewContext, url, run)
    const posted = await publish(
        github,
        target,
        reviewContext,
        result.body,
        run,
    )
    if (!posted)
        core.notice('Review is stale or superseded; no comment published.')
    else if (result.incomplete)
        core.setFailed(
            'Agent review is incomplete. See role statuses in the PR report.',
        )
}
module.exports.runPublisher = runPublisher
