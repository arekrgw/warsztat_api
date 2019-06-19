<?php
  require("../vendor/autoload.php");
  require("../database/database.php");
  require("Helpers.php");

  class Login extends Helpers {
    public $email;
    public $password;
    public $jwt;
    public $logged = false;
    public $userId;
    public $deviceId;

    public function __construct() {
      if(isset($_POST['email']) && isset($_POST['password'])) {
        $this->password = self::retrunHashedPassword(htmlspecialchars($_POST['password']));
        $this->email = htmlspecialchars($_POST['email']);
        if($this->checkIfUserExists()) {
          //deviceeeeee
          if(!isset($_COOKIE['device'])) {
            $this->createNewDevice($deviceHash);
          }
          else {
            //if device exist get the id

          }
          $this->set_JWT_and_cookie();
          $this->logged = true;
        }
        else {
          $this->logged = false;
        }
      }
    }

    public function checkIfUserExists() {
      $checkQuery = "SELECT * FROM users WHERE email=:email AND password=:password";
      $checkParams = array(
        "email" => $this->email,
        "password" => $this->password
      );
      $checkResult = Db::fetch($checkQuery, $checkParams);

      if($checkResult->rowCount() == 1) {
        $this->userId = $checkResult->fetch(PDO::FETCH_ASSOC)['id_user'];
        return true;
      }
    }

    public function set_JWT_and_cookie() {
      $this->jwt = self::generateJWT($this->userId, $this->deviceId);
      $expiration = self::generateJWT(null, null, true);
      $checkIfExist = "SELECT * FROM tokens INNER JOIN devices ON tokens.id_device=devices.id_device WHERE tokens.id_device=:device AND devices.id_user=:user";
      $checkParams = array(
        "device" => $this->deviceId,
        "user" => $this->userId
      );
      Db::fetch($checkIfExist, $checkParams);
      

    }

    public function createNewDevice($deviceHash) {
      $deviceHash = self::generateRandomString(40);
      //setcookie()
      $insertNewDeviceQuery = "INSERT INTO devices VALUES (null, :user, :hash, :last)";
      $insertNewDeviceParams = array(
        "user" => $this->userId,
        "hash" => $deviceHash,
        "last" => time()
      );
      $result = Db::fetch($insertNewDeviceQuery, $insertNewDeviceParams);
      $this->deviceId = $result->fetch(PDO::FETCH_ASSOC)['id_device'];

      //insert into database
    }
  }