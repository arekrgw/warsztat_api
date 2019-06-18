<?php
  require('../vendor/autoload.php');
  require("../database/database.php");

  use \Firebase\JWT\JWT;

  class User {
    private $jwt;
    private $newJwt = null;
    private $device;
    protected $key = 'C90E0D1695F6CE93E80452930CBD3562DCFB63BB656B4846F13E20C2973A2AA119CE421890C91A2823EBF672CAF178FC97583C5374C51AFB7B67B7D0CCE26603';
    public $admin = false;
    public $authenticated = false;

    public function __construct($helper_token_var = null, $helper_device_var = null){
      //Checking from which device is request
      if(isset($_COOKIE['jwt'])) {
        $this->jwt = $_COOKIE['jwt'];
        $this->authorization();
      }
      else {
        $this->authenticated = false;
      }
    }
    //function for generating hashes for JWT tokens !!!! YET TO BE REMOVED BCS NO NEED!!!!!
    public function generateRandomHash($length = 10) {
      $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
      $charactersLength = strlen($characters);
      $randomString = '';
      for ($i = 0; $i < $length; $i++) {
          $randomString .= $characters[rand(0, $charactersLength - 1)];
      }
      return $randomString;
    }
    public function generateJWT($user_id, $device_id, $returnExpiration = false) {
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
      else return JWT::encode($token, $this->key);
    }

    //main authorization function. It sets $authenticated var to true or false 
    public function authorization() {
      
      $authenticate_user_query = 'SELECT * FROM tokens INNER JOIN devices ON tokens.id_device=devices.id_device INNER JOIN users ON devices.id_user=users.id_user WHERE tokens.jwt_token=:jwt';
      $query_parameters = array (
        "jwt" => $this->jwt
      );
      
      $query_result = Db::fetch($authenticate_user_query, $query_parameters);
      if($query_result->rowCount() == 1) {
        $decoded_jwt = (array) JWT::decode($this->jwt, $this->key, array('HS256'));
        $query_result = $query_result->fetch(PDO::FETCH_ASSOC);

        if(time() < $query_result['expiry_date']) {
          $last_activity_query = "UPDATE devices SET last_activity = :last WHERE id_device = :id_device";
          $last_activity_params = array(
            "last" => time(),
            "id_device" => $query_result['id_device']
          );
          Db::fetch($last_activity_query, $last_activity_params);
          if(time() < $decoded_jwt['refresh_expiration']) {
            $this->authenticated = true;
            $query_result['admin'] ? $this->admin = true : $this->admin = false;
          }
          else {
            $this->newJwt = $this->generateJWT($query_result['id_user'], $query_result['id_device']);
            $update_query = 'UPDATE tokens SET jwt_token=:jwt, expiry_date=:expiry WHERE id_token=:id_token';
            $update_params = array(
              "jwt" => $this->newJwt,
              "expiry" => $this->generateJWT(null,null, true),
              "id_token" => $query_result["id_token"]
            );
            Db::fetch($update_query, $update_params);
            $this->authenticated = true;
            $query_result['admin'] ? $this->admin = true : $this->admin = false;
          }
        }
        else {
          $this->authenticated = false;
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

  $arek = new User;
  echo $arek->authenticated."\n";
  echo $arek->admin."\n";
  echo $arek->new_jwt_token() ? $arek->new_jwt_token() : "BRAK";