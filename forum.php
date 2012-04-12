<?php
/************
* Shows all of the forums (globals and game).
* Last major re-write - May 2003
* Last audited: 23/5/04 by Moriarty
*************/


require_once("user.inc.php");

if(isset($_REQUEST['target_id'])){
	$target_id = (int)$_REQUEST['target_id'];
} else {
	print_page("","Stop messing around.");
}


//admin powers
if ($user['login_id'] == 1 || $user['login_id'] == OWNER_ID) {
	$admin_powers = 1;
	!empty($_REQUEST['clan_id']) ? $clan_id = (int)$_REQUEST['clan_id'] : $clan_id = 0;

} else { //mere mortals
	$admin_powers = 0;

	if($target_id == -5){//set clan_id to 0 if not looking at clan forums.
		$clan_id = $user['clan_id'];
	} else {
		$clan_id = 0;
	}
}


$out = "";
$forum_type_url_str = "";
$clan_text_sql = "";

/*******************
* Clan Forum Precursers
*******************/
if($target_id == -5 && ($user['clan_id'] > 0 || $admin_powers == 1)){
	$header = "Clan Forum";
	$forum_id = -5;
	$col_arr = array('#1F2F3A', '#0F383F', '#102830', '#3F4F5A');

	//admin selecting a clan forum to look at
	if(isset($_POST['look_at']) && $admin_powers == 1){
		$clan_id = (int)$_POST['look_at'];
	}

	if($admin_powers == 1){//list forums admin can look at.
		$forum_type_url_str = "&amp;clan_id=$clan_id";

		db("select clan_name,clan_id from ${db_name}_clans order by clan_name");
		$clans=dbr(1);
		if(!empty($clans)){

			//just in case there are clans in a no-clan game (admin can create them despite the rules)
			if($GAME_VARS['clans_max'] < 5){
				$GAME_VARS['clans_max'] = 5;
			}

			//fill array with nothings, so don't get error messages (lvl notice)
			$selected = array_fill(0, $GAME_VARS['clans_max'], "");
			$selected[$clan_id] = " selected";
			$out .= "Select a clan forum to Monitor";
			$out .= "<FORM action='forum.php' method='post'>";
			$out .= "<input type='hidden' name='target_id' value='-5' />";
			$out .= "<select name='look_at'>";
			while($clans){
				$out .= "<option value='$clans[clan_id]'".$selected[$clans['clan_id']].">$clans[clan_name]";
				$clans=dbr(1);
			}
			$out .= "</select>";
			$out .= " - <input type='submit' value='Monitor' /></form><p />";
		} else {
			$out .= "There are no clans in this game at present.";
			print_page("Clan Forum",$out);
		}
		if($clan_id == 0){
			print_page("Clan Forum", $out);
		}
	} else {//update last lookup time for player looking at clan forum
		dbn("update ${db_name}_users set last_access_clan_forum='".time()."' where login_id = '$user[login_id]'");
	}

	if($forum_id == -5 && $clan_id != 0){ //welcome message to forum.
		db("select clan_name,sym_color from ${db_name}_clans where clan_id = '$clan_id'");
		$clan_name=dbr(1);
		$out .= "Welcome to the <font color='$clan_name[sym_color]'>$clan_name[clan_name]</font> Clan Forum.";

		//keep track of who admin is looking at.
		if($user['login_id'] == 1 && $_POST['look_at']){
			insert_history($user['login_id'], "Viewed info for clan $clan_name[clan_name]");
		}
	}

/*******************
* Admin Forum Precursers
*******************/
} elseif($target_id == -99 && $admin_powers == 1){
	$header = "Admin Forum";
	$forum_id = -99;
	$col_arr = array(0 => '#332222', 1 => '#44334F', 2 => '');

	if($user['login_id'] == 1){ //admin update of link
		dbn("update se_games set last_access_admin_forum = '".time()."' where db_name = '$db_name'");
		$game_info['last_access_admin_forum'] = time();
	} else { //server admin update - uses icq num.
		dbn("update user_accounts set icq = '".time()."' where login_id = '$p_user[login_id]'");
		$p_user['icq'] = time();
	}

/*******************
* Global Forum Precursers
*******************/
} elseif($target_id == -50){
	$header = "Global Forum";
	$forum_id = -50;
	$col_arr = array(0 => '#003000', 1 => '#113322');

	dbn("update user_accounts set last_access_global_forum = '".time()."' where login_id = '$p_user[login_id]'");



/*******************
* Regular Forum Precursers
*******************/
} elseif($target_id == -1) {
	$header = "Shoutbox";
	$forum_id = -1;
	$col_arr = array('#444444', '#282828', '#202020', '#888888');
	dbn("update ${db_name}_users set last_access_forum='".time()."' where login_id = '$user[login_id]'");
    
    
    $out .= '<div id="derniers_messages">' . make_table2(array('Derniers messages<br />du forum'));

    $phpbb_root_path = 'forum/';
    $sql = 'SELECT p.*, t.* FROM phpbb_posts p JOIN phpbb_topics t ON p.topic_id = t.topic_id GROUP BY t.topic_id ORDER BY p.post_id DESC LIMIT 12';
    db($sql);
    $messages = '';
    while ($row = dbr()) {
        if (strlen($row['topic_title']) > 40) $row['topic_title'] = substr($row['topic_title'], 0, 37) . '...';
        $messages .= '<a href="' . $phpbb_root_path . 'viewtopic.php?t=' . $row['topic_id'] . '" target="_blank">' . ucfirst($row['topic_title']) . '</a><br />';
    }


    $out .= q_row($messages, 'l_col');

    //end the left-bar cell
    $out .= "\n</table></div>";
    
	// facebook like box
	$out .= '<iframe src="http://www.facebook.com/plugins/likebox.php?id=151362804883979&amp;width=292&amp;connections=10&amp;stream=false&amp;header=false&amp;height=255" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:292px; height:255px; background-color: #FFFFFF;" allowTransparency="false"></iframe>';
    $out .= '<div class="spacer"></div>';
} else { //messing around
	print_page("Dunno","You've been messing with things you don't understand again, havn't you?");
}


/*******************
* Message Deletion
*******************/
if(isset($_GET['killmsg']) && $admin_powers == 1) {
	if($forum_id == -50){//deleting from central forum
		dbn("delete from se_central_messages where message_id = '".(int)$_GET['killmsg']."' && forum_id = '$forum_id'");

	} elseif($forum_id > -50){ //deleting from regular forum
		dbn("delete from ${db_name}_messages where message_id = '".(int)$_GET['killmsg']."' && login_id = '$forum_id'");
	}
	$out .= "Message Deleted";
}

//delete all messages in a forum. can't be done for central forums
if(isset($_REQUEST['killallmsg']) && $admin_powers == 1 && $forum_id > -50) {
	if(!isset($_POST['sure'])) {
		get_var('Delete Messages','forum.php',"Are you sure you want to delete all $header messages?",'sure','yes');
	} else {
		if($forum_id == -5){
			$clan_text_sql = " && clan_id = '$clan_id'";
		}
		dbn("delete from ${db_name}_messages where login_id = '$forum_id'".$clan_text_sql);
		$out .= mysql_affected_rows()." Messages Deleted.<p />";
	}
}

if(($forum_id == -5 && $clan_id != 0) || $forum_id != -5){
	$out .= $rs."<a href='message.php?target_id=$forum_id{$forum_type_url_str}'>Participer sur la $header</a><p />";
}

$out .= print_messages($forum_id, $admin_powers, $clan_id, $col_arr);

//can't kill all messages in central forum.
if($admin_powers == 1 && (($forum_id == -5 && $clan_id != 0) || ($forum_id !=-5 && $forum_id > -50))) {
	$out .= "<p /><a href='forum.php?target_id=$forum_id&killallmsg=1{$forum_type_url_str}'>Delete All Forum Messages</a>";
}

print_page($header, $out);

?>