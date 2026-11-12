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
- `includes/header.php`, `includes/footer.php` — shared layout with role-based navbar; set `$pageTitle` before including the header
- `assets/css/style.css` — custom styles on top of Bootstrap (colour tokens and shared `ce-*` classes from the UI mockup)
- `database.sql` — creates the `consultease` database, tables and sample users
- `docs/ConsultEase UI Mockup.pdf` — the team's UI mockup

### Pages

`auth/` — there is no register page; accounts come from `database.sql`.
- `login.php` — email + password login, redirects by role
- `logout.php` — ends the session, back to login

`lecturer/` — every page requires `$_SESSION['user_role'] === 'lecturer'`.
- `list_slots.php` — the lecturer's slots, with Edit and Delete
- `create_slot.php` — form to create a slot
- `edit_slot.php` — form to update a slot (`?id=`)
- `delete_slot.php` — POST handler, deletes a slot (CSRF-checked)
- `booking_requests.php` — bookings on the lecturer's slots, with Confirm / Decline
- `update_booking_status.php` — POST handler, sets a Pending booking to Confirmed or Declined (CSRF-checked)

`student/` — every page requires `$_SESSION['user_role'] === 'student'`.
- `available_slots.php` — student dashboard: upcoming slots with no Pending or Confirmed booking
- `book_slot.php` — book a slot (`?slot_id=`) with a reason; blocks double booking
- `my_bookings.php` — the student's bookings and statuses, with Cancel
- `cancel_booking.php` — POST handler, sets a Pending or Confirmed booking to Cancelled (CSRF-checked)

Session keys set at login: `user_id`, `user_name`, `user_role`.

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
