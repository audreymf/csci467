<?php
require './functions/server_connect.php';
require_once './functions/php_functions.php';
session_start();

if($_SERVER['REQUEST_METHOD'] === "POST")
{
	$submitted_email = $_POST['email'];
	$submitted_password = $_POST['password'];

	$count = checkEmail($pdo, $submitted_email);

	if($count > 0)
	{
		$validpassword = checkLogin($pdo, $submitted_email, $submitted_password);
		if($validpassword == 1)
		{
			$status = 0; 
		        if($status == 0)
			{
				$data = retrieveInfo($pdo, $submitted_email, $submitted_password);
				
				$_SESSION['name'] = $data['name'];
				$_SESSION['id'] = $data['id'];
				$_SESSION['access_level'] = $data['access'];
				$_SESSION['login_authenticated'] = TRUE;

				redirectClient($_SESSION['access_level']);
			}	
		}	
	}
}

?>

<html>

<head>
<link rel="stylesheet" href="./css/login.css">
<title>CSCI 467 Login Page</title>
</head>

<body>


<div class="login_window">

<!-- <img class="login_image" src="../personal/images/Generic_Logo.png"> -->

<form action="" method="POST">

<h1 class="logintext">Account Login</h1>
<p class="logintext">Please enter your username and password below to login</p>

<?php
if($_SERVER['REQUEST_METHOD'] === "POST")
{
  if($count > 0 && $validpassword > 0 && $status == 0)
  {
   echo "<h4 class='errormessage'>Account has not yet been approved. Try again later</h4>";
  }
  else if($count < 1 || $validpassword == 0)
  {
   echo "<h4 class='errormessage'>Invalid Username/Password</h4>";
  }
}
?>

<input type="text" class="textfield1" name="email" placeholder="Enter Username">

<input type="password" class="textfield2" name="password" placeholder="Password">

<p class="forgotpassword"><a href="forgotpassword.php">Forgot Password?</a></p>

<input type="submit" class="submitbutton" name="submit" value="Login">

</form>

<!-- Removed account creation due to that being delegated to the administrator -->
<!-- <p class="noaccount">Dont have an account? <a href="createaccount.php">Create one here</a></p> -->
<p class="noaccount">Dont have an account? <a href="mailto:admin@groupb.com">Contact Your System Administrator</a></p>
</div>

</body>

</html>

