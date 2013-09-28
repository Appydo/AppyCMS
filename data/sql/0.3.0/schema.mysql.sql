CREATE TABLE Access (
  project_id int(11) NOT NULL,
  access_id int(11) NOT NULL AUTO_INCREMENT,
  access_value text NOT NULL,
  role_id int(11) NOT NULL,
  PRIMARY KEY (access_id)
) ENGINE=InnoDB;

CREATE TABLE Acl (
  user_id int(11) NOT NULL,
  project_id int(11) NOT NULL,
  role_id int(11) NOT NULL,
  KEY user_id (user_id),
  KEY project_id (project_id),
  KEY role_id (role_id)
) ENGINE=InnoDB;

CREATE TABLE Allow (
  allow_id int(11) NOT NULL AUTO_INCREMENT,
  project_id int(11) NOT NULL,
  allow_name text NOT NULL,
  PRIMARY KEY (allow_id)
) ENGINE=InnoDB;

CREATE TABLE Auth (
  id int(11) NOT NULL AUTO_INCREMENT,
  identity text NOT NULL,
  success int(11) NOT NULL,
  `date` decimal(10,0) NOT NULL,
  ip text,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE BankData (
  id int(11) NOT NULL AUTO_INCREMENT,
  `data` text,
  created int(11) NOT NULL,
  updated int(11) NOT NULL,
  price text NOT NULL,
  `code` text NOT NULL,
  bank_code text NOT NULL,
  cvv_code text NOT NULL,
  command text NOT NULL,
  customer text NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE BankOrder (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) DEFAULT NULL,
  description longtext,
  count int(11) NOT NULL,
  price double DEFAULT NULL,
  created datetime NOT NULL,
  updated datetime NOT NULL,
  hide tinyint(1) NOT NULL,
  payment tinyint(1) NOT NULL,
  shipment int(11) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE Basket (
  id int(11) NOT NULL AUTO_INCREMENT,
  product_id int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  `value` text,
  created decimal(10,0) NOT NULL,
  updated decimal(10,0) NOT NULL,
  hide int(11) NOT NULL,
  count int(11) NOT NULL,
  session_key int(11) NOT NULL,
  attributes text NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE BasketOrder (
  id int(11) NOT NULL AUTO_INCREMENT,
  bankorder_id int(11) DEFAULT NULL,
  product_id int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  `value` text,
  count int(11) NOT NULL,
  created decimal(10,0) NOT NULL,
  updated decimal(10,0) NOT NULL,
  hide int(11) NOT NULL,
  payment int(11) NOT NULL,
  attributes text,
  price text,
  delivery text,
  delay text,
  shipment int(11) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE Category (
  id int(11) NOT NULL AUTO_INCREMENT,
  project_id int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `string` longtext NOT NULL,
  created datetime NOT NULL,
  updated datetime NOT NULL,
  hide tinyint(1) NOT NULL,
  path varchar(1000) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE `Comment` (
  id int(11) NOT NULL AUTO_INCREMENT,
  topic_id int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  content longtext NOT NULL,
  hide tinyint(1) DEFAULT NULL,
  created int(11) NOT NULL,
  updated int(11) NOT NULL,
  PRIMARY KEY (id),
  KEY topic_id (topic_id),
  KEY user_id (user_id),
  KEY hide (hide)
) ENGINE=InnoDB;

CREATE TABLE consent (
  email_id int(11) NOT NULL,
  project_id int(11) NOT NULL,
  created int(11) NOT NULL,
  updated int(11) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE Delay (
  delay_id int(11) NOT NULL AUTO_INCREMENT,
  delay int(11) NOT NULL,
  created int(11) NOT NULL,
  updated int(11) NOT NULL,
  hide int(11) NOT NULL,
  delay_name text,
  delay_rule text,
  PRIMARY KEY (delay_id)
) ENGINE=InnoDB;

CREATE TABLE Delivery (
  delivery_id int(11) NOT NULL AUTO_INCREMENT,
  price int(11) NOT NULL,
  weight float NOT NULL,
  created int(11) NOT NULL,
  updated int(11) NOT NULL,
  hide int(11) NOT NULL,
  user_id int(11) NOT NULL,
  delivery_name int(11) DEFAULT NULL,
  delivery_delay int(11) DEFAULT NULL,
  delivery_rule int(11) DEFAULT NULL,
  PRIMARY KEY (delivery_id)
) ENGINE=InnoDB;

CREATE TABLE Deny (
  deny_id int(11) NOT NULL AUTO_INCREMENT,
  role_id int(11) NOT NULL,
  `privileges` text NOT NULL,
  PRIMARY KEY (deny_id)
) ENGINE=InnoDB;

CREATE TABLE email (
  id int(11) NOT NULL AUTO_INCREMENT,
  project_id int(11) NOT NULL,
  email text NOT NULL,
  firstname text,
  lastname text,
  created int(11) NOT NULL,
  updated int(11) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE EmailData (
  ed_id int(11) NOT NULL AUTO_INCREMENT,
  ed_title varchar(250) NOT NULL,
  ed_content text NOT NULL,
  ed_hide int(11) NOT NULL,
  PRIMARY KEY (ed_id),
  KEY ed_title (ed_title)
) ENGINE=InnoDB;

CREATE TABLE `Event` (
  id int(11) NOT NULL AUTO_INCREMENT,
  description longtext,
  username varchar(25) NOT NULL,
  email varchar(60) NOT NULL,
  address varchar(500) DEFAULT NULL,
  phone varchar(255) DEFAULT NULL,
  created datetime NOT NULL,
  updated datetime NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE HomePage (
  homepage_id int(11) NOT NULL AUTO_INCREMENT,
  topic_id int(11) NOT NULL,
  PRIMARY KEY (homepage_id),
  KEY topic_id (topic_id)
) ENGINE=InnoDB;

CREATE TABLE IPN (
  id int(11) NOT NULL AUTO_INCREMENT,
  category_id int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  bank varchar(255) NOT NULL,
  `value` longtext NOT NULL,
  price double NOT NULL,
  created datetime NOT NULL,
  updated datetime NOT NULL,
  hide tinyint(1) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE Log (
  id int(11) NOT NULL AUTO_INCREMENT,
  project_id int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  description text NOT NULL,
  created int(11) NOT NULL,
  updated int(11) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE Mail (
  id int(11) NOT NULL AUTO_INCREMENT,
  project_id int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  email varchar(255) NOT NULL,
  ip varchar(255) NOT NULL,
  message longtext NOT NULL,
  created datetime NOT NULL,
  updated datetime NOT NULL,
  hide tinyint(1) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE Menu (
  id int(11) NOT NULL AUTO_INCREMENT,
  menu_id int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  project_id int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  hide tinyint(1) DEFAULT NULL,
  created date NOT NULL,
  updated date NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE MenuExtern (
  id int(11) NOT NULL AUTO_INCREMENT,
  menu_id int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  project_id int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  url varchar(255) NOT NULL,
  position int(11) DEFAULT NULL,
  hide tinyint(1) DEFAULT NULL,
  created date NOT NULL,
  updated date NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE MenuIntern (
  id int(11) NOT NULL AUTO_INCREMENT,
  topic_id int(11) DEFAULT NULL,
  menu_id int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  project_id int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  position int(11) DEFAULT NULL,
  hide tinyint(1) DEFAULT NULL,
  created date NOT NULL,
  updated date NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE Newsletter (
  project_id int(11) NOT NULL,
  email text NOT NULL,
  firstname text,
  lastname text,
  created int(11) NOT NULL,
  updated int(11) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE organization (
  organization_id int(11) NOT NULL,
  organization_name varchar(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE passwordRecovery (
  pr_id int(11) NOT NULL AUTO_INCREMENT,
  pr_password int(11) NOT NULL,
  pr_salt int(11) NOT NULL,
  created int(11) NOT NULL,
  user_id int(11) NOT NULL,
  PRIMARY KEY (pr_id)
) ENGINE=InnoDB;

CREATE TABLE Payment (
  id int(11) NOT NULL AUTO_INCREMENT,
  category_id int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  bank varchar(255) NOT NULL,
  `value` longtext NOT NULL,
  price double NOT NULL,
  created datetime NOT NULL,
  updated datetime NOT NULL,
  hide tinyint(1) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE `Product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `topic_id` int(11) DEFAULT NULL,
  `name` tinytext NOT NULL,
  `description` text NOT NULL,
  `price` int(11) DEFAULT NULL,
  `created` int(11) NOT NULL,
  `updated` int(11) NOT NULL,
  `hide` tinyint(4) DEFAULT NULL,
  `path` tinytext,
  `stock` int(11) DEFAULT NULL,
  `discount` double DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `weight` int(11) DEFAULT NULL,
  `image` tinytext,
  `project_id` int(11) DEFAULT NULL,
  `image_path` tinytext,
  `image_name` tinytext,
  `ean13` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `topic_id` (`topic_id`),
  KEY `hide` (`hide`),
  KEY `project_id` (`project_id`),
  KEY `price` (`price`)
) ENGINE=InnoDB;

CREATE TABLE Product2Topic (
  product_id int(11) NOT NULL,
  topic_id int(11) NOT NULL,
  created int(11) DEFAULT NULL,
  KEY product_id (product_id),
  KEY topic_id (topic_id)
) ENGINE=InnoDB;

CREATE TABLE ProductAttribute (
  pa_id int(11) NOT NULL AUTO_INCREMENT,
  product_id int(11) DEFAULT NULL,
  pa_stock int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  created int(11) DEFAULT NULL,
  updated int(11) DEFAULT NULL,
  sa_id int(11) DEFAULT NULL,
  PRIMARY KEY (pa_id)
) ENGINE=InnoDB;

CREATE TABLE ProductAttributeChoice (
  pac_id int(11) NOT NULL AUTO_INCREMENT,
  pa_id int(11) NOT NULL,
  created int(11) DEFAULT NULL,
  updated int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  sac_id int(11) DEFAULT NULL,
  PRIMARY KEY (pac_id)
) ENGINE=InnoDB;

CREATE TABLE ProductFile (
  productfile_id int(11) NOT NULL AUTO_INCREMENT,
  product_id int(11) NOT NULL,
  productfile_name text NOT NULL,
  productfile_position int(11) NOT NULL,
  PRIMARY KEY (productfile_id)
) ENGINE=InnoDB;

CREATE TABLE ProductOptionName (
  product_id int(11) NOT NULL,
  `name` text NOT NULL,
  option_id int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (option_id)
) ENGINE=InnoDB;

CREATE TABLE ProductOrder (
  id int(11) NOT NULL AUTO_INCREMENT,
  created date NOT NULL,
  updated date NOT NULL,
  hide tinyint(1) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE `Project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(60) NOT NULL,
  `description` text,
  `information` text,
  `theme` varchar(50) DEFAULT NULL,
  `keywords` text,
  `banner` varchar(255) DEFAULT NULL,
  `note` text,
  `css` longtext,
  `url` varchar(255) DEFAULT NULL,
  `footer` varchar(255) DEFAULT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `hits` tinyint(1) DEFAULT NULL,
  `menu` tinyint(1) DEFAULT NULL,
  `comment` tinyint(1) DEFAULT NULL,
  `stat` tinyint(1) DEFAULT NULL,
  `log` tinyint(1) DEFAULT NULL,
  `contact` tinyint(1) DEFAULT NULL,
  `hide` tinyint(1) DEFAULT NULL,
  `ban` tinyint(1) DEFAULT NULL,
  `zone` varchar(50) DEFAULT NULL,
  `config` tinyint(1) DEFAULT NULL,
  `created` int(11) NOT NULL,
  `updated` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `name` (`name`),
  KEY `theme` (`theme`)
) ENGINE=InnoDB;

CREATE TABLE Resource (
  project_id int(11) NOT NULL,
  resource_id int(11) NOT NULL AUTO_INCREMENT,
  resource_name int(11) NOT NULL,
  PRIMARY KEY (resource_id)
) ENGINE=InnoDB;

CREATE TABLE Role (
  role_id int(11) NOT NULL AUTO_INCREMENT,
  project_id int(11) NOT NULL,
  role_name text NOT NULL,
  role_parent text,
  PRIMARY KEY (role_id)
) ENGINE=InnoDB;

CREATE TABLE Service (
  id int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  url varchar(1000) NOT NULL,
  `type` varchar(255) NOT NULL,
  hide tinyint(1) NOT NULL,
  created datetime NOT NULL,
  updated datetime NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE ShopAttribute (
  sa_id int(11) NOT NULL AUTO_INCREMENT,
  product_id int(11) DEFAULT NULL,
  sa_name text NOT NULL,
  stock int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  created int(11) DEFAULT NULL,
  updated int(11) DEFAULT NULL,
  PRIMARY KEY (sa_id)
) ENGINE=InnoDB;

CREATE TABLE ShopAttributeChoice (
  sac_id int(11) NOT NULL AUTO_INCREMENT,
  sa_id int(11) NOT NULL,
  sac_name text NOT NULL,
  created int(11) DEFAULT NULL,
  updated int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  sac_price double DEFAULT NULL,
  sac_default int(11) DEFAULT NULL,
  PRIMARY KEY (sac_id)
) ENGINE=InnoDB;

CREATE TABLE Stat (
  id int(11) NOT NULL AUTO_INCREMENT,
  project_id int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  url varchar(255) NOT NULL,
  `date` date NOT NULL,
  ip varchar(100) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE Tax (
  tax_id int(11) NOT NULL AUTO_INCREMENT,
  tax_name int(11) NOT NULL,
  tax_value int(11) NOT NULL,
  hide int(11) DEFAULT NULL,
  PRIMARY KEY (tax_id)
) ENGINE=InnoDB;

CREATE TABLE Topic (
  id int(11) NOT NULL AUTO_INCREMENT,
  project_id int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  `name` varchar(250) NOT NULL,
  content text,
  hide int(11) DEFAULT NULL,
  rss int(11) DEFAULT NULL,
  `comment` int(11) DEFAULT NULL,
  created int(11) NOT NULL,
  updated int(11) NOT NULL,
  translate_index int(11) DEFAULT NULL,
  `language` text,
  topic_id int(11) DEFAULT NULL,
  home int(11) DEFAULT NULL,
  lft int(11) DEFAULT NULL,
  rgt int(11) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY project_id (project_id),
  KEY user_id (user_id),
  KEY lft (lft),
  KEY rgt (rgt),
  KEY `name` (`name`),
  KEY hide (hide)
) ENGINE=InnoDB;

CREATE TABLE Topic_archive (
  id int(11) NOT NULL AUTO_INCREMENT,
  project_id int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  content longtext,
  hide tinyint(1) DEFAULT NULL,
  rss tinyint(1) DEFAULT NULL,
  `comment` tinyint(1) DEFAULT NULL,
  created datetime NOT NULL,
  updated datetime NOT NULL,
  translate_index bigint(20) DEFAULT NULL,
  `language` varchar(255) DEFAULT NULL,
  topic_id int(11) DEFAULT NULL,
  home tinyint(1) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE Trash (
  id int(11) NOT NULL AUTO_INCREMENT,
  project_id int(11) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  content longtext NOT NULL,
  hide tinyint(1) DEFAULT NULL,
  rss tinyint(1) DEFAULT NULL,
  `comment` tinyint(1) DEFAULT NULL,
  created datetime NOT NULL,
  updated datetime NOT NULL,
  translate_index bigint(20) DEFAULT NULL,
  `language` varchar(255) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY project_id (project_id),
  KEY user_id (user_id)
) ENGINE=InnoDB;

CREATE TABLE users (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) DEFAULT NULL,
  `firstname` varchar(200) DEFAULT NULL,
  `username` varchar(200) NOT NULL,
  `salt` varchar(30) NOT NULL,
  `role` int(11) NOT NULL,
  `password` varchar(50) NOT NULL,
  `newPassword` varchar(50) DEFAULT NULL,
  `email` varchar(50) NOT NULL,
  `is_active` int(11) NOT NULL,
  `note` text,
  `address` text,
  `city` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created` int(11) NOT NULL,
  `updated` int(11) NOT NULL,
  `postal` mediumint(9) DEFAULT NULL,
  `civility` varchar(3) DEFAULT NULL,
  `partner` int(11) DEFAULT NULL,
  `gts` int(11) DEFAULT NULL,
  `newsletter` int(11) DEFAULT NULL,
  `billing_address` text,
  `billing_city` varchar(120) DEFAULT NULL,
  `billing_phone` varchar(20) DEFAULT NULL,
  `billing_postal` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `is_active` (`is_active`),
  KEY `email` (`email`)
) ENGINE=InnoDB;

CREATE TABLE ProductImageResize (
  pir_id int(11) NOT NULL AUTO_INCREMENT,
  pir_name varchar(100) NULL,
  pir_width int(11) NOT NULL,
  pir_height int(11) NOT NULL,
  created int(11) NOT NULL,
  updated int(11) NOT NULL,
  pir_hide int(1) NOT NULL,
  PRIMARY KEY (pir_id)
) ENGINE=InnoDB;