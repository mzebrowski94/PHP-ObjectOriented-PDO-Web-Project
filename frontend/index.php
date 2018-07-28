<?php
$root_path = $_SERVER['DOCUMENT_ROOT'];
$backend_path = $root_path . "/PHP_PROJECT/Backend";

include_once "$backend_path/consts/Constants.php";
include_once "$backend_path/model/entities/Article.php";
include_once "$backend_path/model/entities/Author.php";

session_name(C_SESSION_ID);
session_start();
init_session_vars();

$is_user_logged = isset($_COOKIE[C_USER_COOKIE_NAME]);

function retrive_cookie()
{
    if (!isset($_COOKIE[C_USER_COOKIE_NAME])) {
        echo "<br>Nie znaleziono ciasteczka o nazwie: '" . C_USER_COOKIE_NAME . "'<br>";
        echo "<br><b>Zaloguj się aby utworzyć ciasteczko.</b>";
    } else {
        echo "<br> Login: " . $_COOKIE[C_COOKIE_LOGIN];
        echo "<br> Hasło (base64): " . $_COOKIE[C_COOKIE_BASED_PASSWORD];
        echo "<br> Dodanych artykułów: " . $_COOKIE[C_COOKIE_ADDED_ARTICLES];
        echo "<br> Zaktualizowanych artykułów: " . $_COOKIE[C_COOKIE_UPDATED_ARTICLES];
        echo "<br> Usuniętych artykułów: " . $_COOKIE[C_COOKIE_DELETED_ARTICLES];
    }
}

function retrive_session()
{
    if (session_status() == PHP_SESSION_NONE) {
        echo "<br> Stan sesji: Sesja nie utworzona";
    } else {
        $id = array_key_exists(C_SESSION_ID, $_SESSION) ? $_SESSION[C_SESSION_ID] : C_SESSION_VAR_NOT_FOUND;
        $create_date = array_key_exists(C_SESSION_CREATE_DATE, $_SESSION) ? $_SESSION[C_SESSION_CREATE_DATE] : C_SESSION_VAR_NOT_FOUND;
        $refresh_count = array_key_exists(C_SESSION_REFRESH_COUNT, $_SESSION) ? $_SESSION[C_SESSION_REFRESH_COUNT] . " razy" : C_SESSION_VAR_NOT_FOUND;
        echo "<b>Stan sesji: </b>Utworzona";
        echo "<br><b>Id: </b>" . $id;
        echo "<br><b>Data utworzenia: <br></b>" . $create_date;
        echo "<br><b>Stronę odświeżono: </b>" . $refresh_count;
    }
}

function generate_table()
{
    $results = NULL;
    if (isset($_POST) && isset($_POST["result"])) {
        $results = unserialize(base64_decode($_POST["result"]));

        if (isset($results["articles"])) {
            $articles_data = $results["articles"];
            $articles = unserialize(base64_decode($articles_data));
            foreach ($articles as $article) {
                echo "<tr>";
                echo "<td>" . $article->getId() . "</td>";
                echo "<td>" . $article->getAuthorName() . "</td>";
                echo "<td>" . $article->getTitle() . "</td>";
                echo "<td>" . $article->getTags() . "</td>";
                echo "<td>" . $article->getContent() . "</td>";
                echo "<td><form action=\"../backend/DatabaseController.php\" method=\"post\"
                      enctype=\"multipart/form-data\"
                      accept-charset=\"utf-8\">
                      <input type='hidden' name='OperationType' value='DatabaseOperation'>
                      <input type='hidden' name='article_id' value='" . $article->getId() . "'>
                      <input type='hidden' name='author_name' value='" . $article->getAuthorName() . "'>
                      <button type='submit' name='Operation' value='Delete'>Usuń</button>
                      <button type='submit' name='Operation' value='AuthorInfo'>O autorze</button>
                </form>
                <form action=\"../backend/DatabaseController.php\" method=\"post\"
                      enctype=\"multipart/form-data\"
                      accept-charset=\"utf-8\">
                      <input type='hidden' name='OperationType' value='DatabaseOperation'>
                      <input type='hidden' name='article_id' value='" . $article->getId() . "'>
                      <input type='hidden' name='author_name' value='" . $article->getAuthorName() . "'>
                      <input type='hidden' name='article_title' value='" . $article->getTitle() . "'>
                      <input type='hidden' name='article_tags' value='" . $article->getTags() . "'>
                      <input type='hidden' name='article_content' value='" . $article->getContent() . "'>
                      <button type='submit' name='Operation' value='PrepareToUpdateArticle'>Edytuj</button>
                </form></td>";
                echo "</tr>";
            }
        }
    }
}

function generate_author_info()
{
    echo "<h4>Informacje o autorze:</h4>";

    $results = NULL;
    if (isset($_POST, $_POST["result"])) {
        $results = unserialize(base64_decode($_POST["result"]));
    }

    if (isset($results["author_info"])) {
        $auhor = unserialize(base64_decode($results["author_info"]));
        echo "<label>Id: </label>" . $auhor->getId() . "<br>";
        echo "<label>Imię: </label>" . $auhor->getName() . "<br>";
        echo "<label>Pseudonim: </label>" . $auhor->getNickname() . "<br>";
        echo "<label>Ilość wpisów: </label>" . $auhor->getArticlesAmount() . "<br>";
    } else {
        echo "<h5>Wyszukaj artykuły a następnie wybierz opcję 'O autorze' aby wyświetlić informacje na jego temat.</h5>";
    }
}

function generate_article_update_form($is_user_logged)
{
    $results = NULL;
    if (isset($_POST) && isset($_POST["result"])) {
        $results = unserialize(base64_decode($_POST["result"]));
    }

    $article_id = "";
    $article_author_name = "";
    $article_title = "";
    $article_content = "Tutaj podaj treść artykułu";
    $article_tags = "Tagi, tag";
    $is_article_updating = false;
    if (isset($results["article_to_edit"])) {
        $article = unserialize(base64_decode($results["article_to_edit"]));
        $article_id = $article->getId();
        $article_author_name = $article->getAuthorName();
        $article_title = $article->getTitle();
        $article_content = $article->getContent();
        $article_tags = $article->getTags();
        $is_article_updating = true;
    }

    echo "<form class=\"article_formula\" action=\"../backend/DatabaseController.php\" id=\"article_form\" method=\"post\"
                      enctype=\"multipart/form-data\"
                      accept-charset=\"utf-8\">

                    Podaj autora artykułu:<br>
                    <input type=\"text\" name=\"Author\" value='" . $article_author_name . "' placeholder=\"Autor artykułu\">
                    <br>Podaj tytuł artykułu:<br>
                    <input type=\"text\" name=\"Title\" value='" . $article_title . "' placeholder=\"Tytuł artykułu\">
                    <br>
                    <br>Jeśli dodajesz/edytujesz artykuł, wpisz treść: <br>
                    <label>
                        <textarea name=\"Content\" form=\"article_form\" rows=\"8\"
                                  cols=\"47\">" . $article_content . "</textarea>
                    </label>
                    <br>
                    <br>Jeśli dodajesz/edytujesz lub szukasz artykułów podaj tagi (po przecinku):<br>
                    <input type=\"text\" name=\"Tags\" value='" . $article_tags . "' placeholder=\"tagi, tag\">
                    <br>
                    <input type=\"hidden\" name=\"OperationType\" value=\"DatabaseOperation\">
                    <input type=\"hidden\" name=\"ArticleId\" value='" . $article_id . "'>
                    <button type=\"submit\" name=\"Operation\" value=\"Search\" " . (!$is_user_logged ? "disabled" : "") . ">Wyszukaj</button>
                    <button type=\"submit\" name=\"Operation\" value=\"Add\" " . (($is_article_updating || !$is_user_logged) ? "disabled" : "") . ">Dodaj</button>
                    <button type=\"submit\" name=\"Operation\" value=\"Update\" " . (($is_article_updating && $is_user_logged) ? "" : "disabled") . ">Aktualizuj</button>
                </form>";
}

function init_session_vars()
{
    if (!isset($_SESSION[C_SESSION_CREATE_DATE])) {
        $_SESSION[C_SESSION_ID] = session_name();
        $_SESSION[C_SESSION_CREATE_DATE] = date("Y-m-d H:i:s");
        $_SESSION[C_SESSION_REFRESH_COUNT] = 0;
    } else {
        $_SESSION[C_SESSION_REFRESH_COUNT] += 1;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edytor treści bazy danych artykułów naukowych</title>
    <link rel='stylesheet' type='text/css' href='css/style.css'/>
</head>
<body>
<div id="logo">
    <b>Edytor treści bazy danych artykułów naukowych</b>
</div>
<div class="fixed_form">
    <div class="informations_form">
        <b>Ciasteczo:
            <hr>
            <?php retrive_cookie() ?>
        </b>

    </div>
    <div class="informations_form">
        <b>Sesja:
            <hr>
        </b>
        <?php retrive_session() ?>
    </div>
</div>
<div id="container">
    <h4>Witaj użytkowniku <u><?php echo($is_user_logged ? $_COOKIE[C_COOKIE_LOGIN] : "") ?></u></h4>
    <h5>Strona ta pozwala na dodawanie, usuwanie, aktualizowanie, oraz przeglądanie artykułów umieszczonych w bazie
        danych.<br>
        Wypełnij odpowiednie pola, a następnie wybierz operację, którą chcesz wykonać.
    </h5>
    <form action="../backend/DatabaseController.php" id="login_form" method="post" enctype="multipart/form-data"
          accept-charset="utf-8">
        <hr>
        Podaj login oraz hasło użytkownika aby wyświetlić i edytować artykuły. (Możesz też zarejestrować nowego
        użytkownika)<br>
        Login: <input type="text" name="Login" value="" placeholder="Podaj login"><br>
        Hasło: <input type="password" name="Password" value="" placeholder="Podaj hasło"><br>
        <input type="hidden" name="OperationType" value="AuthnOperation">
        <button type="submit" name="Operation" value="Login"<?php echo($is_user_logged ? "disabled" : ""); ?>>Zaloguj
            (utwórz ciasteczko)
        </button>
        <button type="submit" name="Operation" value="Logout"<?php echo(!$is_user_logged ? "disabled" : ""); ?>>Wyloguj
            się (usuń ciasteczko i resetuj sesję)
        </button>
        <button type="submit" name="Operation" value="Register">Zarejestruj użytkownika</button>
        <hr>
    </form>

    <table>
        <tr>
            <th>Artykuły:</th>
            <th>Formularz:</th>
        </tr>
        <tr>
            <!--Article search table-->
            <td>
                <table>
                    <tr>
                        <th>Id:</th>
                        <th>Autor:</th>
                        <th>Tytuł:</th>
                        <th>Tagi:</th>
                        <th>Treść:</th>
                        <th>Opcje:</th>
                    </tr>
                    <?php
                    generate_table();
                    ?>
                </table>
            </td>

            <!--Article form-->
            <td>
                <?php
                generate_article_update_form($is_user_logged);
                ?>
                <hr>
                <?php
                generate_author_info();
                ?>
            </td>
        </tr>

    </table>
</div>
</body>
</html>