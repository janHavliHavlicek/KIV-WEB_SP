<?php
/**
 * Controller for reviews page
 * 
 * It reads the data (article credentials and reviews) from database
 * through class @Articles and @Reviews and generates the html code
 * for its displaying.
 * */
class UsersAdministrationController extends Controller
{
    /**
     * Articles instance.
     * 
     * Serves for accessing the database.
     * Provides some wrapped functions for simplier use.
     * */
    private $articles;
    
    /**
     * Reviews instance.
     * 
     * Serves for accessing the database.
     * Provides some wrapped functions for simplier use.
     * */
    private $reviews;
    
    /**
     * Users instance.
     * 
     * Serves for accessing the database.
     * Provides some wrapped functions for simplier use.
     * */
    private $users;
    
    /**
     * Creates new instance of database class,
     * starts initialization of this class, serves the incoming
     * user actions over the html forms.
     * 
     * @param array $params input parameters (Not used)
     * */
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

    /**
     * Initializes the data variables of this class for view.
     * 
     * Generates the reviews tables of this reviewer (user)
     * 
     * @param array   $user        currently logged user
     * */
    private function init($user)
    {
        $usersArray = $this->usersDb->getAllUsers();
        $this->data['userTableRows'] = $this->generateTable($usersArray, 'user');
        $this->data['reviewsTableRows'] = $this->articlesToReview($this->articles);

        $this->data['user'] = $user;
    }

    /**
     * Gets all not accepted articles from database
     * 
     * Runs the generateTable and then returns the result
     * */
    private function articlesToReview()
    {
        $reviews = $this->articles->GetAllNotAccepted();
        return $this->generateTable($reviews, 'review');
    }

    /**
     * Generates the html table for given array
     * 
     * @param array  $arrayOfArrays  Array of article arrays
     * @param string $table          name of table to generate.
     *                               Allows 2 values - "user" and "review"
     * */
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

    /**
     * Generates the Review table row from given $array data
     * Generates also buttons for given rows
     * 
     * @param array $array articles infromation array - review data and article data
     * 
     * @return the generated html for table row
     * */
    private function generateReviewTableRow($array)
    {
        $_SESSION['selectedReviewers_' .$array['article_id']] = array();

        $res = "<form method=\"post\">" .
            "<td>" . "<button type=\"submit\" class=\"btn btn-teal btn-block px-3\" aria-hidden=\"true\" name=\"downloadArticle_". htmlspecialchars($array['article_id']) ."\">".
            htmlspecialchars($array['title']) ."</button></td>" .
            "<td>" . htmlspecialchars($array['author']) . "</td>".
            "<td>" . htmlspecialchars($array['status']) . "</td>" .
            "<td>" .$this->generateReviewersSelect($array['article_id'], 1, $array['article_id']) . "</td>" .
            "<td>" .$this->generateReviewersSelect($array['article_id'], 2, $array['article_id']) . "</td>" .
            "<td>" .$this->generateReviewersSelect($array['article_id'], 3, $array['article_id']) . "</td>" .
            "<td>" .$this->generateStatusSelect($array) . "</td>" .
            "<td><button type=\"submit\" class=\"btn btn-teal px-3\" aria-hidden=\"true\" name=\"reviews_". htmlspecialchars($array['article_id']) ."\">
                <i class=\"fa fa-comments  fa-2x \" ></i>
            </button></td>" .
            "<td><button type=\"submit\" class=\"btn btn-teal px-3\" aria-hidden=\"true\" name=\"updateArticle_". htmlspecialchars($array['article_id']) ."\"> 
                <i class=\"fa fa-paper-plane-o  fa-2x \" ></i>
            </button></td>" .
            "</form>";
        return $res;
    }

    /**
     * Generates the select html object for choosing a status
     * of article.
     * 
     * @param array $rev    array of review information
     * 
     * @return string       html code for select
     * */
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

    /**
     * Generates the select html object for choosing a reviewer
     * of article. It has to choose which reviewer is assigned to this article
     * It has to enerate the name for selection (with order and review ID)
     * 
     * @param string    $revNum     id of review from database
     * @param int       $order      order of select (allowed values 1-3)
     * @param string    $articleId  id of article to be reviewed - 
     *                              specifies the group of reviewers already selected
     * 
     * @return string       html code for select
     * */
    private function generateReviewersSelect($revNum, $order, $articleId)
    {
        if(!isset($_SESSION['selectedReviewers_' .$articleId]))
            $_SESSION['selectedReviewers_' .$articleId] = array();

        $isSelected = false;

        $reviewers = $this->usersDb->getReviewers();
        $res = "<select name=\"select_" . $revNum . "_" . $order . "\" class=\"mdb-select colorful-select dropdown-primary\">" . "<option value=\"Choose a Reviewer\">Choose a Reviewer</option>";

        foreach($reviewers as $rev)
        {
            if($this->isReviewerSelectedAlready($articleId, htmlspecialchars($rev['username'])) == true || $this->reviews->isReviewerAssignet(htmlspecialchars($rev['user_id']), $articleId) == false || $isSelected == true)
            {
                $res .= "<option value=\"" . htmlspecialchars($rev['username']) . "\">" . htmlspecialchars($rev['username']) . "</option>";
            }else
            {       
                $res .= "<option selected=\"selected\" value=\"" . htmlspecialchars($rev['username']) . "\">" . htmlspecialchars($rev['username']) . "</option>";
                array_push($_SESSION['selectedReviewers_' .$articleId], htmlspecialchars($rev['username']));

                $isSelected = true;
            }
        }

        return $res . "</select>";
    }

    /**
     * Determines if this reviewer is already shown as selected
     * 
     * @param string    $articleId      ID of article of selected reviewers
     * @param string    $reveiwerName   Name of the reviewer needs to be selected
     * */
    private function isReviewerSelectedAlready($articleId, $reviewerName)
    {
        foreach($_SESSION['selectedReviewers_' .$articleId] as $rev)
        {
            if(in_array($reviewerName, $_SESSION['selectedReviewers_' .$articleId]))
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Generates the specific table row from given $array data
     * Generates also buttons for given rows
     * 
     * @param array $array  Reviews array - review data for showing.
     * */
    private function generateUserTableRow($array)
    {
        if($array['blocked'] == 0)
            $blockedIcon = "fa-ban";
        else
            $blockedIcon = "fa-check-circle";
            
        return  "<form method=\"post\">" .
            "<td>" . htmlspecialchars($array['username']) . "</td>" .
            "<td>" . htmlspecialchars($array['status']) . "</td>" .
            "<td>" . htmlspecialchars($array['mail']) . "</td>" .
            "<td>" . htmlspecialchars($array['registered']). "</td>" .
            "<td><button type=\"submit\" class=\"btn btn-teal px-3\" aria-hidden=\"true\" name=\"deleteUser_". htmlspecialchars($array['user_id']) ."\">
                <i class=\"fa fa-remove  fa-lg \" ></i>
            </button></td>" .
            "<td><button type=\"submit\" class=\"btn btn-teal px-3\" aria-hidden=\"true\" name=\"blockUser_". htmlspecialchars($array['user_id']) ."\">
                <i class=\"fa $blockedIcon fa-lg \" ></i>
            </button></td>" .
            "<td><button type=\"submit\" class=\"btn btn-teal px-3\" aria-hidden=\"true\" name=\"promoteUser_". htmlspecialchars($array['user_id']) ."\">
                <i class=\"fa fa-arrow-circle-o-up fa-lg \"></i>
            </button></td>" .
            "<td><button type=\"submit\" class=\"btn btn-teal px-3\" aria-hidden=\"true\" name=\"neglectUser_". htmlspecialchars($array['user_id']) ."\">
                <i class=\"fa fa-arrow-circle-o-down fa-lg \"></i>
            </button></td>" .
            "</form>";
    }

    /**
     * Chooses the right function to call (or action to execute)
     * according to keyword in $input
     * 
     * @param string    $input  The input string which determines the right 
     *                          action if contains a specific string keyword
     * */
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

    /**
     * Reaction to update Article button.
     * 
     * It adds all the new or changed reviewers to given article.
     * 
     * @param string    $articleId      ID of article to be changed/updated
     * @param string    $reviewer1      Name of reviewer 1 (of 3)
     * @param string    $reviewer2      Name of reviewer 2 (of 3)      
     * @param string    $reviewer3      Name of reviewer 3 (of 3)
     * @param string    $newStatus      New status to be changed
     * */
    private function updateArticle($articleId, $reviewer1, $reviewer2, $reviewer3, $newStatus)
    {
        $actualReviews = $this->reviews->getReviewsBy('article', $articleId);

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

    /**
     * Reaction to addReviewer
     * 
     * It adds new review into database with given $reviewer
     * 
     * @param string    $articleId      ID of article to be changed/updated
     * @param string    $reviewer       Name of reviewer to be added
     * @param string    $reviewer2      Name of reviewer 2 (of 3)      
     * @param string    $reviewer3      Name of reviewer 3 (of 3)
     * @param array     $actualReviews  array of actually active reviews for this article_id
     * */
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
}
?>