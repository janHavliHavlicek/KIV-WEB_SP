<?php
/**
 * This class serves for interaction with myReviews view.
 * 
 * It is the page where reviewers can add, edit, delete and overview all
 * their reviews.
 * */
class MyReviewsController extends Controller
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
        if(!isset($_SESSION['logged_user']))
        {
            $this->route('login');
        }
        else{

            $db = new Database("localhost", "Conference");
            $reviews = new Reviews($db);

            $this->reviews = $reviews;
            $this->articles = new Articles($db);
            $this->users = new Users($db);

            $this->init($reviews, $_SESSION['logged_user']);

            $this->chooseAction($_POST);

            $this->header = array('title' => 'User', 'keywords' => 'User, articles, blah', 'description' => 'User information and article management page');

            $this->view = 'myReviews';
        }
    }

    /**
     * Initializes the data variables of this class for view.
     * 
     * Generates the reviews tables of this reviewer (user)
     * 
     * @param Reviews   $reviews   database wrapper instance
     * @param array     $user       currently logged user 
     * */
    private function init($reviews, $user)
    {
        $this->data['reviewsAccepted'] = "";
        $this->data['reviewsNew'] = "";
        $this->data['user'] = $user;
        $this->data['article']['title'] = "";
        $this->data['article']['author'] = "";
        $this->data['article']['keywords'] = "";
        $this->data['article']['description'] = "";
        $this->data['article']['status'] = "";
        $this->data['article']['added'] = "";
        $this->data['article']['modified'] = "";
        $this->data['article']['DownloadArticle'] = "<form  method=\"post\"><input id=\"download\" name=\"download\" type=\"submit\" value=\"Download!\" class=\"btn btn-teal\" disabled></form>";
        $this->data['addTitle'] = "Review";
        $this->data['editReview']['overview'] = "";
        $this->data['editReview']['actuality'] = "";
        $this->data['editReview']['facts'] = "";
        $this->data['editReview']['comment'] = "";
        $this->data['addButton'] = "<input id=\"add\" name=\"add\" type=\"submit\" value=\"Post review\" class=\"btn btn-teal\">";

        $reviewsNew = $reviews->getReviewsBy(array('author', 'wasReviewed'), array($user['user_id'], '0'));
        $reviewsAccepted = $reviews->getReviewsBy(array('author', 'wasReviewed'), array($user['user_id'], '1'));

        if(empty($reviewsNew) == false )
        {
            $this->data['reviewsNew'] = $this->generateTable($reviewsNew, true);
        }else
        {
            $this->data['reviewsNew'] = "<h3>No more articles to review...</h3>";
        }

        if(empty($reviewsAccepted) == false )
        {
            $this->data['reviewsAccepted'] = $this->generateTable($reviewsAccepted, false);
        }else
        {
            $this->data['reviewsAccepted'] = "<h3>You have not added any review yet...</h3>";
        }


    }

    /**
     * Edits the review in database.
     * 
     * @param Reviews   $reviews    database wrapper instance
     * @param array     $input      form type post data. User input.
     * */
    private function editReview($reviews, $input)
    {
        $reviews->edit($_SESSION['editedReviewId'], array('overview', 'actuality', 'facts', 'comment', 'changed'),
                       array($input['overview'], $input['actuality'], $input['facts'], $input['comment'], date('Y-m-d G:i:s')));
    }

    /**
     * Chooses the right function to call (or action to execute)
     * according to keyword in $input
     * 
     * @param string    $input  The input string which determines the right 
     *                          action if contains a specific string keyword
     * */
    private function chooseAction($input)
    {
        if(isset($input["download"]))
        {
            $this->downloadArticle($_SESSION['actualArticleUrl']);
        }
        if(isset($input["edit"]) || isset($input["add"]))
        {
            $this->editReview($this->reviews, $input);
            $this->route('user');
        }
        if(isset($input["cancelEditing"]))
        {
            $this->route('user');
        }
        if(($id = $this->catchKeywordsId($input, "editReview")) != false)
        {
            $this->editArticleChoosed($this->reviews, $id);
        }
        if(($id = $this->catchKeywordsId($input, "downloadArticle")) != false)
        {
            $this->downloadArticle($this->articles->selectArticle($id)['pdf_url']);
        }
    }

    /**
     * Reaction on editation of review. Not "send to db", but "edit".
     * Delivers correct values into add form and changes it into edit form.
     * 
     * @param Reviews   $reviews    database wrapper instance
     * @param string    $id         id of given review
     * */
    private function editArticleChoosed($reviews, $id)
    {
        $this->data['addTitle'] = "Review";
        $this->data['editReview'] = $reviews->selectReview($id);
        $this->data['addButton'] = 
            "<input id=\"cancelEditing\" name=\"cancelEditing\" type=\"submit\" value=\"Cancel\" class=\"btn btn-teal\">".
            "<input id=\"edit\" name=\"edit\" type=\"submit\" value=\"Post review\" class=\"btn btn-teal\">";

        $_SESSION['editedReviewId'] = $this->data['editReview']['review_id'];


        $article = $this->articles->selectArticle($this->data['editReview']['article']);
        $this->data['article'] = $article;
        $this->data['article']['author'] = $this->users->getUsernameById($article['author']);
        $this->data['article']['DownloadArticle'] = "<form  method=\"post\"><input id=\"download\" name=\"download\" type=\"submit\" value=\"Download!\" class=\"btn btn-teal\"></form>";
        $_SESSION['actualArticleUrl'] = $article['pdf_url'];
    }

    /**
     * Generates the html table for given array of reviews
     * 
     * @param array $arrayOfArrays  Array of article arrays
     * @param bool  $newReviews     Says if the table will be of newReviews
     *                              (not reveiwed yet by this reviewer) or not.
     *                              These two tables has different content
     * */
    private function generateTable($arrayOfArrays, $newReviews)
    {
        $res = "<table class=\"table table-striped table-hover\">
                            <thead class=\"teal lighten-3 \">
                                <tr>
                                    <th>Article</th>
                                    <th>Author</th>
                                    <th>Overview</th>
                                    <th>Actuality</th>
                                    <th>Facts</th>
                                    <th>Reviewed</th>
                                    <th>Changed</th>";
        if($newReviews)
        {
            $res .= "<th>Rate</th>";
            $res .= "</tr></thead><tbody>";
        }
        else
        {
            $res .= "<th>Edit</th>";
            $res .= "</tr></thead><tbody>";
        }


        foreach($arrayOfArrays as $arr)
        {
            if(is_array($arr))
            {
                $res .= "<tr>" . $this->generateTableRow($arr) . "</tr>";
            }
        }

        $res .= "</tbody></table>";

        return $res;
    }

    /**
     * Generates the specific table row from given $array data
     * Generates also buttons for given rows
     * 
     * @param array $array  Reviews array - review data for showing.
     * */
    private function generateTableRow($array)
    {
        $article = $this->articles->selectArticle($array['article']);
        $author = $this->users->getUsernameById($article['author']);

        $res =  "<form method=\"post\">" .
            "<td>" . "<button type=\"submit\" class=\"btn btn-teal btn-block px-3\" aria-hidden=\"true\" name=\"downloadArticle_". htmlspecialchars($array['article']) ."\">".
                htmlspecialchars($article['title']) ."</button></td>" .
            "<td>" . htmlspecialchars($author) . "</td>" .
            "<td>" . htmlspecialchars($array['overview']) . "</td>" .
            "<td>" . htmlspecialchars($array['actuality']) . "</td>" .
            "<td>" . htmlspecialchars($array['facts']) . "</td>" .
            "<td>" . htmlspecialchars($array['reviewed']) . "</td>".
            "<td>" . htmlspecialchars($array['changed']) . "</td>";

        if($article['status'] != 'accepted')
            $res .= "<td><button type=\"submit\" class=\"btn btn-teal px-3\" aria-hidden=\"true\" name=\"editReview_". htmlspecialchars($array['review_id']) ."\">
                <i class=\"fa fa-edit  fa-2x \" ></i></button></td>" .
            "</form>";
        else
            $res .= "<td></td></form>";

        return $res;    
    }
}
?>