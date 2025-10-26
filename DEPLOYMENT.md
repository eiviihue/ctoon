Deployment guide — CToon
========================

This file documents the recommended steps, required App Settings (Azure App Service) and GitHub secrets to deploy this Laravel app to Azure App Service using the provided GitHub Actions workflow (`.github/workflows/main_CToon.yml`). It also explains the post-deploy sync (`php artisan az:sync-from-storage`) that the workflow triggers.

1) High-level summary
---------------------
- The workflow builds and deploys the repository to Azure App Service.
- The workflow sets App Service app settings so the site serves the `public/` folder as the document root and runs a post-deploy script.
- The post-deploy script runs:
  - `php artisan storage:link`
  - `php artisan migrate --force`
  - `php artisan az:sync-from-storage`

2) Required GitHub repository secrets
------------------------------------
Add these to your repository (Settings → Secrets and variables → Actions) before running the workflow:

- `AZURE_WEBAPP_PUBLISH_PROFILE` — the App Service publish profile XML (used to login/deploy).
- `APP_URL` — your site URL (e.g. https://ctoon.azurewebsites.net) used when the workflow generates a `.env`.
- `DB_CONNECTION` (if different than mysql) — e.g. `mysql`, `sqlsrv`, etc.
- `DB_HOST` — database host.
- `DB_PORT` — database port.
- `DB_DATABASE` — database name.
- `DB_USERNAME` — database username.
- `DB_PASSWORD` — database password.
- `DB_SSL_CA_PATH` (optional) — path to CA if using `MYSQL_ATTR_SSL_CA`.
- `AZURE_STORAGE_ACCOUNT` — storage account name (optional; used when building blob URLs).
- `AZURE_STORAGE_KEY` — storage account key (optional; required if using Azure storage disk driver).
- `AZURE_STORAGE_CONTAINER` — storage container (eg `images`).
- `AZURE_STORAGE_URL` / `AZURE_STORAGE_SAS_TOKEN` — optional, if you need SAS-based URLs.

Notes:
- Do not store sensitive credentials in the repository. Use GitHub Secrets and/or App Service Application Settings.
- The workflow currently writes a minimal `.env` and runs `php artisan key:generate`; however App Service App Settings override `.env` values at runtime. We recommend setting production values in the App Service configuration.

3) Recommended App Service Application Settings
----------------------------------------------
Set these in the Azure Portal (App Service → Configuration → Application settings) or via the workflow's `az webapp config appsettings set` command. App Settings are preferred for production because they are secure and can be changed without modifying code.

- `APP_ENV` = `production`
- `APP_DEBUG` = `false`
- `APP_KEY` = (generated — see below)
- `APP_URL` = https://<your-app>.azurewebsites.net

Database (set per your DB provider):
- `DB_CONNECTION` = `mysql` (or `sqlsrv`, `pgsql`)
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `MYSQL_ATTR_SSL_CA` (optional) — if your DB needs SSL CA

Azure storage (if you want your app to construct blob URLs):
- `AZURE_STORAGE_NAME` or `AZURE_STORAGE_ACCOUNT` = <storage account name>
- `AZURE_STORAGE_CONTAINER` = <container name> (for example `images`)
- `AZURE_STORAGE_KEY` (optional, if you configure filesystem azure disk)
- `AZURE_STORAGE_SAS_TOKEN` (optional — not required if blobs are public)

Document root and deployment script (the workflow sets these, but here for reference):
- `WEBSITE_WEBROOT` = `/home/site/wwwroot/public`
- `POST_DEPLOYMENT_SCRIPT` = `php artisan storage:link; php artisan migrate --force; php artisan az:sync-from-storage`

4) APP_KEY generation
---------------------
- Locally you can run:

```bash
php artisan key:generate --show
```

Copy the output and set it in App Service as `APP_KEY` (Application settings). Alternatively allow the workflow to call `php artisan key:generate` but for production it's safer to set `APP_KEY` once in App Settings so it remains stable across deploys.

5) Triggering the GitHub workflow
---------------------------------
- Push to the `main` branch or use the `workflow_dispatch` UI in GitHub Actions to run the job manually.
- The workflow will:
  1. Checkout the repo, set up PHP, and install composer dependencies.
  2. Create a minimal `.env` and run `php artisan key:generate` (this is executed in the runner; the real runtime will use App Service App Settings).
  3. Login to Azure using the publish profile, set `WEBSITE_WEBROOT` and `POST_DEPLOYMENT_SCRIPT` on the App Service, and then deploy the package.
  4. During Kudu deployment on Azure, the `POST_DEPLOYMENT_SCRIPT` is executed inside the App Service environment: this runs storage linking, database migrations, and `php artisan az:sync-from-storage`.

6) Verifying the deployment and the sync
---------------------------------------
- Confirm App Service configuration:
  - Open the Resource > Configuration and verify `WEBSITE_WEBROOT` set to `/home/site/wwwroot/public` and other App Settings are correct.
- Deployment logs:
  - Azure Portal → App Service → Deployment Center → Logs (or Kudu at https://<app>.scm.azurewebsites.net) to view deployment logs and the post-deploy script output.
- Check the site:
  - Visit your site at `APP_URL`.
  - Open a comic page and open the browser dev tools to inspect the `img` `src` attributes. They should be either:
    - A filesystem `url()` (if you're using the Azure storage driver), or
    - A public blob URL like `https://<storage-account>.blob.core.windows.net/<container>/path/to/file.jpg` if the code built the public URL.
- Confirm database sync:
  - `php artisan az:sync-from-storage` should have added/updated page records to match blob paths. Check by viewing chapters that previously had missing pages.

7) Troubleshooting
------------------
- 403 when loading blob images:
  - Confirm the container public access level allows anonymous read access, or use SAS tokens and include them in URLs.
- 404 when loading blob images:
  - Verify the blob path exactly matches the stored `cover_path` or `image_path` in the database.
- `php artisan migrate --force` fails:
  - Check DB connection settings in App Service. Check network/firewall rules for the database (e.g., allow App Service's outbound IPs if required).
- POST_DEPLOYMENT_SCRIPT not running:
  - Ensure the App Service setting `POST_DEPLOYMENT_SCRIPT` is set prior to Kudu running post-deploy tasks. The workflow sets it before the deploy step.

8) Notes and recommendations
----------------------------
- App Settings (Azure) override `.env` keys — keep secrets in App Service settings or GitHub Secrets, not committed to the repo.
- If you only need to run `php artisan az:sync-from-storage` once, consider running it manually and removing it from `POST_DEPLOYMENT_SCRIPT` to avoid repeated runs on every deploy.
- Consider adding a small health route (`/health`) that checks DB connectivity and a single blob existence for quick smoke checks after deploy.

9) Example `az` commands (run locally if you prefer to set App Settings manually)

```powershell
# Replace values with your resource group and web app name
az webapp config appsettings set --resource-group <rg> --name <app-name> --settings \
  WEBSITE_WEBROOT=/home/site/wwwroot/public \
  POST_DEPLOYMENT_SCRIPT="php artisan storage:link; php artisan migrate --force; php artisan az:sync-from-storage"

# Set DB values
az webapp config appsettings set --resource-group <rg> --name <app-name> --settings \
  DB_CONNECTION=mysql DB_HOST=<db-host> DB_PORT=<db-port> DB_DATABASE=<db> DB_USERNAME=<user> DB_PASSWORD='<pass>'
```

10) If you want me to add more
-----------------------------
I can:
- Add a small `/health` route and controller that returns DB + storage checks.
- Modify the workflow to push all `DB_*` and `AZURE_*` GitHub secrets automatically into App Service app settings before deployment (so you don't have to set them manually in the portal).
- Add a note in README linking to this `DEPLOYMENT.md`.

---

That's it — `DEPLOYMENT.md` has been added to the repository root with the essential steps to deploy and verify. If you'd like, I can now either (A) wire the workflow to automatically push the DB and AZURE secrets into App Settings, or (B) add the `/health` endpoint. Which would you like next?