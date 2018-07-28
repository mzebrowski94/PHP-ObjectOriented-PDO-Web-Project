<?php
require 'DatabaseInitializer.php';
require 'consts/Constants.php';
require 'model/crud/CRUD.php';
require 'model/dao/ArticlesDAO.php';
require 'model/dao/UsersDAO.php';

session_start();

class DatabaseService
{
    private $db = NULL;
    private $articlesDAO = NULL;
    private $usersDAO = NULL;
    private $login = NULL;
    private $password = NULL;
    private $databaseLogin = NULL;
    private $databasePassword = NULL;
    public $isConnected = FALSE;
    public $isLogged = FALSE;

    public function __construct($login, $password)
    {
        $this->login = $login;
        $this->password = $password;
        $this->databaseLogin = "root";
        $this->databasePassword = "";
    }

    private function initializeDatabase()
    {
        $db_initializer = new DatabaseInitializer();
        $db_initializer->initDatabase();
    }

    public function connectToDatabase()
    {
        $login = $this->databaseLogin;
        $password = $this->databasePassword;

        $is_login = isset($login) && !empty($login);
        $is_password = isset($password);
        if ($is_login && $is_password) {
            $this->initializeDatabase();
            $this->db = $this->cerateConnection($login, $password);
            if (!is_null($this->db)) {
                $crud = new CRUD($this->db);
                $this->articlesDAO = new ArticlesDAO($crud);
                $this->usersDAO = new UsersDAO($crud);
            }
        }

        if (!is_null($this->articlesDAO) || !is_null($this->usersDAO)) {
            $this->isConnected = true;
        } else {
            echo "<br> HASŁO LUB LOGIN SĄ BŁĘDNE, BĄDŹ NIE UDAŁO SIĘ NAWIĄZAĆ POŁĄCZENIA Z BAZĄ DANYCH <br>";
            $this->isConnected = false;
        }
        return $this->isConnected;
    }

    public function processAuthnOperation($operation, $input_data)
    {
        $result = isset($operation);
        if ($result) {
            switch ($operation) {
                case 'Login':
                    if($this->loginUser($input_data['Login'],$input_data['Password'])){
                        $this->createCookies($input_data['Login'], $input_data['Password']);
                    }
                    break;
                case 'Logout':
                    $this->deleteCookies();
                    break;
                case 'Register':
                    $this->registerUser($input_data['Login'], $input_data['Password']);
                    break;
                default:
                    $result = false;
                    break;
            }
        }
    }

    private function loginUser($login, $password){
        $user = $this->usersDAO->retriveUser($login,$password);
        return !is_null($user);
    }

    private function registerUser($login, $password){
        $this->usersDAO->createUser($login,$password);
    }

    public function processDatabaseOperation($operation, $input_data)
    {
        if (!$this->isConnected) {
            echo "Błąd połączenia z bazą danych, nie udało się wykonać operacji.";
            die();
        }
        $result = NULL;
        $articles = NULL;
        $author = NULL;
        $article_to_edit = NULL;
        $is_operation_set = isset($operation);

        if ($is_operation_set) {
            switch ($operation) {
                case 'Search':
                    $articles = $this->articlesDAO->retriveArticles();
                    break;
                case 'Add':
                    $author_id = $this->articlesDAO->addArticle($input_data["Author"], $input_data["Title"], $input_data["Content"], $input_data["Tags"]);
                    $articles = $this->articlesDAO->retriveArticles($author_id);
                    break;
                case 'Delete':
                    $this->articlesDAO->delteArticle($input_data["article_id"]);
                    $articles = $this->articlesDAO->retriveArticles();
                    break;
                case 'Update':
                    $author_id = $this->articlesDAO->updateArticle($input_data["ArticleId"], $input_data["Author"], $input_data["Title"], $input_data["Content"], $input_data["Tags"]);
                    $articles = $this->articlesDAO->retriveArticles($author_id);
//                    update_article($_POST["Operation"]);
                    break;
                case 'PrepareToUpdateArticle':
                    $article_to_edit = $this->prepareArticleToEdit($input_data);
                    if (!is_null($article_to_edit)) {
                        $articles = $this->articlesDAO->retriveArticles($result["author_name"]);
                    }
                    break;
                case 'AuthorInfo':
                    $author = $this->articlesDAO->retriveAuthor($input_data["author_name"]);
                    if (!is_null($author)) {
                        $articles = $this->articlesDAO->retriveArticles($author->getId());
                    }
                    break;
                default:
                    $is_operation_set = false;
                    break;
            }
        }

        $result["operation"] = $operation;
        if (!is_null($articles)) {
            $result["articles"] = base64_encode(serialize($articles));
        }
        if (!is_null($author)) {
            $result["author_info"] = base64_encode(serialize($author));
        }
        if (!is_null($article_to_edit)) {
            $result["article_to_edit"] = base64_encode(serialize($article_to_edit));
        }
        return $result;
    }

    private function prepareArticleToEdit($input_data)
    {
        $article = NULL;
        if (!is_null($input_data)
            && isset($input_data["article_id"])
            && isset($input_data["author_name"])
            && isset($input_data["article_title"])
            && isset($input_data["article_tags"])
            && isset($input_data["article_content"])) {

            $article = new Article($input_data["article_id"],
                $input_data["author_name"],
                $input_data["article_title"],
                $input_data["article_content"],
                $input_data["article_tags"]);
        }
        return $article;
    }

    private function serializeStatementResult($query_statement)
    {
        $output = NULL;
        $param_type = get_class($query_statement);

        if ($param_type == 'PDOStatement') {
            $output = $query_statement->fetchAll();
            return serialize($output);
        } else {
            throw new RuntimeException('Parameter in serializeStatementResult is not an PDOStatement instance.');
        }
    }

    private function createCookies($login, $password)
    {
        $password = "Password: " . $password;
        $based_password = base64_encode($password);

        $time_to_next_day = time() + (86400 * 30);
        setcookie(C_USER_COOKIE_NAME, C_USER_COOKIE_NAME, $time_to_next_day, "/");
        setcookie(C_COOKIE_LOGIN, $login, $time_to_next_day, "/");
        setcookie(C_COOKIE_BASED_PASSWORD, $based_password, $time_to_next_day, "/");
        setcookie(C_COOKIE_ADDED_ARTICLES, 0, $time_to_next_day, "/");
        setcookie(C_COOKIE_UPDATED_ARTICLES, 0, $time_to_next_day, "/");
        setcookie(C_COOKIE_DELETED_ARTICLES, 0, $time_to_next_day, "/");
    }

    private function deleteCookies()
    {
        foreach ($_COOKIE as $key => $value) {
            if (isset($_COOKIE[$key])) {
                unset($_COOKIE[$key]);
                setcookie($key, null, -1, '/');
            }
        }
        $this->resetSesion();
    }

    private function cerateConnection($username, $password)
    {
        $server_name = "localhost";
        $database_name = 'article_database';
        try {
//            echo "<br>Próba połącznenia z bazą danych: ";
            $dsn = 'mysql:host=' . $server_name . ';dbname=' . $database_name;
            $db = new PDO($dsn, $username, $password);
//            echo 'POWODZENIE <br>';
        } catch (PDOException $e) {
            echo 'NIEPOWODZENIE <br>' . $e->getMessage() . '<br>';
            return NULL;
        }
        return $db;
    }

    private function resetSesion()
    {
        session_unset();
        session_destroy();
    }
}