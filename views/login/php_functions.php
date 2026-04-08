<?php

require 'server_connect.php';

function checkEmail($pdo, $email) {

	$sql = "SELECT COUNT(*) FROM Users467 WHERE email = '$email'";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	$count = $stmt->fetchColumn();
	return $count;

}


function checkApproval($pdo, $email) {

	$sql = "SELECT acc_approved FROM Users467 WHERE email = '$email'";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	$status = $stmt->fetchColumn();
	return $status;
}


function checkLogin($pdo, $email, $password) {

	$sql = "SELECT COUNT(*) FROM Users467 WHERE BINARY email = '$email' AND BINARY password = '$password'";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	$count = $stmt->fetchColumn();
	return $count;

}


function retrieveInfo($pdo, $email, $password) {

	$sql = "SELECT * FROM Users467 WHERE BINARY email = '$email' AND BINARY password = '$password'";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	$userData = $stmt->fetch();
	return $userData;

}


function redirectClient($accessLevel) {

if($accessLevel == 0)
	{
		header("Location: ./testpages/levelzero.php");
		exit;	
	}
	else if($accessLevel == 1)
	{
		header("Location: ./testpages/levelone.php");
		exit;
	}
	else if($accessLevel == 2)
	{
		header("Location: ./testpages/leveltwo.php");
		exit;
	}
	else
	{
		header("Location: login.php");
		exit;
	}

}


function validateLogin() {

   session_start();

   if($_SESSION['login_authenticated'] != TRUE)
   {
   	$_SESSION = array();
	session_destroy();

	header("Location: login.php");
	exit;
   }

}

function endSession() {

	$_SESSION = array();
	session_destroy();

	header("Location: login.php");
	exit;

}

function checkAccessLevel($access_level) {

	session_start();

	if($_SESSION['access_level'] != $access_level)
	{
           endSession();
	}


}


function checkEmptyFields($fname, $lname, $email, $password) {

	if(empty($fname))
	{
	   return 1;
	}
	else if(empty($lname))
	{
	   return 1;
	}
	else if(empty($email))
	{
	   return 1;
	}
	else if(empty($password))
	{
	   return 1;
	}
	else
	{
	   return 0;
	}
}

?>
