<?php
require("user.inc.php");
$filename = 'player_stat.php';
$text = $st[985];

if(isset($change_dir) && $change_dir == 1){
	$order_dir = "asc";
} else {
	$order_dir = "desc";
}

if(!isset($table)){
	$table = 1;
}

if ($table == 2) {
	$text .= "<a href='$filename'>".$cw['general']."</a> - ".$cw['misc']." - <a href='$filename?table=3'>".$st[986]."</a><br />";
	$text .= "<br />".$st[987].":";

	$text .= "<br /><a href='$filename?table=2&su=1'>".$cw['signed_up']."</a> - <a href='$filename?table=2&lab=1'>".$cw['last_attack']."</a> - <a href='$filename?table=2&la=1'>".$st[988]."</a> - <a href='$filename?table=2&clan=1'>".$cw['clan']."</a> - <a href='$filename?table=2'>Score</a><br />";
	if(isset($su)) {
		$sql_order_str = "joined_game";
		$order_by_str = $st[989];
	} elseif(isset($lab)) {
		$sql_order_str = "last_attack_by";
		$order_by_str = $st[990];
	} elseif(isset($la)) {
		$sql_order_str = "last_attack";
		$order_by_str = $st[991];
	} elseif(isset($clan)) {
		$sql_order_str = "clan_id";
		$order_by_str = $cw['clan'];
	} else {
		$sql_order_str = "score";
		$order_by_str = $cw['score'];
	}
	$text .= "<br />".$st[992]." <b>$order_by_str</b>";
	$text .= make_table(array($cw['name'],$cw['signed_up'],$cw['last_attack'],$st[988],$cw['clan'],$cw['score']));
	db("select login_name,joined_game,last_attack_by,last_attack,clan_sym,score,clan_sym_color,login_id from ${db_name}_users where login_id > 5 order by $sql_order_str $order_dir");
} elseif ($table == 3) {
	$text .= "<a href='$filename'>".$cw['general']."</a> - <a href='$filename?table=2'>".$cw['misc']."</a> - ".$st[986]."<br />";
	$sql_order_str = 0;


	$not_at_zero = 0;
	$sql_select_ships = "";
	db("select count(*) as count, login_id from ${db_name}_ships where login_id > 5 group by login_id order by count desc");
	while($return = dbr(1)){
		if($return['count'] < 1){
			echo $return['count'];
			echo $cw['here'];
			break;
		}
		$sql_select_ships .= "login_id = $return[login_id] || ";
	}
	if(empty($sql_select_ships)){
		print_page($st[986],$st[993]);
	} else {
		$sql_select_ships = preg_replace("/\|\| $/", "", $sql_select_ships);
	}
	db("select cash,login_name,fighters_killed,ships_killed,ships_lost,turns_run,score,login_id from ${db_name}_users where ".$sql_select_ships." order by score $order_dir, fighters_killed $order_dir, ships_killed $order_dir,login_name");
	$text .= '<br />'.$st[994];
	$text .= make_table(array("",$cw['name'],$cw['fighters']."<br />".$cw['killed'],$cw['ships']." <br />".$cw['killed'],$cw['ships']." <br />".$st[995],$cw['turns']." <br />".$st[996],$cw['score']));

} else {
	$text .= $cw['general']." - <a href='$filename?table=2'>Divers</a> - <a href='$filename?table=3'>".$st[986]."</a><br />";
	if(!isset($dir_array)){
		$dir_array = array_fill(1,6,"");
	}
	if(!isset($change_dir) && isset($gen_tab)){
		$dir_array[$gen_tab] = "&change_dir=1";
	} elseif(!isset($gen_tab)){
		$gen_tab = "6";
	}

	$text .= "<br />".$st[987].":";
	$text .= "<br /><a href=$filename?gen_tab=1".$dir_array[1].">".$cw['login_name']."</a> - <a href=$filename?gen_tab=2".$dir_array[2].">".$st[997]."</a> - <a href=$filename?gen_tab=3".$dir_array[3].">".$cw['ship_kills']."</a> - <a href=$filename?gen_tab=4".$dir_array[4].">".$st[998]."</a> - <a href=$filename?gen_tab=5".$dir_array[5].">".$cw['turns_run']."</a> - <a href=$filename?gen_tab=6".$dir_array[6].">".$cw['score']."<br /></a>";
	if(isset($gen_tab) && $gen_tab ==1) {
		$sql_order_str = "login_name";
		$order_by_str = $cw['login_name'];
	} elseif(isset($gen_tab) && $gen_tab ==2) {
		$sql_order_str = "fighters_killed";
		$order_by_str = $cw['fighters_killed'];
	} elseif(isset($gen_tab) && $gen_tab ==3) {
		$sql_order_str = "ships_killed";
		$order_by_str = $st[999];
	} elseif(isset($gen_tab) && $gen_tab ==4) {
		$sql_order_str = "ships_lost";
		$order_by_str = $st[998];
	} elseif(isset($gen_tab) && $gen_tab ==5) {
		$sql_order_str = "turns_run";
		$order_by_str = $cw['turns_run'];
	} else {
		$sql_order_str = "score";
		$order_by_str = $cw['score'];
	}
	db("select cash,login_name,fighters_killed,ships_killed,ships_lost,turns_run,score,login_id from ${db_name}_users where login_id > 5 order by $sql_order_str $order_dir");
	$text .= "<br />".$st[992]." <b>$order_by_str</b>";
	$text .= make_table(array($cw['rank'],$cw['name'],$cw['fighters']."<br />".$cw['killed'],$cw['ships']." <br />".$cw['killed'],$cw['ships']." <br />".$st[995],$cw['turns']." <br />".$st[996],$cw['score']));
}
$text .= '<br />';

$ct1 = 1;
$ct2 = 1;
$last = "";

while($player = dbr(1)) {
	if(isset($player[$sql_order_str]) && $player[$sql_order_str] != $last) {
		$last = $player[$sql_order_str];
		if($ct2 > 1){
			$ct1 = $ct2;
		}
	}
	if($table != 2){
		$player['cash'] = $ct1;
	}

	if (!isset($player['last_attack_by']) && $table == 2) {
		$player['last_attack_by']="~";
	}
	if (isset($player['clan_sym']) && $table == 2) {
		$player['clan_sym'] = "<font color=#$player[clan_sym_color]>$player[clan_sym]</font>";
		$player['clan_sym_color'] = "";
	} elseif(isset($table) && $table == 2) {
		$player['clan_sym'] = "~";
	}
	if (isset($player['last_attack']) && $player['last_attack'] == 1) {
		$player['last_attack']="~";
	} elseif (isset($player['last_attack'])) {
		$player['last_attack'] = date( "M d - H:i",$player['last_attack']);
	}
	if (isset($player['joined_game'])) {
		$player['joined_game'] = date( "M d - H:i",$player['joined_game']);
	}
	$dis_name = print_name($player);
	$player['login_name'] = $dis_name;
	unset($player['login_id'], $player['clan_sym_color']);
	$text .= make_row($player);
	$ct2++;
}

$text .= "</table><br />";
print_page($cw['player_ranking'],$text);
?>
