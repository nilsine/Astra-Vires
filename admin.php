<?php
/**************
* Contains admin controls for the game
* Last audited: 24/5/04 by Moriarty
***************/


require_once("user.inc.php");

if($user['login_id'] != 1 && $user['login_id'] != OWNER_ID) {
	print_page("Admin","Admin access only.");
	exit();
}

$rs = "<p /><a href='$_SERVER[PHP_SELF]'>Back to Admin Page</a>";

$out = "";

/*db("select login_id, id_parrain from user_accounts where signed_up < ".(time() - 20*86400)." and login_count > 10 and parrainage_actif = 0 and last_login > ".(time() - 24*3600));
while ($data = dbr()) {
	echo "update user_accounts set parrainage_actif=1 where login_id=".$data['login_id'];
	echo "<br />";
	echo "update user_accounts set gdt=gdt+1 where login_id=".$data['id_parrain'];
	dbn("update user_accounts set parrainage_actif=1 where login_id=".$data['login_id']);
	dbn("update user_accounts set gdt=gdt+1 where login_id=".$data['id_parrain']);
}*/

print mysql_error();



//save database vars
if(isset($_POST['save_vars'])) {
	foreach($_POST as $var => $value){
		if($var == 'save_vars') {
			continue;
		}
		#update the var, and be sure to only update it if the new range is in value. Otherwise leave it alone.
		dbn("update se_db_vars set ${db_name}_value = '".(int)$value."' where name = '".mysql_escape_string((string)$var)."' && '$value' >= min && '$value' <= max");
	}

	//save the changed variables to files.
	require_once("build_vars.php");
	insert_history($user['login_id'],"Updated Game Vars");


//update all player scores
} elseif(isset($_GET['update_scores'])){
	if($GAME_VARS['score_method'] == 0){
		$out .= "Scoring is presently turned off. Set the admin var to something other than 0 to turn it on.<br /><br />";
	} else {
		$resultat = mysql_query("select * from ${db_name}_users where login_id > 5");
		while ($data = mysql_fetch_array($resultat)) {
			score_func($data['login_id'], 0);
		}
		$out .= "Scores successfully updated.<br /><br />";
	}
	insert_history($user['login_id'],"Updated All Player Scores");


//give players money
} elseif(isset($_REQUEST['more_money'])){
	if(!isset($_POST['money_amount'])){
		get_var('Increase Money',$_SERVER['PHP_SELF'],"How much money do you want to give to each player?","money_amount",'');
	} elseif($_POST['money_amount'] < 1) {
		$out .= "You can't decrease the players money.<br /><br />";
	} else {
		$money_amount = (int)$_POST['money_amount'];
		$out .= "Player's money reserves increased by <b>$money_amount</b><br />Note: This has NOT sent a message to the players. That is your job.<br /><br />";
		dbn("update ${db_name}_users set cash = cash + '$money_amount' where login_id != 1");
		insert_history($user['login_id'],"Gave <b>$money_amount</b> credits to all players.");
	}


//news post
} elseif(isset($_GET['post_game_news']) && empty($_POST['text'])) {
	get_var('Post News',$_SERVER['PHP_SELF'],'What do you want to post in the News?','text','');
} elseif(isset($_POST['post_game_news'])) {
	$text = mysql_escape_string((string)$_POST['text']);
	$login_id = -1;
	post_news($text, "admin");
	$login_id = 1;
	$out = "News Posted.<p />";


//active user listing
} elseif(isset($_GET['show_active'])){
	$out = "Users that have logged with within the past 5 mins.";
	$out .= "<br />Time Loaded: ".date("H:i:s (M d)")."<br /><a href='admin.php?show_active=1'>Reload</a>";
	db("select last_request,login_name,login_id,clan_sym,clan_sym_color,clan_id from ${db_name}_users where last_request > ".(time()-300)." && login_id > 1 order by last_request desc");
	$player = dbr();
	if(!$player){
		$out .= "<p />There are no active users.";
	} else {
		$out .= "<p /><table>";
		$out .= "<tr bgcolor='#555555'><td>Login Name</td><td>Last Request</td></tr>";
		while ($player) {
		  $out .= "<tr bgcolor='#333333'><td>".print_name($player)."</td><td>".date( "H:i:s (M d)",$player['last_request'])."</td><td> - <a href='message.php?target_id=$player[login_id]'>Message</a><br /></td></tr>";
		  $player = dbr();
		}
		$out .= "</table>";
	}

	print_page("Active Users",$out);


//#admin sets difficulty
} elseif(isset($_REQUEST['difficulty'])){
	if(!isset($_POST['set_dif'])){
		$out = "This will have no effect upon the game itself, but will serve simply to inform new joiners to the game what to expect.<form action=$_SERVER[PHP_SELF] name=get_dif_form method=POST>
		<p /><input type='radio' name='set_dif' value='1' />Begginer
		<br /><input type='radio' name='set_dif' value='2' />Begginer -> Intermediate
		<br /><input type='radio' name='set_dif' value='3' />Intermediate
		<br /><input type='radio' name='set_dif' value='4' />Intermediate - > Advanced
		<br /><input type='radio' name='set_dif' value='5' />Advanced
		<br /><input type='radio' name='set_dif' value='6' />All Skill Levels
		<p /><input type='submit' /><input type='hidden' name='difficulty' value='1' /></form>";
		print_page("Select Difficulty",$out);
	} else {
		dbn("update se_games set difficulty = '".(int)$_POST['set_dif']."' where db_name = '$db_name'");
		$out .= "Stated Difficulty updated<p />";
		insert_history($user['login_id'],"Game difficulty changed.");
	}


//ban player from game.
} elseif(isset($_REQUEST['ban'])){
	if($_REQUEST['ban'] == 2){ #show ban a player page.
		$max_time = 168;
		if(!isset($_POST['ban_target']) || $_POST['ban_target'] < 1 || empty($_POST['ban_time'])){
			db("select login_name,login_id from ${db_name}_users where banned_time <= ".time()." && banned_time != -1 && login_id > 5 order by login_name");
			$out .= "Notes:<br />Number of hours you may ban a player for is limited to $max_time.<br />Setting a ban-time of -1 means the ban time will last until the game is reset.<br />You may reset a ban period at any time from this page.\n<FORM action='$_SERVER[PHP_SELF]' method='POST' name='ban_form'>";
			$out .= "\nSelect Player to Ban: <br /><br />";
			$out .= "<select name='ban_target'>";
			$out .= "<option value='0'>Select player... ";
			while($list_em = dbr(1)) {
				$out .= "<option value='$list_em[login_id]'>$list_em[login_name]";
			}
			$out .= "</select>";
			$out .= "<p /><br />Enter the number of hours you would like the player to be banned for:<br /><br /><input type='text' name='ban_time' size='3' /> hours";
			$out .= "<input type='hidden' name='ban' value='2' /><p /><br />Please give the reason you are banning this player.<p /><textarea name='ban_reason' cols='50' rows='5' wrap='soft'></textarea><br /><br /><input type='submit' value='Ban' /></form><p />";
		} elseif ($_POST['ban_target'] > 0){
			db("select login_name, login_id from ${db_name}_users where login_id = '".(int)$_POST['ban_target']."'");
			$ban_info = dbr(1);
			if($_POST['ban_time'] > $max_time || $_POST['ban_time'] < -1){
				$out = "Maximum period of time a player may be banned for is <b>$max_time</b> hours.<br />Or set to -1 to ban for the rest of the game.";
			} elseif(!isset($_POST['sure'])){
				$rs="";
				get_var('Ban Player',$_SERVER['PHP_SELF'],"Are you sure you want to ban <b class='b1'>$ban_info[login_name]</b> for <b>$_POST[ban_time]</b> hours?",'sure','yes');
			} else {
				insert_history($_POST['ban_target'],"Was Banned from the game for $_POST[ban_time] hours");
				if(empty($_POST['ban_reason'])){
					$ban_reason = "No Reason.";
				} else {
					$ban_reason = mysql_escape_string((string)$_POST['ban_reason']);
				}

				if($_POST['ban_time'] > 0){
					$ban_time = time() + round((int)$_POST['ban_time'] * 3600);
				} else {
					$ban_time = -1;
				}

				dbn("update ${db_name}_users set banned_time = '$ban_time', banned_reason = '$ban_reason' where login_id = '$ban_info[login_id]'");
				if($ban_time > 0){
					$time_text = date( "D jS M - H:i",$ban_time);
				} else {
					$time_text = "it resets";
				}
				post_news("<b class='b1'>$ban_info[login_name]</b> has been banned from the game until $time_text by the Admin. <br />The reason being:<br />$ban_reason", "player_status");
				$out = "<b class='b1'>$ban_info[login_name]</b> has been banned from the game until $time_text.<br /><br />";
			}
		}

	} elseif(isset($_REQUEST['unban'])){
		db("select login_name from ${db_name}_users where login_id = '".(int)$_REQUEST['unban']."'");
		$ban_info = dbr(1);
		insert_history($_REQUEST['unban'],"Was Un-Banned from the game");
		dbn("update ${db_name}_users set banned_time = '0', banned_reason = '' where login_id = '".(int)$_REQUEST['unban']."'");
		$out .= "<b class='b1'>$ban_info[login_name]</b> was un-banned.<br /><br />";
		post_news("<b class='b1'>$ban_info[login_name]</b> was un-banned by the Admin", "player_status");
	}

	#list players who are presently banned
	db("select login_name, login_id, banned_time, banned_reason from ${db_name}_users where banned_time = -1 || banned_time > ".time()." order by banned_time desc");
	$b_t1_out = "Listing Banned Players:";
	$b_t1_out .= make_table(array("Login Name","Banned until","Reason",""));
	while($list_banned = dbr()){
		if($list_banned[banned_time] != -1){
			$temp_343 = date( "D jS M - H:i",$list_banned[banned_time]);
		} else {
			$temp_343 = "End of Game";
		}
		$b_t_out .= make_row(array(print_name($list_banned),$temp_343,"$list_banned[banned_reason]","<a href='$_SERVER[PHP_SELF]?ban=1&amp;unban=$list_banned[login_id]'>Un-Ban</a>"));
	}

	$out .= "<a href='$_SERVER[PHP_SELF]?ban=2'>Ban a player</a><br /><br />";
	if(empty($b_t_out)){
		$out .= "<br /><br />No players presently banned.<br />";
	} else {
		$out .= $b_t1_out.$b_t_out."</table>";
	}
	print_page("Ban Player",$out);


//(un)pause
} elseif(isset($_GET['pause'])){
	if($_GET['pause'] == 1){
		$out = "Game Paused.<p />";
		dbn("update se_games set paused = '1' where db_name = '$db_name'");
		post_news("Game Paused", "game_status");
		insert_history($user['login_id'],"Paused Game.");
		$game_info['paused'] = 1;
	} elseif($_GET['pause'] == 2){
		post_news("Game Un-Paused", "game_status");
		$out = "Game Un-paused.<p />";
		dbn("update se_games set paused = '0' where db_name = '$db_name'");
		insert_history($user['login_id'],"Unpaused Game.");
		$game_info['paused'] = 0;
	}


//preview a universe
} elseif(isset($preview)){

	if(!extension_loaded("gd") && !extension_loaded("gd2")){
		$out .= "This server does not have the <b class='b1'>gd</b> module installed, therefore the maps cannot be generated, or previewed.";
	} else {
		$out .= "<script>
		function refresh(){
			var now = new Date();
			document.images.preview_uni_img.src = 'build_universe.php?preview=1&process=1&rand=' + now.getTime();
		}
		</script>";
		$out .= "<center><a href='javascript:refresh();'>Generate New Universe</a><br />\n<img name='preview_uni_img' src='build_universe.php?preview=1&process=1' border=1 alt='Please wait. Generating universe and loading image. This may take some time.' /> \n <br /><a href='javascript:refresh();'>Generate New Universe</a> \n </center>";

		$out .= "<p />The above image uses the following variables <b>only</b>.<p />uv_map_layout <br />uv_max_link_dist <br />uv_min_star_dist <br />uv_num_stars <br />uv_show_warp_numbers <br />uv_size_x_width <br />uv_size_y_height <br />uv_wormholes
		<p />Changing any of these variables will have some sort of effect on the image/universe generated.<br /><b class='b1'>Warning</b> - If you change <b>uv_size_x_width</b> or <b>uv_size_y_height</b>, during a game that has <b>uv_explored</b> set to 0, players may experience some very strange maps getting created. So be sure to set the universe size vars back to what they were when you have finished messing around if you're not about to create a new game.<p />There is no way to save the present universe and use it in a game. It is only an example of what can be created.
		<p />If no image appears, then there is a pretty big bug somewhere in the universe generation process. Report it to the Server Admin.";
	}
	print_header("Preview Universe");
	echo $out;
	print_footer("");

//reset signup times
} elseif(isset($_GET['reset_signup'])) {
	$out = "Signup times reset.<p />";
	dbn("update ${db_name}_users set joined_game = UNIX_TIMESTAMP() where login_id > 5");
	insert_history($user['login_id'],"Reset Signup Times.");


//reset game
} elseif(isset($_GET['reset'])){
	if($_GET['reset'] == 1){
		$out .= "Are you sure you want to reset the game?";
		$out .= "<center><a href='$_SERVER[PHP_SELF]?reset=2'>Yes</a>&nbsp&nbsp&nbsp&nbsp&nbsp<a href='$_SERVER[PHP_SELF]'>No</a></center>";
	} elseif($_GET['reset'] == 2) {
		if(OWNER_ID != 0){
			$owner_txt = " && login_id != ".OWNER_ID;
		} else {
			$owner_txt = "";
		}
		$out .= "Game reset started.<p />";

		dbn("delete from ${db_name}_users where login_id > 5");
		dbn("delete from ${db_name}_user_options where login_id > 5");
		$out .= "Users & their options deleted.<br />";

		dbn("update ${db_name}_users set turns='1000', turns_run='0', location='1', ship_id='1', cash='100000000', on_planet='0', last_attack='0', last_attack_by='', ships_killed='0', ships_lost=0, ships_lost_points=0, ships_killed_points=0, genesis='1', gamma='1', clan_sym='', clan_sym_color='', clan_id=0, fighters_killed='0', one_brob='0', alpha='1', last_request='0', score='0', tech='100000' where login_id=1");
		$out .= "Admin account refurbished.<br />";

		dbn("TRUNCATE TABLE ${db_name}_news");
		$out .= "News erased.<br />";

		dbn("TRUNCATE TABLE ${db_name}_planets");
		$out .= "Planets erased.<br />";

		dbn("delete from ${db_name}_messages where login_id != 1".$owner_txt);
		$out .= "Messages deleted.<br />";

		dbn("delete from ${db_name}_diary where login_id != 1".$owner_txt);
		$out .= "Diaries erased.<br />";

		dbn("delete from ${db_name}_ships");
		$out .= "Ships deleted.<br />";

		dbn("TRUNCATE TABLE ${db_name}_clans");
		$out .= "Clans deleted.<br />";

		dbn("TRUNCATE TABLE ${db_name}_bilkos");
		$out .= "Bilkos Auction House Emptied.<br />";

		if($GAME_VARS['alternate_play_2'] > 1){
			$dbh->do("update se_development_time set ${db_name}_available = 0 where year_set_${game_vars[alternate_play_2]} > 0");
			$dbh->do("update se_development_time set ${db_name}_available = 1 where year_set_${game_vars[alternate_play_2]} = 0");
			$out .= "Year reset to 0\n<br />";
		}

		dbn("update se_games set last_reset = ".time().", days_left = '$GAME_VARS[game_length]' where db_name = '$db_name'");
		$out .= "Last reset date updated to now.<br />";

		post_news("Game Reset.", "game_status");
	}
	insert_history($user['login_id'],"Galaxie remise à zéro");
	print_page("Reset Game",$out);


#list all planets in game
} elseif(isset($_GET['planet_list'])){

	db("select login_name,planet_name,location,fighters,colon,cash,metal,fuel,elect,mining_drones from ${db_name}_planets where location != 1 order by login_name asc, fighters desc, planet_name asc");

	$planet_listing = dbr(1);
	if(isset($planet_listing)) {
		$out .= $rs."<p />".make_table(array("Planet Owner", "Planet Name", "Location", "Fighters", "Colonists", "Cash", "Metal", "Fuel", "Electronics", "Mining Drones"));
		while($planet_listing) {
			$planet_listing['login_name'] = "<b class='b1'>$planet_listing[login_name]</b>";
			$out .= make_row($planet_listing);
			$planet_listing = dbr(1);
		}
		$out .= "</table>";
		print_page("Planet List",$out);
	} else {
		$out .= "There are no planets in the game.<p />";
	}


//give listing of all players in game to admin, and some of their details.
} elseif(isset($_GET['player_list'])){
	db("select login_id, cash, turns, turns_run, tech, game_login_count, genesis, one_brob, alpha, gamma, delta from ${db_name}_users where login_id > 5");

	while($player_info = dbr(1)){
		db2("select count(planet_id), sum(fighters), sum(cash), sum(tech), sum(colon), sum(mining_drones), sum(metal), sum(fuel), sum(elect) from ${db_name}_planets where login_id = '$player_info[login_id]'");
		$planet_info = dbr2(1);

		db2("select count(ship_id), sum(fighters), sum(shields), sum(armour), sum(cargo_bays), sum(metal), sum(fuel), sum(elect), sum(colon), sum(point_value), count(distinct location) from ${db_name}_ships where login_id = '$player_info[login_id]'");
		$ship_info = dbr2(1);


		$out .= "<center><hr width = '200'></center><table cellspacing='1' cellpadding='2' border='0' bgcolor='#111111'><tr><th align='left'>".print_name($player_info)."</th></tr><tr><td>";
		unset($player_info['login_id']);
		$player_info['one_brob'] = num_flagships($player_info['one_brob']);
		//player info
		$out .= make_table(array("Account Cash", "Turns", "Turns Run", "Account Tech", "Logins to game", "Genesis", "Flagship Count", "Alphas", "Gammas", "Delta"));
		$out .= make_row($player_info);

		//planet info
		$out .= "</table><br />Planets<br />".make_table(array("Planet Count", "Planet Fighters", "Planet Cash", "Tech", "Colonists", "Mining Drones", "Metal", "Fuel", "Electronics"));
		$out .= make_row($planet_info);

		//ship info
		$out .= "</table><br />Ships<br />".make_table(array("Ship<br />Count", "Ship<br />Fighters", "Shields", "Armour", "Cargo<br />Bays", "Metal", "Fuel", "Electronics", "Colonists", "Point<br />Value", "# Systems <br />with ships"));
		$out .= make_row($ship_info);

		$out .= "</table></td></tr></table>";
	}

	print_page("Player Info", $out);


//change intro message
} elseif(isset($_REQUEST['messag'])){
	if($_REQUEST['messag'] == 1){
		db("select intro_message from se_games where db_name = '$db_name'");
		$present_mess = dbr(1);
		$present_mess = stripslashes($present_mess[intro_message]);
		$out .= "Please enter a message that all new players will recieve when they join. <p />Notes: HTML is enabled. Message codes are not used.";
		$out .= "<form action='$_SERVER[PHP_SELF]' method='POST'>";
		$out .= "<input type='hidden' name='messag' value='2' />";
		$out .= "<textarea name='new_mess' cols='50' rows='20' wrap='soft'>$present_mess</textarea>";
		$out .= "<p /><input type='submit' value='Change' /></form>";
	} else {
		$new_mess = mysql_escape_string($new_mess);
		dbn("update se_games set intro_message = '$new_mess' where db_name = '$db_name'");
		$out .= "The Intro message has been changed.";
	}
	print_page("Change Intro Message",$out);
	insert_history($user['login_id'],"Intro Message Changed.");


//change admin email
} elseif(isset($_REQUEST['email'])){
	if($_REQUEST['email'] == 1){
		db("select admin_email from se_games where db_name = '$db_name'");
		$present_mail = dbr(1);
		$out .= "Please enter New Admin E-mail Address:";
		$out .= "<form action='$_SERVER[PHP_SELF]' method='POST>";
		$out .= "<input type='hidden' name='email' value='2' />";
		$out .= "<input type='text' name='new_mail' value='$present_mail[admin_email]' size='30' />";
		$out .= "<p /><input type='submit' value='Change' /></form>";
	} else {
		if(!ereg("@",$new_mail) || !ereg("\.",$new_mail)){
			 print_page("Admin Mail","Please Enter a Valid Email Address");
		}

		dbn("update se_games set admin_email = '".mysql_escape_string((string)$new_mail)."' where db_name = '$db_name'");
		$out .= "Admins Email Address has been changed to: <br /><b>$new_mail</b>.";
	}
	print_page("Change Admin Mail",$out);
	insert_history($user['login_id'],"Admin E-mail Addy changed.");


//change admin name
} elseif(isset($_REQUEST['admin_name'])){
	if($_REQUEST['admin_name'] == 1){
		db("select admin_name from se_games where db_name = '$db_name'");
		$admin_name = dbr(1);
		$out .= "Please enter New Admin's Name:";
		$out .= "<form action='$_SERVER[PHP_SELF]' method='POST'>";
		$out .= "<input type='hidden' name=admin_name value='2' />";
		$out .= "<input type='text' name='new_name' value='$admin_name[admin_name]' size='30' />";
		$out .= "<p /><input type='submit' value='Change' /></form>";
	} else {
		dbn("update se_games set admin_name = '".mysql_escape_string((string)$new_name)."' where db_name = '$db_name'");
		$out .= "Admin's Name has been changed to: <br /><b>$new_name</b>.";
	}
	print_page("Change Admin Mail",$out);
	insert_history($user['login_id'],"Admin Name Changed.");


//change game description
} elseif(isset($_REQUEST['descr'])){
	if($_REQUEST['descr'] == 1){
		db("select description from se_games where db_name = '$db_name'");
		$present_desc = dbr(1);
		$out .= "Please enter some words that explain the game.<p />Note: HTML is enabled, but this does not use the message codes. <br />(Leave empty if you don't want to use it)";
		$out .= "<form action='$_SERVER[PHP_SELF]' method='POST'>";
		$out .= "<input type='hidden' name='descr' value='2' />";
		$out .= "<textarea name='new_descr' cols='50' rows='20' wrap='soft'>$present_desc[description]</textarea>";
		$out .= "<p /><input type='submit' value='Change' /></form>";
	} else {
		$new_descr = stripslashes($new_descr);
		$new_descr = mysql_escape_string($new_descr);
		dbn("update se_games set description = '$new_descr' where db_name = '$db_name'");
		$out .= "The description of the game has been changed.";
	}
	print_page("Change Description",$out);
	insert_history($user['login_id'],"Game description changed.");


//change ships available to players.
} elseif(isset($_REQUEST['admin_choose'])){

	if(isset($_POST['add_ship'])){

		db("select type_id from se_ship_types where type_id > 2 && auction = 0");
		while($list_ships = dbr(1)){

			if(!isset($add_ship[$list_ships['type_id']])){//admin has turned ship off.
				dbn("update se_admin_ships set ${db_name}_ship_status = 0 where ship_type_id = '$list_ships[type_id]'");
			} else {//ship is turned on.
				dbn("update se_admin_ships set ${db_name}_ship_status = 1 where ship_type_id = '$list_ships[type_id]'");
			}
		}
		$out .= "Selected ships have now been made available to the players. The rest of the ships are un-available.";

	} else {

		$out .= "Select the ships that you would like users to be able to use within the game:";
		$out .= "\n<form name='select_ships' action='$_SERVER[PHP_SELF]' method='POST'>";
		$out .= "\n<input type='hidden' name='admin_choose' value='1' />";

		$out .= make_table(array("<b class='b1'>Ship Name</b>","Available"));
		db("select s.type_id,s.name,a.{$db_name}_ship_status as status from se_ship_types s, se_admin_ships a where s.type_id > 2 && a.ship_type_id = s.type_id && s.auction = 0");
		while($list_ships = dbr(1)){
			if($list_ships['status'] == 1){
				$out .= "\n".quick_row("$list_ships[name]","<input type='checkbox' name='add_ship[$list_ships[type_id]]' value='$list_ships[type_id]' checked='checked' />");
			} else {
				$out .= "\n".quick_row("$list_ships[name]","<input type='checkbox' name='add_ship[$list_ships[type_id]]' value='$list_ships[type_id]' />");
			}
		}
		$out .= "\n</table>";
		$out .= "\n<br /><a href='javascript:TickAll(\"select_ships\")'>Invert Ship Selection</a>";
		$out .= "\n<p /><input type='submit' value='Submit' /></form>";
	}
	print_page("Ship Types",$out);
	insert_history($user['login_id'],"Ships available in game changed.");
}


#list all admin options
db("select paused from se_games where db_name = '$db_name'");
$paused = dbr(1);

$out .= "Game Functions:<br />";
if(!$paused['paused']){
	$out .= "<a href='$_SERVER[PHP_SELF]?pause=1'>Pause Game</a><br />";
} else {
	$out .= "<a href='$_SERVER[PHP_SELF]?pause=2'>Un-Pause Game</a><br />";
}
$out .= "<a href='$_SERVER[PHP_SELF]?reset=1'>Reset Game</a><br />";
$out .= "<a href='$_SERVER[PHP_SELF]?reset_signup=1'>Reset Signup Times</a><br />";
$out .= "<a href='$_SERVER[PHP_SELF]?admin_choose=1'>Edit Ship Types</a><br />";
$out .= "<a href='$_SERVER[PHP_SELF]?difficulty=1'>Change Stated Difficulty</a><p />";

$out .= "Godlike Abilities:<br />";
$out .= "<a href='build_universe.php?build_universe=1&amp;process=1'>Generate New Universe</a><br />";
$out .= "<a href='$_SERVER[PHP_SELF]?preview=1' target='_blank'>Preview Universe</a><p />";

$out .= "Render New Maps<br /><a href='build_universe.php?gen_new_maps=1&amp;process=1'>Global Map Only</a><br /><a href='build_universe.php?gen_new_maps=1&process=1&all=1'>All Maps</a><p />";

$out .= "Communcations:<br />";
$out .= "<a href='message.php?target_id=-4'>Message <b>All</b> Players</a><br />";
$out .= "<a href='$_SERVER[PHP_SELF]?post_game_news=1'>Post News</a><p />";

$out .= "Game information<br />";
$out .= "<a href='admin.php?show_active=1'>List Online Players</a><br />";
$out .= "<a href='$_SERVER[PHP_SELF]?planet_list=1'>List All Planets</a><br />";
$out .= "<a href='$_SERVER[PHP_SELF]?player_list=1'>List All Players</a><br />";
$out .= "<a href='graph.php' target='_blank'>Game Graphs</a><p />";

$out .= "Players:<br />";
$out .= "<a href='multiscan.php'>Multi scan</a><br />";
$out .= "<a href='$_SERVER[PHP_SELF]?ban=1'>Ban Player</a><br />";
$out .= "<a href='$_SERVER[PHP_SELF]?update_scores=1'>Update Scores</a><br />";
$out .= "<a href='$_SERVER[PHP_SELF]?more_money=1'>Give Money</a><p />";

$out .= "General:<br />";
$out .= "<a href='$_SERVER[PHP_SELF]?admin_name=1'>Change Admin Name</a><br />";
$out .= "<a href='$_SERVER[PHP_SELF]?email=1'>Change Admin E-mail</a><br />";
$out .= "<a href='$_SERVER[PHP_SELF]?descr=1'>Change Game Description</a><br />";
$out .= "<a href='$_SERVER[PHP_SELF]?messag=1'>Change Intro Message</a><br />";
$out .= "<a href='$directories[includes]/other_admin.php?admin_readme=1'>Admin Readme!</a><br />";

$out .= "<form action='$_SERVER[PHP_SELF]' name='get_var_form' method='post'>";

$out .= "<input type='hidden' name='save_vars' value='1' />";

$out .= "<input type='submit' value='Submit Vars' />";
$out .= "<p />Note: Only variables that are within range will be saved.";


//load the vars that are in the DB for this. File vars may be outdated.
load_admin_vars();

db2("select name, min, max, description from se_db_vars order by name");
$out .= list_options(1, $GAME_VARS);

$db_var = dbr(1);
while($db_var) {
	if($db_var['value'] < $db_var['min'] || $db_var['value'] > $db_var['max']){//error checking
		$db_var['value'] = $db_var['default_value'];
	}
	$out .= "<p /><table border=2 cellspacing=1 width=350><tr bgcolor='#333333' width='350'><td width='250'><b><font color='#AAAAEE'>$db_var[name]</font></b> ( $db_var[min] .. $db_var[max] )</td><td align='right' width='100'><input type='text' name='$db_var[name]' value='$db_var[value]' size='8' /></td></tr><tr bgcolor='#555555' width='350'><td colspan='2' width='350'><blockquote>$db_var[descript] <p />Server Default: <b>$db_var[default_value]</b></blockquote></td></tr></table><br />";

	$db_var = dbr(1);
}

$out .= "<p /><input type='submit' value='Submit Vars' />";
$out .= "<br /></form>";
$rs = "<p /><a href='location.php'>Back to Star System</a>";
print_page("Admin",$out);
?>
