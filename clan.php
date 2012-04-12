<?php
require_once("user.inc.php");

$filename = 'clan.php';
$status_bar_help = "?topic=Clans";

$ret_str = "<p /><a href='location.php'>".$cw['return_to_star_system']."</a>";
$error_str = "";
$temp_str = "";

ship_status_checker();

db2("select clan_id, count(login_id) from ${db_name}_users where login_id > 5 && clan_id > 0 group by clan_id");
while($result = dbr2()){
	$clan_member_count[$result[0]] = $result[1];
}

if(isset($join)) { // Join clan
	if($user['clan_id'] > 0) {
		print_page($cw['join_clan'],$st[500]);	
	}
	db("select * from ${db_name}_clans where clan_id = $join");
	$clan = dbr(1);
	if($clan_member_count[$clan['clan_id']] >= $GAME_VARS['clan_member_limit'] && $user['login_id'] != 1) {
		print_page($cw['join_clan'],$st[501]);
	} elseif($user['clan_id'] > 0){
		print_page($cw['join_clan'],$st[502]);
	}
	if($user['login_id'] == 1) {
		$passwd = $clan['passwd'];
	}
	if(empty($passwd)) {
		get_var($cw['join_clan'],$filename,$st[503],'passwd','');
	} elseif($clan['passwd'] != $passwd) {
		print_page($cw['join_clan'],$st[504]);
	} else {
		dbn("update ${db_name}_users set clan_id = $join, clan_sym = '$clan[symbol]', clan_sym_color = '$clan[sym_color]' where login_id = $user[login_id]");
		dbn("update ${db_name}_planets set clan_id = $join where login_id = $user[login_id]");
		dbn("update ${db_name}_ships set clan_id = $join where login_id = $user[login_id]");
		$user['clan_id'] = $join;
		$user['clan_sym'] = $clan['symbol'];
		$user['clan_sym_color'] = $clan['sym_color'];
		if($user['login_id'] > 1){
			send_message($clan['leader_id'],"<b class='b1'>$user[login_name]</b> ".$st[505]);
		}
		insert_history($user['login_id'],sprintf($st[506], $clan[clan_name]));
	}


} elseif(isset($leave)) { // Leave clan
	db("select leader_id,clan_name from ${db_name}_clans where clan_id = $user[clan_id]");
	$clan = dbr(1);
	if($clan['leader_id'] == $user['login_id']) { 
		$error_str .= $st[507];
	} else {
		dbn("update ${db_name}_users set clan_id = 0, clan_sym = '', clan_sym_color = '' where login_id = $user[login_id]");
		dbn("update ${db_name}_planets set clan_id = -1 where login_id = $user[login_id]");
		dbn("update ${db_name}_ships set clan_id = -1, clan_fleet_id = 0 where login_id = $user[login_id]");

		if($user['login_id'] > 1){
			send_message($clan['leader_id'],"<b class='b1'>$user[login_name]</b> ".$st[508]);
		}
		$user['clan_id'] = 0;
		$user['clan_sym'] = "";
		$user['clan_sym_color'] = "";
	}
	insert_history($user['login_id'],sprintf($st[509], $clan[clan_name]));


} elseif(isset($kick)) { // Kick a clan member
	db("select leader_id,clan_name from ${db_name}_clans where clan_id = $user[clan_id]");
	$clan = dbr(1);
	db2("select clan_id,login_name from ${db_name}_users where login_id='$kick'");
	$kick_clan = dbr2();
	if($clan['leader_id'] != $user['login_id'] && $user['login_id'] !=1) { 
		$error_str .= $st[510];
	} elseif($user['clan_id'] < 1) { 
		$error_str .= $st[511];
	} elseif($kick_clan['clan_id'] != $user['clan_id']) {
		$error_str .= $st[512];
	} elseif($kick == $clan['leader_id']) {
		$error_str .= $st[513];
	} elseif($kick == 1) {
		$error_str .= $st[514];
	} elseif(!isset($sure)) {
		get_var($cw['kick_clan_member'],$filename,$st[515],'sure', 'yes');
	} else {
		dbn("update ${db_name}_users set clan_id = 0, clan_sym = '', clan_sym_color = '' where login_id = $kick");
		dbn("update ${db_name}_planets set clan_id = -1 where login_id = $kick");
		dbn("update ${db_name}_ships set clan_id = -1 where login_id = $kick");
		$error_str .= sprintf($st[516], $kick_clan[login_name]);
		insert_history($user['login_id'],sprintf($st[517], $clan[clan_name]));
	}


}elseif(isset($disband)) { // Disband clan
	db("select * from ${db_name}_clans where clan_id = $user[clan_id]");
	$clan = dbr(1);
	if(($clan['leader_id'] != $user['login_id']) && ($user['login_id'] != 1)) { 
		$error_str .= $st[518];
	} elseif($user['clan_id'] < 1) { 
		$error_str .= $st[519];
	} elseif(!isset($sure)) {
		get_var($cw['disband_clan'],$filename, $st[520],'sure' ,'yes');
	} else {
		post_news("<b class='b1'>$user[login_name]</b> ".$cw['disbanded_the']." <b class='b1'>$clan[clan_name](<font color=$clan[sym_color]>$clan[symbol]</font>)</b> ".$cw['clan'], $st[521] );
		dbn("update ${db_name}_users set clan_id = 0, clan_sym = '', clan_sym_color = '' where clan_id = $user[clan_id]");
		dbn("update ${db_name}_planets set clan_id = -1 where clan_id = $user[clan_id]");
		dbn("update ${db_name}_ships set clan_id = -1 where clan_id = $user[clan_id]");
		dbn("delete from ${db_name}_clans where clan_id = $user[clan_id]");
		dbn("delete from ${db_name}_messages where clan_id = $user[clan_id]");
		$user['clan_id'] = 0;
		$user['clan_sym'] = "";
		$user['clan_sym_color'] = "";
		insert_history($user['login_id'],sprintf($st[522], $clan[clan_name]));
	}


} elseif(isset($create)) { //Create a new clan
	db("select count(*) from ${db_name}_clans where clan_id");
	$result_max_clans = dbr();

	if ($result_max_clans[0] >= $GAME_VARS['clans_max'] && $user['login_id'] != 1) {
		$error_str .= $st[523];
	} elseif($user['clan_id'] > 0){
		$error_str = $st[524];
	} elseif($user['cash'] < 10000){
		$error_str = $st[525];
	}elseif(empty($_POST['name'])) {
		get_var($cw['create_clan'],$filename,$st[526],'name','');
	} elseif (empty($_POST['symbol'])) {
		get_var($cw['create_clan'],$filename,$st[527],'symbol','');
	} else {
		if (empty($_POST['sym_color'])) {
			$tempstr = "<form action=clan.php method=POST>";
			$tempstr .= $st[528];
			foreach($_POST as $key => $value){
				$tempstr .= "<input type=hidden name='$key' value='$value' />";
			}

			$tempstr .= $st[529];
			$tempstr .= "<table><tr><td>";
			$tempstr .= "<input type=radio name=sym_color value='FF0099' /><font color=FF0099>FF0099</font><br />";
			$tempstr .= "<input type=radio name=sym_color value='FF0000' /><font color=FF0000>FF0000</font><br />";
			$tempstr .= "<input type=radio name=sym_color value='BF00BF' /><font color=BF00BF>BF00BF</font><br />";
			$tempstr .= "</td><td>";
			$tempstr .= "<input type=radio name=sym_color value='FFFF00' /><font color=FFFF00>FFFF00</font><br />";
			$tempstr .= "<input type=radio name=sym_color value='BFBF00' /><font color=BFBF00>BFBF00</font><br />";
			$tempstr .= "<input type=radio name=sym_color value='00FF00' /><font color=00FF00>00FF00</font><br />";
			$tempstr .= "</td><td>";
			$tempstr .= "<input type=radio name=sym_color value='00BFBF' /><font color=00BFBF>00BFBF</font><br />";
			$tempstr .= "<input type=radio name=sym_color value='F396EC' /><font color=F396EC>F396EC</font><br />";
			$tempstr .= "<input type=radio name=sym_color value='C1B8FA' /><font color=C1B8FA>C1B8FA</font><br />";
			$tempstr .= "</td><td>";
			$tempstr .= "<input type=radio name=sym_color value='96D2F3' /><font color=96D2F3>96D2F3</font><br />";
			$tempstr .= "<input type=radio name=sym_color value='B7C5D9' /><font color=B7C5D9>B7C5D9</font><br />";
			$tempstr .= "<input type=radio name=sym_color value='FFFFFF' /><font color=FFFFFF>FFFFFF</font>";
			$tempstr .= "</td></tr></table>";
			$tempstr .= "<table></td></td>";
			$tempstr .= "<td align=center></table><p />";
			$tempstr.= "<input type='submit' value='Submit' /></form>";
			print_page($cw['choose_symbol_color'],$tempstr);

		} elseif(empty($_POST['passwd'])) {
			get_var($cw['create_clan'],$filename,$st[530],'passwd','');

		} elseif(empty($_POST['passwd_verify'])) {
			get_var($cw['create_clan'],$filename,$st[531],'passwd_verify','');

		} else {

			if(strlen($_POST['passwd']) < 5) {
				print_page($cw['create_clan'],$st[532]);
			}elseif(md5($_POST['passwd']) == $p_user['passwd']) { //password cannot be too similar to account pass
				print_page($cw['create_clan'],$st[533] );
			}elseif($_POST['passwd'] != $_POST['passwd_verify']) {
				print_page($cw['create_clan'],$st[534] );
			}

			$symbol = substr($_POST['symbol'],0,3);
			if(strlen($symbol) < 2) {
				print_page($cw['create_clan'],$st[535]);
			}

			if(!valid_input($symbol)) {
				print_page($cw['create_clan'], $st[536]);
			}

			db("select symbol from ${db_name}_clans where symbol = '$symbol'");
			$result = dbr(1);
			if(!empty($result)) {
				print_page($cw['create_clan'],$st[537]);
			}
			unset($result);

			$name = htmlspecialchars($name);
			$name = addslashes($name);
			$sym_color = substr($sym_color,0,6);
			
			$sym_color = htmlspecialchars($sym_color);
			$sym_color = addslashes($sym_color);
			$symbol = htmlspecialchars($symbol);
			$symbol = addslashes($symbol);
			$passwd = addslashes($passwd);

			$q_string = "insert into ${db_name}_clans (";
			$q_string = $q_string . "clan_name,leader_id,passwd,symbol,sym_color";
			$q_string = $q_string . ") values(";
			$q_string = $q_string . "'$name','$user[login_id]','$passwd','$symbol','$sym_color')";
			db($q_string);
			
			$clan_id = mysql_insert_id();
			dbn("update ${db_name}_planets set clan_id = $clan_id where login_id = $user[login_id]");
			dbn("update ${db_name}_ships set clan_id = $clan_id where login_id = $user[login_id]");
			dbn("update ${db_name}_users set clan_id = $clan_id, clan_sym = '$symbol', clan_sym_color = '$sym_color', cash=cash-10000 where login_id = $user[login_id]");
			$user['clan_id'] = $clan_id;
			$user['clan_sym'] = $symbol;
			$user['clan_sym_color'] = $sym_color;
			post_news("<b class='b1'>$user[login_name]</b> ".$cw['created_the']." <b class='b1'>$name(<font color=$sym_color>$symbol</font>)</b>", "clan, player_status");
			insert_history($user['login_id'],sprintf($st[538], $name));
		}
	}

} elseif(isset($lead_change)) { // Assign new leader
	db("select leader_id from ${db_name}_clans where clan_id = $user[clan_id]");
	$clan = dbr(1);
	if($user['clan_id'] < 1) {
		$error_str .= $st[539];
	} elseif(($clan['leader_id'] != $user['login_id']) && ($user['login_id'] != 1)) { 
		$error_str .= $st[540];
	} elseif(!$leader_id) {
		db2("select login_id,login_name from ${db_name}_users where clan_id = '$user[clan_id]' && login_id != '1' && login_id != '$clan[leader_id]'");
		$member_name = dbr2(1);
		if($member_name) {
			$ostr .= "<form action=$filename method=POST>";
			$ostr .= $st[541];
			foreach($_GET as $var => $value){
				$ostr .= "<input type=hidden name=$var value='$value' />";
			}
			foreach($_POST as $var => $value){
				$ostr .= "<input type=hidden name=$var value='$value' />";
			}
			$ostr .= "<select name=leader_id>";
			while ($member_name) {
				$ostr .= "<option value=$member_name[login_id]>$member_name[login_name]</option>";
				$member_name = dbr2(1);
			}
			$ostr .= "</select>";
			//$ostr .= "<input type=hidden name=sure value='no' />";
			$ostr .= "<input type='submit' value='Submit' /></form>";
			print_page($cw['choose_new_clan_leader'],$ostr);
		} else {
			print_page($cw['error'],$st[542]);
		}
	} elseif(!isset($sure) && $user['login_id'] != 1) {
		get_var($cw['change_clan_leader'],$filename,$st[543],'sure','yes');
	} else {
		dbn("update ${db_name}_clans set leader_id = $leader_id where clan_id = $user[clan_id]");
		$clan['leader_id'] = $leader_id;

		//send a message to the clan members about the change
		db("select login_name from ${db_name}_users where login_id != '$clan[leader_id]'");
		$leader_name = dbr();
		db("select login_id from ${db_name}_users where clan_id = '$user[clan_id]' && login_id != '1' && login_id != '$clan[leader_id]'");
		while($results = dbr()){
			send_message($results['login_id'],$st[544]." <b class='b1'>$leader_name[login_name]</b>.");
		}
		$error_str .= $st[545];
	}


}

#################
#Default clan page - if not in a clan.
################

if(isset($ranking) || ($user['clan_id'] < 1 && empty($clan_info))) { // Clan Ranking

	if(!isset($ranking)){
		$ranking = 0;
	}

	db("select count(clan_id) from ${db_name}_clans");
	$clan_count = dbr();

	if($clan_count[0] > 0) {

		if(isset($change_dir) && $change_dir == 1){
			$order_dir = "asc";
		} else {
			$order_dir = "desc";
			$dir_array = array_fill(0,10,"");
			$dir_array[$ranking] = "&change_dir=1";
		}

		if($ranking == 2){
			$order_by_str = $cw['clan_name'];
			$order_by_sql = "c.clan_name";
		} elseif($ranking == 3){
			$order_by_str = $cw['members'];
			$order_by_sql = "members";
		} elseif($ranking == 4){
			$order_by_str = $cw['fighters_killed'];
			$order_by_sql = "fkilled";
		} elseif($ranking == 5){
			$order_by_str = $cw['fighters_lost'];
			$order_by_sql = "flost";
		} elseif($ranking == 6){
			$order_by_str = $cw['ships_killed'];
			$order_by_sql = "skilled";
		} elseif($ranking == 7){
			$order_by_str = $cw['ships_lost'];
			$order_by_sql = "slost";
		} elseif($ranking == 8){
			$order_by_str = $cw['turns_run'];
			$order_by_sql = "trun";
		} else {
			$order_by_str = $cw['score'];
			$order_by_sql = "score";
		}

		#get details of each clan
		db2("select c.clan_id,c.clan_name,c.symbol,c.sym_color, count(u.login_id) as members, sum(u.fighters_killed) as fkilled, sum(u.fighters_lost) as flost, sum(u.ships_killed) as skilled, sum(u.ships_lost) as slost, sum(u.turns_run) as trun, sum(u.score) as score from ${db_name}_clans c, ${db_name}_users u where u.clan_id = c.clan_id && u.login_id > 5 GROUP by c.clan_id order by $order_by_sql $order_dir");
		$clan = dbr2(1);


		$error_str .= sprintf($st[546], $clan_count[0], $GAME_VARS[clans_max], $order_by_str);
		$error_str .= make_table(array($cw['rank'],"<a href=$filename?ranking=2".$dir_array[2].">".$cw['clan_name']."</a>", "<a href=$filename?ranking=3".$dir_array[3].">".$cw['members']."</a>", "<a href=$filename?ranking=4".$dir_array[4].">".$cw['fighters']."<br />".$cw['killed']."</a>", "<a href=$filename?ranking=5".$dir_array[5].">".$cw['fighters']."<br />".$cw['lost']."</a>", "<a href=$filename?ranking=6".$dir_array[6].">".$cw['ships']."<br />".$cw['killed']."</a>", "<a href=$filename?ranking=7".$dir_array[7].">".$cw['ships']."<br />".$cw['lost']."</a>", "<a href=$filename?ranking=8".$dir_array[8].">".$cw['turns']."<br />".$cw['run']."</a>", "<a href=$filename?ranking=1".$dir_array[1].">".$cw['score']."</a>"));

		$ct1 = 1;
		$ct2 = 1;
		$last = "";

		while($clan) {
			if(isset($player) && $player[$order_by_sql] != $last) {
				$last = $player[$order_by_sql];
				if($ct2 > 1){
					$ct1 = $ct2;
				}
			}
			$option = "";
			if((($user['clan_id'] == 0) && ($clan['members'] < $GAME_VARS['clan_member_limit'])) || $user['login_id'] == 1) {
				$option = "<a href='clan.php?join=$clan[clan_id]'>Rejoindre</a>";
			} elseif($clan['members'] >= $GAME_VARS['clan_member_limit']) {
				$option = "Full";
			} elseif($clan['clan_id'] == $user['clan_id']) {
					$option = "<a href='clan.php'>".$cw['view']."</a>";
			}
		
			$error_str .= make_row(array($ct1,"<b class='b1'>$clan[clan_name]</b>(<b><font color=$clan[sym_color]>$clan[symbol]</font></b>)",$clan['members'],$clan['fkilled'],$clan['flost'],$clan['skilled'],$clan['slost'],$clan['trun'],$clan['score'],$option,"<a href='clan.php?clan_info=1&target=$clan[clan_id]'>".$cw['details']."</a>"));

			$ct2++;
			$clan = dbr2(1);
		}
		$error_str .= "</table>";
	} else {
		$error_str .= sprintf($st[547], $GAME_VARS[clans_max]);
	}
	if(($user['clan_id'] == 0 && $clan_count[0] < $GAME_VARS['clans_max']) || $user['login_id'] == 1) {
		$error_str .= $st[548];
		if ($user['cash'] >= 10000) $error_str .= "<p /><a href='clan.php?create=1'>".$cw['create_a_new_clan']."</a><br />";
	} elseif($clan_count[0] >= $GAME_VARS['clans_max']) {
		$error_str .= sprintf($st[549], $GAME_VARS[clans_max]);
	}
	print_page($cw['clan_rankings'],$error_str);
}

if(isset($changepass)) {// change password
	db("select leader_id,passwd from ${db_name}_clans where clan_id = $user[clan_id]");
	$clan = dbr(1);
	$rs = "<a href='clan.php'>".$cw['back_to_clan_control']."</a>";
	if($user['clan_id'] < 1) { 
		print_page($cw['stop_that'],$st[550]);
	} elseif($clan['leader_id'] != $user['login_id'] && $user['login_id'] != 1) {
		print_page($cw['stop_that'],$st[551]);
	} elseif($changepass==1) {
		$temp_str .= $st[552];
		$temp_str .= "<table><form action=clan.php method=post><input type=hidden name=changepass value=changed />";

		if($login_id != 1){ #don't ask for the old pass for the admin
			$temp_str .= "<tr><td align=right>".$cw['old_password']."</td><td><input type=password name=oldpass /></td></tr>";
		}

		$temp_str .= "<tr><td align=right>".$cw['new_password']."</td><td><input type=password name=newpass /></td></tr>";
		$temp_str .= "<tr><td align=right>".$cw['re_new_password'] ."</td><td><input type=password name=newpass2 /></td></tr>";
		$temp_str .= "<tr><td></td><td><input type='submit' value='Change Password' /></td></tr></form></table><p />";
		print_page($cw['change_password'],$temp_str);
	} elseif ($changepass == 'changed') {
		if (isset($newpass) && ($newpass == $newpass2)) {
			if(strlen($newpass) < 5) {
				$temp_str = $st[553];
			}elseif($newpass == $user['passwd']) {
				$temp_str = $st[554];
			} elseif($newpass == $oldpass) {
				$temp_str = $st[555];
			} elseif ($oldpass != $clan['passwd'] && $login_id != 1) { #admin doesn't need old pass
				$temp_str = $st[556];
				$temp_str .= "<a href='javascript:back()'>".$cw['go_back']."</a><p />";
			} else {
				dbn("update ${db_name}_clans set passwd='$newpass' where clan_id=$user[clan_id]");
				$clan['passwd']='$newpass';
				$temp_str .= $st[557];
			}
		} else {
			$temp_str = $st[558];
			$temp_str .= "<a href='javascript:back()'>".$cw['go_back']."</a><br />";
		}
		print_page($cw['change_password'],$temp_str);
	}

} elseif(isset($clan_info) && $target > 0){ #show clan info

	$x_link = "<a href='clan.php'>".$cw['clan_control']."</a>";

	if($user['login_id'] == 1 || $user['clan_id'] == $target || $user['login_id'] == OWNER_ID) { #admin can see all, as can clan members.
		$full = 1;
	} else {
		$full = 0;
	}

	#list some statistics about the clan, as user is a member (or admin).
	if($full == 1){
		#planet details
		db("select sum(cash) as cash,sum(tech) as tech, sum(fighters) as pfigs, count(distinct planet_id) as planets, sum(research_fac) as rfac, count(distinct shield_gen) as sgens, sum(shield_charge) as scharge, sum(colon) as colon from ${db_name}_planets where clan_id = '$target'");
		$res1 = dbr(1);


		#planet percentages
		db("select sum(cash) as cash,sum(tech) as tech,sum(fighters) as pfigs, count(planet_id) as planets from ${db_name}_planets where login_id > '5'");
		$maths1 = dbr(1);


		#ship detals
		db("select sum(fighters) as sfigs, sum(max_fighters) as max_figs, count(ship_id) as ships, sum(cargo_bays) as cargo from ${db_name}_ships where clan_id = '$target'");
		$res2 = dbr(1);

		#used for ship percentages
		db("select sum(fighters) as sfigs, count(ship_id) as ships from ${db_name}_ships where login_id > '5'");
		$maths2 = dbr(1);


		#get user detals.
		db("select count(login_id) as members, sum(cash) as cash, sum(genesis) as gen, sum(terra_imploder) as imploder, sum(fighters_killed) as fkilled, sum(fighters_lost) as flost, sum(score) as score, sum(alpha) as alpha, sum(gamma) as gamma, sum(delta) as delta, sum(tech) as tech, sum(ships_killed) as skilled, sum(ships_lost) as slost, sum(turns_run) as trun, sum(turns) as turns, sum(ships_killed_points) as spkilled, sum(ships_lost_points) as splost, sum(approx_value) as approx_value from ${db_name}_users where clan_id = '$target'");
		$res3 = dbr(1);

		#used to calculate percentages
		db("select count(login_id) as members, sum(cash) as cash, sum(approx_value) as approx_value, sum(fighters_killed) as fkilled, sum(fighters_lost) as flost, sum(tech) as tech, sum(ships_killed) as skilled, sum(ships_lost) as slost, sum(ships_killed_points) as spkilled, sum(ships_lost_points) as splost, sum(turns_run) as trun, sum(turns) as turns from ${db_name}_users where login_id > '5'");
		$maths3 = dbr(1);

		$temp_str .= $x_link."<br /><br />"; #link to clan control
	} else {#only partial listing given, so only get small amounts of data.
		db("select count(login_id) as members, sum(fighters_killed) as fkilled, sum(fighters_lost) as flost, sum(ships_killed) as skilled, sum(ships_lost) as slost, sum(turns_run) as trun from ${db_name}_users where clan_id = '$target'");
		$res3 = dbr(1);

		#for percentages
		db("select count(login_id) as members, sum(fighters_killed) as fkilled, sum(fighters_lost) as flost, sum(ships_killed) as skilled, sum(ships_lost) as slost, sum(turns_run) as trun from ${db_name}_users where login_id > '5'");
		$maths3 = dbr(1);
	}

	$temp_str .= make_table(array("",""));

	db("select clan_name, passwd, leader_id, symbol, sym_color from ${db_name}_clans where clan_id = '$target'");
	$cd = dbr(1);

	$temp_str .= quick_row("Clan Name",$cd['clan_name']);
	$temp_str .= quick_row("Clan Symbol","<font color=#".$cd['sym_color'].">$cd[symbol]</font>");
	$temp_str .= quick_row("Member Count",$res3['members']);

	if($full == 0){
		$temp_str .= quick_row("Chasseurs détruits",calc_perc($res3['fkilled'],$maths3['fkilled']));
		$temp_str .= quick_row("Chasseurs perdus",calc_perc($res3['flost'],$maths3['flost']));
		$temp_str .= quick_row("Vaisseaux détruits",calc_perc($res3['skilled'],$maths3['skilled']));
		$temp_str .= quick_row("Vaisseaux perdus",calc_perc($res3['slost'],$maths3['slost']));
		$temp_str .= quick_row("Tours joués",calc_perc($res3['trun'],$maths3['trun']));
		$temp_str .= "</table><br /><br />Voici la liste des membres de la guilde : <b class='b1'>$cd[clan_name]</b1> clan. ".make_table(array("Membres","Tours joués","Chasseurs détruits","Chasseurs perdus","Vaisseaux détruits","Vaisseaux perdus"));
		
		db("select login_id,turns_run, fighters_killed,fighters_lost, ships_killed, ships_lost from ${db_name}_users where clan_id = '$target'");
		while($clan_members = dbr(1)){
			$clan_members['login_id'] = print_name($clan_members);
			$clan_members['fighters_killed'] = calc_perc($clan_members['fighters_killed'],$maths3['fkilled']);
			$clan_members['fighters_lost'] = calc_perc($clan_members['fighters_lost'],$maths3['flost']);
			$clan_members['ships_killed'] = calc_perc($clan_members['ships_killed'],$maths3['skilled']);
			$clan_members['ships_lost'] = calc_perc($clan_members['ships_lost'],$maths3['slost']);
			$clan_members['turns_run'] = calc_perc($clan_members['turns_run'],$maths3['trun']);
			$temp_str .= make_row($clan_members);
		}
		
	} else {
		$temp_str .= quick_row("&nbsp;","");
		$temp_str .= quick_row($cw['cash'],calc_perc($res3['cash'] + $res1['cash'],$maths3['cash'] + $maths1['cash']));
		$temp_str .= quick_row($cw['tech_units'],calc_perc($res3['tech'] + $res1['tech'],$maths3['tech'] + $maths1['tech']));
		$temp_str .= quick_row($cw['approx_value'],calc_perc($res3['approx_value'], $maths3['approx_value']));

		$temp_str .= quick_row($cw['turns'],calc_perc($res3['turns'],$maths3['turns']));
		$temp_str .= quick_row($cw['turns_run'],calc_perc($res3['trun'],$maths3['trun']));
		$t_figs = $res1['pfigs'] + $res2['sfigs'];
		$t_fcap = $maths1['pfigs'] + $maths2['sfigs'];
		$temp_str .= quick_row($cw['total_fighters'],calc_perc($t_figs,$t_fcap));
		$temp_str .= quick_row("&nbsp;","");

		$temp_str .= quick_row($cw['ships_killed'],calc_perc($res3['skilled'],$maths3['skilled']));
		$temp_str .= quick_row($cw['ships_lost'],calc_perc($res3['slost'],$maths3['slost']));
		$temp_str .= quick_row($cw['ship_points_killed'],calc_perc($res3['spkilled'],$maths3['spkilled']));
		$temp_str .= quick_row($cw['ship_points_lost'],calc_perc($res3['splost'],$maths3['splost']));
		$temp_str .= quick_row($cw['fighters_killed'],calc_perc($res3['fkilled'],$maths3['fkilled']));
		$temp_str .= quick_row($cw['fighters_lost'],calc_perc($res3['flost'],$maths3['flost']));
		$temp_str .= quick_row($cw['score'], $res3['score']);
		$temp_str .= quick_row("&nbsp;","");

		$temp_str .= quick_row($cw['planets'],calc_perc($res1['planets'],$maths1['planets']));
		$temp_str .= quick_row($cw['planetary_fighters'],calc_perc($res1['pfigs'],$maths1['pfigs']));
		$temp_str .= quick_row($cw['research_facilities'],$res1['rfac']);
		$temp_str .= quick_row($cw['shield_generators'],$res1['sgens']);
		$temp_str .= quick_row($cw['shield_charges'],$res1['scharge']);
		$temp_str .= quick_row($cw['colonists'],$res1['colon']);
		$temp_str .= quick_row("&nbsp;","");

		$temp_str .= quick_row($cw['ships'],calc_perc($res2['ships'],$maths2['ships']));
		$temp_str .= quick_row($cw['ship_fighters'], calc_perc($res2['sfigs'],$maths2['sfigs']));
		$temp_str .= quick_row($cw['fleet_fighter_capacity'],$res2['max_figs']."".$cw['fighters']."");
		$temp_str .= quick_row("Fleet Cargo Capacity",$res2['cargo']." Units");
		$temp_str .= quick_row("&nbsp;","");

		$temp_str .= quick_row($cw['genesis_devices'],$res3['gen']);
		if($GAME_VARS['uv_planets'] >= 0){
			$temp_str .= quick_row($cw['terra_imploders'],$res3['imploder']);
		}
		if($GAME_VARS['bomb_flag'] < 2){
			$temp_str .= quick_row($cw['alpha_bombs'],$res3['alpha']);
			$temp_str .= quick_row($cw['gamma_bombs'],$res3['gamma']);
		}
		$temp_str .= quick_row($cw['delta_bombs'],$res3['delta']);
	}

	$temp_str .= "</table><br />";

	print_page($cw['clan_info'],$temp_str.$x_link);



} else {

	// print normal page for clan-member
	db("select * from ${db_name}_clans where clan_id = $user[clan_id]");
	$clan = dbr(1);
	$clan_name = stripslashes($clan['clan_name']);


	#change a ship's fleet
	if(isset($fleet_type) && $user['login_id'] == $clan['leader_id']){
		if($join_fleet_id_2 != 0){
			$join_fleet_id = $join_fleet_id_2;
		}

		$error_str .= "<br />".change_fleet_num($join_fleet_id,1,$do_ship,"ship_id")."<p /><br />";
	}




	$error_str .= sprintf($st[559], $clan_name, $clan[sym_color], $clan[symbol]);

	if($clan['leader_id'] == $user['login_id']){
		$error_str .= sprintf($st[560], $clan[passwd]);
	}

	$error_str .= make_table(array($cw['member'], $cw['turns'], $cw['cash'], $cw['tech_units'], $cw['kills'], $cw['last_activity']));
	db("select login_name,turns,cash,tech,ships_killed,last_request,login_id from ${db_name}_users where clan_id = $user[clan_id] order by login_name,ships_killed");
	$clan_member = dbr(1);
	while($clan_member) {
		if($clan['leader_id'] == $clan_member['login_id']) {
			$clan_member['login_name'] = "(L) ".print_name($clan_member);
		} else {
			$clan_member['login_name'] = print_name($clan_member);
		}
		if($clan_member['last_request'] > (time()-300)){
			$clan_member['last_request'] = "<b><font color=#00AA00>Online</font></b>";
		} else {
			$clan_member['last_request'] = date('d/m/Y H:i', $clan_member['last_request']);
		}
		$temp_id = $clan_member['login_id'];
		if($clan_member['login_id'] != $user['login_id']){
			$clan_member['login_id'] = "<a href='message.php?target_id=$clan_member[login_id]'>".$cw['message']."</a>";
		} else {
			$clan_member['login_id'] = "";
		}
		if(($user['login_id'] == $clan['leader_id'] || $user['login_id'] == 1) &&	($temp_id != $clan['leader_id'] && $temp_id != 1)) {
			$clan_member['login_id'] .= " - <a href='clan.php?kick=$temp_id'>".$cw['kick']."</a>";
		}
		$error_str .= make_row($clan_member);
		$clan_member = dbr(1);
	}
	$error_str .= "</table>";
	$error_str .= "<br />";

	#little code to allow users to sort planets asc, desc in a number of criteria
	if(isset($sort_planets)){
		if($sorted==1){
			$going = "asc";
			$sorted=2;
		} else {
			$going = "desc";
			$sorted=1;
		}
		db("select login_name,planet_name,location,fighters,colon,cash,metal,fuel,elect from ${db_name}_planets where clan_id = $user[clan_id] and location != 1 order by '$sort_planets' $going");
	} else {
		db("select login_name,planet_name,location,fighters,colon,cash,metal,fuel,elect from ${db_name}_planets where clan_id = $user[clan_id] and location != 1 order by login_name asc, fighters desc, planet_name asc");
		$sorted = "";
	}

	$clan_planet = dbr(1);

	if($clan_planet) {
		$error_str .= make_table(array("<a href='$filename?sort_planets=login_name&sorted=$sorted'>".$cw['planet_owner']."</a>","<a href='$filename?sort_planets=planet_name&sorted=$sorted'>".$cw['planet_name']."</a>","<a href='$filename?sort_planets=location&sorted=$sorted'>".$cw['location']."</a>","<a href='$filename?sort_planets=fighters&sorted=$sorted'>".$cw['fighters']."</a>","<a href='$filename?sort_planets=colon&sorted=$sorted'>".$cw['colonists']."</a>","<a href='$filename?sort_planets=cash&sorted=$sorted'>".$cw['cash']."</a>","<a href='$filename?sort_planets=metal&sorted=$sorted'>".$cw['metal']."</a>","<a href='$filename?sort_planets=fuel&sorted=$sorted'>".$cw['fuel']."</a>","<a href='$filename?sort_planets=elect&sorted=$sorted'>".$cw['electronics']."</a>"));
		while($clan_planet) {
			$clan_planet['login_name'] = "<b class='b1'>$clan_planet[login_name]</b>";
			$error_str .= make_row($clan_planet);
			$clan_planet = dbr(1);
		}
		$error_str .= "</table><br />";
	}



	/*************
	* List Clan Ships
	**************/

	#show all ships, not just other clan members.
	if(isset($show_clan_ships)){

		$error_str .= "<br /><br /><a href='clan.php'>".$st[561]."</a>";

		if($user['login_id'] == $clan['leader_id']){
			$error_str .= "<br /><FORM method=POST action=clan.php name=fleet_maint>";
			$error_str .= $st[562]." <input type=text name=join_fleet_id value='' max=3 size=3 />";
			$error_str .= " - <input type='submit' name=join_fleet_button value=".$cw['change_fleet'] ." />";
			$error_str .= " - <a href=javascript:TickAll(\"fleet_maint\")>Invert Ship Selection</a><p />";
			$error_str .= "<input TYPE='hidden' name=show_clan_ships value=1 />";
		}

		#little to allow users to list the ships by different means, even asc and desc.
		if(isset($sort_ships)){
			if($sorted_ships==1){
				$going = "asc";
				$sorted_ships=2;
			} else {
				$going = "desc";
				$sorted_ships=1;
			}
			db("select ship_name,class_name_abbr,location,fighters,shields,armour,clan_fleet_id,ship_id from ${db_name}_ships where clan_id = '$user[clan_id]' order by '$sort_ships' $going");
		} else {
			db("select ship_name,class_name_abbr,location,fighters,shields,armour,clan_fleet_id,ship_id from ${db_name}_ships where clan_id = '$user[clan_id]' order by fighters desc, ship_name asc");
			$sorted_ships=1;
		}
		$clan_ship = dbr(1);
		$clan_page_tab = array("<a href='$filename?sort_ships=ship_name&sorted_ships=$sorted_ships&show_clan_ships=1'>".$cw['ship_name']."</a>","<a href='$filename?sort_ships=class_name_abbr&sorted_ships=$sorted_ships&show_clan_ships=1'>".$cw['ship_class']."</a>","<a href='$filename?sort_ships=location&sorted_ships=$sorted_ships&show_clan_ships=1'>".$cw['location']."</a>","<a href='$filename?sort_ships=fighters&sorted_ships=$sorted_ships&show_clan_ships=1'>".$cw['fighters']."</a>","<a href='$filename?sort_ships=shields&sorted_ships=$sorted_ships&show_clan_ships=1'>".$cw['shields']."</a>","<a href='$filename?sort_ships=armour&sorted_ships=$sorted_ships&show_clan_ships=1'>".$cw['armour']."</a>","<a href='$filename?sort_ships=clan_fleet_id&sorted_ships=$sorted_ships&show_clan_ships=1'>".$cw['can_fleet']."</a>",$cw['change_fleet']);

		if($GAME_VARS['clan_fleet_attacking'] == 0){
			unset($table_head_array[7]);
		}

		$error_str .= make_table($clan_page_tab);
		while($clan_ship) {
			if($user['login_id'] == $clan['leader_id'] && $GAME_VARS['clan_fleet_attacking'] == 1){
				$clan_ship['ship_id'] = "<input type=checkbox name=do_ship[$clan_ship[ship_id]] value=$clan_ship[ship_id] />";
			} else {
				unset($clan_ship['ship_id']);
			}
			$error_str .= make_row($clan_ship);
			$clan_ship = dbr(1);
		}
		$error_str .= "</table><p />";

		$error_str .= $st[562]." <input type=text name=join_fleet_id_2 value='' max=3 size=3 />";
		$error_str .= "<input TYPE='hidden' name=fleet_type value=1 />";
		$error_str .= " - <input type='submit' name=join_fleet_button value=".$cw['change_fleet']." />";
		$error_str .= " - <a href=javascript:TickAll(\"fleet_maint\")>".$cw['invert_ship_selection']."</a><p /></form>";


	/*************
	* Summary of Clan ships
	**************/
	} else {
		db("select login_id, count(ship_id) as total, sum(fighters) as fighters from ${db_name}_ships where clan_id = $user[clan_id] group by login_id order by fighters desc, ship_name desc");
		$clan_ship = dbr(1);

		$error_str .= "<br /><br /><a href='clan.php?show_clan_ships=1'>".$cw['show_all_clan_ships']."</a><p />";

		while($clan_ship){
			$error_str .= print_name($clan_ship)." has <b>$clan_ship[total]</b> ".$cw['ship(s)']." w/ <b>$clan_ship[fighters]</b> ".$cw['total_fighters']."<br />";
			$clan_ship = dbr(1);
		}
		$error_str .= "<br /><br />";
	}

	$error_str .= "<a href='clan.php?ranking=1'>".$cw['clan_rankings']."</a>";
	$error_str .= "<br /><a href='clan.php?clan_info=1&target=$user[clan_id]'>".$cw['clan_information']."</a><br /><br />";

	if($user['login_id'] == $clan['leader_id'] || $user['login_id'] == 1) {
		$error_str .= "<a href='clan.php?changepass=1'>".$cw['change_clan_password']."</a><br />";

		if($clan_member_count[$clan['clan_id']] >1) {
			$error_str .= "<a href='clan.php?lead_change=1'>".$cw['change_clan_leader']."</a><br />";
			$error_str .= "<a href='message.php?target_id=-2&clan_id=$user[clan_id]'>".$cw['message_clan']."</a><br />";
		}
		if($user['login_id'] ==1 && $user['login_id'] != $clan['leader_id']) {
			$error_str .= "<a href='clan.php?leave=1'>".$cw['leave_clan']."</a><br />";
		}
		$error_str .= "<a href='clan.php?disband=1'>".$cw['disband_clan']."</a><br />";
	} else {
		if($clan_member_count[$clan['clan_id']] >1) {
			$error_str .= "<a href='message.php?target_id=-2&clan_id=$user[clan_id]'>".$cw['message_clan']."</a><br />";
		}
		if($user['login_id'] ==1 || $user['login_id'] != $clan['leader_id']) {
			$error_str .= "<a href='clan.php?leave=1'>".$cw['leave_clan']."</a><br />";
		}
	}


}

print_page($cw['clan'],$error_str);
?>
