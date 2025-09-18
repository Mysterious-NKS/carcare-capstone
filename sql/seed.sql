-- seed.sql — non-controversial demo data. Keep it boring; boring is reliable.

INSERT INTO services (name, price, est_hours, default_interval_km, default_interval_days) VALUES
  ('Engine Diagnostics',      180.00, 1.50, 15000, 365),
  ('Brake Inspection',        120.00, 1.00, 10000, 270),
  ('Transmission Service',    480.00, 4.00, 40000, 720),
  ('Oil Change',               90.00, 0.75,  7000, 180),
  ('Battery Testing',          40.00, 0.30,  NULL, 180),
  ('Tire Rotation',            60.00, 0.75,  8000, 200);

-- For login, we’ll register via the UI to get real password_hash values (less drama, more control).
