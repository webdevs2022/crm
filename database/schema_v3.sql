-- ============================================================
-- Enterprise CRM — UPGRADED FULL SCHEMA v3
-- Includes: Auth, RBAC, Faculty Master, Auto Course Number,
--           Enhanced Topics, Material Tracking, Tasks, Progress
-- Run: mysql -u root -p crm_db < schema_v3.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS crm_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE crm_db;

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. USERS (upgraded with roles matching doc)
-- ============================================================
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    role        ENUM('owner','admin','accounts','coordinator') NOT NULL DEFAULT 'coordinator',
    phone       VARCHAR(30),
    status      ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- 2. FACULTY MASTER (new dedicated table per doc)
-- ============================================================
DROP TABLE IF EXISTS faculty;
CREATE TABLE faculty (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    mobile      VARCHAR(30),
    email       VARCHAR(150),
    city        VARCHAR(80),
    state       VARCHAR(80),
    country     VARCHAR(80) DEFAULT 'India',
    designation VARCHAR(100),
    status      ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_by  INT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================================
-- 3. COURSES (upgraded: auto course number, coordinator link)
-- ============================================================
DROP TABLE IF EXISTS courses;
CREATE TABLE courses (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    course_number   VARCHAR(20) NOT NULL UNIQUE,   -- CRS-YYYY-XXX
    title           VARCHAR(200) NOT NULL,
    description     TEXT,
    category        VARCHAR(100),
    level           ENUM('beginner','intermediate','advanced') NOT NULL DEFAULT 'beginner',
    status          ENUM('draft','active','completed','archived') NOT NULL DEFAULT 'draft',
    coordinator_id  INT,                            -- assigned coordinator (user)
    created_by      INT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (coordinator_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by)     REFERENCES users(id) ON DELETE SET NULL
);

-- Auto-increment course number helper table
DROP TABLE IF EXISTS course_number_seq;
CREATE TABLE course_number_seq (
    year    YEAR    NOT NULL,
    seq     INT     NOT NULL DEFAULT 0,
    PRIMARY KEY (year)
);

-- ============================================================
-- 4. TOPICS (upgraded: lecture types + workflow + reschedule)
-- ============================================================
DROP TABLE IF EXISTS topics;
CREATE TABLE topics (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    course_id       INT NOT NULL,
    title           VARCHAR(200) NOT NULL,
    description     TEXT,
    lecture_type    ENUM('recorded','live','not_decided') NOT NULL DEFAULT 'not_decided',
    sort_order      INT DEFAULT 0,
    duration_minutes INT DEFAULT 0,
    faculty_id      INT,                            -- FK to faculty master
    status          ENUM('pending','in_progress','completed','cancelled','rescheduled') NOT NULL DEFAULT 'pending',
    scheduled_at    DATETIME,
    meeting_link    VARCHAR(500),
    notes           TEXT,
    -- Reschedule fields
    reschedule_reason VARCHAR(500),
    rescheduled_to    DATETIME,
    rescheduled_at    TIMESTAMP NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id)  REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (faculty_id) REFERENCES faculty(id) ON DELETE SET NULL
);

-- ============================================================
-- 5. WORKFLOW STEPS (enhanced per doc checklist)
-- ============================================================
DROP TABLE IF EXISTS workflow_steps;
CREATE TABLE workflow_steps (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    topic_id     INT NOT NULL,
    step_key     VARCHAR(80) NOT NULL,
    step_label   VARCHAR(150) NOT NULL,
    step_order   INT NOT NULL DEFAULT 0,
    is_completed TINYINT(1) NOT NULL DEFAULT 0,
    completed_by INT,
    completed_at DATETIME,
    notes        TEXT,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (topic_id)     REFERENCES topics(id) ON DELETE CASCADE,
    FOREIGN KEY (completed_by) REFERENCES users(id)  ON DELETE SET NULL,
    UNIQUE KEY uq_topic_step (topic_id, step_key)
);

-- ============================================================
-- 6. MATERIALS (new fixed types + count tracking per doc)
-- ============================================================
DROP TABLE IF EXISTS materials;
CREATE TABLE materials (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    course_id      INT NOT NULL,
    topic_id       INT,                             -- optional
    material_type  ENUM(
        'osce',
        'mcq',
        'true_false',
        'dicom_long_case',
        'dicom_short_case',
        'spotters'
    ) NOT NULL,
    received_count INT NOT NULL DEFAULT 0,
    uploaded_count INT NOT NULL DEFAULT 0,
    -- pending_count is COMPUTED: received - uploaded
    status         ENUM('pending','partial','complete') NOT NULL DEFAULT 'pending',
    notes          TEXT,
    uploaded_by    INT,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id)   REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (topic_id)    REFERENCES topics(id)  ON DELETE SET NULL,
    FOREIGN KEY (uploaded_by) REFERENCES users(id)   ON DELETE SET NULL
);

-- ============================================================
-- 7. CONTRACTS (updated FK to faculty master)
-- ============================================================
DROP TABLE IF EXISTS contracts;
CREATE TABLE contracts (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    faculty_id      INT NOT NULL,                   -- FK to faculty master
    course_id       INT,
    contract_number VARCHAR(80) NOT NULL UNIQUE,
    title           VARCHAR(200) NOT NULL,
    start_date      DATE NOT NULL,
    end_date        DATE,
    total_amount    DECIMAL(12,2) NOT NULL DEFAULT 0,
    currency        VARCHAR(10) NOT NULL DEFAULT 'INR',
    status          ENUM('draft','sent','signed','active','completed','cancelled') NOT NULL DEFAULT 'draft',
    terms           TEXT,
    signed_at       DATETIME,
    notes           TEXT,
    created_by      INT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (faculty_id) REFERENCES faculty(id)  ON DELETE CASCADE,
    FOREIGN KEY (course_id)  REFERENCES courses(id)  ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id)    ON DELETE SET NULL
);

-- ============================================================
-- 8. PAYMENTS (FK to faculty master)
-- ============================================================
DROP TABLE IF EXISTS payments;
CREATE TABLE payments (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    contract_id    INT,
    faculty_id     INT NOT NULL,
    invoice_number VARCHAR(80) NOT NULL UNIQUE,
    amount         DECIMAL(12,2) NOT NULL,
    currency       VARCHAR(10) NOT NULL DEFAULT 'INR',
    payment_type   ENUM('advance','milestone','final','bonus') NOT NULL DEFAULT 'milestone',
    status         ENUM('pending','processing','paid','failed','refunded') NOT NULL DEFAULT 'pending',
    due_date       DATE,
    paid_date      DATE,
    payment_method VARCHAR(60),
    transaction_id VARCHAR(120),
    notes          TEXT,
    created_by     INT,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (contract_id) REFERENCES contracts(id) ON DELETE SET NULL,
    FOREIGN KEY (faculty_id)  REFERENCES faculty(id)   ON DELETE CASCADE,
    FOREIGN KEY (created_by)  REFERENCES users(id)     ON DELETE SET NULL
);

-- ============================================================
-- 9. TASKS (new module per doc)
-- ============================================================
DROP TABLE IF EXISTS tasks;
CREATE TABLE tasks (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    title          VARCHAR(200) NOT NULL,
    description    TEXT,
    assigned_to    INT,                             -- user
    assigned_by    INT,
    related_module ENUM('course','topic','material','contract','payment','general') DEFAULT 'general',
    related_id     INT,                             -- ID of related record
    priority       ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
    due_date       DATE,
    status         ENUM('open','in_progress','completed','cancelled') NOT NULL DEFAULT 'open',
    completed_at   DATETIME,
    notes          TEXT,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================================
-- 10. SESSIONS (PHP session store — optional, for DB sessions)
-- ============================================================
DROP TABLE IF EXISTS user_sessions;
CREATE TABLE user_sessions (
    id         VARCHAR(128) PRIMARY KEY,
    user_id    INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent VARCHAR(300),
    payload    TEXT,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Users (owner + all roles; password = "password123")
INSERT INTO users (name, email, password, role, phone, status) VALUES
('Dr. Owner',         'owner@crm.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner',       '+91-90000-00001', 'active'),
('Admin User',        'admin@crm.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin',       '+91-90000-00002', 'active'),
('Accounts Manager',  'accounts@crm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'accounts',    '+91-90000-00003', 'active'),
('Sarah Coordinator', 'sarah@crm.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'coordinator', '+91-90000-00004', 'active'),
('John Coordinator',  'john@crm.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'coordinator', '+91-90000-00005', 'active');
-- Default password for all: password

-- Faculty Master
INSERT INTO faculty (name, mobile, email, city, state, country, designation, status, created_by) VALUES
('Dr. Priya Sharma',  '+91-98765-10001', 'priya@faculty.com',  'Mumbai',      'Maharashtra', 'India', 'Associate Professor',  'active', 1),
('Prof. Rahul Mehta', '+91-98765-10002', 'rahul@faculty.com',  'Delhi',       'Delhi',        'India', 'Professor',            'active', 1),
('Dr. Anita Desai',   '+91-98765-10003', 'anita@faculty.com',  'Ahmedabad',   'Gujarat',      'India', 'Senior Lecturer',      'active', 1),
('Dr. Vikram Nair',   '+91-98765-10004', 'vikram@faculty.com', 'Bengaluru',   'Karnataka',    'India', 'Assistant Professor',  'active', 1),
('Prof. Sunita Rao',  '+91-98765-10005', 'sunita@faculty.com', 'Hyderabad',   'Telangana',    'India', 'Professor',            'active', 2);

-- Course number seed for 2025
INSERT IGNORE INTO course_number_seq (year, seq) VALUES (2025, 5);

-- Courses (with auto-numbers and coordinator assignment)
INSERT INTO courses (course_number, title, description, category, level, status, coordinator_id, created_by) VALUES
('CRS-2025-001', 'Full Stack Web Development',  'Comprehensive full stack development covering HTML, CSS, JavaScript, PHP and MySQL.',       'Technology',   'beginner',     'active',    4, 1),
('CRS-2025-002', 'Data Science with Python',    'Data analysis, visualization and machine learning using Python, Pandas and Scikit-learn.', 'Data Science', 'intermediate', 'active',    4, 1),
('CRS-2025-003', 'Digital Marketing Mastery',   'Complete digital marketing: SEO, SEM, social media and content marketing strategy.',       'Marketing',    'beginner',     'active',    5, 2),
('CRS-2025-004', 'Advanced PHP & Laravel',      'PHP 8 and Laravel framework for enterprise-grade web applications.',                       'Technology',   'advanced',     'draft',     5, 2),
('CRS-2025-005', 'UI/UX Design Fundamentals',   'UX/UI design using Figma, design thinking and prototyping.',                              'Design',       'beginner',     'draft',     4, 1);

-- Topics (updated lecture types, FK to faculty master)
INSERT INTO topics (course_id, title, description, lecture_type, sort_order, duration_minutes, faculty_id, status, scheduled_at, meeting_link) VALUES
(1, 'HTML5 & Semantic Markup',     'Introduction to HTML5 elements, forms and accessibility.',       'recorded',    1, 90,  1, 'completed',   '2025-01-10 10:00:00', NULL),
(1, 'CSS3 & Flexbox Layout',       'CSS fundamentals, Flexbox, Grid and responsive design.',         'recorded',    2, 120, 1, 'completed',   '2025-01-17 10:00:00', NULL),
(1, 'JavaScript Essentials',       'Core JavaScript: DOM, events, ES6 features.',                   'live',        3, 90,  2, 'completed',   '2025-01-24 11:00:00', 'https://meet.google.com/abc-defg'),
(1, 'Bootstrap 5 Framework',       'Responsive UIs with Bootstrap 5 components.',                   'recorded',    4, 60,  1, 'in_progress', '2025-02-01 10:00:00', NULL),
(1, 'PHP Fundamentals',            'PHP 8 syntax, arrays, functions and OOP basics.',               'live',        5, 120, 2, 'pending',     '2025-02-10 11:00:00', 'https://zoom.us/j/12345'),
(1, 'MySQL Database Design',       'Relational DB design, SQL queries and joins.',                   'recorded',    6, 90,  3, 'pending',     '2025-02-17 10:00:00', NULL),
(1, 'RESTful API Development',     'Building REST APIs with PHP.',                                   'not_decided', 7, 0,   NULL, 'pending',  NULL, NULL),
(1, 'Final Project & Deployment',  'Capstone project and live deployment.',                          'not_decided', 8, 0,   NULL, 'pending',  NULL, NULL),
(2, 'Python for Data Science',     'Python basics, NumPy, scientific computing.',                   'recorded',    1, 90,  3, 'completed',   '2025-01-08 09:00:00', NULL),
(2, 'Pandas & Data Wrangling',     'Data manipulation with Pandas.',                                'recorded',    2, 120, 3, 'completed',   '2025-01-15 09:00:00', NULL),
(2, 'Data Visualization',          'Charts with Matplotlib, Seaborn and Plotly.',                   'live',        3, 90,  3, 'in_progress', '2025-01-22 09:00:00', NULL),
(2, 'Statistics & Probability',    'Descriptive stats, distributions, hypothesis testing.',         'recorded',    4, 120, 1, 'pending',     '2025-01-29 09:00:00', NULL),
(2, 'Machine Learning Basics',     'Supervised learning with Scikit-learn.',                        'not_decided', 5, 0,   NULL, 'pending',  NULL, NULL),
(3, 'SEO Fundamentals',            'On/off-page SEO, keyword research.',                            'recorded',    1, 90,  2, 'completed',   '2025-01-06 14:00:00', NULL),
(3, 'Google Ads & SEM',            'Setting up and optimising Google Ads.',                         'live',        2, 120, 2, 'completed',   '2025-01-13 14:00:00', NULL),
(3, 'Social Media Marketing',      'Facebook, Instagram, LinkedIn, Twitter strategy.',              'recorded',    3, 90,  3, 'in_progress', '2025-01-20 14:00:00', NULL),
(3, 'Content Marketing Strategy',  'Content calendars, blogs, video scripts.',                      'not_decided', 4, 0,   NULL, 'pending',  NULL, NULL),
(3, 'Analytics & Reporting',       'GA4, UTM tracking, KPI dashboards.',                            'live',        5, 120, 2, 'pending',     '2025-02-03 14:00:00', NULL);

-- Workflow steps (doc-defined checklists)
-- Recorded workflow for topic 1 (completed)
INSERT INTO workflow_steps (topic_id, step_key, step_label, step_order, is_completed, completed_at) VALUES
(1, 'email_sent',         'Email Sent',             1, 1, '2024-12-20 09:00:00'),
(1, 'meeting_link_shared','Meeting Link Shared',    2, 1, '2024-12-21 10:00:00'),
(1, 'recording_done',     'Recording Done',         3, 1, '2025-01-10 12:00:00'),
(1, 'editing_done',       'Editing Done',           4, 1, '2025-01-12 15:00:00'),
(1, 'uploaded',           'Uploaded',               5, 1, '2025-01-13 10:00:00');

-- Recorded workflow for topic 4 (in progress)
INSERT INTO workflow_steps (topic_id, step_key, step_label, step_order, is_completed, completed_at) VALUES
(4, 'email_sent',         'Email Sent',             1, 1, '2025-01-22 09:00:00'),
(4, 'meeting_link_shared','Meeting Link Shared',    2, 1, '2025-01-23 11:00:00'),
(4, 'recording_done',     'Recording Done',         3, 0, NULL),
(4, 'editing_done',       'Editing Done',           4, 0, NULL),
(4, 'uploaded',           'Uploaded',               5, 0, NULL);

-- Live workflow for topic 3 (completed)
INSERT INTO workflow_steps (topic_id, step_key, step_label, step_order, is_completed, completed_at) VALUES
(3, 'banner_created',      'Banner Created',          1, 1, '2025-01-20 09:00:00'),
(3, 'website_updated',     'Website Updated',         2, 1, '2025-01-21 10:00:00'),
(3, 'info_shared_faculty', 'Info Shared to Faculty',  3, 1, '2025-01-23 11:00:00');

-- Materials (new fixed types with counts)
INSERT INTO materials (course_id, topic_id, material_type, received_count, uploaded_count, status, uploaded_by) VALUES
(1, 1,    'mcq',             40, 40, 'complete', 2),
(1, 1,    'osce',            10, 10, 'complete', 2),
(1, 2,    'mcq',             35, 30, 'partial',  2),
(1, 3,    'true_false',      20, 20, 'complete', 2),
(1, NULL, 'spotters',        15, 10, 'partial',  2),
(2, NULL, 'mcq',             50, 45, 'partial',  2),
(2, 9,    'dicom_long_case',  8,  6, 'partial',  3),
(3, NULL, 'mcq',             60,  0, 'pending',  4),
(3, 15,   'osce',            12,  0, 'pending',  4),
(2, NULL, 'dicom_short_case',10,  8, 'partial',  3);

-- Contracts (FK to faculty master)
INSERT INTO contracts (faculty_id, course_id, contract_number, title, start_date, end_date, total_amount, currency, status, terms, signed_at, created_by) VALUES
(1, 1, 'CNT-2025-001', 'Full Stack Web Dev — Dr. Priya Sharma',  '2025-01-01', '2025-03-31', 45000, 'INR', 'active',    '8 recorded lectures at Rs 5000 each.',    '2025-01-02 10:00:00', 1),
(2, 1, 'CNT-2025-002', 'Full Stack Web Dev — Prof. Rahul Mehta', '2025-01-01', '2025-03-31', 30000, 'INR', 'active',    '5 live sessions at Rs 6000 each.',        '2025-01-03 11:00:00', 1),
(3, 2, 'CNT-2025-003', 'Data Science — Dr. Anita Desai',         '2025-01-05', '2025-02-28', 36000, 'INR', 'active',    '6 lectures at Rs 6000 each.',             '2025-01-06 09:00:00', 1),
(2, 3, 'CNT-2025-004', 'Digital Marketing — Prof. Rahul Mehta',  '2025-01-05', '2025-02-15', 18000, 'INR', 'completed', '3 sessions at Rs 6000 each.',             '2025-01-05 14:00:00', 2),
(1, NULL, 'CNT-2025-005', 'UI/UX Design — Dr. Priya Sharma',     '2025-02-01', '2025-04-30', 24000, 'INR', 'draft',     '4 recorded lectures.',                    NULL, 1);

-- Payments (FK to faculty master)
INSERT INTO payments (contract_id, faculty_id, invoice_number, amount, currency, payment_type, status, due_date, paid_date, payment_method, transaction_id, notes, created_by) VALUES
(1, 1, 'INV-2025-001', 15000, 'INR', 'advance',   'paid',       '2025-01-05', '2025-01-06', 'Bank Transfer', 'TXN-001-2025', 'Advance Phase 1',         1),
(1, 1, 'INV-2025-002', 15000, 'INR', 'milestone',  'paid',       '2025-02-01', '2025-02-03', 'Bank Transfer', 'TXN-002-2025', 'Milestone 2',             1),
(1, 1, 'INV-2025-003', 15000, 'INR', 'final',      'pending',    '2025-03-31', NULL,          NULL,            NULL,           'Final pending',           1),
(2, 2, 'INV-2025-004', 15000, 'INR', 'advance',    'paid',       '2025-01-05', '2025-01-07', 'UPI',           'TXN-003-2025', 'Advance live sessions',   1),
(2, 2, 'INV-2025-005', 15000, 'INR', 'final',      'pending',    '2025-03-31', NULL,          NULL,            NULL,           'Balance after sessions',  1),
(3, 3, 'INV-2025-006', 18000, 'INR', 'advance',    'paid',       '2025-01-10', '2025-01-11', 'Bank Transfer', 'TXN-004-2025', '50% advance DS',          1),
(3, 3, 'INV-2025-007', 18000, 'INR', 'final',      'processing', '2025-02-28', NULL,          'Bank Transfer', NULL,           'Final in process',        1),
(4, 2, 'INV-2025-008', 18000, 'INR', 'final',      'paid',       '2025-02-15', '2025-02-16', 'UPI',           'TXN-005-2025', 'Full payment done',       2);

-- Tasks (new module)
INSERT INTO tasks (title, description, assigned_to, assigned_by, related_module, related_id, priority, due_date, status) VALUES
('Upload Bootstrap 5 recording',   'Editing done, upload to platform asap.',  4, 1, 'topic',    4,  'high',   '2025-02-05', 'open'),
('Review Digital Marketing MCQs',  'Client submitted 60 MCQs for approval.',  5, 1, 'material', 8,  'medium', '2025-02-10', 'open'),
('Finalize Priya Sharma contract',  'Draft ready, get signature.',             3, 1, 'contract', 5,  'urgent', '2025-02-03', 'in_progress'),
('Coordinate PHP live session',     'Share Zoom link and notify faculty.',     4, 2, 'topic',    5,  'high',   '2025-02-09', 'open'),
('Send payment reminder INV-003',   'Final payment due 31 March.',             3, 1, 'payment',  3,  'low',    '2025-03-25', 'open'),
('Set up Data Science topics 5-6',  'Assign faculty and schedule.',            4, 1, 'course',   2,  'medium', '2025-02-15', 'open');
