<?php
require("user.inc.php");

$filename = "upgrade.php";
$status_bar_help = "?topic=Upgrades";

ship_status_checker();


$error_str = "";

if($user['location'] != '1') {
	print_page($cw['error'],$st[602]);
} elseif($user['ship_id'] ==1 && $user['login_id'] !=1) {
	print_page($cw['error'],$st[602].", ".$st[603]);
}

if($user_ship['size'] < 1){
	$user_ship['size'] = 1;
}

#turret costs - based on size of ship
$cost_temp = get_cost("ot");
$pea_turret = round($cost_temp['cost'] * ($user_ship['size'] / 100)) * 15;
$cost_temp = get_cost("dt");
$defensive_turret = round($cost_temp['cost'] * ($user_ship['size'] / 100)) * 15;

#other costs also based on size of ship
$cost_temp = get_cost("ls");
$cloak_cost = round($cost_temp['cost'] * ($user_ship['size'] / 100)) * 15;
$cost_temp = get_cost("e1");
$engine_upgrade = round($cost_temp['cost'] * ($user_ship['size'] / 100)) * 15;

$rs = "<p /><a href='upgrade.php'>".$st[604]."</a>";



// checks
if(isset($buy)) {
	if($buy ==1) { //Fighter capacity
		if($user_ship['max_fighters'] + $fighter_inc > $max_non_warship_fighters && !config_check("bs",$user_ship)) {
			$error_str .= sprintf($st[605], $max_non_warship_fighters)."<p />";
		} else {
			$error_str .= make_basic_upgrade($cw['fighter'],"max_fighters",$fighter_inc,$basic_cost);
		}

	} elseif($buy ==2) { //Shield Capacity
		if (config_check("sj",$user_ship)){ 
			$error_str .= $st[606]."<p />";
		} else {
			$error_str .= make_basic_upgrade($cw['shield'],'max_shields',$shield_inc,$basic_cost);
		}

	} elseif($buy ==3) { //Cargo capacity
		$error_str .= make_basic_upgrade($cw['cargo_bays'],"cargo_bays",$cargo_inc,$basic_cost);

	} elseif($buy ==13) { //Armour capacity
		if (config_check("bo",$user_ship)){ 
			$error_str .= $st[607];
		} else {
			$error_str .= make_basic_upgrade($cw['armour'],"max_armour",$armour_inc,$basic_cost);
		}

	} elseif($buy==4) { //shrouder
		if (config_check("ls",$user_ship)){ 
			$error_str .= $st[608];
		} elseif(!isset($sure)) {
			get_var($st[609],$filename,sprintf($st[610],$user_ship[ship_name]),'sure',''); 
		} else {
			$error_str .= make_standard_upgrade ($cw['shrouding_unit'], "hs", $cloak_cost, 2003);
		}

	} elseif($buy==5) { //Shield Charger
		if ($user_ship['max_shields'] < 1){
			$error_str .= $st[611];
		} elseif(!isset($sure)) {
			get_var($st[612],$filename,sprintf($st[613],$user_ship[ship_name]),'sure',''); 
		} else {
			$cost_temp = get_cost("sh");
			$error_str .= make_standard_upgrade ($cw['shield_charging_upgrade'], "sh", $cost_temp['cost'], 2005);
		}

	} elseif($buy==6) { //Transverser upgrade (wormhole stabiliser)
		if (!config_check("sj",$user_ship)){ 
			$error_str .= $st[614];
		} elseif(!isset($sure)) {
			get_var($st[615],$filename,sprintf($st[616],$user_ship[ship_name]),'sure',''); 
		} else {
			$cost_temp = get_cost("ws");
			$error_str .= make_standard_upgrade ($st[617], "ws", $cost_temp['cost'], 2006);
		}

		
	} elseif($buy==7) { //Scanner
		if(!isset($sure)) {
			get_var($st[618],$filename,sprintf($st[619],$user_ship[ship_name]),'sure',''); 
		} else {
			$cost_temp = get_cost("sc");
			$error_str .= make_standard_upgrade ("Scanner", "sc", $cost_temp['cost'], 2004);
		}

	} elseif($buy==10) { //Pea Turret
		if(isset($many_ot) && $many_ot > 1){
			$pea_turret *= $many_ot;
			$num_to_buy = $many_ot;
		} else {
			$num_to_buy = 1;
		}
		if($user['cash'] < $pea_turret) {
			$error_str .= $st[620];
		}elseif(!avail_check(2000)){
			$error_str .= $st[621];
		} elseif ($user_ship['num_ot'] + ($num_to_buy - 1) >= $max_ot){
			$error_str .= sprintf($st[622],$max_ot);
		} elseif ($user_ship['upgrade_slots'] < $num_to_buy){ 
			$error_str .= $st[624];
		} elseif(!isset($sure)) {
			get_var($st[624],$filename,sprintf($st[625],$num_to_buy,$user_ship[ship_name]),'sure',''); 
		} else {
			$error_str .= sprintf($st[626],$num_to_buy, $user_ship[ship_name],$pea_turret);

			take_cash($pea_turret);

			dbn("update ${db_name}_ships set upgrade_slots = upgrade_slots - '$num_to_buy' ,num_ot = num_ot + '$num_to_buy' where ship_id = '$user[ship_id]'");
			$user_ship['upgrade_slots'] -= $num_to_buy;
			$user_ship['num_ot'] += $num_to_buy;
		}
	
	} elseif($buy==11) { //Defensive Turret
		if(isset($many_dt) && $many_dt > 1){
			$defensive_turret *= $many_dt;
			$num_to_buy = $many_dt;
		} else {
			$num_to_buy = 1;
		}
		if($user['cash'] < $defensive_turret) {
			$error_str .= $st[627];
		}elseif(!avail_check(2001)){
			$error_str .= $st[621];
		} elseif ($user_ship['num_dt'] + ($num_to_buy - 1) >= $max_dt){
			$error_str .= sprintf($st[628],$max_dt);
		} elseif ($user_ship['upgrade_slots'] < $num_to_buy){ 
			$error_str .= $st[623];
		} elseif(!isset($sure)) {
			get_var($st[629],$filename,sprintf($st[630],$num_to_buy,$user_ship[ship_name]),'sure',''); 
		} else {
			$error_str .= sprintf($st[631],$num_to_buy,$user_ship[ship_name],$defensive_turret);

			take_cash($defensive_turret);

			dbn("update ${db_name}_ships set upgrade_slots = upgrade_slots - '$num_to_buy', num_dt = num_dt + '$num_to_buy' where ship_id = '$user[ship_id]'");
			$user_ship['upgrade_slots'] -= $num_to_buy;
			$user_ship['num_dt'] += $num_to_buy;
		}
	
	} elseif($buy ==12) { //Engine Upgrade
		if ($user_ship['move_turn_cost'] < 2){ 
			$error_str .= $st[632];
		} elseif (config_check("e2",$user_ship)){ 
			$error_str .= $st[633];
		} elseif(!isset($sure)) {
			get_var($st[634],$filename,sprintf($st[635],$user_ship[ship_name]),'sure',''); 
		} else {
			$error_str .= make_standard_upgrade ($st[636], "e1", $engine_upgrade, 2007);
			// si le module n'est pas déjà installé
			if (!config_check('e1', $user_ship)) {
				$user_ship['move_turn_cost'] = $user_ship['move_turn_cost'] - 1;
				dbn("update ${db_name}_ships set move_turn_cost = move_turn_cost - 1 where ship_id = '$user[ship_id]'");
			}
		}
	}
}

if(isset($b_buy)) {
	#ensure users don't enter equations in place of numbers.
	settype($num_up, "integer");

	#user should type something in.
	if($num_up < 1) {
		$error_str .= sprintf($st[637],$user_ship[ship_name]);

	#have some free pods?
	} elseif($num_up > $user_ship['upgrade_slots']) {
		$error_str .= $st[638];

	#enough money?
	} elseif(($num_up * $basic_cost) > $user['cash']) {
		$error_str .= $st[639];

	#user not allowed more than 5k figs unless the ship is a battleship.
	} elseif(($user_ship['max_fighters'] + ($fighter_inc * $num_up) > $max_non_warship_fighters) && !config_check("bs",$user_ship) && $b_buy == 1) {
		$error_str .= sprintf($st[640],$max_non_warship_fighters);

	#not allowed shields on a SJ ship.
	} elseif (config_check("sj",$user_ship) && $b_buy == 2){ 
		$error_str .= $st[641];

	#confirmation
	#} elseif(!isset($sure)) {
	#	get_var('Buy Multiple Upgrades',$filename,'Are you sure you want to do a Mass Upgrade?','sure','');

	} else {

		if($b_buy == 1){
			$up_str = $cw['fighters'];
			$up_sql = "max_fighters";
			$inc_amount = $fighter_inc;

		} elseif($b_buy == 2){
			$up_str = $cw['shields'];
			$up_sql = "max_shields";
			$inc_amount = $shield_inc;
		
		} elseif($b_buy == 3){
			$up_str = $cw['cargo_bays'];
			$up_sql = "cargo_bays";
			$inc_amount = $cargo_inc;

		} else {
			$up_str = $cw['armour'];
			$up_sql = "max_armour";
			$inc_amount = $armour_inc;
		}
		$cost = $num_up * $basic_cost;
		$inc_amount *= $num_up;


		$error_str .= sprintf($st[642],$user_ship[ship_name],$up_str,$inc_amount,$cost);
		take_cash($cost);
		dbn("update ${db_name}_ships set $up_sql = $up_sql + '$inc_amount', upgrade_slots = upgrade_slots - '$num_up' where ship_id = '$user_ship[ship_id]'");
		$user_ship['upgrade_slots'] -= $num_up;
		$user_ship[$up_sql] += $inc_amount;
		if($up_sql == "cargo_bays"){
			$user_ship['empty_bays'] += $inc_amount;
		}
	}
}

#ensure user has some upgrade pods free.
if($user_ship['upgrade_slots'] < 1){
	$error_str .= $st[643];

} else {


	$error_str .= sprintf($st[644],$user_ship[upgrade_slots]);
	$error_str .= $st[645];

	if($user_ship['upgrade_slots'] > 1) {
		$error_str .= "<table><tr><td>";
	}

	$error_str .= $cw['basic_upgrades'];
	$error_str .= make_table(array($cw['item_name'],$cw['item_cost']));
	$error_str .= make_row(array("$fighter_inc ".$st[647],$basic_cost,"<a href='$filename?buy=1'>".$cw['buy']."</a>"));
	$error_str .= make_row(array("$shield_inc ".$st[648],$basic_cost,"<a href='$filename?buy=2'>".$cw['buy']."</a>"));
	$error_str .= make_row(array("$armour_inc ".$st[649],$basic_cost,"<a href='$filename?buy=13'>".$cw['buy']."</a>"));
	$error_str .= make_row(array("$cargo_inc ".$st[650],$basic_cost,"<a href='$filename?buy=3'>".$cw['buy']."</a>"));
	$error_str .= "</table>";


	if($user_ship['upgrade_slots'] > 1) {
		$error_str .= "</td><td align=right>";
		$error_str .= "<p />".$st[646].":";
		$error_str .= "<FORM method=get action=upgrade.php>";
		$error_str .= "&nbsp;&nbsp;&nbsp;&nbsp;<select name=b_buy>";
		$error_str .= "<option value=1> + $fighter_inc $st[647]";
		$error_str .= "<option value=2> + $shield_inc $st[648]";
		$error_str .= "<option value=13> + $armour_inc $st[649]";
		$error_str .= "<option value=3> + $cargo_inc $st[650]";
		$error_str .= "</select>";
		$error_str .= " - <input type='text' size='3' name='num_up' />";
		$error_str .= "<p /><input type='submit' value='".$cw['submit']."' /></form><p />";
		$error_str .= "</td></tr></table>";
	}





	/**************************
	* Turrets
	**************************/

	$turret_str = "";
	if(avail_check(2000)){
		$buy_many = "";
		$num = $max_ot - $user_ship['num_ot'];
		if($user_ship['upgrade_slots'] > 1 && $num > 1){
			$buy_many .= "<a href='$filename?buy=10&many_ot=$num'>".$cw['buy']." $num</a>";
		}
		$turret_str .=  make_row(array($cw['pea_shooter'],sprintf($st[652],$max_ot),$pea_turret,"<a href='$filename?buy=10'>".$cw['buy']."</a>",$buy_many, popup_help("help.php?upgrades=1&popup=1&chosen=ot", 350, 315)));
	}
	if(avail_check(2001)){
		$buy_many = "";
		$num = $max_dt - $user_ship['num_dt'];
		if($user_ship['upgrade_slots'] > 1 && $num > 1){
			$buy_many .= "<a href='$filename?buy=11&many_dt=$num'>".$cw['buy']." $num</a>";
		}
		$turret_str .=  make_row(array($cw['defensive_turrets'],sprintf($st[652],$max_dt),$defensive_turret,"<a href='$filename?buy=11'>".$cw['buy']."</a>",$buy_many, popup_help("help.php?upgrades=1&popup=1&chosen=dt", 350, 315)));
	}
	if(!empty($turret_str)){
		$error_str .= "<br /><br />".$cw['turrets'];
		$error_str .= make_table(array($cw['item_name'],$cw['notes'],$cw['item_cost']),"75%");
		$error_str .= $turret_str."</table>";
	}

	/**************************
	* Propulsion Upgrades
	**************************/

	$eng_text = "";
	if (config_check("sj",$user_ship) && avail_check(2006)){
		$cost_temp = get_cost("ws");
		$eng_text .=  make_row(array($st[617],$st[653],$cost_temp['cost'],"<a href='$filename?buy=6'>".$cw['buy']."</a>", popup_help("help.php?upgrades=1&popup=1&chosen=ws", 350, 315)));
	}

	if(avail_check(2007)){
		$engine_changes = "";
		if($GAME_VARS['ship_warp_cost'] == -1){
			$engine_changes = $st[654];
		}
		$eng_text .= make_row(array($st[636],sprintf($st[655],$engine_changes),$engine_upgrade,"<a href='$filename?buy=12'>".$cw['buy']."</a>", popup_help("help.php?upgrades=1&popup=1&chosen=e1", 350, 315)));
	}

	if(!empty($eng_text)){
		$error_str .= "<br /><br />".$st[656];
		$error_str .= make_table(array($cw['item_name'],$cw['notes'],$cw['item_cost']),"75%");
		$error_str .= $eng_text."</table>";
	}


	/**************************
	* Misc Items
	**************************/

	$misc_text = "";
	if(avail_check(2003)){
		$misc_text .=  make_row(array($cw['shrouding_unit'],$st[657],$cloak_cost,"<a href='$filename?buy=4'>".$cw['buy']."</a>", popup_help("help.php?upgrades=1&popup=1&chosen=ls", 350, 315)));
	}
	if(avail_check(2004)){
		$cost_temp = get_cost("sc");
		$misc_text .=  make_row(array($cw['scanner'],$st[658],$cost_temp['cost'],"<a href='$filename?buy=7'>".$cw['buy']."</a>", popup_help("help.php?upgrades=1&popup=1&chosen=sc", 350, 315)));
	}
	if(avail_check(2005)){
		$cost_temp = get_cost("sh");
		$misc_text .=  make_row(array($st[659],$st[660],$cost_temp['cost'],"<a href='$filename?buy=5'>".$cw['buy']."</a>", popup_help("help.php?upgrades=1&popup=1&chosen=sh", 350, 315)));
	}

	if(!empty($misc_text)) {
		$error_str .= "<br /><br />".$cw['misc'];
		$error_str .= make_table(array($cw['item_name'],$cw['notes'],$cw['item_cost']),"75%");
		$error_str .= $misc_text."</table>";
	}

	$error_str .= "<p /><a href='help.php?topic=Upgrades' target='_blank'>".$st[661]."</a>";

}

$rs = "<p /><a href='earth.php'>".$cw['return_to_earth']."</a>";

print_page($cw['accessories_upgrades'],$error_str);








#function for adding 'normal' upgrades to a ship.
function make_basic_upgrade ($upgrade_str, $upgrade_sql, $inc_amount, $cost){
	global $user, $user_ship, $db_name;
	if($user['cash'] < $cost) {
		return $st[662];
	} elseif($user_ship['upgrade_slots'] < 1) {
		return "";
	} else {
		take_cash($cost);
		dbn("update ${db_name}_ships set $upgrade_sql = $upgrade_sql + '$inc_amount', upgrade_slots = upgrade_slots - 1 where ship_id = '$user_ship[ship_id]'");
		$user_ship['upgrade_slots'] --;
		$user_ship[$upgrade_sql] += $inc_amount;

		if($upgrade_sql == "cargo_bays"){
			$user_ship['empty_bays'] += $cargo_inc;
		}

		return sprintf($st[663],$user_ship[ship_name],$upgrade_str,$inc_amount,$cost);
	}
}

?>
