<?php
/**
 * Controller for "home" page
 * 
 * It reads the data (accepted articles) from database
 * through class @Articles and generates the html code
 * for its right displaying.
 * */
class HomeController extends Controller
{
    /**
     * Articles instance.
     * 
     * Serves for accessing the database.
     * Provides some wrapped functions for simplier use.
     * */
    private $articles;
    
    /**
     * Stars only the initialization and action choosing.
     * 
     * @param array $params input parameters (Not used)
     * */
    public function process($params)
    {
        $this->header = array('title' => 'Home', 'keywords' => 'MES, home', 'description' => 'Home page of this web');

        $this->init();
        
        $this->chooseAction($_POST);

        $this->view = 'home';
    }
    
    /**
     * Initializes this class.
     * 
     * Creates new instance of Articles.
     * Generates the articles "grid" html 
     * */
    private function init()
    {
        $this->articles = new Articles(new Database('localhost', 'Conference'));

        $this->data['articles'] = $this->generateArticleGrid($this->articles->GetAllAccepted());
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
        if(($id = $this->catchKeywordsId($input, "downloadArticle")) != false)
        {
            $this->downloadArticle($this->articles->selectArticle($id)['pdf_url']);
        }
    }
    
    /**
     * Generates the article grid.
     * Rows by 3 cards with articles.
     * 
     * @param array $articles   Articles to be showed.
     * 
     * @return string   html code for showing $articles in rows by 3 on each row
     * */
    private function generateArticleGrid($articles)
    {
        $res = "";

        for($i = 0; $i<count($articles); $i += 3)
        {
            if(!isset($articles[$i])) $articles[$i] = ""; 
            if(!isset($articles[$i+1])) $articles[$i+1] = "";
            if(!isset($articles[$i+2])) $articles[$i+2] = "";
            $res .= $this->generateArticleRow(array($articles[$i], $articles[$i+1], $articles[$i+2]));
        }

        return $res;
    }
    
    /**
     * Generates the row of maximum 3 articles.
     * 
     * @param array $articles   Max three articles which cards
     *                          will be generated.
     * 
     * @return string   maximum of three formatted cards in html row
     * */
    private function generateArticleRow($articles)
    {

        $res = "<div class=\"row\">" .
            $this->generateArticleCard($articles[0]) .
            $this->generateArticleCard($articles[1]) .
            $this->generateArticleCard($articles[2]) .
            "</div>";

        return $res;
    }
    
    /**
     * Generates the card from input parameter.
     * Formats the card with mdbootstrap.
     * 
     * @param array $article    Infomations about article which card
     *                          has to be generated
     * 
     * $return string   html code of one card for desired article
     * */
    private function generateArticleCard($article)
    {
        if($article == "")
        {
            return "";
        }
        else{
            return "<div class=\"col-lg-4 col-md-12 .mb-4\"> 
                        <div class=\"card\">
                            <div class=\"card-body\">
                                <h4 class=\"card-title\">". htmlspecialchars($article['title']). "</h4>
                                <p class=\"card-text\"><strong>Author:</strong> ". htmlspecialchars($article['author']) ."</p>
                                <p class=\"card-text\"><strong>Description:</strong>". htmlspecialchars($article['description']) ."</p>
                                <form method=\"POST\"><button type=\"submit\" class=\"btn btn-teal btn-block px-3\" aria-hidden=\"true\" name=\"downloadArticle_". htmlspecialchars($article['article_id']) ."\">READ!</button></form>
                            </div>
                        </div>
                    </div>";   
        }
    }
}
?>