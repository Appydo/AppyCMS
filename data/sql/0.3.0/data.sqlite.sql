-- scripts/data.sqlite.sql
--
-- You can begin populating the database with the following SQL statements.

-- admin / plop

INSERT INTO project (name, user_id, hide, ban, created, updated)
VALUES ('default', '1', '0', '0', '', '');

INSERT INTO users (username, password, email, salt, role, created, updated, is_active, project_id)
VALUES ('test', 'b92b005101da391beaccf7d148de5ff00167ed64', 'mystheme@free.fr','ce8d96d579d389e783f95b3772785783ea1a9854', 'admin', '', '', '1', '1');

INSERT INTO users (username, password, email, salt, role, created, updated, is_active)
VALUES ('admin', '8d0f97f2a691fd6a6fe56e14f43ac978a28a9db1', 'mystheme@free.fr','ce8d96d579d389e783f95b3772785783ea1a9854', 'admin', '', '', '1');

