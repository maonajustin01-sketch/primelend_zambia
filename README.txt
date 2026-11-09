PRIMELEND ZAMBIA - PHASE 1 FOUNDATION

Local folder: C:\xampp\htdocs\primelend_zambia
URL: http://localhost/primelend_zambia/
Database: primelend_zambia
SQL: database.sql

Stack: PHP, MySQL/MariaDB, HTML, CSS, JavaScript, Bootstrap-ready, XAMPP.

Database modules:
users, loan_products, loan_applications, loans, repayment_schedule,
repayments, receipts, notifications, password_resets, audit_logs, settings.

Roles: admin, customer.
Currency: ZMW (Zambian Kwacha).
Brand: PrimeLend Zambia.
Tagline: Simple. Fast. Trusted.

INSTALL:
1. Start Apache and MySQL in XAMPP.
2. Copy this folder to C:\xampp\htdocs\
3. Import database.sql in phpMyAdmin.
4. Open http://localhost/primelend_zambia/
5. The first admin account will be created securely by the application in the authentication phase.

Do not store plain-text passwords in SQL.

AUTHENTICATION PHASE
- Customer registration creates role=customer accounts only.
- Passwords use password_hash() and login uses password_verify().
- Login accepts email, phone or username.
- Sessions are regenerated after successful login.
- CSRF tokens protect authentication/setup forms.
- Inactive/suspended/blocked accounts cannot log in.
- First administrator is created only through auth/admin_setup.php.
- Before first admin setup, change ADMIN_SETUP_KEY in config/app.php.
- The setup page automatically disables itself once an admin exists.
- Never reuse the development setup key in production.

FIRST ADMIN SETUP
1. Import database.sql into phpMyAdmin.
2. Open config/app.php.
3. Change ADMIN_SETUP_KEY to your own private setup key.
4. Open http://localhost/primelend_zambia/auth/admin_setup.php
5. Enter the setup key and create the administrator with a strong password.
6. Log in at http://localhost/primelend_zambia/auth/login.php
7. The admin setup page will then be disabled automatically.

LOAN APPLICATION WORKFLOW (PHASE 3)
1. Customer logs in.
2. Customer opens Customer Dashboard > Apply for a Loan.
3. Customer selects an active loan product, amount, term, income, purpose and employment details.
4. Server validates product limits and prevents duplicate applications under review.
5. Application receives a unique PLA-... number and status pending.
6. Admin opens Admin > Applications > Review.
7. Admin can mark under review, reject, or approve.
8. Approval calculates flat interest, total payable and installment, creates a loan and repayment schedule in one database transaction.
9. Customer can view applications and approved loans from the customer portal.
