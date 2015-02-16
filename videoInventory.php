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
		fieldset { width: 400px; border-radius: 10px; }
		input { border: 1px solid; padding-top: 5px; float: right; width: 300px; }			
		th, td { border: 2px solid; padding: 10px; }	
		#delete {width: 100px;}
	</style>
</head>
<body>
	<form action="videoInventory.php" method="post">
		<fieldset id="addForm">
			<legend><h3>Add a Video</h3> </legend>
			<p>						   
				<label class="title">Name <input type="text" name="videoName"></label>
			</p>
			<p>
				<label class="title">Category <input type="text" name="filmCategory"></label>
			</p>
			<p>
				<label class="title">Length <input type="text" name="filmLength"></label>	
			</p>					
			<p><input type="submit" value="Add" /></p>
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
?>
	
	<h2>Video Inventory List</h2>
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
			if (!($stmt = $mysqli->prepare("SELECT vi.videoID, vi.videoName, vi.videoCategory, vi.videoLength, vi.videoRented
								FROM video_inventory vi"))) {
				echo "Prepare failed: " . $stmt->errno . " " . $stmt->error;
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
		?>
	</table>
	
	
	
	
</body>
</html>