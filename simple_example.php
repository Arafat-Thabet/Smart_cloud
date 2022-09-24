<?php
require_once 'Database.php';
use Smart_cloud\Database\Database;
define('HOST_NAME',"localhost");
define('USER_NAME',"root");
define('PASSWORD',"");
define('DB_BREFIX',"");
$Database =new Database();
// $company_database_name="smart";
// $user_id=1;
// echo $Database->generateApiToken($company_database_name,$user_id);
// check request api token by api  oauth2 token
// $Database->getApiDatabase();
?>