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
	add($pdoq, $_GET['newname'], $_GET['newpassword'], $_GET['newuserid']);
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
echo "<tr>";

#Input for new password
echo "<tr>";
echo "<td class=newsalabel>New Password:</td>";
echo "<td><input class=textbox type='text' name='newpassword' required></td>";
echo "<tr>";
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
	echo "<h1 class='header' style='margin-top: 60px; padding-left: 40px;'>Company Quotes</h1>";

#
	$sql = "SELECT  DATE(date), customerID,  sales_associates.name, quotes.commission, status
 		FROM quotes 
		JOIN sales_associates ON associateID = sales_associates.id;";
	$stmt = $pdoq->query($sql);
	$rows = $stmt->fetchAll();

	echo "<div class=sa-table>";
	echo "<table class=table-data>";

	foreach($rows as $row){
		echo "<tr>";
		echo "<td class=id_name>$row[0]</td>";
		echo "<td class=id_name>$row[1]</td>";
		echo "<td class=id_name>$row[2]</td>";
		echo "<td class=id_name>$row[3]</td>";
		echo "<td class=id_name>$row[4]</td>";
		echo "</tr>";
			      }
	echo "</table>";
	echo "</div>";	
    }
?>
</body>
</html>
