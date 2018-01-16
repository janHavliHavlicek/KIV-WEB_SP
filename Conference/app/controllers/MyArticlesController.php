<?php
class MyArticlesController extends Controller
{
    public function process($params)
    {
        $db = new Database("localhost", "Conference");
        $articles = new Articles($db);
        $reviews = new Reviews($db);

        $this->init($articles, $reviews, $_SESSION['logged_user']);

        $this->chooseAction($articles, $_POST);

        $this->header = array('title' => 'User', 'keywords' => 'User, articles, blah', 'description' => 'User information and article management page');

        $this->view = 'myArticles';
    }

    private function init($articles, $reviews, $user)
    {
        $this->data['articles'] = "";
        $this->data['user'] = $user;
        $this->data['addTitle'] = "Add article";
        $this->data['editArticle']['title'] = "";
        $this->data['editArticle']['description'] = "";
        $this->data['editArticle']['keywords'] = "";
        $this->data['editArticle']['pdfUrl'] = "";
        $this->data['addButton'] = "<input id=\"add\" name=\"add\" type=\"submit\" value=\"Add!\" class=\"btn btn-teal\">";

        $articlesArray = $articles->getArticlesByAuthor($user['user_id']);

        if(empty($articlesArray) == false )
        {
            $this->data['articles'] = $this->generateTable($articlesArray);
        }else
        {
            $this->data['articles'] = "<h3>You have not added any posts yet...</h3>";
        }
    }

    private function addArticle($articles, $input)
    {
        $url = $this->uploadPdf();
        $articles->add($_SESSION['logged_user']['user_id'], $input['title'], $input['description'], $input['keywords'], $url);
    }

    private function editArticle($articles, $input)
    {
        $url = $this->uploadPdf();
        if($url != false)
        {
            $articles->edit('article_id', $_SESSION['editedArticleId'], array('title', 'description', 'keywords', 'pdf_url', 'modified'), array($input['title'], $input['description'], $input['keywords'], $url, date('Y-m-d G:i:s')));
        }
        else
        {
            $articles->edit('article_id', $_SESSION['editedArticleId'], array('title', 'description', 'keywords', 'modified'), array($input['title'], $input['description'], $input['keywords'], date('Y-m-d G:i:s')));
        }
    }

    private function uploadPdf()
    {
        $directory = $_SERVER['DOCUMENT_ROOT']."/articles_pdf/";
        $file = $directory . $_SESSION['logged_user']['user_id'] . '_' . basename($_FILES["fileToUpload"]["name"]);
        $uploadOk = 1;

        $imageFileType = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        //echo $file;
        //exit();
        if($imageFileType == "pdf")
        {
            move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $file);
            return $file;
        }
        else
        {
            return false;    
        }
    }

    private function chooseAction($articles, $input)
    {
        if(isset($input["add"]))
        {
            $this->addArticle($articles, $input);
            $this->route('user');
        }   
        if(isset($input["edit"]))
        {
            $this->editArticle($articles, $input);
            $this->route('user');
        }
        if(isset($input["cancelEditing"]))
        {
            $this->route('user');
        }
        if(($id = $this->catchKeywordsId($input, "deleteArticle")) != false)
        {
            $articles->delete($id);
            $this->route('user');
        }
        if(($id = $this->catchKeywordsId($input, "editArticle")) != false)
        {
            $this->editArticleChoosed($articles, $id);
        }
    }

    private function editArticleChoosed($articles, $articleId)
    {
        $this->data['addTitle'] = "Edit article";
        $this->data['editArticle'] = $articles->selectArticle($articleId);
        $this->data['addButton'] = 
            "<input id=\"edit\" name=\"edit\" type=\"submit\" value=\"Edit!\" class=\"btn btn-teal\">".
            "<input id=\"cancelEditing\" name=\"cancelEditing\" type=\"submit\" value=\"Cancel editing!\" class=\"btn btn-teal\">";

        $_SESSION['editedArticleId'] = $this->data['editArticle']['article_id'];
    }

    private function generateTable($arrayOfArrays)
    {
        $res = "<table class=\"table table-striped table-hover\">
                            <thead class=\"teal lighten-3 \">
                                <tr>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Rating</th>
                                    <th>Added</th>
                                    <th>Modified</th>
                                    <th>Edit</th>
                                    <th>Delete</th>
                                </tr>
                            </thead>
                            <tbody>";

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
        return  "<form method=\"post\">" .
            "<td>" . $array['title'] . "</td>" .
            "<td>" . $array['status'] . "</td>" .
            "<td>" . $array['rating'] . "</td>" .
            "<td>" . $array['added']. "</td>" .
            "<td>" . $array['modified']. "</td>" .
            "<td><button type=\"submit\" class=\"btn btn-teal px-3\" aria-hidden=\"true\" name=\"editArticle_". $array['article_id'] ."\">
                <i class=\"fa fa-edit  fa-2x \" ></i>
            </button></td>" .
            "<td><button type=\"submit\" class=\"btn btn-teal px-3\" aria-hidden=\"true\" name=\"deleteArticle_". $array['article_id'] ."\">
                <i class=\"fa fa-remove  fa-2x \" ></i>
            </button></td>" .
            "</form>";
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