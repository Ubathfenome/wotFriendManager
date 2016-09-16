<?php
	// http://stackoverflow.com/questions/32883160/comment-section-in-php-using-html-form-and-mysql-database
	require('connectionData.php');
	
	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	} else {
		echo "Connected successfully.";
	}

	// Create database
	$sql = "CREATE DATABASE IF NOT EXISTS " . $dbname;
	if ($conn->query($sql) === TRUE) {
		echo "Database created successfully.";
	} else {
		echo "Error creating database: " . $conn->error . ".";
	}

	/*
	// Drop tables
	// $sql = "DROP TABLE IF EXISTS " . $dbtable01 . "," . $dbtable02 . "," . $dbtable03;
	
	if ($conn->query($sql) === TRUE) {
		echo "Table " . $dbtable01 . " deleted successfully.";
	} else {
		echo "Error deleting table: " . $conn->error . ".";
	}
	*/
	
	// Create tables
	$sql = "CREATE TABLE IF NOT EXISTS " . $dbtable01 . " (
	userid VARCHAR(9) NOT NULL,
	country VARCHAR(30) NOT NULL,
	tank VARCHAR(30) NOT NULL,
	mapcode INT(2) NOT NULL,
	mapname VARCHAR(30) NOT NULL,
	date DATE,
	time TIME,
	PRIMARY KEY (userid, date, time)
	)";
	
	if ($conn->query($sql) === TRUE) {
		echo "Table " . $dbtable01 . " created successfully.";
	} else {
		echo "Error creating table: " . $conn->error . ".";
	}
	
	$sql = "CREATE TABLE IF NOT EXISTS " . $dbtable02 . " (
	country VARCHAR(30) NOT NULL,
	tank VARCHAR(30) NOT NULL PRIMARY KEY
	)";
	
	if ($conn->query($sql) === TRUE) {
		echo "Table " . $dbtable02 . " created successfully.";
	} else {
		echo "Error creating table: " . $conn->error . ".";
	}
	
	$sql = "CREATE TABLE IF NOT EXISTS " . $dbtable03 . " (
	mapcode INT(2) NOT NULL PRIMARY KEY,
	mapname VARCHAR(30) NOT NULL
	)";
	
	if ($conn->query($sql) === TRUE) {
		echo "Table " . $dbtable03 . " created successfully.";
	} else {
		echo "Error creating table: " . $conn->error . ".";
	}

	echo "Closing connection.";
	$conn->close();
	echo "<br>";
?>