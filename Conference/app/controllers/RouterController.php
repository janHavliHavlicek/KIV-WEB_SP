<?php
/**
 * This class provides the routing of this web-application
 * 
 * It adheres the MVC architecture. It parses the URL into "pretty" ones.
 * It connects the Controllers with Views.
 * It handles the signOff situation.
 */
class RouterController extends Controller
{
    /**
     * Inherited Controller instance.
     * Used for routing onto the right urls.
     * */
    protected $controller;
    
    /**
     * This method determines what to do when loading the RouterController
     * It parses the URL, takes care of right processing the addresses and
     * showing the right view with the right Controller.
     * 
     * @param array $params parameters as input values, conditions, etc.
     */
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
    
    /**
     * Signs off currently signed user.
     *
     * It destroys its session and re-routes user onto home page.
     * */
    private function signOff($input)
    {
        if($this->catchKeywordsId($input, "signOff", 0))
        {
            session_destroy();
            $this->route('home');
        }
    }
    
    /**
     * This function parses given URL
     * into the pretty one.
     * */
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