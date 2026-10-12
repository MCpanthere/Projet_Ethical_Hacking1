CREATE DATABASE IF NOT EXISTS projet_eh;
USE projet_eh;

CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    identifiant VARCHAR(50) NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,   -- hachage bcrypt (password_hash), jamais en clair
    role VARCHAR(20) DEFAULT 'user',
    num_securite_sociale VARCHAR(15)
);

-- Remédiation : les mots de passe sont stockés HACHÉS (bcrypt via password_hash).
-- Hachages correspondant à :  admin -> SuperSecretAdmin123!   dupont -> azerty
-- (Régénérables avec : php -r 'echo password_hash("...", PASSWORD_DEFAULT);')
INSERT INTO utilisateurs (identifiant, mot_de_passe, role, num_securite_sociale)
VALUES ('admin',  '$2y$10$AFDw0DP7rNq3cDPxzSvr4.TxjGqmrGjwOWZZ/P/KhFojRoy2FiD5e', 'admin', '123456789012345'),
       ('dupont', '$2y$10$Tirex/hQquPhjN.Ct13Q1.O7s9QO8vfh9ufzYqU22Wuoi9gYjdgMO', 'user',  '198012345678901');

CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50),
    sujet VARCHAR(100),
    message TEXT,
    date_envoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
