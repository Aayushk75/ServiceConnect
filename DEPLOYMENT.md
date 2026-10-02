# Deploying ServiceConnect to the Cloud (Free / Near-Free)

This guide takes you from "code on my laptop" to "public URL anyone can visit,"
using only free-tier services. No credit card required for the path below.

**The stack we'll use:**
- **GitHub** — hosts your code, triggers deploys
- **Render** (free tier) — runs your PHP app in the Docker container from this repo
- **Aiven** (always-free tier) — hosts your MySQL database (Render's own free DB is
  Postgres, not MySQL, so we use a separate free MySQL provider)

> ⚠️ A heads-up on free tiers, so there are no surprises: Render's free web
> service **goes to sleep after ~15 minutes of no traffic** and takes 30–60
> seconds to wake back up on the next visit. That's completely fine for a
> student project / portfolio demo. If you later want it always-on, Render's
> paid tier starts around $7/month.

---

## Part 1 — Push your code to GitHub

1. **Create a GitHub account** at github.com if you don't have one.

2. **Create a new repository:**
   - Click the **+** icon (top right) → **New repository**
   - Name it `serviceconnect`
   - Leave it **Public** (required for free-tier deploys on some platforms)
   - Don't initialize with a README (you already have one) → **Create repository**

3. **Push your local project.** Open a terminal in your `serviceconnect` folder
   (the one from the zip I gave you) and run:
   ```bash
   git init
   git add .
   git commit -m "Initial commit - ServiceConnect"
   git branch -M main
   git remote add origin https://github.com/YOUR-USERNAME/serviceconnect.git
   git push -u origin main
   ```
   Replace `YOUR-USERNAME` with your actual GitHub username. You'll be prompted
   to log in (GitHub may ask you to use a Personal Access Token instead of your
   password — GitHub will walk you through generating one if so).

4. **Double-check `.env` was NOT uploaded.** Refresh your repo page on GitHub —
   you should see `.env.example` but never a real `.env` file (it's excluded by
   `.gitignore` on purpose, since it would contain secrets).

---

## Part 2 — Create your free MySQL database (Aiven)

1. Go to **aiven.io** → **Sign up** (no credit card needed for the free plan).
2. Click **Create service** → choose **MySQL**.
3. Pick the **Free** plan, choose any region close to you, name it `serviceconnect-db`.
4. Wait ~2 minutes for it to spin up (status turns green / "Running").
5. Open the service → **Overview** tab. You'll see connection details:
   - Host, Port, User, Password, Database name
   - There's also a ready-made **Service URI** that looks like:
     `mysql://avnadmin:XXXXXXXX@your-service.aivencloud.com:12345/defaultdb?ssl-mode=REQUIRED`
   - **Copy this URI** — this is what you'll paste into Render as `DATABASE_URL`.
6. **Load your schema:** In the Aiven console, open the built-in **Query Editor**
   (or connect with any MySQL client / phpMyAdmin-alternative using the
   connection details above), and paste in the contents of `sql/schema.sql`
   from your project, then run it. This creates your tables and seed data.

---

## Part 3 — Deploy the app on Render

1. Go to **render.com** → **Sign up** → **Sign up with GitHub** (easiest —
   this also grants Render permission to read your repos).

2. On the Render dashboard, click **New +** → **Web Service**.

3. **Connect your repository:** find and select `serviceconnect`, click **Connect**.

4. Render will detect the `Dockerfile` in your repo automatically. Configure:
   - **Name:** `serviceconnect` (this becomes part of your URL)
   - **Region:** closest to you
   - **Branch:** `main`
   - **Runtime:** Docker (should auto-select since it found the Dockerfile)
   - **Instance Type:** **Free**

5. Scroll to **Environment Variables** and add:
   | Key | Value |
   |---|---|
   | `APP_ENV` | `production` |
   | `DATABASE_URL` | *paste the Aiven Service URI from Part 2* |

   > Note: the Aiven URI includes `?ssl-mode=REQUIRED` at the end — that's fine,
   > `parse_url()` in `config/db.php` ignores the query string, and Aiven's
   > MySQL accepts non-SSL connections too on the free plan, so the app will
   > connect either way.

6. Click **Create Web Service**. Render will pull your repo, build the Docker
   image (this takes a few minutes the first time), and deploy it.

7. Watch the **Logs** tab — when you see Apache start successfully, your site
   is live. Render shows your public URL at the top of the page, something like:
   ```
   https://serviceconnect.onrender.com
   ```

8. **Visit the URL.** First load may take 30-60 seconds (cold start) if the
   service was asleep. You should see the ServiceConnect homepage.

9. **Test it end-to-end:** register a driver account, register a mechanic
   account, log in as the seeded admin (`admin@serviceconnect.com`) to verify
   the mechanic — or just register everything fresh through the UI, since new
   signups always hash passwords correctly regardless of the seed data.

---

## Part 4 — Every future update

Any time you change your code:
```bash
git add .
git commit -m "describe your change"
git push
```
Render automatically detects the push and redeploys — no manual steps needed.

---

## Alternative: Railway (bundles app + MySQL together, but not fully free)

Railway is genuinely the smoothest experience — it can host your PHP app *and*
a MySQL database in the same project, auto-wiring `DATABASE_URL` for you with
zero manual copy-pasting. The tradeoff: as of 2026, Railway no longer has an
indefinite free tier — you get a one-time trial credit, then it's a minimum of
about $5/month once you add a database alongside your app. If that's within
budget, it's worth it for the convenience:

1. railway.app → sign up → **New Project** → **Deploy from GitHub repo** →
   select `serviceconnect`. Railway detects your `Dockerfile` automatically.
2. In the same project, click **+ New** → **Database** → **Add MySQL**.
   Railway auto-injects `DATABASE_URL` into your app's environment — you don't
   need to set it manually.
3. Open your MySQL service → **Data** tab → run the contents of `sql/schema.sql`.
4. Go to your app service → **Settings** → **Networking** → **Generate Domain**
   to get your public URL.

---

## Troubleshooting

- **"Database connection failed"** — double-check the `DATABASE_URL` value in
  Render's environment variables (no extra spaces, no quotes around it).
- **Blank white page** — check Render's **Logs** tab for the actual PHP error;
  with `APP_ENV=production` set, error details are hidden from visitors but
  still show up in the logs.
- **Changes not showing up** — confirm the push actually went to the `main`
  branch and that Render's latest deploy log shows your newest commit hash.
- **Site is slow to load the first time** — that's the free-tier cold start
  (15 min of inactivity → sleep). Totally normal, not a bug.
