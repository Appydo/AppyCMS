DROP TABLE IF EXISTS "BankData";
CREATE TABLE BankData (
  id INTEGER PRIMARY KEY autoincrement,
  data longtext,
  created datetime NOT NULL,
  updated datetime NOT NULL
);


DROP TABLE IF EXISTS "BankOrder";
CREATE TABLE BankOrder (
  id INTEGER PRIMARY KEY autoincrement,
  user_id int(11) DEFAULT NULL,
  description longtext,
  count int(11) NOT NULL,
  price double DEFAULT NULL,
  created datetime NOT NULL,
  updated datetime NOT NULL,
  hide tinyint(1) NOT NULL,
  payment tinyint(1) NOT NULL
, "shipment" integer NULL);


DROP TABLE IF EXISTS "BasketOrder";
CREATE TABLE BasketOrder (
  id INTEGER PRIMARY KEY autoincrement,
  bankorder_id int(11) DEFAULT NULL,
  product_id int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  value longtext,
  count int(11) NOT NULL,
  created datetime NOT NULL,
  updated datetime NOT NULL,
  hide tinyint(1) NOT NULL,
  payment tinyint(1) NOT NULL
);


DROP TABLE IF EXISTS "Category";
CREATE TABLE Category (
  id INTEGER PRIMARY KEY autoincrement,
  project_id int(11) DEFAULT NULL,
  name varchar(255) NOT NULL,
  string longtext NOT NULL,
  created datetime NOT NULL,
  updated datetime NOT NULL,
  hide tinyint(1) NOT NULL,
  path varchar(1000) DEFAULT NULL
);


DROP TABLE IF EXISTS "Comment";
CREATE TABLE Comment (
  id INTEGER PRIMARY KEY autoincrement,
  topic_id int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  content longtext NOT NULL,
  hide tinyint(1) DEFAULT NULL,
  created datetime NOT NULL,
  updated datetime NOT NULL
, "name" text NULL);


DROP TABLE IF EXISTS "IPN";
CREATE TABLE IPN (
  id INTEGER PRIMARY KEY autoincrement,
  category_id int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  bank varchar(255) NOT NULL,
  value longtext NOT NULL,
  price double NOT NULL,
  created datetime NOT NULL,
  updated datetime NOT NULL,
  hide tinyint(1) NOT NULL
);


DROP TABLE IF EXISTS "Log";
CREATE TABLE Log (
  id INTEGER PRIMARY KEY autoincrement,
  project_id int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  name varchar(255) NOT NULL,
  description longtext NOT NULL,
  created datetime NOT NULL,
  updated datetime NOT NULL
);


DROP TABLE IF EXISTS "Mail";
CREATE TABLE Mail (
  id INTEGER PRIMARY KEY autoincrement,
  project_id int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  name varchar(255) NOT NULL,
  email varchar(255) NOT NULL,
  ip varchar(255) NOT NULL,
  message longtext NOT NULL,
  created datetime NOT NULL,
  updated datetime NOT NULL,
  hide tinyint(1) NOT NULL
);


DROP TABLE IF EXISTS "Menu";
CREATE TABLE Menu (
  id INTEGER PRIMARY KEY autoincrement,
  menu_id int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  project_id int(11) DEFAULT NULL,
  name varchar(255) NOT NULL,
  hide tinyint(1) DEFAULT NULL,
  created date NOT NULL,
  updated date NOT NULL
);


DROP TABLE IF EXISTS "MenuExtern";
CREATE TABLE MenuExtern (
  id INTEGER PRIMARY KEY autoincrement,
  menu_id int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  project_id int(11) DEFAULT NULL,
  name varchar(255) NOT NULL,
  url varchar(255) NOT NULL,
  position int(11) DEFAULT NULL,
  hide tinyint(1) DEFAULT NULL,
  created date NOT NULL,
  updated date NOT NULL
);


DROP TABLE IF EXISTS "MenuIntern";
CREATE TABLE MenuIntern (
  id INTEGER PRIMARY KEY autoincrement,
  topic_id int(11) DEFAULT NULL,
  menu_id int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  project_id int(11) DEFAULT NULL,
  name varchar(255) NOT NULL,
  position int(11) DEFAULT NULL,
  hide tinyint(1) DEFAULT NULL,
  created date NOT NULL,
  updated date NOT NULL
);


DROP TABLE IF EXISTS "Payment";
CREATE TABLE Payment (
  id INTEGER PRIMARY KEY autoincrement,
  category_id int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  bank varchar(255) NOT NULL,
  value longtext NOT NULL,
  price double NOT NULL,
  created datetime NOT NULL,
  updated datetime NOT NULL,
  hide tinyint(1) NOT NULL
);


DROP TABLE IF EXISTS "ProductOrder";
CREATE TABLE ProductOrder (
  id INTEGER PRIMARY KEY autoincrement,
  created date NOT NULL,
  updated date NOT NULL,
  hide tinyint(1) NOT NULL
);


DROP TABLE IF EXISTS "Project";
CREATE TABLE Project (
  id INTEGER PRIMARY KEY autoincrement,
  user_id int(11) DEFAULT NULL,
  name varchar(50) NOT NULL,
  description longtext,
  information longtext,
  theme varchar(50) DEFAULT NULL,
  keywords longtext,
  banner varchar(255) DEFAULT NULL,
  note longtext,
  css longtext,
  footer varchar(255) DEFAULT NULL,
  subtitle varchar(255) DEFAULT NULL,
  hits tinyint(1) DEFAULT NULL,
  menu tinyint(1) DEFAULT NULL,
  comment tinyint(1) DEFAULT NULL,
  stat tinyint(1) DEFAULT NULL,
  log tinyint(1) DEFAULT NULL,
  contact tinyint(1) DEFAULT NULL,
  hide tinyint(1) DEFAULT NULL,
  ban tinyint(1) DEFAULT NULL,
  zone varchar(50) DEFAULT NULL,
  config tinyint(1) DEFAULT NULL,
  created date NOT NULL,
  updated date NOT NULL
);


DROP TABLE IF EXISTS "Service";
CREATE TABLE Service (
  id INTEGER PRIMARY KEY autoincrement,
  name varchar(255) NOT NULL,
  url varchar(1000) NOT NULL,
  type varchar(255) NOT NULL,
  hide tinyint(1) NOT NULL,
  created datetime NOT NULL,
  updated datetime NOT NULL
);


DROP TABLE IF EXISTS "Stat";
CREATE TABLE Stat (
  id INTEGER PRIMARY KEY autoincrement,
  project_id int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  url varchar(255) NOT NULL,
  date date NOT NULL,
  ip varchar(100) NOT NULL
);


DROP TABLE IF EXISTS "Trash";
CREATE TABLE Trash (
  id INTEGER PRIMARY KEY autoincrement,
  project_id int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  name varchar(100) NOT NULL,
  content longtext NOT NULL,
  hide tinyint(1) DEFAULT NULL,
  rss tinyint(1) DEFAULT NULL,
  comment tinyint(1) DEFAULT NULL,
  created datetime NOT NULL,
  updated datetime NOT NULL,
  translate_index bigint(20) DEFAULT NULL,
  language varchar(255) DEFAULT NULL
);


DROP TABLE IF EXISTS "Event";
CREATE TABLE Event (
  id INTEGER PRIMARY KEY autoincrement,
  description longtext,
  username varchar(25) NOT NULL,
  email varchar(60) NOT NULL,
  address varchar(500),
  phone varchar(255) DEFAULT NULL,
  created datetime NOT NULL,
  updated datetime NOT NULL
);


DROP TABLE IF EXISTS "Auth";
CREATE TABLE "Auth" (
  "id" integer NULL PRIMARY KEY AUTOINCREMENT,
  "identity" text NOT NULL,
  "success" integer NOT NULL,
  "date" numeric NOT NULL
, "ip" text NULL);


DROP TABLE IF EXISTS "Product";
CREATE TABLE "Product" (
  "id" integer NULL PRIMARY KEY AUTOINCREMENT,
  "topic_id" integer NULL,
  "name" text NOT NULL,
  "description" text NOT NULL,
  "price" integer NULL,
  "created" numeric NOT NULL,
  "updated" numeric NOT NULL,
  "hide" integer NULL,
  "path" text NULL,
  "stock" integer NOT NULL,
  "discount" real NULL,
  "user_id" integer NULL,
  "weight" integer NULL,
  "image" text NULL,
  "project_id" real NULL,
  "image_path" text NULL,
  "image_name" text NULL
, "options" text NULL);


DROP TABLE IF EXISTS "Newsletter";
CREATE TABLE "Newsletter" (
  "project_id" integer NOT NULL,
  "email" text NOT NULL,
  "firstname" text NULL,
  "lastname" text NULL,
  "created" integer NOT NULL,
  "updated" integer NOT NULL
);

CREATE UNIQUE INDEX "Newsletter_email" ON "Newsletter" ("email");


DROP TABLE IF EXISTS "organization";
CREATE TABLE organization (
  organization_id int(11) NOT NULL,
  organization_name varchar(100) NOT NULL
);


DROP TABLE IF EXISTS "Resource";
CREATE TABLE "Resource" (
  "project_id" integer NOT NULL,
  "resource_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "resource_name" integer NOT NULL
);


DROP TABLE IF EXISTS "Access";
CREATE TABLE "Access" (
  "project_id" integer NOT NULL,
  "access_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "access_value" text NOT NULL,
  "role_id" integer NOT NULL,
  FOREIGN KEY ("project_id") REFERENCES "Project" ("id"),
  FOREIGN KEY ("role_id") REFERENCES "Role" ("project_id")
);


DROP TABLE IF EXISTS "email";
CREATE TABLE "email" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "project_id" integer NOT NULL,
  "email" text NOT NULL,
  "firstname" text NULL,
  "lastname" text NULL,
  "created" integer NOT NULL,
  "updated" integer NOT NULL,
  FOREIGN KEY ("project_id") REFERENCES "Project" ("id")
);


DROP TABLE IF EXISTS "consent";
CREATE TABLE "consent" (
  "email_id" integer NOT NULL,
  "project_id" integer NOT NULL,
  "created" integer NOT NULL,
  "updated" integer NOT NULL,
  FOREIGN KEY ("project_id") REFERENCES "Project" ("id")
);


DROP TABLE IF EXISTS "Role";
CREATE TABLE "Role" (
  "role_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "project_id" integer NOT NULL,
  "role_name" text NOT NULL,
  "role_parent" text NULL
);


DROP TABLE IF EXISTS "Allow";
CREATE TABLE "Allow" (
  "allow_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "role_id" integer NOT NULL,
  "privileges" text NOT NULL
);


DROP TABLE IF EXISTS "Deny";
CREATE TABLE "Deny" (
  "deny_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "role_id" integer NOT NULL,
  "privileges" text NOT NULL
);


DROP TABLE IF EXISTS "Basket";
CREATE TABLE "Basket" (
  "id" integer NULL PRIMARY KEY AUTOINCREMENT,
  "product_id" integer NULL,
  "user_id" integer NULL,
  "value" text NULL,
  "created" numeric NOT NULL,
  "updated" numeric NOT NULL,
  "hide" integer NOT NULL,
  "count" integer NOT NULL,
  "key" integer NOT NULL,
  "option" text NOT NULL
);


DROP TABLE IF EXISTS "ShopAttribute";
CREATE TABLE "ShopAttribute" (
  "sa_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "product_id" integer NULL,
  "sa_name" text NOT NULL,
  "stock" integer NULL,
  "user_id" integer NULL,
  "created" integer NULL,
  "updated" integer NULL,
  FOREIGN KEY ("user_id") REFERENCES "users" ("id") ON DELETE NO ACTION ON UPDATE NO ACTION
);


DROP TABLE IF EXISTS "ShopAttributeChoice";
CREATE TABLE "ShopAttributeChoice" (
  "sac_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "sa_id" integer NOT NULL,
  "sac_name" text NOT NULL,
  "created" integer NULL,
  "updated" integer NULL,
  "user_id" integer NULL, "sac_default" integer NULL,
  FOREIGN KEY ("user_id") REFERENCES "users" ("id") ON DELETE NO ACTION ON UPDATE NO ACTION
);


DROP TABLE IF EXISTS "ProductAttribute";
CREATE TABLE "ProductAttribute" (
  "pa_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "product_id" integer NULL,
  "pa_stock" integer NULL,
  "user_id" integer NULL,
  "created" integer NULL,
  "updated" integer NULL,
  "sa_id" integer NULL,
  FOREIGN KEY ("user_id") REFERENCES "users" ("id") ON DELETE NO ACTION ON UPDATE NO ACTION,
  FOREIGN KEY ("sa_id") REFERENCES "ShopAttribute" ("sa_id") ON DELETE NO ACTION ON UPDATE NO ACTION
);

CREATE INDEX "ProductOption_product_id" ON "ProductAttribute" ("pa_id");

CREATE INDEX "ProductOption_option_id" ON "ProductAttribute" ("user_id");


DROP TABLE IF EXISTS "ProductAttributeChoice";
CREATE TABLE "ProductAttributeChoice" (
  "pac_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "pa_id" integer NOT NULL,
  "created" integer NULL,
  "updated" integer NULL,
  "user_id" integer NULL,
  "sac_id" integer NULL,
  FOREIGN KEY ("pa_id") REFERENCES "ProductAttribute" ("pa_id"),
  FOREIGN KEY ("pa_id") REFERENCES "ProductAttribute" ("pa_id") ON DELETE NO ACTION ON UPDATE NO ACTION,
  FOREIGN KEY ("user_id") REFERENCES "users" ("id") ON DELETE NO ACTION ON UPDATE NO ACTION,
  FOREIGN KEY ("sac_id") REFERENCES "ShopAttributeChoice" ("sac_id") ON DELETE NO ACTION ON UPDATE NO ACTION,
  FOREIGN KEY ("pa_id") REFERENCES "ShopAttribute" ("sa_id") ON DELETE NO ACTION ON UPDATE NO ACTION
);


DROP TABLE IF EXISTS "Delivery";
CREATE TABLE "Delivery" (
  "delivery_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "price" integer NOT NULL,
  "weight" real NOT NULL
);


DROP TABLE IF EXISTS "ProductFile";
CREATE TABLE "ProductFile" (
  "productfile_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "product_id" integer NOT NULL,
  "productfile_name" text NOT NULL,
  "productfile_position" integer NOT NULL
);


DROP TABLE IF EXISTS "Topic";
CREATE TABLE "Topic" (
  "id" integer NULL PRIMARY KEY AUTOINCREMENT,
  "project_id" integer NULL,
  "user_id" integer NULL,
  "name" text NOT NULL,
  "content" text NULL,
  "hide" integer NULL,
  "rss" integer NULL,
  "comment" integer NULL,
  "created" numeric NOT NULL,
  "updated" numeric NOT NULL,
  "translate_index" integer NULL,
  "language" text NULL,
  "topic_id" integer NULL,
  "home" integer NULL,
  "lft" integer NULL,
  "rgt" integer NULL
);


DROP TABLE IF EXISTS "EmailData";
CREATE TABLE "EmailData" (
  "ed_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "ed_title" integer NOT NULL,
  "ed_content" integer NOT NULL,
  "ed_hide" integer NOT NULL
);


DROP TABLE IF EXISTS "Acl";
CREATE TABLE "Acl" (
  "user_id" integer NOT NULL,
  "project_id" integer NOT NULL,
  "role_id" integer NULL,
  "id" integer NULL PRIMARY KEY AUTOINCREMENT,
  FOREIGN KEY ("user_id") REFERENCES "users" ("id") ON DELETE NO ACTION ON UPDATE NO ACTION
);

CREATE INDEX "Acl_user_id" ON "Acl" ("user_id");


DROP TABLE IF EXISTS "Product2Topic";
CREATE TABLE "Product2Topic" (
  "product_id" integer NOT NULL,
  "topic_id" integer NOT NULL
, "created" integer NULL);


DROP TABLE IF EXISTS "users";
CREATE TABLE "users" (
  "id" integer NULL PRIMARY KEY AUTOINCREMENT,
  "project_id" integer NULL,
  "firstname" text NULL,
  "username" text NULL,
  "salt" text NOT NULL,
  "role" text NOT NULL,
  "password" text NOT NULL,
  "newPassword" text NULL,
  "email" text NOT NULL,
  "is_active" integer NOT NULL,
  "note" text NULL,
  "address" text NULL,
  "city" text NULL,
  "postal" text NULL,
  "phone" text NULL,
  "created" numeric NOT NULL,
  "updated" numeric NOT NULL
);