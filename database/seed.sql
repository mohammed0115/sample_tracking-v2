-- Seed Data

USE sample_tracking;

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password, first_name, last_name, role, is_active) VALUES
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'Admin', 1),
('operator1', 'operator@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Operator', 'One', 'Operator', 1),
('viewer1', 'viewer@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Viewer', 'One', 'Viewer', 1);

-- Insert RFID tags
INSERT INTO rfid_tags (uid, is_active) VALUES
('RFID-AX92-7781', 1),
('RFID-BB12-1234', 1),
('RFID-CC34-5678', 1),
('RFID-DD56-9012', 1),
('RFID-EE78-3456', 1);

-- Insert sample data
INSERT INTO samples (sample_number, sample_type, category, person_name, collected_date, location, rfid_id, status) VALUES
('20230422001', 'دم', 'جنائية', 'يوسف أحمد', '2026-02-01', 'الرياض', 1, 'pending'),
('20230422002', 'لعاب', 'جنائية', 'سارة محمد', '2026-02-01', 'جدة', 2, 'checked'),
('20230422003', 'شعر', 'طب شرعي', 'خالد علي', '2026-02-01', 'مكة', 3, 'approved'),
('20230422004', 'أنسجة', 'طبية', 'فاطمة حسن', '2026-02-02', 'الدمام', 4, 'pending'),
('20230422005', 'دم', 'جنائية', 'محمد عبدالله', '2026-02-02', 'الرياض', 5, 'checked');
