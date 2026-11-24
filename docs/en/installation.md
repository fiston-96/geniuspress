# Installing GeniusPress

GeniusPress installs in three steps from your browser, without touching any configuration file. It works on shared hosting, on a VPS or on a personal computer, with or without Internet access.

## What you need

| Item | Minimum |
|---|---|
| PHP | 8.3 or newer |
| PHP extensions | `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `fileinfo`, `filter`, and `pdo_mysql` **or** `pdo_sqlite` |
| Recommended extensions | `intl` (dates and numbers), `curl` (AI assistant), `gd` (images), `zip` (export, import, plugins) |
| Database | MySQL 8, MariaDB 10.6 or newer, **or** SQLite (a simple file, no server needed) |
| Write permissions | The `.env` file (or the site folder if it does not exist yet), `storage/`, `bootstrap/cache/` |

The installer checks all of this at the first step and tells you exactly what is missing.

## Installing with the installer

1. **Upload the files** to your server. The site's root folder, also called the "document root", must be GeniusPress's `public/` folder.
2. **Open the site's address** in your browser. You land on `/install`.
3. **Step 1 - Server.** All the checks in the list must be green. "Recommended" means GeniusPress works, but one feature will be limited.
4. **Step 2 - Database.**
   - *MySQL / MariaDB*: enter the server (often `127.0.0.1` or `localhost`), the port (`3306`), the database name, and the user and password provided by your host. Tick "Create the database if it does not exist" if your user is allowed to do so.
   - *SQLite*: nothing to fill in. Everything is stored in `database/geniuspress.sqlite`. This is ideal for local or offline use.
   - The installer tests the connection, explains errors in plain language (password refused, database not found, server unreachable...), then creates the tables.
5. **Step 3 - Your site.** Site name, content language, administrator account, and starting point: a sample home page, or an empty page to fill in yourself or with the AI assistant.

At the end, you are logged in to the dashboard. The installer is then **permanently closed**: its addresses return a 404 error.

### What the installer does for you

- It creates the `.env` file and a unique encryption key.
- It saves the database you chose. The `.env` is written only once, at the very end.
- It sets the site address (`APP_URL`) and the **mode**: on a public address, production mode is enabled and detailed error messages are hidden from visitors. On `localhost` or a `.test` address, development mode is used.
- It creates the link to uploaded files (`public/storage`).
- It writes a lock file, `storage/app/installed.lock`, which closes the installer.

## Shared hosting (zip package)

1. Download `geniuspress-x.y.z.zip`. It already contains the PHP dependencies and the compiled builder: **neither Composer nor Node.js** is needed.
2. Unzip it and upload the `geniuspress` folder to your host, by FTP or with the file manager in your hosting control panel.
3. If possible, choose `geniuspress/public` as the domain's root folder. Otherwise, upload the contents into `public_html`: the supplied `.htaccess` file redirects everything to `public/` and blocks access to sensitive files.
4. Open the site's address: the installer starts.

To build the package yourself from the source code:

```bash
npm run build
php artisan geniuspress:package      # creates dist/geniuspress-x.y.z.zip
```

## Docker

The `docker-compose.yml` file starts GeniusPress (Apache, PHP 8.3) and MariaDB:

```bash
docker compose up -d
```

Open <http://localhost:8080>. The installer appears with the database already filled in: leave the password empty, the server's own password is used.

**Automatic installation**, without the installer: set these variables in a `.env` file next to `docker-compose.yml`, or in your shell, before the first start:

```dotenv
GP_SITE_NAME="Hope NGO"
GP_SITE_LOCALE=en
GP_ADMIN_NAME=Amani
GP_ADMIN_EMAIL=admin@ong-espoir.org
GP_ADMIN_PASSWORD=a-strong-password
APP_URL=https://ong-espoir.org
DB_PASSWORD=another-password
ANTHROPIC_API_KEY=            # optional: AI assistant
```

| Volume | Contents |
|---|---|
| `storage` | Uploaded files, configuration (`.env`), installation lock, logs |
| `plugins` | Installed plugins |
| `db` | MariaDB database |

**Updating**: `docker compose pull` (or `docker compose build`) then `docker compose up -d`. On startup, new migrations are applied automatically, and the volumes keep your data.

## Command-line installation

On a VPS or in a deployment script:

```bash
php artisan geniuspress:install
```

The command asks the same questions as the installer. Everything can also be given as options, or through the environment variables shown in parentheses, for an unattended installation:

| Option | Purpose |
|---|---|
| `--db=mysql` or `--db=sqlite` | Database type (`DB_CONNECTION`) |
| `--db-host`, `--db-port`, `--db-name`, `--db-user`, `--db-password` | MySQL connection, created if needed (`DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) |
| `--site-name` | Site name (`GP_SITE_NAME`) |
| `--locale` | Content language: `fr`, `en`, `sw`, `ln`, `pt`, `es`, `ar`, `rw` (`GP_SITE_LOCALE`) |
| `--admin-name`, `--admin-email`, `--admin-password` | Administrator account (`GP_ADMIN_NAME`, `GP_ADMIN_EMAIL`, `GP_ADMIN_PASSWORD`) |
| `--url` | Public address, which enables production mode if it is not a local address (`APP_URL`) |
| `--blank` | Empty home page |

```bash
php artisan geniuspress:install --db=mysql --db-host=127.0.0.1 --db-name=my_site --db-user=my_user \
    --db-password="…" --site-name="Hope NGO" --locale=en --admin-name="Amani" \
    --admin-email=admin@ong-espoir.org --admin-password="…" --url=https://ong-espoir.org -n
```

## Frequently asked questions

**I already have a configured `.env` file.** The installer picks up its MySQL values in the form. If the database is already ready and contains the tables, it goes straight to step 3.

**I develop with `php artisan serve`.** That server restarts every time the `.env` changes. The installer takes this into account and writes the file only at the very end. If the final page stops loading, reload it: the installation is complete.

**I want to reinstall from scratch.** Delete `storage/app/installed.lock`, empty the database (or delete the SQLite file), then open `/install`.

**An installation from before the lock existed.** It is recognized automatically (the database already contains an account) and the lock is created.

**Security.** As long as the installation is not finished, anyone who knows the site's address can open `/install`. Finish the installation right after uploading the files, or install from the command line before making the site public.
