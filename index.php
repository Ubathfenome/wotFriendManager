<?php 
	$installURL = "http://localhost/wotfm/install.php";
	if(file_exists("./db/connectionData.php") == 0){
		header("Location: ".$installURL);
		die();
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>WoT Players</title>
		<link rel="shortcut icon" href="./images/favicon.png">
		<link rel="stylesheet" type="text/css" href="./css/common.css">
		<link rel="stylesheet" type="text/css" href="./css/style001.css">
	</head>
	<body>
		<h1>World of Tanks - Contacts Manager</h1>
		<span id="clanname"></span>
		<div id="id01">
			<div id="form">
				<form id="QUERY" class="QUERY" name="QUERY" method="POST">
					<fieldset>
						<legend><span class="h3">Player's Data</span></legend>
						<label for="username">Username:</label>
						<input type="text" id="username" name="search" ondrop="drop(event)" ondragover="allowDrop(event)" autofocus/>
						<input type="button" value="Submit" onclick="requestUserData()"/>
					</fieldset>
				</form>
				<div id="login">
					<form id="AUTH" class="AUTH" name="AUTH" method="POST" action="/">
						<fieldset>
							<legend><span class="h3">Auth</span></legend>
							<div id="login_msg">
								<input type="submit" value="Login on WG"/>
							</div>
						</fieldset>
					</form>
				</div>
			</div>
			<div id="saved_players">
				<h3>Saved players:</h3>
				<ul id="players_list">
				</ul>
			</div>
		</div>

		<div id="id02">
		</div>
		
		<div id="id03">
		</div>
		<script src="./scripts/appid.js"></script>
		<script title="API_requests">
			var urlUser = "https://api.worldoftanks.eu/wot/account/list/?";
			var urlPlayer = "https://api.worldoftanks.eu/wot/account/info/?";
			var urlClan = "https://api.worldoftanks.eu/wgn/clans/info/?";

			var application_id = appid;
			var account_id;
			var logged_account_id;
			var clan_id;
			var nickname;
			var fieldsUser = "account_id, nickname";
			var fieldsPlayer = "clan_id, global_rating, last_battle_time, logout_at";
			var fieldsClan = "name, tag";
			var elem;
			var search;
			var paramsUser;
			var paramsPlayer;
			
			var rc="rco_";	// Registered Contact
			var lc="lcl_";	// Logged on the Clan
			var ec="ecl_";	// Existent on the Clan
			var taglength=rc.length-1;
			
			var playerLogged=false;
			var token;

			var html;
			var xmlhttp;

			function requestUserData(){
				elem = document.getElementById("username");
				search = elem.value;
				paramsUser = "application_id=" + application_id + "&fields=" + fieldsUser + "&search=" + search;
				
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange=function() {
					if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
						parseUser(xmlhttp.responseText);
						document.getElementById("id02").innerHTML = html;
						account_id = '0';
					}
				};

				xmlhttp.open("GET", urlUser+paramsUser, true);
				xmlhttp.send();
			}
			function requestUsernameData(){
				search = nickname;
				paramsUser = "application_id=" + application_id + "&fields=" + fieldsUser + "&search=" + search;
				
				xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange=function() {
					if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
						parseUser(xmlhttp.responseText);
						document.getElementById("id02").innerHTML = html;
						account_id = '0';
					}
				};

				xmlhttp.open("GET", urlUser+paramsUser, true);
				xmlhttp.send();
			}
			
			function requestUData(account){
				paramsPlayer = "application_id=" + application_id + "&fields=nickname, clan_id&account_id=" + account;
				xmlhttp = new XMLHttpRequest();
				xmlhttp.open("GET", urlPlayer+paramsPlayer, false);
				xmlhttp.send();
				var playerName = parsePlayerName(xmlhttp.responseText);
				return playerName;
			}

			function requestPlayerData(account){
				if(!playerLogged)
					fieldsPlayer = "clan_id, global_rating, last_battle_time, logout_at";
				else
					fieldsPlayer = "clan_id, global_rating, last_battle_time, logout_at, private";
				paramsPlayer = "application_id=" + application_id + "&fields=" + fieldsPlayer + "&account_id=" + account;
				logged="&extra=private.grouped_contacts";
				token="&access_token=" + token;
				
				xmlhttp = new XMLHttpRequest();

				if(!playerLogged){
					xmlhttp.open("GET", urlPlayer+paramsPlayer, false);
					sessionStorage.clear();
				}else
					xmlhttp.open("GET", urlPlayer+paramsPlayer+logged+token, false);
				xmlhttp.send();
				var playerData = parsePlayer(xmlhttp.responseText);
				return playerData;
			}
			
			function requestClanData(clan_id){
				// application_id, fields, access_token, extra, clan_id
				if(!playerLogged)
					fieldsClan = "name, tag";
				else
					fieldsClan= "name, tag, private";
				paramsClan="application_id=" + application_id + "&fields=" + fieldsClan + "&clan_id=" + clan_id;
				logged="&extra=private.online_members";
				token="&access_token=" + token;
				
				xmlhttp = new XMLHttpRequest();

				if(!playerLogged){
					xmlhttp.open("GET", urlClan+paramsClan, false);
					sessionStorage.clear();
				}else
					xmlhttp.open("GET", urlClan+paramsClan+logged+token, false);
				xmlhttp.send();
				if(document.getElementById("clanname").children.length==0){
					var clanData = parseClanname(xmlhttp.responseText);
					var child = document.createElement("h2");
					var t = document.createTextNode(clanData);
					child.appendChild(t);
					document.getElementById("clanname").appendChild(child);
				}
				if(playerLogged){
					var clanMembers = parseClanmembers(xmlhttp.responseText);
				}
			}

			function parseUser(response) {
				var arr = JSON.parse(response);
				var out;
				
				try {
					account_id = arr.data[0].account_id;
					nickname = arr.data[0].nickname;
				} catch(err) {
					out = "<h2>Wrong Username</h2>";
				}
				
				if(account_id != '0' && typeof account_id != 'undefined'){
					out = "<h2>User Found!</h2>\n" +
				"<table>\n<tr><th>ID</th><th>Nickname</th><th>Global Rating</th><th>Last Battle StartTime</th><th>Logout At</th></tr>\n";
					out += "<tr><td>";				
					out += account_id;
					out += "</td><td>";
					out += nickname;
					out += "</td><td>";
					out += requestPlayerData(account_id);
					out += "</td></tr>\n";
					out += "</table>\n";
				}
				
				html = out;
			}

			function parsePlayer(response) {
				var arr = JSON.parse(response);
				var out = "";
				
				var loggedUserAccountId = account_id;
				clan_id=arr.data[loggedUserAccountId].clan_id;
				var loggedUserClanId = clan_id;
				
				var grouped_contacts;				
				
				// To game backend events "logout" and "updated_at" are the same. But, you can use wot/tanks/* methods instead of wot/account/* methods, they updates after every battle.
				
				out += arr.data[account_id].global_rating;
				out += "</td><td>";
				battdate = new Date(arr.data[account_id].last_battle_time * 1000);
				out += battdate.getDate() + "/" + (battdate.getMonth()+1) + "/" + battdate.getFullYear() + " " + battdate.getHours() + ":" + battdate.getMinutes() + ":" + battdate.getSeconds();
				out += "</td><td>";
				logodate = new Date(arr.data[account_id].logout_at * 1000);
				out += logodate.getDate() + "/" + (logodate.getMonth()+1) + "/" + logodate.getFullYear() + " " + logodate.getHours() + ":" + logodate.getMinutes() + ":" + logodate.getSeconds();
				out += "</td>";
				
				if(playerLogged && (arr.data[account_id].private != null)){
					grouped_contacts = arr.data[account_id].private.grouped_contacts.groups;
					var groups = Object.keys(grouped_contacts);
					if(groups.length==0)
						sessionStorage.clear();
					for(var i = 0; i < groups.length; i++){
						var group = grouped_contacts[groups[i]];
						for(var j = 0; j < group.length; j++){
							account_id=group[j];
							var user = requestUData(group[j]);
							if(loggedUserClanId==clan_id){
								if(sessionStorage.getItem(rc + ec + group[j]) == null){
									sessionStorage.setItem(rc + ec + group[j], user);
								}
							} else {
								if(sessionStorage.getItem(rc + group[j]) == null){
									sessionStorage.setItem(rc + group[j], user);
								}
							}
						}
					}
					if(sessionStorage.getItem(rc + ec + loggedUserAccountId) == null){
						sessionStorage.setItem(rc + ec + loggedUserAccountId, nickname);
					}
					
					update_store();
				}
				return out;
			}
			
			function parsePlayerName(response){
				var arr = JSON.parse(response);
				var out = "";
				try {
					clan_id = arr.data[account_id].clan_id;
					out = arr.data[account_id].nickname;
				} catch(err) {
					console.log("Account_id error: Try accessing with the correct id next time.")
				}
				return out;
			}
			
			function parseClanname(response){
				var arr = JSON.parse(response);
				return "[" + arr.data[clan_id].tag + "] - " + arr.data[clan_id].name;
			}
			
			function parseClanmembers(response){
				var arr = JSON.parse(response);
				var online = arr.data[clan_id].private.online_members;
				
				for(var m = 0; m < online.length; m++){
					if(sessionStorage.getItem(lc + online[m]) == null){
						account_id=online[m];
						sessionStorage.setItem(lc + online[m], requestUData(online[m]));
					}
				}
				update_store();
			}
		</script>
		<script src="./scripts/get_params.js"></script>
		<script title="log_user">
			function logged() {
				var params = getSearchParameters();
				if(params.status=="ok") {
					nickname=params.nickname;
					account_id=params.account_id;
					logged_account_id=params.account_id;
					document.getElementById("login").innerHTML = "<form class=\"AUTH\" method=\"POST\" action=\"https://api.worldoftanks.eu/wot/auth/logout/?\" onsubmit=\"return resetPage()\">\n" + 
					"<fieldset>\n<legend><span class=\"h3\">Auth</span></legend>\n<div id=\"logout_msg\">\n" +
					"<h3>Logged in succesfully!</h3>\n<p>Hi, " + params.nickname + "!</p>\n" +
					"<input type=\"hidden\" name=\"application_id\" value=\"" + application_id + "\"/>\n" +
					"<input type=\"hidden\" name=\"access_token\" value=\"" + params.access_token + "\"/>\n" + 
					"<input type=\"submit\" value=\"Logout of WG\" alt=\"Redirects to a new Webpage\"/>\n" + 
					//"<a id=\"refresh_online\" class=\"ICON\" href=\"javascript:requestPlayerData("+logged_account_id+");\">\n<img src=\"./images/Reload.png\" alt=\"Reload user data\"/>\n</a>\n" + 
					"</div>\n</fieldset>\n</form>\n";
					document.getElementById("login").innerHTML+="<form class=\"ReplayBD\" method=\"POST\" action=\"./replays.php?userid="+logged_account_id+"\">\n" +
					"<fieldset>\n<legend>Replays</legend>\n" +
					"<input type=\"submit\" value=\"Access the Replays manager\" />\n" +
					"</fieldset>"
					"</form>\n";
					token=params.access_token;
					playerLogged=true;
					// Search for user.contacts
					requestPlayerData(params.account_id);
					account_id=logged_account_id;
					requestUData(params.account_id);
					requestClanData(clan_id);
				} else {
					document.getElementById("login_msg").innerHTML = "<input type=\"submit\" value=\"Login on WG\" alt=\"Opens on a new Window\"/>\n<h3>Log in failed!</h3>\n";
				}
			}
		</script>
		<script title="drag_drop">
			// http://www.w3schools.com/html/html5_draganddrop.asp
			function allowDrop(ev) {
				ev.preventDefault();
			}

			function drag(ev) {
				ev.dataTransfer.setData("text", ev.target.innerText);
			}

			function drop(ev) {
				ev.preventDefault();
				var data = ev.dataTransfer.getData("text");
				ev.target.value = data;
			}
		</script>
		<script title="arrays">
			function contains(a, obj) {
				for (var i = 0; i < a.length; i++) {
					var key = a.key(i);
					if (key.includes(obj)) {
						return true;
					}
				}
				return false;
			}
			function containsAmmount(a, obj) {
				var amm = 0;
				for (var i = 0; i < a.length; i++) {
					var key = a.key(i);
					if (key.includes(obj)) {
						amm++;
					}
				}
				return amm;
			}
			function getAllTags(a,obj) {
				// http://www.w3schools.com/jsref/jsref_push.asp
				var tags = [];
				var tags2Add = [];
				var indexes = [];
				var tagStringLength=taglength+1;
				var item=null;
				
				for (var i = 0; i < a.length; i++) {
					var key = a.key(i);
					item=a.getItem(key);
					if (key.includes(obj)) {
						var ini = key.length-obj.length;
						var values = key.substr(0,ini);
						indexes.push(i);
						for (var j = 0;j<ini;j+=tagStringLength){
							var currentTag = values.substr(j,tagStringLength);
							tags.push(currentTag);
						}
					}
				}
				
				return tags;
			}
			function hashmapContains(hashmap, obj){
				for (var i = 0; i < hashmap.length; i++) {
					var key = hashmap[i].key;
					if (key.includes(obj)) {
						return true;
					}
				}
				return false;
			}
			function have(a, obj) {
				for (var i = 0; i < a.length; i++) {
					var key = a[i];
					if (key.includes(obj)) {
						return true;
					}
				}
				return false;
			}
			function haveIndex(a, obj){
				for (var i = 0; i < a.length; i++) {
					var key = a[i];
					if (key.includes(obj)) {
						return i;
					}
				}
				return -1;
			}
			function hashmapIndex(hashmap, obj){
				for (var i = 0; i < hashmap.length; i++) {
					var key = hashmap[i].key;
					if (key.includes(obj)) {
						return i;
					}
				}
				return -1;
			}
			function arrayUnique(array) {
				// http://stackoverflow.com/questions/1584370/how-to-merge-two-arrays-in-javascript-and-de-duplicate-items
				var a = array.concat();
				for(var i=0; i<a.length; ++i) {
					for(var j=i+1; j<a.length; ++j) {
						if(a[i] === a[j])
							a.splice(j--, 1);
					}
				}

				return a;
			}
			function eraseWGKey(a, wg_key){
				var newarray = [];
				for(var i = 0; i < a.length; i++){
					if(!a[i].key.includes(wg_key)){
						newarray.push(a[i]);
					}
				}
				
				return newarray;
			}
			function sortStorage(a){
				var hashmap = [];
				
				for(var i = 0; i < a.length; i++){
					var key = a.key(i);
					var value = a.getItem(key);
					var wg_key = key.split("_").pop();
					if(!hashmapContains(hashmap, wg_key)){
						hashmap.push({
							key: key,
							value: value
						});
					} else {
						var curr_tags = key.substr(0,key.length-wg_key.length);
						var existing_tags = hashmap[hashmapIndex(hashmap, wg_key)].key.substr(0, key.length-wg_key.length);
						var tags = [];
						tags = existing_tags.split("_");
						if(tags.length>1)
							tags.pop();
						var temparr = [];
						temparr = curr_tags.split("_");
						if(temparr.length>1)
							temparr.pop();
						tags = arrayUnique(tags.concat(temparr));
						hashmap=eraseWGKey(hashmap,wg_key);
						hashmap.push({
							key: tags.join("_") + "_" + wg_key,
							value: value
						});
					}
				}
				
				return hashmap;
			}
		</script>
		<script title="local_storage">
			// http://www.w3schools.com/html/html5_webstorage.asp
			// https://www.nczonline.net/blog/2009/07/21/introduction-to-sessionstorage/
			function update_store() {
				if(typeof(Storage) !== "undefined") {
					// Code for sessionStorage/sessionStorage.
					var open_code = "<h3>Contacts:</h3>\n<ul id=\"players_list\">\n";
					var close_code = "</ul>\n";
					// Store
					if(!sessionStorage.loaded){
						sessionStorage.loaded = true;
					}
					// Retrieve
					document.getElementById("saved_players").innerHTML = open_code;
					for(var i=0; i < sessionStorage.length; i++){
						var current_key = sessionStorage.key(i);
						var key_pieces = current_key.split("_");
						var wg_key = key_pieces[key_pieces.length-1];
						var keytypes = current_key.substr(0,current_key.length-wg_key.length);
						
						if(!current_key.includes("loaded")){
							var hashmap = sortStorage(sessionStorage);
							sessionStorage.clear();
							for(var j=0;j<hashmap.length;j++){
								sessionStorage.setItem(hashmap[j].key,hashmap[j].value);
							}
							var tags = getAllTags(sessionStorage, wg_key);
							if(sessionStorage.getItem(current_key)==undefined)
								current_key = tags.join("") + wg_key;
							if(have(tags, rc) && !have(tags, ec)){
								// Friend Not in Clan & Unknown logon state
								document.getElementById("players_list").innerHTML += "<li id=\"" + current_key + "\" draggable=\"true\" ondragstart=\"drag(event)\">" + sessionStorage.getItem(current_key) + "</li>\n";
							} else if(have(tags, rc) && have(tags, ec) && have(tags, lc)){
								// Friend in Clan & Logged on
								document.getElementById("players_list").innerHTML += "<li id=\"" + current_key + "\" class=\"on\" draggable=\"true\" ondragstart=\"drag(event)\">" + sessionStorage.getItem(current_key) + "</li>\n";
							} else if(have(tags, rc) && have(tags, ec) && !have(tags, lc)){
								// Friend in Clan & Logged off
								document.getElementById("players_list").innerHTML += "<li id=\"" + current_key + "\" class=\"off\" draggable=\"true\" ondragstart=\"drag(event)\">" + sessionStorage.getItem(current_key) + "</li>\n";
							} else if(!have(tags, rc) && have(tags, lc)){
								// Not friend but contact in Clan & Logged on
								document.getElementById("players_list").innerHTML += "<li id=\"" + current_key + "\" class=\"on\" draggable=\"true\" ondragstart=\"drag(event)\">" + sessionStorage.getItem(current_key) + "</li>\n";
							} else if(!have(tags, rc) && have(tags, ec) && !have(tags, lc)){
								// Not friend but contact in Clan & Logged off
								document.getElementById("players_list").innerHTML += "<li id=\"" + current_key + "\" class=\"off\" draggable=\"true\" ondragstart=\"drag(event)\">" + sessionStorage.getItem(current_key) + "</li>\n";
							}
						}
					}
					document.getElementById("saved_players").innerHTML += close_code;
				} else {
					// Sorry! No Web Storage support..
					document.getElementById("saved_players").innerHTML = "Sorry, your browser does not support Web Storage...";
				}
			}
		</script>
		<script title="reset_page">
			function resetPage(){
				playerLogged=false;
				sessionStorage.clear();
				window.location="http://localhost/wotfm/index.php";
				return true;
			}
		</script>
		<script title="form_action">
			window.onload = function() {
				if(document.AUTH!=null)
					document.AUTH.action=get_action();
			}

			function get_action() {
				return url;
			}
		</script>
		<script title="init_functions">
			update_store();
			logged();
		</script>
		<?php require('./footer.php'); ?>
	</body>
</html>