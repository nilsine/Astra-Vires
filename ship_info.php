<?php
/******************
* Script that simply gives comprehensive details about a ship.
* Created by Moriarty
* On 7th Aug 2004
******************/


//get ship id, and confirm is valid num.
$s_id = (int)$_GET['s_id'];
if(empty($s_id) || $s_id <= 0){
	print_s_page($st[82]);
}

require_once("user.inc.php");

//select ship information
db("select * from ${db_name}_ships where ship_id = '$s_id' && login_id = '$user[login_id]'");
$ship = dbr(1);

//nothing found, so almost certainly not the players own ship.
if(empty($ship)){
	print_s_page($st[83]);
}

$text = sprintf($st[84], $ship[ship_name]);

$text .= make_table(array("",""));

$text .= quick_row($cw['name'],$ship['ship_name']);
$text .= quick_row($cw['type'],$ship['class_name']);
$text .= quick_row($cw['location'],$cw['sys']." # $ship[location]");
$text .= quick_row($cw['fleet']." #",$ship['fleet_id']);
$text .= quick_row("","");
$text .= quick_row($cw['fighters'],"$ship[fighters]/$ship[max_fighters]");
$text .= quick_row($cw['shields'],"$ship[shields]/$ship[max_shields]");
$text .= quick_row($cw['armour'],"$ship[armour]/$ship[max_armour]");
$text .= quick_row($cw['size'],discern_size($ship['size']));
$text .= quick_row($cw['cargo_bays'],bay_storage($ship));
if($GAME_VARS['alternate_play_1'] == 1){
	$text .= quick_row($cw['mining_rate'].": ".$cw['metal'],$ship['mine_rate_metal']);
	$text .= quick_row($cw['mining_rate'].": ".$cw['fuel'],$ship['mine_rate_fuel']);
} else {
	$quick_maths = $ship['mine_rate_metal'] + $ship['mine_rate_fuel'];
	$text .= quick_row($cw['mining_rate'],$quick_maths);
}

$text .= quick_row($cw['speed_move_cost'],$ship['move_turn_cost']);
$text .= quick_row("","");
$text .= quick_row($cw['upgrade_pods'],$ship['upgrade_slots']);
$text .= quick_row($cw['specials'],config_list(0, $ship['config']));
$text .= quick_row("# ".$cw['offensive_turrets'], $ship['num_ot']);
$text .= quick_row("# ".$cw['defensive_turrets'], $ship['num_dt']);
$text .= quick_row("# ".$cw['plasma_turrets'], $ship['num_pc']);
$text .= quick_row("# ".$cw['electronic_warfare_pods'], $ship['num_ew']);
$text .= quick_row("","");
$text .= quick_row($cw['point_value'],$ship['point_value']);
$text .= quick_row($cw['points_killed'],$ship['points_killed']);

$text .= "</table>";

//$text = utf8_encode($text);

$rs = "";
//print_s_page($cw['ship_info'], $text);
echo "<div class='popup'>$text</div>";
?>
