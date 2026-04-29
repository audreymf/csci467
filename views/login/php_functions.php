<?php

require 'server_connect.php';

function checkEmail($pdo, $email) {

	$sql = "SELECT COUNT(*) FROM sales_associates WHERE userID = '$email'";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	$count = $stmt->fetchColumn();
	return $count;

}


function checkApproval($pdo, $email) {

	$sql = "SELECT is_approved FROM sales_associates WHERE userID = '$email'";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	$status = $stmt->fetchColumn();
	return $status;
}


function checkLogin($pdo, $email, $password) {

	$sql = "SELECT COUNT(*) FROM sales_associates WHERE BINARY userID = '$email' AND BINARY password = '$password'";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	$count = $stmt->fetchColumn();
	return $count;

}


function retrieveInfo($pdo, $email, $password) {

	$sql = "SELECT * FROM sales_associates WHERE BINARY userID = '$email' AND BINARY password = '$password'";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	$userData = $stmt->fetch();
	return $userData;

}


function redirectClient($accessLevel) {

       if($accessLevel === 'sales')
	{
		header("Location: ./salesassociate.php");
		exit;	
	}
	else if($accessLevel == 'admin')
	{
		header("Location: ./admin_ui.php"); // THIS HAS TO BE UPDATED FOR WHATEVER THE PAGE NAME IS & ITS LOCATION
		exit;
	}
	else if($accessLevel == 'hq')
	{
		header("Location: ./hq/review_quotes.php"); // THIS HAS TO BE UPDATED FOR WHATEVER THE PAGE NAME IS & ITS LOCATION
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

	header("Location: ./login.php");
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


function retrieveQuoteInfo($pdo, $id) {

	$sql = "SELECT * FROM quotes WHERE associateID = '$id' AND status <> 'finalized' AND status <> 'sanctioned' AND status <> 'ordered' ORDER BY id DESC";
	$stmt = $pdo->prepare($sql);
        $stmt->execute();
	$quoteData = $stmt->fetchAll();
	return $quoteData;
}


function retrieveSingleQuoteInfo($pdo, $quoteID) {

	$sql = "SELECT * FROM quotes WHERE id = '$quoteID'";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	$quoteData = $stmt->fetch();
	return $quoteData;

}


function retrieveLIInfo($pdo, $quoteID) {

	$sql = "SELECT * FROM line_items WHERE quoteID = $quoteID";
	$stmt = $pdo->prepare($sql);
        $stmt->execute();
	$LIData = $stmt->fetchAll();
	return $LIData;
}


function retrieveCustomerInfo($legacypdo) {

	$sql = "SELECT * FROM customers ORDER BY name ASC";
	$stmt = $legacypdo->prepare($sql);
        $stmt->execute();
	$customersData = $stmt->fetchAll();
	return $customersData;
}


function retrievePartsInfo($legacypdo) {

	$sql = "SELECT * FROM parts";
	$stmt = $legacypdo->prepare($sql);
        $stmt->execute();
	$partsData = $stmt->fetchAll();
	return $partsData;
}


function retrieveFinalizedInfo($pdo, $userID) {

	$sql = "SELECT * FROM quotes WHERE associateID = '$userID' AND (status = 'finalized' OR status = 'sanctioned') ORDER BY id DESC";
	$stmt = $pdo->prepare($sql);
        $stmt->execute();
	$quoteData = $stmt->fetchAll();
	return $quoteData;
}

function retrieveOrderedInfo($pdo, $userID) {

	$sql = "SELECT * FROM quotes WHERE associateID = '$userID' AND status = 'ordered' ORDER BY id DESC";
	$stmt = $pdo->prepare($sql);
        $stmt->execute();
	$quoteData = $stmt->fetchAll();
	return $quoteData;
}

function checkExists($pdo, $associateID, $quoteID) {

	$sql = "SELECT COUNT(*) FROM quotes WHERE associateID = '$associateID' AND id = '$quoteID' AND status <> 'finalized' AND status <> 'sanctioned' AND status <> 'ordered'";
        $stmt = $pdo->prepare($sql);
	$stmt->execute();
	$count = $stmt->fetchColumn();
	return $count;


}

function checkFinalized($pdo, $userID) {

	$sql = "SELECT COUNT(*) FROM quotes WHERE associateID = '$userID' AND (status='finalized' OR status='sanctioned')"; 
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	$count = $stmt->fetchColumn();
	return $count;

}

function checkOrdered($pdo, $userID) {

	$sql = "SELECT COUNT(*) FROM quotes WHERE associateID = '$userID' AND status='ordered'"; 
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	$count = $stmt->fetchColumn();
	return $count;

}


function submitNewQuote($pdo, $associateID, $customerID, $customerEmail, $status) {

	$sql = "INSERT INTO quotes (associateID, customerID, email, status) VALUES ('$associateID', '$customerID', '$customerEmail', '$status')";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();

	$id = $pdo->lastInsertId();
	return $id;

}

function insertLineItems($pdo, $quoteID, $services, $prices) {

	foreach($services as $i => $service) {

		$price = $prices[$i];
	
		$sql = "INSERT INTO line_items (quoteID, item, price) VALUES ('$quoteID', '$service', '$price')";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
	
	}

}

function insertNotes($pdo, $quoteID, $notes, $notestatus) {

	foreach($notes as $i => $note) {

		if(!empty($note))
		{
  		  $status = $notestatus[$i];
	
		  $sql = "INSERT INTO notes (quoteID, content, is_secret) VALUES ('$quoteID', '$note', '$status')";
		  $stmt = $pdo->prepare($sql);
		  $stmt->execute();
		}
	
	}

}

function insertNote($pdo, $quoteID, $note) {

	if(!empty($note))
	{

	    $sql = "INSERT INTO notes (quoteID, content, is_secret) VALUES ('$quoteID', '$note', 0)";
	    $stmt = $pdo->prepare($sql);
	    $stmt->execute();

	}

}

function retrieveNotes($pdo, $quoteID) {


	$sql = "SELECT * FROM notes WHERE quoteID = '$quoteID' ORDER BY id DESC";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	$notesData = $stmt->fetchAll();
	return $notesData;



}

function openModal() {

	echo "<script>
              document.addEventListener('DOMContentLoaded', function () {
              var modal = new bootstrap.Modal(document.getElementById('editQuote'));
              modal.show();
              });
              </script>";


}

function deleteLI($pdo, $quoteID, $itemName) {

	$sql = "DELETE FROM line_items WHERE quoteID = '$quoteID' AND item = '$itemName'";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
}

function deleteQuote($pdo, $quoteID) {

	$sql = "DELETE FROM quotes WHERE id  = '$quoteID'";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();

	$sql = "DELETE FROM line_items WHERE quoteID = '$quoteID'";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();

	$sql = "DELETE FROM notes WHERE quoteID = '$quoteID'";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();

}

function updateQuote($pdo, $customerID, $quoteID, $email, $status) {

	$currentData = retrieveSingleQuoteInfo($pdo, $quoteID);

	if($customerID != $currentData['customerID'])
	{
		$sql = "UPDATE quotes SET customerID = '$customerID' WHERE id = '$quoteID'";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
	}

	if($email != $currentData['email'])
	{
		$sql = "UPDATE quotes SET email = '$email' WHERE id = '$quoteID'";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
	}

	if($status != $currentData['status'])
	{
		$sql = "UPDATE quotes SET status = '$status' WHERE id = '$quoteID'";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
	}

}


?>
