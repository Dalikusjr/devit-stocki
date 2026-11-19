-- Droits d'accès (cahier des charges §4)
-- Niveau d'accès : 1 = administrateur (gestion des utilisateurs, accès des projets), 0 = utilisateur
ALTER TABLE users ADD COLUMN is_admin TINYINT(1) NOT NULL DEFAULT 0 AFTER role;
-- Les comptes existants gardent l'accès complet qu'ils avaient
UPDATE users SET is_admin = 1;
-- Les accès des projets sont chiffrés : la colonne doit pouvoir contenir le texte chiffré
-- (déjà TEXT). Le chiffrement des valeurs existantes se fait avec :
--   php migrations/002_chiffrer_acces.php
