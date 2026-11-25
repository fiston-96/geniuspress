# Backing up, exporting and importing a site

Your data belongs to you. At any time, GeniusPress can gather your whole site into **a single zip file**, so you can back it up or move it to another installation: another host, a VPS, an offline computer...

## What the export contains

| Included | Not included |
|---|---|
| All pages, in all languages, with their translation links | User accounts and passwords |
| Unpublished drafts | The AI assistant's API key |
| Search engine settings (title and description) | Version history |
| Media (images, PDFs) and their alternative texts | Plugins (to be reinstalled on the new site) |
| Posts, with their categories, tags and featured images | |
| Theme layouts (header, footer, post, archive…) and their display conditions | |
| Site settings: name, tagline, colors, font, languages, menus, blog, organization presentation | |

## From the administration area

**Backup menu** (administrators only):

- **Export the site**: a file named `site-name-date.zip` is downloaded.
- **Import a site**, with two modes:
  - *Add to the current site*: the imported pages and posts are added. An address that is already in use gets a suffix (`accueil-2`), and your home page stays the same. Categories with the same address are reused.
  - *Replace pages with the same address*: for **restoring a backup**. Existing pages are overwritten and the imported home page is used.
  - *Also import the settings* checkbox: name, colors, font and languages of the exported site.

The maximum size of an uploaded file depends on your host (it is shown on the page). For a large site, use the command line.

## Moving a site to a new installation

At the last step of the installation, choose the starting point **"Import a site"** and provide the zip file. The new site takes over all the pages, media and settings, except the name you have just entered.

From the command line:

```bash
php artisan geniuspress:install --import=my-site.zip …
```

## From the command line

```bash
php artisan geniuspress:export                      # into storage/app/private/exports/
php artisan geniuspress:export /backups/site.zip
php artisan geniuspress:import site.zip             # add to the current site
php artisan geniuspress:import site.zip --replace   # restore a backup
php artisan geniuspress:import site.zip --no-settings
```

For an automatic nightly backup, add the export command to your server's scheduler (cron).

## Security

The import never trusts the contents of the archive:
- only the expected files are read, with no raw extraction and no `../`-style paths;
- media are checked like a normal upload: allowed types, size, and SVG files containing code are rejected;
- page content is cleaned just as in the builder: no scripts, no `javascript:` links;
- settings are validated one by one, and an API key found in the archive is ignored;
- links to images are rewritten to point to the new files.

A non-blocking problem (rejected media, unknown language...) is reported under **Things to check** after the import.
