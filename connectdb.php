<?php
// start a session
session_start();
require('environment.php');

/******************************/
// connecting to DB on XAMPP (local)

// $username = $_ENV['LOCAL_USERNAME'];
// $password = $_ENV['LOCAL_PASSWORD'];
// $host = $_ENV['LOCAL_HOST'];
// $dbname = $_ENV['LOCAL_DBNAME'];


/******************************/
// connecting to DB on CS server

$username = $_ENV['CS_USERNAME'];
$password = $_ENV['CS_PASSWORD'];
$host = $_ENV['CS_HOST'];
$dbname = $_ENV['CS_DBNAME'];

/******************************/

$dsn = "mysql:host=$host;dbname=$dbname";
$db;  // changed from string to object (used to be $db = "";)

/** connect to the database **/
try {
   // $db = new PDO($dsn, $username, $password);   
   $db = mysqli_connect($host, $username, $password, $dbname);
   // echo "<p>You are connected to the database</p>";
} catch (PDOException $e)     // handle a PDO exception (errors thrown by the PDO library)
{
   // Call a method from any object, 
   // use the object's name followed by -> and then method's name
   // All exception objects provide a getMessage() method that returns the error message 
   $error_message = $e->getMessage();
   echo "<p>An error occurred while connecting to the database: $error_message </p>";
} catch (Exception $e)       // handle any type of exception
{
   $error_message = $e->getMessage();
   echo "<p>Error message: $error_message </p>";
}
