<?php
namespace App\Config;

use mysqli;

/* Database credentials. Assuming you are running MySQL
server with default setting (user 'root' with no password) */
class DbConfig {

    public function __construct(){}
    
    /* Attempt to connect to MySQL database */
    public function openConnect() : mysqli{
        $link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME)or die("Connect failed: %s\n". $link -> error);
        return $link;
    
    }
    
    public function closeConnect(mysqli $link){
        $link->close();
    }

}
?>