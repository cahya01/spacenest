CREATE DATABASE IF NOT EXISTS sipak;
USE sipak;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('mahasiswa', 'dosen', 'admin', 'superadmin') NOT NULL,
    fakultas VARCHAR(150) DEFAULT NULL,
    jurusan VARCHAR(150) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pengajuan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    judul VARCHAR(255) NOT NULL,
    kategori VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    status ENUM('diajukan', 'diproses', 'disetujui', 'ditolak') DEFAULT 'diajukan',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE dokumen (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pengajuan_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pengajuan_id) REFERENCES pengajuan(id)
);

INSERT INTO users (username, password, role) VALUES
('admin', 'admin123', 'admin'),
('dosen', 'dosen123', 'dosen'),
('mahasiswa', 'mahasiswa123', 'mahasiswa'),
('superadmin', 'super123', 'superadmin');
