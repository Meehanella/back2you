CREATE DATABASE IF NOT EXISTS back2you_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE back2you_db;

CREATE TABLE accounts (
 id INT AUTO_INCREMENT PRIMARY KEY,
 full_name VARCHAR(150) NOT NULL,
 username VARCHAR(60) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 course_program VARCHAR(120) DEFAULT NULL,
 student_id VARCHAR(80) DEFAULT NULL,
 role ENUM('user','admin','superadmin') NOT NULL DEFAULT 'user',
 account_status ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
 must_change_password TINYINT(1) NOT NULL DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 last_login_at DATETIME DEFAULT NULL,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE lost_items (
 id INT AUTO_INCREMENT PRIMARY KEY,
 item_name VARCHAR(150) NOT NULL,
 category VARCHAR(80) NOT NULL,
 description TEXT,
 color VARCHAR(60),
 date_lost DATE,
 quantity INT NOT NULL DEFAULT 1,
 has_items_inside TINYINT(1) NOT NULL DEFAULT 0,
 image_path VARCHAR(255),
 status ENUM('Lost','Not Found','Recovered','Found','Cancelled') NOT NULL DEFAULT 'Lost',
 location_last_seen VARCHAR(180),
 location_found VARCHAR(180) DEFAULT NULL,
 date_received DATETIME DEFAULT NULL,
 posted_by INT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_status_created(status,created_at),
 INDEX idx_item_name(item_name),
 CONSTRAINT fk_lost_poster FOREIGN KEY(posted_by) REFERENCES accounts(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE found_items (
 id INT AUTO_INCREMENT PRIMARY KEY,
 related_lost_item_id INT NULL,
 item_name VARCHAR(150) NOT NULL,
 category VARCHAR(80),
 description TEXT,
 color VARCHAR(60),
 quantity INT NOT NULL DEFAULT 1,
 has_items_inside TINYINT(1) NOT NULL DEFAULT 0,
 image_path VARCHAR(255),
 location_found VARCHAR(180) NOT NULL,
 date_found DATE NOT NULL,
 recorded_by INT NULL,
 created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_found_lost FOREIGN KEY(related_lost_item_id) REFERENCES lost_items(id) ON DELETE SET NULL,
 CONSTRAINT fk_found_recorder FOREIGN KEY(recorded_by) REFERENCES accounts(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE recovered_items (
 id INT AUTO_INCREMENT PRIMARY KEY,
 related_lost_item_id INT NULL UNIQUE,
 related_found_item_id INT NULL,
 item_name VARCHAR(150) NOT NULL,
 image_path VARCHAR(255),
 location_found VARCHAR(180),
 date_found DATE,
 recorded_by INT NULL,
 intended_owner_id INT NULL,
 recovery_status ENUM('Awaiting Claim','Claimed') NOT NULL DEFAULT 'Awaiting Claim',
 claim_date DATETIME NULL,
 created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_rec_lost FOREIGN KEY(related_lost_item_id) REFERENCES lost_items(id) ON DELETE SET NULL,
 CONSTRAINT fk_rec_found FOREIGN KEY(related_found_item_id) REFERENCES found_items(id) ON DELETE SET NULL,
 CONSTRAINT fk_rec_recorder FOREIGN KEY(recorded_by) REFERENCES accounts(id) ON DELETE SET NULL,
 CONSTRAINT fk_rec_owner FOREIGN KEY(intended_owner_id) REFERENCES accounts(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE expired_items (
 id INT AUTO_INCREMENT PRIMARY KEY,
 original_item_id INT NOT NULL UNIQUE,
 item_name VARCHAR(150) NOT NULL,
 category VARCHAR(80),
 color VARCHAR(60),
 date_lost DATE,
 quantity INT,
 has_items_inside TINYINT(1),
 image_path VARCHAR(255),
 original_poster_id INT NULL,
 original_created_at DATETIME NOT NULL,
 expired_at DATETIME NOT NULL,
 final_status VARCHAR(30) NOT NULL,
 CONSTRAINT fk_exp_poster FOREIGN KEY(original_poster_id) REFERENCES accounts(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE notifications (
 id INT AUTO_INCREMENT PRIMARY KEY,
 sender_id INT NULL,
 recipient_id INT NOT NULL,
 item_id INT NULL,
 notification_type ENUM('Possible matching item','Item recovered and ready for claiming','Please visit the Lost and Found Office','Item status updated','Item post cancelled or expired','General') NOT NULL DEFAULT 'General',
 message TEXT NOT NULL,
 is_read TINYINT(1) NOT NULL DEFAULT 0,
 created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
 read_at DATETIME NULL,
 INDEX idx_recipient_read(recipient_id,is_read),
 CONSTRAINT fk_not_sender FOREIGN KEY(sender_id) REFERENCES accounts(id) ON DELETE SET NULL,
 CONSTRAINT fk_not_recipient FOREIGN KEY(recipient_id) REFERENCES accounts(id) ON DELETE CASCADE,
 CONSTRAINT fk_not_item FOREIGN KEY(item_id) REFERENCES lost_items(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE item_history (
 id INT AUTO_INCREMENT PRIMARY KEY,
 item_id INT NOT NULL,
 changed_by INT NULL,
 old_status VARCHAR(30),
 new_status VARCHAR(30),
 action_description TEXT,
 created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_history_item(item_id),
 CONSTRAINT fk_hist_item FOREIGN KEY(item_id) REFERENCES lost_items(id) ON DELETE CASCADE,
 CONSTRAINT fk_hist_actor FOREIGN KEY(changed_by) REFERENCES accounts(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
 id INT AUTO_INCREMENT PRIMARY KEY,
 actor_id INT NULL,
 action VARCHAR(100) NOT NULL,
 target_type VARCHAR(80),
 target_id INT NULL,
 details TEXT,
 created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_audit_created(created_at),
 CONSTRAINT fk_audit_actor FOREIGN KEY(actor_id) REFERENCES accounts(id) ON DELETE SET NULL
) ENGINE=InnoDB;
