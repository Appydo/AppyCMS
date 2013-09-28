CREATE TABLE users (
  id INTEGER PRIMARY KEY autoincrement,
  project_id int(11) DEFAULT NULL,
  username varchar(30) NOT NULL,
  salt varchar(40) NOT NULL,
  role varchar(40) NOT NULL,
  password varchar(40) NOT NULL,
  newPassword varchar(40) DEFAULT NULL,
  email varchar(60) NOT NULL,
  is_active tinyint(1) NOT NULL,
  note longtext,
  address longtext,
  phone varchar(255) DEFAULT NULL,
  created datetime NOT NULL,
  updated datetime NOT NULL
);


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
);


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
, "attributes" text NULL);


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


CREATE TABLE Comment (
  id INTEGER PRIMARY KEY autoincrement,
  topic_id int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  content longtext NOT NULL,
  hide tinyint(1) DEFAULT NULL,
  created datetime NOT NULL,
  updated datetime NOT NULL
);


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


CREATE TABLE Log (
  id INTEGER PRIMARY KEY autoincrement,
  project_id int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  name varchar(255) NOT NULL,
  description longtext NOT NULL,
  created datetime NOT NULL,
  updated datetime NOT NULL
);


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


CREATE TABLE ProductOrder (
  id INTEGER PRIMARY KEY autoincrement,
  created date NOT NULL,
  updated date NOT NULL,
  hide tinyint(1) NOT NULL
);


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


CREATE TABLE Service (
  id INTEGER PRIMARY KEY autoincrement,
  name varchar(255) NOT NULL,
  url varchar(1000) NOT NULL,
  type varchar(255) NOT NULL,
  hide tinyint(1) NOT NULL,
  created datetime NOT NULL,
  updated datetime NOT NULL
);


CREATE TABLE Stat (
  id INTEGER PRIMARY KEY autoincrement,
  project_id int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  url varchar(255) NOT NULL,
  date date NOT NULL,
  ip varchar(100) NOT NULL
);


CREATE TABLE Topic (
  id INTEGER PRIMARY KEY autoincrement,
  project_id int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  name varchar(100) NOT NULL,
  content longtext,
  hide tinyint(1) DEFAULT NULL,
  rss tinyint(1) DEFAULT NULL,
  comment tinyint(1) DEFAULT NULL,
  created datetime NOT NULL,
  updated datetime NOT NULL,
  translate_index bigint(20) DEFAULT NULL,
  language varchar(255) DEFAULT NULL,
  topic_id int(11) DEFAULT NULL,
  home tinyint(1) DEFAULT NULL
);


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


CREATE TABLE "Auth" (
  "id" integer NULL PRIMARY KEY AUTOINCREMENT,
  "identity" text NOT NULL,
  "success" integer NOT NULL,
  "date" numeric NOT NULL
, "ip" text NULL);


CREATE TABLE "Type" (
  "id_type" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "libelle_type" text(50) NOT NULL
);


CREATE TABLE "Participe" (
  "id_participe" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "id_user" integer NOT NULL,
  "id_evenement" integer NOT NULL,
  FOREIGN KEY ("id_user") REFERENCES "users" ("id"),
  FOREIGN KEY ("id_evenement") REFERENCES "Evenement" ("id_evenement")
);


CREATE TABLE "Accessibilite" (
  "id_accessibilite" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "libelle_accessibilite" integer NOT NULL
);


CREATE TABLE "Circulation" (
  "id_circulation" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "libelle_circulation" integer NOT NULL
);


CREATE TABLE "Guidage" (
  "id_guidage" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "libelle_guidage" integer NOT NULL
);


CREATE TABLE "Guichet" (
  "id_guichet" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "libelle_guichet" text NOT NULL
);


CREATE TABLE "Sanitaire" (
  "id_sanitaire" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "libelle_sanitaire" integer NOT NULL
);


CREATE TABLE "Emplacement_F" (
  "id_emplacement" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "libelle_emplacement" text NOT NULL
);


CREATE TABLE "Lieu" (
  "id_lieu" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "libelle_lieu" text NOT NULL,
  "adresse" text NULL,
  "code_postal" integer NULL,
  "ville" text NULL,
  "type_acces" integer NULL,
  "type_circulation" integer NULL,
  "guichet_accueil" integer NULL,
  "sanitaire" integer NULL,
  "emplacement_fauteuil" integer NULL,
  FOREIGN KEY ("emplacement_fauteuil") REFERENCES "Emplacement_F" ("id_emplacement") ON DELETE NO ACTION ON UPDATE NO ACTION,
  FOREIGN KEY ("sanitaire") REFERENCES "Sanitaire" ("id_sanitaire") ON DELETE NO ACTION ON UPDATE NO ACTION,
  FOREIGN KEY ("guichet_accueil") REFERENCES "Guichet" ("id_guichet") ON DELETE NO ACTION ON UPDATE NO ACTION,
  FOREIGN KEY ("type_circulation") REFERENCES "Circulation" ("id_circulation") ON DELETE NO ACTION ON UPDATE NO ACTION,
  FOREIGN KEY ("type_acces") REFERENCES "Accessibilite" ("id_accessibilite") ON DELETE NO ACTION ON UPDATE NO ACTION
);


CREATE TABLE "Evenement" (
  "id_evenement" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "libelle_evenement" text NOT NULL,
  "message" text NULL,
  "date_evenement" integer NOT NULL,
  "date_fin_evenement" integer NULL,
  "tarif" integer NULL,
  "adresse" text NOT NULL,
  "id_type" integer NOT NULL,
  "id_lieu" integer NOT NULL,
  "id_user" integer NOT NULL,
  FOREIGN KEY ("id_user") REFERENCES "users" ("id") ON DELETE NO ACTION ON UPDATE NO ACTION,
  FOREIGN KEY ("id_type") REFERENCES "Type" ("id_type") ON DELETE NO ACTION ON UPDATE NO ACTION,
  FOREIGN KEY ("id_lieu") REFERENCES "Lieu" ("id_lieu") ON DELETE NO ACTION ON UPDATE NO ACTION,
  FOREIGN KEY ("id_user") REFERENCES "users" ("id") ON DELETE NO ACTION ON UPDATE NO ACTION
);


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
);


CREATE TABLE "Newsletter" (
  "project_id" integer NOT NULL,
  "email" text NOT NULL,
  "firstname" text NULL,
  "lastname" text NULL,
  "created" integer NOT NULL,
  "updated" integer NOT NULL
);


CREATE TABLE "ProductOptionName" (
  "product_id" integer NOT NULL,
  "name" text NOT NULL,
  "option_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT
);


CREATE TABLE organization (
  organization_id int(11) NOT NULL,
  organization_name varchar(100) NOT NULL
);


CREATE TABLE "Resource" (
  "project_id" integer NOT NULL,
  "resource_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "resource_name" integer NOT NULL
);


CREATE TABLE "Access" (
  "project_id" integer NOT NULL,
  "access_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "access_value" text NOT NULL,
  "role_id" integer NOT NULL,
  FOREIGN KEY ("project_id") REFERENCES "Project" ("id"),
  FOREIGN KEY ("role_id") REFERENCES "Role" ("project_id")
);


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


CREATE TABLE "consent" (
  "email_id" integer NOT NULL,
  "project_id" integer NOT NULL,
  "created" integer NOT NULL,
  "updated" integer NOT NULL,
  FOREIGN KEY ("project_id") REFERENCES "Project" ("id")
);


CREATE TABLE "infomail" (
  "infomail_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "code" text NOT NULL,
  "email" integer NOT NULL,
  FOREIGN KEY ("email") REFERENCES "email" ("id")
);


CREATE TABLE "infoxygen" (
  "infoxygen_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "code" text NOT NULL,
  "message" text NULL,
  "topic_id" integer NULL
);


CREATE TABLE "Acl" (
  "user_id" integer NOT NULL,
  "project_id" integer NOT NULL,
  "role_id" integer NULL,
  FOREIGN KEY ("user_id") REFERENCES "users" ("id") ON DELETE NO ACTION ON UPDATE NO ACTION,
  PRIMARY KEY ("role_id")
);


CREATE TABLE "Role" (
  "role_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "project_id" integer NOT NULL,
  "role_name" text NOT NULL,
  "role_parent" text NULL
);


CREATE TABLE "Allow" (
  "allow_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "role_id" integer NOT NULL,
  "privileges" text NOT NULL
);


CREATE TABLE "Deny" (
  "deny_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "role_id" integer NOT NULL,
  "privileges" text NOT NULL
);


CREATE TABLE "Blacklist" (
  "backlist_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "brand_id" integer NOT NULL,
  "message" text NOT NULL
);


CREATE TABLE "Blacklist_brand" (
  "brand_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "brand_name" text NOT NULL,
  "brand_description" text NULL,
  "brand_url" text NULL
, "brand_img" text NULL);


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


CREATE TABLE "ShopAttributeChoice" (
  "sac_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "sa_id" integer NOT NULL,
  "sac_name" text NOT NULL,
  "created" integer NULL,
  "updated" integer NULL,
  "user_id" integer NULL,
  FOREIGN KEY ("user_id") REFERENCES "users" ("id") ON DELETE NO ACTION ON UPDATE NO ACTION
);


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
  "attributes" text NOT NULL
);


CREATE TABLE "Delivery" (
  "delivery_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "price" integer NOT NULL,
  "weight" real NOT NULL,
  "created" integer NOT NULL,
  "updated" integer NOT NULL,
  "hide" integer NOT NULL,
  "user_id" integer NOT NULL,
  FOREIGN KEY ("user_id") REFERENCES "users" ("id")
);


CREATE TABLE "BankData" (
  "id" integer NULL PRIMARY KEY AUTOINCREMENT,
  "data" text NULL,
  "created" integer NOT NULL,
  "updated" integer NOT NULL,
  "price" text NOT NULL,
  "code" text NOT NULL,
  "bank_code" text NOT NULL,
  "cvv_code" text NOT NULL,
  "command" text NOT NULL,
  "customer" text NOT NULL
);


CREATE TABLE Topic_archive (
  id INTEGER PRIMARY KEY autoincrement,
  project_id int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  name varchar(100) NOT NULL,
  content longtext,
  hide tinyint(1) DEFAULT NULL,
  rss tinyint(1) DEFAULT NULL,
  comment tinyint(1) DEFAULT NULL,
  created datetime NOT NULL,
  updated datetime NOT NULL,
  translate_index bigint(20) DEFAULT NULL,
  language varchar(255) DEFAULT NULL,
  topic_id int(11) DEFAULT NULL,
  home tinyint(1) DEFAULT NULL
);


CREATE TABLE "ProductFile" (
  "productfile_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "product_id" integer NOT NULL,
  "productfile_name" text NOT NULL,
  "productfile_position" integer NOT NULL
);