CREATE DATABASE IF NOT EXISTS cyberfolio CHARACTER SET utf8mb4;
USE cyberfolio;
CREATE TABLE IF NOT EXISTS users(id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(50) UNIQUE NOT NULL, pass VARCHAR(255) NOT NULL);
-- One flexible table for every content type (project, lesson, and anything you add later)
CREATE TABLE IF NOT EXISTS entries(
 id INT AUTO_INCREMENT PRIMARY KEY, type VARCHAR(30) NOT NULL, title VARCHAR(160) NOT NULL,
 slug VARCHAR(180) UNIQUE NOT NULL, summary VARCHAR(300) DEFAULT '', category VARCHAR(60) NOT NULL DEFAULT '', body MEDIUMTEXT,
 image VARCHAR(120) DEFAULT '', tags VARCHAR(200) DEFAULT '', link VARCHAR(255) DEFAULT '',
 level VARCHAR(20) DEFAULT '', published TINYINT(1) DEFAULT 1, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 KEY(type,published)
);
CREATE TABLE IF NOT EXISTS files(id INT AUTO_INCREMENT PRIMARY KEY, entry_id INT NOT NULL, kind VARCHAR(10) NOT NULL, filename VARCHAR(120) NOT NULL, orig_name VARCHAR(160) DEFAULT '', size INT DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, KEY(entry_id,kind));
CREATE TABLE IF NOT EXISTS settings(k VARCHAR(40) PRIMARY KEY, v TEXT);
INSERT IGNORE INTO settings VALUES('name','Your Name'),('tagline','I design and build secure web products.'),('about','Edit this text in Admin > Settings.');
INSERT IGNORE INTO entries(type,title,slug,summary,body,tags,level) VALUES
('lesson','Password hashing 101','password-hashing-101','Why plain and MD5 passwords get cracked in minutes.','Use a slow, salted hash such as bcrypt or Argon2.\n\nIn PHP: password_hash() to store, password_verify() to check.\n\nNever use MD5 or SHA1 for passwords.','crypto,web','Beginner'),
('lesson','SQL injection basics','sql-injection-basics','How unsafe queries leak data, and the one-line fix.','SQL injection happens when user input is glued into a query string.\n\nFix: prepared statements (PDO) and input validation. Never build SQL with string concatenation.','owasp,web','Beginner');
CREATE TABLE IF NOT EXISTS skills(id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(60) UNIQUE NOT NULL, category VARCHAR(40) DEFAULT 'General', pct TINYINT DEFAULT 70);
CREATE TABLE IF NOT EXISTS messages(id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(80), email VARCHAR(120), body TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
INSERT IGNORE INTO skills(name,category,pct) VALUES('PHP','Development',80),('MySQL','Development',75),('UI/UX design','Design',85),('Network security','Cybersecurity',70);
INSERT IGNORE INTO settings VALUES('email','you@example.com'),('location','Your city, country'),('github',''),('linkedin','');
