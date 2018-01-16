<?php
class HomeController extends Controller
{
    public function process($params)
    {
        $this->header = array('title' => 'Home', 'keywords' => 'MES, home', 'description' => 'Home page of this web');
        
        $this->view = 'home';
    }
}
?>