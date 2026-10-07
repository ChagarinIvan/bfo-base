# UI Contract: `/app/updates`

- Guest direct navigation redirects to `/app/login?return=/app/updates`; guest menu omits the updates link.
- Authenticated navigation exposes «Абнаўленні»; direct and link navigation show the same static page.
- Intro says dates describe fixation in project history, not production release.
- Each row shows date, title and concise summary; summary activates a keyboard-accessible disclosure.
- Expanded content shows detail, with an example or current SPA destination only when useful. Links have no arrow icon; replaced and internal changes have no GitHub link.
- Rows sort newest first; equal-date rows have stable slug order.
- No API calls fetch update entries.
