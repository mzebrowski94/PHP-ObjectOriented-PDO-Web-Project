<?php

class Author
{
    private $id = NULL;
    private $name = NULL;
    private $articlesAmount = NULL;
    private $nickname = NULL;

    public function __construct($id,$name,$articles_amount,$nickname)
    {
        $this->id = $id;
        $this->name = $name;
        $this->articlesAmount = $articles_amount;
        $this->nickname = $nickname;
    }

    /**
     * @return Author Id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Author name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Articles amount written by the author
     */
    public function getArticlesAmount()
    {
        return $this->articlesAmount;
    }

    /**
     * @return Author nickname
     */
    public function getNickname()
    {
        return $this->nickname;
    }

}