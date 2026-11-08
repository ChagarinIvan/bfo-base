#!/usr/bin/env bash
set -euo pipefail

case "$REVIEW_ROLE" in
  architecture|ddd|security|tests|frontend|uiux) ;;
  *) echo 'Unexpected review role' >&2; exit 1 ;;
esac

# Separate UID keeps the model away from the runner-owned API proxy process.
sudo adduser --system --home /home/codex-review --shell /bin/bash --group codex-review
# Runner home directories are group-traversable, as in the upstream Action example.
sudo usermod -a -G runner codex-review

# Do not follow candidate symlinks or grant writes to either source checkout.
sudo chgrp -hR codex-review "$GITHUB_WORKSPACE/trusted" "$GITHUB_WORKSPACE/candidate"
sudo find "$GITHUB_WORKSPACE/trusted" "$GITHUB_WORKSPACE/candidate" -type d -exec chmod g=rx {} +
sudo find "$GITHUB_WORKSPACE/trusted" "$GITHUB_WORKSPACE/candidate" -type f -exec chmod g=rX {} +
sudo -u codex-review -- git config --global --add safe.directory "$GITHUB_WORKSPACE/trusted"
sudo -u codex-review -- git config --global --add safe.directory "$GITHUB_WORKSPACE/candidate"
sudo chgrp codex-review "$RUNNER_TEMP"
sudo chmod g+x "$RUNNER_TEMP"
sudo chgrp codex-review "$RUNNER_TEMP/review-prompt.md" "$RUNNER_TEMP/review.patch" "$RUNNER_TEMP/review-paths.json"
sudo chmod g+r "$RUNNER_TEMP/review-prompt.md" "$RUNNER_TEMP/review.patch" "$RUNNER_TEMP/review-paths.json"

# Both the Action (runner) and Codex need access to the output. Sticky mode keeps
# Codex from replacing the runner-owned coverage metadata inside this directory.
review_output="$RUNNER_TEMP/codex-output"
mkdir -p "$review_output"
cp "$RUNNER_TEMP/$REVIEW_ROLE-context.json" "$review_output/$REVIEW_ROLE-context.json"
sudo chgrp codex-review "$review_output" "$review_output/$REVIEW_ROLE-context.json"
chmod 1770 "$review_output"
chmod 640 "$review_output/$REVIEW_ROLE-context.json"
# Ensure the Action can read newly created output even if Codex uses a strict umask.
sudo setfacl -m d:u:runner:r,d:m::rwx "$review_output"

# Verify the effective account permissions before the API proxy starts.
sudo -u codex-review -- test -r "$GITHUB_WORKSPACE/trusted/.github/review/report.schema.json"
sudo -u codex-review -- test -r "$RUNNER_TEMP/review.patch"
sudo -u codex-review -- test -w "$review_output"
if sudo -u codex-review -- test -w "$GITHUB_WORKSPACE/trusted/.github/review/report.schema.json"; then
  echo 'Reviewer must not be able to modify trusted controls' >&2
  exit 1
fi
if sudo -u codex-review -- sudo -n true 2>/dev/null; then
  echo 'Reviewer must not have sudo access' >&2
  exit 1
fi
