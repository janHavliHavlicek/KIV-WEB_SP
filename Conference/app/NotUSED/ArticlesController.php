<?php
class ArticlesController extends Controller
{
    public function process($params)
    {
        $this->header = array('title' => 'Articles', 'keywords' => 'articles, MES, themes, traceability', 'description' => 'List of all added articles');
        
        $this->view = 'articles';
    }
}
?>