<?php

/**
 * Controller logic for Register (sign up).
 * 
 * Only processes the register form. Nothing more.
 * */
class RegisterController extends Controller
{
    /**
     * Processes the register form when submitted.
     * It can "log in" the user into $_SESSION variable 
     * when success register process...
     * 
     * @param array $params input parameters (Not used)
     * */
    public function process($params)
    {
        $this->header = array('title' => 'Register', 'keywords' => 'register, sign up', 'description' => 'Sign up for this website');
        
        if(isset($_POST["username"]) && isset($_POST["password"]) && isset($_POST["mail"]))
        {
            if($_POST["password"] == $_POST["passwordVer"])
            {
                $db = new Database("localhost", "conference");
                $users = new Users($db);

                $users->register($_POST["username"], $_POST["password"], $_POST["mail"]);
                
                $_SESSION['logged_user'] = $users->signIn($_POST["username"], $_POST["password"]);
                $this->view = 'home';
            }   

            $this->view = 'register';
        }else{
            $this->view = 'register';
        }
    }
}
?>