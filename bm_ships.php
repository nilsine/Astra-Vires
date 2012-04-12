<?php
require_once("user.inc.php");

$filename = 'bm_ships.php';
$status_bar_help = "?topic=Blackmarkets";

ship_status_checker();
$rs = "";
$error_str = "";

if(isset($from_0)){
 	$rs = "<p /><a href='black_market.php'>".$cw['return_to_blackmarket']."</a>";
}
$rs .= "<p /><a href='location.php'>".$cw['close_contact']."</a>";


#Blackmarket Controls

db("select * from ${db_name}_bmrkt where location = '$user[location]' && (bmrkt_type = 1 || bmrkt_type = 0)");
$bmrkt = dbr(1);

if (empty($bmrkt)) {
	print_page($cw['blackmarket'],$st[362],"?topic=Blackmarkets");
} elseif($GAME_VARS['uv_num_bmrkt'] == 0) {
	print_page($cw['error'],$st[363],"?topic=Blackmarkets");
}




#black market multiple ship purchase.

if(isset($mass)) { //Bulk Purchase of ships
	#ensure users don't enter equations in place of numbers.
	settype($num, "integer");

	$num_ships = ship_counter($user['login_id']);

	$ship_stats = load_ship_types((int)$mass);

	#the ABstar - a big no-no
	if($mass == 399){
		$error_str .= $st[364];
	} elseif($mass < 300){
		$error_str .= $st[365];
	} elseif($user['cash'] < $ship_stats['cost']){
		$error_str .= $st[366];
	} elseif($user['tech'] < $ship_stats['tcost']){
		$error_str .= $st[367];
	} else { #do the processing.
		$cost_text = sprintf($st[368], $ship_stats[cost], $ship_stats[tcost]);
		if(!isset($ship_name)){
			$ship_name = "";
		}
		bulk_buy_1($num,$ship_name,$ship_stats['cost'],0,$ship_stats,"mass",$cost_text);
	}

}


#
#Beginning of Blackmarket ship single purchase
#

if (isset($ship_type)){
	$ship_stats = load_ship_types((int)$ship_type);
	$take_flag = 1;

	if(!isset($ship_stats) && $user['game_login_count'] != '0'){
		print_page($cw['error'],$st[369],"?research=1");
	}

	if(!isset($ship_stats['config'])) {
		$ship_stats['config'] = "";
	}

	//begin ship checks

	if($user['one_brob'] && config_check("oo",$ship_stats)) {
		db("select ship_id from ${db_name}_ships where login_id = '$user[login_id]' && config REGEXP 'oo'");
		$results = dbr();

		if(!empty($results['ship_id'])){
			print_page($cw['flagship'],$st[370]);
		} else {
			$ship_stats['cost'] = $ship_stats['cost'] * $user['one_brob'];
			$ship_stats['tcost'] = $ship_stats['tcost'] * $user['one_brob'];
		}
	}

	$num_ships = ship_counter($user['login_id']);

	if($num_ships['war_reached'] && config_check("bs",$ship_stats)) {
		$error_str = sprintf($st[371], $num_ships[warships], $GAME_VARS[max_warships]);
	} elseif($num_ships['other_reached'] && !config_check("bs",$ship_stats)) {
		$error_str = sprintf($st[372], $num_ships[other_ships], $GAME_VARS[max_other_ships]);
	}elseif($ship_type == 1 || $ship_type == 0) {
		$error_str = $st[373];
	}elseif(!avail_check($ship_type)){
		$error_str .= $st[374];
	}elseif($user['cash'] < $ship_stats['cost']) {
		$error_str = sprintf($st[375], $ship_stats[name]);
	}elseif($user['tech'] < $ship_stats['tcost']) {
		$error_str = sprintf($st[376], $ship_stats[name]);
	} elseif(!isset($ship_name)) {
		$rs = "<p /><a href='bm_ships.php'>".$cw['return_to_blacmarket_ships']."</a>";
		get_var($cw['name_your_new_ship'],'bm_ships.php',sprintf($st[377], $num_ships[total_ships])."<b class='b1'>$ship_stats[name]</b>:(20 Char Max)",'ship_name','');
	} else {
		take_cash($ship_stats['cost']);
		take_tech($ship_stats['tcost']);

		// remove old escape pods
		dbn("delete from ${db_name}_ships where login_id = '$user[login_id]' && class_name REGEXP 'Escape'");

		$ship_name = correct_name($ship_name);

		if(empty($user_ship['fleet_id']) || $user_ship['fleet_id'] < 1){
			$user_ship['fleet_id'] = 1;
		}

	// build the new ship 
		$q_string = "insert into ${db_name}_ships (";
		$q_string = $q_string . "ship_name, login_id, location, clan_id, shipclass, class_name, class_name_abbr, fighters, max_fighters, max_shields, armour, max_armour, cargo_bays, mine_rate_metal, mine_rate_fuel, config, size, upgrade_slots, move_turn_cost, point_value, num_pc, num_ot, num_dt, num_ew, fleet_id";
		$q_string = $q_string . ") values(";
		$q_string = $q_string . "'$ship_name', '$login_id', $user[location], '$user[clan_id]', '$ship_stats[type_id]', '$ship_stats[name]', '$ship_stats[class_abbr]', '$ship_stats[fighters]', '$ship_stats[max_fighters]', '$ship_stats[max_shields]', '$ship_stats[max_armour]', '$ship_stats[max_armour]', '$ship_stats[cargo_bays]', '$ship_stats[mine_rate_metal]', '$ship_stats[mine_rate_fuel]', '$ship_stats[config]', '$ship_stats[size]', '$ship_stats[upgrade_slots]', '$ship_stats[move_turn_cost]', '$ship_stats[point_value]', '$ship_stats[num_pc]', '$ship_stats[num_ot]', '$ship_stats[num_dt]', '$ship_stats[num_ew]', '$user_ship[fleet_id]')";
		dbn($q_string);

		$new_ship_id = mysql_insert_id();

		#the game goes all screwy if a player get's hold of ship_id 1.
		if($new_ship_id == 1){
			$new_ship_id = 2;
			dbn("update ${db_name}_ships set ship_id = '2' where ship_id = '1'");
		}

		dbn("update ${db_name}_users set ship_id = '$new_ship_id' where login_id = '".$user['login_id']."'");
		$user['ship_id'] = $new_ship_id; 
		db("select * from ${db_name}_ships where ship_id = '$user[ship_id]'");
		$user_ship = dbr();
		empty_bays($user_ship);

		$oo_str = "";
		if(config_check("oo",$ship_stats)) {
			if($user['one_brob']){
				dbn("update ${db_name}_users set one_brob = one_brob + one_brob where login_id = '$user[login_id]'");
			} else {
				dbn("update ${db_name}_users set one_brob = 2 where login_id = '$user[login_id]'");
			}
			$oo_str .= $st[378];
		}

		$error_str .= sprintf($st[379], $ship_stats[cost], $ship_stats[tcost], $ship_stats[name]);
		$error_str .= sprintf($st[379], $ship_stats[name]).$oo_str;
	}

	print_page($cw['blackmarket_ship_purchased'],$error_str);

}#end isset


#
#End of bm ship purchase
#

$text = $error_str;


$text .= sprintf($st[381], $bmrkt[bm_name]);
$text .= $st[382];

#list all alient ships.
$ship_types = load_ship_types(0);
$text .= $st[383];
#$text .= make_table(array("Ship Name","Abbrv.","Cash Cost","Tech Cost","Type"));

foreach($ship_types as $num => $ship_stats){
	if($ship_stats['tcost'] == 0){ //skip non-tech ships.
		continue;
	} else {
		if(config_check("oo",$ship_stats)) {
			if($user['one_brob']) {
				$ship_stats['cost'] = $ship_stats['cost'] * $user['one_brob'];
				$ship_stats['tcost'] = $ship_stats['tcost'] * $user['one_brob'];
			}
			$ab_text = "";
		} else {
			$ab_text = "<a href='bm_ships.php?mass=$ship_stats[type_id]'>".$cw['buy_many']."</a>";
		}

		$ship_stats['cost'] = nombre($ship_stats['cost']);

		if(!isset($ships_for_sale[$ship_stats['type']])){
			$ships_for_sale[$ship_stats['type']] = "";
		}

		$txt = make_row(array("<a href='bm_ships.php?ship_type=$ship_stats[type_id]'>$ship_stats[name]</a>", "$ship_stats[class_abbr]", "<b>$ship_stats[cost]</b>", "<b>$ship_stats[tcost]</b>", "<a href='bm_ships.php?ship_type=$ship_stats[type_id]'>".$cw['buy_one']."</a>", $ab_text, popup_help("help.php?popup=1&ship_info=$ship_stats[type_id]&db_name=$db_name",300,400)));
		$ships_for_sale[$ship_stats['type']] .= $txt;
	}
}

if(empty($ships_for_sale)){
	$text .= $st[384];

} else {

	foreach($ships_for_sale as $class => $str){

		$text .= "<p />{$class}s available:";
		if(empty($str)){
			$text .= "<br /><b>".$cw['none']."</b>";
		} else {
			$text .= make_table(array($cw['ship_name'],"Abbrv.",$cw['credit_cost'],$cw['tech_unit_cost']));
			$text .= stripslashes($str);
			$text .= "</table>";
		}
	}

}

#Blackmarket Ships	

$text .= "<p /><a href='help.php?ship_info=-2' target='_blank'>".$st[385]."</a>";

print_page($cw['blackmarket_ships'], $text);

?>
