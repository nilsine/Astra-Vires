<?php
if(isset($_GET['db_name'])){
	$temp_db = mysql_escape_string((string)$_GET['db_name']);
}
require_once("common.inc.php");
$db_name = $temp_db;
require_once("$directories[includes]/${db_name}_vars.inc.php");

db_connect();

ob_start();

echo "<div class='popup'>";

function resolve_difficulty($diff){
	if($diff == 1){
		$diff_txt = "Begginer / Easy";
	} elseif($diff == 2){
		$diff_txt = "Begginer -> Intermediate";
	} elseif($diff == 3){
		$diff_txt = "Intermediate / Medium";
	} elseif($diff == 4){
		$diff_txt = "Intermediate -> Advanced";
	} elseif($diff == 5){
		$diff_txt = "Advanced / Hard";
	} elseif($diff == 6){
		$diff_txt = "All skill levels";
	}
	return $diff_txt;
}


db("select count(login_id),sum(cash),sum(turns),sum(turns_run),sum(ships_killed), sum(fighters_lost) as lost_fighters, sum(fighters_killed) as killed_fighters, sum(tech) as tech from ${db_name}_users where login_id > 5");
$ct = dbr();
db("select count(login_id) from ${db_name}_users where ship_id > 1 && login_id > 5");
$ct2 = dbr();

db("select description, admin_name, name, paused,last_reset,difficulty from se_games where db_name = '$db_name'");
$descr = dbr();

echo make_table(array("",""));
echo quick_row($cw['game_name'],"<b>$descr[name]</b>");
echo quick_row($cw['admin_name'],"<b class='b1'>$descr[admin_name]</b>");
if($descr['paused'] == 1){
	$g_status = $cw['paused'];
} else {
	$g_status = $cw['running'];
}
echo quick_row($cw['game_status'],"$g_status");
echo quick_row($cw['players_max_players'],"$ct[0] / $GAME_VARS[max_players]");

echo quick_row($cw['difficulty'],resolve_difficulty($descr['difficulty']));
echo quick_row($cw['last_reset'],date("M d - H:i",$descr['last_reset']));
echo "</table><br /><br />";

if($ct[0] >= $GAME_VARS['max_players']) {
	echo $st[425];
} elseif($GAME_VARS['new_logins'] == 0  || $GAME_VARS['sudden_death'] == 1){
	echo $st[426];
}


/*if($GAME_VARS['admin_var_show'] == 1){
	echo "<a href='help.php?db_name=$db_name&list_vars=1&popup=1'>".$cw['game_variables']."</a><br /><br />";
}*/


if($descr['description']){
	$descr['description'] = preg_replace("/\n/","<br />",$descr['description']);
	echo "<br /><table cellspacing=1 cellpadding=2 border=0><tr bgcolor=#555555><td>".$st[427]."</td></tr>";
	echo "<tr bgcolor=#333333 align=left><td>$descr[description]</td></tr>";
	echo "</table>";
}

echo "<br /><br />";

//Admin board
//admin news start
echo "<table border=0 cellpadding=5><tr valign=top><td colspan=3>";
db("select headline,timestamp from ${db_name}_news where topic_set = 'admin' order by timestamp desc LIMIT 5");
$news = dbr();
if($news){
	echo $st[428];
	echo "<table cellspacing=1 cellpadding=2 border=0 width=525>";
	while($news) {
		echo quick_row("<b>".date("M d - H:i",$news['timestamp']),stripslashes($news['headline']));
		$news = dbr();
	}
	echo "</table><br />";
}
//admin news end


//Start of the Viewable Information.	
echo "</td></tr>";

db("select count(planet_id),sum(fighters),sum(cash) as cash, sum(tech) as tech from ${db_name}_planets where login_id != 1");
$ct4 = dbr();

if(isset($ct2[0])) {
	echo "<tr valign=top><td>";
	echo make_table(array($cw['players'],"<b>".($ct[0])."</b>"));
	if($ct[0] > 0) {
		echo quick_row($cw['players_alive'],calc_perc($ct2[0],$ct[0]));
		echo quick_row($cw['cash'],number_format($ct[1] + $ct4['cash']));
		echo quick_row($cw['cash_average'],number_format(round((($ct[1] + $ct4['cash']) * 100/$ct[0]) / 100)));
		if($GAME_VARS['uv_num_bmrkt'] > 0){
			echo quick_row($cw['tech_units'],number_format($ct['tech'] + $ct4['tech']));
			echo quick_row($cw['tech_units_average'],number_format(round((($ct['tech'] + $ct4['tech']) * 100/$ct[0]) / 100)));
		}
		echo quick_row($cw['turns'],$ct[2]);
		echo quick_row($cw['turns_average'],round(($ct[2] * 100/$ct[0]) / 100));
		echo quick_row($cw['turns_run'],$ct[3]);
		echo quick_row($cw['turns_run_average'],round(($ct[3] * 100/$ct[0]) / 100));
		echo quick_row($cw['ship_kills'],$ct[4]);
		echo quick_row($cw['ship_kills_average'],round(($ct[4] * 100/$ct[0]) / 100));
		echo quick_row($cw['fighters_killed'],$ct['killed_fighters']);
		echo quick_row($cw['avg_fighters_killed'],round(($ct['killed_fighters'] * 100/$ct[0]) / 100));
	}

	echo "</table><br />";
	echo "</tr><td>";

	db("select count(login_id),sum(fighters) from ${db_name}_ships where login_id != 1 and login_id !=0");
	$ct3 = dbr();
	if($ct3[0] > 0) {
		echo make_table(array($cw['ships'],"<b>$ct3[0]</b>"));
		echo quick_row($cw['ships_avg_player'],round($ct3[0]/$ct[0]));
		echo quick_row($cw['ship_fighters'],$ct3[1]);
		echo quick_row($cw['avg_fighters_ship'],round(($ct3[1] * 100/$ct3[0]) / 100));
		echo "</table><br />";
	}

	if(!empty($ct4[0])) {
		echo make_table(array($cw['planets'],"<b>$ct4[0]</b>"));
		echo quick_row($cw['planets_avg_player'],number_format($ct4[0]/$ct[0],3));
		echo quick_row($cw['planet_fighters'],$ct4[1]);
		echo quick_row($cw['avg_fighters_planet'] ,round(($ct4[1] * 100/$ct4[0]) / 100));
		echo "</table><br />";
	}

	db("select count(distinct clan_id),count(login_id) from ${db_name}_users where clan_id > 0 && login_id > 5");
	$ct5 = dbr();
	if(!empty($ct5[0])) {
		echo make_table(array($cw['clans'],"<b>$ct5[0]</b>"));
		echo quick_row($cw['membership'],$ct5[1]);
		echo "</table><br />";
	}

	echo "</td><td>";

	#echo "Top 10 Players<br />";
	echo make_table(array($cw['score'],$cw['login_name']));
	db("select login_name,clan_id,clan_sym,clan_sym_color,score from ${db_name}_users where ship_id > 1 and login_id > 5 order by score desc, login_name limit 10");
	$players = dbr();

	while(($players)) {
		if ($players['clan_id'] == 0 || $players['clan_sym'] == "") {
			$players['login_name'] = "<b class='b1'>$players[login_name]</b>";
		} else {
			$players['login_name'] = "<b class='b1'>$players[login_name]</b>(<font color=$players[clan_sym_color]>$players[clan_sym]</font>)";
		}
	
		echo quick_row("<b>$players[score]</b>",$players['login_name']);
		$players = dbr();
	}
	echo "</table><br />";

	echo "</td></tr><tr><td colspan=3>";

}
echo "</table>";

echo "</div>";

$html = ob_get_contents();

ob_clean();

echo $html;

?>