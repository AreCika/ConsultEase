# ConsultEase

A simple lecturer consultation booking app for a university assignment. Students book consultation slots that lecturers create.

## Stack

- Plain PHP + MySQL, running on XAMPP (Apache + MySQL)
- Bootstrap 5 loaded from CDN
- No frameworks, no Composer, no build step
- App URL: http://localhost/ConsultEase/

## Structure

- `index.php` — home page
- `config/db.php` — mysqli connection (`$conn`) and `BASE_URL`
- `auth/` — login, register, logout
- `lecturer/` — lecturer pages (manage slots, review bookings)
- `student/` — student pages (browse slots, make bookings)
- `includes/header.php`, `includes/footer.php` — shared layout; set `$pageTitle` before including the header
- `assets/css/style.css` — custom styles on top of Bootstrap
- `database.sql` — creates the `consultease` database, tables and sample users

## Database

- `users` (id, name, email, password, role: student | lecturer)
- `slots` (id, lecturer_id → users.id, slot_date, start_time, end_time, venue)
- `bookings` (id, slot_id → slots.id, student_id → users.id, reason, status: Pending | Confirmed | Declined | Cancelled, created_at)

Sample logins: `lecturer@consultease.test` / `lecturer123`, `student1@consultease.test` and `student2@consultease.test` / `student123`.

## Rules

- Keep code simple and beginner-friendly. Procedural PHP is fine; avoid clever abstractions.
- Always use prepared statements (`$conn->prepare()` + `bind_param()`) for any query that uses user input. Never put variables directly into SQL strings.
- Hash passwords with `password_hash()` and check them with `password_verify()`.
- Escape output with `htmlspecialchars()`.
- Start every commit message with the Jira key, e.g. `KAN-12 Add student booking form`.
- For any UI/UX work, use the ui-ux-pro-max skill and follow the team's UI mockup in the docs/ folder.
