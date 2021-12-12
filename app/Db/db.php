<?php 

namespace  App\Db;

// use Dotenv\Dotenv;
use PDO;
use PDOException;

class DB {
    // This is the Database connection class which can be called to start a PDO connection
    public function connect(){
        // // NOTE: When running POSTman, require config code. Otherwise, comment out when pushing to prod
        // require_once __DIR__ . '/../../vendor/autoload.php';
        // $dotenv = Dotenv::createImmutable(__DIR__."\\..\\..\\");
        // $dotenv->load();

        try{
            // // LOCAL DATABASE, DEVELOPMENT  DATABASE CONNECTION
            // $conn = new PDO($dsn, $user, $pass);

            // PRODUCTION DATABASE CONNECTION
            // $conn = new \PDO("mysql:host=".$_ENV['DB_HOST'].";dbname=".$_ENV['DB_NAME'].";charset=utf8mb4", $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);

            // DEVELOPMENT PRODUCTION DATABASE CONNECTION
            require __DIR__ . '/hidden.php';
            $conn = new \PDO($conn_dsn, $conn_user, $conn_pass);

            // DEVELOPMENT LOCAL DATABASE CONNECTION
            // require __DIR__ . '/hiddenLocal.php';
            // $conn = new \PDO($conn_dsn, $conn_user, $conn_pass);

            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $conn;

        } catch(PDOException $e){
            echo "Database Connection Error, please check your connection file.";
            throw new \PDOException($e->getMessage());
        }
    }
}





?>