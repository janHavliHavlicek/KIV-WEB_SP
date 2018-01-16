<?php
class AuthorsController extends Controller
{
    public function process($params)
    {
        $this->header = array('title' => 'Authors', 'keywords' => 'authors, bio, users', 'description' => 'List of users & reviewers');
        
        $this->view = 'authors';
    }
}
?>