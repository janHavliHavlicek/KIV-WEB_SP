<?php
    mb_internal_encoding("UTF-8");

    function autoloadFunction($class)
    {
        if(preg_match('/Controller$/', $class))
            require("app/controllers/" . $class . ".php");
        else
            require("app/models/" . $class . ".php");
    }
    
    spl_autoload_register("autoloadFunction");

    $router = new RouterController();
    $router->process(array($_SERVER['REQUEST_URI']));
    $router->printView();
?>