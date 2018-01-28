<?php
class LoginController extends Controller
{
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