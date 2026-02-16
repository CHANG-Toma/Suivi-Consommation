-- Création des tables pour EcoTrack

-- Table type_energie
CREATE TABLE IF NOT EXISTS type_energie (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    unite VARCHAR(50),
    couleur VARCHAR(7),
    icone VARCHAR(50)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table user
CREATE TABLE IF NOT EXISTS `user` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(180) NOT NULL UNIQUE,
    roles JSON NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(255),
    last_name VARCHAR(255),
    created_at DATETIME NOT NULL,
    INDEX UNIQ_IDENTIFIER_EMAIL (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table consommation
CREATE TABLE IF NOT EXISTS consommation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type_energie_id INT NOT NULL,
    valeur DECIMAL(10,2) NOT NULL,
    date_releve DATE NOT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES `user`(id) ON DELETE CASCADE,
    FOREIGN KEY (type_energie_id) REFERENCES type_energie(id) ON DELETE CASCADE,
    INDEX IDX_user (user_id),
    INDEX IDX_type_energie (type_energie_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table alerte
CREATE TABLE IF NOT EXISTS alerte (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type_energie_id INT,
    titre VARCHAR(255) NOT NULL,
    message TEXT,
    seuil DECIMAL(10,2),
    lu TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES `user`(id) ON DELETE CASCADE,
    FOREIGN KEY (type_energie_id) REFERENCES type_energie(id) ON DELETE SET NULL,
    INDEX IDX_user (user_id),
    INDEX IDX_type_energie (type_energie_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion des types d'énergie
INSERT INTO type_energie (nom, unite, couleur, icone) VALUES
('Électricité', 'kWh', '#F59E0B', '⚡'),
('Gaz', 'kWh', '#F97316', '🔥'),
('Eau', 'm³', '#06B6D4', '💧')
ON DUPLICATE KEY UPDATE nom=nom;
