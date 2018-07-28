<?php
$root_path = $_SERVER['DOCUMENT_ROOT'];
$entities_path = $root_path . "/PHP_PROJECT/backend/model/entities";
require "$entities_path/Author.php";
require "$entities_path/Tag.php";
require "$entities_path/Article.php";


class ArticlesDAO
{
    private $crud = NULL;

    public function __construct($crud)
    {
        $this->crud = $crud;
    }

    public function retriveArticles($author = null, $title = null, $tags = null)
    {
        $articles_query = $this->crud->retriveArticles($author, $title, $tags);
        $articles = $articles_query->fetchAll();
        $mapped_articles = NULL;
        foreach ($articles as $key => $value) {
            $author_query = $this->crud->retriveAuthorById($value['autor_id']);
            $tags_query = $this->crud->retriveTagsByArticleId($value['id']);
            $author = $author_query->fetchAll();
            $fetched_tags = $tags_query->fetchAll();
            $tags = $this->concateTags($fetched_tags);
            $mapped_articles[$key] = new Article($value['id'], $author[0]['imie'], $value['tytul'], $value['tresc'], $tags);
        }

        return $mapped_articles;
    }

    public function delteArticle($id)
    {
        $this->crud->deleteArticle($id);
    }

    public function addArticle($author_name, $title, $content, $tags)
    {
        $author = $this->retriveAuthor($author_name);
        if (!is_null($author) && !is_null($title) && !empty($title) && !is_null($content) && !empty($content)) {
            $query = $this->crud->createArticle($title, $content, $author->getId());
            $cos = $query->fetchAll();
            $article_query = $this->crud->retriveArticleByAuthorAndTitle($author->getId(), $title);
            if ($article_query != false) {
                $article_data = $article_query->fetchAll();
                if (!is_null($article_data) && !empty($article_data[0])) {
                    $this->addTagsToArticle($tags, $article_data[0]["id"]);
                    return $author->getId();
                }
            }
        }
        return null;
    }

    public function updateArticle($article_id, $author_name, $title, $content, $tags)
    {
        $author = $this->retriveAuthor($author_name);
        if (!is_null($author) && !is_null($article_id)
            && !is_null($author_name) && !empty($title)
            && !is_null($title) && !empty($title)
            && !is_null($content) && !empty($content)) {
            $this->crud->updateArticle($article_id, $author->getId(), $title, $content);
            $article_query = $this->crud->retriveArticles($author->getId());
            if ($article_query != false) {
                $article_data = $article_query->fetchAll();
                if (!is_null($article_data) && !empty($article_data[0])) {
                    $this->addTagsToArticle($tags, $article_data[0]["id"]);
                    return $author->getId();
                }
            }
        }
        return null;
    }

    public function addTag($content)
    {
        if (!is_null($content)) {
            $this->crud->createTag();
        }
    }

    public function addTagsToArticle($tags, $article_id)
    {
        if (is_string($tags)) {
            $tags_data = explode(",", $tags);
            foreach ($tags_data as $tag_string) {
                $tag_query = $this->crud->retriveTagByContent($tag_string);
                $tag_data = NULL;
                if ($tag_query != false) {
                    $tag_data = $tag_query->fetchAll();
                }
                if (is_null($tag_data) || empty($tag_data[0])) {
                    $this->crud->createTag($tag_string);
                    $tag_query = $this->crud->retriveTagByContent($tag_string);
                    $tag_data = $tag_query->fetchAll();
                }

                $this->crud->addTagForArticle($article_id, $tag_data[0]["id"]);
            }
        }
    }

    public function retriveAuthor($author_name = null, $author_id = null)
    {
        $author_query = $this->crud->retriveAuthorByNameOrNickname($author_name);
        $author = NULL;
        $author_id = NULL;

        if ($author_query != false) {
            $author_data = $author_query->fetchAll();
            if(isset($author_data[0]["id"], $author_data[0]["imie"], $author_data[0]["ilosc_wpisow"], $author_data[0]["pseudonim"]))
            $author = new Author($author_data[0]["id"], $author_data[0]["imie"], $author_data[0]["ilosc_wpisow"], $author_data[0]["pseudonim"]);
            return $author;
        } else {
            return null;
        }
    }

    private function concateTags($fetched_tags)
    {
        $tags = NULL;
        foreach ($fetched_tags as $tag_key => $tag_value) {
            $tags[$tag_key] = $tag_value['nazwa'];
        }
        return implode(", ", $tags);
    }
}