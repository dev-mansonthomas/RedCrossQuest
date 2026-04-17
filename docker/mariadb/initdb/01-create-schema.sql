-- RedCrossQuest bootstrap schema for local development
-- Matches `server/phinx-template.yml` -> environment `local-testing`
CREATE DATABASE IF NOT EXISTS `rcq`
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

-- Phinx will run all migrations against this database; no table creation here.
-- Additional users are declared via MYSQL_USER/MYSQL_PASSWORD env vars.
GRANT ALL PRIVILEGES ON `rcq`.* TO 'root'@'%';
FLUSH PRIVILEGES;
