<?php
	require('connectionData.php');
	
	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	} 

	$sql = "DELETE FROM " . $dbtable01;

	if ($conn->query($sql) === TRUE) {
		echo "All records deleted successfully.";
	} else {
		echo "Error deleting record: " . $conn->error;
	}
	$conn->close();
?>