<?php
////////////////////////////////////////////////////////////////////////////////////////
//Server Info Script v1.1 by KilerCris//////////////////////////////////////////////////
//Modified by Moriarty 19/March/03
////////////////////////////////////////////////////////////////////////////////////////
//Server Operator: Edit these vars//////////////////////////////////////////////////////
$server_op = $cw['your_name']; //Name/Nick of server operator
$server_op_email = $cw['your_email']; //Email address of server operator
$server_name = $cw['server_name']; //Name of server
$server_description = $cw['server_description']; //General Description of server
$server_version = "2.3.2"; //Release version of codebase. Suffix an 'M' if code is modified
////////////////////////////////////////////////////////////////////////////////////////

//Block any request not coming from solarempire.com
if($HTTP_SERVER_VARS['REMOTE_ADDR'] != gethostbyname("solarempire.com")) {
	header("HTTP/1.0 403 Forbidden"); 
	header("Status: 403 Forbidden");
	exit();
}


//Create server information structure
$server_info = array();
$server_info['op'] = $server_op;
$server_info['opemail'] = $server_op_email;
$server_info['desc'] = $server_description;
$server_info['ver'] = $server_version;
$server_info['games'] = array();


//Load server configueration
require("dir_names.php");
require("$directories[games]/config.dat.php");
unset($directories);

$server_info['name'] = SERVER_NAME;

db_connect();


//Retrieve game information that is relevent to the enquiery only (e.g. not admin passwords).
$mq_result = mysql_query("select name,admin_name,admin_email,description,paused,num_stars,difficulty,last_reset from se_games");
while($game = mysql_fetch_array($mq_result)) {
	$game_info = array();
	
	//Basic game info
	$game_info['name'] = $game['name'];
	$game_info['admin'] = $game['admin_name'];
	$game_info['adminemail'] = $game['admin_email'];
	$game_info['desc'] = $game['description'];
	$game_info['p'] = $game['paused'];
	$game_info['num_stars'] = $game['num_stars'];
	$game_info['difficulty'] = $game['difficulty'];
	$game_info['last_reset'] = $game['last_reset'];


	////Extended game info

	//Number of stars(value in se_games inaccurate)
/*	if($mq_tres = mysql_query("select count(star_id) AS numstars from $game[db_name]_stars")) {
		$res = mysql_fetch_array($mq_tres);
		$game_info['numstars'] = $res['numstars'];
	} else { die('err'); }*/
	#Use value from se_games as it can't be changed without DB permission anyway.

	//Number of players (first 5 accounts are pre-genned)
	if($mq_tres = mysql_query("select count(login_id) AS numplay from $game[db_name]_users where login_id > 5")) {
		$res = mysql_fetch_array($mq_tres);
		$game_info['numplay'] = $res['numplay'];
	} else { die('err'); }
	//Current active users count
	if($mq_tres = mysql_query("select count(login_id) AS active_users from $game[db_name]_users where login_id > 5 && last_request > ".(time()-300))) {
		$res = mysql_fetch_array($mq_tres);
		$game_info['active_u'] = $res['active_users'];
	} else { die('err'); }
	//Get approx. time of reset from join time of first real user
	/*if($mq_tres = mysql_query("select joined_game from $game[db_name]_users where login_id != 1 order by login_id limit 1")) {
		$res = mysql_fetch_array($mq_tres);
		$game_info['age'] = $res['joined_game'];
	} else { die('err'); }*/
	#no need for that line as its no in the se_games db.

	//Get game status
	if($mq_tres = mysql_query("select ${db_name}_value as value from se_db_vars where name = 'sudden_death'")) {
		$res = mysql_fetch_array($mq_tres);
		$game_info['sd'] = $res['value'];
	} else { die('err'); }
	if($mq_tres = mysql_query("select ${db_name}_value as value from se_db_vars where name = 'new_logins'")) {
		$res = mysql_fetch_array($mq_tres);
		$game_info['nl'] = $res['value'];
	} else { die('err'); }


	$server_info['games'][] = $game_info;
}

//Send data back to host.
echo serialize($server_info);
?>