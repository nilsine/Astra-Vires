<?php
require_once("common.inc.php");
require_once('includes/session_funcs.inc.php');

//Connect to the database
db_connect();
if ($fbuser && !$_POST['associate']){
//facebook login
	db("select login_name from user_accounts where fb_user_id='".mysql_real_escape_string(stripslashes($fbuser->id))."'");
	$data = dbr();
	login_to_server($data['login_name'], '', 0, FALSE, $fbuser->id);
} elseif ($fbuser && $_POST['associate'] && isset($_POST['submit'])){
//facebook associate with classic and login
	$login_name = mysql_escape_string(($pseudo) ? $pseudo:(string)$_POST['pseudo']);
	db("select * from user_accounts where login_name = '$login_name'");
	$data = dbr(1);
	$enc_pass = md5($_POST['mdp']);
	if (empty($data)) { //incorrect username
		print_header($cw['login_problem']);
		echo "<blockquote>".sprintf($st[1816], $login_name)."<br />
		".$st[1817]."<p />
		<p /> <a href='inscription.php'>
		".$cw['sign_up2']."</a> <p /> <a href=\"".URL_PREFIX."/index.php\">".$st[1818]."</a></b></blockquote>";
		print_footer();

	} elseif(($enc_pass != $data['passwd'])) { //incorrect password
		print_header($cw['bad_passwd']);
		echo "<blockquote><b>".$st[1819]."<br />".$st[1820]."
		<p /><a href=\"javascript:history.back()\">".$st[1818]."</a></b><p />".$st[789]." ? <a href=change_pass.php?stage_one=1>".$cw['click_here']."</a></blockquote><p />";
		insert_history($p_user['login_id'], $cw['bad_login']);
		print_footer();
	} else{ //everthing correct ...undate fb_user_id and do login
		dbn("update user_accounts set fb_user_id='".(int)$fbuser->id."' where login_id = '$data[login_id]'");

		//post on facebook wall
		fb_wallpost_wosdk_api('Astra Vires', "{*actor*} utilise maintenant Facebook pour se connecter à l'univers d'Astra Vires");

		login_to_server($data['login_name'], '', 0, FALSE, $fbuser->id);
	}
} elseif ($_GET['sid']) {
	db("select login_name, bp_user_id from user_accounts where session_id='".mysql_real_escape_string(stripslashes($_GET['sid']))."'");
	$data = dbr();
	login_to_server($data['login_name'], '', $data['bp_user_id']);

} elseif(empty($_COOKIE['session_id']) || empty($_COOKIE['login_id']) || isset($_POST['submit'])){

	login_to_server();

//user already logged in. but check session details.
} else {

	check_auth();

	if($login_id == 1) { //admin trying to continue old session.
		echo $st[793];
		exit();
	}
}

if ($fbuser && empty($p_user['fb_token']))
{// verify if the user has fb_token and set it
	$tmp_fb_token = get_facebook_cookie();
	dbn("update user_accounts set fb_token='".$tmp_fb_token['access_token']."' WHERE login_id = '$p_user[login_id]'");
}

$rs = "<br /><br />".$st[794];


//print_header("Game Listings");

$nomPage = 'game_listing';

require('includes/haut_index.inc.php');

//user has selected a game.
if(isset($_REQUEST['game_selected'])){


	db("select db_name from se_games where game_id = '".(int)$_REQUEST['game_selected']."'");
	$game_db = dbr(1);
	$db_name = $game_db['db_name'];

	//see if the user is already in the game
	db("select game_login_count, banned_time, banned_reason from ${db_name}_users where login_id = '$login_id'");
	$in_game = dbr(1);

	//user logging into selected game. update the db, and redirect to location.php
	if(!empty($in_game)){

		//see if user is banned from the selected game
		if(($in_game['banned_time'] > time() || $in_game['banned_time'] == -1) && $login_id > 5){
			print_header("Banned");
			if($in_game['banned_time'] > time()){
				echo sprintf($st[795], date( "D jS M - H:i",$in_game['banned_time']), stripslashes($in_game['banned_reason']));
			} elseif($in_game['banned_time'] < 0){
				echo $st[796].":<br /><br />".stripslashes($in_game['banned_reason']);
			}
			print_footer();
			exit();
		}

		//not banned from game, so may continue.
		insert_history($login_id,"Logged into $db_name");

		//Update score
		score_func($login_id,0);

		//set the user in the game, and increase login count by 1.
		dbn("update ${db_name}_users set game_login_count = game_login_count + 1 where login_id = '$login_id'");
		dbn("update user_accounts set in_game = '$db_name' where login_id = '$login_id'");

//		header("Location: ".URL_PREFIX."/location.php");
		echo "<script>self.location='location.php';</script>";
		exit();


	//user joining selected game
	} else {

		db("select count(login_id) from ${db_name}_users where login_id = '$login_id'");
		$check_count = dbr();

		db("select * from se_games where db_name = '$db_name'");
		$game_info = dbr(1);

		//get the vars for the game
		require_once("$directories[includes]/${db_name}_vars.inc.php");

		//determine when retired if retired, and that isn't rejoining within illicit time.
		if($GAME_VARS['retire_period'] != 0){
			$time_starts = time() - ($GAME_VARS['retire_period'] * 3600);

			//if player retired just before reset, let player join the new game.
			if($game_info['last_reset'] > $time_starts){
				$time_starts = $game_info['last_reset'];
			}
			db("select timestamp from user_history where game_db = '$db_name' && login_id = $p_user[login_id] && timestamp > '$time_starts' && action = 'Retired From Game' order by timestamp desc limit 1");
			$candidate = dbr(1);
		}
		if($check_count[0] >= $GAME_VARS['max_players'] && $login_id != OWNER_ID){ //game full check
			print_header($cw['game_full']);
			echo "<b class='b1'>$game_info[name]</b> $st[797]";
			print_footer();
		} elseif($GAME_VARS['new_logins'] == 0 || $GAME_VARS['sudden_death'] == 1 && $login_id != OWNER_ID){ //game allowing signups check
			print_header("No Entry");
			echo "$st[798] (<b class='b1'>$game_info[name]</b>). $st[799]";
			print_footer();
		} elseif(isset($candidate['timestamp']) && $login_id != OWNER_ID){ //allowed to re-join check
			$result = date( "M d - H:i",$candidate['timestamp'] + ($GAME_VARS['retire_period'] * 3600));
			print_header($st[800]);
			echo sprintf($st[801], $GAME_VARS['retire_period'], $result);
			print_footer();
		} elseif(!isset($_POST['in_game_name'])) { //fine to join
			?>
			<div id="accueilColonne1">
				<p>Pour rejoindre cette galaxie veuillez saisir le pseudo que vous désirez utiliser ainsi que le nom de votre premier vaisseau.</p>
			</div>

			<div id="accueilColonne2">
			<?php
//			echo $st[802]." (<b class='b1'>$game_info[name]</b>).<br />".$st[803];
			echo "
			      <script type='text/javascript'>

					$(document).ready(function() {
					   $('#generate_button').click(function() {
					   		$.get('ajax.php',
							   { cmd: 'generateVesselName' },
							   function(data) {
									$('#ship_name').val(data);
							});
						});
					});

				  </script>";
			echo "<form name='form_user_name' action='$_SERVER[PHP_SELF]' method='POST'>";
			echo "<input type='hidden' name='game_selected' value='$_GET[game_selected]' />";
			echo "<p>Pseudo:<br /><input name='in_game_name' value='$p_user[login_name]' size='20' /></p>";
			echo "<p>".$st[804].":<br />";
			echo "<input name=ship_name id='ship_name' size='15' />&nbsp;<input type='button' value='G&eacute;n&eacute;rer' id='generate_button'/></p>";
			echo "<p><input type='submit' value='Rejoindre la galaxie' /></p></form>";
			echo "<script> document.form_user_name.in_game_name.focus(); </script>";

		} else { //confirming details, then adding to game.

			//validate proposed username
			$in_game_name = trim((string)$_POST['in_game_name']);
			if((strcmp($in_game_name,htmlspecialchars($in_game_name))) || (strlen($in_game_name) < 3) || (eregi("[^a-z0-9~!@#$%&*_+-=£§¥²³µ¶Þ×€ƒ™ ]",$in_game_name))) {
				print_header("New Account - $game_info[name]");
				echo $st[805];
				echo "<p /><a href='javascript:history.back()'>".$st[806]."</a>";
				print_footer();
			}

			#determine if that username is already in user by another player in the game, or another player as a server name.
			db("select pu.login_name, u.login_name as alternate_name from ${db_name}_users u, user_accounts pu where u.login_id != '$p_user[login_id]' && pu.login_id != '$p_user[login_id]' && (u.login_name = '$in_game_name' || pu.login_name = '$in_game_name')");
			$test_name = dbr(1);
			if($test_name['login_name'] || $test_name['alternate_name']){
				print_header($cw['choose_username']);
				echo $st[807];
				$rs = "<br /><br /><a href='javascript:history.back()'>".$st[808]."</a>";
				print_footer();
			}

			$show_sigs = 1;


			$ship_id = give_first_ship($p_user['login_id'], 0, $_POST['ship_name']);

			//create user account within game
			dbn("insert into ${db_name}_users (login_id, login_name, joined_game, turns, cash, explored_sys, ship_id, location, tech) VALUES ('$p_user[login_id]', '$in_game_name', '".time()."', '$GAME_VARS[start_turns]', '$GAME_VARS[start_cash]', '1', '$ship_id', '1', '$GAME_VARS[start_tech]')");

			//insert user options
			dbn("insert ${db_name}_user_options (login_id, show_sigs, color_scheme) VALUES('$p_user[login_id]','$show_sigs', '$p_user[default_color_scheme]')");

			//send the intro message (if there is one to send).
			if(!empty($game_info['intro_message'])){
				$game_info['intro_message'] = nl2br($game_name['intro_message']);
				dbn("insert into ${db_name}_messages (sender_id,sender_name,text,login_id,timestamp) values ('1','Admin','$game_name[intro_message]','$p_user[login_id]','".time()."')");
			}

			insert_history($login_id, $cw['joined_game']);
			post_news("<b class='b1'>$in_game_name</b> ".$st[809], "player_status");

			//update user game counter, and in-game status
			dbn("update user_accounts set num_games_joined = num_games_joined + 1, in_game = '$db_name' where login_id = '$p_user[login_id]'");

			echo "<script>self.location='location.php';</script>";
			exit();
		}//end join process
	}


//list games
} else {

	#get tip of the day
	/*db("select tip_content from daily_tips dt,  se_central_table ct where dt.tip_id = ct.todays_tip");
	$tip_today = dbr(1);*/

	//create a table at the top of the page that contains the game logo and the tips.
	/*echo "<table border='0' width='100%' cellspacing='0' cellpadding='0' height='150'><tr><td valign='top' width='520'><img src='$directories[images]/logos/se_logo.jpg' border='0' /></td>";

	//Create cell that has tip in it
	echo "<td width='250'>";

	//work out the timezone
	$p_time = date("Z");
	$time_temp = "";

	//just in case the user doesn't know the time difference with gmt, make it clear.
	if($p_time > 0){
		$time_temp = "<br />(UTC/GMT +<b>".($p_time / 3600)."</b> hr(s))";
	} elseif($p_time < 0) {
		$time_temp = "<br />(UTC/GMT -<b>".($p_time / 3600)."</b> hr(s))";
	}
	//give timezone data
	echo "<center>Server Timezone: <b class=b1>".date("T")."</b>$time_temp</center>";

	//tip is in it's own table
	echo "<br /><center><table height='75' width='250' border='1' bgcolor='#555555'><tr><td valign='top'>";

	//the tip itself.
	echo "<center><b>Tip of the day</b><br />$tip_today[tip_content]</center>";

	//close the tip table, and the top table
	echo "</td></tr></table></center></td><td width=100></td></tr></table>";

	//create the table that is to have the main content of the page in it
	echo "<table border='0' width='100%' cellspacing='0' cellpadding='0'><tr><td width='450' valign='top'><br /><br /><br />";

	//list games
	echo "<b>Game Listings</b> for <b class='b1'>$p_user[login_name]</b><br /><br />";
	echo "To login or join a game, click it's name below<br /><br />";
	echo "<table border='1' cellspacing='0' cellpadding='20'>";
	echo "<tr><td valign='top' width='300' bgcolor='#555555'>";
	echo "List of games presently running on this server:<br /><br />";
	*/

	// check of the user is in holiday mode
	$show_holiday_form = true;
	$show_galaxies = true;
	$holiday_msg = "<b>Mode vacances</b><br />Si vous passez en mode vacance vous ne pourrez plus gérer votre compte, vos productions seront arretées et les autres joueurs ne pourront vous attaquer.";
	if ( checkHolidayMode() ) {
		$mode = "Désactiver le mode vacances";
		$mode_val = 0;
		$show_galaxies = false;
		$holiday_msg = "Vous êtes en mode vacances, pour revenir en mode normal appuyez sur ce bouton";
	} else {
		$mode = "Activer le mode vacances";
		$mode_val = 1;
		if ( !checkHoliday24h() ) {
			$show_holiday_form = false;
		}
	}

	/*
	if ( isset( $_POST['mode'] ) ) {
		setHolidayMode( (int)$_POST['mode'] );
		echo "<script>self.location='game_listing.php';</script>";
	}
	*/

	?>

	<div id="accueilColonne1">
		<p>Pour rejoindre une galaxie cliquez simplement sur son nom à droite en dessous de « Galaxies à rejoindre »</p>
		<p>Si vous êtes déjà dans une galaxie cliquez sur son nom pour y retourner.</p><br />
        <?php
        /*
		if ( $show_holiday_form )
		{
			echo '<br />';
			echo '<form method="post" action="">
					  <input type="hidden" name="mode" value="'.$mode_val.'" />
					  <p>'.$holiday_msg.'</p><br /><input type="submit" value="'.$mode.'" />
				  </form>';
		}
		*/
        if (!$show_galaxies)
        	echo '<p>Vous êtes en mode vacances, pour revenir en mode normal allez au menu <a href="user_extra.php">compte & parrainage</a>.</p>';
		?>
	</div>

	<div id="accueilColonne2" style="width: 400px;">
	<?php

	$alpha_text = "";
	$beta_text = "";

		if (!$p_user['ban'] && $show_galaxies) {

	//cycle through the games that are running.
	db2("select name, db_name, paused, game_id, days_left, description from se_games where status = '1' order by last_reset asc");
	while ($game_stat = dbr2(1)){

		$stat = " - ".popup_help("game_info.php?db_name=$game_stat[db_name]", 600, 450);

		if($game_stat['paused'] == 1){
			$stat .= " - (".$cw['paused'].")";
		}
		db("select ${game_stat['db_name']}_value as value from se_db_vars where name = 'sudden_death'");
		$sd = dbr(1);
		if($sd['value'] == 1){
			$stat .= " - (".$cw['sudden_death'].")";
		}
		db("select ${game_stat['db_name']}_value as value from se_db_vars where name = 'new_logins'");
		$sd = dbr(1);
		if($sd['value'] == 0){
			$stat .= " - (".$st[810].")";
		}

		db("select ${game_stat['db_name']}_value as value from se_db_vars where name = 'game_length'");
		$sd = dbr(1);
		$game_length = $sd['value'];

		db("select count(login_id) from ${game_stat['db_name']}_users where ship_id > 1 && login_id > 5");
		$nb_joueurs = dbr();

		db("select login_id from ${game_stat['db_name']}_users where login_id = '$p_user[login_id]'");

		if($in_game = dbr(1)) { //player already in that game
			$alpha_text .=  "<h2><a href='$_SERVER[PHP_SELF]?game_selected=$game_stat[game_id]'>$game_stat[name]</a></h2>".$nb_joueurs[0]." joueurs - ".$game_stat['days_left']." jours restants sur $game_length<br /><p>« " . $game_stat['description'] . " »</p>";
		} else { //player not in that game.
			$beta_text .= "<h3><a href='$_SERVER[PHP_SELF]?game_selected=$game_stat[game_id]'>$game_stat[name]</a></h3>".$nb_joueurs[0]." joueurs - ".$game_stat['days_left']." jours restants sur $game_length<br /><p>« " . $game_stat['description'] . " »</p>";
		}
	}

	echo "<h1>" . $cw['games_joined']."</h1>";
	if(!empty($alpha_text)){
		echo $alpha_text;
	} else {
		echo "<b>".$cw['nonee']."</b><br />";
	}
    
    echo '<hr>';

	echo "<h1>".$cw['unjoined_games']."</h1>";
	if(!empty($beta_text)){
		echo $beta_text;
	} else {
		echo "<b>".$cw['nonee']."</b><br />";
	}
	//echo "</tr></td></table>";

//	echo "<br /><a href='bugs_submit.php'>".$cw['report_a_bug']."</a>\n";
	/*echo "<br /> - <a href='bugs_tracker.php'>Bug Tracking</a><br />\n";
	echo "<br /> - <a href='http://se.cornerjukebox.com/forum/' target='_blank'>Global Forums</a>";*/

	if($p_user['login_id'] != 1){
//		echo "<br><a href='logout.php?logout_game_listing=1'>".$cw['complete_logout']."</a>";
	}

	$admin_req_str = "";

	//echo "<td>$admin_req_str<br /><br /><B>Server News</b><br /><br />";

	//require_once("$directories[includes]/server_news.inc.htm");


	/*echo "</td></tr></table><br /><br /><br /><center>
	<p /><a href='credits.php' target='_blank'>Credits</a> - <a href='old_news.php' target='_blank'>Old News</a>
	<br /><br /><a href='http://www.solarempire.com/' target='_blank'><img src='$directories[images]/logos/se_small.gif' border='0' /></a><br />";*/
	$rs = "";

	} elseif ( $p_user['ban'] ) {
	    echo "<p>Votre compte a été banni pour la raison suivante :<br /><br /><b>" . $p_user['raison'] . "</b><br /><br />Pour toute réclamation vous pouvez envoyer un e-mail à info@astravires.fr</p>";
	}

}


db("select login_count from user_accounts where login_id=".$p_user['login_id']);
$res = dbr(1);
if ($res['login_count'] == 2) $nom_page_analytics = '2e_login';
?>
	</div>

	<div class="spacer"></div>

</div>
<?php
//print_footer($nom_page_analytics);

include('includes/bas_index.inc.php');

?>
