<?php
  require("../vendor/autoload.php");
  require("../database/database.php");
  require("../api_config.php");
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
        $this->password = self::returnHashedPassword(htmlspecialchars($_POST['password']));
        $this->email = htmlspecialchars($_POST['email']);
        if($this->searchForTheUser()) {
          if(isset($_COOKIE['device'])) {
            $this->getDeviceId($_COOKIE['device']);
          }
          else {
            $this->createNewDevice();
          }
          $this->set_JWT_and_cookie();


        }
        else $this->logged = false;
      }
    }

    public function searchForTheUser() {
      $checkQuery = "SELECT * FROM users WHERE users.email = :email AND users.password = :password";
      $checkParams = array(
        "email" => $this->email,
        "password" => $this->password
      );
      $checkResult = Db::fetch($checkQuery, $checkParams);

      if($checkResult->rowCount() == 1) {
        $checkResult = $checkResult->fetch(PDO::FETCH_ASSOC);
        $this->userId = $checkResult['id_user'];
        return true;
      }
      else return false;
    }

    public function set_JWT_and_cookie() {
      $this->jwt = self::generateJWT($this->userId, $this->deviceId);
      $expiration = self::generateJWT(null, null, true);
      $checkIfExist = "SELECT * FROM tokens INNER JOIN devices ON tokens.id_device=devices.id_device WHERE tokens.id_device=:device AND devices.id_user=:user";
      $checkParams = array(
        "device" => $this->deviceId,
        "user" => $this->userId
      );
      $checkJWTres = Db::fetch($checkIfExist, $checkParams);

      if($checkJWTres->rowCount() == 0) {
        //insert
        $setJWT = "INSERT INTO tokens VALUES (null, :device, :jwt, :expiration)";
        $setParams = array(
          "device" => $this->deviceId,
          "jwt" => $this->jwt,
          "expiration" => $expiration
        );
        Db::fetch($setJWT, $setParams);

      }
      else {
        //update
        $setJWT = "UPDATE tokens SET jwt_token = :jwt, expiry_date=:expiration WHERE id_token = :tokenId";
        $checkJWTres = $checkJWTres->fetch(PDO::FETCH_ASSOC);
        $setParams = array(
          "tokenId" => $checkJWTres['id_token'],
          "jwt" => $this->jwt,
          "expiration" => $expiration
        );
        Db::fetch($setJWT, $setParams);
      }

      //HTTP ONLY COOKIE
      setcookie("jwt", $this->jwt, self::generateJWT(null, null, true), "/", $configuration['domain'], false, true);

    }

    public function createNewDevice() {
      $deviceHash = self::generateRandomString(40);
      //setcookie()
      $insertNewDeviceQuery = "INSERT INTO devices VALUES (null, :user, :hash, :last)";
      $insertNewDeviceParams = array(
        "user" => $this->userId,
        "hash" => $deviceHash,
        "last" => time()
      );
      Db::fetch($insertNewDeviceQuery, $insertNewDeviceParams);
      setcookie("device", $deviceHash, mktime(0, 0, 1, 12, 31, 2038), "/", $configuration['domain'], false, true);


      $this->getDeviceId($deviceHash);
    }

    public function getDeviceId($deviceHash) {
      $getDevId = "SELECT * FROM devices WHERE device_hash=:hash AND id_user = :user";
      $getParams = array (
        "hash" => $deviceHash,
        "user" => $this->userId
      );
      $getResult = Db::fetch($getDevId, $getParams)->fetch(PDO::FETCH_ASSOC);
      $this->deviceId = $getResult['id_device'];
    }
  }

  $arek = new Login;
  echo $arek->userId."\n";
  echo $arek->deviceId;