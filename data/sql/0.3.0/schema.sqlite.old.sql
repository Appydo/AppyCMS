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

-- --------------------------------------------------------

--
-- Structure de la table Auth
--

CREATE TABLE Auth (
  id INTEGER PRIMARY KEY autoincrement,
  identity varchar(60) NOT NULL,
  success tinyint(1) NOT NULL,
  date datetime NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table BankData
--

CREATE TABLE BankData (
  id INTEGER PRIMARY KEY autoincrement,
  data longtext,
  created datetime NOT NULL,
  updated datetime NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table BankOrder
--

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

-- --------------------------------------------------------

--
-- Structure de la table Category
--

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

-- --------------------------------------------------------

--
-- Structure de la table Comment
--

CREATE TABLE Comment (
  id INTEGER PRIMARY KEY autoincrement,
  topic_id int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  content longtext NOT NULL,
  hide tinyint(1) DEFAULT NULL,
  created datetime NOT NULL,
  updated datetime NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table IPN
--

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

-- --------------------------------------------------------

--
-- Structure de la table Log
--

CREATE TABLE Log (
  id INTEGER PRIMARY KEY autoincrement,
  project_id int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  name varchar(255) NOT NULL,
  description longtext NOT NULL,
  created datetime NOT NULL,
  updated datetime NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table Mail
--

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

-- --------------------------------------------------------

--
-- Structure de la table Menu
--

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

-- --------------------------------------------------------

--
-- Structure de la table MenuExtern
--

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

-- --------------------------------------------------------

--
-- Structure de la table MenuIntern
--

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

-- --------------------------------------------------------

--
-- Structure de la table Payment
--

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

-- --------------------------------------------------------

--
-- Structure de la table Product
--

CREATE TABLE Product (
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
, "ean13" text NULL);

-- --------------------------------------------------------

--
-- Structure de la table ProductOrder
--

CREATE TABLE ProductOrder (
  id INTEGER PRIMARY KEY autoincrement,
  created date NOT NULL,
  updated date NOT NULL,
  hide tinyint(1) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table Project
--

CREATE TABLE Project (
  project_id INTEGER PRIMARY KEY autoincrement,
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

-- --------------------------------------------------------

--
-- Structure de la table Service
--

CREATE TABLE Service (
  id INTEGER PRIMARY KEY autoincrement,
  name varchar(255) NOT NULL,
  url varchar(1000) NOT NULL,
  type varchar(255) NOT NULL,
  hide tinyint(1) NOT NULL,
  created datetime NOT NULL,
  updated datetime NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table Stat
--

CREATE TABLE Stat (
  id INTEGER PRIMARY KEY autoincrement,
  project_id int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  url varchar(255) NOT NULL,
  date date NOT NULL,
  ip varchar(100) NOT NULL
);

-- --------------------------------------------------------

--
-- Structure de la table Topic
--

CREATE TABLE Topic (
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

-- --------------------------------------------------------

--
-- Structure de la table Trash
--

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

CREATE TABLE "Role" (
  "role_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "project_id" integer NOT NULL,
  "role_name" text NOT NULL,
  "role_parent" text NULL
);

CREATE TABLE "Acl" (
  "user_id" integer NOT NULL,
  "project_id" integer NOT NULL,
  "role_id" integer NULL
);

CREATE TABLE "Allow" (
  "allow_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "project_id" integer NOT NULL,
  "allow_name" text NOT NULL
);

CREATE TABLE "Tax" (
  "tax_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "tax_name" integer NOT NULL,
  "tax_value" integer NOT NULL
, "hide" integer NULL);

CREATE TABLE "EmailData" (
  "ed_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "ed_title" text NOT NULL,
  "ed_content" text NOT NULL,
  "ed_hide" integer NOT NULL
);

CREATE TABLE "ProductOptionName" (
  "product_id" integer NOT NULL,
  "name" text NOT NULL,
  "option_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT
);

CREATE TABLE "ShopAttribute" (
  "sa_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "product_id" integer NULL,
  "sa_name" text NOT NULL,
  "stock" integer NULL,
  "user_id" integer NULL,
  "created" integer NULL,
  "updated" integer NULL
);

CREATE TABLE "ProductAttribute" (
  "pa_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "product_id" integer NULL,
  "pa_stock" integer NULL,
  "user_id" integer NULL,
  "created" integer NULL,
  "updated" integer NULL,
  "sa_id" integer NULL
);

CREATE TABLE "ProductAttributeChoice" (
  "pac_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "pa_id" integer NOT NULL,
  "created" integer NULL,
  "updated" integer NULL,
  "user_id" integer NULL,
  "sac_id" integer NULL
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

CREATE TABLE "Delay" (
  "delay_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "delay" integer NOT NULL,
  "created" integer NOT NULL,
  "updated" integer NOT NULL,
  "hide" integer NOT NULL,
  "delay_name" text NULL
, "delay_rule" text NULL);

CREATE TABLE "ShopAttributeChoice" (
  "sac_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "sa_id" integer NOT NULL,
  "sac_name" text NOT NULL,
  "created" integer NULL,
  "updated" integer NULL,
  "user_id" integer NULL,
  "sac_price" real NULL,
  "sac_default" integer NULL,
  FOREIGN KEY ("user_id") REFERENCES "users" ("id") ON DELETE NO ACTION ON UPDATE NO ACTION
);

CREATE TABLE "BasketOrder" (
  "id" integer NULL PRIMARY KEY AUTOINCREMENT,
  "bankorder_id" integer NULL,
  "product_id" integer NULL,
  "user_id" integer NULL,
  "value" text NULL,
  "count" integer NOT NULL,
  "created" numeric NOT NULL,
  "updated" numeric NOT NULL,
  "hide" integer NOT NULL,
  "payment" integer NOT NULL,
  "attributes" text NULL,
  "price" text NULL,
  "delivery" text NULL,
  "delay" text NULL
, "shipment" integer NULL);

CREATE TABLE "ProductFile" (
  "productfile_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "product_id" integer NOT NULL,
  "productfile_name" text NOT NULL,
  "productfile_position" integer NOT NULL
);

CREATE TABLE "Product2Topic" (
  "product_id" integer NOT NULL,
  "topic_id" integer NOT NULL
, "created" integer NULL);

CREATE TABLE "Delivery" (
  "delivery_id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "price" integer NOT NULL,
  "weight" real NOT NULL,
  "created" integer NOT NULL,
  "updated" integer NOT NULL,
  "hide" integer NOT NULL,
  "user_id" integer NOT NULL, "delivery_name" integer NULL, "delivery_delay" integer NULL, "delivery_rule" integer NULL,
  FOREIGN KEY ("user_id") REFERENCES "users" ("id")
);