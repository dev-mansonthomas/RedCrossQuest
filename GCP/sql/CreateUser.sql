create user if not exists '¤MYSQL_USER¤'@'%';
set password for  '¤MYSQL_USER¤'@'%' = password('¤MYSQL_PASSWORD¤');
GRANT  SELECT, UPDATE, INSERT, DELETE ON `¤MYSQL_DB¤`.* TO `¤MYSQL_USER¤`@'%';
GRANT  SELECT ON `¤MYSQL_DB¤`.* TO `¤MYSQL_USER_READ¤`@'%' identified by '¤MYSQL_USER_READ_PASSWORD¤';

GRANT  UPDATE, SELECT ON `¤MYSQL_DB¤`.tronc_queteur        TO `¤MYSQL_USER_WRITE¤`@'%' identified by '¤MYSQL_USER_WRITE_PASSWORD¤';
GRANT  INSERT ON `¤MYSQL_DB¤`.queteur_registration TO `¤MYSQL_USER_WRITE¤`@'%';

flush privileges;
