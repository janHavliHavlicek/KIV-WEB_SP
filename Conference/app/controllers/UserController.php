<?php
/**
 * Controller for user page
 * 
 * It chooses the right page to show according to users status (permissions/role)
 * */
class UserController extends Controller
{
    
    /**
     * Calls the route function if user is not logged.
     * 
     * Stars the initialization of this class when the user is logged.
     * 
     * @param array $params input parameters (Not used)
     * */
    public function process($params)
    {
        if(!isset($_SESSION['logged_user']))
        {
            $this->route('login');
        }
        else{
            $this->header = array('title' => 'User', 'keywords' => 'User, articles, blah', 'description' => 'User information and article management page');
            $this->init($_SESSION['logged_user']);
        }
    }

    /**
     * Re-routes the user to correct page
     * according to given users status (permissions/role)
     * 
     * @param array $user   array of users information
     * */
    private function init($user)
    {
        switch($user['status'])
        {
            case 'author':
                $this->route('myArticles');
                break;
            case 'reviewer': 
                $this->route('myReviews');
                break;
            case 'administrator':
                $this->route('usersAdministration');
                break;
            default:
                $this->route('login');
        }
    }

}
?>