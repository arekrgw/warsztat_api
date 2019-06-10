<?php

  require("_config.php");

  class Db extends Database_configuration {
    
    public static function connection() {
      try {
        $db_connection = new PDO("mysql:host=".self::$host.";dbname=".self::$db_name, self::$db_user, self::$db_password);
      }
      catch(PDOException $e) {
        throw new PDOException("Blad polaczenia bazy danych");
      }
      return $db_connection;
    }

    public static function fetch($query, $params = array()) {
      //Establishing database connection 
      $connection = self::connection();

      $fetch_query = $connection->prepare($query);
      $fetch_query->execute($params);

      //Closing database connection
      $connection = null;

      return $fetch_query;
    }

  }
