<?php
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


if(isset($_GET["id"])){
	$vId = $_GET["id"];
	$query = "DELETE FROM video_inventory WHERE videoID=?";
			
	if(!$stmt = $mysqli->prepare($query)){
		echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
	}
		
	//bind the parameters
	if(!$stmt->bind_param('i', $vId)){
		echo "Bind failed: " . $stmt->errno . " " . $stmt->error;
	}

	//execute
	if (!($stmt->execute())) {
		echo "Execute failed: " . $stmt->errno . " " . $stmt->error;
	}

	// close statement		
	$stmt->close();				
}

header("Location:videoInventory.php", true);		
?>