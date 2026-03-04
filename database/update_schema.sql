-- Create table for application documents
CREATE TABLE IF NOT EXISTS application_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
);

-- Create table for proposals (AI analyzed data + final proposal)
CREATE TABLE IF NOT EXISTS proposals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    consultant_id INT, -- stored if a consultant manually edits it, otherwise NULL for AI auto-gen initially
    ai_analysis_json TEXT, -- detailed AI breakdown
    proposal_text TEXT, -- the final text to be sent to bank
    status ENUM('draft', 'reviewed', 'sent_to_bank', 'approved', 'rejected') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    FOREIGN KEY (consultant_id) REFERENCES consultant_profiles(consultant_id)
);

-- Create table for services (for the Service Catalogue)
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    icon_class VARCHAR(50) DEFAULT 'fas fa-briefcase', -- for fontawesome icons
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add some initial services if table is empty
INSERT INTO services (title, description, icon_class)
SELECT * FROM (SELECT 'SME Business Loan', 'Flexible financing for small and medium enterprises.', 'fas fa-store') AS tmp
WHERE NOT EXISTS (
    SELECT title FROM services WHERE title = 'SME Business Loan'
) LIMIT 1;

INSERT INTO services (title, description, icon_class)
SELECT * FROM (SELECT 'Green Technology', 'Invest in sustainable and eco-friendly solutions.', 'fas fa-leaf') AS tmp
WHERE NOT EXISTS (
    SELECT title FROM services WHERE title = 'Green Technology'
) LIMIT 1;
