<?php

class Tag
{
    private $id;
    private $content;

    public function __construct($id,$content)
    {
        $this->id = $id;
        $this->content = $content;
    }

    /**
     * @return Tag id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Tag content
     */
    public function getContent()
    {
        return $this->content;
    }
}