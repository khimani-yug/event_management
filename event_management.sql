CREATE DATABASE IF NOT EXISTS event_management;
USE event_management;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  email VARCHAR(255) NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','teacher','student') NOT NULL
);

CREATE TABLE event_types (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type_name VARCHAR(100) NOT NULL,
  image VARCHAR(255) NOT NULL
);

CREATE TABLE events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  teacher_id INT NOT NULL,
  event_type_id INT NOT NULL,
  event_name VARCHAR(255) NOT NULL,
  event_date DATE NOT NULL,
  details TEXT,
  participant_limit INT DEFAULT 0,
  FOREIGN KEY (teacher_id) REFERENCES users(id),
  FOREIGN KEY (event_type_id) REFERENCES event_types(id)
);

CREATE TABLE registrations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  event_id INT NOT NULL,
  FOREIGN KEY (student_id) REFERENCES users(id),
  FOREIGN KEY (event_id) REFERENCES events(id)
);

-- admin user(username: admin, password: admin123)
INSERT INTO users (username, password, role) VALUES (
  'admin', '$2y$10$vzUu8R0LiQkPJ1rV7/AAtuZVAhEEPmMYAHzUOPr7chkoMJPx2lXIy', 'admin'
);
