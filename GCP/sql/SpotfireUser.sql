CREATE USER 'spotfire_prod'@'44.230.83.28'  IDENTIFIED BY '¤spotfire_prod_pwd¤';
CREATE USER 'spotfire_prod'@'54.189.206.52' IDENTIFIED BY '¤spotfire_prod_pwd¤';
CREATE USER 'spotfire_prod'@'54.214.94.26'  IDENTIFIED BY '¤spotfire_prod_pwd¤';
CREATE USER 'spotfire_prod'@'54.204.62.233' IDENTIFIED BY '¤spotfire_prod_pwd¤';
CREATE USER 'spotfire_prod'@'50.19.56.150'  IDENTIFIED BY '¤spotfire_prod_pwd¤';
CREATE USER 'spotfire_prod'@'92.94.209.196' IDENTIFIED BY '¤spotfire_prod_pwd¤';


-- GRANT SELECT,SHOW VIEW ON information_schema.* to 'spotfire_prod'@'44.230.83.28', 'spotfire_prod'@'54.189.206.52', 'spotfire_prod'@'54.214.94.26', 'spotfire_prod'@'54.204.62.233', 'spotfire_prod'@'50.19.56.150', 'spotfire_prod'@'92.94.209.196';

-- grant all on rcq_fr_prod_db.* to 'spotfire_prod'@'44.230.83.28', 'spotfire_prod'@'54.189.206.52', 'spotfire_prod'@'54.214.94.26', 'spotfire_prod'@'54.204.62.233', 'spotfire_prod'@'50.19.56.150', 'spotfire_prod'@'92.94.209.196';

flush privileges;

GRANT SELECT,SHOW VIEW ON rcq_fr_prod_db.`daily_stats_before_rcq` to 'spotfire_prod'@'44.230.83.28', 'spotfire_prod'@'54.189.206.52', 'spotfire_prod'@'54.214.94.26', 'spotfire_prod'@'54.204.62.233', 'spotfire_prod'@'50.19.56.150', 'spotfire_prod'@'92.94.209.196';
GRANT SELECT,SHOW VIEW ON rcq_fr_prod_db.`named_donation`         to 'spotfire_prod'@'44.230.83.28', 'spotfire_prod'@'54.189.206.52', 'spotfire_prod'@'54.214.94.26', 'spotfire_prod'@'54.204.62.233', 'spotfire_prod'@'50.19.56.150', 'spotfire_prod'@'92.94.209.196';
GRANT SELECT,SHOW VIEW ON rcq_fr_prod_db.`point_quete`            to 'spotfire_prod'@'44.230.83.28', 'spotfire_prod'@'54.189.206.52', 'spotfire_prod'@'54.214.94.26', 'spotfire_prod'@'54.204.62.233', 'spotfire_prod'@'50.19.56.150', 'spotfire_prod'@'92.94.209.196';
GRANT SELECT,SHOW VIEW ON rcq_fr_prod_db.`queteur_mailing_status` to 'spotfire_prod'@'44.230.83.28', 'spotfire_prod'@'54.189.206.52', 'spotfire_prod'@'54.214.94.26', 'spotfire_prod'@'54.204.62.233', 'spotfire_prod'@'50.19.56.150', 'spotfire_prod'@'92.94.209.196';
GRANT SELECT,SHOW VIEW ON rcq_fr_prod_db.`queteur_registration`   to 'spotfire_prod'@'44.230.83.28', 'spotfire_prod'@'54.189.206.52', 'spotfire_prod'@'54.214.94.26', 'spotfire_prod'@'54.204.62.233', 'spotfire_prod'@'50.19.56.150', 'spotfire_prod'@'92.94.209.196';
GRANT SELECT (`id`, `token`, `token_expiration`, `ul_id`),
    SHOW VIEW ON rcq_fr_prod_db.`spotfire_access`        to 'spotfire_prod'@'44.230.83.28', 'spotfire_prod'@'54.189.206.52', 'spotfire_prod'@'54.214.94.26', 'spotfire_prod'@'54.204.62.233', 'spotfire_prod'@'50.19.56.150', 'spotfire_prod'@'92.94.209.196';
GRANT SELECT,SHOW VIEW ON rcq_fr_prod_db.`tronc`                  to 'spotfire_prod'@'44.230.83.28', 'spotfire_prod'@'54.189.206.52', 'spotfire_prod'@'54.214.94.26', 'spotfire_prod'@'54.204.62.233', 'spotfire_prod'@'50.19.56.150', 'spotfire_prod'@'92.94.209.196';
GRANT SELECT,SHOW VIEW ON rcq_fr_prod_db.`tronc_queteur`          to 'spotfire_prod'@'44.230.83.28', 'spotfire_prod'@'54.189.206.52', 'spotfire_prod'@'54.214.94.26', 'spotfire_prod'@'54.204.62.233', 'spotfire_prod'@'50.19.56.150', 'spotfire_prod'@'92.94.209.196';
GRANT SELECT,SHOW VIEW ON rcq_fr_prod_db.`ul`                     to 'spotfire_prod'@'44.230.83.28', 'spotfire_prod'@'54.189.206.52', 'spotfire_prod'@'54.214.94.26', 'spotfire_prod'@'54.204.62.233', 'spotfire_prod'@'50.19.56.150', 'spotfire_prod'@'92.94.209.196';
GRANT SELECT,SHOW VIEW ON rcq_fr_prod_db.`ul_registration`        to 'spotfire_prod'@'44.230.83.28', 'spotfire_prod'@'54.189.206.52', 'spotfire_prod'@'54.214.94.26', 'spotfire_prod'@'54.204.62.233', 'spotfire_prod'@'50.19.56.150', 'spotfire_prod'@'92.94.209.196';
GRANT SELECT (`id`, `nivol`, `queteur_id`, `role`, `active`),
    SHOW VIEW ON rcq_fr_prod_db.`users`                  to 'spotfire_prod'@'44.230.83.28', 'spotfire_prod'@'54.189.206.52', 'spotfire_prod'@'54.214.94.26', 'spotfire_prod'@'54.204.62.233', 'spotfire_prod'@'50.19.56.150', 'spotfire_prod'@'92.94.209.196';
GRANT SELECT,SHOW VIEW ON rcq_fr_prod_db.`yearly_goal` 			  to 'spotfire_prod'@'44.230.83.28', 'spotfire_prod'@'54.189.206.52', 'spotfire_prod'@'54.214.94.26', 'spotfire_prod'@'54.204.62.233', 'spotfire_prod'@'50.19.56.150', 'spotfire_prod'@'92.94.209.196';


GRANT SELECT
    (`id`                        ,
     `first_name`                ,
     `last_name`                 ,
     `secteur`                   ,
     `nivol`                     ,
     `created`                   ,
     `updated`                   ,
     `parent_authorization`      ,
     `temporary_volunteer_form`  ,
     `notes`                     ,
     `ul_id`                     ,
     `birthdate`                 ,
     `man`                       ,
     `active`                    ,
     `qr_code_printed`           ,
     `referent_volunteer`        ,
     `anonymization_token`       ,
     `anonymization_date`        ,
     `anonymization_user_id`     ,
     `spotfire_access_token`     ,
     `mailing_preference`        ),
    SHOW VIEW ON rcq_fr_prod_db.`queteur`	              to 'spotfire_prod'@'44.230.83.28', 'spotfire_prod'@'54.189.206.52', 'spotfire_prod'@'54.214.94.26', 'spotfire_prod'@'54.204.62.233', 'spotfire_prod'@'50.19.56.150', 'spotfire_prod'@'92.94.209.196';




flush privileges ;















