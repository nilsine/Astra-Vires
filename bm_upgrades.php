<?php
require_once("user.inc.php");

$filename = "bm_upgrades.php";
$status_bar_help = "?topic=Blackmarkets";

ship_status_checker();

$rs = "";
$error_str = "";

if(isset($from_0)){
 	$rs = "<p /><a href='black_market.php'>".$cw['return_to_blackmarket']."</a>";
}

$rs .= "<p /><a href='location.php'>".$cw['close_contact']."</a>";

if(!isset($buy)){
	$buy = 0;
}


db("select * from ${db_name}_bmrkt where location = '$user[location]' && (bmrkt_type = 2 || bmrkt_type = 0)");
$bmrkt = dbr(1);

if (empty($bmrkt)) {
	print_page($cw['blackmarket'],$st[386]);
} elseif($user['ship_id'] ==1 && $user['login_id'] !=1) {
	print_page($cw['error'],
$st[387]);
} elseif($GAME_VARS['uv_num_bmrkt'] == 0) {
	print_page($cw['error'],$st[388]);
}


// Tech and Credit costs of Advanced Upgrades

// Credit Cost
#turret costs - based on size of ship
$cost_temp = get_cost("pc");
$plasma_cannon_c = round($cost_temp['cost'] * ($user_ship['size'] / 100)) * 15;
$plasma_cannon_t = round($cost_temp['tech_cost'] * ($user_ship['size'] / 100)) * 5;

$cost_temp = get_cost("bo");
$bio_armour_c = $cost_temp['cost'] * $user_ship['max_armour'];
$bio_armour_t = $cost_temp['tech_cost'] * $user_ship['max_armour'];

$cost_temp = get_cost("e2");
$advanced_engine_c = round($cost_temp['cost'] * ($user_ship['size'] / 100)) * 15;
$advanced_engine_t = round($cost_temp['tech_cost'] * ($user_ship['size'] / 100)) * 5;


db("select upgrade_slots from ${db_name}_ships where ship_id = $user[ship_id]");
$upgrade_pods = dbr();

$old_config = $user_ship['config'];

// value of $buy determines upgrade to be purchased
// new if statement to prevent multiple occurance of config abbrs
// new addition of num_ot,num_dt, etc to allow multiple upgrades of same type

if($buy ==3) { //Plasma Cannon
	if(isset($many_pc) && $many_pc > 1){
		$plasma_cannon_c *= $many_pc;
		$plasma_cannon_t *= $many_pc;
		$num_to_buy = $many_pc;
	} else {
		$num_to_buy = 1;
	}
	if($user['cash'] < $plasma_cannon_c) {
		$error_str .= $st[389];
	} elseif($user['tech'] < $plasma_cannon_t) {
		$error_str .= $st[390];
	}elseif(!avail_check(5000)){
		$error_str .= $st[391];
	} elseif($user_ship['upgrade_slots'] < $num_to_buy) {
		$error_str .= $st[392];
	} elseif ($user_ship['num_pc'] + ($num_to_buy - 1) >= $max_pc){
		$error_str .= sprintf($st[393], $max_pc);
	} elseif(!isset($sure)) {
		get_var($cw['purchase_plasma_cannon'],$filename,sprintf($st[394], $num_to_buy, $user_ship[ship_name]),'sure',''); 
	} else {
		$error_str .= "$num_to_buy <b class='b1'>".$cw['plasma_cannon']."</b>".sprintf($st[395], $user_ship[ship_name], $plasma_cannon_c, $plasma_cannon_t);
		take_cash($plasma_cannon_c);
		take_tech($plasma_cannon_t);

		dbn("update ${db_name}_ships set upgrade_slots = upgrade_slots - '$num_to_buy', num_pc = num_pc + '$num_to_buy' where ship_id = '$user[ship_id]'");
		$upgrade_pods['upgrade_slots'] -= $num_to_buy;
		$user_ship['num_pc'] += $num_to_buy;
	}

} elseif($buy ==4) { //Bio-Organic Armour
	if($user_ship['max_armour'] < 11) {
		$error_str .= $st[396];
	} elseif(!isset($sure)) {
		get_var($cw['purchase_bio-organic_armour'],$filename,$st[397]."$user_ship[ship_name]</b>?",'sure',''); 
	} else {
		$error_str .= make_standard_upgrade ("Bio-Organic Armour", "bo", $bio_armour_c, 5001, $bio_armour_t);
		$reduce_amount = ceil(($user_ship['max_armour'] / 100) * 10);
		dbn("update ${db_name}_ships set max_armour = max_armour - '$reduce_amount' where ship_id = '$user[ship_id]'");
		$user_ship['max_armour'] -= $reduce_amount;

		if($user_ship['armour'] > $user_ship['max_armour']){
			$user_ship['armour'] = $user_ship['max_armour'];
			dbn("update ${db_name}_ships set armour = max_armour where ship_id = '$user[ship_id]'");
		}
	}

} elseif($buy ==5) { //Electronic Warfare Pod
	$cost_temp = get_cost("ew");
	$electronic_warfare_c = $cost_temp['cost'];
	$electronic_warfare_t = $cost_temp['tech_cost'];

	if(isset($many_ew) && $many_ew > 1){
		$electronic_warfare_c *= $many_ew;
		$electronic_warfare_t *= $many_ew;
		$num_to_buy = $many_ew;
	} else {
		$num_to_buy = 1;
	}
	if($user['cash'] < $electronic_warfare_c) {
		$error_str .= $st[398];
	} elseif($user['tech'] < $electronic_warfare_t) {
		$error_str .= $st[399];
	}elseif(!avail_check(5002)){
		$error_str .= $st[400];
	} elseif($user_ship['upgrade_slots'] < $num_to_buy) {
		$error_str .= $st[400];
	} elseif ($user_ship['num_ew'] + ($num_to_buy - 1) >= $max_ew){
		$error_str .= $st[401];
	} elseif(!isset($sure)) {
		get_var($cw['purchase_electronic_warfare_pod'],$filename,sprintf($st[402], $num_to_buy, $user_ship[ship_name]),'sure',''); 
	} else {
		$error_str .= "$num_to_buy <b class='b1'>".sprintf($st[403], $user_ship[ship_name], $electronic_warfare_c, $electronic_warfare_t);

		take_cash($electronic_warfare_c);
		take_tech($electronic_warfare_t);

		dbn("update ${db_name}_ships set upgrade_slots = upgrade_slots - '$num_to_buy', num_ew = num_ew + '$num_to_buy' where ship_id = '$user[ship_id]'");
		$upgrade_pods['upgrade_slots'] -= $num_to_buy;
		$user_ship['num_ew'] += $num_to_buy;

	}

} elseif($buy ==7 && $GAME_VARS['uv_planets'] > 0) { //Genesis Device
	if($user['cash'] < $genesis_c) {
		$error_str .= $st[404];
	} elseif($user['tech'] < $genesis_t) {
		$error_str .= $st[405];
	}elseif(!avail_check(1000)){
		$error_str .= $st[406];
	} elseif(!isset($sure)) {
		get_var($cw['purchase_genesis_device'],$filename,$st[407],'sure',''); 
	} else{
		$error_str .= sprintf($st[408], $genesis_c, $genesis_t);
		take_cash($genesis_c);
		take_tech($genesis_t);
		$user['genesis']++;
		dbn("update ${db_name}_users set genesis = genesis + 1 where login_id = '$user[login_id]'");
	}

} elseif($buy ==8) { //Terra Imploder
	if($user['cash'] < $terra_i_c) {
		$error_str .= $st[409];
	} elseif($user['tech'] < $terra_i_t) {
		$error_str .= $st[410];
	}elseif(!avail_check(1000)){
		$error_str .= $st[411];
	} elseif(!isset($sure)) {
		get_var($cw['purchase_terra_emploder'],$filename,$st[412],'sure',''); 
	} else {
		$error_str .= sprintf($st[413], $terra_i_c, $terra_i_t);
		take_cash($terra_i_c);
		take_tech($terra_i_t);
		$user['terra_imploder']++;
		dbn("update ${db_name}_users set terra_imploder = terra_imploder + 1 where login_id = '$user[login_id]'");
	}

} elseif($buy ==9) { //Advanced Engine Upgrade
	if ($user_ship['move_turn_cost'] < 3){ 
		$error_str .= $st[414];
	} elseif (config_check("e1",$user_ship)) {
		$error_str .= $st[415];
	} elseif(!isset($sure)) {
		get_var($cw['purchase_advanced_engine_upgrade'],$filename,$st[416]."<b class='b1'>$user_ship[ship_name]</b>?",'sure',''); 
	} else {
		$error_str .= make_standard_upgrade ($cw['advanced_engine_upgrade'], "e2", $advanced_engine_c, 5003, $advanced_engine_t);
		$user_ship['move_turn_cost'] = $user_ship['move_turn_cost'] - 2;
		dbn("update ${db_name}_ships set move_turn_cost = move_turn_cost - 2 where ship_id = '$user[ship_id]'");
	}
}



$error_str .= sprintf($st[417], $upgrade_pods[upgrade_slots]);
$error_str .= $st[418];

$error_str .= $st[419];
$error_str .= make_table(array($cw['item_name'],$cw['notes'],$cw['credits_cost'],$cw['tech_cost']),"75%");

if(avail_check(5000)){
	$buy_many = "";
	$num = $max_pc - $user_ship['num_pc'];
	if($user_ship['upgrade_slots'] > 1 && $num > 1){
		$buy_many .= "<a href='$filename?buy=3&many_pc=$num'>Acheter $num</a>";
	}
	$error_str .= make_row(array($cw['plasma_cannon'],sprintf($st[420], $max_pc),$plasma_cannon_c,$plasma_cannon_t,"<a href='$filename?buy=3'>".$cw['buy']."</a>",$buy_many, popup_help("help.php?upgrades=1&popup=1&chosen=pc", 350, 315)));
}
if(avail_check(5002)){
	$cost_temp = get_cost("ew");
	$buy_many = "";
	$num = $max_ew - $user_ship['num_ew'];
	if($user_ship['upgrade_slots'] > 1 && $num > 1){
		$buy_many .= "<a href='$filename?buy=5&many_ew=$num'>Acheter $num</a>";
	}
	$error_str .= make_row(array($cw['electronic_warfare_pod'],"Max of $max_pc per ship.",$cost_temp['cost'],$cost_temp['tech_cost'],"<a href='$filename?buy=5'>".$cw['buy']."</a>",$buy_many, popup_help("help.php?upgrades=1&popup=1&chosen=ew", 350, 315)));
}

$error_str .= "</table>";

$error_str .= "<br /><br />".$cw['misc']."";
$error_str .= make_table(array($cw['item_name'],$cw['notes'],$cw['credits_cost'],$cw['tech_cost']),"75%");

if(avail_check(5003)){
	if($GAME_VARS['ship_warp_cost'] == -1){
		$engine_changes = $st[421];
	} else {
		$engine_changes = "";
	}
	$error_str .= make_row(array($cw['advanced_engine_upgrade'],sprintf($st[424], $engine_changes),$advanced_engine_c,$advanced_engine_t,"<a href='$filename?buy=9'>".$cw['buy']."</a>", popup_help("help.php?upgrades=1&popup=1&chosen=e2", 350, 315)));
}
if(avail_check(5001)){
	$error_str .= make_row(array($cw['bio-organic_armour'],$st[423],$bio_armour_c,$bio_armour_t,"<a href='$filename?buy=4'>".$cw['buy']."</a>", popup_help("help.php?upgrades=1&popup=1&chosen=bo", 350, 315)));
}

if($GAME_VARS['uv_planets'] > 0 && avail_check(1000)){
	$error_str .= make_row(array($cw['gensis_device'],$cw['gensis_device'],$genesis_c,$genesis_t,"<a href='$filename?buy=7'>Acheter</a>"));
	$error_str .= make_row(array($cw['creates_planets'],$cw['allows_the_destruction_of_a_planet'],$terra_i_c,$terra_i_t,"<a href='$filename?buy=8'>".$cw['buy']."</a>"));
}
$error_str .= "</table>";

$error_str .= $st[422];

print_page("Blackmarket Upgrades",$error_str);
?>
