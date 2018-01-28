<?php
/**
 * This class serves for interaction with myArticles view.
 * 
 * It is the page where authors can add, edit, delete and overview all
 * their articles.
 * */
class MyArticlesController extends Controller
{
    /**
     * Creates new instance of database class,
     * starts initialization of this class, serves the incoming
     * user actions over the html forms.
     * 
     * @param array $params input parameters (Not used)
     * */
    public function process($params)
    {
        $db = new Database("localhost", "Conference");
        $articles = new Articles($db);

        $this->init($articles, $_SESSION['logged_user']);

        $this->chooseAction($articles, $_POST);

        $this->header = array('title' => 'User', 'keywords' => 'User, articles, blah', 'description' => 'User information and article management page');

        $this->view = 'myArticles';
    }

    /**
     * Initializes the data variables of this class for view.
     * 
     * Generates the articles table of this user.
     * 
     * @param Articles  $articles   database wrapper instance
     * @param array     $user       currently logged user 
     * */
    private function init($articles, $user)
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

    /**
     * Adds an article into database.
     * 
     * @param Articles  $articles   database wrapper instance
     * @param array     $input      form type post data. User input.
     * */
    private function addArticle($articles, $input)
    {
        $url = $this->uploadPdf();
        $articles->add($_SESSION['logged_user']['user_id'], $input['title'], $input['description'], $input['keywords'], $url);
    }

    /**
     * Edits the article in database.
     * 
     * @param Articles  $articles   database wrapper instance
     * @param array     $input      form type post data. User input.
     * */
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

    /**
     * Uploads selected .pdf onto server FTP.
     * 
     * Gets the image - adds the user_id into its name (prefix)
     * and then uploads it onto FTP (overwrites existing)
     * */
    private function uploadPdf()
    {
        $directory = $_SERVER['DOCUMENT_ROOT']."/articles_pdf/";
        $file = $directory . $_SESSION['logged_user']['user_id'] . '_' . basename($_FILES["fileToUpload"]["name"]);
        $uploadOk = 1;

        $imageFileType = strtolower(pathinfo($file, PATHINFO_EXTENSION));

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

     /**
     * Chooses the right function to call (or action to execute)
     * according to keyword in $input
     * 
     * @param Articles  $articles   database wrapper instance
     * @param string    $input  The input string which determines the right 
     *                          action if contains a specific string keyword
     * */
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
        if(($id = $this->catchKeywordsId($input, "downloadArticle")) != false)
        {
            $this->downloadArticle($this->articles->selectArticle($id)['pdf_url']);
        }
    }

    /**
     * Reaction on editation of article. Not "send to db", but "edit".
     * Delivers correct values into add form and changes it into edit form.
     * 
     * @param Articles  $articles   database wrapper instance
     * @param string    $articleId  id of given article
     * */
    private function editArticleChoosed($articles, $articleId)
    {
        $this->data['addTitle'] = "Edit article";
        $this->data['editArticle'] = $articles->selectArticle($articleId);
        $this->data['addButton'] = 
            "<input id=\"edit\" name=\"edit\" type=\"submit\" value=\"Edit!\" class=\"btn btn-teal\">".
            "<input id=\"cancelEditing\" name=\"cancelEditing\" type=\"submit\" value=\"Cancel editing!\" class=\"btn btn-teal\">";

        $_SESSION['editedArticleId'] = $this->data['editArticle']['article_id'];
    }

    /**
     * Generates the html table for given array of Articles
     * 
     * @param array $arrayOfArrays  Array of article arrays
     * */
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

    /**
     * Generates the specific table row from given $array data
     * Generates also buttons for given rows (edit/delete/download article)
     * 
     * @param array $array  Article array - article datas for showing.
     * */
    private function generateTableRow($array)
    {
        return  "<form method=\"post\">" .
            "<td><button type=\"submit\" class=\"btn btn-teal btn-block px-3\" aria-hidden=\"true\" name=\"downloadArticle_". htmlspecialchars($array['article_id']) ."\">".htmlspecialchars($array['title'])."</button></td>" .
            "<td>" . htmlspecialchars($array['status']) . "</td>" .
            "<td>" . htmlspecialchars($array['rating']) . "</td>" .
            "<td>" . htmlspecialchars($array['added']). "</td>" .
            "<td>" . htmlspecialchars($array['modified']). "</td>" .
            "<td><button type=\"submit\" class=\"btn btn-teal px-3\" aria-hidden=\"true\" name=\"editArticle_". htmlspecialchars($array['article_id']) ."\">
                <i class=\"fa fa-edit  fa-2x \" ></i>
            </button></td>" .
            "<td><button type=\"submit\" class=\"btn btn-teal px-3\" aria-hidden=\"true\" name=\"deleteArticle_". htmlspecialchars($array['article_id']) ."\">
                <i class=\"fa fa-remove  fa-2x \" ></i>
            </button></td>" .
            "</form>";
    }
}
?>