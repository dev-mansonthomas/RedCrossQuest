
CREATE OR REPLACE VIEW `spotfire_access_users` AS
  SELECT sa.id, sa.token, sa.token_expiration, sa.ul_id,
    u.id as user_id, u.nivol, u.queteur_id, u.role, cast(u.active as unsigned) as active
  FROM spotfire_access sa, users u
  where sa.user_id = u.id;