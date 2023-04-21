<?php
namespace App\Models;

class LoginModel extends UserModel {

    public function __construct() {

    }

    public function getlogin() {
        
        if(!isset($_POST["submit-login"])) 
            return "invalid";
        
        if(isset($_POST["username"]) && isset($_POST["password"])){

            $this->readByName($_POST["username"]);
            //var_dump("info: [ ".date("Y-m-d H:i:s")." ] UserModel to String: ".$this->getPswEnc()." pass to eval: ".$_POST["password"]);

            if(strlen($this->getPswEnc()) > 1 && strcmp($_POST["password"],$this->getPswEnc()) === 0){
                $_SESSION["user"]= [ "type"=> "user_edifact", "data" => $this];
                return "login";
            } else 
                return "invalid";
            

        } else 
            return "invalid";
        
    }
}
?>