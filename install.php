<!DOCTYPE HTML>
<html>
	<head>
		<title>Wot Friend Manager Installer</title>
		<link rel="shortcut icon" href="./images/favicon.png">
		<style>
			body {
				background-color: white;
			}
			h1 {
				text-align: center;
			}
			div#form {
				border: 1px black solid;
				border-radius: 4px;
				box-shadow: 2px 2px 5px grey;
				margin: auto;
				width: 200px;
				padding: 2%;
				align: center;
				display: block;
				background-color: #FFF9F2;
			}
			input {
				margin-bottom: 2%;
			}
		</style>
	</head>
	<body>
		<h1>Install SQL User data</h1>
		<div id="form">
			<form method="POST">
				<label for="sqlserver">SQL Server</label>
				<input type="text" id="sqlserver" name="sqlserver" value="localhost" disabled/>
				<label for="ruser">Root user</label>
				<input type="text" id="ruser" name="ruser" autofocus/>
				<label for="rpass">Root password</label>
				<input type="password" id="rpass" name="rpass"/>
				<input type="hidden" id="dbname" name="dbname" value="mysql" />
				<input type="submit" onclick="callInstallation()"/>
			</form>
		</div>
		<div id="response"></div>
		<script>
			var xmlhttp;
			var sqlserver="";
			var ruser="";
			var rpass="";
			var dbname="";
			
			function callInstallation(){
				sqlserver = document.getElementById("sqlserver").value;
				ruser = document.getElementById("ruser").value;
				rpass = document.getElementById("rpass").value;
				dbname = document.getElementById("dbname").value;
				
				var params = "sqlserver="+sqlserver+"&ruser="+ruser+"&rpass="+rpass+"&dbname="+dbname;
				
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange=function() {
					if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
						window.location="http://localhost/wotfm/index.php";
					}
				};

				xmlhttp.open("POST", "./install.php", true);
				xmlhttp.send(params);
			}
		</script>
	</body>
</html>

<?php
	$sqlerrors = 0;
	if(!isset($_POST["sqlserver"]))
		$_POST["sqlserver"] = "localhost";
	
	if(isset($_POST["sqlserver"]) && !empty($_POST["sqlserver"]) && !empty($_POST["ruser"]) && !empty($_POST["rpass"])){
		echo "<br>Starting installation.<br>";
		startInstallation($_POST["sqlserver"],$_POST["ruser"],$_POST["rpass"],$_POST["dbname"]);
	} else {
		if (count($_POST) > 0) {
			// echo "<br>Previous " . count($_POST) . " \$_POST values.<br>";
			foreach ($_POST as $k=>$v) {
				// echo($_POST[$k]) . "<br>";
				unset($_POST[$k]);
			}
			// echo "<br>Currently have " . count($_POST) . " \$_POST values.<br>";
		} else {
			/*
			echo "PHP Variables<br>";
			echo "Server: '" . $_POST["sqlserver"] . "'<br>";
			echo "User: '" . $_POST["ruser"] . "'<br>";
			echo "Password: '" . $_POST["rpass"] . "'<br>";
			echo "DBName: '" . $_POST["dbname"] . "'<br>";
			*/
		}
	}
	
	function startInstallation($sqlserver = "localhost", $ruser = "undefined", $rpass = "undefined", $dbname = "mysql"){
		global $sqlerrors;
		
		installWotfmUser($sqlserver, $ruser, $rpass, $dbname);
		
		writeConnectionData();
		if(file_exists("./db/connectionData.php") == 1 && $sqlerrors == 0){
			echo '<script language="javascript">alert("Installation completed successfully. Please delete \'install.php\' file.");</script>';
		}
	}

	function installWotfmUser($sqlserver, $ruser, $rpass, $dbname){
		// https://www.digitalocean.com/community/tutorials/crear-un-nuevo-usuario-y-otorgarle-permisos-en-mysql-es
		global $sqlerrors;
		// Create connection
		$conn = new mysqli($sqlserver, $ruser, $rpass, $dbname);
		// Check connection
		if ($conn->connect_error) {
			$sqlerrors++;
			die("Connection failed: " . $conn->connect_error);
		} else {
			echo "Connected successfully.<br>";
		}
		$sql = "SELECT DISTINCT user FROM `" . $_POST["dbname"] . "`.`user` WHERE user LIKE \"wotfm\";";
		$result = $conn->query($sql);
		
		if ($result->num_rows == 0) {
			$sql = "CREATE USER 'wotfm'@'localhost' IDENTIFIED BY 'wotfm';";
			
			if ($conn->query($sql) === TRUE) {
				echo "New user created successfully<br>";
				$sql = "GRANT USAGE ON *.* TO '" . $_POST["dbname"] . "'@'localhost' IDENTIFIED BY PASSWORD 'wotfm';" 
				+ "GRANT ALL PRIVILEGES ON `" . $_POST["dbname"] . "`.* TO 'wotfm'@'localhost';";
			if ($conn->query($sql) === TRUE) {
				echo "Privileges granted successfully<br>";
				$sql = "FLUSH PRIVILEGES;";
				if ($conn->query($sql) === TRUE) {
					echo "Privileges updated successfully<br>";
				} else {
					$sqlerrors++;
					echo "Error: " . $sql . "<br>" . $conn->error . "<br>";
				}
			} else {
				$sqlerrors++;
				echo "Error: " . $sql . "<br>" . $conn->error . "<br>";
			}
			} else {
				$sqlerrors++;
				echo "Error: " . $sql . "<br>" . $conn->error . "<br>";
			}

		} else {
			echo "User already on the database.<br>";
		}
		
		echo "Closing connection.<br>";
		$conn->close();
	}

	function writeConnectionData(){
		$myfile = fopen("./db/connectionData.php", "w") or die("Unable to open file!");
		$txt = "<?php\n\t\$servername = \"localhost\";\n\t\$username = \"wotfm\";\n\t\$password = \"wotfm\";\n\t\$dbname = \"wotReplays\";\n\n\t\$dbtable01 = \"treplays\";\n\t\$dbtable02 = \"ttanks\";\n\t\$dbtable03 = \"tmaps\";\n?>";
		fwrite($myfile, $txt);
		fclose($myfile);
	}
?>