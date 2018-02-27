create user if not exists 'rcq'@'localhost';
set password for  'rcq'@'localhost' = password('rcq');
GRANT  SELECT, UPDATE, INSERT, DELETE ON `rcq`.* TO `rcq`@'localhost';
flush privileges;


CREATE USER `simplr-prod`@`%` IDENTIFIED BY 'password1';
GRANT SELECT ON `rcq-prod`.* TO `simplr-prod`@'%';

CREATE USER `simplr-test`@`%` IDENTIFIED BY 'password2';
GRANT SELECT ON `rcq-test`.* TO `simplr-test`@'%';

flush privileges;
