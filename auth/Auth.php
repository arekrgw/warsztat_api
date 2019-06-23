<?php
  require('../vendor/autoload.php');
  require("../database/database.php");
  require("Helpers.php");

  use \Firebase\JWT\JWT;

  class Auth extends Helpers{
    private $jwt;
    private $newJwt = null;
    private $device;
    
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
    //main authorization function. It sets $authenticated var to true or false 
    public function authorization() {
      
      $authenticate_user_query = 'SELECT * FROM tokens INNER JOIN devices ON tokens.id_device=devices.id_device INNER JOIN users ON devices.id_user=users.id_user WHERE tokens.jwt_token=:jwt';
      $query_parameters = array (
        "jwt" => $this->jwt
      );
      
      $query_result = Db::fetch($authenticate_user_query, $query_parameters);
      if($query_result->rowCount() == 1) {
        $decoded_jwt = (array) JWT::decode($this->jwt, self::$key, array('HS256'));
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
            $this->newJwt = self::generateJWT($query_result['id_user'], $query_result['id_device']);
            $update_query = 'UPDATE tokens SET jwt_token=:jwt, expiry_date=:expiry WHERE id_token=:id_token';
            $update_params = array(
              "jwt" => $this->newJwt,
              "expiry" => self::generateJWT(null,null, true),
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

      return $this->authenticated;
    }

    public function new_jwt_token() {
      return $this->newJwt ? $this->newJwt : false; 

    }
  }


  //testing the class

  $arek = new Auth;
  echo $arek->authenticated."\n";
  echo $arek->admin."\n";
  echo $arek->new_jwt_token() ? $arek->new_jwt_token() : "BRAK";