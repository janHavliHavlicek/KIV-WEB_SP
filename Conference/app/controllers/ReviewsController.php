<?php
/**
 * Controller for reviews page
 * 
 * It reads the data (article credentials and reviews) from database
 * through class @Articles and @Reviews and generates the html code
 * for its displaying.
 * */
class ReviewsController extends Controller
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

            $this->init($reviews, $_SESSION['reviews']['articleId']);

            $this->chooseAction($_POST);

            $this->header = array('title' => 'User', 'keywords' => 'User, articles, blah', 'description' => 'User information and article management page');

            $this->view = 'reviews';
        }
    }

    /**
     * Initializes the data variables of this class for view.
     * 
     * Generates the reviews tables of this reviewer (user)
     * 
     * @param Reviews   $reviews        database wrapper instance
     * @param string    $articleId      id of article to be shown in this reviews view.
     * */
    private function init($reviews, $articleId)
    {
        $article = $this->articles->selectArticle($articleId);

        $this->data['reviews'] = "";
        $this->data['article'] = $article;
        $this->data['article']['author'] = $this->users->getUsernameById($article['author']);
        $this->data['article']['DownloadArticle'] = "<form  method=\"post\"><input id=\"download\" name=\"download\" type=\"submit\" value=\"Download!\" class=\"btn btn-teal\"></form>";
        $this->data['addTitle'] = "Review";
        $this->data['editReview']['overview'] = "";
        $this->data['editReview']['actuality'] = "";
        $this->data['editReview']['facts'] = "";
        $this->data['editReview']['comment'] = "";
        $this->data['addButton'] = "<input id=\"add\" name=\"add\" type=\"submit\" value=\"Post review\" class=\"btn btn-teal\">";

        $reviews = $reviews->getReviewsBy(array('article'), array($articleId));

        if(empty($reviews) == false )
        {
            $this->data['reviews'] = $this->generateTable($reviews);
        }else
        {
            $this->data['reviews'] = "<h3>No reviews added yet...</h3>";
        }
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
        if(($id = $this->catchKeywordsId($input, "downloadArticle", 0)) != false)
        {
            $this->downloadArticle($this->articles->selectArticle($id)['pdf_url']);
        }
    }

    /**
     * Generates the html table for given array
     * 
     * @param array $arrayOfArrays  Array of article arrays
     * */
    private function generateTable($arrayOfArrays)
    {
        $res = "<table class=\"table table-striped table-hover\">
                            <thead class=\"teal lighten-3 \">
                                <tr>
                                    <th>Reviewer</th>
                                    <th>Overview</th>
                                    <th>Actuality</th>
                                    <th>Facts</th>
                                    <th>Reviewed</th>
                                    <th>Changed</th>";

        $res .= "</tr></thead><tbody>";



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
        $reviewer = $this->users->getUsernameById($array['author']);

        $res =  "<form method=\"post\">" .
            "<td>" . htmlspecialchars($reviewer) . "</td>".
            "<td>" . htmlspecialchars($array['overview']) . "</td>" .
            "<td>" . htmlspecialchars($array['actuality']) . "</td>" .
            "<td>" . htmlspecialchars($array['facts']) . "</td>" .
            "<td>" . htmlspecialchars($array['reviewed']) . "</td>".
            "<td>" . htmlspecialchars($array['changed']) . "</td>";
            $res .= "</form>";

        return $res;    
    }
}
?>