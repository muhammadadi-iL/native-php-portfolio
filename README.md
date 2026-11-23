# Muhammad Adil — Portfolio (native PHP)

Practice rewrite of the public portfolio in **native PHP** — no Laravel, no Composer, no Node API layer. Same visual design as the Vercel static site, served locally by Homebrew PHP.

| | |
| --- | --- |
| GitHub | https://github.com/muhammadadi-iL/native-php-portfolio |
| Local URL | http://127.0.0.1:8080/ |
| PHP | 8.4 (Homebrew) |
| Style | Front controller + PHP views + JSONL file storage |
| Sibling | Static/Vercel portfolio stays separate |

---

## What “native PHP” means here

This project was set up as a plain PHP app on purpose (practice):

| Choice | Detail |
| --- | --- |
| No framework | No Laravel, Symfony, Slim, or Composer `vendor/` |
| Front controller | Every non-static request hits `public/index.php` |
| Built-in server | `php -S` with a router script (not Apache/Nginx required for local) |
| Shared layout | `views/layout.php` wraps page/case bodies |
| Thin APIs | `api/visit.php`, `api/chat.php`, `api/captcha.php` — plain PHP scripts |
| Persistence | Append-only JSONL under `storage/` (cap ~500 rows each) |
| Config | Optional root `.env` parsed in `app/bootstrap.php` (no dotenv package) |
| Strict types | `declare(strict_types=1);` on app/API entry files |

The CSS, JS, fonts, assets, and page copy match the original portfolio. Only the **serving layer** changed: HTML generators / Node serverless → PHP.

---

## How the request flows

```
Browser
  → http://127.0.0.1:8080/work/valora/
  → php -S  (docroot = public/)
  → public/router.php
       · if file exists under public/ (css, js, assets, pdf) → serve static
       · else → public/index.php
  → app/bootstrap.php  (paths, .env, helpers, Store, Router)
  → Router::dispatch()
       · /api/*     → require api/*.php
       · /cv/       → public/cv/index.html (print stylesheet)
       · pages      → render() → views/layout.php + views/pages|cases/*.php
```

### Minor routing details

- Paths are normalized with a trailing slash (`/about` → `/about/`).
- Case studies match `/work/{slug}/` against `views/cases/{slug}.php`.
- Meta titles/descriptions live in `app/data/pages.php`.
- `public/router.php` is **only** for the PHP built-in server. On a real host, point the vhost document root at `public/` and send unknown routes to `index.php` (same idea as Laravel’s public folder).

### Layout / views

- `views/layout.php` — `<head>`, theme/lang boot script, noscript redirect, body, JS tags.
- `views/pages/*.php` — home, about, work, how-i-ship, privacy, javascript-required (body markup only).
- `views/cases/*.php` — one file per case study (valora, food-link, msane, …).
- Page bodies already include header, footer, chat, cookies, captcha gate — same DOM as the static site so `js/site.js` keeps working.

### APIs (same paths as the static site)

| Endpoint | Role |
| --- | --- |
| `POST /api/visit` | Log a page view to `storage/visits.jsonl` |
| `GET /api/visit` | Dump visits — **localhost only** |
| `POST /api/chat` | Reply + save thread to `storage/chats.jsonl` |
| `GET /api/captcha` | Whether reCAPTCHA is enabled + site key |
| `POST /api/captcha` | Verify token with Google (skipped on localhost) |

Chat facts for Gemini live in `app/data/chat-facts.txt`. If `GEMINI_API_KEY` is empty, chat returns a short local practice reply so the UI still works offline.

---

## Prerequisites (macOS)

1. **Homebrew** — https://brew.sh  
2. **PHP 8.x via Brew**

```bash
brew install php
php -v          # expect 8.x (this machine uses 8.4)
which php       # often /usr/local/opt/php@8.4/bin/php or /opt/homebrew/opt/php/bin/php
```

3. Optional: `brew services` so php-fpm stays available in the background:

```bash
brew services start php
brew services list | grep php
# expect: php  started  …
```

Notes:

- `brew services start php` starts **php-fpm**, not the portfolio site itself.
- The portfolio site is a separate **LaunchAgent** (or a one-off `./bin/serve`) that runs PHP’s built-in server.
- You do **not** need MySQL, Redis, Nginx, or Composer for this practice app.

---

## Clone and first-time setup

```bash
git clone https://github.com/muhammadadi-iL/native-php-portfolio.git
cd native-php-portfolio

cp .env.example .env
chmod +x bin/serve bin/service bin/install-brew-service

# optional keys — leave blank for local practice
# GEMINI_API_KEY=
# RECAPTCHA_SITE_KEY=
# RECAPTCHA_SECRET_KEY=
# HOST=127.0.0.1
# PORT=8080
```

`.env` is gitignored. `storage/*.jsonl` and `storage/logs/` are also ignored so local visit/chat logs never get pushed.

---

## Run the site

### Option A — one-off (foreground)

```bash
./bin/serve
# → http://127.0.0.1:8080/
# Ctrl+C to stop
```

What `./bin/serve` does:

1. Loads `.env` if present (`HOST`, `PORT`, optional keys).
2. Resolves `php` from `PATH` (or `PHP_BIN`).
3. Runs:

```bash
php -S 127.0.0.1:8080 -t public public/router.php
```

`-t public` sets the document root. The last argument is the **router script** so pretty URLs work instead of needing physical folders for every path.

### Option B — background (Homebrew PHP + LaunchAgent) — recommended on this Mac

```bash
brew services start php          # php-fpm runtime from Brew
./bin/install-brew-service       # installs/starts the portfolio LaunchAgent
```

`bin/install-brew-service` details:

1. Confirms `php` exists.
2. Runs `brew services start php` (best-effort).
3. Writes `~/Library/LaunchAgents/com.muhammadadil.portfolio-php.plist`.
4. Points ProgramArguments at your machine’s `php` binary, `-S HOST:PORT`, `-t …/public`, and `…/public/router.php`.
5. Sets `WorkingDirectory` to the project root, `KeepAlive` + `RunAtLoad` so it survives logout/reboot.
6. Logs to `storage/logs/serve.out.log` and `storage/logs/serve.err.log`.
7. `launchctl bootstrap` + `kickstart` so it is running immediately.

Control the portfolio service:

```bash
./bin/service status     # launchctl print …
./bin/service stop
./bin/service restart    # reinstalls plist + kickstarts
./bin/service logs       # tail -f storage/logs/*
```

Default listen address: **http://127.0.0.1:8080/**  
Change with `HOST` / `PORT` in `.env` **before** `./bin/install-brew-service` (re-run install after changing port).

### Quick smoke checks

```bash
curl -sI http://127.0.0.1:8080/ | head -5
curl -s  http://127.0.0.1:8080/about/ | grep -o '<title>[^<]*</title>'
curl -s  http://127.0.0.1:8080/work/valora/ | grep -o '<title>[^<]*</title>'
curl -s -X POST http://127.0.0.1:8080/api/visit \
  -H 'Content-Type: application/json' \
  -d '{"path":"/","device":"desktop"}'
curl -s http://127.0.0.1:8080/api/captcha
```

Open in the browser: home, Work, a case study, About, Process, Privacy, Resume preview, theme toggle, language menu, chat panel.

---

## Repository layout

```
native-php-portfolio/
├── public/                 # web root (only this should be web-accessible)
│   ├── index.php           # front controller
│   ├── router.php          # php -S router (static file passthrough)
│   ├── css/site.css
│   ├── js/                 # site.js, langs.js, ar.js
│   ├── assets/             # avatar, photos, cursor
│   ├── cv/                 # resume HTML + PDF
│   ├── favicon.svg
│   └── robots.txt
├── app/
│   ├── bootstrap.php       # BASE_PATH, .env loader, requires
│   ├── helpers.php         # e(), env(), render(), json_*, clean()
│   ├── Router.php          # path → page / case / api
│   ├── Store.php           # JSONL read/append with 500-row cap
│   └── data/
│       ├── pages.php       # titles, descriptions, body classes
│       └── chat-facts.txt  # Gemini system facts
├── views/
│   ├── layout.php
│   ├── pages/              # home, about, work, …
│   └── cases/              # valora, food-link, …
├── api/
│   ├── visit.php
│   ├── chat.php
│   └── captcha.php
├── storage/                # visits.jsonl, chats.jsonl, logs/ (local only)
├── bin/
│   ├── serve               # foreground php -S
│   ├── install-brew-service
│   └── service             # start|stop|restart|status|logs
├── ops/
│   └── com.muhammadadil.portfolio-php.plist   # template / copy of LaunchAgent
├── .env.example
└── README.md
```

---

## Environment variables

Copy from `.env.example`:

| Variable | Required | Purpose |
| --- | --- | --- |
| `GEMINI_API_KEY` | No | Live chat replies via Google Gemini; without it, local fallback text |
| `RECAPTCHA_SITE_KEY` | No | Front-end captcha widget |
| `RECAPTCHA_SECRET_KEY` | No | Server-side verify; both keys needed, and **disabled on localhost** |
| `HOST` | No | Built-in server bind host (default `127.0.0.1`) |
| `PORT` | No | Built-in server port (default `8080`) |

`.env` parsing is intentional and small: `KEY=VALUE` lines, `#` comments, quotes stripped — no third-party dotenv library.

---

## Public routes

| Path | View |
| --- | --- |
| `/` | Home |
| `/work/` | All case studies + shorter work |
| `/work/<slug>/` | One case study |
| `/how-i-ship/` | Process |
| `/about/` | About |
| `/privacy/` | Privacy policy |
| `/cv/` | Resume (standalone HTML + PDF download) |
| `/javascript-required/` | Noscript fallback |

Slugs include: `valora`, `food-link`, `msane`, `waraq-oman`, `alejaar`, `billing-portal`, `avatar-coach`, `christian-business-finders`, `ash-shop`, `christian-ashram`.

---

## Why not GitHub Pages / Vercel for this repo?

| Host | PHP? |
| --- | --- |
| GitHub Pages | No — static files only. This repo is for code + local/PHP hosting. |
| Vercel (default) | No first-class native PHP runtime; the sibling site uses static HTML + Node functions. |
| This Mac | Yes — Homebrew PHP + `php -S` / LaunchAgent. |
| Shared PHP host / VPS | Yes — point docroot at `public/`. |

---

## Troubleshooting

| Symptom | Check |
| --- | --- |
| Port already in use | `lsof -i :8080`; stop the other process or set `PORT=8081` and reinstall the service |
| `php` not found | `brew install php` and ensure Brew’s `bin` is on `PATH` |
| LaunchAgent not running | `./bin/service status`; `./bin/install-brew-service`; read `storage/logs/serve.err.log` |
| CSS/JS 404 | Confirm server docroot is `public/` (use `./bin/serve`, not `php -S` from repo root without `-t public`) |
| Chat always fallback | Set `GEMINI_API_KEY` in `.env` and restart the server/service |
| Captcha never shows locally | Expected — captcha is skipped when host is localhost |
| Blank /api response | Ensure `Content-Type: application/json` on POSTs; check PHP error log |
| Contribution graph empty | Commits must use a **verified** GitHub email (this repo uses `adilm0616@gmail.com`) |

Unload the LaunchAgent completely:

```bash
./bin/service stop
# or:
launchctl bootout "gui/$(id -u)/com.muhammadadil.portfolio-php"
```

Stop Brew’s php-fpm (optional; not required to kill the portfolio server):

```bash
brew services stop php
```

---

## Git author (contributions)

Commits on this repo are authored as:

```text
Muhammad Adil <adilm0616@gmail.com>
```

Keep that email verified under GitHub → Settings → Emails so the contribution graph and Contributors list update.

---

## Contact

| | |
| --- | --- |
| Email | [adilm0616@gmail.com](mailto:adilm0616@gmail.com) |
| Phone / WhatsApp | [+92 318 0253146](https://api.whatsapp.com/send/?phone=923180253146&text=Hello%20Muhammad%2C%20I%20saw%20your%20portfolio%20and%20wanted%20to%20reach%20you.&type=phone_number&app_absent=0) |
| GitHub | https://github.com/muhammadadi-iL |
| LinkedIn | https://www.linkedin.com/in/muhammad-adil-khan-7274a1243 |
