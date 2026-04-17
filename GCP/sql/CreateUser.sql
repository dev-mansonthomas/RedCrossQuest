-- 1. Création ou mise à jour de l'utilisateur principal (ADMIN DML)
CREATE USER IF NOT EXISTS '¤MYSQL_USER¤'@'%' IDENTIFIED BY '¤MYSQL_PASSWORD¤';
ALTER USER '¤MYSQL_USER¤'@'%' IDENTIFIED BY '¤MYSQL_PASSWORD¤'; -- Au cas où il existe déjà
GRANT SELECT, UPDATE, INSERT, DELETE ON `¤MYSQL_DB¤`.* TO '¤MYSQL_USER¤'@'%';

-- 2. Création de l'utilisateur Lecture Seule
CREATE USER IF NOT EXISTS '¤MYSQL_USER_READ¤'@'%' IDENTIFIED BY '¤MYSQL_USER_READ_PASSWORD¤';
GRANT SELECT ON `¤MYSQL_DB¤`.* TO '¤MYSQL_USER_READ¤'@'%';

-- 3. Création de l'utilisateur Ecriture restreinte
CREATE USER IF NOT EXISTS '¤MYSQL_USER_WRITE¤'@'%' IDENTIFIED BY '¤MYSQL_USER_WRITE_PASSWORD¤';
GRANT UPDATE, SELECT ON `¤MYSQL_DB¤`.tronc_queteur TO '¤MYSQL_USER_WRITE¤'@'%';
GRANT INSERT ON `¤MYSQL_DB¤`.queteur_registration TO '¤MYSQL_USER_WRITE¤'@'%';

-- 4. Application des changements
FLUSH PRIVILEGES;