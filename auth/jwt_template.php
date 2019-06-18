<?php
  require("../vendor/autoload.php");

  use \Firebase\JWT\JWT;

  function generateJWTToken($user_id, $device_id){
    $key = 'C90E0D1695F6CE93E80452930CBD3562DCFB63BB656B4846F13E20C2973A2AA119CE421890C91A2823EBF672CAF178FC97583C5374C51AFB7B67B7D0CCE26603';
    $token = array(
      "expiration" => time() + 60 * 60 * 24 * 7,
      "refresh_expiration" => time() - 10,
      "user_id" => $user_id,
      "device_id" => $device_id

    );
    return JWT::encode($token, $key);
  }

  echo generateJWTToken(1,1);