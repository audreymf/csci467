<?php

require "db_connect.php";
echo '<link rel="stylesheet" href="styles.css">';

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

//makes a pop up to enter new info
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
echo "<tr>";

#edit password section
echo "<tr>";
echo "<td class=modallabel>Password:</td>";
echo "<td><input class=textbox type='text' name='password' value='$row[3]' required></td>";
echo "<tr>";

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
function add($pdoq, $new_name, $new_password, $new_userid){
	$sql = "INSERT INTO sales_associates(name, userID, password, access)  values(?, ?, ?, 'sales')";
	$stmt = $pdoq->prepare($sql);
	$stmt->execute([$new_name, $new_password, $new_userid]);
					                  }
?>

