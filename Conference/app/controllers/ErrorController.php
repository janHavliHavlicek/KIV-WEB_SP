<?php 
/**
 * Controller for showing potential errors.
 * 
 * Used only for showing the "404" page not Found error.
 * */
class ErrorController extends Controller
{
    /**
     * Sets the view "error" and its head.
     * */
    public function process($params)
    {
        header("HTTP/1.0 404 Not Found");
        $this->head['title'] = 'Error404';
        $this->view = 'error';
    }
}
?>