<?php
namespace App\Controllers;

use App\Models\LoginModel;
use App\Interfaces\ControllerInterface;

class LoginController implements ControllerInterface{
    public $model;

    public function __construct() {
        $this->model = new LoginModel();
    }

    public function invoke() {
        $reslt = $this->model->getlogin();     // it call the getlogin() function of model class and store the return value of this function into the reslt variable.
        if($reslt == "login"){
            require_once APP_ROOT."/Views/Afterlogin.php";
        }
        else{
            require_once APP_ROOT."/Views/login.php";
        }
    }
}
