<?php
  $host = "courses";
  $dbname = "z1989163
  $username = "z1989163";
  $password = "ege467";

  //Connection to mariadb
  try{
    $dsn = "mysql:host=courses;dbname=z1989163";
    $pdo = new PDO($dsn, $username, $password);
  }
  catch(PDOException $e){
  echo "Connection to database failed: " . $e->getMessage();
  }
?>
