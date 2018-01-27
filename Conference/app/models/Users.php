<?php 
class Users
{
    private $database;
        
    public function __construct($db)
    {
        $this->database = $db;
    }
    
    public function signIn($username, $password)
    {
        $user = $this->database->select('user', "*", 'username', $username, false, '');
        
        if($user['password'] == md5($password) && $user['blocked'] == 0)
        {
            return $user;
        }else
        {
            return null;
        }
    }
    
    public function register($username, $password, $mail)
    {
        $cols = array('username', 'password', 'status', 'mail');
        $vals = array($username, md5($password), 'author', $mail);
        $this->database->insert('user', $cols, $vals);
    }
    
    public function delete($username)
    {
        $this->database->delete('user', 'username', $username);
    }
    
    public function deleteById($id)
    {
        $this->database->delete('user', 'user_id', $id);
    }
    
    public function getAllUsers()
    {
        return $this->database->getTable('user');
    }
    
    public function getUsernameById($id)
    {
        return $this->database->select('user', "*", 'user_id', $id, false, '')['username'];
    }
    
    public function getReviewers()
    {
        return $this->database->select('user', "*", 'status', 'reviewer', true, '');
    }
    
    public function getIdsByUsers($input)
    {
        $res = array();
        foreach($input as $name)
        {
            $userId = $this->database->select('user', "*", 'username', $name, false, '')['user_id'];
            
            if($userId != null)
                array_push($res, $userId);
            else
                array_push($res, $name);
        }
        return $res;
    }

    public function promote($userId)
    {
        $user = $this->database->select("user", "*", "user_id", $userId, false, '');
        $toWrite = "author";
        switch ($user['status'])
        {
            case "author":
                $toWrite = "reviewer";
                break;
            case "reviewer":
                $toWrite = "administrator";
                break;
            case "administrator":
                $toWrite = "administrator";
                break;
        }

        $this->database->update("user", "user_id", $userId, array ('status'), array($toWrite));
    }

    public function neglect($userId)
    {
        $user = $this->database->select("user", "*", "user_id", $userId, false, '');
        $toWrite = "author";
        switch ($user['status'])
        {
            case "author":
                $toWrite = "author";
                break;
            case "reviewer":
                $toWrite = "author";
                break;
            case "administrator":
                $toWrite = "reviewer";
                break;
        }

        $this->database->update("user", "user_id", $userId, array ('status'), array($toWrite));
    }
    
    public function block($userId)
    {
        $user = $this->database->select("user", "*", "user_id", $userId, false, '');
        $toWrite = 0;
        
        if($user['blocked'] == 0)
        {
            $toWrite = 1;
        }

        $this->database->update("user", "user_id", $userId, array ('blocked'), array($toWrite));
    }
}
?>