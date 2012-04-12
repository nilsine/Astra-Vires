<?php
/*****************
* Script that contains user-functions that are required to play the game.
*
*Last audited: 23/5/04 by Moriarty
*****************/


//call the other generic includes file
require_once("common.inc.php");

require_once("includes/langage_en.inc.php");
//require_once("includes/langage_fr.inc.php");


/**********************
Script initialisation
**********************/

//Connect to the database
db_connect();

// check and update the authentication.
check_auth();

//get game info if not admin (loaded for admin in check_auth)
if($login_id != 1){
	//get the game information
	db("select * from se_games where db_name = '$db_name'");
	$game_info = dbr(1);
}

//ensure game actually exists
if(empty($game_info['db_name'])){
	exit ("No such database.");
}

db("select * from ${db_name}_users where login_id = '$login_id'");
$user = dbr(1);

db("select * from ${db_name}_user_options where login_id = '$login_id'");
$user_options = dbr(1);

//update last request (so as know when user last requested a page in THIS game).
dbn("update ${db_name}_users set last_request = ".time()." where login_id = '$user[login_id]'");
dbn("update user_accounts set last_request = ".time()." where login_id = '$user[login_id]'");

//array used to store usernames (significantly decreases processing time for large star systems)
$PRINT_NAME_CACHE = array();

//load GAME_VARS array.
require_once("$directories[includes]/${db_name}_vars.inc.php"); //load from file
//load_admin_vars(); //load from DB


//load the ship present usership
$user_ship = array();
get_user_ship($user['ship_id']);

//generic link to go back to the start system
$rs = "<p /><a href='location.php'>".$cw['Back to the Star System']."</a><br />";

//set help topic linking string.
$status_bar_help = "";

$row_counter=0;

// nombre de jours de message sur les forum
$last_time = time() - 30 * 86400;



/****************************************************************
						FUNCTIONS BEGIN
****************************************************************/



/********************
Page printing functions
*********************/


//function that calls the page printing functions
//outputs to the browser, then terminates the script
function print_page($title, $text, $header='') {
	global $p_user;

	/*if ($p_user['bp_user_id']) {
		$bpApi = new TheGame();
		preg_match('`/([^/]*)$`Uis', $_SERVER['PHP_SELF'], $res);
		$pageTag = $bpApi->getPageTags($p_user['bp_user_id'], $p_user['login_id'], $res[1]);
		var_dump($pageTag);
	}*/

	print_header($title, $header);

	print_leftbar();
	print_topbar();
	echo $text;

	print_footer();
}


//function that creates the left bar in the game.
//this function initiates table creation for the page layout
//it assumes the other two bar's are going to be implemented).
function print_leftbar(){
	global $user, $db_name, $game_info, $user_ship, $GAME_VARS, $p_user, $cw, $st;

	//create the major table for the WHOLE PAGE
	$str = "\n<table border='0' cellspacing='0' cellpadding='0' width='100%'>";

	//whole page fills 1 "row".
	$str .= "\n<tr width='100%'>";

	//create "cell" for left bar. width stipulates width of this bar.
	//uses class "leftbar" from the stylesheets to decide on it's width
	$str .= "\n<td class='leftbar' align='left' valign='top'>";

	/*************** Content *************/

	$str .= '<img src="images/logos/logo_petit.png" /><br />';

	//top box with game details in it
	$str .= make_table2(array(date("d M - H:i")), "l_col");
	$str .= q_row(popup_help("game_info.php?db_name=$db_name", 600, 450, $game_info['name']), "l_col");

	if($game_info['paused'] == 1){
		$str .= q_row($cw['game_paused'], "l_col");
	} else {
		$str .= q_row("<b>$game_info[days_left]</b> ".$cw['days_left'], "l_col");
	}

	//active user count details
	$end_lnk = "";
	$cnt_link_str = "";
	if($user['login_id'] == 1 || $user['login_id'] == OWNER_ID) {
		$cnt_link_str .=  "<a href='admin.php?show_active=1'>";
		$end_lnk = "</a>";
	}


	db("select count(login_id) from ${db_name}_users where login_id > 1 && last_request > ".(time()-300));
	$lr_result = dbr();
	$str .= q_row($cnt_link_str."<b>$lr_result[0]</b> ".$cw['active_users']."$end_lnk</b>", "l_col");
	$str .= "</table><p />";
	//end top box with game details

	$str .= make_table2(array(stripslashes(print_name($user))), "l_col");

	$str .= q_row("<b>".number_format($user['turns'])."</b> ".strtolower($cw['turns']) . " sur <b>".number_format($GAME_VARS['max_turns'])."</b> max<br /><a href='buy.php' id='plusCycles'>Plus de cycles</a>", "l_col");

	//newbie safety, or admin link
	if($user['login_id'] == 1 || $user['login_id'] == OWNER_ID) { //cheating and using this space for admin links
		$str .= "\n<td><a href='admin.php'>".$cw['admin']."</a>";
		if($user['login_id'] == OWNER_ID) { //server admin link
			$str .= "\n<br /><a href='developer.php'>".$cw['server_admin']."</a>";
		}
		$str .= "</td>";
	/*} elseif($user['turns_run'] > $GAME_VARS['turns_safe']){
		$str .= "\n<td><a href='buy.php' style='color:#FF0000;'>".$cw['Get more turns']."</a></td>";*/
	} elseif($user['turns_run'] < $GAME_VARS['turns_safe']){
		$s_turns = $GAME_VARS['turns_safe'] - $user['turns_run'];
		$str .= q_row("<b>$s_turns</b> <b class='b1'>".$cw['safe_turns_left']."</b>", "l_col");
	} elseif($user['turns_run'] <= $GAME_VARS['turns_safe']) {
		$str .= q_row("<b class='b1'>".$cw['leaving_newbie_safety']."</b>", "l_col");
		charge_turns(1);
		send_message($user['login_id'], $st[7]);
	}

	if (!$p_user['bp_user_id']) $plusCubits = "<br /><a href='buy_cubits.php' id='plusCycles'>Plus de cubits</a>";
	$str .= q_row("<b>".number_format($user['cash'], 0, ',', ' ')."</b> ".strtolower($cw['credits']) . $plusCubits, "l_col");
	$str .= q_row("<b>".number_format($user['tech'])."</b> ".$cw['tech_units'], "l_col");
	$str .= "</table>";

	/*
	//if not in a ship, don't show these details
	if($user['ship_id'] == 1) {
		$str .= make_table2(array($cw['ship_destroyed']), "l_col");
	} else {
		$str .= make_table2(array(stripslashes($user_ship['ship_name'])), "l_col");
		$str .= q_row(popup_help("help.php?popup=1&ship_info=".$user_ship['shipclass']."&db_name=$db_name", 300, 600, $user_ship['class_name']), "l_col");

		$clan_fleet_str = "";
		if($user['clan_id'] > 0 && $GAME_VARS['clan_fleet_attacking'] == 1){
			$clan_fleet_str= " ".$cw['c_fleet'].": <b>$user_ship[clan_fleet_id]</b>";
		}
		$str .= q_row($cw['fleet'].": <b>$user_ship[fleet_id]</b><br />".$clan_fleet_str, "l_col");
		$str .= q_row($cw['fighters'].": <b>".number_format($user_ship['fighters'])."</b> / <b>".number_format($user_ship['max_fighters'])."</b>", "l_col");
		$str .= q_row($cw['shields'].": <b>".number_format($user_ship['shields'])."</b> / <b>".number_format($user_ship['max_shields'])."</b>", "l_col");
		$str .= q_row($cw['armour'].": <b>".number_format($user_ship['armour'])."</b> / <b>".number_format($user_ship['max_armour'])."</b>", "l_col");

		$str .= q_row($cw['cargo_bays'].":<br />".bay_storage($user_ship), "l_col");
	}

	$str .= "</table>";
	//end of ship detail listing
	*/


	/*************************Forum Links***************************/
/*
	//prepare to get central forum information
	$last_globals = $p_user['last_access_global_forum'];
	$last_admin = 0;

	if($user['login_id'] == 1){ //last access admin forum for admins
		$last_admin = $game_info['last_access_admin_forum'];

	} elseif($user['login_id'] == OWNER_ID) { //last access admin forum for server op
		$last_admin = $p_user['icq'];
	}

	//get new messages from central forums (admin and globals).
	db("select count(message_id) as new_messages, forum_id from se_central_messages where (timestamp > '$last_admin' && (('$user[login_id]' = 1 && (game_id != '$game_info[game_id]' || sender_id != 1)) || (sender_id != '$user[login_id]' && '$user[login_id]' = '".OWNER_ID."')) && forum_id = -99) || (timestamp > '$last_globals' && (sender_id = 1 || (sender_id != 1 && sender_id != '$user[login_id]')) && forum_id = -50) group by forum_id");

	//initialise array that will contain how many of each message was found.
	$msgs_array = array(-1 => 0, -5 => 0, -50 => 0, -99 => 0);
	while($msg_tmp = dbr(1)){
		$msgs_array[$msg_tmp['forum_id']] = $msg_tmp['new_messages'];
	}

	//get new messages for regular and clan forums
	db("select count(message_id) as new_messages, login_id as forum_id from ${db_name}_messages where (timestamp > '$user[last_access_forum]' && login_id = -1 && sender_id != '$user[login_id]') || (timestamp > '$user[last_access_clan_forum]' && login_id = -5 && clan_id = '$user[clan_id]' && sender_id != '$user[login_id]') group by login_id");

	while($msg_tmp = dbr(1)){
		$msgs_array[$msg_tmp['forum_id']] = $msg_tmp['new_messages'];
	}



	//initiate comms. table
	$str .= "<p />".make_table2(array($cw['communications']), "l_col");


	//messages links. Need to work out if there are any messages for the player.
	db("select count(message_id) from ${db_name}_messages where login_id = '$user[login_id]' and timestamp > ".$user['last_access_msg']);
	$counted = dbr();
	if($counted[0] == 0){
		$str .= q_row("\n<a href='mpage.php'>".$cw['no_messages']."</a> - <a href='message.php'>".$cw['send']."</a>", "l_col");
	}else{
		$str .= q_row("\n<a href='mpage.php'><b>$counted[0]</b> ".$cw['messages']."</a> - <a href='message.php'>".$cw['send']."</a>", "l_col");
	}

*/
	/*******************
	* Forum Link
	*******************/
	/*$forum_temp_str = "<a href='forum.php?target_id=-1'>".$cw['shout_box']."</a>";

	if($msgs_array[-1] > 0){
		$forum_temp_str .= " - {$msgs_array[-1]} <a href='forum.php?target_id=-1&last_time=$user[last_access_forum]'>".$cw['new']."</a>";
	}
	$str .= q_row($forum_temp_str, "l_col");*/


	/*******************
	* Global Forum Link
	*******************/
	/*$forum_temp_str = "<a href='/forum' target='_blank'>".$cw['forum']."</a>";

	$str .= q_row($forum_temp_str, "l_col");

	$forum_temp_str = "";*/

	/*******************
	* Clan Forum Link
	*******************/
	/*if($user['login_id'] == 1 || $user['login_id'] == OWNER_ID){
		$forum_temp_str .= "<a href='forum.php?target_id=-5'>".$cw['clan_forums']."</a>";

	} elseif($user['clan_id'] > 0) {
		$forum_temp_str .= "<a href='forum.php?target_id=-5'><font color='$user[clan_sym_color]'>$user[clan_sym]</font> ".$cw['forum']."</a>";

		if($msgs_array[-5] > 0){
			 $forum_temp_str .= " - ({$msgs_array[-5]} <a href='forum.php?target_id=-5&last_time=$user[last_access_clan_forum]'>".$cw['new']."</a>)";
		}
		//security precaution
		unset($_REQUEST['clan_id']);
	}
	//only print the clan forum link for people in a clan
	(!empty($forum_temp_str)) ? $str .= q_row($forum_temp_str, "l_col") : 0;


	$forum_temp_str = "";
*/
	/*******************
	* Admin Forum Link
	*******************/
	/*if($user['login_id'] == 1 || $user['login_id'] == OWNER_ID){
		$forum_temp_str .= "<a href='forum.php?target_id=-99'>".$cw['admin_forums']."</a>";

		if($msgs_array[-99] > 0){
			 $forum_temp_str .= " - ({$msgs_array[-99]} <a href='forum.php?target_id=-99&last_time=$user[last_access_clan_forum]'>new</a>)";
		}
	}
	//only print the clan forum link for people in a clan
	(!empty($forum_temp_str)) ? $str .= q_row($forum_temp_str, "l_col") : 0;


	$str .= "</table>";
	//end forums table

	$str .= "<p />".make_table2(array($cw['account_functions']), "l_col");

	$str .= q_row("\n<a href='parrainage.php'>".$cw['parrainage']."</a>", "l_col");

	$str .= q_row("\n<a href='options.php'>".$cw['preferences']."</a>", "l_col");
	$str .= q_row("\n&nbsp", "v_col");

	//admin lower sidebar
	if($user['login_id'] == 1){
		$str .= q_row("\n<a href='logout.php'>".$cw['logout']."</a>", "l_col");
	} else { //player lower sidebar
		$str .=  q_row("\n<a href='logout.php?logout_single_game=1' target='_top'>".$cw['game_list']."</a>", "l_col");
		$str .=  q_row("\n<a href='logout.php?comp_logout=1' target='_top'>".$cw['complete_logout']."</a>", "l_col");
	}
	$str .= "</table>";*/

	/*************** End Content *************/

	if ($p_user['id_didac']) {
		db("select * from se_didacticiel where id=" . $p_user['id_didac']);
		$data = dbr();

		$str .= "<div id='didacticiel'>";
		$str .= "<div id='texte_didac'>";
		$str .= nl2br($data['texte']);
		$str .= "</div>";

		$str .= "<div id='liens_didac'>";
		$str .= "<a href='#null' id='prec_didac'>&lt;&lt; Préc.</a>&nbsp;&nbsp;";
		$str .= "<a href='#null' id='suiv_didac'>Suiv. &gt;&gt;</a>";
		$str .= "</div>";

		$str .= "<a href='#null' id='annuler_didac'>Fermer</a>";

		$str .= "</div>";
	}

	$str .= '<br />Pour nous soutenir votez sur facebook:<br /><iframe src="http://www.facebook.com/plugins/like.php?href=http%253A%252F%252Fwww.astravires.fr%252F&amp;layout=button_count&amp;show_faces=false&amp;width=150&amp;action=like&amp;font=arial&amp;colorscheme=dark&amp;height=40" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:150px; margin: 5px; height:40px;" allowTransparency="true"></iframe>';

	$rand = mt_rand(1, 999999);

    /*
     * publicité désactivée
     * 
	if (!$p_user['bp_user_id']) {
		$str .= "<br />
				<iframe id='a7eabf27' name='a7eabf27' src='http://pub.nilsine.fr/www/delivery/afr.php?zoneid=2&amp;cb=$rand' framespacing='0' frameborder='no' scrolling='no' width='120' height='600'><a href='http://pub.nilsine.fr/www/delivery/ck.php?n=a3e02ac4&amp;cb=$rand' target='_blank'><img src='http://pub.nilsine.fr/www/delivery/avw.php?zoneid=2&amp;cb=$rand&amp;n=a3e02ac4' border='0' alt='' /></a></iframe>
				";
	}
    */

	$str .= '</td>';
    
    echo $str;
}


//Function prints the top bar in the game.
//outputs directly to the browser
//creates a second table within a cell of the first. This second table has two rows, one of which is the top-bar, the other being the main content
function print_topbar(){
	global $user, $db_name, $status_bar_help, $GAME_VARS, $show_bars, $cw, $st, $p_user;

	//ensure print_footer knows the bars are here.
	$show_bars = true;

	//This is "cell" 2 of the main table, and contains most of the page content.
	//valign=top is necessary otherwise all sorts of bad things happen :) (i.e. the top bars end up in the middle hehehe :o ).
	$str = "\n<td valign='top'>";

	//A table is used to create two rows. one of which houses the page data. (but that's initiated at the end of this function)
	$str .= "\n<table width='100%' border='0' cellspacing='0' cellpadding='3' >";

	//initialise the topbar itself.
	$str .= "\n<tr><td>";

	if ($user['turns_run'] < 100) $guide = "<li><a href='help.php?topic=Getting_Started' target='_blank'>Guide de démarrage</a></li>";

	// ================== Détection nouveaux messages =======================

	//get new messages from central forums (admin and globals).
	db("select count(message_id) as new_messages, forum_id from se_central_messages where (timestamp > '$last_admin' && (('$user[login_id]' = 1 && (game_id != '$game_info[game_id]' || sender_id != 1)) || (sender_id != '$user[login_id]' && '$user[login_id]' = '".OWNER_ID."')) && forum_id = -99) || (timestamp > '$last_globals' && (sender_id = 1 || (sender_id != 1 && sender_id != '$user[login_id]')) && forum_id = -50) group by forum_id");
	//initialise array that will contain how many of each message was found.
	$msgs_array = array(-1 => 0, -5 => 0, -50 => 0, -99 => 0);
	while($msg_tmp = dbr(1)){
		$msgs_array[$msg_tmp['forum_id']] = $msg_tmp['new_messages'];
	}
	//get new messages for regular and clan forums
	db("select count(message_id) as new_messages, login_id as forum_id from ${db_name}_messages where (timestamp > '$user[last_access_forum]' && login_id = -1 && sender_id != '$user[login_id]') || (timestamp > '$user[last_access_clan_forum]' && login_id = -5 && clan_id = '$user[clan_id]' && sender_id != '$user[login_id]') group by login_id");
	while($msg_tmp = dbr(1)){
		$msgs_array[$msg_tmp['forum_id']] = $msg_tmp['new_messages'];
	}

	db("select count(message_id) from ${db_name}_messages where login_id = '$user[login_id]' and timestamp > ".$user['last_access_msg']);
	$counted = dbr();
	$img_nv = '<img src="images/interface/nouveau_msg.png" />';
	if($counted[0] > 0) $nouv1 = $img_nv;
	if ($msgs_array[-1] > 0) $nouv2 = $img_nv;
	if ($msgs_array[-5] > 0) $nouv3 = $img_nv;
	if ($user['clan_id']) $forum_guilde = '<li><a href="forum.php?target_id=-5">Forum guilde&nbsp;' . $nouv3 . '</a></li>';

	if ($nouv1 || $nouv2 || $nouv3) $nouveau = $img_nv;

	$str .= '
	<div id="myslidemenu" class="jqueryslidemenu">
		<ul>
			<li><a href="location.php">Système actuel</a></li>

			<li><a href="#">Centre tactique</a>
	  			<ul>
	  				<li><a href="fleet_command.php">Flottes</a></li>
	  				<li><a href="planet_list.php">Planètes</a></li>
	  				<li><a href="clan.php">Guilde</a></li>
	  				<li><a href="map.php" target="_blank">Carte de la galaxie</a></li>
	  				<li><a href="news.php">Evénements</a></li>
	  				<li><a href="player_stat.php">Classement</a></li>
	  			</ul>
			</li>

			<li><a href="#">Communication&nbsp;' . $nouveau . '</a>
	  			<ul>
	  				<li><a href="mpage.php">Messagerie&nbsp;' . $nouv1 . '</a></li>
	  				<li><a href="forum.php?target_id=-1">Shoutbox&nbsp;' . $nouv2 . '</a></li>
	  				<li><a href="/forum" target="_blank">Forum</a></li>
	  				' . $forum_guilde . '
	  				<li><a href="diary.php">Journal de bord</a></li>
	  			</ul>
			</li>

			<li><a href="#">Compte</a>
	  			<ul>
	  				<li><a href="help.php" target="_blank">Aide</a></li>
	  				<li><a href="user_extra.php">Options & Parrainage</a></li>';
	  				//<li><a href="parrainage.php">Parrainage</a></li>
	  		$str .= '<li><a href="buy.php">Plus de cycles</a></li>';
	  	   $str .= '<li><a href="bugs_tracker.php" target="_blank">Signaler un bug</a></li>
	  				<li><a href="logout.php?logout_single_game=1">Autres galaxies</a></li>
	  				<li><a href="logout.php?comp_logout=1">Déconnexion</a></li>
					<li><a href="options.php?retire=1">Quitter la galaxie</a></li>
	  			</ul>
			</li>

			' . $guide . '
    	</ul>
	<br style="clear: left" />
	</div>
	';

	/*************** Content *************/

	//the following table contains the topbar itself

	$str .= "\n<table cellspacing='2' class='topbar' align='center' border='0'>";

	/*$str .= "<tr>";

	//server admin link for bug tracker / bug reporting
	if($user['login_id'] == OWNER_ID){
		db("select count(id) from server_issuetracking where status = '10'");
		$temp_res = dbr();
		$str .= "\n<td class='v_col_2' align='center'><a href='bugs_tracker.php' target='_blank'>".$cw['bugs']."</a> ($temp_res[0])</td>";

	//others
	} else {
		$str .= "\n<td class='v_col_2' align='center'>\n<a href='bugs_tracker.php'>".$cw['report_a_bug']."</a>\n</td>";
	}

	$str .= "\n<td class='v_col' align='center' colspan='4'>\n".print_name($user)."\n</td>";
	$str .= "\n<td class='v_col_2' align='center'>\n<a href='help.php{$status_bar_help}' target='_blank'>".$cw['help']."</a>\n</td>";

	$str .= "\n</tr>";*/

	//middle row
	/*$str .= "\n<tr align='center' class='v_col'>";
	$str .= "\n<td><a href='diary.php'>".$cw['diary']."</a></td>";
	$str .= "\n<td width=98><a href='player_stat.php'>".$cw['player_ranking']."</a></td>";
	$str .= "\n<td><a href='news.php'>".$cw['news']."</a></td>";
	$str .= "\n<td width=101><a href='fleet_command.php'>".$cw['fleet_command']."</a></td>";
	$str .= "\n<td><a href='planet_list.php'>".$cw['planets']."</a></td>";
	$str .= "\n<td><a href='clan.php'>".$cw['clan_control']."</a></td>";

	//bottom row of topbar
	$str .= "</tr>";*/

	/*$str .= "<tr align='center' class=v_col_2>";


	//newbie safety, or admin link
	if($user['login_id'] == 1 || $user['login_id'] == OWNER_ID) { //cheating and using this space for admin links
		$str .= "\n<td><a href='admin.php'>".$cw['admin']."</a>";
		if($user['login_id'] == OWNER_ID) { //server admin link
			$str .= "\n<br /><a href='developer.php'>".$cw['server_admin']."</a>";
		}
		$str .= "</td>";
	} elseif($user['turns_run'] > $GAME_VARS['turns_safe']){
		$str .= "\n<td><a href='buy.php' style='color:#FF0000;'>".$cw['Get more turns']."</a></td>";
	} elseif($user['turns_run'] < $GAME_VARS['turns_safe']){
		$s_turns = $GAME_VARS['turns_safe'] - $user['turns_run'];
		$str .= "\n<td><b>$s_turns</b><br /><b class='b1'>".$cw['safe_turns_left']."</b></td>";
	} else {
		$str .= "\n<td><b class='b1'>".$cw['leaving_newbie_safety']."</b></td>";
		charge_turns(1);
		send_message($user['login_id'], $st[7]);
	}

	$str .= "\n<td><b>".number_format($user['turns'])."</b>/<b>".number_format($GAME_VARS['max_turns'])."</b><br />".$cw['turns']."</td>";
	$resultat = mysql_query("select star_name from ${db_name}_stars where star_id=".$user['location']);
	$data = mysql_fetch_array($resultat);
	$str .= "\n<td>".$cw['system']." # <b>".$user['location']."</b><br><a href='location.php'>".$data['star_name']."</a></td>";

	$str .= "<td><b>".number_format($user['cash'], 0, ',', ' ')."</b><br />".$cw['credits']."</td>";

	if ($GAME_VARS['uv_num_bmrkt'] > 0) {
		$str .= "<td><b>".number_format($user['tech'])."</b><br />".$cw['tech_units']."</td>";
	} else {
		$str .= "<td>&nbsp;</td>";
	}

	$str .= "<td>&nbsp;</td>";

	$str .= "</tr>";*/

	$resultat = mysql_query("select star_name from ${db_name}_stars where star_id=".$user['location']);
	$data = mysql_fetch_array($resultat);
//	$str .= "<h4 id='systeme'><a href='location.php'>" . $cw['system'] . " <b>" . $user['location'] . "</b><br />" . $data['star_name'] . "</a>";



	$str .= "\n</table>"; //finish topbar itself

	/*************** End Content *************/


	//end the bar (which constitutes one cell and one row.
	$str .= "\n</td></tr>";

	//initialise the main 'cell'. this will contain the pages content.
	//this cannot really be done elsewhere, so is done here
	$str .= "\n<tr><td>\n<br />";

	//output the bar
	echo $str;
	flush();
}



//function that can be used create a viable input form. Adds hidden vars.
function get_var($title, $page_name, $text, $var_name, $var_default, $taille=20) {
	global $directories, $cw, $st;
	$ostr = "";
	if($var_name == "ship_name") {
		$ostr = "<script type='text/javascript'>

					jQuery(document).ready(function($) {
					   $('#generate_button').click(function() {
					   		$.get('ajax.php',
							   { cmd: 'generateVesselName' },
							   function(data) {
									$('input[name = ${var_name}]').val(data);
							});
						});
					});

				  </script>";
	}

	$ostr .= "\n<form name='get_var_form' action='$page_name' method='POST'>";
	$ostr .= "\n $text<p />";
	foreach($_GET as $var => $value){
		$ostr .= "\n<input type='hidden' name='$var' value='$value' />";
	}
	foreach($_POST as $var => $value){
		$ostr .= "\n<input type='hidden' name='$var' value='$value' />";
	}
	if($var_name == 'sure') {
		$ostr .= "\n<input type='hidden' name='sure' value='yes' />";
		$ostr .= "\n<input type='submit' name='".$cw['submit']."' value='".$cw['yes']."' /> - <input type='Button' width='30' value='".$cw['no']."' onclick=\"javascript: history.back()\" />\n</form>";
	} elseif(($var_name == "passwd") || ($var_name == 'passwd_verify') || $var_name == 'passwd2') {
		$ostr .= "\n<input type='password' name='$var_name' value='$var_default' size=20 /> - ";
		$ostr .= "\n<input type='submit' value='".$cw['submit']."' />\n</form>";
	} elseif($var_name == "passwd2") {
		$ostr .= "\n<input type='password' name='$var_name' value='$var_default' size=20 /> - ";
		$ostr .= "\n<input type='submit' value='".$cw['submit']."' />\n</form>";
	} elseif($var_name == "text") {
		$ostr .= "\n<textarea name='$var_name' cols='50' rows='20' wrap='soft'>$var_default</textarea>";
		$ostr .= "\n<p /><input type='submit' value='".$cw['submit']."' /></form>";
	} elseif($var_name == "msg") {
		//get the message html
		ob_start();
		include_once("$directories[includes]/message.inc.php");
		$ostr .= ob_get_contents();
		ob_end_clean();
		$ostr .= "</form>";
		$var_name = "text"; //changing varname to text, so the javascript focus works (as the field is called 'text' not 'msg')
	} else {
		$ostr .= "\n<input type='text' name='$var_name' value='$var_default' size='$taille' class='inputtext' />&nbsp;";

		if($var_name == "ship_name") {
			$ostr .= "<input type='button' value='G&eacute;n&eacute;rer' id='generate_button'/> <br />";
		}

		$ostr .= "\n<input type='submit' value='".$cw['submit']."' /></form>";
	}
	if($var_name != "sure") {
		$ostr .= "\n<script> document.get_var_form.$var_name.focus(); </script>";
	} else {
		$ostr .= "\n<script> document.get_var_form.submit.focus(); </script>";
	}
	print_page($title,$ostr);
}



/********************
Account updating functions
*********************/

//function that charges turns for something. Admin is exempt.
function charge_turns($amount) {
	global $db_name,$user;
	if($user['login_id'] != 1) {
		$amount = round($amount);
		dbn("update ${db_name}_users set turns = turns - '$amount', turns_run = turns_run + '$amount' where login_id = '$user[login_id]'");
		$user['turns'] -= $amount;
		$user['turns_run'] += $amount;
	}
}

//function that can give a user cash. Admin is exempt.
function give_cash($amount) {
	global $db_name,$user;
	if ($user['login_id'] != 1) {
		dbn("update ${db_name}_users set cash = cash + '$amount' where login_id = '$user[login_id]'");
		$user['cash'] += $amount;
	}
}

//function takes cash from a player. Admin is exempt.
function take_cash($amount) {
	global $db_name,$user;
	if ($user['login_id'] != 1) {
		dbn("update ${db_name}_users set cash = cash - '$amount' where login_id = '$user[login_id]'");
		$user['cash'] -= $amount;
	}
}


//take tech support units from a player. Admin is exempt.
function take_tech($amount) {
	global $db_name,$user;
	if ($user['login_id'] != 1) {
		dbn("update ${db_name}_users set tech = tech - '$amount' where login_id = '$user[login_id]'");
		$user['tech'] -= $amount;
	}
}

//Give tech support units to a player. Admin is exempt.
function give_tech($amount) {
	global $db_name,$user;
	if ($user['login_id'] != 1) {
		dbn("update ${db_name}_users set tech = tech + '$amount' where login_id = '$user[login_id]'");
		$user['tech'] += $amount;
	}
}


/********************
Message Functions
*********************/

//sends $text to $to, from global $user
function send_message($to, $text, $clan_id = 0) {
	global $db_name,$user;
	if($to == -5 && $clan_id != 0){//message to the clan
		$c_num = $clan_id;
	} else {
		$c_num = 0;
	}
	dbn("insert into ${db_name}_messages (timestamp,sender_name, sender_id, login_id, text, clan_id) values(".time().",'$user[login_name]','$user[login_id]','$to','".mysql_escape_string(substr($text, 0, 5000))."','$clan_id')");
}

function send_templated_email($to, $type){
	global $db_name;
	db2("select * from user_accounts WHERE login_id = $to LIMIT 1");
	$to_player = dbr2(1);
	db2("select name from se_games where db_name='${db_name}' LIMIT 1");
	$galaxy_name = dbr2(1);
	$galaxy_name = $galaxy_name['name'];
	if ($type == 'message')
	{
		if ($to_player['sendemail_am'][2] == 1 && filter_var($to_player['email_address'], FILTER_VALIDATE_EMAIL))
		{
			$contactname = $to_player['login_name'];
			$contactemail = $to_player['email_address'];
			//db select pt a afla contact name si contactemail
			//includ fisier pt a lua subject si message serbancatalin18@yahoo.com jp.lannoy@nilsine.fr
			include 'includes/email_templates/message.php';
			$message = str_replace(array("{nickname}", "{galaxy_name}"), array($contactname, $galaxy_name), $message);
			send_mail(SERVER_NAME, 'info@astravires.fr', $contactname, $contactemail, $subject, $message);
//			echo SERVER_NAME;
//			echo '<br/>';
//			echo $contactname;
//			echo '<br/>';
//			echo $contactemail;
//			echo '<br/>';
//			echo $subject;
//			echo '<br/>';
//			echo $message;
//			exit();
		}
	}
	else
	{
		if ($to_player['sendemail_am'][0] == 1 && filter_var($to_player['email_address'], FILTER_VALIDATE_EMAIL))
		{
			$contactname = $to_player['login_name'];
			$contactemail = $to_player['email_address'];
			include 'includes/email_templates/attack.php';
			$message = str_replace(array("{nickname}", "{galaxy_name}"), array($contactname, $galaxy_name), $message);
			send_mail(SERVER_NAME, 'info@astravires.fr', $contactname, $contactemail, $subject, $message);
		}
	}
}


//sends $text to all players in $game_db (using $recipients and $sender to tell the users who the message is for and from)
function message_all_players($text, $game_db, $recipients, $sender){
global $user, $cw, $st;

	db2("select login_id from ${game_db}_users");
	while($players = dbr2(1)) {
		dbn("insert into {$game_db}_messages (timestamp,sender_name, sender_id, login_id, text) values(".time().",'$user[login_name]','$user[login_id]','$players[login_id]','".mysql_escape_string(sprintf($st[1814], $recipients, $sender).$text)."')");
	}
	return $cw['Message sent to all players in']." <b>$game_db</b>.";
}


//function that will make BB code into valid HTML for $text
//all are case insensitive
function mcit($text) {
	global $directories, $cw, $st;

	$text = nl2br(strip_tags(trim($text)));

	//Links - will add a http:// if no protocol selected
	$text = preg_replace("/(\[url\])(http:\/\/|https:\/\/|ftp:\/\/|mailto:)*(.*?)(\[\/url\])/ie","'<a href=\''.(strlen('\\2') > 0 ? '\\2'.'\\3' : 'http://'.'\\3').'\' target=_new>'.'\\3'.'</a>'",$text);

	//Smilies - makelower case if not already (as case insensitive (linux is case sensitive).
	//smilie keyword must be between 3 and 6 alphabetical long. Must also match the file name.
	$text = preg_replace("/\[([a-z]{3,6})\]/ie","'\n<img src=\'$directories[images]/smiles/'.strtolower('\\1').'.gif\' />'",$text);

	//Bold and italics and underline
	$text = preg_replace("/\[bi\](.*?)\[\/bi\]/i","<b><i>\\1</i></b>",$text);
	$text = preg_replace("/\[b\](.*?)\[\/b\]/i","<b>\\1</b>",$text);
	$text = preg_replace("/\[u\](.*?)\[\/u\]/i","<u>\\1</u>",$text);
	$text = preg_replace("/\[i\](.*?)\[\/i\]/i","<i>\\1</i>",$text);

	//surrounded braces are replaced with single braces. this allows to print mcit function code.
	$text = preg_replace("/\[(\]|\[)\]/","\\1",$text);

	//Color
	if(!empty($_POST['colorchanger']) && $_POST['colorchanger'] != "#FFFFFF"){
		$text = "<font color='".$_POST['colorchanger']."'>".$text."</font>";
	}
	return $text;
}


//function that lists all messages. Used for printing the forums, as well as private messages.
function print_messages($target_id, $permissions, $clan_id, $col_arr) {
	global $db_name, $user, $user_options, $game_info, $last_time, $cw, $st;

	$clan_string_sql = "";
	$clan_forum_str = "";
	$ret_str = "";

	if($target_id < 0){ //forums
		$last_access = $user['last_access_forum'];
		if($target_id == -5){//clan forum
			$clan_string_sql = " && clan_id = '$clan_id' ";
			$clan_forum_str = "&amp;clan_id=$clan_id";
			$last_access = $user['last_access_clan_forum'];
		}
		if(empty($last_time)){ //not set time to go back to
			$forum_time = time() - ($user_options['forum_back'] * 3600);
		} else {
			$forum_time = (int)$last_time;
		}
	} else { //user messages
		$forum_time = 0;
		db("select count(message_id) from ${db_name}_messages where login_id = '$target_id'");
		$counted = dbr();
		$last_access = $user['last_access_msg'];
	}

	//central forum, different table. - external from game
	if($target_id == -99 || $target_id == -50){
		db("select *, sender_id as login_id from se_central_messages where timestamp > $forum_time && forum_id = '$target_id' order by timestamp desc");
		$user_options['show_sigs'] = 0; //don't try to show sigs in these forums

	} else { //in-game message table.
	//note: due to the fact there is no user -1, this has to cheat and select the sig of user id 1 (admin who is always in game) to ensure messages are loaded. admin sig is not actually show though.
		db("select *, sender_id as login_id from ${db_name}_messages where login_id = '$target_id' and timestamp > $forum_time $clan_string_sql order by timestamp desc");
	}

	$sig_array = array(); $i=0;
	while($messages = dbr(1)) {
		$printed_name = ""; //undeclare to next entry
		$no_reply = 0; //whether can reply
		$cols = 2; //num of cols within this post.

		//no sig, or not showing
		if($user_options['show_sigs'] == 0 || $messages['login_id'] == -1) {
			$messages['sig'] = "";

		//sig already loaded from previous message
		} elseif(isset($sig_array[$messages['login_id']])) {
			$messages['sig'] = $sig_array[$messages['login_id']];

		//load the players sig
		} else {
			db2("select sig from ${db_name}_users where login_id = '$messages[login_id]'");
			$temp_sig = dbr2();
			$sig_array[$messages['login_id']] = mcit(stripslashes("\n\n".$temp_sig['sig']));
			$messages['sig'] = $sig_array[$messages['login_id']];
		}

		$coul_fond = ($messages['timestamp'] > $last_access) ? $col_arr[3]:$col_arr[0];
		$ret_str .= "\n<table border='0' cellpadding='5'>\n<tr bgcolor='$coul_fond'>";
		//time and date of message.
		$ret_str .= "\n<td width='100'>\n<b>".date( "M d - H:i",$messages['timestamp'])."</b></td>\n<td width='250'>";
		if($messages['login_id'] >= 1 && $target_id > -50){//message by a player for in-game
			$printed_name = print_name($messages);

		} elseif(!empty($messages['forum_id'])) {//a central forum
			$no_reply = 1;
			$printed_name = "\n$messages[sender_name]";
		}

		if(empty($printed_name)){ //message by an automated entity, or retired player
			$no_reply = 1;
			$printed_name = "\n<b class='b1'>$messages[sender_name]</b>";
		}

		if ($target_id == -1) $no_reply = 1;

		$ret_str .= $printed_name;

		$ret_str .= "</td>\n";
		//player link to delete messages (with checkboxes)
		if($target_id > -50){//can't do these things with the central forums
			$cols = 3;
			$ret_str .= "<td align='right' width='230'>";
			if($no_reply == 0){//no reply link for certain messages
				$ret_str .= "<a href='message.php?target_id=$messages[sender_id]&amp;reply_to=$messages[message_id]'>".$cw['reply']."</a> - ";
			}
			$ret_str .= "<a href='message.php?forward=$messages[message_id]'>".$cw['forward']."</a> - <a href='diary.php?log_ent=$messages[message_id]'>".$cw['log']."</a>";
			if($target_id > 0 && $counted[0] > 1) {
				$ret_str .= " - <a href='$_SERVER[PHP_SELF]?killmsg=$messages[message_id]'>".$cw['delete']."</a> - <input type='checkbox' name='del_mess[$messages[message_id]]' value='$messages[message_id]' />";

			//admin link to delete messages in forum
			} elseif($permissions == 1) {
				$ret_str .= " - <a href='$_SERVER[PHP_SELF]?target_id=$target_id&amp;killmsg=$messages[message_id]{$clan_forum_str}'>".$cw['delete']."</a>";
			}
			$ret_str .= "</td>";
		}

		$ret_str .= "</tr>\n<tr>\n<td colspan='$cols' bgcolor='$col_arr[1]'><blockquote>$messages[text]</blockquote></td></tr>";
		if(!empty($messages['sig'])){
			$ret_str .= "<tr><td colspan='$cols' bgcolor='$col_arr[2]'>$messages[sig]</td></tr>";
		}
		$ret_str .= "\n</table><p />";
		$i++;
	}
	return $ret_str;
}


/********************
Player Erasing Functions (oh the joy!! ;) )
*********************/

//Retires $target
function retire_user($target) {
	global $user,$db_name, $cw, $st;
	//admin can't be retired. and only Server op and retire the server op.
	if($target < 6 || ($target == OWNER_ID && $user['login_id'] != OWNER_ID)) {
		print_page($cw['retire'],$st[165]);
	}
	if(($target == $user['login_id']) || ($user['login_id'] == 1)) {
		db("select login_name from ${db_name}_users where login_id = '$target'");
		$target_user = dbr(1);

		post_news("<b class='b1'>$target_user[login_name]</b> $st[166]", "player_status");

		dbn("delete from ${db_name}_ships where login_id = '$target'");
		dbn("update ${db_name}_bilkos set bidder_id = 0, timestamp = ".time()." where bidder_id = '$target'");
		dbn("update ${db_name}_planets set login_name = 'Un-Owned', login_id=4, pass='', clan_id = 0 where login_id = '$target'");
		dbn("delete from ${db_name}_diary where login_id = '$target'");
		dbn("delete from ${db_name}_user_options where login_id = '$target'");
		dbn("delete from ${db_name}_users where login_id = '$target'");
	}
}


//function to delete or unset most of what a player owns.
//is generally run if the player has lost their last ship.
function wipe_player($unfortunate_id, $clan_id){
	global $db_name, $GAME_VARS, $cw, $st;
	//erase all ships (though in all likelyhood the player won't have any at this point).
	dbn("delete from ${db_name}_ships where login_id = '$unfortunate_id'");

	//reset bilkos items that the player has bid on but not won
	dbn("update ${db_name}_bilkos set bidder_id = 0 where bidder_id = '$unfortunate_id' && active = 1");

	//delete bilko's items that the player won
	dbn("delete from ${db_name}_bilkos where bidder_id = '$unfortunate_id' && active = 0");

	//set planets to unowned.
	dbn("update ${db_name}_planets set login_name = 'Un-Owned', login_id = 4, pass='', clan_id = 0 where login_id = '$unfortunate_id'");

	//game is not in SD, so can restart
	if($GAME_VARS['sudden_death'] != 1){
		send_message($unfortunate_id, $st[167]);
		$new_ship = give_first_ship($unfortunate_id, $clan_id);

	} else {//game in SD, so in a ship destroyed.
		$new_ship = 1;
	}

	//resest some of the account details
	dbn("update ${db_name}_users set cash = '$GAME_VARS[start_cash]', tech = '$GAME_VARS[start_tech]', turns = '$GAME_VARS[start_turns]', ship_id = '$new_ship', location = 1, genesis = 0, terra_imploder = 0, alpha=0, gamma = 0, delta = 0, turns_run = 0 where login_id = '$unfortunate_id'");
	insert_history($unfortunate_id, $cw['account wiped']);
}


/********************
Ship Functions
*********************/

//function to create an escape pod
function create_escape_pod($target){
	global $db_name, $GAME_VARS, $cw, $st;
	$rand_star = random_system_num(); //make a random system number up.

	$ship_stats = load_ship_types(2); //load ship data for EP (class = 2)

	dbn("insert into ${db_name}_ships (ship_name, login_id, shipclass, class_name, class_name_abbr, fighters, max_fighters, max_shields, armour, max_armour, cargo_bays, mine_rate_metal, mine_rate_fuel, move_turn_cost, location, config,clan_id) values ('Escape Pod', '$target[login_id]', '2', '$ship_stats[name]', '$ship_stats[class_abbr]', '$ship_stats[fighters]', '$ship_stats[max_fighters]', '$ship_stats[max_shields]', '$ship_stats[max_armour]', '$ship_stats[max_armour]', '$ship_stats[cargo_bays]', '$ship_stats[mine_rate_metal]', '$ship_stats[mine_rate_fuel]', '$ship_stats[move_turn_cost]', '$rand_star', '$ship_stats[config]', '$target[clan_id]')");
	$ship_id = mysql_insert_id();


	//get users explored state.
	if(!isset($target['explored_sys']) && $GAME_VARS['uv_explored'] == 0){
		db2("select explored_sys from ${db_name}_users where login_id = '$target[login_id]'");
		$temp_exp_1 = dbr2(1);
		$target['explored_sys'] = $temp_exp_1['explored_sys'];
		unset($temp_exp_1);

		explore_sys($target, $rand_star);
		scramble_explored($target);
	}

	dbn("update ${db_name}_users set ship_id ='$ship_id' where login_id = '$target[login_id]'");

	$target['location'] = $rand_star;
	$target['ship_id'] = $ship_id;

	return $target;
}


//A function that gets all the details for the user's new ship, and returns the completed user_ship array.
function get_user_ship($ship_id){
	global $db_name, $user_ship, $cw, $st;

	//not using an SD, get ship details
	if($ship_id > 1){
		db2("select * from ${db_name}_ships where ship_id = '$ship_id'");
		$user_ship = dbr2();
		empty_bays($user_ship);

		//don't complain if empty.
		!isset($user_ship['points_killed']) ? $user_ship['points_killed'] = 0: 0;

		//get the exp level of the ship.
		$user_ship['exp'] = resolve_level($user_ship['points_killed']);
	}
	if(empty($user_ship)){//give an SD
		$user_ship = array('ship_id' => 1, 'cargo_bays' => 0, 'empty_bays' => 0, 'ship_engaged' => 0, 'exp' => 0, 'config' => '', 'mine_rate_metal' => 0, 'mine_rate_fuel' => 0, 'fleet_id' => 0, 'shipclass' => 'SD');
	}
}


//will buy $num ships, naming them $ship_name, and charging $ship_cost per ship. Can duplicate them ($dupe), using $ship_stats in insert into array. $specific contains the ship_id
function bulk_buy_1 ($num, $ship_name, $ship_cost, $dupe, $ship_stats, $specific, $cost_text){
	global $GAME_VARS, $num_ships, $user, $user_ship, $db_name, $bmrkt_id, $cw, $st;

	$rs = "<p /><a href='earth.php'>".$cw['back to Earth']."</a>";
	$rs .= "<br /><a href='earth.php?ship_shop=1'>".$cw['return to Ship Shop']."</a>";
	$error_str = "";

	if($dupe == 1){
		$dupe_text = $cw['duplicate'];
	} else {
		$dupe_text = "";
	}

	if(config_check("bs",$ship_stats)){
		$w_ship = 1;
		$max_ships = $GAME_VARS['max_warships'];
		$num_ships['applic'] = $num_ships['warships'];
	}else {
		$max_ships = $GAME_VARS['max_other_ships'];
		$num_ships['applic'] = $num_ships['other_ships'];
		$w_ship = 0;
	}

	if($ship_stats['type_id'] < 3){ //trying to build an improper ship
		print_page($cw['error'],$st[168]);
	}

	if($num_ships['war_reached'] == 1 && $w_ship == 1) {
		$error_str = sprintf($st[169],$num_ships[warships],$GAME_VARS[max_warships]);
	} elseif($num_ships['other_reached'] == 1 && $w_ship == 0) {
		$error_str = sprintf($st[170],$num_ships[other_ships],$GAME_VARS[max_other_ships]);

	//check to allow user to enter the number of ships they want to buy.
	} elseif ($num < 1 || empty($ship_name)) {

		$error_str = "<script type='text/javascript'>

					jQuery(document).ready(function($) {
					   $('#generate_button').click(function() {
					   		$.get('ajax.php',
							   { cmd: 'generateVesselName' },
							   function(data) {
									$(\"input[name = 'ship_name']\").val(data);
							});
						});
					});

				  </script>";

		$t7676 = $max_ships - $num_ships['applic'];
		if(($t7676 * $ship_cost) > $user['cash']){
			$t7676 = floor($user['cash'] / $ship_cost);
		}
		if($ship_stats['tcost'] > 0 && ($t7676 * $ship_stats['tcost'] > $user['tech'])){
			$t7676 = floor($user['tech'] / $ship_stats['tcost']);
		}
		$error_str .= sprintf($st[171], $num_ships[total_ships], $num_ships[warships], $num_ships[other_ships]);
		$error_str .= "<form name='mass_buy' action='$_SERVER[PHP_SELF]' method='post'>";
		$error_str .= "<input type='hidden' name='$specific' value='$ship_stats[type_id]' />";
		$error_str .= "<input type='hidden' name='bmrkt_id' value='$bmrkt_id' />";
		$error_str .= $st[172]."<input type='text' name='ship_name' value='' size='15' />&nbsp;";
		$error_str .= "<input type='button' value='G&eacute;n&eacute;rer' id='generate_button'/> <br />";
		$error_str .= sprintf($st[173], $dupe_text, $ship_stats[name]."<input type='text' name=num value='$t7676' size=3 />".$cost_text);
		$error_str .= "<p /><input type='submit' value='".$cw['submit']."' /></form><p />";
	}elseif (($num_ships['warships'] + $num) > $GAME_VARS['max_warships'] && $w_ship == 1) { // check to ensure they are not trying to buy too many warships
		$error_str .= sprintf($st[174],$num_ships[warships],$GAME_VARS[max_warships]);
	}elseif (($num_ships['other_ships'] + $num) > $GAME_VARS['max_other_ships'] && $w_ship == 0) { // check to ensure they are not trying to buy too many other ships
		$error_str .= sprintf($st[175],$num_ships[other_ships], $GAME_VARS[max_other_ships]);
	}elseif($user['cash'] < $ship_cost * $num) { //check to see if the user can afford them
		print_page($cw['error'],sprintf($st[176], $num, $ship_stats[name]));
	} elseif($ship_stats['tcost'] > 0 && $user['tech'] < $ship_stats['tcost'] * $num){
		print_page($cw['error'],sprintf($st[177],$num, $ship_stats[name]));
	} else { //do the processing.

		$ship_name = correct_name($ship_name);
		$quotes = $ship_name;

		// remove old escape pods
		dbn("delete from ${db_name}_ships where login_id = '$user[login_id]' && class_name REGEXP 'Escape'");


		//ensure we don't duplicate extra armour!
		$ship_stats['armour'] = $ship_stats['max_armour'];

		//duplication requires the ship to be somewhat modified from the base stats.
		if($dupe == 1){
			$ship_stats['config'] = $user_ship['config'];
			$ship_stats['num_ot'] = $user_ship['num_ot'];
			$ship_stats['num_dt'] = $user_ship['num_dt'];
			$ship_stats['upgrade_slots'] = $user_ship['upgrade_slots'];
			$ship_stats['move_turn_cost'] = $user_ship['move_turn_cost'];
			$ship_stats['max_fighters'] = $user_ship['max_fighters'];
			$ship_stats['max_shields'] = $user_ship['max_shields'];
			$ship_stats['max_armour'] = $user_ship['max_armour'];
			$ship_stats['cargo_bays'] = $user_ship['cargo_bays'];
		}

		for($s=1;$s<=$num;$s++){
			if ($s<10) {
				$s_name = $ship_name." 0".$s;
			} else {
				$s_name = $ship_name." ".$s;
			}

			if(empty($user_ship['fleet_id']) || $user_ship['fleet_id'] < 1){
				$user_ship['fleet_id'] = 1;
			}

			$q_string = "insert into ${db_name}_ships (";
			$q_string = $q_string . "ship_name, login_id, location, clan_id, shipclass, class_name, class_name_abbr, fighters, max_fighters, max_shields, armour, max_armour, cargo_bays, mine_rate_metal, mine_rate_fuel, config, size, upgrade_slots, move_turn_cost, point_value, num_dt, num_ot, num_pc, num_ew, fleet_id";
			$q_string = $q_string . ") values(";
			$q_string = $q_string . "'$s_name', '$user[login_id]', '$user[location]', '$user[clan_id]', '$ship_stats[type_id]', '$ship_stats[name]', '$ship_stats[class_abbr]', '$ship_stats[fighters]', '$ship_stats[max_fighters]','$ship_stats[max_shields]', '$ship_stats[armour]', '$ship_stats[max_armour]', '$ship_stats[cargo_bays]', '$ship_stats[mine_rate_metal]', '$ship_stats[mine_rate_fuel]', '$ship_stats[config]', '$ship_stats[size]', '$ship_stats[upgrade_slots]', '$ship_stats[move_turn_cost]', $ship_stats[point_value], '$ship_stats[num_dt]', '$ship_stats[num_ot]', '$ship_stats[num_pc]', '$ship_stats[num_ew]', '$user_ship[fleet_id]')";
			dbn($q_string);
		}

		//puts the user into the newest ship, but only if they are in a EP, or ship destroyed.
		if($user['ship_id'] == 1 || $user_ship['shipclass'] < 3) {
			$new_ship_id = mysql_insert_id();
			dbn("update ${db_name}_users set ship_id = '$new_ship_id' where login_id = '$user[login_id]'");
			$user['ship_id'] = $new_ship_id;
		}
		get_user_ship($user['ship_id']);


		$x1 = $num*$ship_cost;
		$tcost = $num*$ship_stats['tcost'];
		$x2=$quotes." 1";
		$x3=$quotes." $num";
		$x4=$num_ships['total_ships'] + $num;
		take_cash($x1);
		if($ship_stats['tcost'] > 0){
			take_tech($tcost);
			$xtra_tech = ' '.$cw['and']." <b>$tcost</b> ". $cw['tech_units'];
		} else {
			$xtra_tech = "";
		}
		$error_str .= sprintf($st[178], $num, $dupe_text, $ship_stats[name], $x1, $xtra_tech, $x2, $x3, $x4, $user_ship[fleet_id]);
	}
	print_page($cw['bulk buying'],$error_str);
}


/*****************
* Ship Counting Functions
*****************/

//function that will work out how many flagships this player has got through
function num_flagships ($num_ships){
	if($num_ships == 0){
		return 0;
	}

	$result_num = 0;
	while($num_ships > 1){
		$num_ships = $num_ships / 2;
		$result_num ++;
	}
	return $result_num;
}


//function that counts the number of ships $u_id has that are warships, and the number that are not warships.
function ship_counter($u_id){
	global $db_name,$GAME_VARS, $cw, $st;

	$ret_array['warships'] = 0;
	$ret_array['total_ships'] = 0;

	db2("SELECT count(*) as ships, config REGEXP 'bs' AS warships FROM ${db_name}_ships WHERE login_id = '$u_id' GROUP BY warships DESC");

	while ($ships = dbr2(1)){
		if($ships['warships'] == 1){
			$ret_array['warships'] += $ships['ships'];
		}
		$ret_array['total_ships'] += $ships['ships'];
	}

	//work out the number of non-warships in the group.
	$ret_array['other_ships'] = $ret_array['total_ships'] - $ret_array['warships'];


	//determine if the user has as many ships as they are allowed.
	if($ret_array['other_ships'] >= $GAME_VARS['max_other_ships']){
		$ret_array['other_reached'] = 1;
	} else {
		$ret_array['other_reached'] = 0;
	}

	if($ret_array['warships'] >= $GAME_VARS['max_warships']){
		$ret_array['war_reached'] = 1;
	} else {
		$ret_array['war_reached'] = 0;
	}

	return $ret_array;
}


/****************
Ship Calculations
****************/

//Function to figure out the bonuses offered by weapon upgrades on $ship
function bonus_calc(&$ship){
	global $dt_damage, $ot_damage, $pc_damage, $ew_damage;

	//defensive turret : lvl 1
	$ship['dt'] = round($dt_damage * (mt_rand(75,125) / 100)) * $ship['num_dt'];

	//offensive turret : lvl 1
	$ship['ot'] = round($ot_damage * (mt_rand(80,120) / 100)) * $ship['num_ot'];

	//plasma cannon : lvl 2
	$ship['pc'] = round($pc_damage * (mt_rand(92,108) / 100)) * $ship['num_pc'];

	//electronic warfare module : lvl 1
	$ship['ewd'] = round((($ew_damage / 100) * 60) * (mt_rand(85,115) / 100)) * $ship['num_ew'];
	$ship['ewa'] = round((($ew_damage / 100) * 40) * (mt_rand(80,120) / 100)) * $ship['num_ew'];
}


//This function will return the level of the ship.
function resolve_level($points = 0){
	$points_per_advance = 4500;
	if(empty($points) || $points == 0){
		return 0;
	} else {
		return floor($points / $points_per_advance);
	}
}


//function that will return a list of the contents of $ship 's cargo bays.
function bay_storage($ship){
	if(empty($ship['cargo_bays'])) {
		return "&nbsp;&nbsp;<b>".$cw['none']."</b>";
	}
	$ret_str = "";
	$colour = '';
	empty_bays($ship);
	if ($ship['cargo_bays']*0.1 > $ship['empty_bays']) {
		$colour = '#c00000';
	} elseif ($ship['cargo_bays']*0.25 > $ship['empty_bays']) {
		$colour ='#d6750e';
	}
	if(!empty($ship['metal'])) {
		$ret_str .= "&nbsp;&nbsp;<b>$ship[metal]</b> <img src='images/logos/titane.gif' align=absmiddle> ".$cw['metals'];
	}
	if(!empty($ship['fuel'])) {
		if(!empty($ret_str)){
			$ret_str .= "<br />";
		}
		$ret_str .= "&nbsp;&nbsp;<b>$ship[fuel]</b> <img src='images/logos/larium.gif' align=absmiddle> ".$cw['fuels'];
	}
	if(!empty($ship['elect'])) {
		if(!empty($ret_str)){
			$ret_str .= "<br />";
		}
		$ret_str .= "&nbsp;&nbsp;<b>$ship[elect]</b> <img src='images/logos/electronique.gif' align=absmiddle> ".$cw['electronics'];
	}
	if(!empty($ship['colon'])) {
		if(!empty($ret_str)){
			$ret_str .= "<br />";
		}
		$ret_str .= "&nbsp;&nbsp;<b>$ship[colon]</b> <img src='images/logos/colons.gif' align=absmiddle> ".$cw['colonists'];
	}
	//if ($colour) $ret_str .= "<font color=$colour>";
	/*if($ship['empty_bays'] > 0) {
		if(!empty($ret_str)){
			$ret_str .= "<br />";
		}
		$ret_str .= "&nbsp;&nbsp;<b>$ship[empty_bays]</b> <img src='images/logos/stock.gif' align=absmiddle> ".$cw['empty'];
	}*/
	//if ($colour) $ret_str .= '</font>';
	return $ret_str;
}


function bay_storage_little($ship){
	if(empty($ship['cargo_bays'])) {
		return "\n&nbsp;&nbsp;<b>".$cw['none']."</b>";
	}
	$ret_str = "";
	$colour = '';
	empty_bays($ship);
	if ($ship['cargo_bays']*0.1 > $ship['empty_bays']) {
		$colour = '#c00000';
	} elseif ($ship['cargo_bays']*0.25 > $ship['empty_bays']) {
		$colour ='#d6750e';
	}
	if ($colour) $ret_str .= "<font color=$colour><b>";
	$bay_storage = str_replace("'", "\\'", bay_storage($ship));
	if ($ship['cargo_bays']-$ship['empty_bays']) $ret_str .= "<span onmouseover=\"montre('$bay_storage');\" onmouseout=\"cache();\">";
	$ret_str .= "<b>".($ship['cargo_bays']-$ship['empty_bays']).'/'.$ship['cargo_bays']."</b>";
	if ($ship['cargo_bays']-$ship['empty_bays']) $ret_str .= "</span>";
	if ($colour) $ret_str .= '</b></font>';
	return $ret_str;
}



/********************
Fleet Functions
*********************/

//puts $do_ships (array) into $join_fleet_id.
function change_fleet_num ($join_fleet_id, $fleet_type, $do_ship, $class){
	global $user,$db_name, $cw, $st;

	if(empty($do_ship)){
		return $st[179]."<p />";
	} elseif($join_fleet_id < 0 || $join_fleet_id > 120){ //clan fleet id.
		return $st[180];
	} elseif($fleet_type == 1 && $user[clan_id] < 1){ //clan fleet id.
		return $st[181];
	} else {

		if($fleet_type == 1){ //clan fleet id.
			$sql_str_fleet = "clan_fleet_id";
			$fleet_type_str = $cw['clan_fleet'];

		} else { //user fleet
			$sql_str_fleet = "fleet_id";
			$fleet_type_str = $cw['fleet'];
		}


		$q_m = 0;

		//loops through the ships. Ensure user can only change their own ships, or those of a clan mate (seeing as only a clan-leader will be able to get to here anyway).
		foreach($do_ship as $var) {
			dbn("update ${db_name}_ships set $sql_str_fleet = '$join_fleet_id' where $class = '$var' && (login_id = '$user[login_id]' || (clan_id = '$user[clan_id]' && '$sql_str_fleet' = 'clan_fleet_id'))");
			$q_m += mysql_affected_rows();
		}

		return "<b>$q_m</b> $st[182] $fleet_type_str #<b>$join_fleet_id</b>.<p />";
	}
}


/*
This function will select fill as many ships in a fleet as possible with whatever is requested.

- 1st arguement sent to it is the sql name for whatever is to be loaded. (i.e. fighters)
- 2nd arguement is the name of the sql entry for the most of that material that any one ship can hold (i.e max_fighters).
- 3rd arguement contains the textual string (i.e. Fighters)
- 4th arguement holds the cost per unit of the item.
- 5th arguement is the name of the orginating script

*/
function fill_fleet($item_sql, $item_max_sql, $item_str, $item_cost, $cargo_run = 0){
	global $user, $user_ship, $db_name, $sure, $fill_dir, $cw, $st;

	$ret_str = "";
	$taken = 0; //item taken from earth far.
	$ship_counter = 0; //ships passed through

	if($cargo_run == 1){ //cargo
		$sql_max_check = $item_max_sql;
		$sql_where_clause = " location = '$user[location]' && login_id='$user[login_id]' && $item_max_sql > 0 ";
		$cargo_run = 1;

	} else {//not cargo
		$sql_max_check = "($item_max_sql - $item_sql)";
		$sql_where_clause = " location = '$user[location]' && login_id='$user[login_id]' && $item_max_sql > 0 && $item_sql < $item_max_sql ";
	}

	//elect all viable ships
	db("select sum($sql_max_check) as total_capacity, count(ship_id) as total_ships from ${db_name}_ships where ".$sql_where_clause);
	$maths = dbr(1);

	//insufficient cash
	if($user['cash'] < $item_cost){
		$ret_str .= sprintf($st[183],$item_str);
	} elseif(empty($maths) || $maths['total_ships'] < 1) { //ensure there are some ships.
		$ret_str .= sprintf($st[184],$item_str);
	} else {
		//work out the total value of them all.
		$total_cost = $maths['total_capacity'] * $item_cost;

		//user CAN afford to fill the whole fleet
		if($total_cost <= $user['cash']) {

			if(empty($sure)){ //confirmation
				get_var($cw['load_ships'],$_SERVER['PHP_SELF'],sprintf($st[185],$maths[total_capacity], $item_str, $maths[total_ships], $item_str),'sure','yes');
			} else { //process.
				dbn("update ${db_name}_ships set $item_sql = $item_max_sql where ".$sql_where_clause);
				take_cash($total_cost);

				if($cargo_run == 0){ //not cargo bay stuff
					$user_ship[$item_sql] = $user_ship[$item_max_sql];
				} else { //cargo bay stuff
					$user_ship[$item_sql] += $user_ship['empty_bays'];
				}

				$ret_str .= "<b>$maths[total_capacity]</b> <b class='b1'>$item_str</b> ".sprintf($st[186], $maths[total_ships]);
			}

		//user CANNOT afford to fill the whole fleet, so we'll have to do it the hard way.
		} else {
			$total_can_afford = floor($user['cash'] / $item_cost); //work out amount can afford.

			if(empty($sure)) { //confirmation
				$extra_text = "<p /><input type=radio name=fill_dir value=1 CHECKED /> - ".$st[187];
				$extra_text .= "<br /><input type=radio name=fill_dir value=2 /> - ".$st[188];
				get_var('Load ships',$_SERVER['PHP_SELF'],sprintf($st[189], $maths[total_capacity], $item_str, $maths[total_ships], $total_can_afford, $item_str, $extra_text),'sure','yes');
			} else { //process
				if($fill_dir == 1){
					$order_dir = "desc";
				} else {
					$order_dir = "asc";
				}

				if($total_can_afford < 1){ //error checking
					return $st[190];
				}

				$used_copy_afford = $total_can_afford; //make copy of the above.
				$final_cost = $item_cost * $total_can_afford; //work out the final cash cost of it all.
				$fill_ships_sql = ""; //intiate sql string to load a bunch of ships at once
				$temp_str = "";

				db2("select ship_id, $item_sql, $item_max_sql as max, ship_name from ${db_name}_ships where ".$sql_where_clause." order by $item_max_sql $order_dir");

				while($ships = dbr2(1)) { //loop through the ships

					$ship_counter++; //increment counter
					$free_space = $ships['max'] - $ships[$item_sql]; //capacity of present ship

					if($free_space < $used_copy_afford) { //can load ship
						$used_copy_afford -= $free_space; //num to use
						$fill_ships_sql .= "ship_id = '$ships[ship_id]' || ";

						$temp_str .= sprintf($st[191], $ships[ship_name], $item_str, $free_space);

						if($ships['ship_id'] == $user_ship['ship_id']){ //do the user ship too.
							if($cargo_run == 0){ //not cargo bay stuff
								$user_ship[$item_sql] = $user_ship[$item_max_sql];
							} else { //cargo bay stuff
								$user_ship[$item_sql] += $user_ship['empty_bays'];
							}
						}

					} else { //cannot load ship whole ship.
						dbn("update ${db_name}_ships set $item_sql = $item_sql + '$used_copy_afford' where ship_id = '$ships[ship_id]'");

						if($ships['ship_id'] == $user_ship['ship_id'] && $cargo_run == 0){ //do the user ship too.
							$user_ship[$item_sql] += $used_copy_afford;
						} elseif($ships['ship_id'] == $user_ship['ship_id']) { //cargo bay stuff
							$user_ship[$item_sql] += $used_copy_afford;
						}
						$temp_str .= sprintf($st[192], $ships[ship_name], $item_str, $used_copy_afford);
						break 1;
					}
				} //end of while

				$ret_str .= sprintf($st[193], $ship_counter, $item_str, $item_str, $item_str ,$total_can_afford, $final_cost, $temp_str);

				//update DB with fully loaded ships.
				if(!empty($fill_ships_sql)){
					$fill_ships_sql = preg_replace("/\|\| $/", "", $fill_ships_sql);
					dbn("update ${db_name}_ships set $item_sql = $item_max_sql where ".$fill_ships_sql);
				}
				take_cash($final_cost); //charge the cash
			}
		}
	}
	return $ret_str; //return the result string.
}


/********************
Get Information Functions
*********************/

//print clickable name of $player
function print_name($player) {
	global $db_name, $user_options, $PRINT_NAME_CACHE, $game_info, $cw, $st;

	//check to see if this user is already cached
	if(!empty($PRINT_NAME_CACHE[$player['login_id']])) {
		return $PRINT_NAME_CACHE[$player['login_id']];

	//no in cache
	} else {
		if (isset($player['login_id'])) {
			$tmp_query = mysql_query("select u.login_id,u.login_name,u.clan_id,u.clan_sym_color,u.clan_sym, pu.aim, pu.icq from ${db_name}_users u, user_accounts pu where u.login_id = '$player[login_id]' && pu.login_id = u.login_id");
			$player = mysql_fetch_array($tmp_query);
		} else {
			return NULL; //no login_id to work from.
		}

		if(empty($player)){ //there is no such player in the game.
			return NULL;
		}

		$temp_str = "<a href=\"player_info.php?target=$player[login_id]\"><b class=\"b1\">$player[login_name]</b></a>";

		//determine if user has clan sig
		if ($player['clan_id'] != 0 && $player['clan_sym']) {
			$temp_str .= "(<font color=\"$player[clan_sym_color]\">$player[clan_sym]</font>)";
		}

		if($player['login_id'] == OWNER_ID){
			$temp_str .= " (Server Admin)";
		} elseif($player['login_id'] == 1){
			$temp_str .= " - $game_info[admin_name]";
		}

		//determine if user has aim
		if($player['aim'] != '' && $user_options['show_aim'] == 1){
			$temp_str .= " - <a href=\"aim:goim?screenname=".urlencode($player['aim'])."&message=Hi+".urlencode($player['aim'])."+Are+you+there?\">AIM</a>";
		}
		//determine if user has icq
		if($player['icq'] != 0 && $user_options['show_icq'] == 1){
			$temp_str .= " - <a href=\"http://wwp.mirabilis.com/$player[icq]\" TARGET=\"_blank\"><img SRC=\"http://web.icq.com/whitepages/online?icq=$player[icq]&img=5\" BORDER=\"0\" /></a>";
		}

		$PRINT_NAME_CACHE[$player['login_id']] = $temp_str;
		return $temp_str;
	}
}



/********************
Star System Functions
*********************/

//Choose a system at random. can't be Sol or system with a black hole
function random_system_num() {
	global $db_name;
	db("select star_id from ${db_name}_stars where event_random != 1 && star_id > 1 order by RAND() LIMIT 1");
	$total = dbr();
	return $total[0];
}


// retrieve the star data
function get_star() {
	global $user, $star,$db_name;
	db("select * from ${db_name}_stars where star_id = '$user[location]'");
	return $star = dbr();
}


//get distance between stars $s1 and $s2
function get_star_dist($s1,$s2) {
	global $db_name;
	if(!isset($s1) || !isset($s2)){
		return 0;
	}
	db("select x_loc,y_loc from ${db_name}_stars where star_id = '$s1' || star_id = '$s2'");
	$star1 = dbr(1);
	$star2 = dbr(1);
	$dist = round(sqrt(abs(($star1['x_loc'] - $star2['x_loc'])*2) + abs(($star1['y_loc'] - $star2['y_loc'])*2)));
	return $dist;
}



/********************
Checking Functions
*********************/

//function to determine if a player is dead or not.
//will also determine if a player's ship
//returns true if alive and fine.
//returns false if dead but allowed on page anyway.
//otherwise prints page.
function ship_status_checker($allow_access = 0){
	global $user, $db_name, $rs, $GAME_VARS, $user_ship, $cw, $st;

	//check to see if the ship is engaged in battle.
	if($user_ship['ship_engaged'] > time()){
		print_page($cw['ship_occupied'],$st[194]);
	}

	//skip for the admins.
	if($user['login_id'] == 1 || $user['login_id'] == OWNER_ID) {
		return true;
	}

	//count the number of ships the user has.
	db("select count(ship_id) as ships from ${db_name}_ships where login_id = '$user[login_id]'");
	$numships = dbr(1);

	if($numships['ships'] > 0) { //player is alive.
		return true;
	}

	//game is in SD
	if($GAME_VARS['sudden_death'] == 1){
		//find out message info.
		db("select count(message_id) from ${db_name}_messages where login_id = '$user[login_id]'");
		$counted = dbr();
		if($counted[0] == 0){
			$rs = "<p />".$cw['no_messages'];
		} else {
			$rs = "<p /><a href='mpage.php'>You have <b>$counted[0]</b> ".$cw['messages']."</a>";
		}
		$rs .= "<br /><a href='forum.php'>".$cw['forum']."</a>";
		print_page($cw['sudden_death'],$st[195]);

	//player is allowed access to this page, even though is dead.
	} elseif($allow_access == 1) {
		return false;

	//access to this page is denied without a ship
	} else {
		print_page($cw['sudden_death'],$st[196]);
	}
}

//function to determine if $sent_id is available for purchase.
function avail_check($sent_id){
	global $db_name, $user, $GAME_VARS;
	if($GAME_VARS['alternate_play_2'] > 0 && $user['login_id'] != 1){
		db("select ${db_name}_available from se_development_time where item_id = '$sent_id'");
		$ret_id = dbr();
		return $ret_id[0];
	} else {
		return 1;
	}
}


/********************
Upgrade options
*********************/

//a function to allow for easy addition of upgrades.
function make_standard_upgrade ($upgrade_str, $config_addon, $cost, $developement_id, $tech_cost = 0){
	global $user, $user_ship, $db_name, $cw, $st;
	if($user['cash'] < $cost) {
		return sprintf($st[197], $upgrade_str);
	} elseif($user['tech'] < $tech_cost && $tech_cost > 0) {
		return $st[198];
	}elseif(!avail_check($developement_id)){
		return sprintf($st[199],$upgrade_str);
	} elseif (config_check($config_addon, $user_ship)){
		return sprintf($st[600], $upgrade_str);
	} elseif ($user_ship['upgrade_slots'] < 1){
		return "";
	} else {
		take_cash($cost);
		take_tech($tech_cost);
		$user_ship['config'] .= ",".$config_addon;
		dbn("update ${db_name}_ships set config = '$user_ship[config] ', upgrade_slots = upgrade_slots - 1 where ship_id = '$user[ship_id]'");
		$user_ship['upgrade_slots'] --;

		return "<b class='b1'>$upgrade_str</b>, ".sprintf($st[601],$user_ship[ship_name], $cost)."<p />";
	}
}

//function that creates a list of checkbox ready ships.
//command == 0: No link; Command == 1: Command link; Command==2: Unload link
function checkbox_ship_list($select_sql, $command_option = 0){
	global $user, $user_ship, $planet_id, $type, $cw, $st, $table_head_array, $db_name;
	db2($select_sql);
	$ships = dbr2(1);
	$ret_str = ""; $plocation = 0;

	if(empty($ships)){
		return -1;
	} else {
		$i=0;
		$last_fleet = '';
		while($ships){
			$ship_cargo = "";
			$ships['fighters'] = $ships['fighters']." / ".$ships['max_fighters'];
			$ships['shields'] = $ships['shields']." / ".$ships['max_shields'];
			$ships['armour'] = $ships['armour']." / ".$ships['max_armour'];
			unset ($ships['max_fighters'], $ships['max_shields'], $ships['max_armour']);

			$ships['ship_name'] = popup_help("ship_info.php?s_id=$ships[ship_id]", 320, 520, $ships['ship_name']);


			//Si db_name est inexistant, la popup n'est pas affichée.
			if($db_name)
			{
			$ships['class_name_abbr'] = popup_help("help.php?popup=1&ship_info=$ships[shipclass]&db_name=$db_name",300,600, $ships['class_name_abbr']);
			}

			if(empty($ships['config'])){
				$ships['config'] = $cw['none'];
			} else {
				$ships['config'] = config_list(0, $ships['config']);
			}
			if($command_option == 1){


				if ($ships['ship_id'] == $user_ship['ship_id']) {
					$bgcolor = '000000';
					array_push($ships,"<i>".$cw['aux_commandes']."</i>");   
				} else {
					$bgcolor = '333333';
					array_push($ships,"<a href='location.php?command=$ships[ship_id]'>".$cw['command']."</a>");
				}



			} elseif($command_option == 2){
				array_push($ships,"<a href='planet.php?planet_id=$planet_id&amp;chosen_ship=$ships[ship_id]&amp;single_ship_deal=1&amp;type=$type'>".$cw['load/unload']."</a>". $cw['ship']);
			}
			$ships['cargo_bays'] = bay_storage_little($ships);
			unset($ships['metal'],$ships['fuel'],$ships['elect'],$ships['colon']);
			$ships['ship_id'] = "<input type='checkbox' name='do_ship[$ships[ship_id]]' value='$ships[ship_id]' />";
			//$bgcolor = ($i % 2 == 0) ? '444444':'333333';

			// if the location is different of the previous
			if ($plocation && $plocation != $ships['location'] && $command_option == 1) {
				$ret_str .= "</table><br /><h2>Système " . $ships['location'] . "</h2>";
				$ret_str .= make_table($table_head_array);
			} elseif (!$plocation) {
				// if this is the first ship listed
				if ($command_option == 1) $ret_str .= "<h2>Système " . $ships['location'] . "</h2>";
				$ret_str .= make_table($table_head_array);
			}
			// suppression du champ ship class (utilisé pour la popup)
			unset($ships['shipclass']);
			// previous location
			$plocation = $ships['location'];

			
			$rowspan = array(1,1,1,1,1,1,1,1,1,1,1,1);
			//var_dump($ships);
			if($last_fleet != $ships['fleet_id']){
						
				//calcul du nombre de vaisseaux dans la flotte actuelle
				db("select count(ship_id) from ${db_name}_ships where login_id = ".$user['login_id']." AND fleet_id = ".$ships['fleet_id']." AND location = ".$ships['location']);
				
				$rowspan_count = dbr();

				//rowspan[4] = nombre de vaisseaux dans la flottte ($rowspan_count[0];)
				$rowspan['fleet_id'] = $rowspan_count[0];
						
				$last_fleet = $ships['fleet_id'];
			}else
			{
				unset($ships['fleet_id']);
			}


			if ($command_option == 2) unset($ships['location']);
			$ret_str .= "\n".make_row($ships,$bgcolor,$rowspan);
			$i++;
			$ships = dbr2(1);
		}
		$ret_str .= "</table><br />";
		return $ret_str;
	}
}


/*******************
Exploring Functions
*******************/

//function that randomly scrambles the explored systems, so the user may loose some of their mapping information.
function scramble_explored (&$target){
	global $db_name, $cw, $st;
	$new_array = array();

	if($target['explored_sys'] != -1){

		//loop through all system explored (if not explored whole universe).
		foreach(explode(",", $target['explored_sys']) as $value){

			//delete value if not sys 1, not present location, and randomly says so.
			if($value == 1 || $value == $target['location'] || mt_rand(0,6) < 4){
				$new_array[] = $value;
			}
		}

	//user has explored whole universe, so going to have to do this the hard way. :)
	} else {
		db2("select count(star_id) from ${db_name}_stars");
		$total_num_stars = dbr2();

		//loop through all existing system
		for($i = 1; $i <= $total_num_stars[0]; $i++){

			//delete value if not sys 1, not present location, and randomly says so.
			if($i == 1 || $i == $target['location'] || mt_rand(0,6) < 4){
				$new_array[] = $i;
			}
		}
	}

	$target['explored_sys'] = implode(",",$new_array);
	dbn("update ${db_name}_users set explored_sys = '$target[explored_sys]' where login_id = '$target[login_id]'");
}


//function that sets a system as explored, if it is not already.
function explore_sys (&$user, $loc){
	global $db_name, $game_info, $GAME_VARS, $cw, $st;

	if($user['explored_sys'] == -1 || $GAME_VARS['uv_explored'] == 1){//explored whole universe.
		dbn("update ${db_name}_users set location = '$loc' where login_id = '$user[login_id]'");
		return 0;
	}

	$exp_sys = explode(",", $user['explored_sys']);

	//new system explored
	if($GAME_VARS['uv_explored'] == 0 && $loc > 1 && $loc <= $game_info['num_stars'] && array_search($loc, $exp_sys) === false){
		if(count($exp_sys) + 1 >= $game_info['num_stars']){//just finished exploring whole universe.
			$user['explored_sys'] = -1;
		} else {
			$exp_sys[] = $loc;
			$user['explored_sys'] = implode(",",$exp_sys);
		}
		dbn("update ${db_name}_users set explored_sys = '$user[explored_sys]', location = '$loc' where login_id = '$user[login_id]'");
	} else {//already explored
	if(count($exp_sys) >= $game_info['num_stars']){}
		dbn("update ${db_name}_users set location = '$loc' where login_id = '$user[login_id]'");
	}
}

$midx = $GAME_VARS['uv_size_x_width']/2;
$midy = $GAME_VARS['uv_size_y_height']/2;
?>
