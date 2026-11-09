CREATE DATABASE IF NOT EXISTS primelend_zambia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE primelend_zambia;

CREATE TABLE IF NOT EXISTS users (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 role ENUM('admin','customer') NOT NULL DEFAULT 'customer',
 first_name VARCHAR(100) NOT NULL,
 last_name VARCHAR(100) NOT NULL,
 username VARCHAR(100) NULL UNIQUE,
 email VARCHAR(150) NULL UNIQUE,
 phone VARCHAR(30) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 national_id VARCHAR(50) NULL UNIQUE,
 date_of_birth DATE NULL,
 address VARCHAR(255) NULL,
 city VARCHAR(100) NULL,
 province VARCHAR(100) NULL,
 employment_status ENUM('employed','self_employed','unemployed','student','other') NULL,
 employer_name VARCHAR(150) NULL,
 monthly_income DECIMAL(12,2) NULL DEFAULT 0.00,
 profile_photo VARCHAR(255) NULL,
 status ENUM('active','suspended','blocked') NOT NULL DEFAULT 'active',
 last_login_at DATETIME NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_users_role(role), INDEX idx_users_status(status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS loan_products (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(120) NOT NULL,
 description TEXT NULL,
 min_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
 max_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
 interest_rate DECIMAL(6,2) NOT NULL DEFAULT 0.00,
 interest_type ENUM('flat','reducing_balance') NOT NULL DEFAULT 'flat',
 term_min_months INT UNSIGNED NOT NULL DEFAULT 1,
 term_max_months INT UNSIGNED NOT NULL DEFAULT 1,
 processing_fee DECIMAL(12,2) NOT NULL DEFAULT 0.00,
 late_fee DECIMAL(12,2) NOT NULL DEFAULT 0.00,
 status ENUM('active','inactive') NOT NULL DEFAULT 'active',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_products_status(status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS loan_applications (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 application_no VARCHAR(40) NOT NULL UNIQUE,
 customer_id INT UNSIGNED NOT NULL,
 loan_product_id INT UNSIGNED NOT NULL,
 requested_amount DECIMAL(12,2) NOT NULL,
 requested_term_months INT UNSIGNED NOT NULL,
 purpose VARCHAR(255) NOT NULL,
 monthly_income DECIMAL(12,2) NOT NULL DEFAULT 0.00,
 employment_details TEXT NULL,
 requested_date DATE NOT NULL,
 status ENUM('pending','under_review','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
 admin_notes TEXT NULL,
 reviewed_by INT UNSIGNED NULL,
 reviewed_at DATETIME NULL,
 rejection_reason TEXT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_app_customer(customer_id), INDEX idx_app_status(status), INDEX idx_app_date(requested_date),
 CONSTRAINT fk_app_customer FOREIGN KEY(customer_id) REFERENCES users(id) ON DELETE CASCADE,
 CONSTRAINT fk_app_product FOREIGN KEY(loan_product_id) REFERENCES loan_products(id) ON DELETE RESTRICT,
 CONSTRAINT fk_app_reviewer FOREIGN KEY(reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS loans (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 loan_no VARCHAR(40) NOT NULL UNIQUE,
 application_id INT UNSIGNED NOT NULL UNIQUE,
 customer_id INT UNSIGNED NOT NULL,
 loan_product_id INT UNSIGNED NOT NULL,
 principal_amount DECIMAL(12,2) NOT NULL,
 interest_rate DECIMAL(6,2) NOT NULL,
 interest_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
 processing_fee DECIMAL(12,2) NOT NULL DEFAULT 0.00,
 total_payable DECIMAL(12,2) NOT NULL DEFAULT 0.00,
 amount_paid DECIMAL(12,2) NOT NULL DEFAULT 0.00,
 outstanding_balance DECIMAL(12,2) NOT NULL DEFAULT 0.00,
 term_months INT UNSIGNED NOT NULL,
 installment_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
 disbursement_date DATE NULL,
 first_payment_date DATE NULL,
 maturity_date DATE NULL,
 status ENUM('approved','active','completed','overdue','defaulted','cancelled') NOT NULL DEFAULT 'approved',
 approved_by INT UNSIGNED NULL,
 approved_at DATETIME NULL,
 notes TEXT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_loans_customer(customer_id), INDEX idx_loans_status(status), INDEX idx_loans_maturity(maturity_date),
 CONSTRAINT fk_loan_application FOREIGN KEY(application_id) REFERENCES loan_applications(id) ON DELETE RESTRICT,
 CONSTRAINT fk_loan_customer FOREIGN KEY(customer_id) REFERENCES users(id) ON DELETE CASCADE,
 CONSTRAINT fk_loan_product FOREIGN KEY(loan_product_id) REFERENCES loan_products(id) ON DELETE RESTRICT,
 CONSTRAINT fk_loan_approver FOREIGN KEY(approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS repayment_schedule (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 loan_id INT UNSIGNED NOT NULL,
 installment_no INT UNSIGNED NOT NULL,
 due_date DATE NOT NULL,
 amount_due DECIMAL(12,2) NOT NULL DEFAULT 0.00,
 amount_paid DECIMAL(12,2) NOT NULL DEFAULT 0.00,
 status ENUM('pending','partially_paid','paid','overdue') NOT NULL DEFAULT 'pending',
 paid_at DATETIME NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY unique_installment(loan_id,installment_no), INDEX idx_schedule_due(due_date), INDEX idx_schedule_status(status),
 CONSTRAINT fk_schedule_loan FOREIGN KEY(loan_id) REFERENCES loans(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS repayments (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 repayment_no VARCHAR(40) NOT NULL UNIQUE,
 loan_id INT UNSIGNED NOT NULL,
 customer_id INT UNSIGNED NOT NULL,
 schedule_id INT UNSIGNED NULL,
 amount DECIMAL(12,2) NOT NULL,
 payment_method ENUM('cash','bank_transfer','mobile_money','card','other') NOT NULL DEFAULT 'cash',
 transaction_reference VARCHAR(100) NULL,
 payment_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 received_by INT UNSIGNED NULL,
 notes TEXT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_repay_loan(loan_id), INDEX idx_repay_customer(customer_id), INDEX idx_repay_date(payment_date),
 CONSTRAINT fk_repay_loan FOREIGN KEY(loan_id) REFERENCES loans(id) ON DELETE CASCADE,
 CONSTRAINT fk_repay_customer FOREIGN KEY(customer_id) REFERENCES users(id) ON DELETE CASCADE,
 CONSTRAINT fk_repay_schedule FOREIGN KEY(schedule_id) REFERENCES repayment_schedule(id) ON DELETE SET NULL,
 CONSTRAINT fk_repay_receiver FOREIGN KEY(received_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS receipts (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 receipt_no VARCHAR(40) NOT NULL UNIQUE,
 repayment_id INT UNSIGNED NOT NULL UNIQUE,
 customer_id INT UNSIGNED NOT NULL,
 amount DECIMAL(12,2) NOT NULL,
 issued_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 issued_by INT UNSIGNED NULL,
 receipt_file VARCHAR(255) NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_receipt_customer(customer_id),
 CONSTRAINT fk_receipt_repayment FOREIGN KEY(repayment_id) REFERENCES repayments(id) ON DELETE CASCADE,
 CONSTRAINT fk_receipt_customer FOREIGN KEY(customer_id) REFERENCES users(id) ON DELETE CASCADE,
 CONSTRAINT fk_receipt_issuer FOREIGN KEY(issued_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS notifications (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED NOT NULL,
 title VARCHAR(150) NOT NULL,
 message TEXT NOT NULL,
 type ENUM('info','success','warning','danger') NOT NULL DEFAULT 'info',
 related_type VARCHAR(50) NULL,
 related_id INT UNSIGNED NULL,
 is_read TINYINT(1) NOT NULL DEFAULT 0,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_notifications_user(user_id,is_read),
 CONSTRAINT fk_notification_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS password_resets (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED NOT NULL,
 token_hash VARCHAR(255) NOT NULL,
 expires_at DATETIME NOT NULL,
 used_at DATETIME NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_reset_user(user_id),
 CONSTRAINT fk_reset_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS audit_logs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED NULL,
 action VARCHAR(100) NOT NULL,
 entity_type VARCHAR(60) NULL,
 entity_id INT UNSIGNED NULL,
 description TEXT NULL,
 ip_address VARCHAR(45) NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_audit_user(user_id), INDEX idx_audit_action(action), INDEX idx_audit_date(created_at),
 CONSTRAINT fk_audit_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS settings (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 setting_key VARCHAR(100) NOT NULL UNIQUE,
 setting_value TEXT NULL,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO loan_products(name,description,min_amount,max_amount,interest_rate,interest_type,term_min_months,term_max_months,processing_fee,late_fee)
SELECT 'Personal Loan','Short-term personal financing for eligible customers.',500,10000,30,'flat',1,12,0,50
WHERE NOT EXISTS (SELECT 1 FROM loan_products WHERE name='Personal Loan');
INSERT INTO loan_products(name,description,min_amount,max_amount,interest_rate,interest_type,term_min_months,term_max_months,processing_fee,late_fee)
SELECT 'Business Loan','Financing for small business working capital and approved business needs.',1000,25000,30,'flat',1,18,0,75
WHERE NOT EXISTS (SELECT 1 FROM loan_products WHERE name='Business Loan');
INSERT INTO settings(setting_key,setting_value) SELECT 'company_name','PrimeLend Zambia' WHERE NOT EXISTS (SELECT 1 FROM settings WHERE setting_key='company_name');
INSERT INTO settings(setting_key,setting_value) SELECT 'currency','ZMW' WHERE NOT EXISTS (SELECT 1 FROM settings WHERE setting_key='currency');
INSERT INTO settings(setting_key,setting_value) SELECT 'tagline','Simple. Fast. Trusted.' WHERE NOT EXISTS (SELECT 1 FROM settings WHERE setting_key='tagline');
-- Create the first admin securely in the application with password_hash().
