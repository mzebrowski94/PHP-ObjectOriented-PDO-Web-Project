<?php
require 'DatabaseService.php';

do_post();

function do_post()
{
    $result = null;
    if (isset($_COOKIE[C_USER_COOKIE_NAME]) && isset($_COOKIE[C_COOKIE_LOGIN]) && isset($_COOKIE[C_COOKIE_BASED_PASSWORD])) {
        $password = resolve_password($_COOKIE[C_COOKIE_BASED_PASSWORD]);
        $dbservice = new DatabaseService($_COOKIE[C_COOKIE_LOGIN], $password);
    } else if (isset($_POST["Login"]) && isset($_POST["Password"])) {
        //Database service
        $dbservice = new DatabaseService($_POST["Login"], $_POST["Password"]);
    } else {
        echo "Logging error";
        die();
    }

    //Process proper operation
    if (isset($_POST["OperationType"])) {
        switch ($_POST["OperationType"]) {
            case "DatabaseOperation":
                //Create database if not exists and connect
                $dbservice->connectToDatabase();
                $result = $dbservice->processDatabaseOperation($_POST["Operation"], $_POST);
                break;
            case "AuthnOperation":
                $dbservice->connectToDatabase();
                $dbservice->processAuthnOperation($_POST["Operation"], $_POST);
        }
    }

    //Redirect
    redirect($result);
}


function resolve_password($based_password)
{
    $decoded = base64_decode($based_password);
    $password = trim("Password: ", $decoded);
    return $password;
}


function redirect($result = null)
{
    if(!is_null($result)){
        $based_result = base64_encode(serialize($result));
        ?><form name='result_form' id='result_form' method='POST'>
        <input type='hidden' name='result' value='<?php echo $based_result?>'>
        </form>
        <script type='text/javascript'>
            var form = document.getElementById("result_form");
            form.action = '../frontend/index.php';
            form.submit();
        </script><?php
    } else {
        //todo...
        $url = "http://php.project/PHP_PROJECT/frontend/";
        header('Location: ' . $url, true, 303);
        die();
    }
}
