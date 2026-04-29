<?php

require './functions/server_connect.php';
require './functions/php_functions.php';

validateLogin();
checkAccessLevel('sales');


if(isset($_POST['logout']))
{
  endSession();
}
else if(isset($_POST['submitnewquote']))
{
	$quoteID = submitNewQuote($pdo, $_SESSION['id'], $_POST['customer'], $_POST['email'], $_POST['status']);
	insertLineItems($pdo, $quoteID, $_POST['services'], $_POST['prices']);
	insertNotes($pdo, $quoteID, $_POST['notes'], $_POST['issecret']);	
	
}
else if(isset($_POST['editquote']))
{
   $currentlyEditing = $_POST['editquote'];
   openModal();  
}
else if(isset($_POST['deleteService']))
{
   $currentlyEditing = $_POST['editID'];
   deleteLI($pdo, $_POST['editID'], $_POST['deleteService']);
   openModal();
}
else if(isset($_POST['searchQuote']))
{
   $checkExists = checkExists($pdo, $_SESSION['id'], $_POST['searchQuoteID']);

   if($checkExists > 0)
   {
     $currentlyEditing = $_POST['searchQuoteID'];
     openModal();
   }
}
else if(isset($_POST['updateQuote']))
{
	insertLineItems($pdo, $_POST['editID'], $_POST['services'], $_POST['prices']);
	updateQuote($pdo, $_POST['customer'], $_POST['editID'], $_POST['email'], $_POST['status']);
        insertNotes($pdo, $_POST['editID'], $_POST['notes'], $_POST['issecret']);	
}
else if(isset($_POST['deleteQuote']))
{
	deleteQuote($pdo, $_POST['editID']);
}



?>

<html>

<!-- BEGIN HEAD --> 
<head>
<!-- Fontawesome is the library used for the add quote icon, delete line item icon, and delete note icon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- Bootstrap is being used for the Modals -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<script src="./js/salesassociate.js" defer></script>
<link rel="stylesheet" href="./css/salesassociate.css">
<title>CSCI 467 Salesperson Interface</title>

</head>

<!-- BEGIN BODY -->
<body>


<!-- Begin Navbar -->
<div class="navbar">

<h3>Plant Repair Services - Salesperson Interface</h3>

<form action="" method="POST">

<input class="logout" type="submit" name="logout" value="Logout">

</form>

</div>
<!-- End Navbar -->



<!-- BEGIN SALESPERSONCONTENT. This is the main content of the page -->
<div class="salespersoncontent">


<div class="welcomecontent">

<?php

echo "<h1> Welcome, " . $_SESSION['name'] . "</h1>";

?>

<form action="" method="POST">

<input type="text" name="searchQuoteID" placeholder="Enter Quote Number & Press Enter">
<input type="submit" name="searchQuote" hidden>

</form>
</div> <!-- End Welcomecontent -->













<!-- Begin Quote Area. This will show all of the current salespersons open quotes -->
<div class="quotearea">

<div class="header">
  
<?php
  echo "<span>" . $_SESSION['name'] . "'s Open Quotes </span>";
?>

<button type="button" data-bs-toggle="modal" data-bs-target="#createQuote"><i class="fa-solid fa-file-circle-plus"></i></button>

</div> <!-- End Header -->

<div class="scrollablequotes">

<form action="" method="POST">

<?php

$allQuoteData = retrieveQuoteInfo($pdo, $_SESSION['id']);

foreach($allQuoteData as $quote)
{

	$LIInfo = retrieveLIInfo($pdo, $quote['id']);
	$items = "";

	foreach($LIInfo as $LI)
	{
	  $items .= $LI['item'] . ", ";
	}

	$items = substr_replace($items, "", -2);

	echo "<button type='submit' name='editquote' class='custombutton' value='" . $quote['id'] . "'> <strong> Quote ID: " . $quote['id'] . "</strong>\n" .
		"<pre>Quote Status: " . $quote['status'] . " | " .
		"Customer Contact: " . $quote['email'] . " | " .
		"Order Details: " . $items . "</pre></button>"; 
	  
}

?>

</form>

<!-- End Open Quotes Stuff -->
</div> <!-- End scrollablequotes -->


</div> <!-- End quotearea -->



<!-- Modals are down here. Modals are created using bootstrap -->


<!-- Create Quote Modal -->
<div class="modal fade" id="createQuote" aria-hidden="true">
<div class="modal-dialog modal-xl modal-dialog-centered">
<div class="modal-content">

<!-- Modal Header that contains the title in the top left and the X to close the modal-->
<div class="modal-header">
<h3 class="modal-title">Create a New Quote</h3>
<button type="button" class="btn-close" data-bs-dismiss="modal"</button>
</div>

<!-- Modal Body which will house the actual form that gets submitted -->
<div class="modal-body">

<div class="newquoteform"><!-- begin newquoteform -->

<!-- Begin Form -->
<form action="" method="POST">

<h4>Customer Information:</h4>
<!-- Create a select dropdown that will contain all the customers from the legacy database -->
<select name="customer" id="customerSelect" class="form-select-lg" style="margin-right: 20px;" required>
<option value="" disabled selected>Please Make a Selection</option>

<?php

// Retrieve all the customer information from the legacy database
// legacypdo is created in the server_connect.php file
$customerInfo = retrieveCustomerInfo($legacypdo);

foreach($customerInfo as $customer)
{
  //Loop through all the retrieved customer information. The customer ID is the value of each option which will be put into the quotes database
  echo "<option value='" . $customer['id'] . "'>" . $customer['name'] . "</option>";

}

?>

</select><!-- end customer selection -->

<!-- An email input fields is used to record the customers contact information -->
<input type="email" class="email" name="email" placeholder="Enter Customer Email" required>
<br><br>

<h4 style="display: inline-block;">Service(s):</h4> 
<!-- The salesassociate.js file has a onclicklistener for this button to add more services -->
<button type="button" id="addService" class="btn"><i class="fa fa-plus-square" aria-hidden="true" style="margin-left: auto;"></i></button>

<br>

<div class="services" id="services"><!-- start services-->

<!-- At least 1 service is required. Services are stored in the services[] array, while prices are store in the prices[] array. These get processed by the insertLineItems PHP function -->
<div class="serviceRow"> <!-- start serviceRow div -->
<input type="text" name="services[]" placeholder="Enter Service Description" required>
<input type="number" step="0.01" name="prices[]" placeholder="Enter Service Price" required>
</div> <!-- end serviceRow div -->

<!-- end product selection -->

</div> <!-- end services div -->

<br>

<h4 style="display: inline-block;">Note(s):</h4>
<button type="button" id="addNoteCreate" class="btn"><i class="fa fa-plus-square" aria-hidden="true" style="margin-left: auto;"></i></button>
<br>
<div class="newNote">
<textarea type="text" name="notes[]" rows="4" cols="50" placeholder="Enter a Note"></textarea>
<select name="issecret[]" class="form-select-sm">
<option value='1'>Private</option>
<option value='0'>Public</option>
</select>
</div>
<div class="notesContainerCreate" id="notesContainerCreate"></div>

<br>


<h4>Quote Status:</h4>

<select name="status" class="form-select-lg">
<option value="draft">Draft</option>
<option value="finalized">Finalized</option>
</select>

</div><!-- end newquoteform -->

</div>

<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<input type="submit" name="submitnewquote" value="Submit Quote" class="btn btn-primary">

</form><!-- End form here so submit button is in footer -->

</div>

</div>
</div>
</div>
<!-- End Create Quote Modal -->












<!-- Existing Quote Modal -->

<div class="modal fade" id="editQuote" data-bs-backdrop="static">
<div class="modal-dialog modal-xl modal-dialog-centered">
<div class="modal-content">

<div class="modal-header">
<h3 class="modal-title">Editing Quote 
<?php
// Check if editquote or deleteService have been submitted. If not then no content is loaded into the modal and the page continues to load. This fixed an earlier bug where only half the page would load if you didnt 
// have editquote or deleteService submitted
if(isset($_POST['editquote']) || isset($_POST['deleteService']) || $checkExists > 0)
{
	echo $currentlyEditing;
}
?>
</h3>
</div>

<!-- Start the Edit Quote modal body which contains the form and loaded information -->
<div class="modal-body">


<h4>Customer Information:</h4>

<form action="" method="POST">

<?php 

// This hidden input field is needed to submit the quoteID that you are currently editing. This should not be changed by the user hence the utilization of a hidden field
echo "<input type='hidden' name='editID' value='$currentlyEditing'>";

?>

<!-- A select field is utilized for the customers just like the create quote modal -->
<select name="customer" id="customerSelect" class="form-select-lg" style="margin-right: 20px;" required>

<?php

if(isset($_POST['editquote']) || isset($_POST['deleteService']) || $checkExists > 0)
{

//Retrieve the informaiton about the current quote and all the customers from the legacy database
$quoteInfo = retrieveSingleQuoteInfo($pdo, $currentlyEditing);

$customerInfo = retrieveCustomerInfo($legacypdo);


// Loop through all the customers in the database. When the current customer matches with the customer from the legacy database, that customer will be set as the selected customer.
foreach($customerInfo as $customer)
{	

	// an if else statement is used to determine if the customer being looped through matches the customer in the quote
	if($quoteInfo['customerID'] == $customer['id'])
	{
	  $selected = "selected";
	}
	else
	{
	  $selected = "";
	}

	echo "<option value='" . $customer['id'] . "'  $selected>" . $customer['name'] . "</option>";

}

echo "</select>";


// a text field is used for the email and the current email from the quote is filled in
echo "<input class='email' type='email' name='email' value='{$quoteInfo['email']}' required>";

}

?>

<br><br>

<h4 style="display: inline-block;">Service(s):</h4>
<button type="button" id="addServiceEdit" class="btn"><i class="fa fa-plus-square" aria-hidden="true" style="margin-left: auto;"></i></button>

<br>

<div id="editServices"> <!-- start services div -->

<?php

if(isset($_POST['editquote']) || isset($_POST['deleteService']) || $checkExists > 0)
{

	$LIData = retrieveLIInfo($pdo, $quoteInfo['id']);

	foreach($LIData as $LI)
	{
	  echo "<div class='serviceRow'>";
	  echo "<input class='disabled' type='text' value='{$LI['item']}' disabled>";
	  echo "<input class='disabled' type='text' value='\${$LI['price']}' disabled>";
	  echo "<button type='submit' class='deleteServiceBtn' name='deleteService' value='{$LI['item']}'><i class='fa-solid fa-delete-left'></i></button>";
	  echo "</div>";


	}
	
}

?>

</div> <!-- end services div -->


<br>

<h4 style="display: inline-block;">Note(s):</h4>
<button type="button" id="addNoteEdit" class="btn"><i class="fa fa-plus-square" aria-hidden="true" style="margin-left: auto;"></i></button>


<div class="notesContainerEdit" id="notesContainerEdit"></div>

<?php

if(isset($_POST['editquote']) || isset($_POST['deleteService']) || $checkExists > 0)
{
  $notesData = retrieveNotes($pdo, $quoteInfo['id']);

  foreach($notesData as $note)
  {
    echo "<div class='newNote'>";
    echo "<textarea type='text' name='note' rows='2' cols='50' disabled>{$note['content']}</textarea>";
    echo "</div>";

  }

}
?>

<br>


<h4>Quote Status:</h4>

<select name="status" id="status" class="form-select-sm">
<?php

if(isset($_POST['editquote']) || isset($_POST['deleteService']) || $checkExists > 0)
{
  $statustypes = ['draft', 'finalized'];

  for($i = 0; $i < 2; $i++)
  {
     if($statustypes[$i] == $quoteInfo['status'])
     {
       $selected = "selected";
     }
     else
     {
       $selected = "";
     }

     echo "<option value='" . $statustypes[$i] . "'  $selected>" . $statustypes[$i] . "</option>";

  }
}

?>
</select>


</div><!-- end modal content -->

<div class="modal-footer">
<button type="submit" name='deleteQuote' class="btn btn-danger" data-bs-dismiss="modal">Delete Quote</button>
<input type="submit" name="updateQuote" value="Save Changes" class="btn btn-primary">

</form><!-- end edit form -->

</div>

</div>
</div>
</div>











<!-- End Existing Quote Modal -->


<!-- End modals -->











<!-- Begin triblock area. This will be used to show various information about cx, approved quotes, etc. -->
<div class="triblock">

<!-- Start Block 1 -->
<div class="block">

<div class="header">

<span>Completed/Ordered Quotes</span>

</div> <!-- End Header -->

<?php

$count = checkOrdered($pdo, $_SESSION['id']);

if($count > 0)
{
   echo "<div class='scrollablequotes'>";

   $finalQuotes = retrieveOrderedInfo($pdo, $_SESSION['id']);

   foreach($finalQuotes as $final)
   {
     echo "<button type='button' class='custombutton'><strong>Quote ID: " . $final['id'] . "</strong><br>Commission - <span class='commission'> $" . $final['commission'] . "</span></button>";
   }

  echo "</div>"; 

}
else
{
   echo "<div class='nofinalizedfound'>";
   echo "<strong>No Sanctioned Quotes Found for User, " . $_SESSION['name'] . "</strong>";
   echo "</div>";
}



?>




</div> <!-- End Block 1 -->


<!-- Start Block 2 -->
<div class="block" style="margin-left: 1%; margin-right: 1%;">

<div class="header">

<span>Finalized & Sanctioned Quotes</span>

</div> <!-- End Header -->


<?php

$count = checkFinalized($pdo, $_SESSION['id']);

if($count > 0)
{
   echo "<div class='scrollablequotes'>";

   $finalQuotes = retrieveFinalizedInfo($pdo, $_SESSION['id']);

   foreach($finalQuotes as $final)
   {
     echo "<button type='button' class='custombutton'><strong>Quote ID: " . $final['id'] . "</strong><br>Current Status: <span> " . $final['status'] . "</span></button>";
   }

  echo "</div>"; 

}
else
{
   echo "<div class='nofinalizedfound'>";
   echo "<strong>No Finalized or Sanctioned Quotes Found for User, " . $_SESSION['name'] . "</strong>";
   echo "</div>";
}


?>





</div> <!-- End Block 2 -->



<!-- Start Block 3 -->
<div class="block">

<div class="header">

<span>Customer Database</span>

</div> <!-- End Header --> 


<div class="scrollablequotes">


<?php

$customerInfo = retrieveCustomerInfo($legacypdo);

foreach($customerInfo as $row)
{

	echo "<button type='button' class='custombutton'><strong>" . $row['name'] . "</strong><br><span>" . $row['street'] . ", " . $row['city'] . " | " . $row['contact'] . "</span></button>";

}


?>



</div> <!-- End scrollablequotes -->


</div> <!-- End Block 3 -->




<!-- End triblock -->
</div>




</div> <!-- End salespersoncontent -->















<div class="footer">


</div> <!-- End Footer -->

</body>



</html>




