<?php
//Turn on error reporting
ini_set('display_errors', 'On');
include 'storedInfo.php';

//connect to database
$mysqli = new mysqli("oniddb.cws.oregonstate.edu", "abadjier-db", $myPassword, "abadjier-db");

//check to make sure it's connected
if ($mysqli->connect_errno) {
	echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
//} else {
	//echo "Connection OK";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<title>Video Inventory</title>
	<style type="text/css">		
		th, td { border: 2px solid; padding: 10px; }
		fieldset { width: 400px; height: 175px; border-radius: 10px; }
		#delete {width: 100px;}
		.title { border: 1px solid; padding-top: 5px; float: right; width: 300px; }		
		.warning { color: red; }		
	</style>
</head>
<body>
	<table>
	<tr><td>
			<form action="videoInventory.php" method="post">
				<fieldset id="addForm">
					<legend><h3>Add a Video</h3> </legend>
					<p>						   
						<label>Name <input class="title" type="text" name="videoName"></label>
					</p>
					<p>
						<label >Category <input class="title" type="text" name="filmCategory"></label>
					</p>
					<p>
						<label >Length <input class="title" type="text" name="filmLength"></label>	
					</p>					
					<p><input class="title" type="submit" value="Add" /></p>
				</fieldset>		
			</form>
		<?php
			if(isset($_POST["videoName"])){
				// create new entry:
				$query = "INSERT INTO video_inventory (videoName, videoCategory, videoLength) VALUES (?, ?, ?)";
				
				// create variable names
				$vName = $_POST["videoName"];
				$vCat = $_POST["filmCategory"];
				$vLen = $_POST["filmLength"];
				
				$message1 = "ERROR: 'Name' is a required field.";
				$message2 = "ERROR: Please input a valid positive number for 'Length'.";
								
				//Make sure customer has entered all required data
				//source: http://stackoverflow.com/questions/13851528/how-to-pop-the-alert-using-php 
				if($vName === ""){
					echo "<p class='warning'>" . $message1 . "</p>";
					
				} else if($vLen != "" && (!is_numeric($vLen) || $vLen < 1)){
					echo "<p class='warning'>" . $message2 . "</p>";
					
				} else {						
					if(!$stmt = $mysqli->prepare($query)){
						echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
					}
					
					//bind the parameters
					if(!$stmt->bind_param('ssi', $vName, $vCat, $vLen)){
						echo "Bind failed: " . $stmt->errno . " " . $stmt->error;
					}

					//execute
					if (!($stmt->execute())) {
						echo "Execute failed: " . $stmt->errno . " " . $stmt->error;
					//} else {
					//	echo $stmt->affected_rows . " records(s) added";
					}		

					// close statement
					$stmt->close();	
				}
			}
		?>
		</td>
		<td><form method="post" action="videoInventory.php">
			<fieldset>
				<legend><h3>Filter movies by category</h3> </legend>
				<h4>Please select a category from the drop down menu below.</h4>
				<label>
					Film Category:
					<select name="fCat" >
						
						<?php
							if(!($stmt = $mysqli->prepare("SELECT DISTINCT videoCategory FROM video_inventory WHERE videoCategory != ' '"))){
								echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
							}

							if(!$stmt->execute()){
								echo "Execute failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
							}
							if(!$stmt->bind_result($vCat1)){
								echo "Bind failed: "  . $mysqli->connect_errno . " " . $mysqli->connect_error;
							}
							
							if($stmt->fetch() !== null){
								//If the records table is not empty
								//Output the first result of fetch and add 'select all' option ...
								echo "<option value='selectAll'>Select all</option>";
								echo "<option value='". $vCat1 . "'> " . $vCat1 . "</option>\n";
							}
							
							//...then output the rest of the results
							while($stmt->fetch()){
								echo "<option value='". $vCat1 . "'> " . $vCat1 . "</option>\n";
							}
							$stmt->close();
							
						?> 
					</select>	
				</label>
				<input type="submit" value=" Filter " >
			</fieldset>
		</form></td></tr>
	</table>
	<h2>Video Inventory List</h2>
	
	<p><form method="post" action="videoDelete.php"  >
		<input type="submit" value="Delete All Entries" name="delAll" >
	</form></p>
	
	<table id="List">
		<tr>
			<th width="275">Name</th>
			<th width="125">Category</th>
			<th width="75">Length</th>
			<th width="150">Status</th>
			<th width="100">Delete</th>
			<th width="100">Check In/Out</th>
		</tr>
		<?php
			if(isset($_POST["fCat"])){	
				// create variable names
				$vCat2 = $_POST["fCat"];
				
				//Create query based on user request
				if($vCat2 === "selectAll"){
					$query = "SELECT vi.videoID, vi.videoName, vi.videoCategory, vi.videoLength, vi.videoRented
								FROM video_inventory vi";
					
					if(!$stmt = $mysqli->prepare($query)){
					echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
					}				

				
				} else{
					$query = "SELECT vi.videoID, vi.videoName, vi.videoCategory, vi.videoLength, vi.videoRented
								FROM video_inventory vi WHERE vi.videoCategory=?";
					
					if(!$stmt = $mysqli->prepare($query)){
					echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
					}
				
					//bind the parameters
					if(!$stmt->bind_param('s', $vCat2)){
						echo "Bind failed: " . $stmt->errno . " " . $stmt->error;
					}					
				}

				if (!($stmt->execute())) {
					echo "Execute failed: " . $stmt->errno . " " . $stmt->error;
				}
		
				if (!($stmt->bind_result($vId, $vName, $vCategory, $vLength, $vRented))) {
					echo "Bind failed: "  . $stmt->errno . " " . $stmt->error;
				}
				
				while($stmt->fetch()){
					$status = NULL;
										
					if($vRented == "1"){
						$status = "Available";
						echo "<tr>\n<td>\n" . $vName . "\n</td>\n<td>\n" . $vCategory . "\n</td>\n<td>\n" . $vLength . 
						"\n</td>\n<td>\n" .  $status . "\n</td>\n<td>\n" . "<a href='videoDelete.php?id=$vId'>Delete</a>" . 
						"\n</td>\n<td>\n" . "<a href='vCheckOut.php?id=$vId'>Check out</a>" . "\n</td>\n</tr>";
					} else {
						$status = "Checked out";
						echo "<tr>\n<td>\n" . $vName . "\n</td>\n<td>\n" . $vCategory . "\n</td>\n<td>\n" . $vLength . 
						"\n</td>\n<td>\n" .  $status . "\n</td>\n<td>\n" . "<a href='videoDelete.php?id=$vId'>Delete</a>" . 
						"\n</td>\n<td>\n" . "<a href='vCheckIn.php?id=$vId'>Check in</a>" . "\n</td>\n</tr>";
					}				
				}
				
				// close statement
				$stmt->close();
		
			}	else {	//No filtering applied display all entries
			
				$query = "SELECT vi.videoID, vi.videoName, vi.videoCategory, vi.videoLength, vi.videoRented
								FROM video_inventory vi";
					
				if(!$stmt = $mysqli->prepare($query)){
					echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
				}
			
				if (!($stmt->execute())) {
						echo "Execute failed: " . $stmt->errno . " " . $stmt->error;
				}
			
				if (!($stmt->bind_result($vId, $vName, $vCategory, $vLength, $vRented))) {
					echo "Bind failed: "  . $stmt->errno . " " . $stmt->error;
				}
					
				while($stmt->fetch()){
					$status = NULL;
				
					if($vRented == "1"){
						$status = "Available";
						echo "<tr>\n<td>\n" . $vName . "\n</td>\n<td>\n" . $vCategory . "\n</td>\n<td>\n" . $vLength . 
						"\n</td>\n<td>\n" .  $status . "\n</td>\n<td>\n" . "<a href='videoDelete.php?id=$vId'>Delete</a>" . 
						"\n</td>\n<td>\n" . "<a href='vCheckOut.php?id=$vId'>Check out</a>" . "\n</td>\n</tr>";
					} else {
						$status = "Checked out";
						echo "<tr>\n<td>\n" . $vName . "\n</td>\n<td>\n" . $vCategory . "\n</td>\n<td>\n" . $vLength . 
						"\n</td>\n<td>\n" .  $status . "\n</td>\n<td>\n" . "<a href='videoDelete.php?id=$vId'>Delete</a>" . 
						"\n</td>\n<td>\n" . "<a href='vCheckIn.php?id=$vId'>Check in</a>" . "\n</td>\n</tr>";
					}
				}
				
				// close statement
				$stmt->close();	
			}
		?>
	</table>
	
	
	
	
</body>
</html>