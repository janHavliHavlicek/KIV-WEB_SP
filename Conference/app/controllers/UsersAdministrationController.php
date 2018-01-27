<?php
class UsersAdministrationController extends Controller
{
    private $usersDb;
    private $articles;
    private $reviews;
    public function process($params)
    {
        $db = new Database("localhost", "conference");
        $this->usersDb = new Users($db);
        $this->articles = new Articles($db);
        $this->reviews = new Reviews($db);
        
        $this->init($_SESSION['logged_user']);

        $this->chooseAction($db, $_POST);

        $this->header = array('title' => 'user administration', 'keywords' => 'users', 'description' => 'Page for administrating the registered users of this web');


        $this->view = 'usersAdministration';
    }

    private function init($user)
    {
        $usersArray = $this->usersDb->getAllUsers();
        $this->data['userTableRows'] = $this->generateTable($usersArray, 'user');
        $this->data['reviewsTableRows'] = $this->articlesToReview($this->articles);

        $this->data['user'] = $user;
    }

    private function articlesToReview()
    {
        $reviews = $this->articles->GetAllNotAccepted();
        return $this->generateTable($reviews, 'review');
    }

    private function generateTable($arrayOfArrays, $table)
    {
        $res = "";

        foreach($arrayOfArrays as $arr)
        {
            if(is_array($arr))
            {
                if($table == 'user')
                    $res .= "<tr>" . $this->generateUserTableRow($arr) . "</tr>";
                else if($table == 'review')
                    $res .= "<tr>" . $this->generateReviewTableRow($arr) . "</tr>";
            }
        }

        return $res;
    }

    private function generateReviewTableRow($array)
    {
        $_SESSION['selectedReviewers_' .$array['article_id']] = array();

        $res = "<form method=\"post\">" .
            "<td>" . "<button type=\"submit\" class=\"btn btn-teal btn-block px-3\" aria-hidden=\"true\" name=\"downloadArticle_". $array['article_id'] ."\">".
            $array['title'] ."</button></td>" .
            "<td>" . $array['author'] . "</td>".
            "<td>" . $array['status'] . "</td>" .
            "<td>" .$this->generateReviewersSelect($array['article_id'], 1, $array['article_id']) . "</td>" .
            "<td>" .$this->generateReviewersSelect($array['article_id'], 2, $array['article_id']) . "</td>" .
            "<td>" .$this->generateReviewersSelect($array['article_id'], 3, $array['article_id']) . "</td>" .
            "<td>" .$this->generateStatusSelect($array) . "</td>" .
            "<td><button type=\"submit\" class=\"btn btn-teal px-3\" aria-hidden=\"true\" name=\"reviews_". $array['article_id'] ."\">
                <i class=\"fa fa-comments  fa-2x \" ></i>
            </button></td>" .
            "<td><button type=\"submit\" class=\"btn btn-teal px-3\" aria-hidden=\"true\" name=\"updateArticle_". $array['article_id'] ."\"> 
                <i class=\"fa fa-paper-plane-o  fa-2x \" ></i>
            </button></td>" .
            "</form>";
        return $res;
    }

    private function generateStatusSelect($rev)
    {
        $res = "<select name=\"selectStatus_" . $rev['article_id'] . "\" class=\"mdb-select colorful-select dropdown-primary\">";

        if($rev['status'] == "new")
        {
            $res .= "<option selected=\"selected\" value=\"new\">new</option>";
        }else{
            $res .= "<option value=\"new\">new</option>";
        }

        if($rev['status'] == "reviewed")
        {
            $res .= "<option selected=\"selected\" value=\"reviewed\">reviewed</option>";            
        }else{
            $res .= "<option value=\"reviewed\">reviewed</option>";            
        }

        if($rev['status'] == "rejected")
        {
            $res .= "<option selected=\"selected\" value=\"rejected\">rejected</option>";            
        }else{
            $res .= "<option value=\"rejected\">rejected</option>";            
        }

        if($rev['status'] == "accepted"){
            $res .= "<option selected=\"selected\" value=\"accepted\">accepted</option>";
        }else{
            $res .= "<option value=\"accepted\">accepted</option>";
        }

        return $res . "</select>";
    }

    private function generateReviewersSelect($revNum, $order, $articleId)
    {
        if(!isset($_SESSION['selectedReviewers_' .$articleId]))
            $_SESSION['selectedReviewers_' .$articleId] = array();

        $isSelected = false;

        $reviewers = $this->usersDb->getReviewers();
        $res = "<select name=\"select_" . $revNum . "_" . $order . "\" class=\"mdb-select colorful-select dropdown-primary\">" . "<option value=\"Choose a Reviewer\">Choose a Reviewer</option>";

        //echo '<pre>' .print_r($reviewers, TRUE).'</pre>';

        foreach($reviewers as $rev)
        {
            //echo '<pre>' .print_r($rev, TRUE).'-----------</pre>';
            if($this->isReviewerSelectedAlready($articleId, $rev['username']) == true || $this->reviews->isReviewerAssignet($rev['user_id'], $articleId) == false || $isSelected == true)
            {
                $res .= "<option value=\"" . $rev['username'] . "\">" . $rev['username'] . "</option>";
            }else
            {       
                //echo '<pre> hahaha' .print_r($rev, TRUE).'</pre>';
                $res .= "<option selected=\"selected\" value=\"" . $rev['username'] . "\">" . $rev['username'] . "</option>";
                array_push($_SESSION['selectedReviewers_' .$articleId], $rev['username']);

                $isSelected = true;
            }
        }

        return $res . "</select>";
    }

    private function isReviewerSelectedAlready($articleId, $reviewerName)
    {
        //echo '<pre>'. print_r('selectedReviewers_' .$articleId, TRUE) .'</pre>';
        //echo '<pre>'. print_r($_SESSION['selectedReviewers_' .$articleId], TRUE) .'</pre>';
        //echo '<pre>'. print_r($reviewerName, TRUE) .'***********************************************</pre>';
        foreach($_SESSION['selectedReviewers_' .$articleId] as $rev)
        {
            if(in_array($reviewerName, $_SESSION['selectedReviewers_' .$articleId]))
            {
                return true;
            }
        }
        return false;
    }

    private function generateUserTableRow($array)
    {
        if($array['blocked'] == 0)
            $blockedIcon = "fa-ban";
        else
            $blockedIcon = "fa-check-circle";
            
        return  "<form method=\"post\">" .
            "<td>" . $array['username'] . "</td>" .
            "<td>" . $array['status'] . "</td>" .
            "<td>" . $array['mail'] . "</td>" .
            "<td>" . $array['registered']. "</td>" .
            "<td><button type=\"submit\" class=\"btn btn-teal px-3\" aria-hidden=\"true\" name=\"deleteUser_". $array['user_id'] ."\">
                <i class=\"fa fa-remove  fa-lg \" ></i>
            </button></td>" .
            "<td><button type=\"submit\" class=\"btn btn-teal px-3\" aria-hidden=\"true\" name=\"blockUser_". $array['user_id'] ."\">
                <i class=\"fa $blockedIcon fa-lg \" ></i>
            </button></td>" .
            "<td><button type=\"submit\" class=\"btn btn-teal px-3\" aria-hidden=\"true\" name=\"promoteUser_". $array['user_id'] ."\">
                <i class=\"fa fa-arrow-circle-o-up fa-lg \"></i>
            </button></td>" .
            "<td><button type=\"submit\" class=\"btn btn-teal px-3\" aria-hidden=\"true\" name=\"neglectUser_". $array['user_id'] ."\">
                <i class=\"fa fa-arrow-circle-o-down fa-lg \"></i>
            </button></td>" .
            "</form>";
    }

    private function chooseAction($db, $input)
    {
        
        
        if(($id = $this->catchKeywordsId($input, "updateArticle", 4)) != false)
        {
            $this->updateArticle($id, $_POST["select_" .$id. "_1"], $_POST["select_" .$id. "_2"], $_POST["select_" .$id. "_3"], $_POST['selectStatus_' . $id]);
            $this->route('usersAdministration');
        }
        else if(($id = $this->catchKeywordsId($input, "deleteUser", 0)) != false)
        {
            $this->usersDb->deleteById($id);
            $this->route('usersAdministration');
        }
        else if(($id = $this->catchKeywordsId($input, "blockUser", 0)) != false)
        {
            $this->usersDb->block($id);
            $this->route('usersAdministration');
        }
        else if(($id = $this->catchKeywordsId($input, "promoteUser", 0)) != false)
        {
            $this->users->promote($db, $id);
            $this->route('usersAdministration');
        }
        else if(($id = $this->catchKeywordsId($input, "neglectUser", 0)) != false)
        {
            $this->users->neglect($db, $id);
            $this->route('usersAdministration');
        }
        else if(($id = $this->catchKeywordsId($input, "downloadArticle", 0)) != false)
        {
            $this->downloadArticle($this->articles->selectArticle($id)['pdf_url']);
        }
        else if(($id = $this->catchKeywordsId($input, "reviews", 4)) != false)
        {
            $_SESSION['reviews']['articleId'] = $id;
            $this->route('reviews');
        }
    }

    private function updateArticle($articleId, $reviewer1, $reviewer2, $reviewer3, $newStatus)
    {
        //echo '<pre>'. "$articleId, $reviewer1, $reviewer2, $reviewer3, $newStatus" .'________________</pre>';

        $actualReviews = $this->reviews->getReviewsBy('article', $articleId);

        //echo '<pre>'. print_r($actualReviewers, TRUE) .'</pre>';

        if(empty($reviewer1) == false)
        {
            $this->addReviewer($reviewer1, $reviewer2, $reviewer3, $actualReviews, $articleId);
        }
        if(empty($reviewer2) == false)
        {
            $this->addReviewer($reviewer2, $reviewer1, $reviewer3, $actualReviews, $articleId);
        }
        if(empty($reviewer3) == false)
        {
            $this->addReviewer($reviewer3, $reviewer1, $reviewer2, $actualReviews, $articleId);
        }
        if(empty($newStatus) == false)
        {
            $this->articles->updateStatus($articleId, $newStatus);
        }
    }

    private function addReviewer($reviewer, $reviewer2, $reviewer3, $actualReviews, $articleId)
    {
        $reviews = $this->reviews;
        if($reviews->isReviewerPresent($reviewer, $actualReviews) == false)
        {
            if($reviews->isReviewFree($articleId) == true && $reviewer != 'Choose a Reviewer')
            {
                $reviews->addOnlyReviewer($articleId, $reviewer);
            }
            else if($reviews->isReviewFree($articleId) == false)
            {
                $newReviewers = $this->usersDb->getIdsByUsers(array($reviewer, $reviewer2, $reviewer3));
                $actualReviewersArray = array();

                foreach($actualReviews as $rev)
                {
                    array_push($actualReviewersArray, $rev['author']);
                }

                $replaceWho = array_values(array_diff($actualReviewersArray, $newReviewers));
                $replaceByWhom = array_values(array_diff($newReviewers, $actualReviewersArray));

                for($i = 0; $i < count($replaceWho); $i++)
                {

                    $reviewId = $reviews->getReviewId($articleId, $replaceWho[$i]);


                    if($replaceByWhom[$i] == 'Choose a Reviewer')
                    {
                        $reviews->delete($reviewId);
                    }
                    else
                    {
                        $reviews->edit($reviewId, array('author', 'overview', 'actuality', 'factuality', 'comment'), array($replaceByWhom[$i], 'NULL', 'NULL', 'NULL', 'NULL'));
                    }
                }
            }
        }
    }

    private function catchKeywordsId($input, $keyword, $index)
    {
        if(isset(array_keys($input)[$index]))
        {
            $callerName = array_keys($input)[$index];
            $pos = strpos($callerName, $keyword);

            if($pos !== false)
            {
                $id = substr($callerName, strpos($callerName, "_") +1);

                return $id;
            }else
            {
                return false;
            }
        }
    }

    private function downloadArticle($url)
    {
        if (file_exists($url)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($url).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($url));
            readfile($url);
            exit;
        }
    }
}
?>