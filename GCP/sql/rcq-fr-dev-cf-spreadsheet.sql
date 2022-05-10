create user if not exists 'rcq-fr-dev-cf-spreadsheet'@'%';
set password for  'rcq-fr-dev-cf-spreadsheet'@'%' = '¤MYSQL_PASSWORD¤';
GRANT  SELECT, UPDATE ON `ul` TO 'rcq-fr-dev-cf-spreadsheet'@'%';
flush privileges;

