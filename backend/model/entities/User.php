<?php

class User
{
    private $login;
    private $password;

    public function __construct($login,$password)
    {
        $this->login = $login;
        $this->password = $password;
    }

    /**
     * @return User login
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @return User password
     */
    public function getPassword()
    {
        return $this->password;
    }
}