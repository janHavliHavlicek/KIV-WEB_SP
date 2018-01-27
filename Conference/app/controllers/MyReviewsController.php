<?php
class MyReviewsController extends Controller
{
    private $articles;
    private $reviews;
    private $users;
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

        //exit();

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

    private function editReview($reviews, $input)
    {
        $reviews->edit($_SESSION['editedReviewId'], array('overview', 'actuality', 'facts', 'comment', 'changed'),
                       array($input['overview'], $input['actuality'], $input['facts'], $input['comment'], date('Y-m-d G:i:s')));
    }

    private function chooseAction($input)
    {
        //echo '<pre>' . print_r($input, true) . '</pre>';
        //exit();

        if(isset($input["download"]))
        {
            //echo '<pre>' . print_r($input, true) . '</pre>';
            //echo $_SESSION['actualArticleUrl'];
            $this->downloadArticle($_SESSION['actualArticleUrl']);
            //exit();
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

        //exit();
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

    private function generateTableRow($array)
    {
        $article = $this->articles->selectArticle($array['article']);
        $author = $this->users->getUsernameById($article['author']);

        $res =  "<form method=\"post\">" .
            "<td>" . "<button type=\"submit\" class=\"btn btn-teal btn-block px-3\" aria-hidden=\"true\" name=\"downloadArticle_". $array['article'] ."\">".
                $article['title'] ."</button></td>" .
            "<td>" . $author . "</td>" .
            "<td>" . $array['overview'] . "</td>" .
            "<td>" . $array['actuality'] . "</td>" .
            "<td>" . $array['facts'] . "</td>" .
            "<td>" . $array['reviewed'] . "</td>".
            "<td>" . $array['changed'] . "</td>";

        if($article['status'] != 'accepted')
            $res .= "<td><button type=\"submit\" class=\"btn btn-teal px-3\" aria-hidden=\"true\" name=\"editReview_". $array['review_id'] ."\">
                <i class=\"fa fa-edit  fa-2x \" ></i></button></td>" .
            "</form>";
        else
            $res .= "<td></td></form>";

        return $res;    
    }

    private function catchKeywordsId($input, $keyword)
    {
        if(isset(array_keys($input)[0]))
        {
            $callerName = array_keys($input)[0];
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
}
?>