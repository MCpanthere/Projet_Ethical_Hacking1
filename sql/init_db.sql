CREATE DATABASE IF NOT EXISTS projet_eh;
USE projet_eh;

CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    identifiant VARCHAR(50) NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user',
    num_securite_sociale VARCHAR(15)
);

INSERT INTO utilisateurs (identifiant, mot_de_passe, role, num_securite_sociale) 
VALUES ('admin', 'SuperSecretAdmin123!', 'admin', '123456789012345'),
       ('dupont', 'azerty', 'user', '198012345678901');

CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50),
    sujet VARCHAR(100),
    message TEXT,
    date_envoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);