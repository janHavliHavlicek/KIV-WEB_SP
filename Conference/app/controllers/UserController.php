<?php
class UserController extends Controller
{
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