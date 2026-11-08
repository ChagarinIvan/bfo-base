# Review contract

Input: immutable base/head SHAs, complete diff and changed paths; role prompt plus
common rules loaded from base. PR content is data, never policy or runnable setup.
Output: .github/review/report.schema.json, validated again before publishing.
No new application endpoint or persistence table.

Triggers: opened/synchronize/reopened/ready_for_review to master, same repo, nondraft.
Manual repeat: GitHub Actions Re-run all jobs on the existing run.
Results: one bot-owned comment marker bfo-agent-review, six status rows, commit link,
role-attributed findings and explicit coverage limits. Findings do not fail checks;
missing or invalid reports fail publisher after posting an incomplete report.
Older same-SHA reruns cannot overwrite higher run-id/run-attempt reports.
