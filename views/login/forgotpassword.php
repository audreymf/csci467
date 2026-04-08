<?php

require 'server_connect.php';
require 'php_functions.php';

session_start();

?>

<html>

<head>
<link rel="stylesheet" href="login.css">
<title>CSCI 467 Forgot Password Page</title>
</head>

<body>


<div class="login_window">

<!-- <img class="login_image" src="../personal/images/Generic_Logo.png"> -->

<form action="" method="POST">

<h1 class="logintext">Forgot Password?</h1>
<!--<p class="logintext">Enter your email below and if an account exists \n we will send you a password reset link</p>-->

<?php
if($_SERVER['REQUEST_METHOD'] === "POST")
{
  if(empty($_POST['email']))
  {
	echo "<p class='errormessage'>Enter an email dummy</p>";
  }
  else
  {
	  echo "<p class='successmessage'>Password reset link has been requested!</p>";
  }
}
else
{
  echo "<p class='logintext'>Enter your email below and if an account exists <br> we will send you a password reset link</p>";
}
?>

<input type="text" class="textfield1" name="email" placeholder="Enter Email">

<input type="submit" class="submitbutton" name="submit" value="Request Password Reset">

</form>

<p class="noaccount"><a href="login.php">Click here to return to the Login Page</a></p>

</div>

</body>

</html>
