create user if not exists 'rcq'@'localhost';
set password for  'rcq'@'localhost' = password('rcq');
GRANT  SELECT, UPDATE, INSERT, DELETE ON `rcq`.* TO `rcq`@'localhost';
flush privileges;

