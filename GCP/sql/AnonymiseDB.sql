update queteur        set email='rcq@redcrossquest.com' where id not in (select queteur_id from users);
update queteur        set mobile='33601020304';
update users          set password = 'AAAAAAA'; -- password : disabled, users have to reinit it.
update named_donation set email='rcq@redcrossquest.com';
update named_donation set phone='N/A';
update named_donation set address='N/A';
update named_donation set first_name='N/A';
update named_donation set last_name ='N/A';
update users          set password = '$2y$10$d8WTLEW8c8wiaR6ZjsJl.egsVxWi8ETZzeqIkU7JGqPJX/FYEWzce' where role = 9; -- CRFCRF
