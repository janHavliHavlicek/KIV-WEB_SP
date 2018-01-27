<?php
class HomeController extends Controller
{
    private $articles;
    private $users;
    private $reviews;

    public function process($params)
    {
        $this->header = array('title' => 'Home', 'keywords' => 'MES, home', 'description' => 'Home page of this web');

        $this->init();
        
        $this->chooseAction($_POST);

        $this->view = 'home';
    }

    private function init()
    {
        $this->articles = new Articles(new Database('localhost', 'Conference'));

        $this->data['articles'] = $this->generateArticleGrid($this->articles->GetAllAccepted());
    }


    private function chooseAction($input)
    {
            //echo "<pre>". print_r($input) ."</pre>";
        if(($id = $this->catchKeywordsId($input, "downloadArticle")) != false)
        {
            $this->downloadArticle($this->articles->selectArticle($id)['pdf_url']);
        }
    }

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

    private function generateArticleRow($articles)
    {

        $res = "<div class=\"row\">" .
            $this->generateArticleCard($articles[0]) .
            $this->generateArticleCard($articles[1]) .
            $this->generateArticleCard($articles[2]) .
            "</div>";

        //echo "==========================================================";
        //echo $res;
        //exit();

        return $res;
    }

    private function generateArticleCard($article)
    {
        //echo "<pre>" . print_r($article, TRUE) . "</pre>";

        if($article == "")
        {
            //echo "empty";
            $res = "";
        }
        else{
            $res = "<div class=\"col-lg-4 col-md-12 .mb-4\"> 
                        <div class=\"card\">
                            <div class=\"card-body\">
                                <h4 class=\"card-title\">". $article['title']. "</h4>
                                <p class=\"card-text\"><strong>Author:</strong> ". $article['author'] ."</p>
                                <p class=\"card-text\"><strong>Description:</strong>". $article['description'] ."</p>
                                <form method=\"POST\"><button type=\"submit\" class=\"btn btn-teal btn-block px-3\" aria-hidden=\"true\" name=\"downloadArticle_". $article['article_id'] ."\">READ!</button></form>
                            </div>
                        </div>
                    </div>";

            //echo "NOT EMPTY";     
        }

        return $res;
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
    
    private function catchKeywordsId($input, $keyword)
    {
        if(isset(array_keys($input)[0]))
        {
            //echo "<pre>". print_r($input) . "</pre>";
            //echo $keyword. "====";
            
            $callerName = array_keys($input)[0];
            $pos = strpos($callerName, $keyword);

            //echo $callerName. "====" . $pos . "_________";
            
            if($pos !== false)
            {
                $id = substr($callerName, strpos($callerName, "_") +1);

                //echo $id;
                //exit();
                
                return $id;
            }else
            {
                return false;
            }
        }
    }
}
?>