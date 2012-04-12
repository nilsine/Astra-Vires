<?php
require_once("user.inc.php");

//set vars to 0 if not set or empty.

//set clan id to whatever it's requested as, for admins, or when sending a mess to clanmates
if(isset($_REQUEST['clan_id']) && (($user['login_id'] == 1 || $user['login_id'] == OWNER_ID && $target_id == -5) || $target_id == -2)){
	$clan_id = (int)$_REQUEST['clan_id'];

//set clan_id to players clan id
} elseif($user['clan_id'] > 0) {
	$clan_id = $user['clan_id'];

} else { //no clan
	$clan_id = 0;
}

$target_id = isset($_REQUEST['target_id']) ? (int)$_REQUEST['target_id'] : $target_id = 0;

$text = !empty($_POST['text']) ? (string)$_POST['text'] : $text = 0;

// message checks
if($target_id == -2 && $clan_id <= 0) {
	print_page($cw['send_clan_message'],$st[895]);
} elseif($target_id == -2 && $clan_id != $user['clan_id']) {
	print_page($cw['send_clan_message'],$st[896]);
} elseif($target_id == -2 && $user['clan_id'] < 1) {
	print_page($cw['send_clan_message'],$st[897]);
} elseif($target_id == -4 && $user['login_id'] != 1 && $user['login_id'] != OWNER_ID) {
	print_page($cw['send_mass_message'],$st[898]);
} elseif($target_id == -5 && $user['clan_id'] < 1 && $user['login_id'] != 1 && $user['login_id'] != OWNER_ID) {
	print_page("Clan Forum",$cw[899]);
} elseif($target_id == -99 && $user['login_id'] != 1 && $user['login_id'] != OWNER_ID) {
	print_page($cw['admin_forum'],$st[898]);
} elseif(empty($_POST['text']) || isset($_POST['preview'])) {

	$people_can_mess = $cw['enter_your_message_to'] . ' ';

	if($target_id > 0){ //message to another player.
		db("select login_name,login_id,clan_sym,clan_sym_color from ${db_name}_users where login_id = '$target_id'");
		$rec = dbr(1);
		$people_can_mess .= print_name($rec);
	} elseif($target_id== -1){
		$people_can_mess .= $st[900];
	} elseif($target_id== -2){
		$people_can_mess .= $st[901];
	} elseif($target_id== -4){
		$people_can_mess .= $st[902];
	} elseif($target_id== -5){
		$people_can_mess .= $st[903];
	} elseif($target_id== -50){
		$people_can_mess .= $st[904];
	} elseif($target_id== -99){
		$people_can_mess .= $cw['admin_forum'];

	} else { #user has not specified a target, so list all the players who can be messaged

		$people_can_mess = $st[905]."<p />\n<select name=target_id>";

		db("select login_name,login_id,clan_sym from ${db_name}_users where login_id != '$user[login_id]' && (login_id > 5 || login_id = 1) order by login_name asc");

		while($dest = dbr(1)){
			if($dest['clan_sym']) {
				$sym_txt = " ($dest[clan_sym])";
			} else {
				$sym_txt = "";
			}
			$people_can_mess .= "\n<option value='$dest[login_id]'> $dest[login_name]".$sym_txt;

		}
		$people_can_mess .= "\n</select>";
	}

	$page_str = "";
	$data_for_textbox = "";
	$loadable_mess_sql = " (login_id = -1 || login_id = '$user[login_id]' || (login_id = -5 && clan_id = '$clan_id'))";

	if(isset($_REQUEST['forward'])) {//get forwarded message
		db("select text from ${db_name}_messages where message_id = '".(int)$_REQUEST['forward']."' && ".$loadable_mess_sql);
		$forward_message = dbr(1);
		$data_for_textbox = "\n\n\n\n\n\n$st[906]:\n\n\"$forward_message[text]\"";

		$page_str = $people_can_mess;

	} elseif(isset($_REQUEST['reply_to'])){ //a reply to a message, so get reply text
		db("select text from ${db_name}_messages where message_id = '$reply_to' && ".$loadable_mess_sql);
		$reply_to = dbr(1);
		$page_str = $st[906]." :<br /><blockquote><hr><br />".nl2br($reply_to['text'])."<br /><hr></blockquote><br />";

	} elseif(!isset($_POST['preview'])) { //plain normal message.
		$page_str = $people_can_mess;

	}

	//can be previewing and forwarding/replying, so not part of above if structure.
	if(isset($_POST['preview'])) { //previewing
		$prev = mcit($text);
		$page_str .= $people_can_mess."<p />".$st[907].": <p /><hr width=75% align=left><blockquote>$prev</blockquote><hr width='75%' align='left'><br />";
		$data_for_textbox = $text;

		//unset so don't end up back at the preview when clicking submit
		unset($_POST['preview'], $_POST['text']);
	}

	get_var('Send Message','message.php',$page_str,'msg',$data_for_textbox);

} else { //done with the text
	$text = mcit($text);

}

//send messages
// if there are more than 100 characters in a line with no spaces, then complain.
	if(preg_match("/\S{100,}/", $text)){
		print_page($cw['error'],$st[908]." ;-)");
	}

//send a message to each of the clan mates.
if($target_id==-2) {
	db2("select login_id from ${db_name}_users where clan_id='$clan_id' && clan_id > 0");
	$target_member = dbr2(1);
	while($target_member) {
		send_message($target_member['login_id'],$text);
		send_templated_email($target_member['login_id'], 'message');
		$target_member = dbr2(1);
	}
	$error_str = $st[909];


//send message to all players in the game
} elseif($target_id==-4) {
	$error_str = message_all_players($text,$db_name, $st[910],"<b class=\"b1\">".$cw['admin']."</b>");


//send message to clan forum
} elseif($target_id==-5 && $clan_id > 0) {
	send_message($target_id,$text, $clan_id);
	$error_str = $cw['message_sent'].".";

//send message to central forum
} elseif($target_id == -99 || $target_id == -50){

	if($user['login_id'] == OWNER_ID){ //server admin
		$sender_name = "<b class=\"b1\">".$p_user['login_name']."</b> (".$cw['server_admin'].")";

	} elseif($user['login_id'] == 1) { //admin
		$sender_name = "<b class=\"b1\">".$cw['admin']."</b> - ($game_info[admin_name] - $game_info[name])";

	} else {//regular player
		$sender_name = "<b class=\"b1\">".$p_user['login_name']."</b> - $game_info[name]";
	}
	dbn("insert into se_central_messages (timestamp, forum_id, sender_id, sender_name, text, game_id) values(".time().", '$target_id', '$user[login_id]', '$sender_name', '".mysql_escape_string(substr($text, 0, 5000))."', '$game_info[game_id]')");
	$error_str = $st[911];

} else {
	send_message($target_id,$text);
	send_templated_email($target_id, 'message');
	$error_str = $st[912];
}


if($target_id == -1 || $target_id == -50) {
	$error_str .= "<br /><br /><a href='forum.php?target_id=$target_id'>".$cw['back_forum']."</a>";
} elseif($target_id == -2) {
	$error_str .= "<br /><br /><a href='clan.php'>".$st[913]."</a>";
} elseif($target_id == -5) {
	$error_str .= "<br /><br /><a href='forum.php?target_id=-5&amp;clan_id=$clan_id'>".$st[914]."</a>";
} elseif($target_id != -99) {
	$error_str .= "<br /><br /><a href='mpage.php'>".$st[915]."</a>";
}

// print page
print_page($cw['send_message'],$error_str);
?>