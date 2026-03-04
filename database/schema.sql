-- ============================================================
-- Kenes Platform — Full Database Schema
-- Database: kenes
-- ============================================================

CREATE DATABASE IF NOT EXISTS `kenes` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `kenes`;

-- Customers
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    city_region VARCHAR(100) DEFAULT NULL,
    business_name VARCHAR(150) DEFAULT NULL,
    business_type VARCHAR(50) DEFAULT NULL,
    iin_bin VARCHAR(12) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Consultants
CREATE TABLE IF NOT EXISTS consultants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    employee_id VARCHAR(30) NOT NULL UNIQUE,
    department VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Services catalog
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    interest_rate DECIMAL(5,2) DEFAULT NULL,
    max_amount DECIMAL(15,2) DEFAULT NULL,
    duration VARCHAR(50) DEFAULT NULL,
    loan_type VARCHAR(50) DEFAULT 'general',
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Loan applications
CREATE TABLE IF NOT EXISTS applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    service_id INT DEFAULT NULL,
    service_type VARCHAR(50) DEFAULT NULL,
    amount DECIMAL(15,2) NOT NULL,
    purpose TEXT,
    notes TEXT,
    status ENUM('submitted','pending','processing','under_review','analyzed','sent_to_bank','approved','rejected','bank_approved','bank_rejected') DEFAULT 'submitted',
    consultant_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL,
    FOREIGN KEY (consultant_id) REFERENCES consultants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Application documents
CREATE TABLE IF NOT EXISTS application_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    document_type VARCHAR(50) DEFAULT 'other',
    file_type VARCHAR(50) DEFAULT NULL,
    file_size INT DEFAULT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- AI proposals
CREATE TABLE IF NOT EXISTS proposals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    consultant_id INT DEFAULT NULL,
    scoring_points DECIMAL(5,2) DEFAULT NULL,
    risk_level VARCHAR(20) DEFAULT NULL,
    recommended_amount DECIMAL(15,2) DEFAULT NULL,
    ai_summary TEXT,
    consultant_notes TEXT,
    proposal_text TEXT,
    status ENUM('draft','reviewed','sent_to_bank','approved','rejected') DEFAULT 'draft',
    word_file_path VARCHAR(500) DEFAULT NULL,
    pptx_file_path VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    FOREIGN KEY (consultant_id) REFERENCES consultants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Activity log
CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_type ENUM('customer','consultant') NOT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Seed data — initial services
-- ============================================================
INSERT INTO services (name, description, interest_rate, max_amount, duration, loan_type) VALUES
('SME Working Capital', 'Short-term financing to bridge the gap between payables and receivables. Ideal for inventory smoothing.', 14.00, 50000000.00, '3 - 12 Months', 'working_capital'),
('Equipment Leasing', 'Acquire the machinery or technology your business needs without draining your cash flow.', 12.00, 100000000.00, '1 - 5 Years', 'leasing'),
('Microcredit', 'Small-scale funding for startups and individual entrepreneurs looking to launch.', 18.00, 10000000.00, '6 - 24 Months', 'microcredit'),
('Investment Loan', 'Long-term capital for business expansion, new branches, or large-scale projects.', 11.50, 500000000.00, '1 - 7 Years', 'investment'),
('Green Technology', 'Invest in sustainable and eco-friendly solutions with preferential Damu rates.', 10.00, 200000000.00, '2 - 10 Years', 'green'),
('Risk Analysis Report', 'A comprehensive financial health check using our proprietary AI model. Get actionable insights.', NULL, NULL, 'Instant Generation', 'analysis')
ON DUPLICATE KEY UPDATE name = VALUES(name);
