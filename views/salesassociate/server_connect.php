<?php

try {
        $username = "z1989163";
        $password = "ege467";
        $dsn = "mysql:host=courses;dbname=z1989163";
        $pdo = new PDO($dsn, $username, $password);
}
catch(PDOexception $e){
        echo "Failed to connect to database" . $e->getmessage();
}


try {

	$legacyhost = "blitz.cs.niu.edu";
	$legacyport = 3306;
	$legacydb = "csci467";
	$legacyusername = "student";
	$legacypassword = "student";

	$legacydsn = "mysql:host=$legacyhost;port=$legacyport;dbname=$legacydb";
	$legacypdo = new PDO($legacydsn, $legacyusername, $legacypassword);


}
catch(PDOexception $e){
	echo "Failed to connect to legacy database" . $e->getmessage();
}

?>
