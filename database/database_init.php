<?php 

  require("database.php");

  $create_USERS_table = 'CREATE TABLE IF NOT EXISTS users (id_user int(8) UNSIGNED PRIMARY KEY AUTO_INCREMENT, first_name varchar(25) NOT NULL, last_name varchar(30) NOT NULL, email varchar(50) NOT NULL, password varchar(255) NOT NULL, admin boolean NOT NULL) DEFAULT "0"'; 

  $create_TOKENS_table = 'CREATE TABLE IF NOT EXISTS tokens (id_token int(8) UNSIGNED PRIMARY KEY AUTO_INCREMENT, id_device int(8) UNSIGNED NOT NULL, jwt_token varchar(255) NOT NULL, expiry_date int(20) UNSIGNED)';

  $create_DEVICES_table = 'CREATE TABLE IF NOT EXISTS devices (id_device int(8) unsigned PRIMARY KEY AUTO_INCREMENT, id_user int(8) unsigned NOT NULL, device_hash varchar(255) NOT NULL, last_activity int(20) UNSIGNED)';


  Db::fetch($create_USERS_table);
  Db::fetch($create_TOKENS_table);
  Db::fetch($create_DEVICES_table);
