<?php

require 'server_connect.php';
require 'php_functions.php';
session_start();

if($_SERVER["REQUEST_METHOD"] === "POST")
{

	$creation_success = FALSE;

	$submitted_fname = $_POST['fname'];
	$submitted_lname = $_POST['lname'];
	$submitted_email = $_POST['email'];
	$submitted_pass = $_POST['password'];
	$retype = $_POST['retype'];

	$checkFields = checkEmptyFields($submitted_fname, $submitted_lname, $submitted_email, $submitted_pass);
	$checkExists = checkEmail($pdo, $submitted_email);

	if($submitted_pass == $retype)
	{
	  if($checkExists == 0 && $checkFields == 0)
	  {
		  $sql = "INSERT INTO Users467 (email, password, fname, lname) VALUES ('$submitted_email', '$submitted_pass', '$submitted_fname', '$submitted_lname');";
		  $stmt = $pdo->prepare($sql);
		  $stmt->execute();

	  	  $creation_success = TRUE;
	  }

	}
}

?>

<html>

<head>
<link rel="stylesheet" href="login.css">
<title>CSCI 467 Create Account Page</title>
</head>

<body>


<div class="login_window">

<!-- <img class="login_image" src="../personal/images/Generic_Logo.png"> -->

<form action="" method="POST">

<h1 class="logintext">Create an Account</h1>
<!-- <p class="logintext">Fill in the below info to submit for account creation</p>-->

<?php
  if($creation_success == TRUE)
  {
   echo "<h4 class='successmessage'>Account Creation Successful!</h4>";
  }
  else if($submitted_pass != $retype)
  {
   echo "<h4 class='errormessage'>Passwords do not match</h4>";
  }
  else if($checkExists > 0)
  {
   echo "<h4 class='errormessage'>This email is already associated with an account!</h4>";
  }
  else if($checkFields > 0)
  {
   echo "<h4 class='errormessage'>Fields Cannot be Empty!</h4>";
  }
  else
  {
   echo "<p class='logintext'>Fill in the below info to submit for account creation</p>";
  }

?>

<div class="namecontainer">

<input type="text" class="textfield1" name="fname" placeholder="First Name">
<input type="text" class="textfield1" name="lname" placeholder="Last Name">
</div>
<input type="text" class="textfield1" name="email" placeholder="Enter Email">


<div class="passwordcontainer">
<input type="password" class="textfield1" name="password" placeholder="Password">

<input type="password" class="textfield2" name="retype" placeholder="Re-Enter Password">

<p class="forgotpassword"></p>
</div>
<input type="submit" class="submitbutton" name="submit" value="Create Account">

</form>

<p class="returnbtn"><a href="login.php">Click here to return to the login page</a></p>

</div>

</body>

</html>
