<?php
//IF(!ISSET($_SESSION)){
    session_start();
//}

abstract class Controller
{
    protected $data = array();
    protected $view = "";
    protected $head = array('title' => '', 'keywords' => '', 'description' => '');
    
    abstract function process($params);
    
    public function printView()
    {
        if($this->view){
            extract($this->data);
            require("app/views/" . $this->view . ".phtml");
        }
    }
    
    public function route($url)
    {
        header("Location: /$url");
        header("Connection: close");
        session_write_close();
        exit;
    }
}
?>