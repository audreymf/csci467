<?php

require "db_connect.php";
echo '<link rel="stylesheet" href="Admin.css">';

/*This functions removes the select record from the database
 *It takes 2 arguments, a pdoq object and the id of the record to delete
 */
function remove($pdoq, $id){
	$sql = "DELETE FROM quotes WHERE associateID = ?";
   	$statement = $pdoq->prepare($sql);
   	$statement->execute([$id]);

        $sql = "DELETE FROM sales_associates WHERE id = ?";
        $statement = $pdoq->prepare($sql);
        $statement->execute([$id]);
			 			  }

/*
 *This functions edits the selected sales associate and updated the value in the database
 *It takes 2 arguments, a pdoq object and the id of the record to delete
 */

function edit($pdoq, $id){

	#if button is clicked
	if(isset($_GET['leave'])){

	#if cancel was chosen
        if($_GET['leave'] == 'Cancel'){
            return;
				      				  }

	#if submit was chosen
	if($_GET['leave'] == 'Submit'){
            $sql = "UPDATE sales_associates SET name=?, address=?, userID=?, password=?, commission=? WHERE id=?";
            $statement = $pdoq->prepare($sql);
            $statement->execute([$_GET['name'], $_GET['address'], $_GET['UserID'], $_GET['password'], $_GET['commission'], $_GET['id']]);
            return;
       				      }
   				 }
	#gets information of selected Sales Associate
	$sql = "SELECT name, address, userID, password, commission
	       	FROM sales_associates
		WHERE id = ?";
	$statement = $pdoq->prepare($sql);
        $statement->execute([$id]);
	$row = $statement->fetch();

#makes a pop up to enter new info
	echo "<dialog open class=modalstyle>";
	echo "<h1 class=formheader>Editing Associate</h1>";

#edit form info
	echo "<form method=GET>";
	echo "<input type='hidden' name='id' value='$id'>";

	echo "<table>";

#edit name section
	echo "<tr>";
	echo "<td class=modallabel>Name:</td>";
	echo "<td><input class=textbox type='text' name='name' value='$row[0]' required></td>";
	echo "</tr>";

#edit address section
	echo "<tr>";
	echo "<td class=modallabel>Address:</td>";
	echo "<td><input class=textbox type='text' name='address' value='$row[1]' required></td>";
	echo "</tr>";

#edit UserID section
	echo "<tr>";
	echo "<td class=modallabel>UserID:</td>";
	echo "<td><input class=textbox type='text' name='UserID' value='$row[2]' required></td>";
	echo "</tr>";

#edit password section
	echo "<tr>";
	echo "<td class=modallabel>Password:</td>";
	echo "<td><input class=textbox type='text' name='password' value='$row[3]' required></td>";
	echo "</tr>";
	
#edit commission section
	echo "<tr>";
	echo "<td class=modallabel>Commission:</td>";
	echo "<td><input class=textbox type='number' name='commission' value='$row[4]' step=0.01 required></td>";
	echo "</tr>";

	echo "</table>";

#submit or clear section
	echo "<input class=clear type='submit' name='leave' value='Cancel'/>";
	echo "<input class=submit type='submit' name='leave' value='Submit'/>";
	echo "</form>";
	echo "</dialog>";
                        }

/*
 * This function adds a new sales associates to the database
 * It takes 4 arguments, a pdoq objected, name, password, and UserId
 */
function add($pdoq, $new_name, $new_password, $new_userid, $position){
	$sql = "INSERT INTO sales_associates(name, userID, password, access)  values(?, ?, ?, ?)";
	$stmt = $pdoq->prepare($sql);
	$stmt->execute([$new_name, $new_password, $new_userid, $position]);
					                  	     						}

/*
 * This functions sorts and creates the table of quotes
 * It takes 7 arguments 2 database objects and 4 filters
 */
function table($sdate, $edate, $sa, $customer, $status, $pdoc, $pdoq){
	if($edate < $sdate){
		echo "<p class=error>***Ending date must be after starting date***</p>";
		die();
			  		  }
#gets customer info
	$sql = "SELECT id FROM customers WHERE name = ?";
	$stmt = $pdoc->prepare($sql);
	$stmt->execute([$customer]);
	$cid = $stmt->Fetch();


#Starting sql for sorted table
	$sql = "SELECT DATE(date), customerID,  sales_associates.name, COALESCE(quotes.commission,0), status, quotes.id
		FROM quotes 
		JOIN sales_associates ON associateID  = sales_associates.id
		WHERE date BETWEEN ? AND ?";	
	$params = [];
	$params[] = $sdate;
	$params[] = $edate;

#if sales associate was chosen
	if($sa != 'All'){
		$sql .= " AND sales_associates.name = ?";
		$params[] = $sa;
					}

#if customer was chosen
	if($customer != 'All'){
		$sql .= " AND customerID = ?";
		$params[] = $cid[0];
			 		     }

#if status was chosen
	if($status != 'All'){
		$sql .= " And status = ?";
		$params[] = $status;
			   			 }

	$stmt = $pdoq->prepare($sql);
	$stmt->execute($params);
	$rows = $stmt->fetchAll();


#if no rows selected
if(count($rows) == 0){
	echo "<p class='error'>***No quotes available***</p>";
		     		}	

else{
#Show table

	echo "<div class='sa-table'>";
	echo "<table class='table-data'>";


#Headers for sales associate table
	echo "<tr>";
	echo "<th class=table-headers>Date</th>";
	echo "<th class=table-headers>Customer</th>";
	echo "<th class=table-headers>Sales Associate</th>";
	echo "<th class=table-headers>Commission</th>";
	echo "<th class=table-headers style='padding-left: 50px;'>Status</th>";
	echo "<th class=table-headers></th>";
	echo "<tr>";

	foreach($rows as $row){
//get customer info from customers db
  		$sql = "SELECT name, city, street, contact FROM customers WHERE id = ?";
    		$stmt = $pdoc->prepare($sql);
   		$stmt->execute([$row[1]]);
    		$crow = $stmt->fetch();
    		$cname = $crow[0];
    		$ccity = $crow[1];
    		$cstreet = $crow[2];
    		$ccontact = $crow[3];

    	echo "<tr>";
    	echo "<td class='id_name'>$row[0]</td>";
    	echo "<td class='id_name'>$cname</td>"; 
   		echo "<td class='id_name'>$row[2]</td>";
   		echo "<td class='commission'>$$row[3]</td>"; 
   		echo "<td style='padding-left: 50px;'>$row[4]</td>";

#get line items associates with the quote
		$sql = "SELECT item, price FROM line_items WHERE quoteID = ?";
		$stmt = $pdoq->prepare($sql);
		$stmt->execute([$row[5]]);
		$items = $stmt->fetchAll();


#stores all items
		$itemList = [];
		foreach($items as $item){
   			 $itemList[] = ["item" => $item[0], "price" => $item[1]];
								}
		$itemJson = json_encode($itemList);

#get notes
		$sql = "SELECT content FROM notes WHERE quoteID = ?";
		$stmt = $pdoq->prepare($sql);
		$stmt->execute([$row[5]]);
		$notes = $stmt->fetchAll();

#stores all notes
		$notesList = [];
		foreach($notes as $note){
    			$notesList[] = $note[0];
								}

#Converts both to Json String
		$itemJson = json_encode($itemList);
		$notesJson = json_encode($notesList);


#Button For viewing quote
		echo "<td>";
		echo "<button onclick='openModal(\"{$row[0]}\", \"{$cname}\", \"{$ccity}\", \"{$cstreet}\", \"{$ccontact}\", \"{$row[3]}\", {$itemJson}, {$notesJson})' class='view'>View</button>";
		echo "</td>";
		echo "</tr>";
		     }
		echo "</table>";
		echo "</div>";							    }
			}

?>

<!-- 
Functions To Display the details of the quote-->
<div id="myModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center;">
<div style="background:#fff; padding:30px; border-radius:8px; min-width:400px;">
<h1>Viewing Quote Details</h1>
<div id="modalContent"></div>
<button onclick="closeModal()" style="float:right;" >Close</button>
</div>
</div>


<script>
/*This functions displays a quotes details
  It takes 9 arguments which are all information regarding the quote
*/

function openModal(date, cname, ccity, cstreet, ccontact, commission, lineitems, notes){

	let itemRows = "";

//if not line items
	if(lineitems.length == 0){
   		 itemRows = "<p>None</p>";
				 			}
	else{
	let count = 1;

    		lineitems.forEach(i => {
		itemRows += `<tr><td>${count}. ${i.item}</td><td style="text-align:right;">$${i.price}</td></tr>`;
		count++
								   });
            }

	let noteRows = "";

//if no notes
	if(notes.length == 0){
   		 noteRows = "<p>None</p>";
			   			  }
	else{
		let count = 1;

//adds next line item
    		notes.forEach(i => {
			noteRows += `<tr><td>${count}. ${i}</td></tr>`;
			count++
			   	  				 });
            }
	document.getElementById('modalContent').innerHTML = `

		<h3 class='labelc'>Customer Information</h3>
		<p>Name: ${cname}</p>
		<p>Address: ${cstreet},&nbsp;${ccity}</p>
		<p>Contact: ${ccontact}</p>
		<p>Fullfilled on: ${date}</p>
		<p>Commission: $${commission}</p>

		<h3 class='labelc'>Line Items</h3>
		<table style="width:100%">${itemRows}</table>

		<h3 class='labelc'>Notes</h3>
		<table style="width:100%">${noteRows}</table>

							   `;
   	 document.getElementById('myModal').style.display = 'flex';
  											}
/*This functions closes the modal
  Takes no arguments
 */

  function closeModal() {
    	document.getElementById('myModal').style.display = 'none';
  				     	}
</script>


