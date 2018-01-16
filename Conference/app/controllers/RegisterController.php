<?php
class RegisterController extends Controller
{
    public function process($params)
    {
        $this->header = array('title' => 'Register', 'keywords' => 'register, sign up', 'description' => 'Sign up for this website');

        if(isset($_POST["username"]))
        {
            if($_POST["password"] == $_POST["passwordVer"])
            {
                $db = new Database("localhost", "conference");
                $users = new Users($db);

                $users->register($_POST["username"], $_POST["password"], $_POST["mail"]);
                
                $_SESSION['logged_user'] = $users->signIn($_POST["username"], $_POST["password"]);
            }   

            $this->view = 'home';
        }else{
            $this->view = 'register';
        }
    }
}
?>