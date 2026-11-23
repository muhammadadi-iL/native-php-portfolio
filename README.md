# Muhammad Adil — Portfolio (native PHP)

Practice portfolio in **native PHP** (no Laravel, no Composer, no Node APIs). Same design as the Vercel static site; runs locally with Homebrew PHP.

| | |
| --- | --- |
| GitHub | https://github.com/muhammadadi-iL/native-php-portfolio |
| Contributor | [@muhammadadi-iL](https://github.com/muhammadadi-iL) · `adilm0616@gmail.com` |
| Local URL | http://127.0.0.1:8080/ |
| PHP | 8.4 via Homebrew |

---

## Native PHP setup — start here

Yeh project **plain / native PHP** pe chalaya jata hai. Neeche wala tareeqa start se end tak hai (macOS + Homebrew).

### Step 1 — Homebrew install karo (agar nahi hai)

```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

### Step 2 — Native PHP install karo

```bash
brew install php
php -v
which php
```

Expect PHP **8.x** (is machine pe **8.4**). Path aksar:

- Intel Mac: `/usr/local/opt/php@8.4/bin/php`
- Apple Silicon: `/opt/homebrew/opt/php/bin/php`

PHP `PATH` mein hona chahiye. Check:

```bash
echo $PATH
# brew bin pehle hona chahiye, e.g. /usr/local/bin or /opt/homebrew/bin
```

### Step 3 — Brew PHP service (php-fpm) start karo

```bash
brew services start php
brew services list | grep php
# expect: php   started   …
```

**Note:** Yeh **php-fpm** start karta hai (Homebrew ka PHP runtime). Portfolio site alag se `php -S` / LaunchAgent se chalti hai — Step 6.

Is practice app ke liye **MySQL, Redis, Nginx, Composer zaroori nahi**.

### Step 4 — Repo clone karo

```bash
git clone https://github.com/muhammadadi-iL/native-php-portfolio.git
cd native-php-portfolio
```

### Step 5 — Env + scripts ready karo

```bash
cp .env.example .env
chmod +x bin/serve bin/service bin/install-brew-service
```

`.env` mein optional keys (khali chhod sakte ho local practice ke liye):

```bash
GEMINI_API_KEY=
RECAPTCHA_SITE_KEY=
RECAPTCHA_SECRET_KEY=
HOST=127.0.0.1
PORT=8080
```

`.env`, `storage/*.jsonl`, aur `storage/logs/` gitignore pe hain — push nahi hote.

### Step 6 — Site chalao

**Asan tareeqa (foreground — testing ke liye):**

```bash
./bin/serve
```

Browser: **http://127.0.0.1:8080/**  
Stop: `Ctrl+C`

Andar yeh command chalti hai:

```bash
php -S 127.0.0.1:8080 -t public public/router.php
```

- `-t public` → document root = `public/`
- `public/router.php` → pretty URLs (`/about/`, `/work/valora/`, …) bina physical folder ke

**Background (Mac pe recommended — Brew + LaunchAgent):**

```bash
brew services start php
./bin/install-brew-service
```

Yeh `~/Library/LaunchAgents/com.muhammadadil.portfolio-php.plist` likhta hai aur `php -S` ko `KeepAlive` ke sath chalata hai (reboot ke baad bhi).

```bash
./bin/service status
./bin/service stop
./bin/service restart
./bin/service logs
```

### Step 7 — Verify

```bash
curl -sI http://127.0.0.1:8080/ | head -5
curl -s  http://127.0.0.1:8080/about/ | grep -o '<title>[^<]*</title>'
curl -s  http://127.0.0.1:8080/work/valora/ | grep -o '<title>[^<]*</title>'
```

Browser mein open karo: Home, Work, case study, About, Process, Privacy, Resume, theme, language, chat.

### Setup summary (short)

```text
1. brew install php
2. brew services start php
3. git clone … && cd native-php-portfolio
4. cp .env.example .env && chmod +x bin/*
5. ./bin/serve
   OR  ./bin/install-brew-service
6. open http://127.0.0.1:8080/
```

---

## What “native PHP” means here

| Choice | Detail |
| --- | --- |
| No framework | No Laravel, Symfony, Slim, or Composer `vendor/` |
| Front controller | Every non-static request hits `public/index.php` |
| Built-in server | `php -S` + router script (local pe Apache/Nginx optional) |
| Shared layout | `views/layout.php` wraps page/case bodies |
| Thin APIs | `api/visit.php`, `api/chat.php`, `api/captcha.php` |
| Persistence | JSONL under `storage/` (~500 row cap) |
| Config | Root `.env` parsed in `app/bootstrap.php` (no dotenv package) |
| Strict types | `declare(strict_types=1);` on app/API files |

CSS/JS/assets/copy original portfolio jaisi hain. Sirf serving layer PHP pe hai.

---

## How the request flows

```
Browser
  → http://127.0.0.1:8080/work/valora/
  → php -S  (docroot = public/)
  → public/router.php
       · static file under public/ → serve as-is
       · else → public/index.php
  → app/bootstrap.php
  → Router::dispatch()
       · /api/*  → api/*.php
       · /cv/    → public/cv/index.html
       · pages   → views/layout.php + views/pages|cases/*.php
```

### Minor routing details

- Trailing slash normalize: `/about` → `/about/`
- Cases: `/work/{slug}/` → `views/cases/{slug}.php`
- Meta: `app/data/pages.php`
- `public/router.php` sirf built-in server ke liye; real host pe docroot = `public/`, unknown routes → `index.php`

### APIs

| Endpoint | Role |
| --- | --- |
| `POST /api/visit` | Page view → `storage/visits.jsonl` |
| `GET /api/visit` | Dump visits — **localhost only** |
| `POST /api/chat` | Reply + save → `storage/chats.jsonl` |
| `GET/POST /api/captcha` | reCAPTCHA (localhost pe skip) |

Chat facts: `app/data/chat-facts.txt`. Bina `GEMINI_API_KEY` ke local fallback reply aati hai.

### LaunchAgent (`./bin/install-brew-service`) — thori detail

1. `php` binary check
2. `brew services start php` (best-effort)
3. Plist write: `com.muhammadadil.portfolio-php`
4. Args: `php -S HOST:PORT -t …/public …/public/router.php`
5. `KeepAlive` + `RunAtLoad`
6. Logs: `storage/logs/serve.out.log` / `serve.err.log`
7. `launchctl bootstrap` + `kickstart`

Port badalna ho to pehle `.env` mein `PORT=` set karo, phir `./bin/install-brew-service` dubara chalao.

---

## Repository layout

```
native-php-portfolio/
├── public/           # web root
│   ├── index.php     # front controller
│   ├── router.php    # php -S router
│   ├── css/  js/  assets/  cv/
├── app/              # bootstrap, Router, Store, helpers, data/
├── views/            # layout + pages + cases
├── api/              # visit, chat, captcha
├── storage/          # jsonl + logs (local)
├── bin/              # serve, install-brew-service, service
├── ops/              # LaunchAgent plist template
├── .env.example
├── CONTRIBUTORS.md
└── README.md
```

---

## Environment variables

| Variable | Required | Purpose |
| --- | --- | --- |
| `GEMINI_API_KEY` | No | Live Gemini chat; warna local fallback |
| `RECAPTCHA_SITE_KEY` | No | Captcha widget |
| `RECAPTCHA_SECRET_KEY` | No | Captcha verify; localhost pe skip |
| `HOST` | No | Default `127.0.0.1` |
| `PORT` | No | Default `8080` |

---

## Public routes

| Path | Page |
| --- | --- |
| `/` | Home |
| `/work/` | All work |
| `/work/<slug>/` | Case study |
| `/how-i-ship/` | Process |
| `/about/` | About |
| `/privacy/` | Privacy |
| `/cv/` | Resume |
| `/javascript-required/` | Noscript fallback |

Slugs: `valora`, `food-link`, `msane`, `waraq-oman`, `alejaar`, `billing-portal`, `avatar-coach`, `christian-business-finders`, `ash-shop`, `christian-ashram`.

---

## Why not GitHub Pages / Vercel for this repo?

| Host | Runs this PHP app? |
| --- | --- |
| GitHub Pages | No (static only) — repo code ke liye hai |
| Vercel default | No first-class native PHP |
| This Mac | Yes — Brew PHP + `php -S` / LaunchAgent |
| PHP shared host / VPS | Yes — docroot → `public/` |

---

## Troubleshooting

| Symptom | Fix |
| --- | --- |
| Port in use | `lsof -i :8080` ya `PORT=8081` + service reinstall |
| `php` not found | `brew install php` + Brew `bin` on `PATH` |
| Service down | `./bin/service status` → `./bin/install-brew-service` → `storage/logs/serve.err.log` |
| CSS/JS 404 | Docroot must be `public/` (`./bin/serve`, not bare `php -S` from repo root) |
| Chat fallback only | `.env` mein `GEMINI_API_KEY` + restart |
| Captcha nahi dikha locally | Expected on localhost |
| Contribution graph empty | GitHub Settings → Emails: `adilm0616@gmail.com` **Verified** |

```bash
./bin/service stop
# or:
launchctl bootout "gui/$(id -u)/com.muhammadadil.portfolio-php"

brew services stop php   # optional — php-fpm only
```

---

## Git author (contributions)

```text
Muhammad Adil <adilm0616@gmail.com>
GitHub: https://github.com/muhammadadi-iL
```

Email verified rakho taake [Contributors](https://github.com/muhammadadi-iL/native-php-portfolio/graphs/contributors) aur profile progress count ho.

---

## Contact

| | |
| --- | --- |
| Email | [adilm0616@gmail.com](mailto:adilm0616@gmail.com) |
| Phone / WhatsApp | [+92 318 0253146](https://api.whatsapp.com/send/?phone=923180253146&text=Hello%20Muhammad%2C%20I%20saw%20your%20portfolio%20and%20wanted%20to%20reach%20you.&type=phone_number&app_absent=0) |
| GitHub | https://github.com/muhammadadi-iL |
| LinkedIn | https://www.linkedin.com/in/muhammad-adil-khan-7274a1243 |
