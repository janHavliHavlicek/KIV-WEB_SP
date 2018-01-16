<?php 
class Articles
{
    private $database;
        
    public function __construct($db)
    {
        $this->database = $db;
    }
    
    /**
    * author = user_id from table "user"!
    */
    public function add($author, $title, $description, $keywords, $pdf_url)
    {
        $cols = array('author', 'title', 'description', 'keywords', 'pdf_url', 'status');
        $vals = array($author, $title, $description, $keywords, $pdf_url, 'new');
        $this->database->insert('article', $cols, $vals);
    }
    
    public function getAll()
    {
        return $this->database->getTable('articles');
    }
    
    public function selectArticle($articleId)
    {
        return $this->database->select('article', 'article_id', $articleId, false, '');
    }
    
    public function edit($colWhere, $valWhere, $arrayColumns, $arrayValues)
    {
        $this->database->update('article', $colWhere, $valWhere, $arrayColumns, $arrayValues);
    }
    
    public function getArticlesByAuthor($authorId)
    {
        $res = $this->database->select("article", "author", $authorId, true, '');
        
        if(empty($res) == true)
        {
            return "";
        }
        if($this->countdim($res) <= 1)
        {
            $rating = $this->database->selectAVG("review", "article", $res['article_id'], "overview");
            
            $res['rating'] = $rating["AVG(overview)"];
            
            return array($res);
        }
        else
        {
            for($i = 0; $i < count($res); $i++)
            {
                $rating = $this->database->selectAVG("review", "article", $res[$i]['article_id'], "overview");
                $res[$i]['rating'] = $rating["AVG(overview)"];
            }
            
            return $res;
        }
    }
    
    public function updateStatus($articleId, $newStatus)
    {
        $this->database->update('article', 'article_id', $articleId, array('status'), array($newStatus));
    }
    
    public function GetAllNotAccepted()
    {
        $res = $this->database->select("article", "status", "accepted", true, 'NOT ');
        
        for($i = 0; $i < count($res); $i++)
        {
            $author = $this->database->select("user", "user_id", $res[$i]['author'], false, '')['username'];
            $res[$i]['author'] = $author;
            
            $reviews = $this->database->select("review", "article", $res[$i]["article_id"], true, '');
            $res[$i]['reviews'] = $reviews;
        }
        
        return $res;
    }

    public function delete($articleId)
    {
        $this->database->delete("article", "article_id", $articleId);
    }
    
    private function countdim($array)
    {
        if (is_array(reset($array)))
        {
            $return = $this->countdim(reset($array)) + 1;
        }

        else
        {
            $return = 1;
        }

        return $return;
    }
}
?>