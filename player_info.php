<?php
require("user.inc.php");

ship_status_checker();

if(!isset($target)){
	$target=$user['login_id'];
}

$text = "";

#History of players actions.
if(isset($history) && $history > 0){
	if(!isset($action_show)){
		$action_show = 200;
	}

	//admin can see all, except own. server op can see all.
	if(($user['login_id'] == 1 && $history != 1) || $user['login_id'] == OWNER_ID){
		$rs="<a href='player_info.php?target=$history'>".$cw['back_player_info']."</a><br /><br />";
		$sec_sort = "";

		//a direction to the history.
		if(isset($sorted_history) && $sorted_history == 1){
			$going = "asc";
			$sorted_history=2;
		} else {
			$going = "desc";
			$sorted_history=1;
		}

		//user not yet chosen to sort the history
		if(empty($sort_history) || $sort_history == "time"){
			$sql_sort = "timestamp $going";
		} elseif($sort_history == "game") {
			$sql_sort = "game_db $going, timestamp desc";
		} elseif($sort_history == "action") {
			$sql_sort = "action $going, timestamp desc";
		} elseif($sort_history == "IP") {
			$sql_sort = "user_IP $going, timestamp desc";
		} elseif($sort_history == "other") {
			$sql_sort = "other_info $going, timestamp desc";
		}

		$sql_game_select = "";
		$games_links = "";
		//user only wants to list history from a certain game.
		if(!empty($select_game)){
			$sql_game_select = " && game_db = '$select_game' ";
			$games_links .= "<br /><a href='$_SERVER[PHP_SELF]?history=$history&select_game='>".$cw['all_games']."</a>";
		} else {
			$select_game = "";
		}

		//only the server op can see all of an admins details.
		if(($user['login_id'] == OWNER_ID && OWNER_ID != 0) || ($user['login_id'] == 1 && $history != 1 && $history != OWNER_ID) || $user['login_id'] == $history){
			$to_select = "timestamp,game_db,action,user_IP,other_info";
			$is_full = 1;
		} else {
			$to_select = "timestamp,game_db,action";
			$is_full = 0;
		}

		db("select ".$to_select." from user_history where login_id = '$history' $sql_game_select order by $sql_sort LIMIT $action_show");

		if($user['login_id'] == 1 || ($user['login_id'] == OWNER_ID && OWNER_ID != 0)){
			//get all the games the user has been in.
			db2("select game_db from user_history where login_id = '$history' group by game_db");
			while($games_in = dbr2()){
				$games_links .= "\n<br /><a href='$_SERVER[PHP_SELF]?history=$history&select_game=$games_in[game_db]'>$games_in[game_db]</a>";
			}
		}
		$text = $rs;
		if(!empty($games_links)){
			$text .= $st[1000].":".$games_links."<p />";
		}

		$text .= sprintf($st[1500],$action_show);
		$text .= make_table(array("<a href='player_info.php?history=$history&sort_history=time&sorted_history=$sorted_history&select_game=$select_game'>".$st[1501]."</a>","<a href='player_info.php?history=$history&sort_history=game&sorted_history=$sorted_history&select_game=$select_game'>".$cw['game']."</a>","<a href='player_info.php?history=$history&sort_history=action&sorted_history=$sorted_history&select_game=$select_game'>".$cw['entry']."</a>","<a href='player_info.php?history=$history&sort_history=IP&sorted_history=$sorted_history&select_game=$select_game'>IP</a>","<a href='player_info.php?history=$history&sort_history=other&sorted_history=$sorted_history&select_game=$select_game'>".$cw['other']."</a>"));
		while($hist = dbr()){
			if($is_full == 0){
				$hist['user_IP'] = "";
				$hist['other_info'] = "";
			}
			$text .= make_row(array("<b>".date("M d - H:i",$hist['timestamp']),$hist['game_db'],$hist['action'],$hist['user_IP'],$hist['other_info']));
		}
		$text .= "</table><br />";
		print_page($cw['account_history'],$text);
	} else{
		print_page($cw['account_history'],$st[1502]);
	}
}

// transfer cash
if(isset($transfer)) {

	if($sure != 'yes') {
		get_var($st[1503],'player_info.php',sprintf($st[1504], $trans_amount, $trans_target),'sure','yes');
	} else {
		settype($trans_amount, "integer");
			db2("select * from user_accounts where login_id = '$trans_target_id' LIMIT 1");
			$user_transferto = dbr2();
			db2("select * from user_accounts where login_id = '".$user['login_id']."' LIMIT 1");
			$user_transferfrom = dbr2();
		if ($trans_amount<=0) {
			print_page($cw['transfer_error'],$st[1505]."<br /><a href='javascript:back()'>".$cw['go_back']."</a><br />");
		} elseif ($user['cash'] < $trans_amount) {
			print_page($cw['transfer_error'],$st[1506]."<br /><a href='javascript:back()'>".$cw['go_back']."</a><br />");
		} elseif ($user['joined_game'] > (time() - ($min_before_transfer * 86400)) && $user['login_id'] > 1) {
			print_page($cw['transfer_error'],sprintf($st[1507], $GAME_VARS[min_before_transfer])." <br /><a href='javascript:back()'>".$cw['go_back']."</a><br />");
		} elseif ($user_transferfrom['last_ip'] == $user_transferto['last_ip']) {
			print_page($cw['transfer_error'],sprintf($st[1890], $user_transferto['login_name']));
		} else {
			take_cash($trans_amount);
			dbn("update ${db_name}_users set cash = cash + $trans_amount where login_id = '".$trans_target_id."'");
			send_message($trans_target_id,"<b class='b1'>$user[login_name]</b> ".$st[1508]." <b>$trans_amount</b> ".$cw['credits'].".");
			insert_history($user['login_id'],sprintf($st[1509],$trans_amount,$trans_target));
			print_page($cw['transfer_complete'],sprintf($st[1510],$trans_amount)." <b class='b1'>$trans_target</b>.");
		}
	}
}

db("select u.*, pu.*, pu.login_name as generic_l_name, u.login_name as login_name from ${db_name}_users u, user_accounts pu where u.login_id = '$target' && pu.login_id = u.login_id");
$player = dbr();

#used to calculate percentages
db("select sum(cash) as cash, sum(fighters_killed) as fighters_killed, sum(fighters_lost) as fighters_lost, sum(score) as score, sum(tech) as tech, sum(ships_killed) as ships_killed, sum(ships_lost) as ships_lost, sum(ships_killed_points) as ships_killed_points, sum(ships_lost_points) as ships_lost_points, sum(turns_run) as turns_run, sum(turns) as turns, sum(game_login_count) as game_login_count, sum(approx_value) as approx_value from ${db_name}_users where login_id > '5'");
$all_player = dbr();

# Won't display alien or pirate information, or the other two reserved accounts.
# they have login_id 2 or 3, or 4,5 for the reserved ones.
# Minimum login id is 0 (or 1, but then as admin gets 1, it must be 2 for players cos its auto increment).
if($target < 6){
	$special_show = 1;
	if($user['login_id'] == 1 && $target == 1){
		$full = 1;
	} else {
		$full = 0;
	}
} elseif($target == $user['login_id'] || $target == 1 || ($user['clan_id'] == $player['clan_id'] && $user['clan_id'] > 0) || $user['login_id'] == 1 || $user['login_id'] == OWNER_ID) { #admin can see all, but not aliens/pirates
	$full = 1;
} else { #if none of the above are true, then a more limited view is given.
	$full = 0;
}

//keep track of who admin is looking at.
if($user['login_id'] == 1){
	insert_history($user['login_id'], $st[1511]." $player[login_name]");
}

$text .= "<a href='message.php?target_id=$target'>".$cw['send_message_to']." $player[login_name]</a><br /><br />";

$text .= make_table(array("",""));
$text .= quick_row($cw['game_name'],print_name($player));
if($full == 1 || isset($special_show)) {
	$text .= quick_row($cw['login_name'],$player['generic_l_name']);
	if(isset($special_show)){
		$text .= quick_row($st[875],$player['real_name']);
		$text .= quick_row($cw['purpose'],"$player[email_address]");
	} else {
		$text .= quick_row($cw['real_name'],$player['real_name']);
		$text .= quick_row($cw['email_address'],"<a href='mailto:$player[email_address]'>$player[email_address]</a>");
	}

	if($user['login_id'] == OWNER_ID && OWNER_ID != 0){
		$text .= quick_row("&nbsp;","");
		$text .= quick_row($st[1512],$player['login_count']);
		$text .= quick_row($st[1513],$player['page_views']);
	}
	$text .= quick_row("&nbsp;","");
	$text .= quick_row($cw['joined_game'],date( "M d - H:i",$player['joined_game']));
	$text .= quick_row($cw['last_page_request'],date( "M d - H:i:s",$player['last_request']));
	$text .= quick_row($cw['login_count'],calc_perc($player['game_login_count'],$all_player['game_login_count']));
	$text .= quick_row($st[1514],$player['last_ip']);
	$text .= quick_row($st[1515],$player['num_games_joined']);
	$text .= quick_row("&nbsp;","");
	db("select count(ship_id) from ${db_name}_ships where login_id = '$target'");
	$ship_count = dbr();
	db("select count(ship_id) from ${db_name}_ships where login_id > 5");
	$ship_count_all = dbr();

	$text .= quick_row($cw['ship_count'],calc_perc($ship_count[0],$ship_count_all[0]));
	$text .= quick_row($cw['cash'],calc_perc($player['cash'],$all_player['cash']));
	$text .= quick_row($st[1516],calc_perc($player['approx_value'],$all_player['approx_value']));
	if($GAME_VARS['uv_num_bmrkt'] > 0){
		$text .= quick_row($st[1517],calc_perc($player['tech'],$all_player['tech']));
	}
	$text .= quick_row($cw['turns'],calc_perc($player['turns'],$all_player['turns']));
}

$text .= quick_row($cw['turns_run'],calc_perc($player['turns_run'],$all_player['turns_run']));
$text .= quick_row("&nbsp;","");

$text .= quick_row($st[999], calc_perc($player['ships_killed'],$all_player['ships_killed']));
$text .= quick_row($st[998], calc_perc($player['ships_lost'],$all_player['ships_lost']));
$text .= quick_row($st[1518], calc_perc($player['ships_killed_points'], $all_player['ships_killed_points']));
$text .= quick_row($st[1519],calc_perc($player['ships_lost_points'],$all_player['ships_lost_points']));
$text .= quick_row($cw['fighters_killed'],calc_perc($player['fighters_killed'],$all_player['fighters_killed']));
$text .= quick_row($cw['fighters_lost'],calc_perc($player['fighters_lost'],$all_player['fighters_lost']));
$text .= quick_row($st[1520],num_flagships($player['one_brob']));

if($GAME_VARS['score_method'] != 0){
	db("select count(login_id) from ${db_name}_users where score > '$player[score]' && login_id > 5");
	$score_front = dbr();
	db("select count(login_id) from ${db_name}_users where login_id > 5");
	$score_back = dbr();

	$score_front[0]++;
	$text .= quick_row($cw['score'],$player['score']." ($score_front[0] of $score_back[0])");
}

if($player['last_attack'] <= 1){
	$text .= quick_row($st[1521],$st[1522]);
	$player['last_attack_by'] = " -";
} else {
	$text .= quick_row($st[1521],date( "M d - H:i",$player['last_attack']));
}

$text .= quick_row($st[1523],$player['last_attack_by']);


if($full == 1) {
	$text .= quick_row("&nbsp;","");
	$text .= quick_row($st[1524],$player['genesis']);
	if($GAME_VARS['bomb_flag'] < 2){
		$text .= quick_row($st[1525],$player['alpha']);
		$text .= quick_row($st[1526],$player['gamma']);
		$text .= quick_row($st[1527],$player['delta']);
	}
}

$text .= quick_row("&nbsp;","");
if($player['aim'] != ''){
	$text .= quick_row("AIM SN","<a href=\"aim:goim?screenname=$player[aim]&message=Hi+$player[aim].+Are+you+there?\">$player[aim]</a>");
}
//onwer uses icq num to store last viewing of admin forum. so don't show.
if(!$player['icq'] == '0' && $target != OWNER_ID){
	$text .= quick_row("ICQ #","<a href=\"http://wwp.mirabilis.com/$player[icq]\" TARGET=\"_blank\">$player[icq]</a>");
}
if($player['msn'] != ''){
	$text .= quick_row("MSN SN","$player[msn]");
}
if($player['yim'] != ''){
	$text .= quick_row("YIM SN","$player[yim]");
}


//show bug listing information
$bug_status = array_fill(31, 5, 0);
$bug_status['outstanding'] = 0;
$bug_status[10] = 0;

db("select count(STATUS) as num, status from server_issuetracking where login_id = '$target' group by STATUS");
while($temp_status_bugs = dbr(1)){
	if(preg_match("/^2[0-9]/", $temp_status_bugs['status'])){ //outstanding bug
		$bug_status['outstanding'] += $temp_status_bugs['num'];
	} else {
		$bug_status[$temp_status_bugs['status']] = $temp_status_bugs['num'];
	}
}

$text .= quick_row("&nbsp;","");
$text .= quick_row($st[1528],array_sum($bug_status));
if(array_sum($bug_status) > 0){
	$text .= quick_row(	$st[1529],$bug_status['10']);
	$text .= quick_row($st[1530],$bug_status['outstanding']);
	$text .= quick_row($st[1531],$bug_status['31']);
	$text .= quick_row($cw['left'],$bug_status['32']);
	$text .= quick_row($cw['duplicates'],$bug_status['33']);
	$text .= quick_row($cw['invalid'],$bug_status['34']);
	$text .= quick_row($cw['feature'],$bug_status['35']);

}
$signature_filtree = $player['signature'];
$signature_filtree = strip_tags($signature_filtree,'<b><i><img><marquee><font><center>');
$text .= quick_row('Signature ',nl2br($signature_filtree));
$text .= "</table><br />";


if($full) {

	if(isset($sort_planets)){
		if($sorted_planets==1){
			$going = "asc";
			$sorted_planets=2;
		} else {
			$going = "desc";
			$sorted_planets=1;
		}
		db("select planet_name,location,fighters,colon,cash,metal,fuel,elect,mining_drones from ${db_name}_planets where login_id = '$target' order by '$sort_planets' $going");
	} else {
		db("select planet_name,location,fighters,colon,cash,metal,fuel,elect,mining_drones from ${db_name}_planets where login_id = '$target' order by fighters desc, planet_name asc, location desc");
		$sorted_planets = "";
	}
	$clan_planet = dbr(1);
	if($clan_planet) {
		$text .= make_table(array("<a href='player_info.php?target=$target&sort_planets=planet_name&sorted_planets=$sorted_planets'>".$cw['planet_name']."</a>","<a href='player_info.php?target=$target&sort_planets=location&sorted_planets=$sorted_planets'>".$cw['location']."</a>","<a href='player_info.php?target=$target&sort_planets=fighters&sorted_planets=$sorted_planets'>".$cw['fighters']."</a>","<a href='player_info.php?target=$target&sort_planets=colon&sorted_planets=$sorted_planets'>".$cw['colonists']."</a>","<a href='player_info.php?target=$target&sort_planets=cash&sorted_planets=$sorted_planets'>".$cw['cash']."</a>","<a href='player_info.php?target=$target&sort_planets=metal&sorted_planets=$sorted_planets'>".$cw['metal']."</a>","<a href='player_info.php?target=$target&sort_planets=fuel&sorted_planets=$sorted_planets'>".$cw['fuel']."</a>","<a href='player_info.php?target=$target&sort_planets=elect&sorted_planets=$sorted_planets'>".$cw['electronics']."</a>","<a href='player_info.php?target=$target&sort_planets=mining_drones&sorted_planets=$sorted_planets'>".$cw['mining_drones']."</a>"));
		while($clan_planet) {
			$clan_planet['planet_name'] = "<b class='b1'>$clan_planet[planet_name]</b>";
			$text .= make_row($clan_planet);
			$clan_planet = dbr(1);
		}
		$text .= "</table><br />";
	}
}

#links at bottom of page to transfer stuff and message player.
if($player['login_id'] != $user['login_id']) {
	if($user['joined_game'] < (time() - ($GAME_VARS['min_before_transfer'] * 86400)) || $user['login_id'] < 2){
		$text .= "<a href='send_ship.php?target=$target'>".$cw['transfer_ship_registration']."</a><br />";
		$text .= "<form action=player_info.php><input type=hidden name=transfer value=yes />";
		$text .= $st[1532]." <b class='b1'>$player[login_name]</b>:<br />";
		$text .= "<input type=text name=trans_amount size=6 />";
		$text .= "<input type=hidden name=trans_target_id value=$player[login_id] />";
		$text .= "<input type=hidden name=trans_target value=$player[login_name] />";
		$text .= "<input type='submit' value='".$cw['transfer']."' /></form>";
	} else {
		$text .= fprintf($st[1533],$GAME_VARS[min_before_transfer]);
	}
}

#retire player
//no link to retire for owner or admin, but both can see links for others
if(($user['login_id'] == 1 || $user['login_id'] == OWNER_ID) && $target != 1 && $target != OWNER_ID) {
	$text .= "<a href='retire.php?target=$target'>".$cw['retire']." $player[login_name]</a><br />";
}

#show account history link
if(($user['login_id'] == 1 && $target != 1) || $user['login_id'] == OWNER_ID){
	$text .= "<a href='player_info.php?history=$target'>".$st[1534]."</a><br />";
}

print_page($cw['player_info'],$text);

?>
