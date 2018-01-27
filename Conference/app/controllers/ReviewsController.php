<?php
class ReviewsController extends Controller
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

            $this->init($reviews, $_SESSION['reviews']['articleId']);

            $this->chooseAction($_POST);

            $this->header = array('title' => 'User', 'keywords' => 'User, articles, blah', 'description' => 'User information and article management page');

            $this->view = 'reviews';
        }
    }

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

        //exit();

        if(empty($reviews) == false )
        {
            $this->data['reviews'] = $this->generateTable($reviews);
        }else
        {
            $this->data['reviews'] = "<h3>No reviews added yet...</h3>";
        }
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

    private function generateTableRow($array)
    {
        $article = $this->articles->selectArticle($array['article']);
        $reviewer = $this->users->getUsernameById($array['author']);

        $res =  "<form method=\"post\">" .
            "<td>" . $reviewer . "</td>".
            "<td>" . $array['overview'] . "</td>" .
            "<td>" . $array['actuality'] . "</td>" .
            "<td>" . $array['facts'] . "</td>" .
            "<td>" . $array['reviewed'] . "</td>".
            "<td>" . $array['changed'] . "</td>";
            $res .= "</form>";

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