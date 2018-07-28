<?php
$root_path = $_SERVER['DOCUMENT_ROOT'];
$entities_path = $root_path . "/PHP_PROJECT/backend/model/entities";
require "$entities_path/User.php";

class UsersDAO
{
    private $crud = NULL;

    public function __construct($crud)
    {
        $this->crud = $crud;
    }

    public function createUser($login, $password)
    {
        if(!is_null($login) && !empty($login)
        && !is_null($password) && !empty($password)){
            $user_query = $this->crud->createUser($login, $password);
        }
    }

    public function retriveUser($login, $password)
    {
        $user = NULL;
        if(!is_null($login) && !empty($login)
            && !is_null($password) && !empty($password)){
            $user_query  = $this->crud->retriveUser($login, $password);
            if($user_query != false){
                $user_data = $user_query->fetchAll();
                if(isset($user_data[0]["login"]) && isset($user_data[0]["haslo"]))
                $user = new User($user_data[0]["login"],$user_data[0]["haslo"]);
            }
        }
        return $user;
    }
}