<?php

namespace Smart_cloud\Database;

require_once __DIR__ . '/Authorization_Token.php';

use Smart_cloud\Authorization_Token\Authorization_Token;
use Exception;
use mysqli;

class Database
{
  /**
   * Smart handle multi-database connections
   *
   * @category                Library
   * @author                 Arafat Thabet
   * @version         2.0.1
   */
  private $company = 'empty';
  static $SMART;

  public function __construct()
  {
    self::$SMART = $this;
  }

  public static function setDatabase($name = "empty")
  {

    $time = time();
    $session_id = md5(time() . "_smart");
    setcookie("_s_", $session_id, $time + 1800, "/");
    if (!isset($_COOKIE['_client']) or $_COOKIE['_client'] != $name) {
      $time = time();
      if (isset($_COOKIE['_client']))
        unset($_COOKIE['_client']);
      setcookie("_client", $name, $time + 5600000, "/");
    }
    return $session_id;
  }
  //return name of current database user
  public static function getDatabase()
  {
    return DB_BREFIX . self::$SMART->_returnRow()["database"];
  }
  public static function ApiDatabase()
  {
    return DB_BREFIX . self::$SMART->getApiDatabase();
  }
  private function _returnRow()
  {

    $return = array();
    $database = isset($_COOKIE["_client"]) ? $_COOKIE["_client"] : "empty";
    if (!isset($_COOKIE["_client"])) {
      $return["database"] = $database;
      return $return;
    }
    $database = str_replace(array("()", ".", "#", "@", " ", "!", "{", "}", "=", "+", "/", "$", "%", "'", '"', ")", "("), "", $database);
    $database = (strlen($database) > 2 ? $database : "empty");
    $return["database"] = $database;
    ini_set('display_errors', 0);
    error_reporting(0);
    $conn = new mysqli(HOST_NAME, USER_NAME, PASSWORD, DB_BREFIX . $database);
    if (!$conn->connect_error) {
      //if  valid database then save it ;
      $return["database"] = $database;
      return $return;
    } else {
      $return["database"] = 'empty';
      return $return;
    }
    return $return;
  }



  public function generateApiToken($company, $user_id)
  {
    $token_data['user_id'] = $user_id;
    $token_data['company'] = strtolower($company);
    // Generate Token
    $authorization_token = new Authorization_Token();
    $access_token = $authorization_token->generateToken($token_data);
    return $access_token;
  }
  public function returnApiDatabase()
  {
    $authorization_token = new Authorization_Token();
    $this->company = 'empty';

    //  User Token Validation
    $is_valid_token = $authorization_token->validateToken();
    if (!empty($is_valid_token) and $is_valid_token['status'] === true) {
      $company = $is_valid_token['data']->company;

      $this->company =  $company;
      $this->user = $is_valid_token['data']->user_id;
      $this->company = strlen($company) > 0 ? $company : $this->company;
    } else {
      $this->company = $this->company;
    }

    return $this->company;
  }

  public function _Get_Post_content()
  {
    $post_object = json_decode(file_get_contents('php://input'));
    if (is_object($post_object)) :
      $_POST = get_object_vars($post_object);
    endif;

    return $this;
  }

  public function getApiDatabase()
  {
    $this->_Get_Post_content();
    if (isset($_POST['login_company']) && !empty($_POST['login_company'])) {
      return DB_BREFIX . $_POST['login_company'];
    }
    $db = $this->returnApiDatabase();
    try {
      ini_set('display_errors',0);
      error_reporting(0);
      $conn = new mysqli(HOST_NAME, USER_NAME, PASSWORD, DB_BREFIX . $db);
      if (!$conn->connect_error) {
        //if  valid database then save it ;
        return   DB_BREFIX . $db;
      } else {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(401);
        echo json_encode(array(
          "success" => FALSE, 'logged_in' => false,
          "message" => "invalid connection"
        ));
        exit;
      }
    } catch (Exception $e) {
      header('Content-Type: application/json; charset=utf-8');
      http_response_code(401);
      echo json_encode(array(
        "success" => FALSE, 'logged_in' => false,
        "message" => "invalid connection"
      ));
      exit;
    }
  }
}
