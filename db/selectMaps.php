<?php
	require('connectionData.php');
	
	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	} 

	$sql = "SELECT DISTINCT mapname, (select count(*) FROM " . $dbtable01 . " r2 where r1.mapname = r2.mapname) as battles FROM " . $dbtable01 . " r1";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			echo $row["mapname"] . ";" . $row["battles"] . "@";
		}
	}
	$conn->close();
?>