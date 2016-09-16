<?php
	
	require('connectionData.php');
		
	if (isset($_POST["userid"]) && isset($_POST["country"]) && isset($_POST["tank"]) && isset($_POST["mapcode"]) && isset($_POST["mapname"]) && isset($_POST["date"]) && isset($_POST["time"])) {
		// Create connection
		$conn = new mysqli($servername, $username, $password, $dbname);
		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		} else {
			echo "Connected successfully.";
		}
		
		$sql = "SELECT * FROM " . $dbtable01 . " WHERE userid EQ " . $_POST["userid"] . " date EQ " . $_POST["date"] . " AND time EQ " . $_POST["time"];
		$result = $conn->query($sql);
		
		$sql = "INSERT INTO " . $dbtable01 . " (userid, country, tank, mapcode, mapname, date, time) VALUES ('" . $_POST["userid"] . "','" . $_POST["country"] . "','" . $_POST["tank"] . "','" . $_POST["mapcode"] . "','" . $_POST["mapname"] . "','" . $_POST["date"] . "','" . $_POST["time"] . "')";

		if ($conn->query($sql) === TRUE) {
			echo "New " . $dbtable01 . " record created successfully";
		} else {
			echo "Error: " . $sql . "<br>" . $conn->error;
		}
		
		$sql = "SELECT * FROM " . $dbtable02 . " WHERE tank EQ " . $_POST["tank"];
		$result = $conn->query($sql);
		
		if ($result->num_rows == 0) {
			$sql = "INSERT INTO " . $dbtable02 . " (country, tank) VALUES ('" . $_POST["country"] . "','" . $_POST["tank"] . "')";

			if ($conn->query($sql) === TRUE) {
				echo "New " . $dbtable02 . " record created successfully";
			} else {
				echo "Error: " . $sql . "<br>" . $conn->error;
			}
		} else {
			echo "Already existing entry.";
		}
		
		$sql = "SELECT * FROM " . $dbtable03 . " WHERE mapcode EQ " . $_POST["mapcode"];
		$result = $conn->query($sql);
		
		if ($result->num_rows == 0) {
			$sql = "INSERT INTO " . $dbtable03 . " (mapcode, mapname) VALUES ('" . $_POST["mapcode"] . "','" . $_POST["mapname"] . "')";

			if ($conn->query($sql) === TRUE) {
				echo "New " . $dbtable03 . " record created successfully";
			} else {
				echo "Error: " . $sql . "<br>" . $conn->error;
			}
		} else {
			echo "Already existing entry.";
		}
		
		echo "Closing connection.";
		$conn->close();
		echo "<br>";
	}
?>