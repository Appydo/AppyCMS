# INSERT INTO users (id, project_id, firstname, username, salt, role, password, newPassword, email, is_active, note, address, city, phone, created, updated, postal, civility, partner, gts, newsletter, billing_address, billing_city, billing_phone, billing_postal) VALUES (1, 1, NULL, 'admin', 'Zxoby0jgxmZfw?$', 'admin', '2794f8204254233eb8eca489d416a7272ad36edd', NULL, 'contact@appydo.com', 1, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

INSERT INTO Role (role_id, project_id, role_name, role_parent) VALUES (1, 1, 'guest', NULL);
INSERT INTO Role (role_id, project_id, role_name, role_parent) VALUES (2, 1, 'staff', 'guest');
INSERT INTO Role (role_id, project_id, role_name, role_parent) VALUES (3, 1, 'editor', 'staff');
INSERT INTO Role (role_id, project_id, role_name, role_parent) VALUES (4, 1, 'admin', NULL);

INSERT INTO Acl (user_id, project_id, role_id) VALUES (1, 1, 4);

# INSERT INTO Project (id, user_id, name, description, information, theme, keywords, banner, note, css, footer, subtitle, hits, menu, comment, stat, log, contact, hide, ban, zone, config, created, updated) VALUES (1,	1,	'appydo',	'',	NULL,	NULL,	NULL,	NULL,	'petite note',	'',	NULL,	NULL,	NULL,	NULL,	0,	NULL,	NULL,	NULL,	0,	0,	NULL,	NULL,	'1354298713',	'1359843636');