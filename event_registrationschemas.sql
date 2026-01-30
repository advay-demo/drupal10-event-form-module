CREATE TABLE event_registration_event (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_name VARCHAR(255) NOT NULL,
  category VARCHAR(120) NOT NULL,
  event_date INT NOT NULL,
  reg_start_date INT NOT NULL,
  reg_end_date INT NOT NULL,
  created INT NOT NULL
);

CREATE TABLE event_registration_entry (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT NOT NULL,
  full_name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  college VARCHAR(255) NOT NULL,
  department VARCHAR(255) NOT NULL,
  created INT NOT NULL
);
