<?php
require_once("user.inc.php");

$filename = "equip_shop.php";

$status_bar_help = "?topic=Equipment";

if($user['location'] != '1') {
	print_page($cw['error'],$st[1041]);
}

ship_status_checker();


$error_str = "";

if($GAME_VARS['alternate_play_1'] == 1){
	$ship_stats = load_ship_types($user_ship['shipclass']);
	$mining_switch_cost = round($ship_stats['cost']/10);
}

//fighter cost is now based upon the admin var.
//$fighter_cost = 100;
$fighter_cost = $GAME_VARS['fighter_cost_earth'];
if($fighter_cost <= 0){
	$fighter_cost = 1;
}


$genesis_cost = $GAME_VARS['cost_genesis_device'];
$bomb_cost = $GAME_VARS['bomb_cost'];


$rs = "<p /><a href='equip_shop.php'>".$cw['return_to_equipment_shop']."</a>";

settype($amount, 'int');
$amount = round($amount);


if(isset($switch)){//someone is switching mining types.
	if($GAME_VARS['alternate_play_1'] != 1){//check to see if alternate style of play is in effect.
		$error_str .= $st[1042];
	} elseif($user['cash'] < $mining_switch_cost){
		$error_str .= $st[1043];
	} elseif($user_ship['mine_rate_metal'] < 1 && $user_ship['mine_rate_fuel'] < 1) {
		$error_str .= $st[1044];
	}elseif(!isset($sure)) {
		get_var($cw['switch_mining'],$filename,sprintf($st[1045], $user_ship[mine_rate_metal], $user_ship[mine_rate_fuel], $mining_switch_cost, $user_ship[mine_rate_fuel], $user_ship[mine_rate_metal] ),'sure',''); 
	} else {
		take_cash($mining_switch_cost);
		dbn("update ${db_name}_ships set mine_rate_fuel = $user_ship[mine_rate_metal], mine_rate_metal = $user_ship[mine_rate_fuel] where ship_id = '$user_ship[ship_id]'");
		$temp4854 = $user_ship['mine_rate_metal'];
		$user_ship['mine_rate_metal'] = $user_ship['mine_rate_fuel'];
		$user_ship['mine_rate_fuel'] = $temp4854;
		$error_str .= sprintf($st[1046], $mining_switch_cost);
	}

} elseif(isset($mass_switch)){ //switch the fleet
	db("select sum(s.cost / 10)as cost, count(ship_id) as num from se_ship_types s, ${db_name}_ships us where s.type_id = us.shipclass && location='$user[location]' && us.login_id='$user[login_id]' && ship_id != 1 && (s.mine_rate_fuel > 1 || s.mine_rate_metal > 1) group by us.login_id");
	$total_cost = dbr();
	$total_cost['cost'] = round($total_cost['cost']);
	if($GAME_VARS['alternate_play_1'] != 1){
		$error_str .= $st[1047];
	} elseif($user['cash'] < $total_cost['cost']){
		$error_str .= sprintf($st[1048], $total_cost[num], $total_cost[cost]);
	} elseif($total_cost['num'] < 1){
		$error_str .= $st[1049];
	}elseif(!isset($sure)) {
		get_var($cw['switch_mining'],$filename,sprintf($st[1050], $total_cost[num], $total_cost[cost]),'sure',''); 
	} else {
		take_cash($total_cost['cost']);
		db("select ship_id,mine_rate_fuel,mine_rate_metal from ${db_name}_ships where location='$user[location]' && login_id='$user[login_id]' && ship_id != 1 && (mine_rate_fuel > 1 || mine_rate_metal > 1)");
		while($results=dbr()){
			dbn("update ${db_name}_ships set mine_rate_fuel = '$results[mine_rate_metal]', mine_rate_metal = '$results[mine_rate_fuel]' where ship_id = $results[ship_id]");
		}

		$temp4854 = $user_ship['mine_rate_metal'];
		$user_ship['mine_rate_metal'] = $user_ship['mine_rate_fuel'];
		$user_ship['mine_rate_fuel'] = $temp4854;
		$error_str .= sprintf($st[1051], $total_cost[cost],  $total_cost[num]);
	}
}

//function that allows for quick and simple purchase of basic items.
function buy_basic ($item_sql, $item_max_sql, $item_str, $cost){
	global $amount, $user, $user_ship, $db_name, $st, $cw;
	settype($amount, "int"); //security check

	$ret_str = "";

	if($user_ship[$item_sql] >= $user_ship[$item_max_sql]){
		$ret_str .= sprintf($st[1052], $item_str);

	} elseif($amount < 1) {
		$amount_can_buy = floor($user['cash'] / $cost);
		if($amount_can_buy > $user_ship[$item_max_sql] - $user_ship[$item_sql]) {
			$amount_can_buy = $user_ship[$item_max_sql] - $user_ship[$item_sql];
		}

		get_var($cw['buy']." $item_str",'equip_shop.php',sprintf($st[759], $item_str),'amount',$amount_can_buy); 

	} else {
		$total_cost = $amount * $cost;
		if($user['cash'] < $total_cost) {
			$ret_str .= $st[1054]."<b class='b1'>$item_str</b>.<p />";
		} elseif($user_ship[$item_sql] + $amount > $user_ship[$item_max_sql]) {
			$ret_str .= $st[1055]." <b class='b1'>$item_str</b>.<p />";
		} else {
			$ret_str .= "<b>$amount</b> <b class='b1'>$item_str</b> ".$cw['purchased_for']." <b>$total_cost</b> ".$cw['credits.']."<p />";
			take_cash($total_cost);

			dbn("update ${db_name}_ships set $item_sql = $item_sql + '$amount' where ship_id = '$user_ship[ship_id]'");
			
			$user_ship[$item_sql] += $amount;
		}
	}
	return $ret_str;
}


// checks
if(isset($buy)) {
	if($buy == 1) { //fighters
		$error_str .= buy_basic('fighters', 'max_fighters', $cw['fighters'], $fighter_cost);

	} elseif($buy == 2) { //shields
		$error_str .= buy_basic('shields' , 'max_shields', $cw['shields'] , $shield_cost);

	} elseif($buy == 3) { //armour
		$error_str .= buy_basic('armour', 'max_armour', $cw['armour-units'], $armour_cost);

	} elseif($buy == 5) { // genesis device
		//if($GAME_VARS['uv_planets'] > 0 && $user['login_id'] != 1){
		//	$error_str .= "The admin has set it so as genesis devices are un-available.";
		if(!avail_check(1000)){
			$error_str .= $st[1056];
		} elseif(!isset($sure)) {
			get_var($cw['buy-genesis_device'],$filename,$st[1057],'sure',''); 
		} else {
			if($user['cash'] < $genesis_cost) {
				$error_str .= $st[1058];
			} else {
				$error_str .= sprintf($st[1059], $genesis_cost);
				take_cash($genesis_cost);
				dbn("update ${db_name}_users set genesis = genesis + 1 where login_id = $user[login_id]");
			}
		}
	} elseif($buy == 7) { // alpha bomb
		if(!avail_check(1001)){
			$error_str .= $st[1060];
		} elseif(!isset($sure)) {
			get_var($cw['buy_alpha_bomb'],$filename,$st[1061],'sure',''); 
		} else {
			if($user['cash'] < $bomb_cost) {
				$error_str .= $st[1062];
			} elseif ((!$GAME_VARS['bomb_flag']) || ($user['login_id'] ==1)) {
				$error_str .= sprintf($st[1063], $bomb_cost);
				take_cash($bomb_cost);
				dbn("update ${db_name}_users set alpha = alpha + 1 where login_id = $user[login_id]");
			} else {
				$error_str .= $st[1064];
			}
		}
	}

} elseif(isset($fill_fleet)) { //fill fleet functionality

	if($fill_fleet == 1){ //fighters
		$error_str .= fill_fleet('fighters', 'max_fighters', $cw['fighters'], $fighter_cost);

	} elseif($fill_fleet == 2){ //shields
		$error_str .= fill_fleet('shields' , 'max_shields', $cw['shields'], $shield_cost);

	} else { //armour
		$error_str .= fill_fleet('armour', 'max_armour', $cw['armour-units'], $armour_cost);
	}
}


db("select * from ${db_name}_ships where ship_id = $user[ship_id]");

$vaisseau_controle = dbr(1);

$error_str .= $st[1065];
$error_str .= $st[1066];
$error_str .= "<table class='equip_shop'><tr><th>Type</th><th>Coût</th><th>Vaisseau (".$vaisseau_controle['ship_name'].")</th><th>Flotte complète</th><th></th></tr>";
$error_str .= "<tr><td>".$cw['fighters']."</td><td> <b>$fighter_cost</b> / unité</td><td><center><a href='$filename?buy=1'>Equiper vaisseau</center></a>  </td><td>  <a href='$filename?fill_fleet=1'>".$cw['fill_fleet']."</a></td>  <td>".popup_help("help.php?topic=Combat&popup=1&sub_topic=-_Chasseurs",440,225)."</td></tr>";

$error_str .= "<tr><td>".$cw['shields']."</td><td> <b>$shield_cost</b> / unité</td><td><center><a href='$filename?buy=2'>Equiper vaisseau</center></a> </td><td>  <a href='$filename?fill_fleet=2'>".$cw['fill_fleet']."</a></td>  <td>".popup_help("help.php?topic=Combat&popup=1&sub_topic=-_Boucliers",400,205)."</td></tr>";

$error_str .= "<tr><td>".$cw['armour']."</td><td> <b>$armour_cost </b> / unité </td><td><center><a href='$filename?buy=3'>Equiper vaisseau</center></a></td><td><a href='$filename?fill_fleet=3'>".$cw['fill_fleet']."</a></td>  <td>".popup_help("help.php?topic=Combat&popup=1&sub_topic=-_Coques",440,260)."</td></tr>";


if($GAME_VARS['alternate_play_1'] == 1){
	$error_str .= "<tr><td>".$cw['mining_switcher']."</td><td><b>".nombre($mining_switch_cost)."</b></td><td><center><a href='$filename?switch=1'>Equiper vaisseau</a></center></td><td><a href='$filename?mass_switch=1'>".$cw['switch_fleet']."</td></tr></a></table>";
}

	$error_str .= "<table class='equip_shop'><tr><th>Type</th><th>Coût</th><th></th></tr>";

if($user['login_id'] == 1 || avail_check(1000)){
	$error_str .= $st[1067];
	$error_str .= "<tr><td>".$cw['genesis_device']."</td><td><b>".nombre($genesis_cost)."</b></td><td><a href='$filename?buy=5'>Acheter</a></td></tr>";
}

if ((!$GAME_VARS['bomb_flag']) || ($user['login_id'] ==1)) {
	if(avail_check(1001)){
		$error_str .= "<tr><td>".$cw['alpha_bomb']."</td><td><b>".nombre($bomb_cost)."</b></td><td><a href='$filename?buy=7'>Acheter</a></td></tr> ";
	}
	/*if(avail_check(1002)){
		$error_str .= "<br /><a href='$filename?buy=6'>Gamma Bomb</a>: $bomb_cost";
	}*/
}
$error_str .= "</table>";
$error_str .= "<br /><br /><p /><a href='help.php?topic=Equipment' target='_blank'>".$st[1068]."</a>";

$rs = "<p /><a href='earth.php'>".$cw['return_to_earth']."</a>";


print_page($cw['equipment_shop'],$error_str);
?>
