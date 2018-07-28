<?php

class DatabaseInitializer
{
    private $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $dbName = 'article_database';
    private $dbConnection;

    public function initDatabase()
    {
        $this->createDatabase();
        $this->connectToDatabase();
        $this->createDatabaseStructure();
        $this->createTestData();
    }

    private function createDatabase()
    {
        try {
            $dbh = new PDO("mysql:host=localhost", $this->username, $this->password);

            $db_create_query = "CREATE DATABASE IF NOT EXISTS `" . $this->dbName . "` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
		CREATE USER 'admin'@'localhost' IDENTIFIED BY 'admin';
		GRANT ALL PRIVILEGES ON `articles_database`.`*` TO 'admin'@'localhost';
		FLUSH PRIVILEGES;";

            $dbh->exec($db_create_query);
        } catch (PDOException $e) {
            echo "<br>Próba utworzenia bazy danych: NIEPOWODZENIE<br>";
            die("Błąd tworzenia bazy danych: " . $e->getMessage());
        }
    }

    private function connectToDatabase()
    {
        try {
            $dsn = 'mysql:host=' . $this->servername . ';dbname=' . $this->dbName;
            $this->dbConnection = new PDO($dsn, $this->username, $this->password);
        } catch (PDOException $e) {
            echo '<br> Próba połącznenia z bazą danych: NIEPOWODZENIE <br>' . $e->getMessage();
        }
    }

    private function createDatabaseStructure()
    {
        $authors_table = "CREATE TABLE IF NOT EXISTS `autorzy` (
          `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `imie` VARCHAR(10) NOT NULL UNIQUE,
          `ilosc_wpisow` INT NOT NULL,
          `pseudonim` VARCHAR(10) NOT NULL UNIQUE,
          PRIMARY KEY `pk_id`(`id`)
        )";

        $articles_table = "CREATE TABLE IF NOT EXISTS `wpisy` (
          `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `tresc` VARCHAR(255) NOT NULL,
          `tytul` VARCHAR(50) NOT NULL UNIQUE,
          `autor_id` INT UNSIGNED NOT NULL,
          PRIMARY KEY `pk_id`(`id`),
        
          CONSTRAINT `fk_wpisy_autorzy`
            FOREIGN KEY (`autor_id`)
            REFERENCES `autorzy` (`id`)
            ON DELETE CASCADE
            ON UPDATE CASCADE
        )";

        $tags_table = "CREATE TABLE IF NOT EXISTS `tagi` (
          `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `nazwa` VARCHAR(255) UNIQUE,
          PRIMARY KEY `pk_id`(`id`)
        )";

        $articles_tags_table = "CREATE INDEX `id_idx` ON `wpisy` (`id` ASC);
        CREATE INDEX `id_idx` ON `tagi` (`id` ASC);
        
        CREATE TABLE IF NOT EXISTS `wpisy_tagi` (
          `wpis_id` INT UNSIGNED NOT NULL,
          `tag_id` INT UNSIGNED NOT NULL,
        
          PRIMARY KEY (`wpis_id`, `tag_id`),
          INDEX `fk_wpisy_tagi_wpisy_id_idx` (`wpis_id` ASC),
          INDEX `fk_wpisy_tagi_tagi_id_idx` (`tag_id` ASC),
        
          CONSTRAINT `fk_wpisy_tagi_wpisy`
            FOREIGN KEY (`wpis_id`)
            REFERENCES `wpisy` (`id`)
            ON DELETE CASCADE
            ON UPDATE CASCADE,
        
          CONSTRAINT `fk_wpisy_tagi_tagi`
            FOREIGN KEY (`tag_id`)
            REFERENCES `tagi` (`id`)
            ON DELETE CASCADE
            ON UPDATE CASCADE)";

        $users_table = "CREATE TABLE IF NOT EXISTS `uzytkownicy` (
          `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `login` VARCHAR(10) UNIQUE,
          `haslo` VARCHAR(10),
          PRIMARY KEY `pk_id`(`id`)
        ) ";

        try {
            $this->dbConnection->exec($users_table);
            $this->dbConnection->exec($authors_table);
            $this->dbConnection->exec($articles_table);
            $this->dbConnection->exec($tags_table);
            $this->dbConnection->exec($articles_tags_table);
        } catch (PDOException $e) {
            echo "<br>Próba inicjalizacji struktury bazy danych: NIEPOWODZENIE <br>";
            die("Błąd inicjalizacji struktury bazy danych: " . $e->getMessage());
        }
    }

    private function createTestData()
    {
        $insert_authors = "INSERT INTO `autorzy`(imie, ilosc_wpisow, pseudonim) VALUES
('Mateusz','2','Student'), ('Piotr','4','Uczeń'), ('Marek', '3', 'Profesor');";

        $insert_articles = "INSERT INTO `wpisy`(tresc,tytul,autor_id) VALUES 
        ('To jest fajny artykul Mateusza','Art Mat','1'),
        ('To jest dobry artykul Piotra','Art Pio','2'),
        ('To jest mądry artykul Marka','Art Mar','3');";

        $insert_tags = "INSERT INTO `tagi`(nazwa) VALUES ('Fajny'),('Dobry'),('Mądry');";

        $insert_articles_tags = "INSERT INTO `wpisy_tagi`(wpis_id,tag_id) VALUES ('1','1'),('2','2'),('3','3');";
        try {
            $this->dbConnection->exec($insert_authors);
            $this->dbConnection->exec($insert_articles);
            $this->dbConnection->exec($insert_tags);
            $this->dbConnection->exec($insert_articles_tags);
        } catch (PDOException $e) {
            echo "<br>Próba inicjalizacji danych w bazie: NIEPOWODZENIE<br>";
            die("Błąd inicjalizacji danych: " . $e->getMessage());
        }

    }
}