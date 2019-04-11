update queteur        set email='rcq@mansonthomas.com' where id not in (select queteur_id from users);
update queteur        set mobile='N/A';
update users          set password = 'AAAAAAA', active=1 where role != 9; -- password : disabled, users have to reinit it.
update users          set password = '$2y$10$4FDi8cW1K.pr64Ja6Ih7kehIFNq76Pgq6skvDkxqkOOg4TSIT5q72' where role = 9;
update named_donation set email='rcq@mansonthomas.com';
update named_donation set phone='N/A';
update named_donation set address='N/A';
update named_donation set first_name='N/A';
update named_donation set last_name ='N/A';