<?php

/**
 * Controller logic for loggin in.
 * 
 * Only processes the login form. Nothing more.
 * */
class LoginController extends Controller
{
    /**
     * Processes the login form when submitted.
     * It can "log in" the user into $_SESSION variable.
     * 
     * @param array $params input parameters (Not used)
     * */
    public function process($params)
    {
        $this->header = array('title' => 'Login', 'keywords' => 'login', 'description' => 'Login or sign up for this website');

        if(isset($_POST["username"]))
        {
            $db = new Database("localhost", "conference");
            $users = new Users($db);

            $user = $users->signIn($_POST["username"], $_POST["password"]);

            if($user != null)
            {
                $_SESSION["logged_user"] = $user;
                $this->route('user');
            }else
            {
                $_SESSION["logged_user"] = null;
                $_SESSION["logged_user"]["username"] = 'Log in...';
                
                //$this->alert("Wrong password inserted...");
                $this->view = 'login';
            }

            session_write_close();
        }else
        {
            $this->view = 'login';
        }
    }
}
?>