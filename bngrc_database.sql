-- =============================================
-- BASE DE DONNÉES BNGRC - SCHÉMA COMPLET
-- =============================================

-- Créer la base de données
CREATE DATABASE IF NOT EXISTS gnbrc;
USE gnbrc;

-- =============================================
-- TABLES
-- =============================================

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
    quantite_satisfaite DECIMAL(10,2) DEFAULT 0,
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

-- Table de configuration
CREATE TABLE config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(255) NOT NULL UNIQUE,
    value TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des achats
CREATE TABLE achats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    besoin_id INT NOT NULL,
    ville_id INT NOT NULL,
    quantite DECIMAL(10,2) NOT NULL,
    montant_base DECIMAL(10,2) NOT NULL,
    frais_percent DECIMAL(5,2) DEFAULT 0,
    montant_total DECIMAL(10,2) NOT NULL,
    status ENUM('simule', 'valide') DEFAULT 'simule',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (besoin_id) REFERENCES besoins(id) ON DELETE CASCADE,
    FOREIGN KEY (ville_id) REFERENCES villes(id) ON DELETE CASCADE
);

-- Table de liaison achat-dispatch
CREATE TABLE achat_dispatch (
    id INT AUTO_INCREMENT PRIMARY KEY,
    achat_id INT NOT NULL,
    don_id INT NOT NULL,
    montant_utilise DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (achat_id) REFERENCES achats(id) ON DELETE CASCADE,
    FOREIGN KEY (don_id) REFERENCES dons(id) ON DELETE CASCADE
);

-- =============================================
-- DONNÉES - 22 RÉGIONS
-- =============================================
INSERT INTO regions (nom) VALUES ('Analamanga');
INSERT INTO regions (nom) VALUES ('Vakinankaratra');
INSERT INTO regions (nom) VALUES ('Itasy');
INSERT INTO regions (nom) VALUES ('Bongolava');
INSERT INTO regions (nom) VALUES ('Sofia');
INSERT INTO regions (nom) VALUES ('Boeny');
INSERT INTO regions (nom) VALUES ('Melaky');
INSERT INTO regions (nom) VALUES ('Alaotra-Mangoro');
INSERT INTO regions (nom) VALUES ('Analanjirofo');
INSERT INTO regions (nom) VALUES ('Atsinanana');
INSERT INTO regions (nom) VALUES ('Atsimo-Atsinana');
INSERT INTO regions (nom) VALUES ('Ihorombe');
INSERT INTO regions (nom) VALUES ('Haute Matsiatra');
INSERT INTO regions (nom) VALUES ('Vatovavy-Fitovinany');
INSERT INTO regions (nom) VALUES ('Atsimo-Andrefana');
INSERT INTO regions (nom) VALUES ('Androy');
INSERT INTO regions (nom) VALUES ('Anosy');
INSERT INTO regions (nom) VALUES ('Menabe');
INSERT INTO regions (nom) VALUES ('Diana');
INSERT INTO regions (nom) VALUES ('Sava');

-- =============================================
-- DONNÉES - 9 VILLES
-- =============================================
-- Antananarivo (Capitale) - Analamanga (region_id = 1)
INSERT INTO villes (nom, region_id) VALUES ('Antananarivo', 1);

-- Antsirabe - Vakinankaratra (region_id = 2)
INSERT INTO villes (nom, region_id) VALUES ('Antsirabe', 2);

-- Toamasina - Atsinanana (region_id = 10)
INSERT INTO villes (nom, region_id) VALUES ('Toamasina', 10);

-- Mahajanga - Boeny (region_id = 6)
INSERT INTO villes (nom, region_id) VALUES ('Mahajanga', 6);

-- Mananjary - Atsinanana (region_id = 10)
INSERT INTO villes (nom, region_id) VALUES ('Mananjary', 10);

-- Farafangana - Atsimo-Atsinana (region_id = 11)
INSERT INTO villes (nom, region_id) VALUES ('Farafangana', 11);

-- Nosy Be - Diana (region_id = 18)
INSERT INTO villes (nom, region_id) VALUES ('Nosy Be', 18);

-- Morondava - Menabe (region_id = 17)
INSERT INTO villes (nom, region_id) VALUES ('Morondava', 17);

-- Toliara - Atsimo-Andrefana (region_id = 15)
INSERT INTO villes (nom, region_id) VALUES ('Toliara', 15);

-- =============================================
-- DONNÉES - 16 DONS
-- =============================================

-- Dons en argent (6)
INSERT INTO dons (type, designation, quantite, date_saisie) VALUES ('argent', 'Argent', 5000000, '2026-02-16');
INSERT INTO dons (type, designation, quantite, date_saisie) VALUES ('argent', 'Argent', 3000000, '2026-02-16');
INSERT INTO dons (type, designation, quantite, date_saisie) VALUES ('argent', 'Argent', 4000000, '2026-02-17');
INSERT INTO dons (type, designation, quantite, date_saisie) VALUES ('argent', 'Argent', 1500000, '2026-02-17');
INSERT INTO dons (type, designation, quantite, date_saisie) VALUES ('argent', 'Argent', 6000000, '2026-02-17');
INSERT INTO dons (type, designation, quantite, date_saisie) VALUES ('argent', 'Argent', 20000000, '2026-02-19');

-- Dons en nature (6)
INSERT INTO dons (type, designation, quantite, date_saisie) VALUES ('nature', 'Riz (kg)', 400, '2026-02-16');
INSERT INTO dons (type, designation, quantite, date_saisie) VALUES ('nature', 'Eau (L)', 600, '2026-02-16');
INSERT INTO dons (type, designation, quantite, date_saisie) VALUES ('nature', 'Haricots', 100, '2026-02-17');
INSERT INTO dons (type, designation, quantite, date_saisie) VALUES ('nature', 'Riz (kg)', 2000, '2026-02-18');
INSERT INTO dons (type, designation, quantite, date_saisie) VALUES ('nature', 'Eau (L)', 5000, '2026-02-18');
INSERT INTO dons (type, designation, quantite, date_saisie) VALUES ('nature', 'Haricots', 88, '2026-02-17');

-- Dons en matériaux (4)
INSERT INTO dons (type, designation, quantite, date_saisie) VALUES ('matériaux', 'Tôle', 50, '2026-02-17');
INSERT INTO dons (type, designation, quantite, date_saisie) VALUES ('matériaux', 'Bâche', 70, '2026-02-17');
INSERT INTO dons (type, designation, quantite, date_saisie) VALUES ('matériaux', 'Tôle', 300, '2026-02-18');
INSERT INTO dons (type, designation, quantite, date_saisie) VALUES ('matériaux', 'Bâche', 500, '2026-02-19');

-- =============================================
-- DONNÉES - 26 BESOINS
-- =============================================

-- Toamasina (6 besoins)
INSERT INTO besoins (ville_id, type, designation, quantite, quantite_satisfaite, prix_unitaire, date_saisie) 
SELECT id, 'nature', 'Riz (kg)', 800, 0, 3000, '2026-02-16' FROM villes WHERE nom = 'Toamasina';

INSERT INTO besoins (ville_id, type, designation, quantite, quantite_satisfaite, prix_unitaire, date_saisie) 
SELECT id, 'nature', 'Eau (L)', 1500, 0, 1000, '2026-02-15' FROM villes WHERE nom = 'Toamasina';

INSERT INTO besoins (ville_id, type, designation, quantite, quantite_satisfaite, prix_unitaire, date_saisie) 
SELECT id, 'matériaux', 'Tôle', 120, 0, 25000, '2026-02-16' FROM villes WHERE nom = 'Toamasina';

INSERT INTO besoins (ville_id, type, designation, quantite, quantite_satisfaite, prix_unitaire, date_saisie) 
SELECT id, 'matériaux', 'Bâche', 200, 0, 15000, '2026-02-15' FROM villes WHERE nom = 'Toamasina';

INSERT INTO besoins (ville_id, type, designation, quantite, quantite_satisfaite, prix_unitaire, date_saisie) 
SELECT id, 'argent', 'Argent', 12000000, 0, 1, '2026-02-16' FROM villes WHERE nom = 'Toamasina';

INSERT INTO besoins (ville_id, type, designation, quantite, quantite_satisfaite, prix_unitaire, date_saisie) 
SELECT id, 'matériaux', 'Groupe', 3, 0, 6750000, '2026-02-15' FROM villes WHERE nom = 'Toamasina';

-- Mananjary (5 besoins)
INSERT INTO besoins (ville_id, type, designation, quantite, quantite_satisfaite, prix_unitaire, date_saisie) 
SELECT id, 'nature', 'Riz (kg)', 500, 0, 3000, '2026-02-15' FROM villes WHERE nom = 'Mananjary';

INSERT INTO besoins (ville_id, type, designation, quantite, quantite_satisfaite, prix_unitaire, date_saisie) 
SELECT id, 'nature', 'Eau (L)', 120, 0, 6000, '2026-02-16' FROM villes WHERE nom = 'Mananjary';

INSERT INTO besoins (ville_id, type, designation, quantite, quantite_satisfaite, prix_unitaire, date_saisie) 
SELECT id, 'matériaux', 'Tôle', 80, 0, 25000, '2026-02-15' FROM villes WHERE nom = 'Mananjary';

INSERT INTO besoins (ville_id, type, designation, quantite, quantite_satisfaite, prix_unitaire, date_saisie) 
SELECT id, 'matériaux', 'Clous (kg)', 60, 0, 8000, '2026-02-16' FROM villes WHERE nom = 'Mananjary';

INSERT INTO besoins (ville_id, type, designation, quantite, quantite_satisfaite, prix_unitaire, date_saisie) 
SELECT id, 'argent', 'Argent', 6000000, 0, 1, '2026-02-15' FROM villes WHERE nom = 'Mananjary';

-- Farafangana (5 besoins)
INSERT INTO besoins (ville_id, type, designation, quantite, quantite_satisfaite, prix_unitaire, date_saisie) 
SELECT id, 'nature', 'Riz (kg)', 600, 0, 3000, '2026-02-16' FROM villes WHERE nom = 'Farafangana';

INSERT INTO besoins (ville_id, type, designation, quantite, quantite_satisfaite, prix_unitaire, date_saisie) 
SELECT id, 'nature', 'Eau (L)', 1000, 0, 1000, '2026-02-15' FROM villes WHERE nom = 'Farafangana';

INSERT INTO besoins (ville_id, type, designation, quantite, quantite_satisfaite, prix_unitaire, date_saisie) 
SELECT id, 'matériaux', 'Bâche', 150, 0, 15000, '2026-02-16' FROM villes WHERE nom = 'Farafangana';

INSERT INTO besoins (ville_id, type, designation, quantite, quantite_satisfaite, prix_unitaire, date_saisie) 
SELECT id, 'matériaux', 'Bois', 100, 0, 10000, '2026-02-15' FROM villes WHERE nom = 'Farafangana';

INSERT INTO besoins (ville_id, type, designation, quantite, quantite_satisfaite, prix_unitaire, date_saisie) 
SELECT id, 'argent', 'Argent', 8000000, 0, 1, '2026-02-16' FROM villes WHERE nom = 'Farafangana';

-- Nosy Be (5 besoins)
INSERT INTO besoins (ville_id, type, designation, quantite, quantite_satisfaite, prix_unitaire, date_saisie) 
SELECT id, 'nature', 'Riz (kg)', 300, 0, 3000, '2026-02-15' FROM villes WHERE nom = 'Nosy Be';

INSERT INTO besoins (ville_id, type, designation, quantite, quantite_satisfaite, prix_unitaire, date_saisie) 
SELECT id, 'nature', 'Haricots', 200, 0, 4000, '2026-02-16' FROM villes WHERE nom = 'Nosy Be';

INSERT INTO besoins (ville_id, type, designation, quantite, quantite_satisfaite, prix_unitaire, date_saisie) 
SELECT id, 'matériaux', 'Tôle', 40, 0, 25000, '2026-02-15' FROM villes WHERE nom = 'Nosy Be';

INSERT INTO besoins (ville_id, type, designation, quantite, quantite_satisfaite, prix_unitaire, date_saisie) 
SELECT id, 'matériaux', 'Clous (kg)', 30, 0, 8000, '2026-02-16' FROM villes WHERE nom = 'Nosy Be';

INSERT INTO besoins (ville_id, type, designation, quantite, quantite_satisfaite, prix_unitaire, date_saisie) 
SELECT id, 'argent', 'Argent', 4000000, 0, 1, '2026-02-15' FROM villes WHERE nom = 'Nosy Be';

-- Morondava (5 besoins)
INSERT INTO besoins (ville_id, type, designation, quantite, quantite_satisfaite, prix_unitaire, date_saisie) 
SELECT id, 'nature', 'Riz (kg)', 700, 0, 3000, '2026-02-16' FROM villes WHERE nom = 'Morondava';

INSERT INTO besoins (ville_id, type, designation, quantite, quantite_satisfaite, prix_unitaire, date_saisie) 
SELECT id, 'nature', 'Eau (L)', 1200, 0, 1000, '2026-02-15' FROM villes WHERE nom = 'Morondava';

INSERT INTO besoins (ville_id, type, designation, quantite, quantite_satisfaite, prix_unitaire, date_saisie) 
SELECT id, 'matériaux', 'Bâche', 180, 0, 15000, '2026-02-16' FROM villes WHERE nom = 'Morondava';

INSERT INTO besoins (ville_id, type, designation, quantite, quantite_satisfaite, prix_unitaire, date_saisie) 
SELECT id, 'matériaux', 'Bois', 150, 0, 10000, '2026-02-15' FROM villes WHERE nom = 'Morondava';

INSERT INTO besoins (ville_id, type, designation, quantite, quantite_satisfaite, prix_unitaire, date_saisie) 
SELECT id, 'argent', 'Argent', 10000000, 0, 1, '2026-02-16' FROM villes WHERE nom = 'Morondava';

-- =============================================
-- DONNÉES - CONFIGURATION
-- =============================================
INSERT INTO config (config_key, value) VALUES ('purchase_fee_percent', '10');
