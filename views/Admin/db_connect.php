<?php

$host = "blitz.cs.niu.edu";
$dbname = "csci467";
$username = "student";
$password = "student";

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
    $pdoc = new PDO($dsn, $username, $password);
    $pdoc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } 
     catch (PDOException $e){
     die("Legacy database connection failed: " . $e->getMessage());
     }

#connect quote database
$host = 'courses';              
$dbname = 'z1989163';            
$username = 'z1989163';        
$password = 'ege467'; 

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
    $pdoq = new PDO($dsn, $username, $password);
    $pdoq->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } 
    catch (PDOException $e) {
    die("Quotes database connection failed: " . $e->getMessage());
			    }

?> 
