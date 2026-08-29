# Deployment

Production runs the PHP application and Nginx in Docker Compose. The Dockerfile
uses a Node.js 22 frontend build stage and copies only the generated
`public/spa/` artifact into the PHP image. This keeps the SPA build independent
of the host npm version and does not change the legacy laravel-mix output.

## Release

1. Merge the pull request into `master`.
2. On the EC2 host, fetch the release:

```bash
git pull origin master
```

3. Build and recreate the application image:

```bash
docker compose build --pull app
docker compose up -d --force-recreate app webserver
```

4. Apply database migrations:

```bash
docker compose exec app php artisan migrate --force
```

5. Check the routes and SPA shell:

```bash
docker compose exec app php artisan route:list
curl -I https://example.com/app/competitions
```

The production Compose file must not mount the source tree over `/var/www`,
because such a volume hides the application files and the generated
`public/spa/` artifact from the image.

## Sanctum migration

Existing installations may already have `personal_access_tokens` created by the
original Sanctum migration. The current migration is idempotent for that case;
do not drop the table or run `migrate:fresh`, because existing Bearer tokens
would be lost. Preserve the existing users table and password hashes; Sanctum
tokens are linked to the same user IDs through `tokenable_id`.

Sanctum token lifetime is 1440 minutes. Expired records can be pruned with the
standard Sanctum pruning command when scheduled.
