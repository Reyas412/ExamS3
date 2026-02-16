CREATE DATABASE IF NOT EXISTS gnbrc ;
USE gnbrc;

-- Table des régions
CREATE TABLE IF NOT EXISTS regions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL UNIQUE
);

-- Table des villes
CREATE TABLE villes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL UNIQUE,
    region_id INT NOT NULL,
    FOREIGN KEY (region_id) REFERENCES regions(id) ON DELETE RESTRICT
);

-- Table des besoins
CREATE TABLE besoins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ville_id INT NOT NULL,
    type ENUM('nature', 'matériaux', 'argent') NOT NULL,
    designation VARCHAR(255) NOT NULL,
    quantite DECIMAL(10,2) NOT NULL,
    prix_unitaire DECIMAL(10,2) NOT NULL,
    date_saisie DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ville_id) REFERENCES villes(id) ON DELETE CASCADE
);

-- Table des dons
CREATE TABLE dons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('nature', 'matériaux', 'argent') NOT NULL,
    designation VARCHAR(255) NOT NULL,
    quantite DECIMAL(10,2) NOT NULL,
    date_saisie DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table du dispatch
CREATE TABLE dispatch (
    id INT AUTO_INCREMENT PRIMARY KEY,
    don_id INT NOT NULL,
    besoin_id INT NOT NULL,
    quantite_attribuee DECIMAL(10,2) NOT NULL,
    date_dispatch DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (don_id) REFERENCES dons(id) ON DELETE CASCADE,
    FOREIGN KEY (besoin_id) REFERENCES besoins(id) ON DELETE CASCADE
);

-- Données de base

-- Insertion des régions
INSERT INTO regions (nom) VALUES
('Analamanga'),
('Vakinankaratra'),
('Itasy'),
('Bongolava'),
('Sofia'),
('Boeny'),
('Melaky'),
('Alaotra-Mangoro'),
('Analanjirofo'),
('Atsinanana'),
('Atsimo-Atsinana'),
('Ihorombe'),
('Haute Matsiatra'),
('Vatovavy-Fitovinany'),
('Atsimo-Andrefana'),
('Androy'),
('Anosy'),
('Menabe'),
('Diana'),
('Sava');

-- Insertion des villes (modifiées pour inclure region_id)
INSERT INTO villes (nom, region_id) VALUES
('Antananarivo', 1),
('Antsirabe', 2),
('Toamasina', 11),
('Mahajanga', 6);

-- Insertion des besoins (exemples)
INSERT INTO besoins (ville_id, type, designation, quantite, prix_unitaire, date_saisie) VALUES
(1, 'nature', 'Riz', 1000.00, 2.50, '2023-01-01 10:00:00'),
(1, 'nature', 'Huile', 500.00, 5.00, '2023-01-02 11:00:00'),
(2, 'matériaux', 'Tôle', 200.00, 10.00, '2023-01-03 12:00:00'),
(3, 'argent', 'Argent', 10000.00, 1.00, '2023-01-04 13:00:00');

-- Insertion des dons (exemples)
INSERT INTO dons (type, designation, quantite, date_saisie) VALUES
('nature', 'Riz', 800.00, '2023-01-05 14:00:00'),
('nature', 'Huile', 400.00, '2023-01-06 15:00:00'),
('matériaux', 'Tôle', 150.00, '2023-01-07 16:00:00'),
('argent', 'Argent', 5000.00, '2023-01-08 17:00:00');
