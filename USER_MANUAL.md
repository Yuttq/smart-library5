# Smart Library System — User Manual

## Overview
Smart Library is a PHP/MySQL web app with role-based access for Librarian, Staff, Students, and Teachers. It supports book inventory, borrowing/returns, reservations, penalties, and semester-based clearance.

## Getting Started
- Access: `http://localhost/smart-library/`
- Demo logins (default unless changed):
  - Librarian: `librarian1` / `password`
  - Staff: `staff1` / `password`
  - Student: `student1` / `password`
  - Teacher: `teacher1` / `password`
- Database: Import `database/schema_v3.sql` in phpMyAdmin before first use.
- Optional demo data: run `http://localhost/smart-library/test_data_generator.php` or `php test_data_generator.php`.

## Roles & Capabilities
- Librarian
  - Add/edit/archive books; manage title, author, ISBN, category, price
  - View inventory with statuses (available, borrowed, reserved, archived)
  - Link to user management
- Staff
  - Process borrows/returns with limits and due dates
  - Manage penalties and payments
  - Clearance management
- Student/Teacher
  - View dashboards, reservations (if applicable), fines, and history

## Librarian Guide
- Add Book: Title & Author required; ISBN optional; Category/Price optional; duplicate ISBN prevented.
- Edit Book: Update any fields; duplicate ISBN checks apply.
- Archive/Unarchive: Archive removes from circulation without deleting; unarchive restores to available.
- Inventory Table: Title, Author, ISBN, Category, Price, Status with badges.

## Staff Guide
- Borrow: Search/select User and Book. Prevents borrow if not Available or Archived. Due dates: Students 14 days, Teachers 30 days. Students max 3 active borrows/semester.
- Return: From Active Borrows → Return; updates statuses and history.
- Penalties: View overdue, calculate penalties, record payments/waivers.
- Clearance: Resolve outstanding items to clear users for the semester.

## Student/Teacher Guide
- Dashboards: View active borrows, due dates, fines.
- Reservations: Reserve available books; reservations auto-cancel if staff borrows the book.

## Search
- Users: name, student ID, email.
- Books: title, author, ISBN, category.
- Real-time dropdowns assist selection on staff dashboard.

## Penalties (Defaults)
- Students: ₱10/day, max ₱500, 3-day grace.
- Teachers: ₱5/day, max ₱250, 5-day grace.

## Data Notes
- Book status: available, borrowed, reserved, archived. Archiving preserves history and hides from circulation.

## Troubleshooting
- Cannot borrow: ensure status is Available (not Borrowed/Reserved/Archived).
- Duplicate ISBN: use a unique ISBN or leave blank.
- Test generator foreign key error: re-import `schema_v3.sql`, re-run generator.
- Deadlock on generator: re-run; script clears with FK checks off, then inserts.

## Locations
- Entry/Login: `index.php` → redirects to role dashboards or `landing.php`
- Librarian: `librarian/dashboard.php`
- Staff: `staff/dashboard.php`, `staff/penalty_management.php`, `staff/clearance_management.php`
- Student/Teacher: `student/`, `teacher/`
- Config/DB: `config/`, `database/schema_v3.sql`

## Tips
- Prefer Archive over Delete for books.
- Keep `test_data_generator.php` for demos; exclude in production.
- After updates, (re)import the latest schema if needed.

---
For deeper details, see `README_COMPLETE.md` and `database/ERD_DOCUMENTATION.md`.
