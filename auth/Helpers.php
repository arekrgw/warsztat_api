<?php

  require('../vendor/autoload.php');

  use \Firebase\JWT\JWT;

  class Helpers {
    protected static $key = 'C90E0D1695F6CE93E80452930CBD3562DCFB63BB656B4846F13E20C2973A2AA119CE421890C91A2823EBF672CAF178FC97583C5374C51AFB7B67B7D0CCE26603';
    public static function generateJWT($user_id, $device_id, $returnExpiration = false) {
      $expiration = time() + 60*60*24*7;
      $token = array(
        "expiration" => $expiration,
        "refresh_expiration" => time() + 60*60*24,
        "user_id" => $user_id,
        "device_id" => $device_id,
        "randomizer" => rand()
      );
      if($returnExpiration) {
        return $expiration;
      }
      else return JWT::encode($token, self::$key);
    }

    public static function generateRandomString($length = 10) {
      $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
      $charactersLength = strlen($characters);
      $randomString = '';
      for ($i = 0; $i < $length; $i++) {
          $randomString .= $characters[rand(0, $charactersLength - 1)];
      }
      return $randomString;
    }

    public static function returnHashedPassword($password) {
      return hash("sha256", $password);
    }
    
  }

