<?php 
namespace App\Routes;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

// Routes system
class WebRoutes {
    protected RouteCollection $routes;

    public function __construct(){

        $this->routes = new RouteCollection();

    }

    function stablishRoutes() : RouteCollection{

        $this->routes->add('edifile', new Route(constant('URL_SUBFOLDER') . '/login', array('controller' => 'LoginController', 'method'=>'invoke'), array()));
        
        $user = $_SESSION["user"] ?? null;

        if(empty($user) && $user === null)
            $this->routes->add('login', new Route(constant('URL_SUBFOLDER') . '/', array('controller' => 'LoginController', 'method'=>'invoke'), array()));
        else
            $this->routes->add('homepage', new Route(constant('URL_SUBFOLDER') . '/', array('controller' => 'FileController', 'method'=>'invoke'), array()));
        
        
        return $this->routes;
    }

}
//$routes->add('edifile', new Route(constant('URL_SUBFOLDER') . '/edifile/{id}', array('controller' => 'ProductController', 'method'=>'showAction'), array('id' => '[0-9]+')));