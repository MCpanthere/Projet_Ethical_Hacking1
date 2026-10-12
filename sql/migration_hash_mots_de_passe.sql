-- Migration d'une base créée avec la version vulnérable vers la version sécurisée,
-- sans recréer les tables (les messages existants sont conservés).
-- Idempotent : peut être relancé sans risque.
--   mariadb -u root -p < sql/migration_hash_mots_de_passe.sql
USE projet_eh;

-- 1. Remplacer les mots de passe encore en clair par leur hachage bcrypt.
--    admin -> SuperSecretAdmin123!   dupont -> azerty
UPDATE utilisateurs
   SET mot_de_passe = '$2y$10$AFDw0DP7rNq3cDPxzSvr4.TxjGqmrGjwOWZZ/P/KhFojRoy2FiD5e'
 WHERE identifiant = 'admin' AND mot_de_passe NOT LIKE '$2y$%';

UPDATE utilisateurs
   SET mot_de_passe = '$2y$10$Tirex/hQquPhjN.Ct13Q1.O7s9QO8vfh9ufzYqU22Wuoi9gYjdgMO'
 WHERE identifiant = 'dupont' AND mot_de_passe NOT LIKE '$2y$%';

-- 2. Supprimer les doublons d'identifiant (garder la ligne la plus ancienne).
DELETE u1 FROM utilisateurs u1
  JOIN utilisateurs u2
    ON u1.identifiant = u2.identifiant AND u1.id > u2.id;

-- 3. Empêcher tout nouveau doublon.
ALTER TABLE utilisateurs ADD UNIQUE INDEX IF NOT EXISTS uq_identifiant (identifiant);
