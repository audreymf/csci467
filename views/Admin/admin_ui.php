<html><head><title>Administration UI</title><link rel="stylesheet" href="Admin.css"></head>
<body id= body1>
<?php

#connects to database
require "db_connect.php";
require_once "Functions.php";

#Displays the title
echo "<div class='bar'>";
echo "Plant Repair Services: Administration";

#Button to switch between SA and Quotes
echo"<form method='get'>";

#if currently looking at Sales Associate
if(!isset($_GET['view']) || $_GET['view'] == 'SA'){
	echo "<button type='submit' name='view' value='Quotes' class=switch>Show Sales Quotes</button>";
						  }

#if currently looking at Sales Quotes
else{
	echo "<button type='submit' name='view' value='SA' class=switch>Show Sales Associates</button>";
    }
echo "</form>";
echo "</div>";


#Section to make changes to Sales associates

#if delete sales associate is chosen
if(isset($_GET['delete'])){
	remove($pdoq,$_GET['delete']);
			  }	

#if edit sales associate is chosen
if(isset($_GET['edit']) || isset($_GET['leave'])){
	edit($pdoq, $_GET['edit']);
						 }

#if add sales associate is chosen
if(isset($_GET['new_associate'])){
	add($pdoq, $_GET['newname'], $_GET['newpassword'], $_GET['newuserid'], $_GET['position']);
			         }



#Display sales associate info
if(!isset($_GET['view']) || $_GET['view'] == 'SA'){
	echo "<div class=header-box>";
		echo "<div>";
		echo "<h1 class='header'>Company Sales Associates</h1>";


#sql to get all current sales associates
$sql = "Select id, name, commission FROM sales_associates;";
$results = $pdoq->query($sql);
$rows = $results->fetchAll();

#Creates table for sales associates
echo "<div class='sa-table'>";
echo "<table class=table-data>";

#Headers for sales associate table
echo "<tr>";
echo "<th class=table-headers>Id</th>";
echo "<th class=table-headers>Name</th>";
echo "<th class=table-headers>Commission</th>";
echo "<th class=table-headers></th>";
echo "<th class=table-headers></th>";
echo "<tr>";

#Stores data in tables 
foreach($rows as $row){
	echo "<tr>";
	echo "<td class=id_name>$row[0]</td>"; 
	echo "<td class=id_name>$row[1]</td>";
	echo "<td class=commission> $$row[2]</td>";


#Button for Editing sales associate
	echo "<td>";
	echo "<form method=get class=edit>";
	echo "<input type='hidden' name='view' value='SA'>";
	echo "<button type='submit' name='edit' value=$row[0] class=buttonColor>Edit</button>";
	echo "</form>";
	echo "</td>";

#Button for deleting sales associate
	echo "<td>";
	echo "<form method=get class=delete>";
	echo "<input type='hidden' name='view' value='SA'>";
	echo "<button type='submit' name='delete' value=$row[0] class=buttonColor>Delete</button>";
	echo "</form>";
	echo "</td>";
	echo "</tr>";
		      }
echo "</table>";
echo "</div>";
echo "</div>";


#Sections to add new Sales Associate
echo "<div>";
echo "<h1 class=header>New Sales Associate</h1>";
echo "<form method=get>";
echo "<table>";

#Input for new name
echo "<tr>";
echo "<td class=newsalabel>Name:</td>";
echo "<td><input class=textbox type='text' name='newname' required></td>";
echo "</tr>";

#Input for new UserID
echo "<tr>";
echo "<td class=newsalabel>New UserID:</td>";
echo "<td><input class=textbox type='text' name='newuserid' required></td>";
echo "</tr>";

#Input for new password
echo "<tr>";
echo "<td class=newsalabel>New Password:</td>";
echo "<td><input class=textbox type='text' name='newpassword' required></td>";
echo "</tr>";

#Input for new Position
echo "<tr>";
echo "<td class=newsalabel>Position:</td>";
echo "<td class=textbox>";
echo "<select name='position'>";
echo "<option value='sales'>Sales</option>";
echo "<option value='hq'>HQ</option>";
echo "<option value='admin'>Admin</option>";
echo "</select>";
echo "</td>";
echo "</tr>";

echo "</table>";

#Submit and Clear button for adding new sales associate
echo "<input class=newsaclear type='button' value='Clear' onclick='this.form.reset()'/>";
echo "<input class=submit type='submit' name='new_associate' value='Submit'/>";

echo "</form>";
echo "</div>";
echo "</div>";
					  }

#section for Quotes
else{
	echo "<br>";
	echo "<form method=get>";

#Form for date
	echo "Start date: ";
	if(isset($_GET['startdate'])){
    		echo "<input type='date' name='startdate' value='" . $_GET['startdate'] . "' />";
				     } 
	else{
    		echo "<input type='date' name='startdate' value='" . date('Y-m-d') . "' />";
	    }

	echo " to: "; 

	if(isset($_GET['enddate'])){
 		echo "<input type='date' name='enddate' value='" . $_GET['enddate'] . "' />";
				   } 
	else {
    echo "<input type='date' name='enddate' value='" . date('Y-m-d') . "' />";
	     }

	echo "<br>";
	echo "<br>";

#Form for SA
	echo "Sales associate: ";
	echo "<select name=SalesAssociate>";

	$sql = "SELECT DISTINCT name
		FROM quotes
		JOIN sales_associates ON sales_associates.id = quotes.associateID;";

	$stmt = $pdoq->query($sql);
	$rows = $stmt->fetchAll();

#if All should be selected- Sales Associate
	if($_GET['SalesAssociate'] == 'All'){
   		$selectedAll = "selected";
					    }
       	else {
        	$selectedAll = "";
  	     }	

	echo "<option value='All' $selectedAll>All</option>";

#if Sa should be selected- Sales Associates

	foreach($rows as $row){

		if($_GET['SalesAssociate'] == $row[0]){
  		  $saselected = "selected";
						      } 
		else {
    		  $saselected = "";
		     }
		echo "<option value='$row[0]' $saselected>$row[0]</option>";
			      }
	echo "</select>";

#Form for Customer
	echo "  Customer: ";
	echo "<select name=Customer>";

#if All should be selected -Customer
if(!isset($_GET['Customer']) || $_GET['Customer'] == 'All'){
	$selectedAll = "selected";
							   }
else {
    $selectedAll = "";
     }
echo "<option value='All' $selectedAll>All</option>";


#Gets customer id that are in quotes
	$sql = "SELECT DISTINCT customerID FROM quotes";
	$stmt = $pdoq->query($sql);
	$ids = $stmt->fetchAll();

#uses id to get names from customer db
foreach($ids as $id){
	$sql = "SELECT name 
		FROM customers
		WHERE id = ?";

	$stmt = $pdoc->prepare($sql);
	$stmt->execute([$id[0]]);
	$row = $stmt->fetch();

#if Customer should be selected - Customer
	if($_GET['Customer'] == $row[0]){
        $selectedAll = "selected";
					}
       	else {
        $selectedAll = "";
   	     }

        echo "<option value='$row[0]' $selectedAll>$row[0]</option>";
		    }
	echo "</select>";

#Form for Status
	echo " Status: ";
	echo "<select name=Status>";

#If all should be selected -Status
	if($_GET['Status'] == 'All'){
		$AllStatus = "selected";
				    }
	else{
		$AllStatus = "";
	    }
	echo "<option value=All $AllStatus>All</option>";

#A status should be selected   -Status
	
	$statuses = ['Finalized', 'Sanctioned', 'Ordered'];

	foreach($statuses as $status){
        	if($_GET['Status'] == $status){
        		$statusselected = "selected";
					      } 
		else {
        		$statusselected = "";
  		     }
		echo "<option value='$status' $statusselected>$status</option>";
				     }
	echo "</select>";


#Submit Form
	echo "<br>";	
	echo "<input type=hidden value=Quotes name=view>";
	echo "<input type=submit value=Sort name=sort>";
	

	echo "</form>";

	echo "<h1 class='header' style='margin-top: 30px; padding-left: 40px; '>Company Quotes</h1>";

#Show table
	table($_GET['startdate'],$_GET['enddate'],$_GET['SalesAssociate'],$_GET['Customer'], $_GET['Status'], $pdoc, $pdoq);

    }
?>
</body>
</html>
