<?php
require("user.inc.php");

$num_ships = ship_counter($user['login_id']);

$ship_type = $_REQUEST['ship_type'];
$ship_name = $_REQUEST['ship_name'];


//cannot access this page in SD.
//doesn't use the status checker, because this page is loaded when players have no ships and have just joined the game.
if($num_ships['total_ships'] < 1 && $GAME_VARS['sudden_death'] && $user['login_id'] != 1 && $user['last_login'] != 0) {
	print_page($cw['sudden_death'],$st[698]);
}

if($user['location'] != 1) {
	print_page($cw['error'],$st[699]);
}

$rs = "<p /><a href='earth.php'>".$cw['back_earth']."</a>";
$rs .= "<br /><a href='earth.php?ship_shop=1'>".$cw['return_ship_shop']."</a>";
$error_str = "";


if(!isset($duplicate) && !isset($ship_designer)){ #don't want to load ship details this early for the duplicator.

	if(isset($mass)){
		$ship_stats = load_ship_types((int)$mass);
		$take_flag = 1;
	} else {
		$ship_stats = load_ship_types((int)$ship_type);
		$take_flag = 1;
	}

	if(!isset($ship_stats) && $user['game_login_count'] != '0'){
		print_page($cw['error'],$st[700]);
	}

	if(!isset($ship_stats['config'])) {
		$ship_stats['config'] = "";
	}
} else {
	$ship_stats = load_ship_types((int)$user_ship['shipclass']);
}


//The Brob Test!!!
if($user['one_brob'] > 0 && !isset($duplicate) && !isset($mass)) {
	if(config_check("oo", $ship_stats)){
		$ship_stats['cost'] = $ship_stats['cost'] * $user['one_brob'];
	}
	db("select ship_id from ${db_name}_ships where login_id = '$user[login_id]' && config REGEXP 'oo'");
	$results = dbr(1);
	if(!empty($results['ship_id'])){//already have a oo on hand.
		$got_a_brob = 1;
	} else {
		$got_a_brob = 0;
	}
} else {
	$got_a_brob = 0;
}

if(isset($mass)) { //Bulk Purchase of ships
	#ensure users don't enter equations in place of numbers.
	settype($num, "integer");

	#the brob - a big no-no
	if($mass == 12){
		$error_str .= $st[701];
	}else { #do the processing.
		$cost_text = sprintf($st[702],$ship_stats[cost]);
		!isset($ship_name) ? $ship_name = "" : 1;
		bulk_buy_1($num,$ship_name,$ship_stats['cost'],0,$ship_stats,"mass",$cost_text);
	}

#ship duplicator
} elseif(isset($duplicate) ){
	settype($num, "integer");

	#initial checks to see if the ship is viable for duplication
	if($user_ship['shipclass'] < 2){
		$error_str .= $st[703];
	} elseif($user_ship['shipclass'] == 2){
		$error_str .= $st[704];
	} elseif($num_ships['war_reached'] && config_check("bs", $user_ship)) {
		$error_str = sprintf($st[326],$num_ships[warships], $GAME_VARS[max_warships]);
	} elseif($num_ships['other_reached'] && !config_check("bs", $user_ship)) {
		$error_str = sprintf($st[327],$num_ships[other_ships],$GAME_VARS[max_other_ships]);
	} elseif($user_ship['num_pc'] > 0 || $user_ship['num_ew'] > 0 || config_check("e2", $user_ship) || config_check("bo", $user_ship)){
		$error_str .= $st[707];
	} elseif(config_check("oo", $ship_stats)){
		$error_str .= $st[708];
	} else {
		$blueprints = load_ship_types($user_ship['shipclass']);

		#more specific checks to see if anything has been done to the old ship
		if(!isset($blueprints)){
			$error_str .= $st[709];
		} elseif($user['cash'] < $blueprints['cost']) {
			$error_str .= $st[710];
		} elseif($blueprints['tcost'] > 0){
			$error_str .= $st[711];
		} else {

			//detect if a variable has been changed
			function det_change($input_var){
				global $changed_factor,$blueprints,$user_ship;
				if($blueprints[$input_var] != $user_ship[$input_var]) {
					$changed_factor[$input_var] = $user_ship[$input_var] - $blueprints[$input_var];
				}
			}

			det_change("max_shields");
			det_change("max_fighters");
			det_change("max_armour");
			det_change("cargo_bays");
			det_change("num_dt");
			det_change("num_ot");

			$notes_str = "";

			//can't replicate the :bs
			if(!config_check("bs", $blueprints) && config_check("bs", $user_ship)) {
				$user_ship['config'] = str_replace("bs","",$user_ship['config']);
				$notes_str .= "<br /> - ".$st[712];
				//add extra upgrade to user_ship. This will allow for the one used by the BS upgrade.
				$user_ship['upgrade_slots'] ++;
				$bs_change = 1;
			}

			//compare the configs.
			if($blueprints['config'] != $user_ship['config']) {
				if($blueprints['config'] == ""){
					$changed_factor['config'] = $user_ship['config'];
				} else {// Remove original config if config has been changed.
					$changed_factor['config'] = str_replace($blueprints['config'].",","",$user_ship['config']);
				}
			}

			if(!isset($changed_factor)){ //Ship is same as normal ones. So may as well not duplicate
				$error_str .= $st[713];
				if(isset($bs_change)){
					$error_str .= "<p />".$st[714];
				}

			} else {
				
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

				$xtra_cost = 0;
				$up_txt = "";

				if(isset($changed_factor['config'])){ //work out upgrade costs && upgrade names within the config
					if(config_check("sc", $changed_factor)) {
						$temp_cost = get_cost("sc");
						$scanner_cost = $temp_cost['cost'];
						$xtra_cost += $scanner_cost;
						$up_txt .= "1 ".$cw['scanner'].": <b>$scanner_cost</b><br />";
					}
					if(config_check("sh", $changed_factor)) {
						$temp_cost = get_cost("sh");
						$shield_charger = $temp_cost['cost'];
						$xtra_cost += $shield_charger;
						$up_txt .= "1 ".$st[715].": <b>$shield_charger</b><br />";
					}
					if(config_check("ws", $changed_factor)) {
						$temp_cost = get_cost("ws");
						$stabiliser_upgrade = $temp_cost['cost'];
						$xtra_cost += $stabiliser_upgrade;
						$up_txt .= "1 ".$st[716].": <b>$stabiliser_upgrade</b><br />";
					}
					if(config_check("ls", $changed_factor)) {
						$f_cloak_cost = round($cloak_cost * ($user_ship['size'] / 100)) * 15;
						$xtra_cost += $f_cloak_cost;
						$up_txt .= "1 ".$st['cloak'].": <b>$f_cloak_cost</b><br />";
					}
					
					if(config_check("e1", $changed_factor)) {
						$f_engine_cost = round($engine_upgrade * ($user_ship['size'] / 100)) * 15;
						$xtra_cost += $f_engine_cost;
						$up_txt .= "1 ".$cw['engine_upgrade'].": <b>$f_engine_cost</b><br />";
					}
				}# done with config


				if(!empty($changed_factor['num_ot'])){ //xtra offensive turrets
					$temp_a = round($pea_turret * ($user_ship['size'] / 100) * 15) * $changed_factor['num_ot'];
					$xtra_cost += $temp_a;
					$up_txt .= $changed_factor['num_ot']." ".$st[718].": <b>$temp_a</b><br />";
				}
				if(!empty($changed_factor['num_dt'])){ //xtra defensive turrets
					$temp_a = round($defensive_turret * ($user_ship['size'] / 100) * 15) * $changed_factor['num_dt'];
					$xtra_cost += $temp_a;
					$up_txt .= $changed_factor['num_dt']." ".$cw['defensive_turrets'].": <b>$temp_a</b><br />";
				}
				if(!empty($changed_factor['max_shields'])){ //xtra shield cap
					$temp_a = $basic_cost * ceil($changed_factor['max_shields'] / $shield_inc);
					$xtra_cost += $temp_a;
					$up_txt .= $changed_factor['max_shields']." ".$st[719].": <b>$temp_a</b><br />";
				}
				if(!empty($changed_factor['max_fighters'])){ //xtra fig cap
					//there are more fighters on the ship than would be allowed if the ship wasn't a BS.
					if(isset($bs_change) && $user_ship['max_fighters'] > $max_non_warship_fighters){
						$temp_figs_inc = floor(($max_non_warship_fighters - $blueprints['max_fighters']) / $fighter_inc);
						$changed_factor['max_fighters'] = $temp_figs_inc * $fighter_inc;
						$user_ship['upgrade_slots'] += $temp_figs_inc; //inc number upgrade slots free
						$notes_str .= "<br /> -".sprintf($st[720],$max_non_warship_fighters);
					}

					$temp_a = $basic_cost * ceil($changed_factor['max_fighters'] / $fighter_inc);
					$xtra_cost += $temp_a;
					$up_txt .= $changed_factor['max_fighters']." ".$cw['extra_fighter_cap'].": <b>$temp_a</b><br />";
				}
				if(!empty($changed_factor['max_armour'])){ //xtra fig cap
					$temp_a = $basic_cost * ceil($changed_factor['max_armour'] / $armour_inc);
					$xtra_cost += $temp_a;
					$up_txt .= $changed_factor['max_armour']." ".$cw['extra_armour_cap'].": <b>$temp_a</b><br />";
				}
				if(!empty($changed_factor['cargo_bays'])){ //xtra cargo cap
					$temp_a = $basic_cost * ceil($changed_factor['cargo_bays'] / $cargo_inc);
					$xtra_cost += $temp_a;
					$up_txt .= $changed_factor['cargo_bays']." ".$cw['extra_cargo_cap'].": <b>$temp_a</b><br />";
				}

				$total_cost = $blueprints['cost'] + $xtra_cost; //total cost of each new ship

				if($user['cash'] < $total_cost) {//check user has sufficient money
					$error_str .= sprintf($st[921],$blueprints[name], $blueprints[cost],$up_txt, $total_cost);
				} else {
					if(!isset($ship_name)){
						$ship_name = "";
					}
					bulk_buy_1($num,$ship_name,$total_cost,1,$blueprints,$cw['duplicate'],sprintf($st[722],$blueprints[name], $blueprints[cost], $up_txt, $notes_str, $total_cost));
				}
			}
		}
	}

	print_page($cw['ship_duplicator'],$error_str);


//single ship build
} else {

	if($num_ships['war_reached'] && config_check("bs", $ship_stats)) {
		$error_str = sprintf($st[326],$num_ships[warships], $GAME_VARS[max_warships]);

	} elseif($num_ships['other_reached'] && !config_check("bs", $ship_stats)) {
		$error_str = sprintf($st[327],$num_ships[other_ships], $GAME_VARS[max_other_ships]);

	}elseif($got_a_brob == 1 && config_check("oo", $ship_stats)) {
		$error_str .= $st[725] ;

	}elseif($ship_type == 1 || $ship_type == 0) {
		$error_str = $st[726];

	}elseif($user['cash'] < $ship_stats['cost']) {
		$error_str = sprintf($st[727], $ship_stats[name]);

	} elseif(!isset($ship_name)) {
		get_var($cw['name_your_new_ship'],'ship_build.php',sprintf($st[728], $num_ships[total_ships], $ship_stats[name]),'ship_name','');

	} else {
		take_cash($ship_stats['cost']);

		// remove old escape pods
		dbn("delete from ${db_name}_ships where login_id = '$user[login_id]' && class_name REGEXP 'Escape'");

		$ship_name = correct_name($ship_name);

		if(empty($user_ship['fleet_id']) || $user_ship['fleet_id'] < 1){
			$user_ship['fleet_id'] = 1;
		}

		// build the new ship 
		$q_string = "insert into ${db_name}_ships (";
		$q_string = $q_string . "ship_name, login_id, clan_id, shipclass, class_name, class_name_abbr, fighters, max_fighters, max_shields, armour, max_armour, cargo_bays, mine_rate_metal, mine_rate_fuel, config, size, upgrade_slots, move_turn_cost, point_value, num_dt, num_ot, num_pc, num_ew, fleet_id";
		$q_string = $q_string . ") values(";
		$q_string = $q_string . "'" . mysql_real_escape_string($ship_name) . "', '$login_id', '$user[clan_id]', '$ship_stats[type_id]', '$ship_stats[name]', '$ship_stats[class_abbr]', '$ship_stats[fighters]', '$ship_stats[max_fighters]', '$ship_stats[max_shields]', '$ship_stats[max_armour]', '$ship_stats[max_armour]', '$ship_stats[cargo_bays]', '$ship_stats[mine_rate_metal]', '$ship_stats[mine_rate_fuel]', '$ship_stats[config]', '$ship_stats[size]', '$ship_stats[upgrade_slots]', '$ship_stats[move_turn_cost]', '$ship_stats[point_value]', '$ship_stats[num_dt]', '$ship_stats[num_ot]', '$ship_stats[num_pc]', '$ship_stats[num_ew]', '$user_ship[fleet_id]')";
		dbn($q_string);

		$new_ship_id = mysql_insert_id();

		#the game goes all screwy if a player get's hold of ship_id 1.
		if($new_ship_id == 1){
			$new_ship_id = 2;
			dbn("update ${db_name}_ships set ship_id = '2' where ship_id = '1'");
		}

		dbn("update ${db_name}_users set ship_id = '$new_ship_id' where login_id = '".$user['login_id']."'");
		$user['ship_id'] = $new_ship_id; 
		get_user_ship($new_ship_id);

		$oo_str = "";
		if(config_check("oo", $ship_stats)) {
			if($user['one_brob']){
				dbn("update ${db_name}_users set one_brob = one_brob + one_brob where login_id = '$user[login_id]'");
			} else {
				dbn("update ${db_name}_users set one_brob = 2 where login_id = '$user[login_id]'");
			}
			$oo_str = "<p />".$st[729];
		}
	 
		$error_str .= sprintf($st[730], $ship_stats[cost], $ship_stats[name], $user_ship[fleet_id]);

		if($user_ship['fighters'] < $user_ship['max_fighters'] && $user_ship['max_fighters'] > 0){
			$error_str .= "<p /><a href='equip_shop.php?buy=1'>".$cw['buy_some_fighter']."</a>";
		}
		if($user_ship['upgrade_slots'] > 0){
			$error_str .= "<br /><a href='upgrade.php'>".$st[731]."</a>";
		}
	}
}

// print page
print_page($cw['ship_built'],$error_str);

?>