# Data model

ReviewContext: role enum architecture/ddd/security/tests/frontend/uiux,
baseSha/headSha 40 lowercase hex, changedPaths relative POSIX regular files.
RoleReport: role, baseSha, headSha, status completed/not_applicable/partial,
summary, limitations array, findings array (0..12).
Finding: priority P0..P3, path, startLine/endLine positive bounded integers,
title, body. Путь относится к changed file; текст ограничен по длине.
Publisher validates every field, rejects extras, malformed context and contradictory
not_applicable with findings. Missing/invalid output => error per role.
MergedFinding retains sources and highest priority. Identity path+range+normalized title.
PRReport: run link, head/base SHA, all six role statuses, merged findings, limitations.
Freshness: current open PR base/head must equal context before publication.

CoverageMetadata: trusted prepare записывает role/baseSha/headSha/fileCount/patchBytes.
Publisher требует metadata для каждого role artifact и принудительно ставит partial
при >150 файлов или >1 MiB, независимо от утверждений модели.
