<?php
/*************************************
* PHP Daily maint.
* Created: 3/May/2004
* By: Moriarty
**************************************/

//include('includes/langage_fr.inc.php');


/*********************
Intialisations
*********************/

//start counting
$maint_time = explode(" ",microtime());
$maint_time = $maint_time[1] + $maint_time[0];
$since_last = array(0 => 0);

require_once("../$directories[includes]/${db_name}_vars.inc.php");

db("select count(star_id) from ${db_name}_stars");
$num_ss_temp = dbr();
$num_stars = $num_ss_temp[0];

$final_str .= "\n\n<hr><p>Beginning Daily Maintenance for $db_name<p>";



/********************
* Quick Maints
********************/
//delete from clan and regular forums posts that are fairly old. Save space.
//dbn("delete from ${db_name}_messages where timestamp < ".time()."-604800 && login_id = -1");
//dbn("delete from ${db_name}_messages where timestamp < ".time()."-259200 && login_id = -5");


//work out how long it has been (in days) since the last reset. Then update day counter.
db("select last_reset from se_games where db_name = '$db_name'");
$last_reset_store = dbr(1);
$time_left = $GAME_VARS['game_length'] - floor((time() - $last_reset_store['last_reset']) / 86400);
if ($time_left > 0)
	dbn("update se_games set days_left = '$time_left' where db_name = '$db_name'");
else
	dbn("update se_games set status=0, paused=1 where db_name = '$db_name'");


//24hrs or so have passed, so update start_cash and start_tech with start_late_multiplier
if(mysql_affected_rows() > 0 && $GAME_VARS['start_late_multiplier'] > 0){
	//start_cash
	$GAME_VARS['start_cash'] += round($GAME_VARS['start_cash'] * ($GAME_VARS['start_late_multiplier'] / 100));
	dbn("update se_db_vars set ${db_name}_value = '$GAME_VARS[start_cash]' where name = 'start_cash'");

	//start_tech
	$GAME_VARS['start_tech'] += round($GAME_VARS['start_tech'] * ($GAME_VARS['start_late_multiplier'] / 100));
	dbn("update se_db_vars set ${db_name}_value = '$GAME_VARS[start_tech]' where name = 'start_tech'");
}
$final_str .= "\n<br />Quick-Maints Completed...<br />";
print_time();


/**********************
Run the relevent functions
**********************/
//retire inactives
retire_ood_users();
//send mails to inactive ~ 7days
mail_ood_users();
$final_str .= "\n<br />Inactives Retired...<br />";
print_time();

// Conditions pour être considéré comme actif et attribuer le GDT de parrainage:
// Au moins 10 connexions
// Au moins 20j d'ancienneté
// Dernière connexion - de 24h
$final_str .= "<br />Activation des parrainages...<br />";
db("select login_id, login_name, id_parrain from user_accounts where signed_up < ".(time() - 20*86400)." and login_count > 10 and parrainage_actif = 0 and id_parrain != 0 and last_login > ".(time() - 24*3600));
while ($data = dbr()) {
	$final_str .= "Parrainage activé - filleul:".$data['login_name'].", parrain:".$data['id_parrain']."<br />";
	dbn("update user_accounts set parrainage_actif=1 where login_id=".$data['login_id']);
	dbn("update user_accounts set gdt=gdt+1 where login_id=".$data['id_parrain']);
}

//move blackmarkets
if($GAME_VARS['uv_num_bmrkt'] > 0){
	move_blackmarkets();
	$final_str .= "\n<br />Blackmarkets Randomly Shifted...<br />";
	print_time();
}

//restock uni with minerals
if($GAME_VARS['rr_chance'] > 0){
	regerate_minerals();
	$final_str .= "\n<br />Minerals Regenerated...<br />";
	print_time();
}


//build new game_vars (as some may have changed).
$file_loc = "../$directories[includes]/${db_name}_vars.inc.php";
require("../build_vars.php");
$final_str .= "\n<br />Built db_vars.inc.php...<br />";
print_time();

/************************
* Shut Down the maint.
************************/

//print that maint was run, and how long it took.
$end_time = explode(" ",microtime());
$end_time = $end_time[1] + $end_time[0];
$total_time = ($end_time - $maint_time);

$final_str .= "\n<p>... All done in $total_time seconds.";
//post_news("Daily Maintenance run for this game in <b>$total_time</b> seconds", "maint");


dbn("update se_games set last_daily = '".time()."' where db_name = '$db_name'");

return 1;


#=======================================
# Functions
#=======================================


/**********************
* Retire   players.
**********************/
function retire_ood_users(){
	global $db_name, $final_str, $cw, $st;

	//select users who have been inactive in a game for a while.
	$time = time() - (14 * 86400);
	db("select clan_id, login_id, login_name from ${db_name}_users where login_id > 5 && joined_game < '$time' && last_request < '$time' && (banned_time < ".time()." || banned_time = 0)");

	while($users = dbr(1)){
		if ($users['clan_id'] > 0) { //user in a clan
			db2("select leader_id from ${db_name}_clans where clan_id = '$users[clan_id]'");
			$clan = dbr2(1);
			#if player is in clan, remove the clan.
			if ($clan['leader_id'] == $users['login_id']) {
				dbn("update ${db_name}_users set clan_id = 0 where clan_id = '$users[clan_id]'");
				dbn("update ${db_name}_planets set clan_id = -1 where clan_id = '$users[clan_id]'");
				dbn("delete from ${db_name}_clans where clan_id = '$users[clan_id]'");
			} else {
				dbn("update ${db_name}_planets set clan_id = -1 where login_id = '$users[login_id]'");
			}
		}

		dbn("delete from ${db_name}_ships where login_id = '$users[login_id]'");
		dbn("delete from ${db_name}_diary where login_id = '$users[login_id]'");

		insert_history($users['login_id'], "Was removed from $db_name after 14 days of in-activity.");

		dbn("delete from ${db_name}_user_options where login_id = '$users[login_id]'");
		dbn("delete from ${db_name}_users where login_id = '$users[login_id]'");

		$final_str .= "\n<br />$users[login_name] Removed";
		post_news("<b class=b1>$users[login_name]</b> " . $st[1831], "player_status");
	}
}

/**********************
* send mail to >7 days players.
**********************/
function mail_ood_users(){
	global $db_name, $final_str, $cw, $st;

	//get galaxy name
	db2("select name from se_games where db_name='${db_name}' LIMIT 1");
	$galaxy_name = dbr2(1);
	$galaxy_name = $galaxy_name['name'];
	//select users who have been inactive in a game for a while.
	$time = time() - (7 * 86400);
	$q = "select ${db_name}_users.login_id, ${db_name}_users.login_name,
	user_accounts.last_reminder,
	user_accounts.email_address
	from ${db_name}_users
	left join user_accounts on ${db_name}_users.login_id = user_accounts.login_id
	where ${db_name}_users.login_id > 5
	&& ${db_name}_users.joined_game < '$time'
	&& ${db_name}_users.last_request < '$time'
	&& (${db_name}_users.banned_time < ".time()." || ${db_name}_users.banned_time = 0)
	&& user_accounts.last_reminder = 0
	";
	db($q);
//	echo 'a ajuns';
//	var_dump($q);

	while($users = dbr(1)){
//		echo 'a intrat';
		if (filter_var($users['email_address'], FILTER_VALIDATE_EMAIL))
		{
			$contactname = $users['login_name'];
			$contactemail = $users['email_address'];
			include '../includes/email_templates/days7.php';
			$message = str_replace(array("{nickname}", "{galaxy_name}"), array($contactname, $galaxy_name), $message);
			//the line bellow is for testing only
			//$contactemail = 'jp.lannoy@nilsine.fr';

			$headers = "MIME-Version: 1.0\n";
			$headers .= "Content-type: text/plain; charset=iso-8859-1\n";
			$headers .= "X-Priority: 1\n";
			$headers .= "X-MSMail-Priority: High\n";
			$headers .= "X-Mailer: php\n";
			$headers .= "From: \"".SERVER_NAME."\" <info@astravires.fr>\n";
			mail("\"".$contactname."\" <".$contactemail.">", $subject, $message, $headers);

			dbn("update user_accounts set last_reminder = '".time()."' where login_id = '$users[login_id]'");
			$final_str .= "\n<br />$users[login_name] Reminded through email";
		}
	}
}

//randomly move the blackmarkets around
function move_blackmarkets (){
	global $db_name, $final_str, $num_stars, $st, $cw;

	db("select bmrkt_id from ${db_name}_bmrkt");

	#make it so as the game thinks theres a BM in sys 1 already.
	$bmrkt_check = array();

	#loop through bms players, and move them.
	while ($bm_id = dbr(1)){

		$check=0; //ensure only loop 5 times max

		//loop through until placed in unique system, or 5 passes have been made.
		do {
			$bmrkt_loc = mt_rand(2, $num_stars - 1);
			$check++;
		} while(!empty($bmrkt_check[$bmrkt_loc]) && $check < 5);

		dbn("update ${db_name}_bmrkt set location = '$bmrkt_loc' where bmrkt_id = '$bm_id[bmrkt_id]'");
		$bmrkt_check[$bmrkt_loc] = 1;
		$final_str .= "\n<br />Blackmarket $bm_id[bmrkt_id] moved to $bmrkt_loc\n";
	}
}


function regerate_minerals(){
	global $GAME_VARS, $db_name, $final_str, $num_stars, $cw, $st;

	db("select count(login_id) as nbj from ${db_name}_users");
	$data = dbr();
	$nbJoueurs = $data['nbj'];

	for($ct = 2;$ct <= $num_stars;$ct++) {
		if(mt_rand(0, 100) < $GAME_VARS['rr_chance']) {
			if (mt_rand(0, 1) == 0) {
				// metal
				$q = mt_rand(0, $GAME_VARS['rr_metal_chance_max'] * $nbJoueurs);
				dbn("update ${db_name}_stars set metal = metal + $q where star_id = '$ct'");
				$final_str .= "\n<br />#$ct gets $q more titane";
			} else {
				// fuel
				$q = mt_rand(0, $GAME_VARS['rr_fuel_chance_max'] * $nbJoueurs);
				dbn("update ${db_name}_stars set fuel = fuel + $q where star_id = '$ct'");
				$final_str .= "\n<br />#$ct gets $q more larium";
			}
		}
	}
}



