const { test } = require('node:test')
const assert = require('node:assert/strict')
const { validate, aggregate, render, publish } = require('./review.cjs')
const context = {
    baseSha: 'a'.repeat(40),
    headSha: 'b'.repeat(40),
    changedPaths: ['app/a.php'],
}
const finding = {
    priority: 'P2',
    path: 'app/a.php',
    startLine: 3,
    endLine: 5,
    title: 'Lost update',
    body: 'Concurrent writes overwrite the saved value.',
}
function report(role = 'architecture', extra = {}) {
    return {
        role,
        baseSha: context.baseSha,
        headSha: context.headSha,
        status: 'completed',
        summary: 'Checked',
        limitations: [],
        findings: [finding],
        ...extra,
    }
}
test('rejects mismatched identity, traversal, extras and contradictory reports', () => {
    for (const value of [
        report('security'),
        report('architecture', { headSha: 'c'.repeat(40) }),
        report('architecture', { extra: true }),
        report('architecture', { status: 'not_applicable' }),
        report('architecture', {
            findings: [{ ...finding, path: '../secret' }],
        }),
        report('architecture', { findings: [{ ...finding, startLine: 0 }] }),
    ]) {
        assert.throws(() => validate(value, 'architecture', context))
    }
    assert.equal(
        validate(report(), 'architecture', context).status,
        'completed',
    )
})
test('combines duplicates, keeps strongest priority and distinct issues', () => {
    const result = aggregate([
        report(),
        report('ddd', {
            findings: [
                { ...finding, priority: 'P1' },
                { ...finding, title: 'Missing event' },
            ],
        }),
    ])
    assert.equal(result.length, 2)
    assert.equal(result[0].priority, 'P1')
    assert.deepEqual(result[0].roles, ['architecture', 'ddd'])
})
test('renders all six statuses, missing errors, partial limits and escapes hostile output', () => {
    const reports = {
        architecture: report('architecture', {
            status: 'partial',
            limitations: ['No browser'],
            summary: '<script>@all</script>',
            findings: [{ ...finding, title: '[click](https://evil.test)' }],
        }),
    }
    const result = render(
        reports,
        context,
        'https://github.com/o/r/actions/runs/1',
        '1.1',
    )
    assert.equal(result.incomplete, true)
    assert.match(result.body, /security.*error/)
    assert.match(result.body, /No browser/)
    assert.doesNotMatch(result.body, /<script>|@all|\[click\]\(https:/)
    assert.match(result.body, /uiux/)
})
function api(pr, comments = []) {
    const calls = []
    const github = {
        rest: {
            pulls: { get: async () => ({ data: pr }) },
            issues: {
                listComments: 'list',
                createComment: async (v) => calls.push(v),
                updateComment: async (v) => calls.push(v),
            },
        },
        paginate: async () => comments,
    }
    return { github, calls }
}
const pr = {
    state: 'open',
    base: { sha: context.baseSha },
    head: { sha: context.headSha },
}
test('does not publish for stale or closed PR', async () => {
    for (const current of [
        { ...pr, state: 'closed' },
        { ...pr, head: { sha: 'c'.repeat(40) } },
        { ...pr, base: { sha: 'c'.repeat(40) } },
    ]) {
        const { github, calls } = api(current)
        await publish(
            github,
            { owner: 'o', repo: 'r', issue_number: 1 },
            context,
            'body',
            '1.1',
        )
        assert.equal(calls.length, 0)
    }
})
test('updates only bot-owned marker and ignores older runs on same commit', async () => {
    const marker = '<!-- bfo-agent-review run=2.1 -->'
    const { github, calls } = api(pr, [
        { id: 12, user: { login: 'github-actions[bot]' }, body: marker },
    ])
    await publish(
        github,
        { owner: 'o', repo: 'r', issue_number: 1 },
        context,
        'new',
        '1.1',
    )
    assert.equal(calls.length, 0)
    await publish(
        github,
        { owner: 'o', repo: 'r', issue_number: 1 },
        context,
        'new',
        '2.2',
    )
    assert.equal(calls[0].comment_id, 12)
})
test('coverage limits override a model claiming completion', () => {
    const reports = Object.fromEntries(
        ['architecture', 'ddd', 'security', 'tests', 'frontend', 'uiux'].map(
            (role) => [role, report(role, { findings: [] })],
        ),
    )
    const result = render(
        reports,
        { ...context, coverageLimited: true },
        'https://github.com/o/r/actions/runs/1',
        '1.1',
    )
    assert.equal(result.incomplete, true)
    assert.match(result.body, /partial/)
    assert.doesNotMatch(result.body, /Все шесть ролей завершены/)
})
test('six complete reports including not applicable produce a complete review', () => {
    const reports = Object.fromEntries(
        ['architecture', 'ddd', 'security', 'tests', 'frontend', 'uiux'].map(
            (role) => [
                role,
                report(role, { status: 'not_applicable', findings: [] }),
            ],
        ),
    )
    assert.equal(
        render(reports, context, 'https://github.com/o/r/actions/runs/1', '1.1')
            .incomplete,
        false,
    )
})
test('publisher posts incomplete status and fails visibly for missing artifacts', async () => {
    const { runPublisher } = require('./review.cjs')
    const { github, calls } = api(pr)
    github.rest.pulls.listFiles = 'files'
    github.paginate = async (method) =>
        method === 'files' ? [{ filename: 'app/a.php' }] : []
    const failures = []
    const saved = process.env.REPORTS_DIR
    process.env.REPORTS_DIR = '/nonexistent/bfo-review-reports'
    try {
        await runPublisher({
            github,
            context: {
                payload: { pull_request: { ...pr, number: 1 } },
                repo: { owner: 'o', repo: 'r' },
                runId: 1,
            },
            core: {
                warning() {},
                notice() {},
                setFailed: (message) => failures.push(message),
            },
        })
        assert.equal(calls.length, 1)
        assert.match(calls[0].body, /Проверка неполная/)
        assert.equal(failures.length, 1)
    } finally {
        if (saved === undefined) delete process.env.REPORTS_DIR
        else process.env.REPORTS_DIR = saved
    }
})
