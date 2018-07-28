<?php

class CRUD
{
    private $dbConnection = NULL;

    public function __construct($database_connection)
    {
        $param_type = get_class($database_connection);

        if ($param_type == 'PDO') {
            $this->dbConnection = $database_connection;
        } else {
            throw new RuntimeException('Parameter in constructor is not an PDO instance.');
        }
    }

    public function echo()
    {
        echo "Articles database CRUD Object";
    }

    //----------------ARTICLES--------------------//

    public function retriveArticles($author_id = null, $title = null)
    {
        $where_statement = "";
        if (!is_null($author_id)) {
            $where_statement .= "WHERE autor_id ='" . $author_id . "'";
        }
        if (!is_null($title)) {
            empty($where_statement) ? "WHERE tytul ='" . $title . "'" : " OR tytul ='" . $title . "'";
        }

        $statement = "SELECT id,tresc,tytul,autor_id FROM wpisy " . $where_statement;

        return $this->dbConnection->query($statement);
    }

    public function retriveArticleById($id)
    {
        $statement = "SELECT id,tresc,tytul,autor_id  FROM wpisy WHERE ( `id` =" . $id . ")";
        return $this->dbConnection->query($statement);
    }

    public function retriveArticlesByAuthor($author_id)
    {
        $statement = "SELECT id,tresc FROM wpisy WHERE ( `autor_id` =" . $author_id . ")";
        return $this->dbConnection->query($statement);
    }

    public function retriveArticleByAuthorAndTitle($author_id, $title)
    {
        $statement = "SELECT id,tresc FROM wpisy w WHERE ( w.autor_id ='" . $author_id . "' AND w.tytul ='" . $title . "') LIMIT 1";
        return $this->dbConnection->query($statement);
    }

    public function deleteArticle($id)
    {
        $statement = "DELETE FROM wpisy WHERE id = '" . $id . "'";
        return $this->dbConnection->query($statement);
    }

    public function createArticle($content, $title, $author_id)
    {
        $statement = "INSERT INTO wpisy (tytul, tresc, autor_id) VALUES ('" . $content . "','" . $title . "','" . $author_id . "')";
        return $this->dbConnection->query($statement);
    }

    public function updateArticle($article_id, $author_id, $title, $content)
    {
        $statement = "UPDATE wpisy SET tytul='" . $title . "', tresc='" . $content . "', autor_id='" . $author_id . "' WHERE id='" . $article_id . "'";
        return $this->dbConnection->query($statement);
    }

    //----------------AUTHORS--------------------//

    public function retriveAuthorById($id)
    {
        $statement = "SELECT id,imie,ilosc_wpisow,pseudonim FROM autorzy WHERE ( `id` =" . $id . ")";
        return $this->dbConnection->query($statement);
    }

    public function retriveAuthorByNameOrNickname($author_name)
    {
        $statement = "SELECT * FROM autorzy a WHERE ( a.pseudonim = '" . $author_name . "' OR  a.imie ='" . $author_name . "') LIMIT 1";
        return $this->dbConnection->query($statement);
    }

    //----------------TAGS--------------------//

    public function retriveTagsByArticleId($id)
    {
        $statement = "SELECT t.id, t.nazwa FROM tagi t, wpisy_tagi wt WHERE (t.id = wt.tag_id AND wt.wpis_id =" . $id . ")";
        return $this->dbConnection->query($statement);
    }

    public function retriveTagByContent($content)
    {
        $statement = "SELECT t.id, t.nazwa FROM tagi t WHERE ( t.nazwa = '" . $content . "')";
        return $this->dbConnection->query($statement);
    }

    public function createTag($content)
    {
        $statement = "INSERT INTO tagi (nazwa) VALUES ('" . $content . "')";
        return $this->dbConnection->query($statement);
    }

    //----------------TAGS_ARTICLES--------------------//

    public function addTagForArticle($article_id, $tag_id)
    {
        $statement = "INSERT INTO wpisy_tagi (wpis_id, tag_id) VALUES ('" . $article_id . "','" . $tag_id . "')";
        return $this->dbConnection->query($statement);
    }

    //---------------------USERS-------------------------//

    public function createUser($login, $password)
    {
        $statement = "INSERT INTO uzytkownicy (login, haslo) VALUES ('" . $login . "','".$password."')";
        return $this->dbConnection->query($statement);
    }

    public function retriveUser($login, $password)
    {
        $statement = "SELECT u.login, u.haslo FROM uzytkownicy u WHERE (u.login = '" . $login . "' AND u.haslo = '" . $password . "')";
        return $this->dbConnection->query($statement);
    }

}