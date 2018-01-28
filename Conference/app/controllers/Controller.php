<?php
/**
 * The session is started when first accessing the web-application
 * */
session_start();

/**
 * This class provides the right showing of web page.
 * It serves as abstract parent for all *Controller classes.
 */
abstract class Controller
{
    /**
     * Data to be transmitted to view
     * 
     * @var array $data
     */
    protected $data = array();

    /**
     * Name of view paired with controller
     * 
     * @var string $view
     */
    protected $view = "";

    /**
     * Head of concrete page - title, keywords and description
     * 
     * $var array $head
     */
    protected $head = array('title' => '', 'keywords' => '', 'description' => '');

    /**
     * This method determines what to do when loading the Controller
     * 
     * @param array $params parameters as input values, conditions, etc.
     */
    abstract function process($params);

    /**
     * Shows the correct view as page
     */
    public function printView()
    {
        if($this->view){
            extract($this->data);
            require("app/views/" . $this->view . ".phtml");
        }
    }

    /**
     * Routes the web to desired url
     * 
     * @param string    $url    url address of page to be routed to
     */
    public function route($url)
    {
        header("Location: /$url");
        header("Connection: close");
        session_write_close();
        exit;
    }
    
    
    /**
     * Catches the id from callers name.
     * 
     * Used for getting the id´s from button submit actions.
     * Works only if the button name is in format of "$keyword_$id"
     * with underscore between them.
     * 
     * $param string    $input      input string - normally 
     *                              the caller element name.
     * $param string    $keyword    keyword that is searched 
     *                              in the input
     * 
     * $return int/bool $id         if keyword is presented in $input,
     *                              then returns the correct $id. Otherwise,
     *                              returns false.
     * */
    public function catchKeywordsId($input, $keyword)
    {
        if(isset(array_keys($input)[0]))
        {   
            $callerName = array_keys($input)[0];
            $pos = strpos($callerName, $keyword);
            
            if($pos !== false)
            {
                $id = substr($callerName, strpos($callerName, "_") +1);        
                return $id;
            }else
            {
                return false;
            }
        }
    }
    
    /**
     * Starts the download of file from web-application.
     * 
     * Calls the native browsers "download" window. Mainly
     * used for downloading the articles in ".pdf".
     * 
     * $param string    $url        url of the desired file.
     * */
    public function downloadArticle($url)
    {
        if (file_exists($url)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($url).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($url));
            readfile($url);
            exit;
        }
    }

    /**
     * Shows the javascript alert with message
     *
     * @param string    $msg    message to be displayed
     */
    public function alert($msg)
    {
        echo '<script type="text/javascript">alert("' . $msg . '")</script>';
    }
}
?>