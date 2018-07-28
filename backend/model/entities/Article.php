<?php

class Article
{
    private $id = NULL;
    private $authorName = NULL;
    private $title = NULL;
    private $content = NULL;
    private $tags = NULL;

    public function __construct($id, $author_name, $title, $content, $tags)
    {
        if (is_null($id) || is_null($author_name) || is_null($content) || is_null($title)) {
            throw new InvalidArgumentException("Constructor parameters: id, author, content cannot be NULL");
        } else {
            $this->id = $id;
            $this->authorName = $author_name;
            $this->title = $title;
            $this->content = $content;
            $this->tags = $tags;
        }
    }

    /**
     * @return Article Id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Article Author
     */
    public function getAuthorName()
    {
        return $this->authorName;
    }

    /**
     * @return Article Content
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return Article Tags
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @return Article Title
     */
    public function getTitle()
    {
        return $this->title;
    }

}