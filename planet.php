<?php
require_once("user.inc.php");

# -----------------------
#functions for planets

$output_str = "";
$status_bar_help = "?topic=Planets";

#work out idle colonist count.
function idle_colonists() {
	global $planet;
	return $planet['colon'] - $planet['alloc_fight'] - $planet['alloc_elect'];
}


#ensure a user can transfer stuff.
function conditions($user,$planet){
	global $GAME_VARS;
		if($user['joined_game'] > (time() - ($GAME_VARS['min_before_transfer'] * 86400)) && ($user['login_id'] != $planet['login_id'] && $planet['fighters'] != 0) && $user['login_id'] != 1) {
			return 1;
		} else {
			return 0;
		}
}

#ensure a user is the owner of the planet and can to transfer stuff
function owned_planet($user,$planet){
	if ($user['login_id'] != $planet['login_id'])
		return 1;
	else
		return 0;
}

//function that will set tech_mat and text_mat depending on the type of mineral being used.
function discern_type($type){
	global $tech_mat, $text_mat, $cw, $st;
	//determine the type of mineral being played with
	if(!isset($type) || $type == 0){
		$type = 0;
		$tech_mat = "fighters";
		$text_mat = $cw['fighters'];
	} elseif($type == 1){
		$tech_mat = "colon";
		$text_mat = $cw['colonists'];
	} elseif($type == 2){
		$tech_mat = "metal";
		$text_mat = $cw['metals'];
	} elseif($type == 3){
		$tech_mat = "fuel";
		$text_mat = $cw['fuels'];
	} else {
		$tech_mat = "elect";
		$text_mat = $cw['electronics'];
	}
}

//function that allows loading and unloading of the fleet, or a select group of ships.
//1st arg dictates whether all ships should be selected, or only a certain few
//2nd arg dictates whether loading (1) or unloading(2)
//3rd arg is for the array of ships for selected ships.
function load_unload_planet($selected_ships, $load_or_unload, $do_ship=0){
	global $user, $user_ship, $db_name, $tech_mat, $text_mat, $planet, $sure, $type, $dump_all, $cw, $st;

	discern_type($type);

	$extra_where = "";
	$captaining = 0;
	if($selected_ships == 1){
		foreach($do_ship as $ship_id){
			$extra_where .= "ship_id = '$ship_id' || ";
			if($ship_id == $user['ship_id']){
				$captaining = 1;
			}
		}
		$extra_where = preg_replace("/\|\| $/", "", $extra_where);
		$extra_where = "&& ($extra_where)";
	}


	if($load_or_unload == 2){ //unloading the ship
		//select all ships that are valid
		db("select sum($tech_mat) as goods, count(ship_id) as ship_count from ${db_name}_ships where login_id = '$user[login_id]' && location = '$user[location]' && $tech_mat > 0 ".$extra_where);
		$results = dbr(1);

		$turn_cost = ceil($results['ship_count'] * 0.75);
		$max_reached = "";
		if(!isset($results['goods'])) { //no goods on any ships
			return sprintf($st[1535],$text_mat);
		} elseif($user['turns'] < $turn_cost && !isset($dump_all)) { //insufficient turns
			return sprintf($st[1536],$turn_cost);
			unset($results);
		} elseif($results['goods'] + $planet['colon'] > $planet['max_population'] && $type == 1) {
			$max_reached = "<b class='b1'>".$cw['warning']."</b>: ".$st[1537];
		} elseif(!isset($sure) && $selected_ships == 0 && !isset($dump_all)) { //confirmation
			get_var($st[1538]." $text_mat",'planet.php',sprintf($st[1539],$text_mat,$results[goods],$results[ship_count],$turn_cost).$max_reached,'sure','yes');
		} else {//finish unloading the planets.
			$t_cost_str = "";
			dbn("update ${db_name}_planets set $tech_mat = $tech_mat + '$results[goods]' where planet_id = '$user[on_planet]'");
			dbn("update ${db_name}_ships set $tech_mat = 0 where login_id = '$user[login_id]' && location = '$user[location]' && $tech_mat > 0 ".$extra_where);
			if(!isset($dump_all)){
				charge_turns($turn_cost);
				$t_cost_str = " pour un coût total de <b>$turn_cost</b> cycles.";
			}
			if($captaining == 1 || ($selected_ships == 0 && $user_ship[$tech_mat] > 0)){
				$user_ship[$tech_mat] = 0;
				empty_bays($user_ship);
			}
			$planet[$tech_mat] += $results['goods'];
			return "<b>$results[goods]</b> ".sprintf($st[1540],$text_mat,$results[ship_count]).$t_cost_str;
		}
	} else {//loading the ship
		$taken = 0; //goods taken from planet so far.
		$ship_counter = 0;
		if($planet[$tech_mat] < 1) { //can't take stuff if there isn't any to take
			return sprintf($st[1541],$text_mat);
		} elseif($type == 0){ //fighters
			db2("select ship_id,(max_fighters - fighters) as free,ship_name, $tech_mat from ${db_name}_ships where login_id = '$user[login_id]' && location = '$user[location]' && (max_fighters - fighters) > 0 $extra_where order by free desc");
		} elseif($type != 0) {
			db2("select ship_id, (cargo_bays - metal - fuel - elect - colon) as free, ship_name, $tech_mat from ${db_name}_ships where login_id = '$user[login_id]' && location = '$user[location]' && (cargo_bays - metal - fuel - elect - colon) > 0 $extra_where order by free desc");
		}
		$ships = dbr2(1);

		$first_mess = "";
		$sec_mess = "";
		$turns_txt = "";
		$out = "";
		if($type == 0 && $planet['allocated_to_fleet'] > 0){//warning about fighter allocation
			$first_mess = $cw['warning'].".<br /> ".$st[1542];
			$sec_mess = $cw['warning'].":<br />".$st[1543];
		}

		if(!isset($ships['ship_id']) && $selected_ships == 0){ //check to see if there are any ships
			return $st[1544]." <b class='b1'>$text_mat</b>.";
		} elseif(!isset($ships['ship_id']) && $selected_ships == 1){ //check to see if there are any ships
		return $st[1545]." <b class='b1'>$text_mat</b>.";
		} else {
			while($ships) {
				$ship_counter++;
				//planet can load ship w/ spare fighters.
				if($ships['free'] < ($planet[$tech_mat] - $taken)) {
					if(isset($sure) || $selected_ships == 1){ //only run during the real thing.
						dbn("update ${db_name}_ships set $tech_mat = $tech_mat + '$ships[free]' where ship_id = '$ships[ship_id]'");
						$out .= "<br /><b class='b1'>$ships[ship_name]</b> : ".sprintf($st[1546],$ships[free],$text_mat);
						if($ships['ship_id'] == $user_ship['ship_id'] && $type == 0){ #update user ship
							$user_ship['fighters'] = $user_ship['max_fighters'];
						} elseif($ships['ship_id'] == $user_ship['ship_id'] && $type > 0){ #update user ship
							$user_ship[$tech_mat] += $ships['free'];
							$user_ship['empty_bays'] -= $ships['free'];
						}
					}
					$taken += $ships['free'];

					//ensure user has enough turns, or stop the loop where the user is.
					if($user['turns'] <= ceil($ship_counter * 0.75)){
						$turns_txt = $st[1547];
						$out .=  $st[1548];
						unset($ships);
						break;
					}

				//planet will run out of fighters.
				} elseif($ships['free'] >= ($planet[$tech_mat] - $taken)) {
					$t868 = $ships[$tech_mat] + ($planet[$tech_mat] - $taken);
					if(isset($sure) || $selected_ships == 1){ #only run during the real thing.
						dbn("update ${db_name}_ships set $tech_mat = '$t868' where ship_id = '$ships[ship_id]'");

						$out .= "<br /><b class='b1'>$ships[ship_name]</b>s ".$st[1549]." <b>$t868</b> <b class='b1'>$text_mat</b>";

						if($ships['ship_id'] == $user_ship['ship_id'] && $type == 0){ #update user ship
							$user_ship['fighters'] = $t868;
						}elseif($ships['ship_id'] == $user_ship['ship_id'] && $type > 0){ #update user ship
							$user_ship[$tech_mat] = $t868;
							empty_bays($user_ship);
						}
					}
					$taken += $t868 - $ships[$tech_mat];
					unset($ships);
					break;
				}
				$ships = dbr2(1);
			} #end loop of ships

			$turn_cost = ceil($ship_counter * 0.75);

			if(!isset($sure) && $selected_ships == 0) {
				get_var($st[1550],'planet.php',$turns_txt.sprintf($st[1551],$ship_counter,$taken,$text_mat,$turn_cost)."<p />".$first_mess,'sure','yes');
			} else {
				dbn("update ${db_name}_planets set $tech_mat = $tech_mat - '$taken' where planet_id = '$user[on_planet]'");
				$planet[$tech_mat] -= $taken;
				if($planet['allocated_to_fleet'] > 0 && $type == 0){
					dbn("update ${db_name}_planets set allocated_to_fleet = 0 where planet_id = '$user[on_planet]'");
				}
				charge_turns($turn_cost);

				if($type == 1){ #colonists
					$planet['alloc_fight'] = 0;
					$planet['alloc_elect'] = 0;
					dbn("update ${db_name}_planets set alloc_fight = 0, alloc_elect=0 where planet_id = '$user[on_planet]'");
					$out .= $st[1552];
				}

				return "<b>$ship_counter</b>".sprintf($st[1553],$taken,$text_mat)." <b class='b1'>$planet[planet_name]</b>:<br />".$out."<p />".$st[1554]." <b>$turn_cost</b>.<p />".$sec_mess;
			}
		}
	}
}


#End of planet functions.
# ---------------------------------
ship_status_checker();

mt_srand((double)microtime()*1000000);

db("select * from ${db_name}_planets where planet_id = '$planet_id'");
$planet = dbr();
$planet_loc = $planet['location'];
$has_pass = 0;
$pre_processed_txt = "";
$defending_fighters = $planet['fighters'] - $planet['allocated_to_fleet'];

//user not in the correct system
if($user['location'] != $planet_loc) {
	print_page($cw['planet'],$st[107]);

} elseif ($user['turns_run'] < $GAME_VARS['turns_before_planet_attack'] && $user['login_id'] != 1){
	print_page($cw['no_landing'],sprintf($st[1555],$GAME_VARS[turns_before_planet_attack]));

//planet has password and user has entered it correctly.
} elseif (!empty($planet['pass']) && $planet['login_id'] != $user['login_id'] && isset($p_pass) && $p_pass == $planet['pass']) {
	$has_pass = 1;
	SetCookie("p_pass",$p_pass,time()+600);

//admin and owner don't need password, same goes for no fighters on planet
} elseif($planet['login_id'] == $user['login_id'] || $user['login_id'] == 1 || $planet['fighters'] < 1 || ($user['clan_id'] > 0 && $user['clan_id'] == $planet['clan_id'] && empty($planet['pass']))){
	$has_pass = 1;

//there is a password on the planet and the user must enter it.
} elseif(!empty($planet['pass']) && empty($p_pass) && $defending_fighters > 0) {
	unset($p_pass, $_GET['p_pass'], $_POST['p_pass']);
	get_var($cw['access_denied'],'planet.php',$st[1556],'p_pass',"");

//invalid password
} elseif(isset($p_pass) && $p_pass != $planet['pass']){
	$rs = "<p /><a href='planet.php?planet_id=$planet_id'>".$cw['try_again']."</a>";
	print_page($cw['Error'],$st[1557]);

} elseif($planet['planet_engaged'] > time()){
	print_page($cw['planet_occupied'],$st[1558]);
} elseif($has_pass == 0 && $defending_fighters > 0 && $user['login_id'] != 1){
	print_page($st['warning'],$st[1559]);
}

//the user is on the planet.
if($has_pass == 1) {
	$user['on_planet'] = $planet_id;
}


//ensure pass is valid, or is about to wipe out old pass.
if(isset($new_pass) && isset($change_pass) && isset($has_pass) && (valid_input($new_pass) || $new_pass == -1)){

	if(levenshtein($new_pass,$p_user['passwd']) < 2) { //password cannot be too similar to account pass
		$output_str .= $st[1560];
	} else {
		if($new_pass == -1){
			$new_pass = 0;
		}

		dbn("update ${db_name}_planets set pass = '$new_pass' where planet_id = '$planet_id'");
		$planet['pass'] = $new_pass;
		$passwd = $new_pass;
		$output_str .= $st[1561];
	}

} elseif(isset($new_pass) && isset($change_pass) && isset($has_pass)){ //invalid password
	print_page($cw['password_change'],$st[1562]."<p /><a href='javascript:back()'>Try Again</a>");
} elseif(isset($change_pass) && !isset($new_pass)) {
	if($user['login_id'] != $planet['login_id'] && $user['login_id'] != 1){
		print_page($cw['error'], $st[1563]);
	} else {
		get_var($cw['planet_password'],'planet.php',$st[1564],'new_pass',"");
	}
}


#ensure $amount is rounded, and an integer.
if(isset($amount)) {
	$amount = round($amount);
	settype($amount, "integer");
}

if($user['on_planet'] > 0) {
	$planet_id = $user['on_planet'];
}

$rs = "<p /><a href='planet.php?planet_id=$planet_id'>".$cw['return_planet']."</a><br />";


#destroy planet
if(isset($destroy)) {
	db("select * from ${db_name}_planets where planet_id = $user[on_planet]");
	if($user['login_id'] == $planet['login_id'] || ($user['clan_id'] == $planets['clan_id'] && $user['clan_id'] > 0)) {
		if($GAME_VARS['uv_planets'] >= 0 && $user['terra_imploder'] <= 0){
			$output_str.= $st[1565]."<br /><br />";
		} elseif($sure != 'yes') {
			get_var($cw['destroy_planet'],'planet.php',$st[1566],'sure','yes');
		} else {
			if($GAME_VARS['uv_planets'] >= 0 && $user['login_id'] > 1){
				dbn("update ${db_name}_users set on_planet = 0, terra_imploder = terra_imploder - 1 where login_id = $user[login_id]");
				$terra_txt = $st[1567];
				$terra_txt2 = $st[1568];
			} else {
				dbn("update ${db_name}_users set on_planet = 0 where login_id = $user[login_id]");
			}
			dbn("update ${db_name}_stars set planetary_slots = planetary_slots + 1 where star_id = '$user[location]'");
			dbn("delete from ${db_name}_planets where planet_id = $user[on_planet]");
			post_news("<b class='b1'>$user[login_name]</b> ".$st[1569]." <b class='b1'>$planet[planet_name]</b>".$terra_txt, $cw['planet']);
			$rs = "<p /><a href='location.php'>".$cw['Back to the Star System']."</a><br />";
			print_page($cw['planet_destroyed'],sprintf($st[1570],$planet[planet_name]).$terra_txt2);
		}
	}else{
		print_page($st[1571],$st[1572]);
	}
} elseif(isset($claim)) {

	//db("select * from ${db_name}_planets where planet_id = '$user[on_planet]'");
	//$planet = dbr();

//var_dump($GAME_VARS['min_before_transfer']);
//var_dump($GAME_VARS['min_before_transfer'] * 86400);
//var_dump($planet['fighters']);
//var_dump($planet['clan_id']);
//var_dump($user['clan_id']);
//var_dump($user['joined_game']);
//var_dump(time() - ($GAME_VARS['min_before_transfer'] * 86400));
//var_dump($user['joined_game'] > (time() - ($GAME_VARS['min_before_transfer'] * 86400)));

	if($planet['login_id'] == $user['login_id']) {
		$output_str .= "<br />".$st[1573]."<p />";
	} elseif(($user['clan_id'] == $planet['clan_id'] || $planet['fighters'] == 0) && $user['joined_game'] > (time() - ($GAME_VARS['min_before_transfer'] * 86400))) {
		$output_str .= "<br />".sprintf($st[1574],$GAME_VARS['min_before_transfer'])."<p />";
	} elseif($planet['research_fac'] > 0 && $GAME_VARS['uv_num_bmrkt'] > 0 && $sure != 'yes') {
		get_var($cw['claim_planet'],'planet.php',"<b class='b1'>Warning.</b><br />".$st[1575],'sure','yes');
	} else {
		send_message($planet['login_id'],"<b class='b1'>$user[login_name]</b> ".sprintf($st[1576],$planet[planet_name]));
		post_news("<b class='b1'>$user[login_name]</b> ".$st[1577]." <b class='b1'>$planet[planet_name]</b>.", $cw['planet']);
		dbn("update ${db_name}_planets set clan_id = '$user[clan_id]', login_id = '$user[login_id]', login_name = '$user[login_name]', research_fac = 0, pass = 0 where planet_id = '$planet[planet_id]'");
		$planet['pass'] = 0;
		$planet['clan_id'] = $user['clan_id'];
		$planet['login_id'] = $user['login_id'];
		$planet['login_name'] = $user['login_name'];
		$has_pass = 1;
		$output_str .= $st[1578];
	}

/*
* Allocate Fighters for planet/fleet defence
*/
} elseif(isset($allocate_figs) || isset($update_allocation)){

	if($planet['fighters'] < 100){ //insufficient fighters.
		$output_str .= $st[1579];
	} elseif($user['login_id'] != $planet['login_id']){ //not planet owner
		$output_str .= $st[1580];
	} elseif(!isset($update_allocation)) {

//javascript that will try to make life easier for the user, by doing the maths for them.
$top_page = "
<SCRIPT LANGUAGE=\"JavaScript\"><!--
function ensure_valid(to_process) {
 if(to_process == 1) { //changed planets
  document.fighter_allocation.fighters_to_fleet.value = $planet[fighters] - document.fighter_allocation.fighters_to_planet.value;
 } else {//changed fleets
  document.fighter_allocation.fighters_to_planet.value = $planet[fighters] - document.fighter_allocation.fighters_to_fleet.value;
 }
}
--></script>
";

		$remaining_figs = $planet['fighters'] - $planet['allocated_to_fleet'];
		$fig_alloc = sprintf($st[1581],$planet[fighters]);
		$fig_alloc .= "<form method=post action=$_SERVER[PHP_SELF] name=fighter_allocation><input type=hidden name=planet_id value=$planet_id /><input type=hidden value=1 name=update_allocation />";

		$fig_alloc .= $st[1582]." <input type=text name=fighters_to_planet value='$remaining_figs' ONBLUR='ensure_valid(1)' />";

		$fig_alloc .= "<p /><b>OR</b>";

		$fig_alloc .= "<p />".$st[1583]." <input type=text name=fighters_to_fleet value='$planet[allocated_to_fleet]' ONBLUR='ensure_valid(2)' />";


		$fig_alloc .= "<p /><input type=reset /> - <input type='submit' />";
		print_page($cw['fighter_allocation'],$top_page.$fig_alloc );

	} else {
		settype($fighters_to_planet, "integer");
		settype($fighters_to_fleet, "integer");
		$p_figs = $planet['fighters'] - $planet['allocated_to_fleet'];
		if($fighters_to_fleet == $planet['allocated_to_fleet'] && $fighters_to_planet == $p_figs){ //no change
			$output_str .= $st[1584];
		} elseif($fighters_to_fleet > $planet['fighters'] || $fighters_to_planet > $planet['fighters']) { //to many
			$output_str .= $st[1585];
		} elseif($fighters_to_fleet < 0 || $fighters_to_planet < 0) { //too few
			$output_str .= $st[1586];
		} else { //something has been changed
			if($fighters_to_planet == $p_figs){//not changed planetary figs. So change fleet figs.
				$new_alloc = $fighters_to_fleet;
			} else {
				$new_alloc = $planet['fighters'] - $fighters_to_planet;
			}
			if($new_alloc > $planet['fighters'] || $new_alloc < 0){//double check
				$output_str .= $st[1587];
			} else {
				$output_str .= "<b>$new_alloc</b> ".$st[1588];
				$planet['allocated_to_fleet'] = $new_alloc;
				$defending_fighters = $planet['fighters'] - $planet['allocated_to_fleet'];
				dbn("update ${db_name}_planets set allocated_to_fleet = '$new_alloc' where planet_id = '$planet_id' && login_id = '$user[login_id]'");
			}
		}
	}


/*
* Create Mining Drones
*/
} elseif(isset($more_drones)) {
	settype($num_drones, "integer");
	$cost = $mining_drone_cost * $num_drones;
	if(!avail_check(4002)){
		$output_str .= $st[1589];
	} elseif($planet['mining_drones'] >= $max_drones){
		$output_str .= $st[1590];
	} elseif($num_drones < 1){
		$default_buy = floor($user['cash'] / $mining_drone_cost);
		if($default_buy + $planet['mining_drones'] > $max_drones){
			$default_buy = $max_drones - $planet['mining_drones'];
		}
		get_var($st[1591],$_SERVER['PHP_SELF'],sprintf($st[1592],$mining_drone_cost,$max_drones,$planet[mining_drones]),'num_drones',$default_buy);
	} elseif(($num_drones + $planet['mining_drones']) > $max_drones){
		$output_str .= $st[1593];
	} elseif($num_drones * $mining_drone_cost > $user['cash']) {//insufficient cash
		$output_str.= $st[1594];
	} elseif(!isset($sure)) {
		get_var($cw['mining_drones'],$_SERVER['PHP_SELF'],sprintf($st[1595],$num_drones,$cost),'sure','yes');
	} else {
		take_cash($cost);
		$planet['mining_drones'] += $num_drones;
		dbn("update ${db_name}_planets set mining_drones = mining_drones + '$num_drones' where planet_id = '$planet_id' && location = '$user[location]'");
		$output_str.= sprintf($st[1596],$num_drones,$cost);
	}


/*
* Allocate Mining Drones
*/
} elseif(isset($allocate_mining_drones) || isset($update_drones)) {
	if($planet['mining_drones'] < 1){ //no drones!!
		$output_str.= $st[1597];
	} elseif($user['login_id'] != $planet['login_id']){ //not planet owner
		$output_str .= $st[1598];
	} elseif(!isset($update_drones)){
		get_star(); //get star details

		$print_str = sprintf($st[1599],$star[star_id])." <br />".$cw['metals'].": <b>$star[metal]</b><br />".$cw['fuels'].": <b>$star[fuel]</b>";
		$print_str .= sprintf($st[1600],$planet[mining_drones]);

		$print_str .= "<form name=drone_allocation method=post action=$_SERVER[PHP_SELF]><input type=hidden name=planet_id value=$planet_id /><input type=hidden value=1 name=update_drones />";

		$print_str .= make_table(array($cw['mineral'],$cw['drones_allocated']));
		$print_str .= make_row(array($cw['metals'],"<input type=text name=drones_metal value='$planet[drones_alloc_metal]' />"));
		$print_str .= make_row(array($cw['fuels'],"<input type=text name=drones_fuel value='$planet[drones_alloc_fuel]' />"));

		$print_str .= "</table><p /><input type=reset /> - <input type='submit' />";

		print_page($st[1601],$print_str);
	} else {
		settype($drones_metal, "integer");
		settype($drones_fuel, "integer");
		if($drones_metal == $planet['drones_alloc_metal'] && $drones_fuel == $planet['drones_alloc_fuel']){ //no change
			$output_str .= $st[1602];
		} elseif($drones_metal > $planet['mining_drones'] || $drones_fuel > $planet['mining_drones'] || ($drones_metal + $drones_fuel > $planet['mining_drones'])) { //too many) { //too many
			$output_str .= $st[1603];
		} elseif($drones_metal < 0 || $drones_fuel < 0) { //too few
			$output_str .= $st[1604];
		} else { //something has been changed
			if($drones_metal != $planet['drones_alloc_metal']){//changed metals.
				$new_metal = $drones_metal;
			} else {//not changed metals
				$new_metal = $planet['drones_alloc_metal'];
			}
			if($drones_fuel != $planet['drones_alloc_fuel']){//changed fuels.
				$new_fuel = $drones_fuel;
			} else {//not changed fuels
				$new_fuel = $planet['drones_alloc_fuel'];
			}
			$still_unalloc = $planet['mining_drones'] - $new_metal - $new_fuel;
			$output_str .= sprintf($st[1605],$new_metal,$new_fuel,$still_unalloc);
			$planet['drones_alloc_metal'] = $new_metal;
			$planet['drones_alloc_fuel'] = $new_fuel;
			dbn("update ${db_name}_planets set drones_alloc_metal = '$new_metal', drones_alloc_fuel = '$new_fuel' where planet_id = '$planet_id' && login_id = '$user[login_id]'");
		}
	}


/*
* Autoshift
*/
} elseif(isset($autoshift)){
	#get ship information/see if there is enough capacity etc.
	db("select count(ship_id), sum(cargo_bays-metal-fuel-elect-colon) from ${db_name}_ships where login_id = '$user[login_id]' && location = '$user[location]' && (cargo_bays-metal-fuel-elect-colon) > 0");
	$ship_count = dbr();
	$colonist_cap = $ship_count[1];			#total cargo capacity of fleet in system
	$colonist = $ship_count[1];				#total cargo capacity of fleet in system
	$ship_count = $ship_count[0];			#number of ships in system that have cargo capacity
	db("select ship_id from ${db_name}_ships where login_id = '$user[login_id]' && location = '$user[location]' && config REGEXP 'ws'");					#ensure there is a transverser with the ws upgrade
	$lead = dbr();

	discern_type($type);
	if(!isset($type) || $type > 4 || $type < 1){
		$output_str .= $st[1606];
	} elseif(!isset($ship_count)) {#ensure there is some cargo cap
		$output_str .= $st[1607]."<p />";
	} elseif($user['cash'] < $GAME_VARS['cost_colonist']) {#can't afford even 1 colonist!
		$output_str .= $st[1608];
	} elseif($user['turns'] < 2) {#ensure there is some cargo cap
		$output_str.= $st[1609];
	} elseif(!isset($lead['ship_id'])) { #ensure there is a transverer with the ws upgrade
		$output_str .= $st[1610]."<p />";
	} elseif($planet['colon'] >= $planet['max_population'] && $type == 1){
		$output_str .= $st[1611];
	} elseif(!isset($dest_system)){ #get the user to select a system from where the colonists are to come from
		$new_page = sprintf($st[1612], $text_mat);
		db2("select planet_id,planet_name from ${db_name}_planets where login_id = '$user[login_id]' && location != '$user[location]' && ((colon - (alloc_fight + alloc_elect) > 0 && '$type' = 1) || ('$type' > 1 && $tech_mat > 0))"); #gets users planet other than ones in the present system.
		$other_sys = dbr2();

		if(!isset($other_sys['planet_id']) && $type > 1){ #determine if there is a suitable target.
			$output_str .= $st[1613]."$text_mat.<p />";
		} else {
			$new_page .= "<form action=planet.php method=POST name=autoshifting>";
			$new_page .= "<select name=dest_system>";

			if($type==1){
				$new_page .= "<option value=-1>".$st[1614]."</option>";
			}

			while ($other_sys) {
				$new_page .= "<option value=$other_sys[planet_id]>$other_sys[planet_name]</option>";
				$other_sys = dbr2();
			}
			$new_page .= "</select>";
			$new_page .= "<input type=hidden name=autoshift value=1 />";
			$new_page .= "<input type=hidden name=type value='$type' />";
			$new_page .= "<input type=hidden name=planet_id value=$planet_id />";
			$new_page .= "<p /><input type='submit' value='".$cw['submit']."' /></form>";
			print_page($st[1615],$new_page);
		}
	} else { #user has selected destination.


		if($dest_system == -1){ #user is getting the colonists from Sol. Thus needs to pay, and there is an infinite source.
			$turn = round(get_star_dist($user['location'],1)/2 +1)*2;	#do maths to work out turn cost to get there
			if($user['turns'] < $turn) { #ensure user has enough turns to get there
				$output_str .= $st[1616]."<p />";
			} else { #main autoshifting bit for taking colonists from Sol
				#determine if player can afford the costs, and if they can, then do the processing
				$c_c = $GAME_VARS['cost_colonist'] * $colonist;
				if($user['cash'] < $c_c || $user[turns] < $turn + $ship_count){
					if($GAME_VARS['cost_colonist'] > 0){//max colonists (cost)
						$colonist = floor($user['cash'] / $GAME_VARS['cost_colonist']);
					}
					$max_reached = "";
					if($colonist + $planet['colon'] > $planet['max_population']){ //max colonists (space on plnt)
						$colonist = $planet['max_population'] - $planet['colon'];
						$max_reached = $st[1617];
					}
					if($colonist_cap > $colonist || $user['turns'] < $turn + $ship_count){
						$free_turns = $user['turns'] - $turn;
						$bays_used = 0;
						$count_quick = 0;

						db2("select sum(cargo_bays-metal-fuel-elect-colon),ship_id from ${db_name}_ships where login_id = '$user[login_id]' && location = '$user[location]' && (cargo_bays-metal-fuel-elect-colon) > 0 group by ship_id order by (cargo_bays-metal-fuel-elect-colon) desc");
						$quick_ship = dbr2();
						while($quick_ship && $bays_used < $colonist && $free_turns > $count_quick){
							$bays_used += $quick_ship[0];
							$count_quick++;
							$quick_ship = dbr2();
						}
						$ship_count = $count_quick;
						$colonist_cap = $bays_used;
						if($colonist > $colonist_cap){
							$colonist = $colonist_cap;
						}
					}
				}
				$turn += $ship_count;
				$c_c = $colonist * $GAME_VARS['cost_colonist'];
				if(!isset($sure)) { #ensure the user wants to carry out the autoshift.
					get_var($st[1615],'planet.php',$st[1618]."
					<br /><b>$ship_count</b> ".$cw['ship_s'].".
					<br /><b>$colonist</b> ".$st[1619]."
					<p />".$st[1620]."<b class='b1'>$planet[planet_name]</b>(#<b>$planet[location]</b>).
					<p />".sprintf($st[1621],$turn,$c_c),'sure','');
				} else { #update the game cos the user does want to do the autoshifting.
					dbn("update ${db_name}_planets set colon = colon + '$colonist' where planet_id = '$planet_id'");
					charge_turns($turn);
					take_cash($c_c);
					$output_str .= sprintf($st[1622],$colonists)."<p />$max_reached<p />";
				}
			}

		} else { #user is getting the materials from a system other than Sol. Thus different maths and stuff needs to be done as there is a finite number of materials, but no cash cost.

			db("select location,login_id,planet_name,$tech_mat,planet_id,alloc_fight,alloc_elect from ${db_name}_planets where planet_id = '$dest_system'");
			$from_sys=dbr(1);

			$turn = round(get_star_dist($user['location'],$from_sys['location'])/1.8 +1)*2; #work out turn cost
			#echo $turns_can_use = floor(($user['turns']- $turn) * 1.35);
			if($user['turns'] < $turn) { #ensure user has enough turns to get there
				$output_str .= sprintf($st[1623],$turn,$from_sys[planet_name],$from_sys[location]);
			} elseif(!isset($from_sys)){
				$output_str .= $st[1624]."<p />";
			} elseif($from_sys['login_id'] != $user['login_id']){
				$output_str .= $st[1625]."<p />";
			} elseif($type == 1 && ($from_sys['colon'] - ($from_sys['alloc_elect'] + $from_sys['alloc_fight'])) < 1){
				$output_str .= $st[1626]."<p />";
			} elseif($type > 1 && $from_sys[$tech_mat] <= 0){
				$output_str .= sprintf($st[1627],$text_mat);
			} else { #main autoshifting bit for taking materials from target planet
				if($type == 1){
					$available = $from_sys['colon'] - ($from_sys['alloc_elect'] + $from_sys['alloc_fight']);
				} else {
					$available = $from_sys[$tech_mat];
				}

				if($available >= $colonist_cap){ #there are more goods on target planet than there is cargo capacity.
					$col_to_take = $colonist_cap;
				} else { #got more goods capacity than goods on planet.
					$col_to_take = $available;
				}

				$max_reached = "";
				if($type == 1 && $col_to_take + $planet['colon'] > $planet['max_population']){ //max colonists (space on plnt)
					$col_to_take = $planet['max_population'] - $planet['colon'];
					$max_reached = $st[1617];
				}

				if(!isset($sure)) { #ensure the user wants to carry out the autoshift.
					get_var($st[1615],'planet.php',sprintf($st[1628],$col_to_take,$text_mat,$from_sys[planet_name],$from_sys[location],$planet[planet_name],$planet[location],$turn),'sure','');
				} else { #update the game as the user does want to do the autoshifting.
					dbn("update ${db_name}_planets set $tech_mat = $tech_mat + '$col_to_take' where planet_id = '$planet_id'");				#give goods to recieving planet
					dbn("update ${db_name}_planets set $tech_mat = $tech_mat - '$col_to_take' where planet_id = '$from_sys[planet_id]'");	#take goods from sending planet.
					charge_turns($turn);
					$output_str .= sprintf($st[1629],$col_to_take,$text_mat,$max_reached);
				}
			}
		}
	}

} elseif(isset($chosen_ship)){//user wants to load/unload selected ship
	if($chosen_ship != $user_ship['ship_id']){//no need to use the DB if playing with the user ship
		db("select * from ${db_name}_ships where location='$user[location]' && login_id = '$user[login_id]' && ship_id = '$chosen_ship'");
		$present_ship = dbr(1);
		if(isset($present_ship)){//ensure the ship exists
			empty_bays($present_ship);
		}
	} else {
		$present_ship = $user_ship;
	}
	if(!isset($type)){
		$type = 0;
	}
	discern_type($type);

	if($type == 0){
		$free_space = $cw['ship_fighters'].": <b>$present_ship[fighters]</b> / <b>$present_ship[max_fighters]</b>";
	} else {
		$free_space = $st[1630].": <br />".bay_storage($present_ship);
	}

	//loading or unloading a single ship
	if(!isset($present_ship)){
		$output_str .= $st[1631];
	} elseif($user['turns'] < 1){
		$pre_processed_txt .= $st[1632];
	} elseif($present_ship['cargo_bays'] < 1 && $type > 0){
		$pre_processed_txt .= $st[1633];
	} elseif($present_ship['max_fighters'] < 1 && $type == 1){
		$pre_processed_txt .= $st[1634];
	} elseif(!isset($single_ship_process) || $amount < 1){
		$out = sprintf($st[1635],$text_mat)." $text_mat: <b>$planet[$tech_mat]</b><p />".$free_space;
		$out .= "<form action=$_SERVER[PHP_SELF] name=single_ship_process method=POST><input type=hidden name=single_ship_deal value=1 /><input type=hidden name=type value='$type' /><input type=hidden name=chosen_ship value='$chosen_ship' /><input type=hidden name=planet_id value='$planet_id' /><p /><input type=text name=amount value=0 /><p /><input type='submit' name='single_ship_process' value='".$cw['load_ship']."' /> ------- <input type='submit' name='single_ship_process' value='".$cw['unload_ship']."' /><p /><a href='$_SERVER[PHP_SELF]?planet_id=$planet_id&type=$type&single_ship_deal=1'>".$cw['back']."</a>";
		print_page($cw['load/unload'],$out);
	} elseif($single_ship_process == $cw['load_ship']) {//loading the ship
		if(($type == 0 && ($present_ship['fighters'] + $amount) > $present_ship['max_fighters']) || ($type > 0 && ($present_ship['empty_bays'] - $amount) < 0)){
			$pre_processed_txt .= sprintf($st[1636],$present_ship[ship_name],$amount,$text_mat);
		} elseif($amount > $planet[$tech_mat]){//not that much on the planet
			$pre_processed_txt .= sprintf($st[1637],$text_mat);
		} elseif(($present_ship['cargo_bays'] < 1 || $present_ship['empty_bays'] < 1) && $type > 0){
			$output_str .= $st[1633];
		} elseif($present_ship['max_fighters'] < 1 && $type == 1){
			$output_str .= $st[1634];
		} else {//do the deed
			dbn("update ${db_name}_ships set $tech_mat = $tech_mat + '$amount' where ship_id = '$present_ship[ship_id]' && login_id = '$user[login_id]' && location = '$user[location]'");
			dbn("update ${db_name}_planets set $tech_mat = $tech_mat - '$amount' where planet_id = '$planet_id'");
			$planet[$tech_mat] -= $amount;
			if($chosen_ship == $user_ship['ship_id']){//user ship was the one we're changing
				$user_ship[$tech_mat] += $amount;
				empty_bays($user_ship);
			}
			$present_ship[$tech_mat] += $amount;
			$pre_processed_txt .= sprintf($st[1638],$present_ship[ship_name],$text_mat,$amount);
			charge_turns(1);
		}
	} else {//unloading the ship
		if($amount > $present_ship[$tech_mat]){//ensure there are that many on the ship.
			$pre_processed_txt .= sprintf($st[1639],$amount,$text_mat,$present_ship[ship_name]);
		} else {
			dbn("update ${db_name}_ships set $tech_mat = $tech_mat - '$amount' where ship_id = '$present_ship[ship_id]' && login_id = '$user[login_id]' && location = '$user[location]'");
			dbn("update ${db_name}_planets set $tech_mat = $tech_mat + '$amount' where planet_id = '$planet_id'");
			$planet[$tech_mat] += $amount;
			if($chosen_ship == $user_ship['ship_id']){//user ship was the one we're changing
				$user_ship[$tech_mat] -= $amount;
				empty_bays($user_ship);
			}
			$present_ship[$tech_mat] -= $amount;
			$pre_processed_txt = sprintf($st[1640],$present_ship[ship_name],$amount,$text_mat);
			charge_turns(1);
		}
	}

//going to load/unload selected ships
} elseif(isset($group_selected)){
	if(!isset($type)){
		$type = 0;
	}
	discern_type($type);
	if($user['turns'] < 1){
		$pre_processed_txt .= $st[1641];
	} elseif($type == 1 && $planet['colon'] >= $planet['max_population'] && $group_selected == $cw['unload_selected']){
		$pre_processed_txt .= $st[1642];
	} elseif(conditions($user,$planet)) { //check to see if been in game for long enough
		$pre_processed_txt .= sprintf($st[1643],$GAME_VARS[min_before_transfer]);
	} elseif(owned_planet($user,$planet)) {
		$pre_processed_txt .= $st[1888];
	} elseif(count($do_ship) < 1){
		$pre_processed_txt .= $st[1644];
	} elseif($group_selected == $cw['unload_selected']) { //leaving goods
//		var_dump(load_unload_planet(1,2,$do_ship));
//		exit();
		$pre_processed_txt .= load_unload_planet(1,2,$do_ship);
	} else { //taking goods
		$pre_processed_txt .= load_unload_planet(1,1,$do_ship);
	}

}

//take or leave a physical resource.
if(isset($single_ship_deal)){
	if(!isset($_REQUEST['type'])){
		$type = 0;
	} else {
		$type = (int)$_REQUEST['type'];
	}

	discern_type($type);
	$materials_array = array($cw['fighters'],$cw['colonists'],$cw['metals'],$cw['fuels'],$cw['electronics']);
	$materials_array[$type] = "<b class='b1'>".$materials_array[$type]."</b>";

	//select all ships in the system that are the users.
	$select_ships_sql = "select ship_name, class_name_abbr, fighters ,max_fighters, shields, max_shields, armour, max_armour, cargo_bays, metal, fuel, elect, colon, fleet_id, config, ship_id, location from ${db_name}_ships where login_id = '$user[login_id]' && location = '$user[location]' order by $tech_mat desc";

	$table_head_array = array($cw['ship_name'],$cw['ship_class'],$cw['fighters'],$cw['shields'],$cw['armour'],$cw['cargo_bays'],$st[1650],$cw['config'],$cw['select'],$st[1651]);
	$ship_listing = checkbox_ship_list($select_ships_sql, 2);

	if($ship_listing == -1){
		$output_str .= $st[1645];
	} elseif(conditions($user,$planet)) { //ensure user is allowed to play with this sort of stuff.
		$output_str .= sprinf($st[1646],$GAME_VARS[min_before_transfer]);
	} else {//list all the ships, and
		$out = $rs."<br />";
		if(!empty($pre_processed_txt)){
			$out .= $pre_processed_txt."<br />";
		} else {
			$out .= sprintf($st[1647],$text_mat,$text_mat);
		}
		$out .= "<p />".$st[1648].":<br />$materials_array[0]: <b>$planet[fighters]</b><br />$materials_array[1]: <b>$planet[colon]</b><br />$materials_array[2]: <b>$planet[metal]</b><br />$materials_array[3]: <b>$planet[fuel]</b><br />$materials_array[4]: <b>$planet[elect]</b><p />";

		//list the resources this page can trade
		$out .= sprintf($st[1649],$materials_array[$type]);
		foreach($materials_array as $key => $material){
			if($key != $type){
				$out .= "<a href='$_SERVER[PHP_SELF]?planet_id=$planet_id&type=$key&single_ship_deal=1'>$material</a> - ";
			} else {
				$out .= "$materials_array[$type] - ";
			}
		}
		$out = preg_replace("/ \- $/", "", $out);
		$out .= "<form name='take_or_leave_amount' method='post' action='$_SERVER[PHP_SELF]'><input type=hidden name=single_ship_deal value=1 /><input type=hidden name=type value=$type /><input type=hidden name=planet_id value='$planet_id' />";

		$out .= $ship_listing;
		$out .= "<p />".$st[1652]." <b class='b1'>$text_mat</b>.<p /><input type='submit' name='group_selected' value='".$cw['load_selected']."' /> ------- <input type='submit' name='group_selected' value='".$cw['unload_selected']."' /></form><p /><br />";

//		var_dump($out);
//		exit();
		print_page($st[1653],$out);
	}

//unload all cargo from all ships
} elseif(isset($dump_all)){
	if($user['turns'] < 100){
		$output_str .= $st[1654];
	} elseif($planet['colon'] >= $planet['max_population'] && $do_all == 2){
		$output_str .= $st[1655];
	} elseif(conditions($user,$planet)) { //check to see if been in game for long enough
		$output_str .= "$text_mat ".sprintf($st[1656],$GAME_VARS[min_before_transfer])."<p />";
	} elseif(owned_planet($user,$planet)) {
		$output_str .= $st[1888];
	} elseif(!isset($sure)) { //check to see if been in game for long enough
		get_var($cw['leave_all'],'planet.php',$st[1657],'sure','yes');
	} else {
		$type = 1;
		$output_str .= load_unload_planet(0,2)."<br />";
		$type = 2;
		$output_str .= load_unload_planet(0,2)."<br />";
		$type = 3;
		$output_str .= load_unload_planet(0,2)."<br />";
		$type = 4;
		$output_str .= load_unload_planet(0,2);
		charge_turns(100);
		$output_str .= "<p />".$st[1658];
	}


#Load/Unload the fleet
} elseif(isset($do_all) && isset($type)){
	if(!isset($type)){
		$type = 0;
	}
	discern_type($type);
	if($user['turns'] < 1){
		$output_str .= $st[1659];
	} elseif($type == 1 && $planet['colon'] >= $planet['max_population'] && $do_all == 2){
		$output_str .= $st[1611];
	} elseif(conditions($user,$planet)) { //check to see if been in game for long enough
		$output_str .= $text_mat.sprintf($st[1660],$GAME_VARS[min_before_transfer]) ."<p />";
	} elseif(owned_planet($user,$planet)) {
		$output_str .= $st[1888];
	} elseif($do_all == 1){ //Leaving Goods
		$output_str .= load_unload_planet(0,1);
	} elseif($do_all == 2){ //taking the goods
		$output_str .= load_unload_planet(0,2);
	}

} elseif(isset($all_shield)) { // Charge all shields on all ships in system.
	$taken = 0; //Shields taken from planet so far.
	$ship_counter = 0;
	if($sure != "yes") {
		get_var($st[1661],'planet.php',$st[1662],'sure','yes');
	} elseif(conditions($user,$planet)) {
		$output_str .=  sprintf($st[1663],$GAME_VARS[min_before_transfer])."<p />";
	} elseif($user['turns'] < 3) {
		print_page($cw['error'],$st[1664]);
	} elseif($planet['shield_charge'] < 1) {
		print_page($cw['error'],$st[1665]);
	} else {
		db2("select ship_id,shields,max_shields,ship_name from ${db_name}_ships where login_id = '$user[login_id]' && location = '$planet_loc' && max_shields > 0 && shields < max_shields");
		while($ships = dbr2()) {
			//planet can charge ship w/ spare shields maybe.
			$free = $ships['max_shields'] - $ships['shields'];
			if($free <= ($planet['shield_charge'] - $taken)) {
				$ship_counter++;
				dbn("update ${db_name}_ships set shields = max_shields where ship_id = '$ships[ship_id]'");
				$out .= "<br /><b class='b1'>$ships[ship_name]</b> ".sprintf($st[1666],$free);
				if($ships['ship_id'] == $user_ship['ship_id']){
					$user_ship['shields'] = $user_ship['max_shields'];
				}
				$taken += $free;
			//planet will run out of shields.
			} elseif($free >= ($planet['shield_charge'] - $taken)) {
				$ship_counter++;
				$t868 = $ships['shields'] + ($planet['shield_charge'] - $taken);
				dbn("update ${db_name}_ships set shields = '$t868' where ship_id = '$ships[ship_id]'");
				if($ships['ship_id'] == $user_ship['ship_id']){
					$user_ship['shields'] = $t868;
				}
				$taken += $t868 - $ships['shields'];
				$out .= "<br /><b class='b1'>$ships[ship_name]</b>s ".sprintf($st[1667],$t868);
				break;
			}
			if(($planet['shield_charge'] - $taken) < 1){
				break;
			}
		}
		dbn("update ${db_name}_planets set shield_charge = shield_charge - $taken where planet_id = '$user[on_planet]'");
		if($ship_counter > 0){
			charge_turns(3);
			print_page($st[1668],"<b>$ship_counter</b> ".$st[1669]." <b class='b1'>$planet[planet_name]</b>:<br />".$out);
		} else {
			print_page($cw['no_ships'],$st[1670]);
		}
	}

//assign colonists
} elseif(isset($assinging)) {
	#ensure all are rounded & valid
	$num_pop_set_1 = round($num_pop_set_1);
	settype($num_pop_set_1, "integer");

	$num_pop_set_2 = round($num_pop_set_2);
	settype($num_pop_set_2, "integer");

	settype($num_pop_set_3, "integer");
	$num_pop_set_3 = round($num_pop_set_3);

	if($num_pop_set_1 >= 0 && $num_pop_set_1 != $planet['alloc_fight']) { // Fighters
		if($num_pop_set_1 > idle_colonists() + $planet['alloc_fight']){ #ensure user doesn't go over the limit.
			$num_pop_set_1 = idle_colonists() + $planet['alloc_fight'];
		}
		$planet['alloc_fight'] = $num_pop_set_1;
		dbn("update ${db_name}_planets set alloc_fight = $num_pop_set_1 where planet_id = $user[on_planet]");
	}

	if($num_pop_set_2 >= 0 && $num_pop_set_2 != $planet['alloc_elect']) { // Electronics
		if($num_pop_set_2 > idle_colonists() + $planet['alloc_elect']){ #ensure user doesn't go over the limit.
			$num_pop_set_2 = idle_colonists() + $planet['alloc_elect'];
		}
		$planet['alloc_elect'] = $num_pop_set_2;
		dbn("update ${db_name}_planets set alloc_elect = $num_pop_set_2 where planet_id = $user[on_planet]");
	}

} elseif(isset($monetary)){
	#ensure all are rounded & valid
	$set_cash = round($set_cash);
	settype($set_cash, "integer");

	if($set_cash >= 0 && $set_cash != $planet['cash']){ #cash dispensary
		if($set_cash > $user['cash'] + $planet['cash']){ #ensure user doesn't go over the limit.
			$set_cash = $user['cash'] + $planet['cash'];
		}

		if($set_cash > $planet['cash']){ #user putting money onto planet.
			$take_from_user = $set_cash - $planet['cash'];
			take_cash($take_from_user);
			$planet['cash'] = $set_cash;
			dbn("update ${db_name}_planets set cash = $set_cash where planet_id = $user[on_planet]");
		} else { #taking money from planet.
			$give_to_user = $planet['cash'] - $set_cash;
			give_cash($give_to_user);
			$planet['cash'] = $set_cash;
			dbn("update ${db_name}_planets set cash = $set_cash where planet_id = $user[on_planet]");
		}
	}

	if(isset($set_tech) && $GAME_VARS['uv_num_bmrkt'] > 0){ #tech units
		$set_tech = round($set_tech);
		settype($set_tech, "integer");

		if($set_tech >= 0 && $set_tech != $planet['tech']){
			if($set_tech > $user['tech'] + $planet['tech']){ #ensure user doesn't go over the limit.
				$set_tech = $user['tech'] + $planet['tech'];
			}

			if($set_tech > $planet['tech']){ #user putting money onto planet.
				$take_from_user = $set_tech - $planet['tech'];
				take_tech($take_from_user);
				$planet['tech'] = $set_tech;
				dbn("update ${db_name}_planets set tech = $set_tech where planet_id = $user[on_planet]");
			} else { #taking money from planet.
				$give_to_user = $planet['tech'] - $set_tech;
				give_tech($give_to_user);
				$planet['tech'] = $set_tech;
				dbn("update ${db_name}_planets set tech = $set_tech where planet_id = $user[on_planet]");
			}
		}
	}
}

if(isset($rename)){
	if($user['login_id'] != $planet['login_id']){
		$text .= $st[1671];
	} elseif($name_to) {
		$name_to = correct_name($name_to);
		if(!$name_to || strlen($name_to) < 3) {
			$rs = "<p /><a href='javascript:history.back()'>".$cw['try-again']."</a>";
			print_page($cw['invalid_name'],$st[1672]);
		}
		#$stuff = addslashes($name_to);
		#echo eregi_replace("'","",$name_to);
		$text .= sprintf($st[1673],$planet[planet_name])." <b class='b1'>$name_to</b>.";
		dbn("update ${db_name}_planets set planet_name = '$name_to' where planet_id = '$planet[planet_id]'");
		post_news("<b class='b1'>$user[login_name]</b> ".sprintf($st[1674],$planet[planet_name])." <b class='b1'>$name_to</b>.", $cw['planet']);
	} else {
		$text .= $st[1675];
		$text .= "<FORM method=POST action=planet.php>";
		$text .= "<input type=text name=name_to size=30 value=\"$planet[planet_name]\" />";
		$text .= "<input type=hidden name=rename value=1 />";
		$text .= "<input type=hidden name=planet_id value=$planet[planet_id] />";
		$text .= "<p /><input type='submit' value='".$cw['rename']."' /></form><p />";
	}
	print_page($cw['rename_planet'],$text);
}


#put any messages into a "message" box.
$messages = $output_str;

#sets the largest span distance. Allows for quicker manipulation of the page.
$span_dist = 2;

$output_str = "<table width=90%>";
$r_name_txt = "";

#determine if user can re-name planet.
if($has_pass == 1) {
	$r_name_txt .= " - <a href='planet.php?planet_id=$planet_id&rename=1'>".$cw['rename']."</a>";
}

// begin printing of page
$output_str .= quick_row($cw['planet_name'],"<b class='b1'>$planet[planet_name]</b>$r_name_txt");


#print no name if the owner has none
if($planet['login_id'] == 0){
	$output_str.= quick_row($cw['owner_by'],$cw['noone']);
} else {
	$output_str .= quick_row($cw['owner_by'],$st[1676].": <b class='b1'>".print_name(array('login_id' =>$planet['login_id']))."</b>");
}


#show the planetary picture
if($user_options['show_pics']){
	$output_str .= "<tr><td colspan=$span_dist><center><img src=$directories[images]/planets/".$planet['planet_img'].".jpg border=0 alt='Image of the planet' /></center></td></tr>";
}

if(!empty($messages)){
	$output_str.= "<tr><td colspan=$span_dist><center>$messages</center></td></tr>";
}
$output_str.= "</table><p />";

#players may only view planetary data when they have the planet as their own.
if($has_pass == 1) {

	$output_str .= "<table>";

	if(empty($planet['pass'])) {
		$temp_str = "";
		if($user['login_id'] == $planet['login_id']){
			$temp_str .= "<a href='planet.php?change_pass=1&planet_id=$planet_id'>".$st[1677]."</a>";
		}
		$output_str .= quick_row($st[1678],$temp_str);
	} elseif($user['login_id'] == $planet['login_id']) { //only the owner may change the password
		$output_str .= quick_row($st[1679]." '$planet[pass]'","<a href='planet.php?change_pass=1&planet_id=$planet_id'>".$cw['change_it'].".</a>");
	} else {
		$output_str .= quick_row($st[1680]." $planet[pass]","");
	}

	#show the claim link for clan mates.
	if($planet['login_id'] != $user['login_id']){
		$output_str .= quick_row("<a href='planet.php?planet_id=$planet_id&claim=1'>".$cw['claim']." $planet[planet_name]</a>","");
	}

	$output_str .= "</table><p /><br />".$st[1681]."<table><form name=monetary_set_form method=post action=planet.php><input type=hidden name=planet_id value='$planet_id' /><input type=hidden name=monetary value='1' />";

	$output_str .= quick_row($st[1682],"<input type=text name=set_cash value=$planet[cash] size=8 />");

	if($GAME_VARS['uv_num_bmrkt'] > 0 && $planet['research_fac'] > 0){
			$output_str .= quick_row($st[1683],"<input type=text name=set_tech value=$planet[tech] size=8 />"); //.popup_help("help.php?topic=Blackmarkets&popup=1&sub_topic=Centres_de_recherches_et_Unités_de_Support", 500, 400)
	}
	$output_str .="</table><input type='submit' value='".$cw['change']."' /></form>";

	$output_str .= "<p /><br />".$st[1684];
	$output_str .= make_table(array("",$st[1685],$st[1686], $st[1687],$st[1688]));

	$output_str .= make_row(array($cw['fighters'],$planet['fighters'],"<a href='$_SERVER[PHP_SELF]?planet_id=$planet_id&single_ship_deal=1&type=0'>".$cw['load/unload']."</a>", "<a href='planet.php?planet_id=$planet_id&do_all=1&type=0'>".$st[1689]."</a> - <a href='planet.php?planet_id=$planet_id&do_all=2&type=0'>".$st[1690]."</a> "));

	$output_str .= make_row(array($cw['colonists'],'<b>'.(nombre($planet['colon']))."</b><br />(".$cw['max'].": <b>".nombre($planet[max_population])."</b>)","<a href='$_SERVER[PHP_SELF]?planet_id=$planet_id&single_ship_deal=1&type=1'>".$cw['load/unload']."</a>", "<a href='planet.php?planet_id=$planet_id&do_all=1&type=1'>".$st[1689]."</a> - <a href='planet.php?planet_id=$planet_id&do_all=2&type=1'>".$st[1690]."</a> ","<a href='planet.php?planet_id=$planet_id&autoshift=1&type=1'>".$st[1615]."</a>"));

	$output_str .= make_row(array($cw['metals'],$planet['metal'],"<a href='$_SERVER[PHP_SELF]?planet_id=$planet_id&single_ship_deal=1&type=2'>".$cw['load/unload']."</a>", "<a href='planet.php?planet_id=$planet_id&do_all=1&type=2'>".$st[1689]."</a> - <a href='planet.php?planet_id=$planet_id&do_all=2&type=2'>".$st[1690]."</a> ","<a href='planet.php?planet_id=$planet_id&autoshift=1&type=2'>".$st[1615]."</a>"));

	$output_str .= make_row(array($cw['fuels'],$planet['fuel'],"<a href='$_SERVER[PHP_SELF]?planet_id=$planet_id&single_ship_deal=1&type=3'>".$cw['load/unload']."</a>", "<a href='planet.php?planet_id=$planet_id&do_all=1&type=3'>".$st[1689]."</a> - <a href='planet.php?planet_id=$planet_id&do_all=2&type=3'>".$st[1690]."</a> ","<a href='planet.php?planet_id=$planet_id&autoshift=1&type=3'>".$st[1615]."</a>"));

	$output_str .= make_row(array($cw['electronics'],$planet['elect'],"<a href='$_SERVER[PHP_SELF]?planet_id=$planet_id&single_ship_deal=1&type=4'>".$cw['load/unload']."</a>", "<a href='planet.php?planet_id=$planet_id&do_all=1&type=4'>".$st[1689]."</a> - <a href='planet.php?planet_id=$planet_id&do_all=2&type=4'>".$st[1690]."</a> ","<a href='planet.php?planet_id=$planet_id&autoshift=1&type=4'>".$st[1615]."</a>"));
	$output_str .= "</td></tr></form></table>";

	$output_str .= "<a href='$_SERVER[PHP_SELF]?planet_id=$planet_id&dump_all=1'>".$st[1691]."<p /><br /><p />";


	$output_str .= $st[1692].make_table(array($st[1693],$st[1694],$st[1695],popup_help("help.php?topic=Combat&popup=1&sub_topic=Planet,_and_assisted_fleet_combat", 500, 400,$st[1696])));
	$fleet_def_figs = $planet['fighters'] - $defending_fighters;
	$output_str .= make_row(array($planet['fighters'],$defending_fighters, $fleet_def_figs,"<a href='planet.php?planet_id=$planet_id&allocate_figs=1'>".$st[1697]."</a>"));
	$output_str .= "</table>";


	if(avail_check(4002)){//ensure mining drones have been invented
		$output_str .= "<p /><br />".$cw['system_mining']."<br />";
		if($planet['mining_drones'] > 0){
			$unalloc = $planet['mining_drones'] - $planet['drones_alloc_metal'] - $planet['drones_alloc_fuel'];

			get_star(); //get the star's metal and fuel deposits
			$output_str.=  make_table(array($cw['resource'],$st[1698],$cw['drones_allocated']));
			$output_str.=  make_row(array($cw['metal'], $star['metal'], $planet['drones_alloc_metal']));
			$output_str.=  make_row(array($cw['fuel'], $star['fuel'], $planet['drones_alloc_fuel']));
			$output_str.=  make_row(array($cw['unallocated_drones'], $unalloc, "<a href='$_SERVER[PHP_SELF]?allocate_mining_drones=1&planet_id=$planet_id'>".$st[1699]."</a>"));
			$output_str.= "</table>";
			if($planet['mining_drones'] < $max_drones){
				$output_str.= "<a href='$_SERVER[PHP_SELF]?more_drones=1&planet_id=$planet_id'>".$st[1700]."</a>";
			}
		} else {
			$output_str.=  "<a href='$_SERVER[PHP_SELF]?more_drones=1&planet_id=$planet_id'>".$st[1701]."</a>";
		}
	}

	$output_str .= "<p /><br /><table><tr><td>".$st[1702]."</tr></td><form name=pop_set_form method=post action=planet.php><input type=hidden name=planet_id value='$planet_id' /><input type=hidden name=assinging value='1' />";
	$output_str .= quick_row($cw['tax_rate'], "6% (".$st[1703].")");
	$output_str .= quick_row($st[1704],idle_colonists());
	$output_str .= quick_row($st[1705],"<input type=text name=num_pop_set_1 value='$planet[alloc_fight]' size=6 />");
	$output_str .= quick_row($st[1706],"<input type=text name=num_pop_set_2 value='$planet[alloc_elect]' size=6 />");

	$output_str.= "<tr><td><input type='submit' value='".$cw['set']."' /></td><td><input type=reset value=Reset /></td></tr></form>";

	$output_str .= "</table><br />";


	if ($GAME_VARS['uv_num_bmrkt'] > 0 && $planet['research_fac'] == 0 && avail_check(4000)){
		$output_str .= "<p /><a href='add_planetary.php?planet_id=$planet_id&research_fac=1'>".$st[1707]."</a> - <b>$research_fac_cost</b> - ".popup_help("help.php?topic=Blackmarkets&sub_topic=Research_Facilities_and_Support_Units&popup=1", 500, 400);
	}

	if(!$planet['shield_gen'] && avail_check(3000)){
		$output_str .= "<p /><a href='add_planetary.php?planet_id=$planet_id&shield_gen=1'>".$st[1708]."</a> - <b>$shield_gen_cost</b> - ".popup_help("help.php?topic=Planets&popup=1&sub_topic=Shield_Generators", 400, 220);
	} elseif($planet['shield_gen'] && avail_check(3000)){
		$t545 = $planet['shield_gen'] * 1000;
		$output_str .= "<p />".$st[1709].": <b>$planet[shield_charge]</b> / <b>$t545</b> - <a href='planet.php?planet_id=$planet_id&all_shield=1'>".$st[1710]."</a>";
	}


	db("select * from ${db_name}_planets where planet_id = '$user[on_planet]'");
	$planet = dbr();

	if(($user['login_id'] == $planet['login_id'] || $user['clan_id'] == $planet['clan_id']) && ($GAME_VARS['uv_planets'] < 0 || $user['terra_imploder'] > 0)) {
		$output_str .= "<p /><a href='planet.php?planet_id=$planet_id&destroy=1'>".$cw['destroy']." $planet[planet_name]</a>";
	}


#only show the "claim" link to someone who doesn't own the planet.
} else {
	$output_str .= "<a href='planet.php?planet_id=$planet_id&claim=1'>".$cw['claim']." $planet[planet_name]</a>";
}
$rs = "<p /><a href='location.php'>".$cw['takeoff']."</a><br />";

print_page($cw['planet'],$output_str);

?>
