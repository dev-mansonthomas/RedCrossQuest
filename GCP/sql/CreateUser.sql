create user if not exists '¤MYSQL_USER¤'@'%';
set password for  '¤MYSQL_USER¤'@'%' = password('¤MYSQL_PASSWORD¤');
GRANT  SELECT, UPDATE, INSERT, DELETE ON `¤MYSQL_DB¤`.* TO `¤MYSQL_USER¤`@'%';
flush privileges;
