<?php
/********************************* *
* Fleet Attack Module.
* Version 1.2.9
* Finished: Never!!!
* Last Modified:
* Created by: Jonathan "Moriarty"
* (c) Copyright 2003 to Jonathan "Moriarty"
* This file most definitely covered by the GPL
**********************************/

/* table that shows possible attack scheme things.
	$error_str .= "<table border=1><tr><th>Attacker</th><th>Defender</th></tr>";
	$error_str .= "<tr><td>Send some ships to distract planet. Rest of fleet attacks enemy ships.</td><td>Random</td></tr>";
	$error_str .= "<tr><td>All attack Battleships</td><td>Random</td></tr>";
	$error_str .= "<tr><td>All attack PO ships</td><td>Random</td></tr>";
	$error_str .= "<tr><td>All attack freighters</td><td>Random</td></tr>";
	$error_str .= "<tr><td>Target planet. Remaining fleet occupies enemy fleet.</td><td>Defend selves, then defend planet.</td></tr>";
	$error_str .= "<tr><td>Distract warships, and hit freighters.</td><td>Random</td></tr>";
	$error_str .= "<tr><td>Distract ships, and hit planet.</td><td>Random</td></tr>";
	$error_str .= "<tr><td>Distract ships, and hit planet.</td><td>Random</td></tr>";
	$error_str .= "<tr><td>Destroy enemy</td><td></td></tr>";
	$error_str .= "<tr><td>Destroy as many ships as possible</td><td>Random</td></tr>";
	$error_str .= "<tr><td>Do most damage possible. (i.e. PO's take on planet (if is one). SO's take on ships).</td><td></td></tr>";
	$error_str .= "<tr><td></td><td></td></tr>";
	$error_str .= "<tr><td></td><td></td></tr>";
	$error_str .= "<tr><td></td><td></td></tr>";
	$error_str .= "<tr><td></td><td></td></tr>";
	$error_str .= "<tr><td></td><td></td></tr>";
	$error_str .= "<tr><td></td><td></td></tr>";
	$error_str .= "<tr><td></td><td></td></tr>";
*/

require_once("user.inc.php");

ship_status_checker();


//seed random number generator
mt_srand((double)microtime()*1000000);

if($user['ship_id'] == 1){
	print_page($st[200]);
}

//echo "<pre>";

#quark disrupter.
if(isset($quark)) {
	db("select * from ${db_name}_planets where planet_id = '$planet_num'");
	$planet_info = dbr();
	$sv_turns = 30;
	$status_bar_help = "?topic=Technical_Information";

	if($user['turns'] < $sv_turns) {
		print_page($cw['quark_displacer'],sprintf($st[201], $sv_turns, $user[turns]));
	} elseif($user['location'] != $planet_info['location']) {
		print_page($cw['quark_displacer'],sprintf($st[202], $planet_info[planet_name]));
	} elseif($user['turns_run'] < $GAME_VARS['turns_before_planet_attack'] && $user['login_id'] != 1) {
		print_page($cw['quark_displacer'],sprintf($st[203], $GAME_VARS[turns_before_planet_attack]));
	} elseif($GAME_VARS['attack_planet_flag'] == 0) {
		print_page($cw['quark_displacer'],$st[204]);
	} elseif($planet_info['login_id'] == 1) {
		print_page($cw['quark_displacer'],$st[205]);
	} elseif(!$planet_info['fighters']) {
		print_page($cw['quark_displacer'],$st[206]."<p /><a href='planet.php?planet_id=$planet_info[planet_id]'>".$cw['land']."</a>".$st[207]);
	} elseif(!config_check("sv", $user_ship)) {
		print_page($cw['quark_displacer'],$st[208]);
	} elseif(!isset($sure)) {
		get_var($cw['quark_displacer'],'attack.php',$st[209]." <b class='b1'>$planet_info[planet_name]</b>?",'sure','yes');
	}	else {
		charge_turns($sv_turns);

		$sv_damage = mt_rand(600,1400);

	/*
		if($sv_damage > $planet_info[fighters]) {
			$sv_damage = $planet_info[fighters];
			}*/

		$out_str = "";

		dbn("update ${db_name}_planets set fighters = fighters - '$sv_damage' where planet_id = $planet_num");
		$planet_info['fighters'] -= $sv_damage;
		post_news("<b class='b1'>$user[login_name]</b>".$st[210]."<b class='b1'>$planet_info[planet_name]</b>.", $st[211]);
		if($planet_info['fighters'] < 1) {
			send_message($planet_info['login_id'],sprintf($st[212], $user_ship[ship_name],$planet_info[planet_name],$sv_damage));
			send_templated_email($planet_info['login_id'], 'attack');
			dbn("update ${db_name}_planets set fighters = 0 where planet_id = $planet_num");
			$f_killed = $planet_info['fighters'];
			$planet_info['fighters'] = 0;
			$out_str .= sprintf($st[213], $sv_damage, $planet_info[planet_name], $sv_turns, $planet_info[planet_name])."<br /> - <a href='planet.php?planet_id=$planet_info[planet_id]'>".$cw['land']."</a><br />";
		} else {
			send_message($planet_info['login_id'],sprintf($st[214], $user_ship[ship_name], $planet_info[planet_name], $sv_damage));
			send_templated_email($planet_info['login_id'], 'attack');
			$f_killed = $sv_damage;
			$out_str .= sprintf($st[215], $sv_damage, $planet_info[planet_name], $sv_turns);
		}
		dbn("update ${db_name}_users set fighters_killed = fighters_killed + '$f_killed' where login_id = $user[login_id]");
		dbn("update ${db_name}_users set fighters_lost = fighters_lost + '$f_killed' where login_id = $planet_info[login_id]");
		print_page($cw['quark_disrupter'],$out_str);
	}
}


#terra maelstrom
if(isset($terra)) {

	db("select * from ${db_name}_planets where planet_id = '$planet_num'");
	$planet_info = dbr();
	$sw_turns = 50;
	$base_percent = 2;
	$status_bar_help = "?topic=Technical_Information";

	if($user['turns'] < $sw_turns) {
		print_page($cw['terra_maelstrom'],sprintf($st[216], $sw_turns, $user[turns]));
	} elseif($planet_info['planet_id'] == 1) {
		print_page($cw['terra_maelstrom'],$st[217]);
	} elseif($user['location'] != $planet_info['location']) {
		print_page($cw['terra_maelstrom'],sprintf($st[218], $planet_info[planet_name]));
	} elseif($user['turns_run'] < $GAME_VARS['turns_before_planet_attack'] && $user['login_id'] != 1) {
		print_page($cw['terra_maelstrom'],sprintf($st[219], $GAME_VARS[turns_before_planet_attack]));
	} elseif($GAME_VARS['attack_planet_flag'] == 0) {
		print_page($cw['quark_displacer'],$st[220]);
	} elseif($planet_info['login_id'] == 1) {
		print_page($cw['terra_maelstrom'],$st[221]);
	} elseif(!$planet_info['fighters']) {
		print_page($cw['terra_maelstrom'],$st[222]."<p /><a href='planet.php?planet_id=$planet_info[planet_id]'>".$cw['land']."</a>".$st[223]);
	} elseif($GAME_VARS['enable_superweapons'] == 0) {
		print_page($cw['terra_maelstrom'],$st[224]);
	} elseif(!config_check("sw", $user_ship)) {
		print_page($cw['terra_maelstrom'],$st[225]);
	}	else {

		#base amount of damage, done for the X turns.
		$sq_damage = mt_rand(4000,6000);

		#if planet has more than that many fighters, use an alternate system:
		if($planet_info['fighters'] > $sq_damage && $user['turns'] > $sw_turns){

			#work out how many fighters may be killed in one shot (between 65 and 75 percent) as a max.
			$max_fig_kills = round(($planet_info['fighters'] / 100) * mt_rand(65,75));

			#work out based on the max fighters that can be killed the num of fighters killed per turn.
			$killed_per_turn = round($max_fig_kills / $GAME_VARS['max_turns']);

			#damage done is based on num turns used times fighters killed per turn used. simple
			$damage_done = round($killed_per_turn * $user['turns']);

			#random factor. allows for an increased randomness in damage done.
			$damage_done += round(mt_rand(-$damage_done * .05,$damage_done * .05));

	#old method of doing damage with terra maelstrom
	#		$t_dam_done = round(($user[turns] - $sw_turns) / 10) + $base_percent;
	#		$damage_done = round($planet_info[fighters] /100 * ($base_percent + $t_dam_done));
	#		$damage_done += round(mt_rand(-$damage_done * .15,$damage_done * .15));
		}

		#damage done by alternate system isn't as much as using the sure fire method (fixed damage for fixed turns)
		if($sq_damage > $damage_done){
			$sw_damage = $sq_damage;
			$turn_cost = $sw_turns;
		} else { #damage done by alternate method is greater than normal damage done.
			$turn_cost = $user['turns'];
			$sw_damage = $damage_done;
		}

		if(!isset($sure)) {
			get_var($cw['terra_maelstrom'],'attack.php',sprintf($st[226], $planet_info[planet_name], $turn_cost, $damage_done),'sure', 'yes');
		}

		charge_turns($turn_cost);
		$out_str ="";

		dbn("update ${db_name}_planets set fighters = fighters - '$sw_damage' where planet_id = $planet_num");
		$planet_info['fighters'] -= $sw_damage;
		post_news("<b class='b1'>$user[login_name]</b>".$st[227]." <b class='b1'>$planet_info[planet_name]</b>.", $cw['planet'], $cw['attacking']);
		if($planet_info['fighters'] < 1) {
			send_message($planet_info['login_id'],sprintf($st[228], $user_ship[ship_name], $planet_info[planet_name], $sw_damage));
			send_templated_email($planet_info['login_id'], 'attack');
			dbn("update ${db_name}_planets set fighters = 0 where planet_id = $planet_num");
			$f_killed = $planet_info['fighters'];
			$planet_info['fighters'] = 0;
			$out_str .= sprintf($st[229], $sw_damage, $planet_info[planet_name], $turn_cost, $planet_info[planet_name])."<a href='planet.php?planet_id=$planet_info[planet_id]'>".$cw['land']."</a><br />";

		} else {
			send_message($planet_info['login_id'],sprintf($st[230], $user_ship[ship_name], $planet_info[planet_name], $sw_damage));
			send_templated_email($planet_info['login_id'], 'attack');
			$f_killed = $sw_damage;
			$planet_info['fighters'] -= $sw_damage;
			$out_str .= sprintf($st[231], $sw_damage, $planet_info[planet_name], $turn_cost);
		}
		dbn("update ${db_name}_users set fighters_killed = fighters_killed + '$f_killed' where login_id = $user[login_id]");
		dbn("update ${db_name}_users set fighters_lost = fighters_lost + '$f_killed' where login_id = $planet_info[login_id]");
		print_page($cw['terra_maelstrom'],$out_str);
	}
}



/**************************************************************************
                          !!!Fleet Attacking!!!
***************************************************************************/


//location of combat.
$combat_loc = $user['location'];

$status_bar_help = "?topic=Combat";

//establish if attacking a planet
if(isset($planet_attack) && $planet_attack == 1){
	$planet_attack = 1;
} else {
	$planet_attack = 0;
}

//establish if simulating attack
if(!isset($simulate_attack)){
	$simulate_attack = 0;
} else {
	$simulate_attack = 1;
}

//load the armour multiplier. save chance of forgetting about it. :)
$armour_multiplier = $GAME_VARS['armour_multiplier'];


/********
* Intial checks to see if user can attack
********/

settype($target, "integer");

if($planet_attack == 1){ //player attacking a planet - select planet details
	db("select u.turns_run as turns_run, p.login_id as login_id, u.clan_id as clan_id, u.login_name as login_name, planet_engaged from ${db_name}_users u, ${db_name}_planets p where p.planet_id = '$target' && u.login_id = p.login_id");
	$target_fleet_brief = dbr(1);
	$sql_to_find = "(config REGEXP 'bs' && config NOT REGEXP 'so') || config REGEXP 'oo' || config REGEXP 'po'"; //allow PO ships in on planet attacks.
} else { //player attacking a ship - select ship details
	db("select u.turns_run as turns_run, s.login_id as login_id, s.fleet_id as fleet_id, s.clan_fleet_id as clan_fleet_id, u.clan_id as clan_id, s.config as config from ${db_name}_users u, ${db_name}_ships s where s.ship_id = '$target' && u.login_id = s.login_id");
	$target_fleet_brief = dbr(1);
	$sql_to_find = "(config REGEXP 'bs' && config NOT REGEXP 'po') || config REGEXP 'oo' || config REGEXP 'so' ";
}

//count warships that user has.
db("select count(*) as warships from ${db_name}_ships where fleet_id = '$user_ship[fleet_id]' && login_id = '$user[login_id]' && location = '$combat_loc' && ship_engaged <= '".time()."' && ($sql_to_find)");
$warship_count = dbr(1);

unset($sql_to_find);


//determine if user has actually selected anything to attack
if(empty($target) || ($target < 2 && $planet_attack == 0)) {
	$tech_str = $st[232];

//admin not allowed to attack.
} elseif($user['login_id'] == 1) {
	$tech_str = $st[233];

//ensure target is attackable, and that it even exists properly!
} elseif(!isset($target_fleet_brief['login_id'])) {
	$tech_str = $st[280];

//determine if ship attacking is actually allowed
} elseif($GAME_VARS['attack_space_flag'] == 0 && $planet_attack == 0) {
	$tech_str = $st[234];

//determine if planet attacking is actually allowed
} elseif($GAME_VARS['attack_planet_flag'] == 0 && $planet_attack == 1) {
	$tech_str = $st[235];

//ensure the user is not trying to attack at sol.
} elseif($GAME_VARS['attack_sol_flag'] == 0 && $combat_loc == 1 && $user['login_id'] != 1){
	$tech_str = $st[236];

//ensure user has at least 1 warship in the fleet.
} elseif(empty($warship_count['warships']) || $warship_count['warships'] == 0){
	if($planet_attack == 1){
		$tech_str = $st[237];
	} else {
		$tech_str = $st[238];
	}

//target planet already in combat
} elseif($planet_attack == 1 && $target_fleet_brief['planet_engaged'] > time()) {
	$tech_str = $st[239];

//ensure user has run enough turns for S to S
} elseif($user['turns_run'] < $GAME_VARS['turns_before_space_attack'] && $planet_attack == 0) {
	$tech_str = sprintf($st[240], $GAME_VARS[turns_before_space_attack]);

//ensure user has run enough turns for S to P
} elseif($user['turns_run'] < $GAME_VARS['turns_before_planet_attack'] && $planet_attack == 1) {
	$tech_str = sprintf($st[241], $GAME_VARS[turns_before_planet_attack]);

//ensure user has enough turns to attack at least a single ship.
} elseif($user['turns'] < $GAME_VARS['attack_turn_cost_space'] && $planet_attack == 0) {
	$tech_str = sprintf($st[242], $GAME_VARS[attack_turn_cost_space]);

//ensure user has enough turns to attack a planet.
} elseif($user['turns'] < $GAME_VARS['attack_turn_cost_planet'] && $planet_attack == 1) {
	$tech_str = sprintf($st[243], $GAME_VARS[attack_turn_cost_planet]);

//ensure target is attackable, and that it even exists properly!
} elseif($target_fleet_brief['turns_run'] < $GAME_VARS['turns_safe']) {
	$tech_str = $st[244];

//ensure the attacking ship has a scanner on it.
} elseif($planet_attack == 0 && ((!config_check("sc",$user_ship) && (config_check("ls",$target_fleet_brief) || config_check("hs",$target_fleet_brief))) && $planet_attack == 0)) {
	$tech_str = $st[245];

//player trying to attack either themselves, or a clan-mate?!?!
} elseif($target_fleet_brief['login_id'] == $user['login_id'] || ($target_fleet_brief['clan_id'] == $user['clan_id'] && $user['clan_id'] > 0)) {
	$tech_str = $st[246];

//wouldn't want to attack the admin would we?
} elseif($target_fleet_brief['login_id'] == 1) {
	$tech_str = $st[247];

//check if attacked user is in holiday mode
} elseif ( checkHolidayMode( $target_fleet_brief['login_id'] ) ) {
	$tech_str = $st[1886];

//user passed initial tests.
} else {
	unset($warship_count);


/***********
* second level of checks. Ensure there is a fleet to attack and user may actually attack
***********/

	//select all enemy ships that belong to that user and are in that fleet, or that are clan-mates and are in the same fleet - must also be outside turns safe.
	/*$enemy_ship_counter_sql = " from ${db_name}_ships s, ${db_name}_users u, ${db_name}_planets p where
	(
	(((s.fleet_id = '$target_fleet_brief[fleet_id]' && s.login_id = '$target_fleet_brief[login_id]')
	|| (u.clan_id > 0 && s.clan_fleet_id = '$target_fleet_brief[clan_fleet_id]' && u.clan_id = '$target_fleet_brief[clan_id]' && s.login_id != '$target_fleet_brief[login_id]')) && s.location = '$combat_loc' && s.login_id = u.login_id)

	|| (((p.fleet_id = '$target_fleet_brief[fleet_id]' && p.login_id = '$target_fleet_brief[login_id]')
	|| (u.clan_id > 0 && p.clan_fleet_id = '$target_fleet_brief[clan_fleet_id]' && u.clan_id = '$target_fleet_brief[clan_id]' && p.login_id != '$target_fleet_brief[login_id]')) && p.location = '$combat_loc' && p.login_id = u.login_id ))

	&& u.login_id != 1 && u.turns_run > '$GAME_VARS[turns_safe]'";*/



	$players_array = array();
	$time_to_engage = time() + 60;
	$total_planet_dam_taken = 0;
	$ships_in_system = 0;
/* Enemy user details */

	$foes_name_str = "";

	//target is a fleet, not a planet
	if($planet_attack == 0){
		$enemy_ship_counter_sql = " from ${db_name}_ships s, ${db_name}_users u where ((s.fleet_id = '$target_fleet_brief[fleet_id]' && s.login_id = '$target_fleet_brief[login_id]') || ('$GAME_VARS[clan_fleet_attacking]' = 1 && u.clan_id > 0 && s.clan_fleet_id = '$target_fleet_brief[clan_fleet_id]' && u.clan_id = '$target_fleet_brief[clan_id]' && u.login_id != '$target_fleet_brief[login_id]')) && s.location = '$combat_loc' && u.login_id != 1 && s.login_id = u.login_id && u.turns_run >= '$GAME_VARS[turns_safe]' && s.ship_engaged <= '".time()."'";

		//count ships and users in enemy fleets.
		db("select count(distinct s.ship_id) as ship_count, count(distinct s.login_id) as user_count, sum(s.fighters) as fighter_count, sum(num_dt + num_ot + num_pc + num_ew) as upgrade_count".$enemy_ship_counter_sql);
		$target_details = dbr(1);
		$target_details['fighters_lost'] = 0;

		//dump all the enemies names into a string.
		db("select u.login_id as login_id, u.clan_id as clan_id, u.ship_id as ship_id, u.login_name as login_name, u.location as location ".$enemy_ship_counter_sql." group by u.login_id");
		while($foes_logins = dbr(1)) {
			add_user_to_users_array($players_array, $ships_involved_str, $foes_name_str, $foes_logins, 2);
		}
		unset($foes_logins);


/**************** Load Assisting Planets ****************/
		$target_planets = array();
		$total_planet_figs = 0;

		//load all planets that can assist. will only load clan planets if player in a clan, and clan fleet attacking is enabled.
		db("select p.*, p.allocated_to_fleet as fighters, p.fighters as possible_fighters, u.location as location from ${db_name}_planets p, ${db_name}_users u where (p.login_id = '$target_fleet_brief[login_id]' || (p.clan_id = '$target_fleet_brief[clan_id]' && '$GAME_VARS[clan_fleet_attacking]' = 1 && '$target_fleet_brief[clan_id]' > 0 && p.login_id != '$target_fleet_brief[login_id]')) && p.location = '$combat_loc' && p.allocated_to_fleet > 0 && u.login_id = p.login_id && u.turns_run > '$GAME_VARS[turns_safe]' && p.planet_engaged <= '".time()."'");

		while($assisting_planets_db = dbr(1)) {

			//simulating an attack will result in planets fig count being unknown (randomised).
			if($simulate_attack == 1 || !isset($sure)){
				$assisting_planets_db['fighters'] += mt_rand(round(-$assisting_planets_db['fighters'] * 0.2),round($assisting_planets_db['fighters'] * 0.2));
				if($assisting_planets_db['fighters'] > $assisting_planets_db['possible_fighters']){
					$assisting_planets_db['fighters'] = $assisting_planets_db['possible_fighters'];
				}
			}

			//add planet details to assisting planet details array.
			$target_planets[$assisting_planets_db['planet_id']] = $assisting_planets_db;
			$total_planet_figs += $assisting_planets_db['fighters'];

			//dump player details into player array.
			$assisting_planets_db['ship_id'] = 0;
			add_user_to_users_array($players_array, $ships_involved_str, $foes_name_str, $assisting_planets_db, 2);
			$players_array[$assisting_planets_db['login_id']]['total_fighters'] += $assisting_planets_db['fighters'];
		}
		unset($assisting_planets_db);

		if(count($target_planets) > 0){//assisting planets. So include PO
			$friendly_sql_ships = "config REGEXP 'bs' || config REGEXP 'oo'";
		} else {//no planets. So no po's involved.
			$friendly_sql_ships = "(config REGEXP 'bs' || config REGEXP 'oo') && config NOT REGEXP 'po'";
		}

	//user attacking a planet. We can use the collection of info we garnered at the initial stage to initialise this entry.
	//We also need to create some 'target details'.
	} else {

		//only select non SO ships.
		$friendly_sql_ships = "(config REGEXP 'bs' || config REGEXP 'oo') && config NOT REGEXP 'so'";

		$enemy_ship_counter_sql = " from ${db_name}_planets p, ${db_name}_users u where p.planet_id = '$target' && p.location = '$combat_loc' && p.login_id != 1 && u.login_id = p.login_id && u.turns_run >= '$GAME_VARS[turns_before_planet_attack]'";

		db("select p.fighters - p.allocated_to_fleet as fighter_count, p.fighters as possible_fighters".$enemy_ship_counter_sql);
		$target_details = dbr(1);

		//planet doesn't exist for some reason
		if(!is_array($target_details)){
			print_page($st[248]);

		//the planet has no fighters that can defend it.
		} elseif(empty($target_details['fighter_count']) || $target_details['fighter_count'] < 1){
			print_page($st[249]."<a href='planet.php?planet_id=$target'>".$cw['land']."</a>".$st[250]);
		}

		$target_details['ship_count'] = 0;
		$target_details['user_count'] = 1;
		$target_details['upgrade_count'] = 0;
		$target_details['fighters_lost'] = 0;

		$total_planet_figs = $target_details['fighter_count'];

		//select the planet owners command ship, so as the owner doesn't end up in some obscure place at the end of combat.
		db("select ship_id, location from ${db_name}_users where login_id = '$target_fleet_brief[login_id]'");
		$temp_ship_id = dbr(1);
		$target_fleet_brief['ship_id'] = $temp_ship_id['ship_id'];
		$target_fleet_brief['location'] = $temp_ship_id['location'];
		unset($temp_ship_id);

		add_user_to_users_array($players_array, $ships_involved_str, $foes_name_str, $target_fleet_brief, 2);

		/*******************************************************/
		// Select Target planet Details!
		/*******************************************************/

		//check to see how many 'defensive ships' are in the system
		//not all fighters are allocated to planet defence, some are set to assist fleets.
		if($target_details['fighter_count'] != $target_details['possible_fighters']){

			//Check to see if there are any ships in the system to 'defend'.
			//If there are such ships, then the fighters allocated to them won't be available for planet defence.
			db("select count(ship_id) as num_ships from ${db_name}_ships where location = '$combat_loc' && (login_id = '$target_fleet_brief[login_id]' || (login_id != '$target_fleet_brief[login_id]' && clan_id > 0 && '$GAME_VARS[clan_fleet_attacking] = 1' && clan_id = '$target_fleet_brief[clan_id]'))");
			$ships_count_temp = dbr(1);

			$ships_count_temp['num_ships'];

			//there are some ships in the system, so only non-allocated fighters go to planet defence.
			if($ships_count_temp['num_ships'] > 0){
				$temp_var['fighters'] = $target_details['fighter_count'];
				$ships_in_system = 1;

				if($simulate_attack == 1){//the user won't know the exact number of figs
					$temp_var['fighters'] += mt_rand(round(-$temp_var['fighters'] * 0.2),round($temp_var['fighters'] * 0.2));
					if($temp_var['fighters'] > $target_details['possible_fighters']){
						$temp_var['fighters'] = $target_details['possible_fighters'];
					}
				}

			//no ships in system. All the planets fighters go into defending itself.
			} else {
				$temp_var['fighters'] = $target_details['possible_fighters'];
			}
			unset($ships_count_temp);

		} else { //all fighters are allocated to planet defence.
			$temp_var['fighters'] = $target_details['possible_fighters'];
		}

		db2("select p.* ".$enemy_ship_counter_sql);
		$temp_planet = dbr2(1);

		//set planet to 'combat engaged' for next 60 secs
		if(isset($sure) && $simulate_attack == 0) {
			dbn("update ${db_name}_planets set planet_engaged = '$time_to_engage' where planet_id = '$target'");
		}

		$target_planets[$target] = $temp_planet;
		$target_planets[$target]['ship_destroyed'] = 0;
		$total_planet_figs = $target_planets[$target]['fighters'];
		$players_array[$target_fleet_brief['login_id']]['total_fighters'] += $total_planet_figs;

		unset($temp_planet);
		$target_details['fighter_count'] = 0; //this var contains ship fig count only
	}

	if(($simulate_attack == 1 || !isset($sure)) && $total_planet_figs > 0 && $ships_in_system == 1){
		$report_figs = $total_planet_figs." (Guess)";
	} else {
		$report_figs = $total_planet_figs;
	}

/* Friendly user details */

	//select all own and clan ships (when clans are permitted to join combat) that are capable of attacking (not po or na), and whose users are allowed to attack.
	$friendly_ship_counter_sql = " from ${db_name}_ships s, ${db_name}_users u where ((s.fleet_id = '$user_ship[fleet_id]' && s.login_id = '$user_ship[login_id]') || ('$GAME_VARS[clan_fleet_attacking]' = 1 && $user[clan_id] > 0 && s.clan_fleet_id = '$user_ship[clan_fleet_id]' && u.clan_id = '$user[clan_id]' && u.login_id != '$user[login_id]')) && s.location = '$combat_loc' && u.login_id != 1 && s.login_id = u.login_id && u.turns_run >= '$GAME_VARS[turns_before_space_attack]' && ($friendly_sql_ships) && s.config NOT REGEXP 'na'";

	//count ships and users in own fleets.
	db("select count(s.ship_id) as ship_count, count(distinct s.login_id) as user_count, sum(s.fighters) as fighter_count, sum(num_dt + num_ot + num_pc + num_ew) as upgrade_count".$friendly_ship_counter_sql);
	$friendly_details = dbr(1);
	$friendly_details['fighters_lost'] = 0;

	//create a string with the names of the friend fleet owners on.
	$friends_name_str = "";
	db("select u.login_id as login_id, u.clan_id as clan_id, u.ship_id as ship_id, u.login_name as login_name, u.location as location".$friendly_ship_counter_sql." group by u.login_id");
	while($friends_logins = dbr(1)) {
		add_user_to_users_array($players_array, $ships_involved_str, $friends_name_str, $friends_logins, 1);
	}
	unset($friends_logins);


/*Generic Details and more checks*/

	//select most experienced ship in the users fleet. Must be BS or OO.
	db("select ship_name, class_name, points_killed from ${db_name}_ships where login_id = '$user[login_id]' && location = '$combat_loc' && fleet_id = '$user_ship[fleet_id]' && (config REGEXP 'bs' || config REGEXP 'oo') order by points_killed desc LIMIT 1");
	$experienced_ship = dbr(1);

	//work out level of the ship.
	settype($exp, "integer");
	$exp = resolve_level($experienced_ship['points_killed']);


	//Work out turn cost to attack
	if($planet_attack == 1){
		$total_attack_turn_cost = $GAME_VARS['attack_turn_cost_planet'];
	} else {
		$total_attack_turn_cost = $GAME_VARS['attack_turn_cost_space'] * $target_details['ship_count'];
	}

	//work out max turns that can be used to attack (is max turn storage minus 1 hrs worth of turns).
	$highest_turns = $GAME_VARS['max_turns'] - $GAME_VARS['hourly_turns'];

	//if the total cost is going to be more than the user can actually store, make the number equal the max_turns minus one maints worth (so the player can get in and get out again).
	if($total_attack_turn_cost > $highest_turns){
		$total_attack_turn_cost = $highest_turns;
	}

	//check to see if the user has enough turns to progress, less than 100% turn capacity required.
	if($user['turns'] < $total_attack_turn_cost && $total_attack_turn_cost < $highest_turns) {
		$tech_str = sprintf($st[251], $target_details[ship_count], $GAME_VARS[attack_turn_cost_space], $total_attack_turn_cost);

	//user doesn't have enough turns, and user requires high turn capacity.
	} elseif($user['turns'] < $total_attack_turn_cost && $total_attack_turn_cost >= $highest_turns) {
		$tech_str = sprintf($st[252], $target_details[ship_count], $highest_turns);

	//ensure there are enemy ships that can be attacked.
	} elseif((empty($target_details['ship_count']) || $target_details['ship_count'] == 0) && $planet_attack == 0){
		$tech_str = $st[253];

	//ensure user actually has some ships that can attack
	} elseif(empty($friendly_details['ship_count']) || $friendly_details['ship_count'] == 0){
		$tech_str = $st[254];

	//ensure user actually has some damage doing ability!
	} elseif($friendly_details['fighter_count'] < 1 && $friendly_details['upgrade_count'] < 1){
		$tech_str = $st[255];

	//list the tactics available to the user. And present absolute confirmation.
	} elseif(!isset($sure)) {


		//can't be telling the user the name of the target now can we? :) Not if they have high stealth at least.
		if($planet_attack == 0 && config_check("hs",$target_fleet_brief)){
			$temp_foes_name_str = " <b>".$cw['unknown_target']."</b>";
		} else {
			$temp_foes_name_str = $foes_name_str;
		}

/******** Simulation Text *********/
		$sim_hid_vars = "";
//		if($exp == 0 || $planet_attack == 1){
			$sim_hid_vars = "<input type=hidden name='gen_tactic_to_use' value=1 />";
//		}
		//$sim_text = "\n\n<br /><br /><br /><b>".$cw['simulation']."</b> - ".popup_help("help.php?topic=Combat&popup=1&sub_topic=Simulating_Combat", 300, 430, $st[1696])."<br />".sprintf($st[270], $simulate_attack_turn_cost)."<FORM action=attack.php method=post name=simulate_attack_form>\n<input type=hidden name='target' value='$target' /><input type=hidden name='planet_attack' value='$planet_attack' />\n<input type=hidden name='sure' value='yes' />\n <input type=hidden name='simulate_attack' value=1 />\n $sim_hid_vars \n<input type='Submit' value='".$cw['simulate']."' /></FORM>";
		$attack_help_str = "<p /><a target='_blank' href='help.php?topic=Combat'>".$cw['attacking_help']."</a>";

/******** Single ship combat, Or ship against planet combat. *********/
		//no need for 'tactics' if just going 1 on 1.
		if($target_details['ship_count'] == 1 && $friendly_details['ship_count'] == 1 && $planet_attack == 0 && count($target_planets) < 1){
			$tech_str = sprintf($st[256], $temp_foes_name_str, $total_attack_turn_cost, $attack_help_str);
			$tech_str .= "\n<FORM action=attack.php method=post name=confirmation_request>\n";
			$tech_str .= "\n<input type=hidden name=gen_tactic_to_use value=1 />\n<input type=hidden name='target' value='$target' />\n<input type=hidden name='sure' value='yes' />\n<input type=\"Button\" value='".$cw['flee']."' onclick=\"javascript: history.back()\" /> - <input type='Submit' value='".$cw['attack']."!!!!!' /></FORM>";
			print_page($cw['ship_attack_confirmation'],$tech_str);
		} elseif($planet_attack == 1){
			$tech_str = $st[257]." <b class='b1'>{$target_planets[$target]['planet_name']}".sprintf($st[258], $total_attack_turn_cost)." $attack_help_str";
			$tech_str .= "<p />".make_table(array("",$st[271], $cw['ships'], $cw['ship_fighters'], $cw['planet_fighters']),"WIDTH=50%");
			$tech_str .= make_row(array($cw['friendly_fleet'], $friendly_details['user_count'].$friends_name_str, $friendly_details['ship_count'], $friendly_details['fighter_count']));
			$tech_str .= make_row(array($cw['enemy_forces'],$target_details['user_count'].$temp_foes_name_str, "-", "-", "Up to ".$target_details['possible_fighters']));
			$tech_str .= "</table>".$sim_text;
			$tech_str .= "\n<FORM action=attack.php method=post name=confirmation_request>\n";
			$tech_str .= "\n<input type=hidden name=gen_tactic_to_use value=1 />\n<input type=hidden name='target' value='$target' />\n<input type=hidden name='planet_attack' value='1' />\n<input type=hidden name='sure' value='yes' />\n<input type=\"Button\" value='".$cw['flee']."' onclick=\"javascript: history.back()\" /> - <input type='Submit' value=".$cw['attack']."!!!!! /></FORM>";
			print_page($cw['planet_attack_confirmation'],$tech_str);
		}


		//start of the tactical control page.
		$tech_str = $st[259].$attack_help_str;


/******** General Overview *********/
		//give general stats as to what the two sides have.
		$tech_str .= "\n<br /><br /><br /><b>".$cw['general_overview']."</b>";
		$tech_str .= $st[260];
		$tech_str .= "<p />".make_table(array("",$st[271], $cw['ships'], $cw['ship_fighters'], $cw['planets'], $cw['planet_fighters']),"WIDTH=50%");

		$tech_str .= make_row(array($cw['friendly_fleet'],$friendly_details['user_count'].$friends_name_str, $friendly_details['ship_count'], $friendly_details['fighter_count']));
		$tech_str .= make_row(array($cw['enemy_forces'],$target_details['user_count'].$temp_foes_name_str, $target_details['ship_count'], $target_details['fighter_count'], count($target_planets), $report_figs));

		$tech_str .= sprintf($st[261], $total_attack_turn_cost);
		$tech_str .= $sim_text;

/******** Listing Of Tactics *********/

		$tech_str .= $st[262];
		//print about experience level of the ship.
		if($exp > 0){
			$tech_str .= sprintf($st[263], $experienced_ship[ship_name], $experienced_ship[class_name], $exp);
		} else {
			$tech_str .= $st[264];
		}


		//quick var to make sure can't run any tactics other than the 1 that's created! :)
		$no_others = 1;

		$tech_str .= "\n<br /><br /><br /><b>".$cw['confirmation']."</b>";

		$form_str = "<FORM action=attack.php method=post name=tactic_choice>\n";

		//only tactic available to the user is simple all-out attack.
		//if($exp == 0 || $planet_attack == 1){
			$confirm_str = $st[265];
			$hidden_vars = "<input type=hidden name=gen_tactic_to_use value=1 />";

		/*} else {
			$confirm_str = "\n<p />Are you <b class='b1'>Certain</b> that you want to commence a fleet attack using the tactics selected above?";
			$hidden_vars = "<input type='Reset' value='Reset' /> - ";
			$tech_str .= "<input type=reset value='Reset Tactics' />";
		}*/

		//output final form.
		$tech_str .= $confirm_str."\n";
		$tech_str .= $form_str.$hidden_vars."\n<p /><input type=hidden name='target' value='$target' />\n<input type=hidden name='sure' value='yes' />\n<input type=\"Button\" value='".$cw['flee']."' onclick=\"javascript: history.back()\" /> - <input type='Submit' value='".$cw['attack']."!!!!!' />";


		$tech_str .= "</FORM><p />".$st[266];




		print_page($cw['attack_confirmation'],$tech_str);

/***********
* Third level checks. Ensure user isn't trying to use tactics that are not available to them.
***********/

	//ensure user is actually allowed to use this tactic.
	} elseif($gen_tactic_to_use - 1 > $exp && $planet_attack == 0) {
		$tech_str = $st[267];


	//other error checking would go here if tactics were complete.



/***********
* Processing of attack begins.
***********/
	} else {

		//set time limit to 60 seconds. This script could take a while to process.
		set_time_limit(60);

		$friends_killed = 0;
		$targets_killed = 0;

		//these two allow for easy change of the direction that ships are ordered (when loaded).
		//This in turn has a dramatic effect on which ships get damaged first in combat (fighter heavy ships (generally warships) or fighter light ships (generally freighters)).
		$dir_foe_ships = "desc";
		$dir_friend_ships = "desc";

/**************
* Friendly ships into array
**************/

		//select all friendly ships and dump them into an array.
		db2("select s.*".$friendly_ship_counter_sql." order by fighters $dir_friend_ships");

		$update_ships_sql_friends = "";

		//loop through friendlies and put them all into a array
		while($loop_friends = dbr2(1)) {
			$loop_friends['approx_damage'] = $loop_friends['shields'] + $loop_friends['fighters'] + ($loop_friends['armour'] * $armour_multiplier);
			$loop_friends['ship_allocated'] = 0;
			$loop_friends['friend_foe'] = 1;
			if($loop_friends['approx_damage'] < 1){//ship has no damage absorb cap. So kill it outright.
				$loop_friends['ship_destroyed'] = 1;
			} else {
				$loop_friends['ship_destroyed'] = 0;
			}
			$friendly_ships[$loop_friends['ship_id']] = $loop_friends;
			$update_ships_sql_friends .= "ship_id = '$loop_friends[ship_id]' || ";
			$players_array[$loop_friends['login_id']]['total_fighters'] += $loop_friends['fighters'];
		}

		if($simulate_attack == 0){
			//replace only the last '|| ' from the string so it can be used as a viable sql string.
			if(!empty($update_ships_sql_friends)){
				$update_ships_sql_friends = preg_replace("/\|\| $/", "", $update_ships_sql_friends);

				//Make sure all ships are given a time-stamp so as they can't be used during attacking.
				dbn("update ${db_name}_ships set ship_engaged = '$time_to_engage' where ".$update_ships_sql_friends);
				$updated_friends_1 = mysql_affected_rows();
			}

			//something went wrong and there were no ships to update?!?!?!
			if(empty($update_ships_sql_friends) || (isset($updated_friends_1) && $updated_friends_1 < 1)){
				print_page($st[268]);
			}
		}

/**************
* Enemy ships into array
**************/

		//no need to collect foes ships if attacking a planet.
		if($planet_attack == 0){
			//select all Enemy ships and dump them into an array.
			db2("select s.*".$enemy_ship_counter_sql." group by s.ship_id order by fighters $dir_foe_ships");

			$update_ships_sql_enemies = "";

			//loop through friendlies and put them all into a array
			while($loop_enemies = dbr2(1)) {
				$loop_enemies['approx_damage'] = $loop_enemies['shields'] + $loop_enemies['fighters'] + ($loop_enemies['armour'] * $armour_multiplier);
				$loop_enemies['ship_allocated'] = 0;
				$loop_enemies['friend_foe'] = 2;
				if($loop_enemies['approx_damage'] < 1){//ship has no damage absorb cap. So kill it outright.
					$loop_enemies['ship_destroyed'] = 1;
				} else {
					$loop_enemies['ship_destroyed'] = 0;
				}
				$target_ships[$loop_enemies['ship_id']] = $loop_enemies;
				$update_ships_sql_enemies .= "ship_id = '$loop_enemies[ship_id]' || ";
				$players_array[$loop_enemies['login_id']]['total_fighters'] += $loop_enemies['fighters'];
			}

			if($simulate_attack == 0){
				//replace only the last '|| ' from the string so it can be used as a viable sql string.
				if(!empty($update_ships_sql_enemies)){
					$update_ships_sql_enemies = preg_replace("/\|\| $/", "", $update_ships_sql_enemies);

					//Make sure all ships are given a time-stamp so as they can't be used during attacking.
					dbn("update ${db_name}_ships set ship_engaged = '$time_to_engage' where ".$update_ships_sql_enemies);
					$updated_enemies_1 = mysql_affected_rows();
				}

				//something went wrong and there were no ships to update?!?!?!
				if(empty($update_ships_sql_enemies) || (isset($updated_enemies_1) && $updated_enemies_1 < 1)){
					print_page($st[269]);
				}
			}

			//let's make a replica of the starting ships, for posterities sake (and report writing necessity).
			$replica_of_ships = $friendly_ships + $target_ships;


			//timestamp planets so they can't be used during the combat.
			if(count($target_planets) > 0 && $simulate_attack == 0){
				foreach($target_planets as $planet_id){
					dbn("update ${db_name}_planets set planet_engaged = '$time_to_engage' where planet_id = '$planet_id'");
				}
			}


		//planet attacking, so let's do some stuff to the planet.
		} else {
			//replica of ships (only friendlies, as target doesn't have ships).
			$target_ships = array();
			$replica_of_ships = $friendly_ships;
		}

		//a nice little tidy up.
		unset($enemy_ship_counter_sql, $friendly_ship_counter_sql, $update_ships_sql_enemies, $time_to_engage, $loop_enemies, $loop_friends, $updated_enemies_1, $updated_friends_1);

		//make a replica of planets (for some end-of-script processing).
		$replica_of_planets = $target_planets;

/************************************************************************************
*							Tactical processing begins
************************************************************************************/

		//ensure don't play with a tactic that won't work for planet attacking.
		if($planet_attack == 1){
			$gen_tactic_to_use = 1;
		}

		//an array that stores the number of planetary fighers alloted to this battlegroup (key the same as the battlegroup it's linked with).
		$battle_group_planets_array = array();

		//the array that stores all the groups for combat.
		$combat_array = array();


		if(!isset($gen_tactic_to_use) || $gen_tactic_to_use < 1){
			$gen_tactic_to_use = 1;
		}


		$gen_tactic_to_use = 1;


		//A switch which contains all the different tactics.
		switch ($gen_tactic_to_use){

		//just using random attack, as there is no experience within the fleet.
		case 1;
			//fills the an array with a list of friendly ship ids, and set's their values to 1
			$temp_storage_array_friend = array_keys($friendly_ships);
			$temp_storage_array_friend = array_flip($temp_storage_array_friend);
			$temp_storage_array_friend = array_map("set_friend",$temp_storage_array_friend);

			if($planet_attack == 0){
				//Same as above, but for enemy ships and sets their values to 2.
				$temp_storage_array_foe = array_keys($target_ships);
				if(count($target_planets) > 0){
					array_unshift($temp_storage_array_foe, -1);
					$battle_group_planets_array[] = array('fighters' => $total_planet_figs, 'planet_destroyed' => 0, 'approx_damage' => $total_planet_figs); //note: approx_damage is same as 'fighters', to simplify damage distribution.
				}
				$temp_storage_array_foe = array_flip($temp_storage_array_foe);
				$temp_storage_array_foe = array_map("set_foe",$temp_storage_array_foe);

			//add the planet(ary group).
			} else {
				$temp_storage_array_foe = array('-1' => 2);
				$battle_group_planets_array[] = array('fighters' => $total_planet_figs, 'planet_destroyed' => 0, 'approx_damage' => $total_planet_figs);
			}
			$combat_array[] = $temp_storage_array_friend + $temp_storage_array_foe;
			break;





		//This tactic allows PO ships to take out the planets assisting fighters, whilst the rest of the players fleet distracts the enemy fleet.
		//it can also be used in the other way - distract a planets fighters with a PO ship, whilst the players fleet decimates the enemy fleet.
		case 10;

			//only way can have PO ships at this point is if there's an assisting enemy planet.
			$po_ship_power = 0;
			$po_combat_array = array();

			foreach($friendly_ships as $ship_id => $ship){
				//begin setting any PO ships into a anti-planet group
				if(config_check("po",$ship)){
					$po_combat_array[$ship_id] = 1;
					$po_ship_power ++;
				} else {
					$temp_storage_array_friend[$ship_id] = 1;
				}
			}

			//if po ships and assisting planets, set all po figs against the planets.
			if($po_ship_power > 0){
				$po_combat_array[-1] = 2;
				$combat_array[] = $po_combat_array;
			}

			//the assisting planets have the same key as the first entry in combat_array. This means if there are no PO ships (by some strange bug thingy), then they will match the rest of the ships, and it'll be a free-for-all (as normal).
			$battle_group_planets_array[] = array('fighters' => $total_planet_figs, 'planet_destroyed' => 0, 'approx_damage' => $total_planet_figs);

			$temp_storage_array_foe = array_map("set_foe",array_flip(array_keys($target_ships)));
			$combat_array[] = $temp_storage_array_friend + $temp_storage_array_foe;


			unset($po_combat_array, $po_ship_power);
			break;


		default;
			write_to_error_log($st[272]);
			break;
		}

		unset($temp_storage_array_friend, $temp_storage_array_foe);

/**************************************************************************************
*                                End of tactical Processing
**************************************************************************************/

/**************************************************************************************
*                              Beginning of Damage Processing
**************************************************************************************/


		//ensure we actually have something in the combat array.
		if(empty($combat_array)){
			write_to_error_log("$ ".$st[273],$st[274]);
		}


		//start of short report
		if($planet_attack == 1){
			$short_str = "<br /><b class='b1'>{$players_array[$user['login_id']]['login_name_link']}</b> ".$st[275]." <b class='b1'>{$target_planets[$target]['planet_name']}</b> ".$cw['in_system']." #<b>$combat_loc</b>. <p />".$cw['participants']."";
		} else {
			$short_str = "<br /><b class='b1'>{$players_array[$user['login_id']]['login_name_link']}</b> ".$st[276]." #<b>$combat_loc</b>. <p />".$cw['participants'];
		}

		//start of advanced report.
		if($simulate_attack == 0){
			$tech_str = sprintf($st[277], $combat_loc, $total_attack_turn_cost);
		} else {
			$tech_str = $st[278];
		}
		$temp_str = make_table(array("", $cw['attacker']."(s)", $cw['defender']."(s)"));
		$temp_str .= make_row(array("<b class='b1'>".$cw['players']."</b>", "<b>$friendly_details[user_count]</b> ($friends_name_str)", "<b>$target_details[user_count]</b> ($foes_name_str)"));
		$temp_str .= make_row(array("<b class='b1'>".$cw['ships']."</b>", $friendly_details['ship_count'], $target_details['ship_count']));
		$temp_str .= make_row(array("<b class='b1'>".$cw['ship_fighters']."</b>", $friendly_details['fighter_count'], $target_details['fighter_count']));
		$temp_str .= make_row(array("<b class='b1'>".$cw['ship_weapon_systems']."</b>", $friendly_details['upgrade_count'], $target_details['upgrade_count']));
		$temp_str .= make_row(array("<b class='b1'>".$cw['planets']."</b>", 0, count($target_planets)));
		$temp_str .= make_row(array("<b class='b1'>".$cw['planet_fighters']."</b>", 0, $report_figs));
		$temp_str .= "</table>";

		$tech_str .= $temp_str;
		$short_str .= $temp_str;
		unset($temp_str, $report_figs);

/*********************************
!!!!Big processing loop begins!!!!
*********************************/

		//loop through the combat array, and begin processing the ships.
		foreach($combat_array as $battle_group_number => $battle_group_array) {

			//Declaration of all the necassary variables.
			$user_group = array('ship_count' => 0, 'fighters' => 0, 'shields' => 0, 'armour' => 0, 'speed' => 0, 'size' => 0, 'exp' => 0, 'config' => "", 'fighters_ship_count' => 0, 'shields_ship_count' => 0, 'armour_ship_count' => 0, 'points_lost' => 0);
			$target_group = array('ship_count' => 0, 'fighters' => 0, 'shields' => 0, 'armour' => 0, 'speed' => 0, 'size' => 0, 'exp' => 0, 'config' => "", 'fighters_ship_count' => 0, 'shields_ship_count' => 0, 'armour_ship_count' => 0, 'points_lost' => 0, 'planets' => 0);

			$u_bonus = array('num_ot' => 0, 'num_dt' => 0, 'num_pc' => 0, 'num_ew' => 0, 'dt' => 0, 'ot' => 0, 'pc' => 0, 'ewa' => 0, 'ewd' => 0);
			$t_bonus = array('num_ot' => 0, 'num_dt' => 0, 'num_pc' => 0, 'num_ew' => 0, 'dt' => 0, 'ot' => 0, 'pc' => 0, 'ewa' => 0, 'ewd' => 0);

			$po_ship_figs_friends = 0;
			$po_ship_figs_foes = 0;
			$so_ship_figs_friends = 0;
			$so_ship_figs_foes = 0;

			//set up the groups for the array.
			foreach($battle_group_array as $ship_id => $friend_foe){
				//dump friend into array
				if($friend_foe == 1){
					$user_group['ship_count']++;
					$user_group['fighters'] += $friendly_ships[$ship_id]['fighters'];
					$user_group['shields'] += $friendly_ships[$ship_id]['shields'];
					$user_group['armour'] += $friendly_ships[$ship_id]['armour'];
					$user_group['speed'] += $friendly_ships[$ship_id]['move_turn_cost'];
					$user_group['size'] += $friendly_ships[$ship_id]['size'];
					$user_group['exp'] += $friendly_ships[$ship_id]['points_killed'];
					$user_group['config'] .= ":".$friendly_ships[$ship_id]['config'];

					//if ship has figs/shields/armour, increase count of that type by 1 (for damage distro later)
					$friendly_ships[$ship_id]['fighters'] > 0 ? $user_group['fighters_ship_count'] ++ : 1;
					$friendly_ships[$ship_id]['shields'] > 0 ? $user_group['shields_ship_count'] ++ : 1;
					$friendly_ships[$ship_id]['armour'] > 0 ? $user_group['armour_ship_count'] ++ : 1;

					$u_bonus['num_ot'] += $friendly_ships[$ship_id]['num_ot'];
					$u_bonus['num_dt'] += $friendly_ships[$ship_id]['num_dt'];
					$u_bonus['num_pc'] += $friendly_ships[$ship_id]['num_pc'];
					$u_bonus['num_ew'] += $friendly_ships[$ship_id]['num_ew'];

					if(config_check("po",$friendly_ships[$ship_id])){
						$po_ship_figs_friends += $friendly_ships[$ship_id]['fighters'];
					} elseif(config_check("so",$friendly_ships[$ship_id])){
						$so_ship_figs_friends += $friendly_ships[$ship_id]['fighters'];
					}

				//dump foe into array
				} elseif($friend_foe == 2) {
				//playing with planet data. So had best be careful.
					if($ship_id == -1){
						$target_group['planets'] = 1;
						$target_group['fighters'] += $battle_group_planets_array[$battle_group_number]['fighters'];
						$target_group['fighters_ship_count'] ++;
						continue 1;
					}

					$target_group['ship_count']++;
					$target_group['fighters'] += $target_ships[$ship_id]['fighters'];
					$target_group['shields'] += $target_ships[$ship_id]['shields'];
					$target_group['armour'] += $target_ships[$ship_id]['armour'];
					$target_group['speed'] += $target_ships[$ship_id]['move_turn_cost'];
					$target_group['size'] += $target_ships[$ship_id]['size'];
					$target_group['exp'] += $target_ships[$ship_id]['points_killed'];
					$target_group['config'] .= ":".$target_ships[$ship_id]['config'];

					//if ship has figs/shields/armour, increase count of that type by 1 (for damage distro later)
					$target_ships[$ship_id]['fighters'] > 0 ? $target_group['fighters_ship_count'] ++ : 1;
					$target_ships[$ship_id]['shields'] > 0 ? $target_group['shields_ship_count'] ++ : 1;
					$target_ships[$ship_id]['armour'] > 0 ? $target_group['armour_ship_count'] ++ : 1;

					$t_bonus['num_ot'] += $target_ships[$ship_id]['num_ot'];
					$t_bonus['num_dt'] += $target_ships[$ship_id]['num_dt'];
					$t_bonus['num_pc'] += $target_ships[$ship_id]['num_pc'];
					$t_bonus['num_ew'] += $target_ships[$ship_id]['num_ew'];

					if(config_check("po",$target_ships[$ship_id])){
						$po_ship_figs_foes += $target_ships[$ship_id]['fighters'];
					} elseif(config_check("so",$target_ships[$ship_id])){
						$po_ship_figs_foes += $target_ships[$ship_id]['fighters'];
					}
				}
			} //end foreach for ships dumping ships into groups

			//remember how many fighters had in each group at start of combat. This copy is used for fig kills calculations.
			$user_fighters = $user_group['fighters'];
			$target_fighters = $target_group['fighters'];


			//make sure neither side has 0 ships.
			if(($target_group['ship_count'] == 0 && $target_group['planets'] == 0) || $user_group['ship_count'] == 0){
				write_to_error_log(sprintf($st[281], $target_group[ship_count], $user_group[ship_count], $gen_tactic_to_use));
			}

			//work out the averages of stuff
			$user_group['speed'] = average_finder($user_group['speed'],$user_group['ship_count']);
			if($planet_attack == 0){
				$target_group['speed'] = average_finder($target_group['speed'],$target_group['ship_count']);
				$target_group['size'] = average_finder($target_group['size'],$target_group['ship_count']);
				$target_group['exp'] = average_finder($target_group['exp'],$target_group['ship_count']);
			} else {
				$target_group['speed'] = 0;
				$target_group['size'] = 0;
				$target_group['exp'] = 0;
			}

			$user_group['size'] = average_finder($user_group['size'],$user_group['ship_count']);
			$user_group['exp'] = average_finder($user_group['exp'],$user_group['ship_count']);

			//update bonus's.
			bonus_calc($u_bonus);
			bonus_calc($t_bonus);


			//declaration of some variables to do with working out who damages what.
			$t_figs_lost = 0;
			$u_figs_lost = 0;
			$t_shields_lost = 0;
			$u_shields_lost = 0;

			//should use the ship replicas of the orignals to work out whats left on the ship to eliminate.
			$t_replica = $target_group;
			$u_replica = $user_group;


			//replica's of the fighter counts. These will be changed by the defensive turrets, and used to work out the final fighter damage.
			$t_fig_replica = $target_group['fighters'];
			$u_fig_replica = $user_group['fighters'];


			//variables that dictate whether a whole group has been destroyed.
			$target_group_destroyed = 0;
			$user_group_destroyed = 0;



	/**************
	* Start of Electronic Warfare
	***************/
			//only use EW if there is no planet involved.
			if($target_group['planets'] == 0){
				//electronic warfare pods cancel each other out
				if($u_bonus['num_ew'] == $t_bonus['num_ew'] && $u_bonus['num_ew'] > 0){
					$t_bonus['ewa'] = 0;
					$t_bonus['ewd'] = 0;
					$u_bonus['ewa'] = 0;
					$u_bonus['ewd'] = 0;
				} elseif($u_bonus['num_ew'] > $t_bonus['num_ew'] && $u_bonus['num_ew'] > 0){ //attacker has more EW pods
					$u_bonus['ewd'] -= $t_bonus['ewa'];
					$t_bonus['ewa'] = 0;
					$t_bonus['ewd'] = 0;

				} elseif($u_bonus['num_ew'] < $t_bonus['num_ew'] && $t_bonus['num_ew'] > 0){ //defender has more EW pods
					$t_bonus['ewa'] -= $u_bonus['ewd'];
					$t_bonus['ewd'] -= $u_bonus['ewa'];
					$u_bonus['ewa'] = 0;
					$u_bonus['ewd'] = 0;
				}
			}//exit the no-planets restriction, so can play with turret abilities.


			//ensures none of the bonuses go below 0.
			$u_bonus = array_map("leveller",$u_bonus);
			$t_bonus = array_map("leveller",$t_bonus);

			//offensive turret damage merged.
			$t_bonus['at'] = $t_bonus['ot'] + $t_bonus['pc'];
			$u_bonus['at'] = $u_bonus['ot'] + $u_bonus['pc'];

			//not needed any more, so get rid of them to make sure not accidentally used.
			unset($u_bonus['pc'], $u_bonus['ot']);
			unset($t_bonus['pc'], $t_bonus['ot']);


			if($target_group['planets'] == 0){
				//--------------
				// EW's defenses v's offensive turrets
				//--------------
				//defensives for attacker are greater than the defenders turrets can handle.
				if($u_bonus['ewd'] >= $t_bonus['at'] && $t_bonus['at'] > 0){
					$u_bonus['ewd'] -= $t_bonus['at'];
					$t_bonus['at'] = 0;

				//Attacking turrets nullify the EW pods defensives.
				} elseif($u_bonus['ewd'] < $t_bonus['at'] && $u_bonus['ewd'] > 0){
					$t_bonus['at'] -= $u_bonus['ewd'];
					$u_bonus['ewd'] = 0;

				//defensives for defender are greater than the attackers turrets can handle.
				} elseif($t_bonus['ewd'] >= $u_bonus['at'] && $u_bonus['at'] > 0){
					$t_bonus['ewd'] -= $u_bonus['at'];
					$u_bonus['at'] = 0;

				//Attacking turrets nullify the EW pods defensives.
				} elseif($t_bonus['ewd'] < $u_bonus['at'] && $t_bonus['ewd'] > 0){
					$u_bonus['at'] -= $t_bonus['ewd'];
					$t_bonus['ewd'] = 0;
				}


				//=============
				// EW attacks defensive turrets
				//=============
				//EW for attacker greater than the defenders turrets can handle.
				if($u_bonus['ewa'] >= $t_bonus['dt'] && $t_bonus['dt'] > 0){
					$u_bonus['ewa'] -= $t_bonus['dt'];
					$t_bonus['dt'] = 0;

				//Defensive turrets nullify the EW pods attack.
				} elseif($u_bonus['ewa'] < $t_bonus['dt'] && $u_bonus['ewa'] > 0){
					$t_bonus['dt'] -= $u_bonus['ewa'];
					$u_bonus['ewa'] = 0;

				//defensives for defender are greater than the attackers turrets can handle.
				} elseif($t_bonus['ewa'] >= $u_bonus['dt'] && $u_bonus['dt'] > 0){
					$t_bonus['ewa'] -= $u_bonus['dt'];
					$u_bonus['dt'] = 0;

				//Attacking turrets nullify the EW pods defensives.
				} elseif($t_bonus['ewa'] < $u_bonus['dt'] && $t_bonus['ewa'] > 0){
					$u_bonus['dt'] -= $t_bonus['ewa'];
					$t_bonus['ewa'] = 0;
				}


				//combine the offensive and defensive remnants to take out the enemy fighters.
				$u_bonus['ew'] = $u_bonus['ewa'] + $u_bonus['ewd'];
				$t_bonus['ew'] = $t_bonus['ewa'] + $t_bonus['ewd'];


				//========
				//EW attacks enemy fighters
				//========
				//EW takes out the defenders fighters
				if($u_bonus['ew'] >= $t_replica['fighters'] && $t_replica['fighters'] > 0){
					$u_bonus['ew'] -= $t_replica['fighters'];
					$t_replica['fighters'] = 0;

				//EW takes out some of the defenders fighters.
				} elseif($u_bonus['ew'] < $t_replica['fighters'] && $u_bonus['ew'] > 0) {
					$t_replica['fighters'] -= $u_bonus['ew'];
					$u_bonus['ew'] = 0;

				//EW takes out the attackers fighters
				} elseif($t_bonus['ew'] >= $u_replica['fighters'] && $u_replica['fighters'] > 0){
					$t_bonus['ew'] -= $u_replica['fighters'];
					$u_replica['fighters'] = 0;

				//EW takes out some of the attackers fighters.
				} elseif($t_bonus['ew'] < $u_replica['fighters'] && $t_bonus['ew'] > 0) {
					$u_replica['fighters'] -= $t_bonus['ew'];
					$t_bonus['ew'] = 0;
				}
			}//end skipping of EW's due to planetary involvement
			//==================
			//end of EW part of the attacking system
			//==================

			//=======
			//start of defensive turret section
			//=======
			//attacking ship takes out defenders fighters with defensive turret.
			if($u_bonus['dt'] >= $t_replica['fighters'] && $t_replica['fighters'] > 0){
				$u_bonus['dt'] -= $t_replica['fighters'];
				$t_replica['fighters'] = 0;
				$t_fig_replica = 0;

			//attacker takes out some defensive fighters
			} elseif ($u_bonus['dt'] < $t_replica['fighters'] && $u_bonus['dt'] > 0){
				$t_replica['fighters'] -= $u_bonus['dt'];
				$t_fig_replica -= $u_bonus['dt'];
				$u_bonus['dt'] = 0;
			}

			if($planet_attack == 0){//planets can't have turrets!
				//defending ship ship takes out attackers fighters with defensive turret.
				if($t_bonus['dt'] >= $u_replica['fighters'] && $u_replica['fighters'] > 0){
					$t_bonus['dt'] -= $u_replica['fighters'];
					$u_replica['fighters'] = 0;
					$u_fig_replica = 0;

				//defender takes out some attacking fighters
				} elseif ($t_bonus['dt'] < $u_replica['fighters'] && $t_bonus['dt'] > 0){
					$u_replica['fighters'] -= $t_bonus['dt'];
					$u_fig_replica -= $t_bonus['dt'];
					$t_bonus['dt'] = 0;
				}
			}
			/***************
			* Defensive Turrets complete.
			***************/

			//==============
			// Offensive turrets v's shields
			//==============
			if($planet_attack == 0){//planets can't have turrets, or shields, so no point running this
				//shields eliminated on defending ship by turrets.
				if($u_bonus['at'] >= $t_replica['shields'] && $t_replica['shields'] > 0){
					$u_bonus['at'] -= $t_replica['shields'];
					$t_replica['shields'] = 0;

				//turrets stopped by defending ships shields
				} elseif($u_bonus['at'] < $t_replica['shields'] && $u_bonus['at'] > 0){
					$t_replica['shields'] -= $u_bonus['at'];
					$u_bonus['at'] = 0;
				}
				//shields eliminated on attacking ship by turrets.
				if($t_bonus['at'] >= $u_replica['shields'] && $u_replica['shields'] > 0){
					$t_bonus['at'] -= $u_replica['shields'];
					$u_replica['shields'] = 0;

				//turrets stopped by attacking ships shields
				} elseif($t_bonus['at'] < $u_replica['shields'] && $t_bonus['at'] > 0){
					$u_replica['shields'] -= $t_bonus['at'];
					$t_bonus['at'] = 0;
				}
			}

			//==============
			// Offensive turrets v's fighters
			//==============
			//fighters eliminated on defending ship by turrets.
			if($u_bonus['at'] >= $t_replica['fighters'] && $t_replica['fighters'] > 0){
				$u_bonus['at'] -= $t_replica['fighters'];
				$t_replica['fighters'] = 0;

			//turrets stopped by defending ships fighters
			} elseif($u_bonus['at'] < $t_replica['fighters'] && $u_bonus['at'] > 0){
				$t_replica['fighters'] -= $u_bonus['at'];
				$u_bonus['at'] = 0;
			}

			if($planet_attack == 0){//no turrets on planets.
				//fighters eliminated on attacking ship by turrets.
				if($t_bonus['at'] >= $u_replica['fighters'] && $u_replica['fighters'] > 0){
					$t_bonus['at'] -= $u_replica['fighters'];
					$u_replica['fighters'] = 0;

				//turrets stopped by attacking ships fighters
				} elseif($t_bonus['at'] < $u_replica['fighters'] && $t_bonus['at'] > 0){
					$u_replica['fighters'] -= $t_bonus['at'];
					$t_bonus['at'] = 0;
				}
			}

			//==============
			// Offensive turrets v's armour - Armour GENERALLY takes twice as much damage as either shields or fighters!
			//==============

			if($planet_attack == 0){//planets can't have turrets, or armour, so no point running this
				//armour eliminated on defending ship by turrets. - SHIP GROUP DESTROYED!
				if($u_bonus['at'] >= ($t_replica['armour'] * $armour_multiplier) && $t_replica['armour'] > 0){
					$u_bonus['at'] -= ($t_replica['armour'] * $armour_multiplier);
					$t_replica['armour'] = 0;
					$target_group_destroyed = 1;

				//turrets stopped by defending ships armour
				} elseif($u_bonus['at'] < ($t_replica['armour'] * $armour_multiplier) && $u_bonus['at'] > 0){
					$t_replica['armour'] -= ceil($u_bonus['at'] / $armour_multiplier);
					$u_bonus['at'] = 0;
				}

				//armour eliminated on attacking ship by turrets. - SHIP GROUP DESTROYED!
				if($t_bonus['at'] >= ($u_replica['armour'] * $armour_multiplier) && $u_replica['armour'] > 0){
					$t_bonus['at'] -= ($u_replica['armour'] * $armour_multiplier);
					$u_replica['armour'] = 0;
					$user_group_destroyed = 1;

				//turrets stopped by attacking ships armour
				} elseif($t_bonus['at'] < ($u_replica['armour'] * $armour_multiplier) && $t_bonus['at'] > 0){
					$u_replica['armour'] -= ceil($t_bonus['at'] / $armour_multiplier);
					$t_bonus['at'] = 0;
				}
			}

			/*****************************
			* Upgrades compeleted! Down to the real business. Fighters!!!
			*****************************/

			//Attacker: Work out how many fighters are left (after DT damage), then work out damage to do.
			if($u_fig_replica > 0){
				$user_fig_dam = round($u_fig_replica * 0.65);
				$user_fig_dam += mt_rand(round(-$u_fig_replica * 0.06),round($u_fig_replica * 0.06));
			} else {
				$user_fig_dam = 1;
			}

			//defender: Work out how many fighters are left (after DT damage), then work out damage to do.
			if($t_fig_replica > 0){
				if($planet_attack == 1){ //planets do extra damage with more randomness.
					$fig_dam_ratio = 0.95;
					$randomness = 0.15;
				} else {
					$fig_dam_ratio = 0.85;
					$randomness = 0.07;
				}
				$target_fig_dam = round($t_fig_replica * $fig_dam_ratio);
				$target_fig_dam += mt_rand(round(-$t_fig_replica * $randomness),round($t_fig_replica * $randomness));
			} else {
				$target_fig_dam = 1;
			}

			//ensure nothing is negative.
			if($user_fig_dam < 1){
				$user_fig_dam = 1;
			}
			if($target_fig_dam < 1){
				$target_fig_dam = 1;
			}

			//declare up the attack modifiers.
			$user_fig_dam_xtra = 1;
			$user_fig_dam_less = 1;

			$target_fig_dam_xtra = 1;
			$target_fig_dam_less = 1;

			//take into account ship speed, and ship size.
			$user_fig_dam_xtra += $target_group['speed'] + $target_group['size'] * 1.5;
			$target_fig_dam_xtra += $user_group['speed'] + $user_group['size'] * 1.5;

			$user_fig_dam_xtra -= ($user_group['speed'] * 2);
			$target_fig_dam_xtra -= ($target_group['speed'] * 2);


			//ship experience - valuable stuff!
			$user_fig_dam_xtra += resolve_level($user_group['exp']);
			$target_fig_dam_xtra += resolve_level($target_group['exp']);


			//take into account ship specialties
			$user_fig_dam_xtra += inc_dam("bs",$user_group,10);
			$target_fig_dam_xtra += inc_dam("bs",$target_group,10);

			$user_fig_dam_xtra += inc_dam("hs",$user_group,3.5);
			$target_fig_dam_xtra += inc_dam("hs",$target_group,3.5);

			$user_fig_dam_xtra -= inc_dam("hs",$target_group,4.5);
			$target_fig_dam_xtra -= inc_dam("hs",$user_group,4.5);

			$user_fig_dam_xtra += inc_dam("ls",$user_group,1);
			$target_fig_dam_xtra += inc_dam("ls",$target_group,1);

			$user_fig_dam_xtra -= inc_dam("ls",$target_group,3);
			$target_fig_dam_xtra -= inc_dam("ls",$user_group,3);

			$user_fig_dam_xtra += inc_dam("sc",$user_group,5);
			$target_fig_dam_xtra += inc_dam("sc",$target_group,5);

			$user_fig_dam_xtra += inc_dam("fr",$user_group,4);
			$target_fig_dam_xtra += inc_dam("fr",$target_group,4);

			$user_fig_dam_xtra -= inc_dam("fr",$user_group,7.5);
			$target_fig_dam_xtra -= inc_dam("fr",$target_group,7.5);

			if($target_group['planets'] == 0){//no SO bonuses if planets involved
				$user_fig_dam_xtra += inc_dam("so",$user_group,5);
				$target_fig_dam_xtra += inc_dam("so",$target_group,5);
			}

			//Effects of attacking SO ships are nullified when attacking a fleet that has planets defending it.
			//however, PO ships do full damage when planets are involved.

			//PO ships get disadvantage against only ships
			if($po_ship_figs_foes > 0){ //foes PO ships
				$target_fig_dam_xtra -= ($po_ship_figs_foes / $target_group['fighters']) * 85;
				$user_fig_dam_xtra += ($po_ship_figs_foes / $target_group['fighters']) * 10;
				if($so_ship_figs_friends > 0 && $target_group['planets'] == 0){//SO ships are particularly dangererous against PO ships.
					$user_fig_dam_xtra += ($po_ship_figs_foes / $target_group['fighters']) * 85;
					$target_fig_dam_xtra -= ($po_ship_figs_foes / $target_group['fighters']) * 15;
				}
			}
			//planet only ships get Advantage against planets.
			if($po_ship_figs_friends > 0 && $target_group['planets'] == 1) {
				$user_fig_dam_xtra += (($po_ship_figs_friends / $user_group['fighters']) * (($battle_group_planets_array[$battle_group_number]['fighters'] / $target_group['fighters']) * 100)) * 0.8;
				$target_fig_dam_xtra -= ($po_ship_figs_friends / $user_group['fighters']) * 10;
			}

			//SO ships get advantage against ships
			if($so_ship_figs_foes > 0){ //enemies SO figs
				$target_fig_dam_xtra += ($so_ship_figs_foes / $target_group['fighters']) * 20;
			}
			if($so_ship_figs_friends > 0 && $target_group['planets'] == 0){//friends SO figs
				$user_fig_dam_xtra += ($so_ship_figs_friends / $user_group['fighters']) * 20;
			}
			unset($po_ship_figs_foes, $po_ship_figs_friends, $so_ship_figs_foes, $so_ship_figs_friends);

//echo "<p />".__LINE__."<br />ux: $user_fig_dam_xtra <br />tx: $target_fig_dam_xtra";


			$user_fig_before = $user_fig_dam;
			$target_fig_before = $target_fig_dam;

			//do the final calculations
			$user_fig_dam += $user_fig_dam * ($user_fig_dam_xtra /100);
			$target_fig_dam += $target_fig_dam * ($target_fig_dam_xtra /100);

			$user_fig_dam = round($user_fig_dam);
			$target_fig_dam = round($target_fig_dam);

			//ships can't do less than 1/ 9th the damage they would normally have done.
			if($user_fig_dam < ($user_fig_before / 9)){
				$user_fig_dam = round($user_fig_before / 9);
			}
			if($target_fig_dam < ($target_fig_before / 9)){
				$target_fig_dam = round($target_fig_before / 9);
			}
			unset($user_fig_before, $target_fig_before);


			//ensure nothing is negative.
			if($user_fig_dam < 1){
				$user_fig_dam = 1;
			}
			if($target_fig_dam < 1){
				$target_fig_dam = 1;
			}

			//update the groups
			$u_fighters_lost = $user_group['fighters'] - $u_replica['fighters'];
			$u_shields_lost = $user_group['shields'] - $u_replica['shields'];
			$u_armour_lost = $user_group['armour'] - $u_replica['armour'];

			//target
			//set the planet(s) as destroyed (by the upgrades). Saves having to do calculations later.
			if($t_replica['fighters'] < 1){
				$battle_group_planets_array[$battle_group_number]['planet_destroyed'] = 1;
			}
			$t_fighters_lost = $target_group['fighters'] - $t_replica['fighters'];
			if($planet_attack == 0){//don't need to run this for planet attacks.
				$t_shields_lost = $target_group['shields'] - $t_replica['shields'];
				$t_armour_lost = $target_group['armour'] - $t_replica['armour'];
			}

			//take the ship count and use it.
			$user_group['approx_damage_ship_count'] = $user_group['ship_count'];
			$target_group['approx_damage_ship_count'] = $target_group['ship_count'];

			$user_group['approx_damage'] = $user_group['fighters'] + $user_group['shields'] + ($user_group['armour'] * $armour_multiplier);
			$target_group['approx_damage'] = $target_group['fighters'] + $target_group['shields'] + ($target_group['armour'] * $armour_multiplier);

			if($target_group['planets'] == 1){//add 1 to approx_damage count, so as
				$target_group['approx_damage_ship_count'] ++;
			}


			//determine if the group was destroyed outright or not.
			if($user_fig_dam >= $target_group['approx_damage']){
				$target_group_destroyed = 1;
				$battle_group_planets_array[$battle_group_number]['planet_destroyed'] = 1;
			}
			if($target_fig_dam >= $user_group['approx_damage']){
				$user_group_destroyed = 1;
			}


/**************************************************
!!! Looping Through ships in battlegroup begins !!!
**************************************************/

			//loop through the friendly ships to distribute the damage between them.
			foreach($battle_group_array as $ship_id => $friend_foe){

				/***************
				*Friendly Ship Damage
				**************/
				if($friend_foe == 1 && ($target_fig_dam > 0 || $u_fighters_lost > 0 || $u_shields_lost > 0 || $u_armour_lost > 0)){

					//if the user group is already confirmed as destroyed, we can set all the ships to destroyed and be done with them.
					if($user_group_destroyed == 1){
						$friendly_ships[$ship_id]['ship_destroyed'] = 1;
						$user_group['points_lost'] += $friendly_ships[$ship_id]['point_value'];
						$friends_killed ++;
						continue 1;
					}

					/**************************
					*Distribute Upgrade Damage
					***************************/

					//Fighters
					if($user_group['fighters'] < 1){
						$friendly_ships[$ship_id]['fighters'] = 0;
					} elseif($u_fighters_lost > 0 && $friendly_ships[$ship_id]['fighters'] > 0){
						if(damage_ship_predefined($u_fighters_lost, $friendly_ships[$ship_id], $user_group, "fighters",$friendly_details)){
							$friends_killed ++;
							$user_group['points_lost'] += $friendly_ships[$ship_id]['point_value'];
							continue 1;
						}
					}

					//shields
					if($user_group['shields'] < 1){
						$friendly_ships[$ship_id]['shields'] = 0;
					} elseif($u_shields_lost > 0 && $friendly_ships[$ship_id]['shields'] > 0){
						if(damage_ship_predefined($u_shields_lost, $friendly_ships[$ship_id], $user_group, "shields",$friendly_details)){
							$friends_killed ++;
							$user_group['points_lost'] += $friendly_ships[$ship_id]['point_value'];
							continue 1;
						}
					}

					//armour
					if($user_group['armour'] < 1){
						$friendly_ships[$ship_id]['armour'] = 0;
					} elseif($u_armour_lost > 0 && $friendly_ships[$ship_id]['armour'] > 0){
						damage_ship_predefined($u_armour_lost, $friendly_ships[$ship_id], $user_group, "armour",$friendly_details);
					}


					/******************************************************************************
												Distribute Fighter Damage.
					*******************************************************************************/

					//ensure there is a need to process this lot.
					if($target_fig_dam > 1){

						//may as well kill the ship, as it has nothing on it.
						if($friendly_ships[$ship_id]['approx_damage'] < 1){
							$friendly_ships[$ship_id]['ship_destroyed'] = 1;
							$user_group['points_lost'] += $friendly_ships[$ship_id]['point_value'];
							$friends_killed ++;

						//there are some fighters on the ship, and we can hurt them...
						} else {
							$fig_kills = resolve_damage_to_do($target_fig_dam,$friendly_ships[$ship_id], $user_group, "approx_damage");

							//remove the ship from the ship group
							$user_group['approx_damage_ship_count'] --;
							$user_group['approx_damage'] -= $friendly_ships[$ship_id]['approx_damage'];

							$target_fig_dam -= $fig_kills;

							//enemy eliminated on attacking ship.
							if($fig_kills >= $friendly_ships[$ship_id]['approx_damage'] && $fig_kills > 0){
								$friendly_ships[$ship_id]['ship_destroyed'] = 1;
								$user_group['points_lost'] += $friendly_ships[$ship_id]['point_value'];
								$friends_killed ++;

							//not enough damage done to kill the ship.
							} elseif($fig_kills < $friendly_ships[$ship_id]['approx_damage'] && $fig_kills > 0){
								$approx_power_reduction = 0;

								//damage SOME shields
								if($fig_kills <= $friendly_ships[$ship_id]['shields']){
									$friendly_ships[$ship_id]['shields'] -= $fig_kills;
									$approx_power_reduction += $fig_kills;
									$fig_kills = 0;

								//There are enough fig kills to take out any shields and then some
								} else {
									$fig_kills -= $friendly_ships[$ship_id]['shields'];
									$approx_power_reduction += $friendly_ships[$ship_id]['shields'];
									$friendly_ships[$ship_id]['shields'] = 0;

									//damage SOME fighters
									if($fig_kills <= $friendly_ships[$ship_id]['fighters']){
										$friendly_ships[$ship_id]['fighters'] -= $fig_kills;
										$approx_power_reduction += $fig_kills;
										$fig_kills = 0;

									//can do some damage to the Ship armour now.
									} else {
										$fig_kills -= $friendly_ships[$ship_id]['fighters'];
										$approx_power_reduction += $friendly_ships[$ship_id]['fighters'];
										$friendly_ships[$ship_id]['fighters'] = 0;

										//damage SOME armour
										if($fig_kills < ($friendly_ships[$ship_id]['armour'] * $armour_multiplier)){
											$armour_lost = ceil($fig_kills / $armour_multiplier);
											dist_rand_damage($armour_lost, $friendly_ships[$ship_id]);
											$friendly_ships[$ship_id]['armour'] -= $armour_lost;
											$approx_power_reduction += $armour_lost;
											$fig_kills = 0;
										//in THEORY the game shouldn't get to this statement. But just in case.
										} else {
											$approx_power_reduction += ($friendly_ships[$ship_id]['armour'] * $armour_multiplier);
											$friendly_ships[$ship_id]['ship_destroyed'] = 1;
											$user_group['points_lost'] += $friendly_ships[$ship_id]['point_value'];
											$friends_killed ++;
											$fig_kills = 0;

										}// end of armour if
									}// end of fighter if
								}// end of shields if

							//update all the vars with the requisite changes.

							$friendly_ships[$ship_id]['approx_damage'] -= $approx_power_reduction;

							} //end of damagaing ship if - inner.
						} //end of damagaing ship if - outer.

					}//end of fighter damage if.


				/*****************************************************************************
				*								Target Ship Damage
				******************************************************************************/

				} elseif($friend_foe == 2 && ($user_fig_dam > 0 || $t_fighters_lost > 0 || $t_shields_lost > 0 || $t_armour_lost > 0)) {

					/****************
					Planet Damaging
					****************/
					if($ship_id == -1){
						if($battle_group_planets_array[$battle_group_number]['planet_destroyed'] == 1){
							$target_group['approx_damage_ship_count'] --;
							$total_planet_dam_taken += $battle_group_planets_array[$battle_group_number]['fighters'];
							$target_group['fighters'] -= $battle_group_planets_array[$battle_group_number]['fighters'];
							$target_group['approx_damage'] -= $battle_group_planets_array[$battle_group_number]['fighters'];
							$battle_group_planets_array[$battle_group_number]['fighters'] = 0;
							continue 1;
						}

						//Damage from enemy upgrades
						if($t_fighters_lost > 0 && $battle_group_planets_array[$battle_group_number]['fighters'] > 0){
							$planet_figs_temp = $battle_group_planets_array[$battle_group_number]['fighters'];
							if(damage_ship_predefined($t_fighters_lost, $battle_group_planets_array[$battle_group_number], $target_group, "fighters",$target_details)){
								$total_planet_dam_taken += $planet_figs_temp;
								$target_group['approx_damage_ship_count'] --;
								$battle_group_planets_array[$battle_group_number]['planet_destroyed'] = 1;
								continue 1;
							}
							$total_planet_dam_taken = $planet_figs_temp - $battle_group_planets_array[$battle_group_number]['fighters'];

							//set approx_damage for the planet to the new fighter count.
							$battle_group_planets_array[$battle_group_number]['approx_damage'] = $battle_group_planets_array[$battle_group_number]['fighters'];
						}
						//total_planet_dam_taken
						$planet_fig_kills = resolve_damage_to_do($user_fig_dam, $battle_group_planets_array[$battle_group_number], $target_group, "approx_damage");

						//update relevent vars
						$user_fig_dam -= $planet_fig_kills;
						$target_group['approx_damage_ship_count'] --;
						$temp_p_figs = $battle_group_planets_array[$battle_group_number]['fighters'];
						$target_group['approx_damage'] -= $temp_p_figs;

						//planet was destroyed
						if($planet_fig_kills >= $temp_p_figs){
							$lost_figs = $temp_p_figs;
							$battle_group_planets_array[$battle_group_number]['planet_destroyed'] = 1;

						//planet wasn't destroyed.
						} else {
							$lost_figs = $planet_fig_kills;
						}

						$total_planet_dam_taken += $lost_figs;
						$battle_group_planets_array[$battle_group_number]['fighters'] -= $lost_figs;
						$target_group['fighters'] -= $lost_figs;

						//re-align approx_damage to fig count.
						$battle_group_planets_array[$battle_group_number]['approx_damage'] = $battle_group_planets_array[$battle_group_number]['fighters'];

						unset($lost_figs, $planet_fig_kills, $temp_p_figs);

					/****************
					Ship Damaging
					****************/
					} else {

						//if the user group is already confirmed as destroyed, we can set all the ships to destroyed and be done with them.
						if($target_group_destroyed == 1){
							$target_ships[$ship_id]['ship_destroyed'] = 1;
							$target_group['points_lost'] += $target_ships[$ship_id]['point_value'];
							$targets_killed ++;
							continue 1;
						}

						/**************************
						*Distribute Upgrade Damage
						***************************/

						//Fighters
						if($target_group['fighters'] < 1){
							$target_ships[$ship_id]['fighters'] = 0;
						} elseif($t_fighters_lost > 0 && $target_ships[$ship_id]['fighters'] > 0){
							if(damage_ship_predefined($t_fighters_lost, $target_ships[$ship_id], $target_group, "fighters",$target_details)){
								$targets_killed ++;
								$target_group['points_lost'] += $target_ships[$ship_id]['point_value'];
								continue 1;
							}
						}

						//shields
						if($target_group['shields'] < 1){
							$target_ships[$ship_id]['shields'] = 0;
						} elseif($t_shields_lost > 0 && $target_ships[$ship_id]['shields'] > 0){
							if(damage_ship_predefined($t_shields_lost, $target_ships[$ship_id], $target_group, "shields",$target_details)){
								$targets_killed ++;
								$target_group['points_lost'] += $target_ships[$ship_id]['point_value'];
								continue 1;
							}
						}

						//armour
						if($target_group['armour'] < 1){
							$target_ships[$ship_id]['armour'] = 0;
						} elseif($t_armour_lost > 0 && $target_ships[$ship_id]['armour'] > 0){
							damage_ship_predefined($t_armour_lost, $target_ships[$ship_id], $target_group, "armour",$target_details);
						}

						/*********************************
							Distribute Fighter Damage.
						**********************************/
						//ensure there is a need to process this lot.
						if($user_fig_dam > 1){

							//may as well kill the ship, as it has nothing on it.
							if($target_ships[$ship_id]['approx_damage'] < 1){
								$target_ships[$ship_id]['ship_destroyed'] = 1;
								$target_group['points_lost'] += $target_ships[$ship_id]['point_value'];
								$targets_killed ++;

							//there are some fighters on the ship, and we can hurt it...
							} else {
								$fig_kills = resolve_damage_to_do($user_fig_dam,$target_ships[$ship_id], $target_group, "approx_damage");

								//remove the ship from the ship group
								$target_group['approx_damage_ship_count'] --;
								$target_group['approx_damage'] -= $target_ships[$ship_id]['approx_damage'];

								$user_fig_dam -= $fig_kills;

								//this ship is dead
								if($fig_kills >= $target_ships[$ship_id]['approx_damage'] && $fig_kills > 0){
									$target_ships[$ship_id]['ship_destroyed'] = 1;
									$target_group['points_lost'] += $target_ships[$ship_id]['point_value'];
									$targets_killed ++;

								//not enough damage done to kill the ship entirely. So we'll damage it.
								} elseif($fig_kills < $target_ships[$ship_id]['approx_damage'] && $fig_kills > 0){
									$approx_power_reduction = 0;

									//damage SOME shields
									if($fig_kills <= $target_ships[$ship_id]['shields']){
										$target_ships[$ship_id]['shields'] -= $fig_kills;
										$approx_power_reduction += $fig_kills;
										$fig_kills = 0;

									//There are enough fig kills to take out any shields and then some
									} else {
										$fig_kills -= $target_ships[$ship_id]['shields'];
										$approx_power_reduction += $target_ships[$ship_id]['shields'];
										$target_ships[$ship_id]['shields'] = 0;

										//damage SOME fighters
										if($fig_kills <= $target_ships[$ship_id]['fighters']){
											$target_ships[$ship_id]['fighters'] -= $fig_kills;
											$approx_power_reduction += $fig_kills;
											$fig_kills = 0;

										//can do some damage to the Ship armour now.
										} else {
											$fig_kills -= $target_ships[$ship_id]['fighters'];
											$approx_power_reduction += $target_ships[$ship_id]['fighters'];
											$target_ships[$ship_id]['fighters'] = 0;

											//damage SOME armour
											if($fig_kills < ($target_ships[$ship_id]['armour'] * $armour_multiplier)){
												$armour_lost = ceil($fig_kills / $armour_multiplier);
												dist_rand_damage($armour_lost, $target_ships[$ship_id]);
												$target_ships[$ship_id]['armour'] -= $armour_lost;
												$approx_power_reduction += $armour_lost;
												$fig_kills = 0;

											//in THEORY the game shouldn't get to this statement. But just in case.
											} else {
												$approx_power_reduction += ($target_ships[$ship_id]['armour'] * $armour_multiplier);
												$target_ships[$ship_id]['ship_destroyed'] = 1;
												$target_group['points_lost'] += $target_ships[$ship_id]['point_value'];
												$targets_killed ++;
												$fig_kills = 0;
											}// end of armour if
										}// end of fighter if
									}// end of shields if

								//update all the vars with the requisite changes.

								$target_ships[$ship_id]['approx_damage'] -= $approx_power_reduction;

								} //end of damagaing ship 'if' - inner.
							} //end of damagaing ship 'if' - outer.

						}//end of fighter damage distribution 'if'.
					}//end of 'planet or ships' if.
				}//end of 'if' that determines whether friend or foe.
			}//end of 'foreach' through all the ships in the battlegroup to do damage processing.

			if($simulate_attack == 0){
				//loop throught the battle groups array AGAIN. just a quick one to update the ships points_killed.
				foreach($battle_group_array as $ship_id => $friend_foe){

					//attacking ships gain points
					if($friend_foe == 1 && $target_group['points_lost'] > 0  && $replica_of_ships[$ship_id]['fighters'] > 0 && $user_fighters > 0){

						$temp = round((($replica_of_ships[$ship_id]['fighters'] / $user_fighters) * 100) * ($target_group['points_lost'] / 100));

						$friendly_ships[$ship_id]['points_killed'] += $temp;
						$players_array[$replica_of_ships[$ship_id]['login_id']]['ship_points_killed'] += $temp;

					//defending ships gain points.
					} elseif($friend_foe == 2 && $ship_id != -1 && $user_group['points_lost'] > 0 && $replica_of_ships[$ship_id]['fighters'] > 0 && $target_fighters > 0) {

						$temp = round((($replica_of_ships[$ship_id]['fighters'] / $target_fighters) * 100) * ($user_group['points_lost'] / 100));

						$target_ships[$ship_id]['points_killed'] += $temp;
						$players_array[$replica_of_ships[$ship_id]['login_id']]['ship_points_killed'] += $temp;
					}
				} //end of foreach through ships in the battlegroup for score allocation.
			} //end of simulation check to skip second battlegroup loop.







	/*********************************************/
		}//end of 'foreach' through all the battlegroups.
	/*********************************************/

	/*********************************************
	* Outside of any loops!. All below code has the purpose of updating the DB, and finalising things.
	*********************************************/


		unset($combat_array);


		//merge the two ship arrays, as there is no need to process them seperately now.
		$all_ships = $friendly_ships + $target_ships;

		$delete_sql = "";
		$disengage_sql = "";
		$metal_scrap = 0;
		$fuel_scrap = 0;
		$reload_ship = 0;


		//update fighters lost to include planetary fighters.
		$target_details['fighters_lost'] += $total_planet_dam_taken;


		/************
		* Loop through all the ships in the game.
		************/

		//loop through all the ships in the combat and process them.
		foreach($all_ships as $ship_id => $ship){

			//The ship has been destroyed.
			if($ship['ship_destroyed'] == 1){
				$delete_sql .= "ship_id = '$ship_id' || "; //list of ships to delete

				$ships_involved_str[$ship['login_id']]['lost'] .= "<br /><b class='b1'>$ship[ship_name]</b> ($ship[class_name_abbr]) - Shds: <b>".$replica_of_ships[$ship_id]['shields']."</b> - Figs: <b>".$replica_of_ships[$ship_id]['fighters']."</b> - Amr: <b>{$replica_of_ships[$ship_id]['armour']}</b>";

				if($ship['friend_foe'] == 1){
					$friendly_details['fighters_lost'] += $replica_of_ships[$ship_id]['fighters'];
				} else {
					$target_details['fighters_lost'] += $replica_of_ships[$ship_id]['fighters'];
				}
				$players_array[$ship['login_id']]['fighters_lost'] += $replica_of_ships[$ship_id]['fighters'];

				//owner looses some points.
				$players_array[$replica_of_ships[$ship_id]['login_id']]['ship_points_lost'] += $ship['point_value'];
				$players_array[$replica_of_ships[$ship_id]['login_id']]['ships_lost'] ++;

				//someone just lost an EP!
				if($ship['shipclass'] == 2){

					//game is in SD!
					if($GAME_VARS['sudden_death'] > 0){
						$sudden_death_mess_str = $st[282];
						$sd_news_str = $st[283];
					} else {//not in SD.
						$sudden_death_mess_str = "";
						$sd_news_str = "";
					}

					//another player lost their EP.
					if($user['login_id'] != $ship['login_id']){ //send messages
						if($simulate_attack == 0){
							send_message($ship['login_id'],$st[284].$sudden_death_mess_str);
							send_templated_email($ship['login_id'], 'attack');
						}
						$tech_str .= "<p /><b class='b1'>{$players_array[$replica_of_ships[$ship_id]['login_id']]['login_name_link']}</b>'s <b class='b1'>".$cw['escape_pod'].$st[285] ;
					//the user just lost their ep!
					} else {
						$tech_str .= "<p /><b class='b1'>".$cw['important']."!</b> ".$st[286]." <b class='b1'>".$cw['escape_pod']."</b> ".$st[287]."<p />";
					}

					//post to the news
					if($simulate_attack == 0){
						post_news($st[288]."{$players_array[$ship['login_id']]['login_name_link']} ".sprintf($st[289], $combat_loc).$sd_news_str);
					}

					//some peeps might get an EP by some other means. let's make sure they are actually commanding it.
					if($ship_id == $players_array[$ship['login_id']]['ship_id']){
						$players_array[$ship['login_id']]['ship_id'] = -1; //ep destroyed
					}

				//someones Command ship went bang
				} elseif($ship_id == $players_array[$ship['login_id']]['ship_id']){
					$players_array[$ship['login_id']]['ship_id'] = -2; //command ship went bye-bye. But not an EP.
				}


				//scrap for the table.
				$metal_scrap += $ship['metal'];
				$fuel_scrap += $ship['fuel'];


			//ship was not destroyed
			} else {

				//player has a alive replacement ship in the same system, and that is part of the combat.
				if(empty($players_array[$replica_of_ships[$ship_id]['login_id']]['replacement_ship'])){
					$players_array[$replica_of_ships[$ship_id]['login_id']]['replacement_ship'] = $ship_id;
				}

				//make an sql query to update the database with at the end of the process.
				$update_ships_sql[] = "update ${db_name}_ships set fighters = '$ship[fighters]', shields = '$ship[shields]', armour = '$ship[armour]', points_killed = '$ship[points_killed]' where ship_id = '$ship_id'";

				//some quick maths.
				$figs_lost = $replica_of_ships[$ship_id]['fighters'] - $ship['fighters'];
				$shd_lost = $replica_of_ships[$ship_id]['shields'] - $ship['shields'];
				$amr_lost = $replica_of_ships[$ship_id]['armour'] - $ship['armour'];

				$ships_involved_str[$ship['login_id']]['survived'] .= "<br /><b class='b1'>$ship[ship_name]</b> ($ship[class_name_abbr]) - Shds-lost: <b>".$shd_lost."</b> - Figs-lost: <b>".$figs_lost."</b> - Amr-lost: <b>$amr_lost</b>";

				$players_array[$ship['login_id']]['fighters_lost'] += $figs_lost;

				if($ship['friend_foe'] == 1){
					$friendly_details['fighters_lost'] += $figs_lost;
				} else {
					$target_details['fighters_lost'] += $figs_lost;
				}

				$disengage_sql .= "ship_id = '$ship_id' || "; //list of ships to re-instate

				//damage to the users command ship. So we should update the command ship's details.
				if($ship['login_id'] == $user['login_id'] && $ship_id == $user['ship_id']){
					$reload_ship = 1;
				}
			}
		} //end of 'foreach' processing the ships.

		unset($replica_of_ships);


/**********************
* Loop through all planets.
*********************/
		$remaining_planet_figs = $total_planet_figs - $total_planet_dam_taken;
		$copy_planet_figs_lost = $total_planet_dam_taken;
		$planet_group = array('fighters' => $total_planet_figs, 'fighters_ship_count' => count($target_planets));
		$planets_beaten = 0;

		//loop through the planets, and process fighter losses (also, distribute damage between the players).
		foreach($target_planets as $p_id => $p_details){

			$p_dam = resolve_damage_to_do($copy_planet_figs_lost, $p_details, $planet_group, "fighters");
			$copy_planet_figs_lost -= $p_dam;
			$planet_group['fighters_ship_count'] --;
			$planet_group['fighters'] -= $target_planets[$p_id]['fighters'];

			if($planet_attack == 0){
				$msg_txt_1 = $st[291];
				$msg_txt_2 = $st[292];

			} else {
				$msg_txt_1 = $st[293];
				$msg_txt_2 = $st[294];
			}

			//planet lost all it's allocated figs
			if($p_dam >= $p_details['fighters']){
				$players_array[$p_details['login_id']]['fighters_lost'] += $target_planets[$p_id]['fighters'];
				$target_planets[$p_id]['fighters'] = 0;
				$planets_beaten ++;
				$ships_involved_str[$p_details['login_id']]['lost'] .= "<br /><b class='b1'>$p_details[planet_name]</b> $msg_txt_1 <b>".nombre($replica_of_planets[$p_id]['fighters'])."</b>";

			} else {
				$players_array[$p_details['login_id']]['fighters_lost'] += $p_dam;
				$target_planets[$p_id]['fighters'] -= $p_dam;
				$ships_involved_str[$p_details['login_id']]['survived'] .= "<br /><b class='b1'>$p_details[planet_name]</b> $msg_txt_2 <b>".nombre($p_dam)."</b>";
			}
			$target_planets[$p_id]['allocated_to_fleet'] = 0;

		}//end of planets processing foreach loop



		/*************
		* Misc, pre-user processing.
		*************/

		//do some maths to print pretty percentages.
		$friends_lost_perc = calc_perc($friends_killed, $friendly_details['ship_count']);
		$targets_lost_perc = calc_perc($targets_killed, $target_details['ship_count']);
		$friends_alive_perc = calc_perc($friendly_details['ship_count'] - $friends_killed, $friendly_details['ship_count']);
		$targets_alive_perc = calc_perc($target_details['ship_count'] - $targets_killed, $target_details['ship_count']);

		if($planet_attack == 1 || count($target_planets) > 0){
			$temp_planets_1 = "Planets<br />Eliminated";
			$temp_planets_2 = $planets_beaten;
			$temp_planets_3 = "-";
		} else {
			$temp_planets_1 = "";
			$temp_planets_2 = "";
			$temp_planets_3 = "";
		}

		//print a basic results table
		$temp_str = "<p />Results<br />";
		$temp_str .= make_table(array("",$st[295],"".$cw['ships']."<br />Lost",$cw['fighters']."<br />".$cw['killed'], $temp_planets_1));
		$temp_str .= make_row(array($cw['attacker']."(s)", $friends_alive_perc, $friends_lost_perc, $target_details['fighters_lost'], $temp_planets_2));
		$temp_str .= make_row(array($cw['defender']."(s)", $targets_alive_perc, $targets_lost_perc, $friendly_details['fighters_lost'], $temp_planets_3));
		$temp_str .= "</table>";

		$tech_str .= $temp_str;
		$short_str .= $temp_str;
		unset($temp_str, $temp_planets_1, $temp_planets_2, $temp_planets_3);

		if($planets_beaten > 0 && $planet_attack == 1 && $simulate_attack == 0){//show planet land link
			$tech_str .= $st[296]."<p /><font size = 4>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href='planet.php?planet_id=$target'> ".$cw['land_on_the_planet']."</a></font><br /><br />";
		}

		//no ships lost by user.
		if($ships_involved_str[$user['login_id']]['lost'] == ""){
			$ships_involved_str[$user['login_id']]['lost'] = "None";
		}

		//no ships beloning to the user survived
		if($ships_involved_str[$user['login_id']]['survived'] == ""){
			$ships_involved_str[$user['login_id']]['survived'] = "None";
		}


		$targets_involved_ships = "";
		$temp_attack_ship_list = "";
		$temp_defend_ship_list = "";

		/*****************************
		*Loop through the players.
		*****************************/
		foreach($players_array as $player_id => $player){

//			$player['location'] = $combat_loc;
			$lost_ep_update = "";

			//someone lost an EP.
			if($player['ship_id'] == -1){

				//update their details to reflect loss of the EP. Messages/news already sent (in ship loop).
				$player['location'] = 1;
				$player['ship_id'] = 1;
				$lost_ep_update = ", explored_sys = '1'";

			//someone's command ship went bang.
			} elseif($player['ship_id'] == -2){

				$quick_replace_txt = $st[297];

				//a replacement ship is somewhere in the collection of combat ships.
				if(!empty($player['replacement_ship'])){
					$player['ship_id'] = $player['replacement_ship'];
					$quick_replace_txt .= $st[298];

				//no replacement that is in the system, but not involved in combat. Search the rest of the system.
				} else {
					db("select ship_id from ${db_name}_ships where location = '$combat_loc' && ship_engaged < '".time()."' && login_id = '$player[login_id]' order by fighters desc LIMIT 1");
					$transfer_command = dbr(1);

					//player does have a ship in the system. We'll use it for the new command ship.
					if(!empty($transfer_command['ship_id'])){
						$player['ship_id'] = $transfer_command['ship_id'];
						$quick_replace_txt .= $st[298];

					//no replacement command ship in the system. Let's try elsewhere
					} else {
						unset($transfer_command);
						db("select ship_id, location from ${db_name}_ships where location != '$combat_loc' && login_id = '$player[login_id]' order by fighters desc LIMIT 1");
						$transfer_command = dbr(1);

						//new command ship found. let's pop over there.
						if(!empty($transfer_command['ship_id'])){
							$player['ship_id'] = $transfer_command['ship_id'];
							$player['location'] = $transfer_command['location'];
							$quick_replace_txt .= $st[299]." //<b>$player[location]</b>.";
							unset($transfer_command);

						//no other ships at all! Let's pop the EP out into the universe.
						} elseif($simulate_attack == 0) {

							//create the EP
							$player = create_escape_pod($player);

							$quick_replace_txt = sprintf($st[1822], $player[location]);

							post_news(sprintf($st[1823], $player[login_name_link]), "ship, attacking, player_status");
						}
					}
				}

				//Lost a ship. time to inform someone.
				if(isset($quick_replace_txt)){

					//user lost their ship
					if($user['login_id'] == $player['login_id']){
						$tech_str .= $quick_replace_txt;
						$user['ship_id'] = $player['ship_id'];
						$reload_ship = 1;

					//another player
					} elseif($simulate_attack == 0){
						send_message($player_id,$quick_replace_txt);
						send_templated_email($player_id, 'attack');
					}
					unset($quick_replace_txt);
				}
			}

			if($player['friend_foe'] == 1){ //an attacker
				$temp_u_figs = $friendly_details['fighter_count'];
				$temp_figs_killed = $target_details['fighters_lost'];
				$temp_ships_killed = $targets_killed;
				$last_attack = $foes_name_str;

			} else { //a defender
				$temp_u_figs = $target_details['fighter_count'] + $total_planet_figs;
				$temp_figs_killed = $friendly_details['fighters_lost'];
				$temp_ships_killed = $friends_killed;
				$last_attack = $friends_name_str;
			}

			$temp_defend_ship_list = "";
			//work out the percentage of kills and stuff that this user has made.
			if($player['total_fighters'] > 0 && $temp_u_figs > 0) {
				$player_perc = ($player['total_fighters'] / $temp_u_figs) * 100;
			} else {
				$player_perc = 0;
			}

			//if a player has made some fig kills, they are entitled to some of the ship/fig kills and fig losses.
			if($player_perc > 0.5){
				if($temp_figs_killed > 0){
					$player['fighters_killed'] += round($player_perc * ($temp_figs_killed / 100));
				}
				if($temp_ships_killed > 0){
					$player['ships_killed'] += round($player_perc * ($temp_ships_killed / 100));
				}
			}


			//don't want to be doing certain stuff to the user. (specifically messages).
			if($player_id != $user['login_id']){

				//no ships lost by this user.
				if($ships_involved_str[$player_id]['lost'] == ""){
					$ships_involved_str[$player_id]['lost'] = "None";
				}

				//no ships belonging to this user survived
				if($ships_involved_str[$player_id]['survived'] == ""){
					$ships_involved_str[$player_id]['survived'] = "None";
				}

				//a attacking ship.
				if($player['clan_id'] == $user['clan_id'] && $user['clan_id'] > 0) {

					//add the attacking ships to the ship list.
					$temp_attack_ship_list .= "<p /><hr width=20%><p />".make_table(array("<center>$player[login_name_link]</center>","Lost: $player[ships_lost], Survived: ".substr_count($ships_involved_str[$player_id]['survived'],"<br />")));

					$temp_attack_ship_list .= "<tr><td bgcolor=#221111>Destroyed</td><td bgcolor=#221111>".$ships_involved_str[$player_id]['lost']."</td></tr>";

					$temp_attack_ship_list .= "<tr><td bgcolor=#112211>Survived</td><td bgcolor=#112211>".$ships_involved_str[$player_id]['survived']."</td></tr>";
					$temp_attack_ship_list .= "</table>";

				//a defending ship
				} else {

					//add the defending ships to a variable to be put into the ship list later.
					$temp_defend_ship_list .= "<p /><hr width=20%><p />".make_table(array("<center>$player[login_name_link]</center>","Lost: $player[ships_lost], Survived: ".substr_count($ships_involved_str[$player_id]['survived'],"<br />")));
					$temp_defend_ship_list .= "<tr><td bgcolor=#221111>Destroyed</td><td bgcolor=#221111>".$ships_involved_str[$player_id]['lost']."</td></tr>";
					$temp_defend_ship_list .= "<tr><td bgcolor=#112211>Survived</td><td bgcolor=#112211>".$ships_involved_str[$player_id]['survived']."</td></tr>";
					$temp_defend_ship_list .= "</table>";

				} //end of 'if' for attacking or defening ship.

				//send the message to looping player.
				if($simulate_attack == 0){
					send_message($player_id, $short_str);
					send_templated_email($player_id, 'attack');
				}

				//prepare to print the list to the user.
				$targets_involved_ships .= $temp_defend_ship_list;


			}//end of not-the-player 'if'

			//make an sql query to update the database with at the end of the process.
			//this will update all user aspects related combat (including new ship id's and locations if any!).
			$update_users_sql[] = "update ${db_name}_users set ships_killed_points = ships_killed_points + '$player[ship_points_killed]', ships_lost_points = ships_lost_points + '$player[ship_points_lost]', ships_killed = ships_killed + '$player[ships_killed]', ships_lost = ships_lost + '$player[ships_lost]', fighters_killed = fighters_killed + '$player[fighters_killed]', fighters_lost = fighters_lost + '$player[fighters_lost]', last_attack_by = '$last_attack', last_attack = '".time()."', location = '$player[location]', ship_id = '$player[ship_id]' ".$lost_ep_update." where login_id = '$player_id'";

			$players_array[$player_id] = $player;
		} //end of 'foreach' processing players.

		//start of table listing to the player all the ships involved in the combat.
		$tech_str .= $st[1825];
		$tech_str .= make_table(array("<center>".$st[1826]."</center>","Lost: {$players_array[$user['login_id']]['ships_lost']}", $cw['Survived'].":".substr_count($ships_involved_str[$user['login_id']]['survived'],"<br />")));
		$tech_str .= "<tr><td bgcolor=#221111>".$cw['Destroyed']."</td><td bgcolor=#221111>".$ships_involved_str[$user['login_id']]['lost']."</td></tr>";
		$tech_str .= "<tr><td bgcolor=#112211>".$cw['Survived']."</td><td bgcolor=#112211>".$ships_involved_str[$user['login_id']]['survived']."</td></tr>";
		$tech_str .= "</table>";



		$tech_str .= $temp_attack_ship_list.$targets_involved_ships;
		unset($temp_attack_ship_list, $targets_involved_ships);

					/*******************************************************
					*		End of the loops. Let's finish this!
					********************************************************/



		/****************
		* post to the news.
		****************/
		$total_ships_killed = $friends_killed + $targets_killed;
		if($simulate_attack == 0){
			$same_txt = sprintf($st[306], $friends_name_str, $friendly_details['ship_count'], $target_details['ship_count'], $foes_name_str).'<br />'.sprintf($st[308], $friends_killed, $targets_killed);
			if($total_ships_killed < 1 && $planet_attack == 0){
				if(mt_rand(0,1) == 1){ //choose between funny comments.
					post_news($st[305]." <b>{$combat_loc}</b>. $friends_name_str ".$st[300]."{$friendly_details['ship_count']} ".$st[301]." {$target_details['ship_count']} ".$st[302]." {$foes_name_str}.<br />".$st[303], "ship, attacking");
				} else {
					post_news($st[305]." <b>{$combat_loc}</b>. $friends_name_str ".$st[300]." {$friendly_details['ship_count']} ".$st[301]."{$target_details['ship_count']} ".$st[302]." {$foes_name_str}.<br />".$st[309], "ship, attacking");
				}
			} elseif($total_ships_killed < 100 && $planet_attack == 0){ //moderate casualties
					post_news($st[310]." <b>{$combat_loc}</b>.<br />".$same_txt, $st[304]);

			} elseif($total_ships_killed >= 100 && $planet_attack == 0){ //major casualties!
				if(mt_rand(0,1) == 1){ //choose between funny comments.
					post_news($st[311]." <b>{$combat_loc}</b>. $same_txt.<p />".$st[312], $st[304]);
				} else {
					post_news($st[313]." <b>{$combat_loc}</b>. $same_txt.<p />".$st[314]);
				}
			} elseif($planet_attack == 1){
				if($planets_beaten > 0){
					$conq_txt = sprintf($st[315], $friends_killed);
				} else {
					$conq_txt = sprintf($st[316], $friends_killed);
				}
				post_news("<b class='b1'>$user[login_name]</b> ".$st[317]." <b class='b1'>{$target_planets[$target]['planet_name']}</b> ".$st[318]." <b>$combat_loc</b>.<br />".$st[319]." <b>{$friendly_details['ship_count']}</b> ".$cw['ships'].".<br />".$conq_txt, $st[320]);
				unset($conq_txt);
			} //end of news posting spree.
		}

			/**********************
			*Finish the Script
			**********************/


		//Scatter any minerals from the destroyed ships into the system. But only if not in system 1.
		if($combat_loc != 1 && $simulate_attack == 0){
			if($metal_scrap > 0){ //scrap from cargo bays
				$metal_scrap = ceil($metal_scrap / mt_rand (1,5));
			}
			if($fuel_scrap > 0){ //fuel from cargo bays
				$fuel_scrap = ceil($fuel_scrap / mt_rand (1,5));
			}
			if($friendly_details['fighters_lost'] > 0){ //fighter parts.
				$metal_scrap += ceil($friendly_details['fighters_lost'] / mt_rand (50,100));
				$fuel_scrap += ceil($friendly_details['fighters_lost'] / mt_rand (50,100));
			}
			if($target_details['fighters_lost'] > 0){ //fighter parts.
				$metal_scrap += ceil($target_details['fighters_lost'] / mt_rand (50,100));
				$fuel_scrap += ceil($target_details['fighters_lost'] / mt_rand (50,100));
			}
			$metal_scrap < 0 ? $metal_scrap = 0 : 1;
			$fuel_scrap < 0 ? $fuel_scrap = 0 : 1;

			if($metal_scrap > 0 || $fuel_scrap > 0){
				dbn("update ${db_name}_stars set metal = metal + '$metal_scrap', fuel = fuel + '$fuel_scrap' where star_id = '$combat_loc'");
			}
		}

		/************************
		* Database Updating.
		************************/

		//we wouldn't want to run out of time during DB updating.
		set_time_limit(10);

		//we only want to update the DB if not Simulating combat.
		if($simulate_attack == 0){

			if (count($all_ships) >= 10)
			{//do facebook
				foreach ($players_array as $fb_player)
				{
					db("select login_name, fb_user_id, fb_token from user_accounts where login_id='".$fb_player['login_id']."'");
					$data = dbr();
					if ($data['fb_user_id'] && $data['fb_token'])
					{// user has facebook...do wall post
						if ($fb_player['login_id'] == $user['login_id'])
						{// current user ... so he is friend
							if ($friends_killed < $friendly_details['ship_count'])
							// he won
								fb_wallpost_wosdk_api('Astra Vires', ucfirst($data['login_name'])." a remporté une grosse bataille dans l'univers d'Astra Vires", NULL, $data['fb_token']);
/*							else
							// he lost
								fb_wallpost_wosdk_api('Astra Vires', ucfirst($data['login_name']).' took part in a big battle on Astra Vires and lost', 'Astra Vires is a game that ... ', $data['fb_token']);
*/
						}
						else
						{// other user ... so he is target
							if ($friends_killed >= $friendly_details['ship_count'])
/*							// he lost
								fb_wallpost_wosdk_api('Astra Vires', ucfirst($data['login_name']).' took part in a big battle on Astra Vires and lost', 'Astra Vires is a game that ... ', $data['fb_token']);
							else
*/
							// he won
								fb_wallpost_wosdk_api('Astra Vires', ucfirst($data['login_name'])." a remporté une grosse bataille dans l'univers d'Astra Vires", NULL, $data['fb_token']);
						}
					}
				}
//				exit();
//				fb_wallpost_wosdk_api('Astra Vires', '{*actor*} just joined Astra Vires!', 'Astra Vires is a game that ... ');
			}
			if (!empty($target_planets) ){
				if($planets_beaten > 0 && $planet_attack == 1 && $simulate_attack == 0)
				{//player win
					db("select login_name, fb_user_id, fb_token from user_accounts where login_id='".$user['login_id']."'");
					$data = dbr();
					if ($data['fb_user_id'] && $data['fb_token'])
					{// user has facebook...do wall post
						fb_wallpost_wosdk_api('Astra Vires', ucfirst($data['login_name'])." a écrasé la défense d'une planète dans l'univers d'Astra Vires", NULL, $data['fb_token']);
					}
				}
				else
				{//player lost and planet won
					$posted_fb_ids = array();
					foreach($target_planets as $p_id => $p_details){
						db("select login_id from ${db_name}_planets where planet_id='$p_id'");
						$fb_planet_info = dbr();
						db("select login_name, fb_user_id, fb_token from user_accounts where login_id='".$fb_planet_info['login_id']."'");
						$data = dbr();
						if ($data['fb_user_id'] && $data['fb_token'] && !in_array($data['fb_user_id'], $posted_fb_ids))
							{// user has facebook...do wall post
								fb_wallpost_wosdk_api('Astra Vires', ucfirst($data['login_name'])." a vaillamment repoussé l'attaque contre une de ses planètes dans l'univers d'Astra Vires", NULL, $data['fb_token']);
								$posted_fb_ids[] = $data['fb_user_id'];
							}
					}
				}

//				echo 'aici';
//				exit();
			}

			//ensure that we have some ships to update.
			if(isset($update_ships_sql[0])){
				//update the ships
				foreach($update_ships_sql as $sql){
					dbn($sql);
				}
			}

			//update the users
			foreach($update_users_sql as $sql){
				dbn($sql);
			}

			if($reload_ship == 1){
				get_user_ship($user['ship_id']);
			}

			//update planet records
			if(!empty($target_planets)){
				foreach($target_planets as $p_id => $p_details){
					if($planet_attack == 0){ //assisting planets
						dbn("update ${db_name}_planets set planet_engaged = 0, fighters = (fighters - allocated_to_fleet) + '$p_details[fighters]', allocated_to_fleet = '$p_details[fighters]' where planet_id = '$p_id'");
					} else { //target planet
						if($ships_in_system == 1){//damage is done only to unallocated fighters
							dbn("update ${db_name}_planets set planet_engaged = 0, fighters = allocated_to_fleet + '$p_details[fighters]' where planet_id = '$p_id'");
						} else { //fighter damage is done to all fighters
							dbn("update ${db_name}_planets set planet_engaged = 0, fighters = '$p_details[fighters]' where planet_id = '$p_id'");
							dbn("update ${db_name}_planets set allocated_to_fleet = fighters where allocated_to_fleet > fighters && planet_id = '$p_id'");
						}
					}
				}
			}

			//Delete any destroyed ships.
			if(!empty($delete_sql)){
				$delete_sql = preg_replace("/\|\| $/", "", $delete_sql);
				dbn("delete from ${db_name}_ships where ".$delete_sql);
			}

			/***********
			* Quick loop through players
			***********/
			foreach($players_array as $player_id => $player){

				//think the player has lost EP, so wipe them out.
				if($player['ship_id'] == 1 && $player['location'] == 1 && $player['login_id'] > 4){
					wipe_player($player_id, $player['clan_id']);
				}
			}

			//ships are now free to do as they will
			if(!empty($disengage_sql)){
				$disengage_sql = preg_replace("/\|\| $/", "", $disengage_sql);
				dbn("update ${db_name}_ships set ship_engaged = 0 where ".$disengage_sql);
			}
			//planets are now free to do as they will as well
			if(!empty($disengage_planets_sql)){
				$disengage_planets_sql = preg_replace("/\|\| $/", "", $disengage_planets_sql);
				dbn("update ${db_name}_planets set planet_engaged = 0 where ".$disengage_planets_sql);
			}

			charge_turns($total_attack_turn_cost);

		} else { //even simulations take turns to run!
			charge_turns($simulate_attack_turn_cost);
		}

/***************************
* Now leaving the attack system code.
****************************/
	} //end of fleet attacking 'if' - inner

} //end of fleet attacking 'if' - outer


//print the page and be done with it.
print_page("Fleet Attacking",$tech_str);
exit();



/*****************************************************************
*								Functions
******************************************************************/


//friends - function to set value of an array to 1 (friend) in rediness to send to the attack processor
function set_friend($a){
	return 1;
}

//foes - function to set value of an array to 2 (foe) in rediness to send to the attack processor
function set_foe($a){
	return 2;
}

//function to set stuff to 0.
function leveller($input){
	if($input < 0){
		return (int)0;
	} else{
		return (int)$input;
	}
}

//function that will find the average of a number.
function average_finder($result,$total){
	if($total == 0 || $result == 0){
		return (int) 0;
	}

	$result = floor($result / $total);
	if($result < 1) {
		return (int)1;
	} else {
		return (int)$result;
	}
}

//function that works out how many ships have a certain upgrade, and then works out the percent benefit.
function inc_dam($stat,$ship,$num){

	$num_item = substr_count($ship['config'],$stat);
	if($num_item < 1){
		return (int)0;
	} else {
		return (int)floor($num * ($num_item / $ship['ship_count']));
	}
}


//function that will return the amount of damage to be done to that ship, based on the percent of that commodity that the ship has (i.e. 20% group fighters will result in getting 20% of the fighter damage (ish)).
function resolve_damage_to_do($dam_to_split, $ship_details, $ship_group, $element_key){

	//ship cannot take any of this damage type, or there is no damage. Return nothing.
	if($ship_details[$element_key] < 1 || $dam_to_split < 1 || $ship_group[$element_key] < 1){
		return (int)0;

	//ship is the only one that can take this kind of damage, so we assign all this damage to the ship.
	} elseif($ship_details[$element_key] >= $ship_group[$element_key] || $ship_group[$element_key."_ship_count"] < 2) {
		return (int)$dam_to_split;

	//ship can take this damage type. And there is damage to take.
	} else {
		$ship_details[$element_key]."xx";
		$damage_as_percent = ($dam_to_split / 100);
		$perc_damage_per_ship_theory = 100 / $ship_group[$element_key."_ship_count"];
		$perc_of_ship_element = ($ship_details[$element_key] / $ship_group[$element_key]) * 100;
		$possible_result = round($damage_as_percent * $perc_of_ship_element);


		//the damage being done is greater than the ship can take. So simply return the lower number (ship-capacity)
		if($possible_result > $ship_details[$element_key]){
			return (int)$ship_details[$element_key];
		}

		//let's randomise the damage a bit. Damage could go up or down by as much as 25%
		$possible_result += mt_rand(round(-$possible_result * 0.25),round($possible_result * 0.25));


		//work out what percentage of this item that the ship needs in order to survive (i.e. ship has 2% of fleets armour, it'll probably be killed). Otherwise, we just kill it.
		$ship_needed_perc = $perc_damage_per_ship_theory + mt_rand(round(-$perc_damage_per_ship_theory * 0.65),round($perc_damage_per_ship_theory * 0.65));

		/*
		//ensure we don't use more damage than we should. Again. :)
		//OR lets make sure that we don't end up wasting damage. i.e.: 500 figs on 2 ships 250 each), damage_done = 490. We don't want to do 230 to one ship, 260 for the other ship (cos the other ship only needs 250). We do know that there shouldn't be enough to kill all ships, or this function wouldn't be processed.
		//OR we can blow it up on a random basis.
		//OR we can blow it up if it has less than a certain percent of the possible total and there are more than a few ships.
		//and we should be sure only to blow it up if we won't be giving it more damage than it would otherwise be possible to take.*/
		if($possible_result >= $ship_details[$element_key] || (((($dam_to_split - $possible_result) > ($ship_group[$element_key] - $ship_details[$element_key])) || mt_rand(1,15) == 1 || $perc_of_ship_element < $ship_needed_perc) && $ship_details[$element_key] <= $dam_to_split)){
			return (int)$ship_details[$element_key];

		//it should be safe to return the result now.
		} else {
			return (int)$possible_result;
		}
		//just to make sure doesn't all go wrong.
		return (int)0;
	}
}


//function that will take details about a ship, and will inflict damage upon it, using the 'resolve_damage_to_do' function to work out how much damage to do.
//only used for updrade damage.
//All entries thare are returned are referenced.
function damage_ship_predefined (&$initial_damage, &$present_ship, &$ship_group, $element_key, &$side_details){

	//there are some of the item on some ships, and we can hurt it...
	if($initial_damage > 0 && $present_ship[$element_key] > 0) {
		$upgrade_kills = resolve_damage_to_do($initial_damage, $present_ship, $ship_group, $element_key);

		//damage took out the ships element.
		if($upgrade_kills >= $present_ship[$element_key] && $upgrade_kills > 0){
			$ship_group[$element_key] -= $present_ship[$element_key];
			$ship_group['approx_damage'] -= $present_ship[$element_key];
			$present_ship['approx_damage'] -= $present_ship[$element_key];

			$initial_damage -= $present_ship[$element_key];
			$present_ship[$element_key] = 0;
			$ship_group[$element_key] -= $present_ship[$element_key];

			//kill off the ship if everything is dead
			if($present_ship['armour'] < 1 && (($element_key == "fighters") || ($element_key == "shields" && $present_ship['fighters'] < 1))){
				//the ship will no longer be able to participate in the battle.
				$ship_group['fighters_ship_count'] --;
				$ship_group['shields_ship_count'] --;
				$ship_group['armour_ship_count'] --;
				$ship_group['approx_damage_ship_count'] --;
				$present_ship['ship_destroyed'] = 1;
				return (int)1;
			}

			$ship_group[$element_key."_ship_count"] --;

		//damage did not get through completely.
		} elseif($upgrade_kills < $present_ship[$element_key] && $upgrade_kills > 0){

			if($element_key == "armour"){
				dist_rand_damage($upgrade_kills, $present_ship);
			}

			$initial_damage -= $upgrade_kills;
			$present_ship[$element_key] -= $upgrade_kills;
			$ship_group[$element_key] -= $upgrade_kills;
			$ship_group['approx_damage'] -= $upgrade_kills;
			$present_ship['approx_damage'] -= $upgrade_kills;

			$ship_group[$element_key."_ship_count"] --;

		}
		return (int)0;
	}
}


//function that actually damages a non-destroyed ship.
//Ships have taken armour damage to get to this function.
function dist_rand_damage ($arm_dam_recieved, &$ship_details){
	global $simulate_attack;

	if($simulate_attack == 1){//don't bother running the function if simulating attack.
		return 0;
	}

	//ensure no zeros
	if($arm_dam_recieved > 0 && $ship_details['max_armour'] > 0){
		$perc_arm_lost = round(($arm_dam_recieved / $ship_details['max_armour']) * 100);

		//ensure more than 20% of armour has been destroyed. Otherwise, no damage will be done.
		if($perc_arm_lost > 5){
			if(mt_rand(0,10) > 6){//40%chance of damaging fig cap
				//damage done to fig capacity is 40% of armour loss percent.
				$ship_details['max_fighters'] -= round(($ship_details['max_fighters'] / 100) * ($perc_arm_lost * .4));
				if($ship_details['fighters'] > $ship_details['max_fighters']){//ensure not got too many figs
					$ship_details['fighters'] = $ship_details['max_fighters'];
				}
			}
			if(mt_rand(0,10) > 4){//60%chance of damaging shield cap
				//shield capacity goes down by 53% of armour loss.
				$ship_details['max_shields'] -= round(($ship_details['max_shields'] / 100) * ($perc_arm_lost * .53));
				if($ship_details['shields'] > $ship_details['max_shields']){//ensure not got too many shields
					$ship_details['shields'] = $ship_details['max_shields'];
				}
			}
		}
	}
}


//function that will add a user to the list of users involved in combat (if the user isn't in it already).
function add_user_to_users_array(&$players_array, &$ships_involved_str, &$name_str, $data_array, $friend_or_foe){

	//player already in the array, so skip adding again.
	if(array_key_exists($data_array['login_id'], $players_array)){
		return (int)0;
	}

	$players_array[$data_array['login_id']] = array('login_id' => $data_array['login_id'], 'clan_id' => $data_array['clan_id'], 'login_name' => $data_array['login_name'], 'location' => $data_array['location'], 'login_name_link' => print_name($data_array), 'player_ship_count' => 0, 'total_fighters' => 0, 'ship_points_killed' =>0, 'ship_points_lost' =>0, 'friend_foe' => $friend_or_foe, 'fighters_lost' => 0, 'fighters_killed' => 0, 'ships_lost' => 0, 'ships_killed' => 0, 'ship_id' => $data_array['ship_id'], 'replacement_ship' => "");

	//thinking a long way ahead. :)
	$ships_involved_str[$data_array['login_id']] = array('lost'=>"", 'survived'=>"");

	!empty($name_str) ? $name_str .= "," : 1;
	$name_str .= " ".$players_array[$data_array['login_id']]['login_name_link'];
}


//function that will sum up all values of the same element key in different elements within a nested array.
function sum_array_values ($array_to_use, $element_key){
	$result = 0;
	foreach($array_to_use as $second_level_array){
		if(!empty($second_level_array[$element_key])){
			$result += $second_level_array[$element_key];
		}
	}
	return (int)$result;
}

?>
