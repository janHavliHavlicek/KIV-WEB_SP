<?php
class ContactController extends Controller
{
    public function process($params)
    {
        $this->header = array('title' => 'Contact form', 'keywords' => 'contact, email, form', 'description' => 'Contact form of our web');
        
        if(isset($_POST["email"]))
        {
            if($_POST['year'] == date("Y"))
            {
                $mailSender = new MailSender();
                $mailSender->send("jan.havli.havlicek@gmail.com", "Email z webu", $_POST['zprava'], $_POST['email']);
            }
        }
        
        $this->view = 'contact';
    }
}
?>