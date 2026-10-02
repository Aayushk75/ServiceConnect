# ServiceConnect
**A Web-Based Emergency Roadside Assistance Platform**
GL Bajaj Institute of Technology & Management — Mini Project

Connects stranded drivers with nearby, verified mechanics in real time,
replacing manual phone-call coordination with instant digital dispatch.

---

## Tech Stack
- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5
- **Backend:** PHP (PDO + MySQLi-style prepared statements)
- **Database:** MySQL (via XAMPP)
- **Architecture:** 3-tier MVC-inspired separation (views / includes / ajax controllers)

---

## Deploying to the Cloud (Render / Railway)

Want a public URL instead of running this locally? See **[DEPLOYMENT.md](./DEPLOYMENT.md)**
for a full step-by-step guide covering GitHub, a free MySQL host (Aiven), and
deploying the included `Dockerfile` to Render or Railway.

The database connection (`config/db.php`) already reads from environment
variables (`DATABASE_URL`, or `DB_HOST`/`DB_USER`/etc individually) — no code
changes needed between local XAMPP and cloud hosting.

---

## Setup Instructions (XAMPP — local development)

1. **Install XAMPP** (https://www.apachefriends.org) and start **Apache** + **MySQL**.

2. **Copy the project folder** into your XAMPP web root:
   - Windows: `C:\xampp\htdocs\serviceconnect`
   - macOS/Linux: `/Applications/XAMPP/htdocs/serviceconnect` or `/opt/lampp/htdocs/serviceconnect`

3. **Create the database:**
   - Open `http://localhost/phpmyadmin`
   - Click **Import** → choose `sql/schema.sql` → Go
   - This creates the `serviceconnect` database with all tables and a couple of demo accounts.

4. **Set up your local environment file:**
   - Copy `.env.example` to `.env` in the project root
   - Defaults already match a stock XAMPP install (host: `localhost`, user: `root`,
     empty password) — only change values if yours differs
   - Run `composer install` in the project folder once, so the `.env` loader
     (vlucas/phpdotenv) is available. If you don't have Composer installed,
     grab it free from getcomposer.org — or just edit the fallback defaults
     directly in `config/db.php`, since they match XAMPP out of the box anyway.

5. **Fix the seeded password hash** (one-time step, since the placeholder hash in
   `schema.sql` needs to be regenerated on your machine):
   - Create a temp file `hash.php` in the project root with:
     ```php
     <?php echo password_hash('password123', PASSWORD_DEFAULT);
     ```
   - Visit `http://localhost/serviceconnect/hash.php`, copy the output.
   - In phpMyAdmin, run:
     ```sql
     UPDATE users SET password = '<paste hash here>';
     ```
   - Delete `hash.php` afterward.
   - **Or simply skip this** and register brand-new accounts through the UI —
     new signups always hash correctly automatically.

6. **Visit the app:** `http://localhost/serviceconnect/`

---

## Demo Accounts (after fixing the hash, see step 5)
| Role      | Email                          | Password      |
|-----------|--------------------------------|----------------|
| Admin     | admin@serviceconnect.com       | password123    |
| Mechanic  | ravi.mechanic@example.com      | password123    |
| Mechanic  | sanjay.mechanic@example.com    | password123    |

Or just register fresh **Driver** / **Mechanic** accounts from the Sign Up page —
new mechanic accounts start **unverified** until an admin approves them
(Admin → Mechanic Verification table).

---

## Folder Structure
```
serviceconnect/
├── config/db.php              # PDO database connection
├── includes/
│   ├── auth.php                # session + role guards + helpers
│   ├── header.php               # shared nav
│   └── footer.php
├── ajax/                        # AJAX controllers (dispatch, accept, rate, verify)
├── user/dashboard.php           # driver portal — submit & track requests
├── mechanic/dashboard.php       # mechanic portal — view & accept jobs
├── admin/dashboard.php          # admin portal — verify mechanics, oversee platform
├── assets/css/style.css
├── assets/js/script.js          # geolocation, AJAX handlers, polling, star rating
├── sql/schema.sql               # database schema + seed data
├── index.php / login.php / register.php / logout.php
└── README.md
```

---

## Core Workflow
1. **Register & Login** — role-based (Driver / Mechanic); mechanics await admin verification.
2. **Request Submission** — driver picks an issue type, browser geolocation captures
   lat/lng, request is inserted with status `pending`.
3. **Acceptance & Dispatch** — request broadcasts to all mechanics (AJAX polling);
   first verified mechanic to click **Accept** wins the job (race-safe via a
   conditional `UPDATE ... WHERE status='pending'` query).
4. **Resolution & Completion** — mechanic marks the job **Completed**; driver can then
   rate the mechanic, which updates their running average rating.

## Known Limitations / Suggested Next Steps
- Distance shown to mechanics uses their **last known** lat/lng (captured only if
  seeded) rather than continuous live tracking — a good "future enhancement" to
  mention in your viva, matching the presentation's roadmap slide (live GPS tracking,
  mobile app, payments, insurance tie-ups).
- No payment gateway integration (by design, per project scope).
- Notifications are via 15-second AJAX polling rather than WebSockets/push —
  simple and reliable for a mini-project demo, called out in the code comments
  as a place to extend.
