<?php 
class Reviews
{
    private $database;

    public function __construct($db)
    {
        $this->database = $db;
    }

    public function add($article, $author, $overview, $actuality, $facts, $comment)
    {
        $cols = array('article', 'author', 'overview', 'actuality', 'facts', 'comment');
        $vals = array($article, $author, $overview, $actuality, $facts, $comment);
        $this->database->insert('review', $cols, $vals);
    }

    public function addOnlyReviewer($article, $author)
    {
        $authorId = $this->database->select('user', 'username', $author, false, '')['user_id'];
        $cols = array('article', 'author');
        $vals = array($article, $authorId);
        $this->database->insert('review', $cols, $vals);
    }

    public function getAll()
    {
        return $this->database->getTable('review');
    }
    
    public function getReviewId($article, $author)
    {
        return $this->database->select('review', array('article', 'author'), array($article, $author), false, '')['review_id'];
    }

    public function edit($reviewId, $arrayColumns, $arrayValues)
    {
        $this->database->update('review', 'review_id', $reviewId, $arrayColumns, $arrayValues);
    }

    public function getReviewsBy($colVal, $searchVal)
    {
        $res = $this->database->select("review", $colVal, $searchVal, true, '');

        return $res;
    }
    
    public function selectReview($id)
    {
        return $this->database->select('review', 'review_id', $id, false, '');
    }

    public function delete($reviewId)
    {
        $this->database->delete("review", "review_id", $reviewId);
    }

    private function countDimension($array)
    {
        if (is_array(reset($array)))
        {
            $return = countdim(reset($array)) + 1;
        }

        else
        {
            $return = 1;
        }

        return $return;
    }

    public function isReviewerPresent($reviewer, $actualReviewers)
    {
        $reviewer = $this->database->select('user', 'username', $reviewer, false, '');

        if(count($actualReviewers) > 1)
        {
            foreach($actualReviewers as $rev)
            {
                if($reviewer['user_id'] == $rev['author'])
                {
                    return true;
                }
            }
        }else if(count($actualReviewers) == 1)
        {
            if($reviewer['user_id'] == $actualReviewers[0]['author'])
            {
                return true;
            }

        }
        return false;
    }

    public function isReviewFree($articleId)
    {
        $reviews = $this->getReviewsBy('article', $articleId);
        
        if(count($reviews) <3){
            return true;
        }else
        {
            return false;
        }
    }

    public function isReviewerAssignet($reviewerId, $articleId)
    {
        $reviewers = $this->getReviewsBy('article', $articleId);
        foreach($reviewers as $revs)
        {
            if($revs['author'] == $reviewerId)
            {
                return true;
            }
        }

        return false;
    }

    //public function 
}
?>