<?php
require_once("common.inc.php");

//Connect to the database
db_connect();

check_auth();

//admin logout
if($login_id == 1){
	db("update se_games set session_id = 0, session_exp=0 where db_name = '$db_name'");

//logout FROM GAME. to either gamelisting or index
} elseif(isset($logout_single_game) || isset($comp_logout)){

	dbn("update user_accounts set in_game = '' where login_id = '$login_id'");
	SetCookie("p_pass","",0);

	dbn("update ${db_name}_users set on_planet = 0 where login_id = '$login_id'");
	//Update score, and last_request
	score_func($login_id,0);

	$time_to_set = time() - 1800; //30 mins ago
	dbn("update ${db_name}_users set last_request = '$time_to_set' where login_id = '$login_id'");


	//only logging out to gamelisting
	if(isset($logout_single_game)){
		insert_history($login_id,sprintf($st[75], $db_name));
		header("Location: ".URL_PREFIX."/game_listing.php");
//		echo "<script>self.location='".URL_PREFIX."/game_listing.php';</script>";
		exit();
	}
}

insert_history($login_id,$cw['logged_out_completely']);

//unset session details.
dbn("update user_accounts set session_id = '', session_exp = 0 where login_id = '$login_id'");

SetCookie("session_id",0,0);
SetCookie("login_id",0,0);

if ($p_user['bp_user_id']) {
	$url = 'http://www.bigpoint.com/';
} else {
	$url = URL_PREFIX."/index.php";
}
header("Location: $url");
//echo "<script>self.location='".URL_PREFIX."/';</script>";
exit();
?>
