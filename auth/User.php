<?php

  require("../database/database.php");

  class User {
    private $jwt;
    private $newJwt = null;
    private $device;
    public $admin = false;
    public $authenticated = false;

    public function __construct($helper_token_var = null, $helper_device_var = null){
      //Checking from which device is request
      if(isset($_COOKIE['jwt']) && isset($_COOKIE['device'])) {
        $this->jwt = $_COOKIE['jwt'];
        $this->device = $_COOKIE['device'];
        $this->authorization();
      }
      else {
        $this->authenticated = false;
      }
    }
    //function for generating hashes for JWT tokens
    public function generateRandomHash($length = 10) {
      $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
      $charactersLength = strlen($characters);
      $randomString = '';
      for ($i = 0; $i < $length; $i++) {
          $randomString .= $characters[rand(0, $charactersLength - 1)];
      }
      return $randomString;
    }

    //main authorization function. It sets $authenticated var to true or false 
    public function authorization() {
      $authenticate_user_query = 'SELECT * FROM tokens INNER JOIN devices ON tokens.id_device=devices.id_device INNER JOIN users ON devices.id_user=users.id_user WHERE tokens.jwt_token=:jwt AND device_hash=:device';
      $query_parameters = array (
        "jwt" => $this->jwt,
        "device" => $this->device
      );
      
      $query_result = Db::fetch($authenticate_user_query, $query_parameters);

      if($query_result->rowCount() == 1) {

        $query_result = $query_result->fetch(PDO::FETCH_ASSOC);
        if($query_result['admin']) $this->admin = true;

        if($query_result['expiry_date'] < time()) {
          $this->newJwt = $this->generateRandomHash(60);
          $insert_jwt_query = "UPDATE tokens SET jwt_token=:newJwt, expiry_date=:newExpiry  WHERE id_token=:tokenId";
          $jwt_update_params = array(
            "newJwt" => $this->newJwt,
            "newExpiry" => time() + 60 * 60 * 24,
            "tokenId" => $query_result["id_token"]
          );
          Db::fetch($insert_jwt_query, $jwt_update_params);
          $this->authenticated = true;

        }
      }
      else {
        $this->authenticated = false;
      }
    }

    public function new_jwt_token() {
      return $this->newJwt ? $this->newJwt : false; 

    }
  }


  //testing the class
  $arek = new User();
  echo $arek->authenticated;
  if($arek->new_jwt_token()) echo $arek->new_jwt_token();
