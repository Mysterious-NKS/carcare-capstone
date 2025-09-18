-- schema.sql — single source of truth for tables.
-- philosophy: strict enough to protect data, flexible enough to move fast.

-- Always good manners:
SET NAMES utf8mb4;
SET time_zone = '+08:00';
SET FOREIGN_KEY_CHECKS = 1;

-- USERS: the keys to the kingdom. Keep it tidy.
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name       VARCHAR(120)  NOT NULL,
  email           VARCHAR(190)  NOT NULL UNIQUE,  -- unique or chaos ensues
  phone           VARCHAR(30)   DEFAULT NULL,
  password_hash   VARCHAR(255)  NOT NULL,         -- never store plaintext, future-me
  role            ENUM('ADMIN','STAFF','CUSTOMER') NOT NULL DEFAULT 'CUSTOMER',
  status          ENUM('ACTIVE','BANNED') NOT NULL DEFAULT 'ACTIVE',
  created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- VEHICLES: belong to users; purge on user delete because “abandoned objects” are how bugs breed.
CREATE TABLE IF NOT EXISTS vehicles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id   INT NOT NULL,
  plate_no  VARCHAR(20) NOT NULL UNIQUE,
  make      VARCHAR(60) DEFAULT NULL,
  model     VARCHAR(60) DEFAULT NULL,
  year      SMALLINT     DEFAULT NULL,
  color     VARCHAR(30)  DEFAULT NULL,
  vin       VARCHAR(40)  DEFAULT NULL,
  mileage   INT          DEFAULT 0,
  last_service_date DATE DEFAULT NULL,
  insurance_provider VARCHAR(120) DEFAULT NULL,
  policy_number      VARCHAR(80)  DEFAULT NULL,
  notes     TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_vehicle_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SERVICES: the catalogue. Minimal now; extend later if the team gets ambitious.
CREATE TABLE IF NOT EXISTS services (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  price DECIMAL(10,2) DEFAULT 0.00,
  est_hours DECIMAL(4,2) DEFAULT NULL,
  default_interval_km   INT DEFAULT NULL,
  default_interval_days INT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- APPOINTMENTS: one service per appointment (MVP). If multi-service is demanded, add appointment_services table.
CREATE TABLE IF NOT EXISTS appointments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  vehicle_id  INT NOT NULL,
  service_id  INT NOT NULL,
  staff_id    INT DEFAULT NULL,   -- assigned later
  scheduled_at DATETIME NOT NULL,
  status ENUM('PENDING','APPROVED','REJECTED','IN_PROGRESS','WAITING_PARTS','COMPLETED','CANCELLED')
         NOT NULL DEFAULT 'PENDING',
  remarks TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_appt_customer FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_appt_vehicle  FOREIGN KEY (vehicle_id)  REFERENCES vehicles(id) ON DELETE CASCADE,
  CONSTRAINT fk_appt_service  FOREIGN KEY (service_id)  REFERENCES services(id),
  CONSTRAINT fk_appt_staff    FOREIGN KEY (staff_id)    REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SERVICE RECORDS: evidence we did real work. Photos as JSON for now; table later if needed.
CREATE TABLE IF NOT EXISTS service_records (
  id INT AUTO_INCREMENT PRIMARY KEY,
  appointment_id INT NOT NULL UNIQUE,      -- one record per appointment (1:1). remove UNIQUE if you want multiple stages.
  odometer_km INT DEFAULT NULL,
  work_done TEXT DEFAULT NULL,
  diagnostics_notes TEXT DEFAULT NULL,
  photos JSON DEFAULT NULL,                -- array of file paths; table if you want per-photo metadata
  cost DECIMAL(10,2) DEFAULT NULL,
  completed_at DATETIME DEFAULT NULL,
  CONSTRAINT fk_record_appt FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- RATINGS: customers sound off. Be brave.
CREATE TABLE IF NOT EXISTS ratings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  appointment_id INT NOT NULL,
  staff_id INT DEFAULT NULL,
  stars TINYINT NOT NULL,                  -- behave: 1..5 at app layer
  comment TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_rate_appt FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
  CONSTRAINT fk_rate_staff FOREIGN KEY (staff_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- NOTIFICATIONS: in-app + email log. Keep it simple; you’ll thank me later.
CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  type ENUM('IN_APP','EMAIL') NOT NULL DEFAULT 'IN_APP',
  title VARCHAR(160) NOT NULL,
  body  TEXT DEFAULT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_note_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- REMINDERS: time/mileage triggers; cron will poke this.
CREATE TABLE IF NOT EXISTS reminders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vehicle_id INT NOT NULL,
  service_id INT NOT NULL,
  due_date DATE DEFAULT NULL,
  due_mileage INT DEFAULT NULL,
  last_sent_at DATETIME DEFAULT NULL,
  status ENUM('DUE','SENT','DISMISSED') NOT NULL DEFAULT 'DUE',
  CONSTRAINT fk_rem_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
  CONSTRAINT fk_rem_service FOREIGN KEY (service_id) REFERENCES services(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ACTIVITY LOGS: breadcrumbs for the grown-ups (admin).
CREATE TABLE IF NOT EXISTS activity_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT DEFAULT NULL,
  action  VARCHAR(80)  NOT NULL,
  entity  VARCHAR(80)  DEFAULT NULL,
  entity_id INT        DEFAULT NULL,
  meta JSON DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_log_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Helpful indices (fast lists = happy humans)
CREATE INDEX IF NOT EXISTS idx_appt_customer ON appointments(customer_id);
CREATE INDEX IF NOT EXISTS idx_appt_staff    ON appointments(staff_id);
CREATE INDEX IF NOT EXISTS idx_appt_sched    ON appointments(scheduled_at);
CREATE INDEX IF NOT EXISTS idx_notif_user    ON notifications(user_id);
CREATE INDEX IF NOT EXISTS idx_rem_vehicle   ON reminders(vehicle_id);
