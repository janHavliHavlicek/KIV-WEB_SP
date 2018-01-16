<?php
class RouterController extends Controller
{
    protected $controller;
    
    public function process($params)
    {
        $parsedURL = $this->parseURL($params[0]);
        global $cfgvars;
        
        if(empty($parsedURL[0]))
            $this->route('app/views/home');
        
        $controllerClass = $this->dashesToCamelCase(array_shift($parsedURL)) . 'Controller';
        
        if(file_exists('app/controllers/' . $controllerClass . '.php'))
            $this->controller = new $controllerClass;
        else
            $this->route('error');
        
        $this->controller->process($parsedURL);
        
        $this->signOff($_POST);
        
        $this->data['title'] = $this->controller->head['title'];
        $this->data['description'] = $this->controller->head['description'];
        $this->data['keywords'] = $this->controller->head['keywords'];
        $this->view = 'layout';
    }
    
    private function signOff($input)
    {
        if($this->isKeyword($input, "signOff"))
        {
            session_destroy();
            $this->route('home');
        }
    }
    
    private function isKeyword($input, $keyword)
    {
        if(isset(array_keys($input)[0]))
        {
            $callerName = array_keys($input)[0];
            
            if($callerName == $keyword)
            {
                return true;
            }else
            {
                return false;
            }
        }
    }
    
    private function parseURL($url)
    {
        $res = parse_url($url);
        $res["path"] = ltrim($res["path"], "/");
        $res["path"] = trim($res["path"]);
        
        if ($_SERVER['SERVER_NAME'] == 'localhost')
                array_shift($rozdelenaCesta);
        
        return explode("/", $res["path"]);
    }
    
    private function dashesToCamelCase($param)
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $param)));
    }
}
?>