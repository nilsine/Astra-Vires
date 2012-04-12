<?php
require_once("user.inc.php");

$filename = "black_market.php";
$status_bar_help = "?topic=Blackmarkets";

ship_status_checker();

$error_str = "";

db("select * from ${db_name}_bmrkt where location = '$user[location]' order by bmrkt_type asc");
$bmrkt = dbr(1);

if (empty($bmrkt)) {
	print_page($cw['port'],$st[68]);
} elseif($GAME_VARS['uv_num_bmrkt'] == 0 && $user['login_id'] !=1) {
	print_page($cw['error'],$st[69]);
}


$error_str .= sprintf($st[70], $bmrkt[bm_name]);

$error_str .= $st[71];

$error_str .= "<br /><a href='bm_ships.php?from_0=1'>".$st[72]."</a>";
$error_str .= "<br /><a href='bm_upgrades.php?from_0=1'>".$st[73]."</a>";

$rs = "<p /><a href='location.php'>".$cw['close_contact']."</a><br />";

print_page($cw['blackmarket'],$error_str);


?>
