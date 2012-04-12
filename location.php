<?php

require_once("user.inc.php");

get_star();

//dump the stars links into an array (called $star_links);
function archive_links(){
	global $star_links, $star;
	$star_links = array();
	for ($i = 1; $i <= 6; ++$i) {
		$to = $star['link_' . $i];
		if ($to != 0) {
			$star_links[] = $to;
		}
	}
}


function print_link($link_num) {
	global $error_str, $am_alive;
	if($link_num && $am_alive) {
		$error_str .= "\n<a href='location.php?toloc=$link_num'><span class='liens_prl'>[$link_num]</span></a>&nbsp;&nbsp;";
	} elseif($link_num){ //unclickable if dead.
		$error_str .= "\n&lt;$link_num&gt; ";
	}
}

function search_links($star,$link_num) {
	if($star['link_1'] == $link_num) {
		return 0;
	} elseif($star['link_2'] == $link_num) {
		return 0;
	} elseif($star['link_3'] == $link_num) {
		return 0;
	} elseif($star['link_4'] == $link_num) {
		return 0;
	} elseif($star['link_5'] == $link_num) {
		return 0;
	} elseif($star['link_6'] == $link_num) {
		return 0;
	} elseif($star['wormhole'] == $link_num) {
		return 0;
	}
	return 1;
}


#determine autowarp route.
//works from the destination, to the present location, following links.
function determine_route($dest_sector){
	global $user, $cw, $st, $db_name, $autowarp, $num_aw_left, $exp_sys_arr, $GAME_VARS;

	//set starting location
	$start_sector = $user['location'];

	//declare systems array
	//will contain distance travelled ('dist'), and source of link to this system (source)
	$systems = array();

	//declare search_queue, and add the destination sector to it.
	$search_queue = array();
	array_unshift($search_queue, $start_sector);

	#loop through the systems.
	while($search_sector = array_pop($search_queue)) {

		//skip systems that are unexplored
		if($GAME_VARS['uv_explored'] == 0 && $exp_sys_arr[0] != -1 && array_search($search_sector, $exp_sys_arr) === false){
			continue 1;
		}

		//get all systems that link to present system
		db("SELECT wormhole, link_1, link_2, link_3, link_4, link_5, link_6 FROM ${db_name}_stars WHERE star_id = '$search_sector'");
		$adj_sectors = dbr(1);

		#loop through all links to system to present system
		foreach($adj_sectors as $key => $vertex) {

			#have reached the destination sector
			if($vertex == $dest_sector) {
				$ret_str = "";
				$path = "";

				//jump from system to system from the start sector to the end sector.
				for($linkback = $search_sector; $linkback != $start_sector;) {
					$ret_str = "<b>$linkback</b> - ".$ret_str;

					if(!empty($systems[$linkback]['worm'])) {//a mid jump by wormhole
						$ret_str = $cw['wormhole_to'].' '.$ret_str;

					} elseif($key == "wormhole") { //final jump is wormhole one
						$ret_str .= $cw['wormhole_to'].' ';
					}

					$path = "$linkback ".$path;
					$linkback = $systems[$linkback]['source'];
				}
				$ret_str = $cw['distance_is']." <b>".(substr_count($ret_str, "-") + 1)."</b> ".$cw['warps']."<br />".$cw['path_is']." <b>$start_sector</b> - ".$ret_str."<b>$dest_sector</b><br /><br />";
				$path .= "$dest_sector";
				$autowarp = $path;
				return $ret_str;
			}

			//selected linked system has not been visted before, so add as potential destintation.
			if($vertex > 0 && empty($systems[$vertex])) {

				$systems[$vertex]['source'] = $search_sector; //set where came from.

				if($key == "wormhole"){ //a wormhole jump
					$systems[$vertex]['worm'] = 1;
				}

				//add link destination to list of systems to search.
				array_unshift($search_queue, $vertex);
			}
		}
	}
	return sprintf($st[0], $dest_sector);
}

/************************ Page Start Processing ***********************/
$header = $cw['star_system'];
$auto_str = "";
$error_str = "";
$user_loc_message = "";


//check to see how alive the player is
//used to establish if the player can mover around or not.
$am_alive = ship_status_checker(1);


if(isset($calc_autowarp)){
	$exp_sys_arr = explode(",", $user['explored_sys']);
	if(!isset($dest_sector)) {
		get_var("AutoWarp", "location.php", $st[1], "dest_sector", "", 4);
	} elseif(($dest_sector < 1 || $dest_sector > $game_info['num_stars'] || $user['location'] == $dest_sector)){
		$user_loc_message .= $st[2];
	} elseif($GAME_VARS['uv_explored'] == 0 && $user['explored_sys'] != -1 && array_search($dest_sector, $exp_sys_arr) === false){
		$user_loc_message .= $st[3];
	} else {
		$user_loc_message .= determine_route($dest_sector);
	}
}

// Check if on_planet
if($user['on_planet'] != 0) {
	dbn("update ${db_name}_users set on_planet = 0 where login_id = $user[login_id]");
	$user['on_planet'] = 0;
}

#change a ship's fleet
if(isset($fleet_type)){
	if($join_fleet_id_2 != ''){
		$join_fleet_id = $join_fleet_id_2;
	}

	if(isset($do_ship_type)) { #selected by ship type
		$other = "shipclass";
	} else { #selected by ship
		$other = "ship_id";
		$do_ship_type = $do_ship;
	}

	$user_loc_message = change_fleet_num($join_fleet_id,$fleet_type,$do_ship_type,$other);

}


// command a different ship
if(!empty($command)) {
	if($command==0 || $command==1){
		print_page($cw['error'],$st[4]);
	}
	db("select * from ${db_name}_ships where ship_id = $command");
	$temp_ship = dbr(1);
	if($temp_ship['login_id'] == $user['login_id']) {
		if($temp_ship['location'] != $user['location']) {
			$dist = get_star_dist($user['location'],$temp_ship['location']);
			if($dist < 12) {
				if ($dist > $user['turns']) {
					print_page($cw['command_failed'], sprintf($st[5], $dist, $user[turns]));
				} else {
					$header = $cw['remote_command'];
					$user_loc_message .= $cw['command_transfered'].".<p />";
				}
			} else {
				$dist = $dist -10;
				if ($dist > $user['turns']) {
					print_page($cw['command_failed'], sprintf($st[5], $dist, $user[turns]));
				} else {
					$header = $cw['remote_command'];
					charge_turns($dist);
					$user_loc_message .= sprintf($st[6], $dist);
				}
			}
		} else {
			$user_loc_message .= $cw['command_transfered'].".<p />";
		}
		explore_sys($user, $temp_ship['location']);
		dbn("update ${db_name}_users set ship_id = '$command' where login_id = '$user[login_id]'");
		$user['ship_id'] = $command;
		$user['location'] = $temp_ship['location'];
		get_star();

		get_user_ship($command);
	}
}

//subspace jump
if(!empty($subspace)) {
	db("select count(ship_id) from ${db_name}_ships where fleet_id = '$user_ship[fleet_id]' && location = '$user_ship[location]' && ship_id != '$user[ship_id]' && login_id = '$user[login_id]'");
	$num_towed1 = dbr();
	$num_towed = $num_towed1[0];

	db("select count(star_id) from ${db_name}_stars");
	$num_ss = dbr();
	$turn = round(get_star_dist($user['location'],$subspace)/2 +1);
	$exp_sys_arr = explode(",", $user['explored_sys']);
	if(!config_check("sj",$user_ship)) {
		$user_loc_message .= $st[8];
	} elseif($subspace == $user['location']) {
		$user_loc_message .= $st[9];
	} elseif($user['turns'] < $turn) {
		$user_loc_message .= sprintf($st[10], $turn);
	} elseif($num_towed > 10 && !config_check("ws",$user_ship)) {
		$user_loc_message .= sprintf($st[11], $num_towed);
	} elseif($subspace > $num_ss[0] || $subspace <= 0) {
		$user_loc_message .= sprintf($st[12], $num_ss[0]);
	} elseif($GAME_VARS['uv_explored'] == 0 && $user['explored_sys'] != -1 && $user['login_id'] != 1 && array_search($subspace, $exp_sys_arr) === false) {
		$user_loc_message .= $st[13];
	} else {
		explore_sys($user, $subspace);
		$user_ship['mine_mode'] = 0;

		dbn("update ${db_name}_ships set location = '$subspace', mine_mode = 0 where fleet_id = '$user_ship[fleet_id]' and location = '$user_ship[location]' && login_id = '$user[login_id]'");

		charge_turns($turn);

		$user['location'] = $subspace;
		$user_ship['location'] = $subspace;
		get_star();

		$user_loc_message .= sprintf($st[14], $subspace, $turn);

		//random event stuff
		if ($star['event_random'] > 0 && $user['login_id'] != 1) {
			require_once("$directories[includes]/random_event_funcs.php");
			random_event_checker($star,$user,$autowarp);
		}
	}
}

// Process page location command if given
if(!empty($toloc)) {
	// checks
	if($GAME_VARS['ship_warp_cost'] < 0){ #warp cost is determined by largest ship in fleet.
		if($user['ship_id'] == 1){ //ship destroyed warp cost in turns.
			$warp_cost = 1;
		} else {
			db("select move_turn_cost from ${db_name}_ships where login_id = '$user[login_id]' && location = '$user[location]' && fleet_id = '$user_ship[fleet_id]' order by move_turn_cost desc limit 1");
			$move_turn_cost_fleet = dbr();
			$warp_cost = $move_turn_cost_fleet['move_turn_cost']; #set it to warp_cost so can keep generic
		}
	} else {#warp cost is set by admin
		$warp_cost = $GAME_VARS['ship_warp_cost']; #set to warp_cost so as to keep generic
	}

	if($user['turns'] < $GAME_VARS['ship_warp_cost'] && $GAME_VARS['ship_warp_cost'] > 0 && $user['login_id'] > 1) {
		$user_loc_message = sprintf($st[15], $GAME_VARS[ship_warp_cost]);
	} elseif($GAME_VARS['ship_warp_cost'] < 0 && $user['turns'] < $warp_cost && $user['login_id'] > 1) {
		$user_loc_message = sprintf($st[16], $warp_cost);

	//player isn't alive, and thus cannot move around.
	} elseif(!$am_alive){
		$user_loc_message = $st[17];

	} else {

		if($toloc < 1) {
			$user_loc_message = $st[18];
		} elseif($toloc == $user['location']) {
			$user_loc_message = $st[19];
		} elseif(search_links($star,$toloc) && $user['location'] > 0 && $user['location'] <= $game_info['num_stars']) {
			$user_loc_message = sprintf($st[20], $toloc);
		} else {

			explore_sys($user, $toloc);
			charge_turns($warp_cost);
			dbn("update ${db_name}_ships set location = '$toloc', mine_mode = '0' where ship_id = '$user[ship_id]'");
			$user_ship['mine_mode'] = 0;
			$user_ship['location'] = $toloc;

			#simpler & quicker version of ramscooping.
			#ramscooping using the mammoth ramjet
			$temp056 = mt_rand(1,3);

			dbn("update ${db_name}_ships set fuel = fuel + '$temp056' where shipclass = 301 && ((ship_id = '$user_ship[ship_id]' && '$temp056' + '$user_ship[empty_bays]' > 0) || (fleet_id = '$user_ship[fleet_id]' && location = '$user[location]' && login_id = '$user[login_id]' && (cargo_bays - metal-fuel-elect-colon > $temp056)))");
			if($user_ship['shipclass'] == 301 && ($temp056 + $user_ship['empty_bays'] > 0)){
				$user_ship['fuel'] += $temp056;
				$user_ship['empty_bays'] -= $temp056;
			}

			#ramscooping using the asteroid processor.
			$temp056 = mt_rand(1,3);
			dbn("update ${db_name}_ships set metal = metal + '$temp056' where shipclass = 302 && ((ship_id = '$user_ship[ship_id]' && '$temp056' + '$user_ship[empty_bays]' > 0) || (fleet_id = '$user_ship[fleet_id]' && location = '$user[location]' && login_id = '$user[login_id]' && (cargo_bays - metal-fuel-elect-colon) > $temp056))");
			if($user_ship['shipclass'] == 302 && ($temp056 + $user_ship['empty_bays'] > 0)){
				$user_ship['metal'] += $temp056;
				$user_ship['empty_bays'] -= $temp056;
			}

			dbn("update ${db_name}_ships set location = '$toloc', mine_mode = '0' where fleet_id = '$user_ship[fleet_id]' && location = '$user[location]' && login_id = '$user[login_id]'");
			$user['location'] = $toloc;
			get_star();
		}
	}

	//random event stuff
	if ($star['event_random'] > 0 && $user['login_id'] != 1) {
		require_once("$directories[includes]/random_event_funcs.php");
		random_event_checker($star,$user,$autowarp);
	}
}

//emergency returning to earth
if(isset($_REQUEST['emergency_return'])){
	if(!config_check("er", $user_ship)) {
		$user_loc_message .= $st[21];
	} elseif($user['location'] == 1){
		$user_loc_message .= $st[22];
	} elseif(!isset($_POST['sure'])){//not confirmed yet.
		get_var($cw['emergency_return'],$_SERVER['PHP_SELF'], $st[23],'sure','yes');
	} else{

		explore_sys($user, 1); //on the off chance it hasn't been destroyed.

		$user_ship['config'] = str_replace("er" ,"" ,$user_ship['config']);

		dbn("update ${db_name}_ships set location = '1', mine_mode = '0', config='$user_ship[config]', upgrade_slots = upgrade_slots + 1 where ship_id = '$user[ship_id]'");
		$user_ship['mine_mode'] = 0;
		$user_ship['location'] = 1;
		$user['location'] = 1;
		get_star();
	}
}

archive_links();

#random event stuff:
if ($star['event_random'] > 0) {
if ($star['event_random'] == 2) {
	$random_str = "<font color=#00aaaa><center><p />".$st[24]." <a href='help.php?topic=Random_Events' target='_blank'>(".$cw['help'].")</a>";
	$random_str .= "<p />".$st[25]."</center><p /></font>";
	$header = $cw['nebula'];
} elseif($star['event_random'] == 4) {
	$random_str = "<center><p />".$st[26];
	$random_str .= "<p />".$st[27]."</center>";
	$header = $cw['metal_rush'];
} elseif($star['event_random'] == 5) {
	$random_str = "<center><p />".$st[28];
	$random_str .= "<p />".$st[29]."<p />";
	$header = $cw['supernova'];
} elseif($star['event_random'] == 6) {
	$random_str = "<center><p />".$st[30];
	$random_str .= "<p />".$st[31]."<p />";
	$header = $cw['supernova_remnant'];
} elseif($star['event_random'] == 14) {
	$random_str = "<center><p />".$st[32];
	$random_str .= "<br />".$st[33]."</center>";
	$header = $cw['safe_supernova_remnant'];
} elseif($star['event_random'] == 10) {
	$random_str .= "<center><p />".$st[34]."</center><p />";
	$header = $cw['artificial_supernova'];
} elseif($star['event_random'] == 11) {
	$random_str .= "<center><p />".$st[35]."</center><p />";
	$header = $cw['artificial_supernova'];
} elseif($star['event_random'] == 12) {
	$random_str .= "<center><p />".$st[36]."</center><p />";
	$header = $cw['solar_storm'];
}
}


#Normal system - Single ship mining
if(isset($mine)) {
	if($mine == 1 && $user_ship['mine_rate_metal'] < 1 && $GAME_VARS['alternate_play_1'] == 1){
		$user_loc_message .= $st[37];
		$user_ship['mine_mode'] = 0;
	} elseif($mine == 0 && $user_ship['mine_rate_fuel'] < 1 && $GAME_VARS['alternate_play_1'] == 1) {
		$user_loc_message .= $st[38];
		$user_ship['mine_mode'] = 0;
	} elseif($user_ship['mine_rate_metal'] < 1 && $user_ship['mine_rate_fuel'] < 1 && $alternate_plat_1 == 0){
		$user_loc_message .= $st[39];
		$user_ship['mine_mode'] = 0;
		} else {
		dbn("update ${db_name}_ships set mine_mode = '$mine' where ship_id = '$user[ship_id]'");
		$user_ship['mine_mode'] = $mine;
		$user_loc_message .= $cw['ship_mining'];
	}
}


if(isset($mine_all)) {
	if($GAME_VARS['alternate_play_1'] == 1){ #alternate mining
		if($mine_all == 1){#metal
			dbn("update ${db_name}_ships set mine_mode = '".(int)$mine_all."' where mine_rate_metal > 0 && (ship_id = '$user[ship_id]' || (login_id = '$user[login_id]' && location = '$user[location]'))");
		} else {#fuel
			dbn("update ${db_name}_ships set mine_mode = '".(int)$mine_all."' where mine_rate_fuel > 0 && (ship_id = '$user[ship_id]' || (login_id = '$user[login_id]' && location = '$user[location]'))");
		}
	} else { #normal mining
		dbn("update ${db_name}_ships set mine_mode = '$mine_all' where (mine_rate_metal > 0 || mine_rate_fuel > 0) && (ship_id = '$user[ship_id]' || (login_id = '$user[login_id]' && location = '$user[location]'))");
	}
	#mass mining
	if((($user_ship['mine_rate_metal'] > 0 || $user_ship['mine_rate_fuel'] > 0) && $mine_all && $GAME_VARS['alternate_play_1'] ==0) || ($user_ship['mine_rate_metal'] > 0 && $mine_all == 1 && $GAME_VARS['alternate_play_1'] ==1) ||($user_ship['mine_rate_fuel'] > 0 && $mine_all == 2 && $GAME_VARS['alternate_play_1'] ==1)){
		$user_ship['mine_mode'] = $mine_all;
		$user_loc_message .= $cw['fleet_mining'];
	} else {
		$user_loc_message .= $st[40];
		$user_ship['mine_mode'] = 0;
	}
}


if(isset($jettison)) {
	if(isset($sure)) {
			get_var($cw['jettison_cargo'],'location.php',$st[41],'sure','yes');
	} else {
		//if the ship HAD colonists on, then give the player a nice little easter egg for jettisoning the unfortunate souls.
		if($user_ship['colon'] > 0){
			$temp = mt_rand(0,4);
			if($temp == 0) {
				$extra_text = "<br />".$st[42];
				$news_text_extra = sprintf($st[43], $user[login_name], $user_ship[colon]);
			} elseif($temp == 1) {
				$extra_text = "<br />".$st[44];
				$news_text_extra = sprintf($st[45], $user[login_name], $user_ship[colon]);
			} elseif($temp == 2) {
				$extra_text = "<br />".$st[46];
				$news_text_extra = sprintf($st[47], $user_ship[colon], $user[login_name]);
			} elseif($temp == 3) {
				$extra_text = "<br />".$st[48];
				$news_text_extra = sprintf($st[49], $user[login_name], $user_ship[colon]);
			} elseif($temp == 4) {
				$extra_text = "<br />".$st[50];
				$news_text_extra = sprintf($st[51], $user[login_name], $user_ship[colon]);
			}
			post_news($news_text_extra, "other, player_status");
		}
		dbn("update ${db_name}_ships set metal=0, fuel=0, elect=0, colon=0 where ship_id = '$user_ship[ship_id]'");
		$user_ship['metal'] = 0;
		$user_ship['fuel'] = 0;
		$user_ship['elect'] = 0;
		$user_ship['colon'] = 0;
		empty_bays($user_ship);
		$user_loc_message .= $cw['cargo_jettisoned'].". \n$extra_text<p />";
	}
}

if ($_GET['tempo']) {
	if (!$user['gdt']) {
		$user_loc_message .= "<p>".$st[1801]."</p>";
	} else {
		$cyclesenp = mt_rand(50, 150);
		$cycles = $user['turns'] + $cyclesenp;
		if ($cycles > $GAME_VARS['max_turns']) $cycles = $GAME_VARS['max_turns'];
		dbn("update ${db_name}_users set gdt=gdt-1, turns=$cycles where login_id=".$user['login_id']);
		$user['gdt']--;
		$user_loc_message .= "<p>".sprintf($st[1802], $cyclesenp)."</p>";
	}
}

$rs = "";

//this will show any remaining autowarps the player is trying to perform.
if(isset($autowarp)) {
	$auto_str  .= " - <a href='location.php?calc_autowarp=1'>".$cw['set_new_autowarp']."</a>";

	$autowarp_path = explode(" ", $autowarp);
	$next_sector = array_shift($autowarp_path);
	$num_aw_left = count($autowarp_path) + 1;

	//ensure next link is valid
	if($next_sector == $star['link_1'] || $next_sector == $star['link_2'] || $next_sector == $star['link_3'] || $next_sector == $star['link_4'] || $next_sector == $star['link_5'] || $next_sector == $star['link_6'] || $next_sector == $star['wormhole']) {
		$autowarp = implode($autowarp_path, "+");
		$sys_to_go = "<b>".str_replace("+", "</b> - <b>", $autowarp)."</b>";
		$auto_link = "";
		if(!empty($autowarp)){
			$auto_link .= "&lt;<a href='location.php?toloc=$next_sector&autowarp=$autowarp'>$next_sector</a>&gt;";
		} else {
			$auto_link .= "&lt;<a href='location.php?toloc=$next_sector'>$next_sector</a>&gt;";
		}
	}
} else {
	$auto_str .= " - <a href='location.php?calc_autowarp=1'><span class='liens_prl'>".$cw['set_autowarp']."</span></a>";
}


#determine system metal info
if($star['metal'] > 0) {
	$metal_str = "<b>".format_nb($star['metal'])."</b>";
	if($user_ship['mine_rate_metal'] > 0 || (($user_ship['mine_rate_metal'] > 0 || $user_ship['mine_rate_fuel'] > 0) && $GAME_VARS['alternate_play_1'] == 0)){
		if($user_ship['mine_mode'] == 1) {
			$metal_str .= " - <img src='images/interface/minage.gif' align='absmiddle' />&nbsp;".$cw['currently_mining']." - <a href='location.php?mine_all=1'>".$cw['fleet_mining']."</a>";
		} else {
			$metal_str .= " - <a href='location.php?mine=1'>".$cw['mine']."</a> - <a href='location.php?mine_all=1'>".$cw['fleet_mining']."</a>";
		}
	} else {
		$metal_str .= " - <a href='location.php?mine_all=1'>".$cw['fleet_mining']."</a>";
	}
}


#determine system fuel info
if($star['fuel'] > 0) {
	$fuel_str = "<b>".format_nb($star['fuel'])."</b>";

	if($user_ship['mine_rate_fuel'] > 0 || (($user_ship['mine_rate_metal'] > 0 || $user_ship['mine_rate_fuel'] > 0) && $GAME_VARS['alternate_play_1'] == 0)){
		if($user_ship['mine_mode'] == 2) {
			$fuel_str .= " - <img src='images/interface/minage.gif' align='absmiddle' />&nbsp;".$cw['currently_mining']." - <a href='location.php?mine_all=2'>".$cw['fleet_mining']."</a>";
		} else {
			$fuel_str .= " - <a href='location.php?mine=2'>".$cw['mine']."</a> - <a href='location.php?mine_all=2'>".$cw['fleet_mining']."</a>";
		}
	} else {
		$fuel_str .= " - <a href='location.php?mine_all=2'>".$cw['fleet_mining']."</a>";
	}
}

//this table contains the star system, and the minimap. The first row being for the system data
$error_str .= "\n\n<table width='100%' border='0'><tr><td>";

$error_str .= "\n<table border='0' cellspacing='1' cellpadding='3'>";
$error_str .= "\n<tr>\n<td bgcolor='#333333' colspan='2' nowrap><h4 id='systeme'>".$cw['star_system']." #$user[location] - <b class='b1'>$star[star_name]</b> = ($star[planetary_slots] ".$cw['planetary_slots'].")</h4></td></tr>";

#warp links
$error_str .= "\n<tr><td bgcolor='#555555'><span class='liens_prl'>".$cw['sauter_vers']."</span></td><td bgcolor=#333333>";

if($user['location'] > 0 && $user['location'] <= $game_info['num_stars']){ //ensure user is in a system that exists.
	print_link($star['link_1']);
	print_link($star['link_2']);
	print_link($star['link_3']);
	print_link($star['link_4']);
	print_link($star['link_5']);
	print_link($star['link_6']);
	$error_str .= $auto_str . "</td>"; // => autowarp

	if(!empty($star['wormhole'])) {
		$error_str .= "<td bgcolor=#555555>".$cw['wormhole']."</td><td bgcolor=#333333>&lt;<a href='location.php?toloc=$star[wormhole]'>$star[wormhole]</a>&gt;</td>";
	}
} else { //user not in a system that exists, so make a link to system 1.
	$error_str .= "&lt;<a href='location.php?toloc=1'>1</a>&gt; ";
}

$error_str .= "</tr>"; #end warp links

#autowarp
if(!empty($auto_link)){
	if(preg_match("/[0-9]/",$sys_to_go)){ //only put the '-' in when more than 1 jump left.
		$sys_to_go = " - ".$sys_to_go;
	}
	$error_str .= "<tr><td bgcolor=#555555>".$cw['autowarp']."</td><td bgcolor=#333333>$auto_link $sys_to_go = (<b>$num_aw_left</b> ".$cw['warps'].")</td></tr>";
}

#metal and fuel
if(!empty($metal_str)){
	$error_str .= "<tr><td bgcolor=#555555>".$cw['metals']."</td><td bgcolor=#333333><img src='images/logos/titane.gif' align=absmiddle> ".$metal_str."</td></tr>";
}
if(!empty($fuel_str)){
	$error_str .= "<tr><td bgcolor=#555555>".$cw['fuel']."</td><td bgcolor=#333333><img src='images/logos/larium.gif' align=absmiddle> ".$fuel_str."</td></tr>";
}

if(!empty($user_loc_message)){
	$error_str .= "<tr><td bgcolor=#333333 colspan=2 align=center>$user_loc_message</td></tr>";
}

if(!empty($random_str)){
	$error_str .= "<tr><td bgcolor=#333333 colspan=2 align=center>$random_str</td></tr>";
}

//close small table with system info in it
$error_str .= "</table>";



$error_str .= "<p /><table cellpadding=2><tr>";

// ports
db("select port_id from ${db_name}_ports where location = '$user[location]'");
while($port = dbr(1)) {
	$error_str .= "\n<td align=center><a href='port.php?port_id=$port[port_id]'><img src='images/places/port_petit.jpg' border=0></a><br>".$cw['starport']." - <a href='port.php?port_id=$port[port_id]'>".$cw['dock']."</a></td><td width=20>&nbsp;</td>";
}

// blackmarkets
if($GAME_VARS['uv_num_bmrkt'] > 0){
	$bm_t[0] = "black_market.php";
	$bm_t[1] = "bm_ships.php";
	$bm_t[2] = "bm_upgrades.php";

	db("select bmrkt_id,bm_name,bmrkt_type from ${db_name}_bmrkt where location = '$user[location]'");
	$bmrkt = dbr();
	if($bmrkt){
		$error_str .= "<br />";
		while($bmrkt) {
			$error_str .= "<td align=center><a href=".$bm_t[$bmrkt['bmrkt_type']]."?bmrkt_id=$bmrkt[bmrkt_id]><img src='images/places/bm_petit.jpg' border=0></a><br><b class='b1'>$bmrkt[bm_name]'s</b> ".$cw['blackmarket']." - <a href=".$bm_t[$bmrkt['bmrkt_type']]."?bmrkt_id=$bmrkt[bmrkt_id]>".$cw['contact']."</a></td>";
			$bmrkt = dbr();
		}
		$error_str .= "<br />";
	}
}

/**********************
* Planet Listings
**********************/
if($user['location'] == 1){ //system 1. Only earth
   $temp_str = "\n<td align=center><a href='earth.php'><img src='images/places/earth_petite.jpg' border=0></a><br>".$cw['planet_earth']." - <a href='earth.php'>".$cw['land']."</a></td>";
} else {

   db("select * from ${db_name}_planets where location = '$user[location]' order by planet_name asc, fighters desc");
   $planets = dbr(1);

   $temp_str = "";
   $temp2_str = "";

   while($planets) {
      if($planets['login_id'] == $user['login_id']){ #seperate user planets from other planets
         $temp2_str .= "<a href='planet.php?planet_id=$planets[planet_id]'><img src=$directories[images]/planets/".$planets['planet_img'].".jpg border=0 alt='".$st[52]."' border=0 /></a><br /><br />";
         $temp2_str .= "\n<br />".sprintf($st[53], $planets['planet_name'], $planets['fighters'], $planets['allocated_to_fleet'])." - <a href='planet.php?planet_id=$planets[planet_id]'>".$cw['land']."</a><br />";
      } else { #other players planets
         $temp_str .= "<br /><img src=$directories[images]/planets/".$planets['planet_img'].".jpg border=0 alt='".$st[52]."' /><br /><br />"; // traduction
         $temp_str .= "\n<br />".$cw['planet']." <b class='b1'>$planets[planet_name]</b> ";
         if ($planets['login_id'] > 5) $temp_str .= " appartenant à " . print_name($planets);
         $temp_str .= " (<b>$planets[fighters]</b> ".$cw['fighters'].")";

         if(($planets['login_id'] == $user['login_id']) || ($planets['clan_id'] == $user['clan_id'] && $planets['clan_id'] != 0) || ($user['login_id'] == 1) || ($planets['fighters'] == 0) || $user['ship_id'] == 1) {
            $temp_str .= " - <a href='planet.php?planet_id=$planets[planet_id]'>".$cw['land']."</a><br />";
         } else {
            if($GAME_VARS['attack_planet_flag'] != 0){
               $temp_str .= " - <a href='attack.php?target=$planets[planet_id]&planet_attack=1'>".$cw['attack']."</a>";
               if(config_check("sv",$user_ship)) { //quark disrupter
                  $temp_str .= " - <a href='attack.php?quark=1&planet_num=$planets[planet_id]'>".$cw['fire_quark_displacer']."</a>";
               } elseif(config_check("sw",$user_ship) && $GAME_VARS['enable_superweapons'] == 1) { //terra maelstrom
                  $temp_str .= " - <a href='attack.php?terra=1&planet_num=$planets[planet_id]'>".$cw['fire_terra_maelstrom']."</a><br />";
               }
               if($planets['pass'] != '0' && !empty($planets['pass'])) {
                  $temp_str .= " - <a href='planet.php?planet_id=$planets[planet_id]'>".$cw['have_pass']."</a><br />";
               }
            }
         }
      }
      $planets = dbr(1);
   }//end while
}

//$error_str .= '</tr></table>';

#determine if user has any planets in the system
if(!empty($temp2_str)){
$error_str .= '</tr></table>';
   $error_str .= "\n<br /><h3>".$cw['your_planets']."</h3><br />".$temp2_str."<br />";
   if(!empty($temp_str)){
$error_str .= '</tr></table>';
      $error_str .= "\n<br /><h3>".$cw['other_planets']."</h3>".$temp_str;
   }
} else {

   $error_str .= $temp_str;
$error_str .= '</tr></table>';
}
$temp_str = "";



/**********************
* Player Ship Listings
**********************/

$error_str .= "<p />";

db("select count(ship_id) from ${db_name}_ships where login_id = '$user[login_id]' && location='$user[location]'");
$count=dbr();

settype($show_user_ships, "integer");

/* HANDLE USER SHIPS */
if ($show_user_ships == 1) {
	$user['show_user_ships'] = 1;
	dbn("update ${db_name}_users set show_user_ships = 1 where login_id = '$user[login_id]'");
} elseif ($show_user_ships == 2) {
	$user['show_user_ships'] = 0;
	dbn("update ${db_name}_users set show_user_ships = 0 where login_id = '$user[login_id]'");
} elseif ($show_user_ships == 3) {
	$user['show_user_ships'] = 3;
	dbn("update ${db_name}_users set show_user_ships = 3 where login_id = '$user[login_id]'");
}


$error_str .= '<h3>' . $cw['vos_vaisseaux'] . '</h3>';


$error_str .= sprintf($st[54], $count[0]);

#show the fleet info
if($count[0] > 1 && $user['show_user_ships'] != 3) {
	$error_str .= "<br /><br /><a href='#null' id='mc_chgt_flotte'><img src='images/interface/right.gif' align='absmiddle' />&nbsp;Options flotte</a><br />";
	$error_str .= "<div id='chgt_flotte'><FORM method='POST' action='location.php' name='fleet_maint'>";
	$error_str .= $cw['changement_flotte'] . " vers la flotte n° <input type='text' name='join_fleet_id' value='' max='3' size='3' class='inputtext' />";

	#only show the radio buttons if user is in a clan.
	if($user['clan_id'] > 0 && $GAME_VARS['clan_fleet_attacking'] == 1){
		$error_str .= " - ".$cw['your_fleet']."<input TYPE='radio' NAME='fleet_type' value=0 CHECKED />- ".$cw['can_fleet']."<input TYPE='radio' NAME='fleet_type' value=1 />";
	} else {
		$error_str .= "<input TYPE='hidden' name=fleet_type value=0 />";
	}
	$error_str .= " - <input type='submit' name=join_fleet_button value='".$cw['change_fleet']."' /></div><br /><br />";
//	$error_str .= " - <a href=javascript:TickAll(\"fleet_maint\")>".$cw['invert_ship_selection']."</a><p />";
}

/* SHOW FULL LIST OF USER SHIPS */
if($user['show_user_ships'] == 1) {
	db2("select * from ${db_name}_ships where login_id = '$user[login_id]' && location = '$user[location]' && ship_id > 1 order by fleet_id asc, fighters desc, ship_name asc");
	$ships = dbr2(1);


		if($ships == "") {
			$error_str .= $st[55];

		} else {
		//$error_str .= "\n<p />".$cw['show_all_ships']."<!-- - <A HREF='location.php?show_user_ships=2'>".$cw['show_ship_summary']."</A>--> - <A HREF='location.php?show_user_ships=3'>".$cw['show_fleet_summary']."</A><p />";

			$last_fleet = '';

			if($user['clan_id'] > 0 && $GAME_VARS['clan_fleet_attacking'] == 1)
				$table_head_array = array($cw['name'], $cw['class'], $cw['fighters'], $cw['cargo'], $cw['can_fleet'], $cw['fleet'], $cw['command'], "&nbsp;");
			else
				$table_head_array = array($cw['name'], $cw['class'], $cw['fighters'], $cw['cargo'], $cw['fleet'], $cw['command'], "&nbsp;");
			$error_str .= make_table($table_head_array);

		
			#Loop through all of a players ships in the system.
			while($ships){
				$cloak_str_start = "";
				$cloak_str_end = "";
				$ships['ship_name'] = stripslashes($ships['ship_name']);
				$rowspan = array(1,1,1,1,1,1,1);
				#ship is cloaked.
				if(config_check("ls",$ships) || config_check("hs",$ships)){
					$cloak_str_start = "<b class='cloak'>";
					$cloak_str_end = "</b>";
				}

				if($last_fleet != $ships['fleet_id']){
						
					//calcul du nombre de vaisseaux dans la flotte actuelle
					db("select count(ship_id) from ${db_name}_ships where login_id = ".$user['login_id']." AND fleet_id = ". $ships['fleet_id']." AND location = ".$ships['location']);
					$rowspan_count = dbr();
					//var_dump($rowspan[0]);
					//rowspan[4] = nombre de vaisseaux dans la flottte ($rowspan_count[0];)
					$rowspan[4] = $rowspan_count[0];
						
					$last_fleet = $ships['fleet_id'];
				}else
				{
					$ships['fleet_id'] = null;
				}

				//$error_str .= "\n $cloak_str_start $ships[ship_name] (<b class='b1'>$ships[class_name_abbr]</b> w/ <b>$ships[fighters]</b> fighters)".$cloak_str_end;
				$row_array = array("$cloak_str_start ".popup_help("ship_info.php?s_id=$ships[ship_id]", 320, 520, $ships['ship_name'])." $cloak_str_end", "".popup_help("help.php?popup=1&ship_info=$ships[shipclass]&db_name=$db_name",300,600,$ships[class_name_abbr]), "$ships[fighters]", bay_storage_little($ships));

				if ($user['clan_id'] > 0 && $GAME_VARS['clan_fleet_attacking'] == 1){
					//$c_fleet_str = " - Clan Fleet: <b>".$ships['clan_fleet_id']."</b> ";
					$row_array = array_merge($row_array, (array)$ships['clan_fleet_id']);
				} else {
					$c_fleet_str = "";
				}

				//$error_str .= " - Fleet: <b>$ships[fleet_id]</b> $c_fleet_str- <a href='location.php?command=$ships[ship_id]'>Command</a>";
				//$error_str .= " - <input type=checkbox name=do_ship[$ships[ship_id]] value=$ships[ship_id] /><br />";

				if ($ships['ship_id'] == $user['ship_id']) {
					$bgcolor = '000000';
					$commander = '<i>'.$cw['aux_commandes'].'</i>';
				} else {
					$bgcolor = '333333';
					$commander = "<a href='location.php?command=$ships[ship_id]'>".$cw['command']."</a>";
				}

				$row_array2 = array( $commander, "<input type=checkbox name=do_ship[$ships[ship_id]] value=$ships[ship_id] />");
								
				if ($ships['fleet_id'] != null) array_push($row_array, $ships['fleet_id']);
				$row_array = array_merge($row_array, $row_array2);

				 $error_str .= make_row($row_array, $bgcolor, $rowspan);
				//var_dump($row_array);
			$ships = dbr2(1);
			}
			$error_str .= '</table><br>';

		} # end of looping through all of players' ships.

	#more than 10 ships, put one at the bottom too.
	/*if($count[0] > 10 && $user['show_user_ships'] != 3){
		$error_str .= "\n<p />".$cw['fleet']." #: <input type=text name=join_fleet_id_2 value='' max=3 size=3 class='inputtext'/>";

		#only show the radio buttons if user is in a clan.
		if($user['clan_id'] > 0 && $GAME_VARS['clan_fleet_attacking'] == 1){
			$error_str .= " - ".$cw['your_fleet']."<input TYPE='radio' NAME='fleet_type' value=0 CHECKED />- ".$cw['can_fleet']."<input TYPE='radio' NAME='fleet_type' value=1 />";
		} else {
			$error_str .= "<input TYPE='hidden' name=fleet_type value=0 />";
		}
		$error_str .= " - <input type='submit' name=join_fleet_button value='".$cw['change_fleet']."' />";
//		$error_str .= " - <a href=javascript:TickAll(\"fleet_maint\")>".$cw['invert_ship_selection']."</a><p /></form>";

	} elseif($count[0] > 1 && $user['show_user_ships'] != 3) {
		$error_str .= "</form>";
	}*/

unset($ships);

/* SHOW SUMMARY OF USER SHIPS */
} elseif($user['show_user_ships'] == 0) {
	db2("select count(ship_id) as total, ship_id, sum(fighters) as fighters, class_name, count(DISTINCT fleet_id) as fleets, fleet_id, config, shipclass from ${db_name}_ships where location = '$user[location]' && ship_id > 1 && login_id = '$user[login_id]' && ship_id != '$user[ship_id]' group by class_name order by total desc, fighters desc");
	$ships = dbr2(1);
	if(!$ships){
		$error_str .= $st[56];
	} else {
		//$error_str .= "<p /><A HREF='location.php?show_user_ships=1'>".$cw['show_all_ships']."</A><!-- - ".$cw['show_ship_summary']." -->- <A HREF='location.php?show_user_ships=3'>".$cw['show_fleet_summary']."</A><p />";
		echo"<br />";
		$error_str .= $st[57];

		while($ships){
			$error_str .= "\n<b class='b1'>$ships[class_name]s</B>: <b>$ships[total]</b> w/ <b>$ships[fighters]</b> ".$cw['fighters'];
			#show config for ships.
			$group_tow_text = "";
			if($ships['total'] > 1){
				$group_tow_text .= " ".$cw['group'];
			}

			if($ships['fleets'] > 1){ #more than 1 fleet in this ship type.
				db("select fleet_id from ${db_name}_ships where location = '$user[location]' && ship_id > 1 && login_id = '$user[login_id]' && ship_id != '$user[ship_id]' && shipclass = '$ships[shipclass]' group by fleet_id order by fleet_id asc");

				$fleet_num_str = " - ".$cw['fleets'].":";
				$fl_count = 0;
				while ($fleet_nums = dbr(1)){

					if($fl_count > 0){
						$fleet_num_str .= ", <b>$fleet_nums[fleet_id]</b>";
					} else {
						$fleet_num_str .= " <b>$fleet_nums[fleet_id]</b>";
					}

					$fl_count++;
				}
			} else { #only 1 fleet.
				$fleet_num_str = " - ".$cw['fleet'].": <b>$ships[fleet_id]</b>";
			}

			$error_str .= " $fleet_num_str - <input type=checkbox name=do_ship_type[$ships[shipclass]] value=$ships[shipclass] /> - <a href='location.php?command=$ships[ship_id]'>".$cw['command']."</a><br />";

			$ships = dbr2(1);
		}

		if($count[0] > 1){
			$error_str .= "</form>";
		}

		unset($ships);
	}


#List by Fleet
} else {
	db2("select count(ship_id) as total, ship_id, sum(fighters) as fighters, fleet_id, count(distinct shipclass) as type_count, class_name_abbr from ${db_name}_ships where location = '$user[location]' && ship_id > 1 && login_id = '$user[login_id]' group by fleet_id order by fleet_id asc"); // && ship_id != '$user[ship_id]'
	$ships = dbr2(1);
	if(!$ships){
		$error_str .= $st[56];
	} else {
		//$error_str .= "<p /><A HREF='location.php?show_user_ships=1'>".$cw['show_all_ships']."</A> <!-- - <A HREF='location.php?show_user_ships=2'>".$cw['show_ship_summary']."</A> --> - ".$cw['show_fleet_summary']."<p />";

		$error_str .= $st[58];

		while($ships){

			if($ships['type_count'] > 1){ #more than 1 fleet in this ship type.
				db("select class_name_abbr from ${db_name}_ships where location = '$user[location]' && ship_id > 1 && login_id = '$user[login_id]' && ship_id != '$user[ship_id]' && fleet_id = '$ships[fleet_id]' group by class_name_abbr order by shipclass asc");

				$tl_count = 0;
				$type_num_str = "";

				while ($type_abbr = dbr(1)){

					if($tl_count > 0){
						$type_num_str .= ", <b class='b1'>$type_abbr[class_name_abbr]</b>";
					} else {
						$type_num_str .= " <b class='b1'>$type_abbr[class_name_abbr]</b>";
					}

					$tl_count++;
				}
			} else { #only 1 fleet.
				$type_num_str = "<b class='b1'>$ships[class_name_abbr]</b>";
			}

			$error_str .= "\n".$cw['fleet'].": <B>$ships[fleet_id]</B> - ".$cw['ships'].": <b>$ships[total]</b> (Types: $type_num_str) - ".$cw['fighters'].": <b>$ships[fighters]</b>";
			#show config for ships.
			$group_tow_text = "";
			if($ships['total'] > 1){
				$group_tow_text .= " ".$cw['group'];
			}

			!isset($ships['config']) ? $ships['config'] = "" : 1;

			$error_str .= " - <a href='location.php?command=$ships[ship_id]'>".$cw['command']."</a><br />";

			$ships = dbr2(1);
		}

		if($count[0] > 1){
			$error_str .= "</form>";
		}

		unset($ships);
	}
}


/**********************
* Other Ship Listings
**********************/

$error_str .= '<br /><h3>' . $cw['autre_vaisseaux'] . '</h3>';

settype($show_enemy_ships, "integer");

/* HANDLE Enemy SHIPS */
if ($show_enemy_ships == 1) {
	$user['show_enemy_ships'] = 1;
	dbn("update ${db_name}_users set show_enemy_ships = 1 where login_id = '$user[login_id]'");
} elseif ($show_enemy_ships == 2) {
	$user['show_enemy_ships'] = 0;
	dbn("update ${db_name}_users set show_enemy_ships = 0 where login_id = '$user[login_id]'");
}

/* SHOW FULL LIST OF ENEMY SHIPS */
if($user['show_enemy_ships'] == 1){
	db2("select s.ship_id, s.ship_name, s.login_id, s.fighters, s.class_name,s.class_name_abbr, s.size, u.login_name, u.clan_id, u.clan_sym, u.clan_sym_color, u.turns_run, s.clan_fleet_id, s.config from ${db_name}_ships s, ${db_name}_users u where s.location = '$user[location]' and s.ship_id > 1 and s.login_id = u.login_id && s.login_id != '$user[login_id]' order by s.config REGEXP 'ls|hs', s.fighters desc, s.ship_name");
	$ships = dbr2(1);

//	$error_str .= "<br />";
	if($user['login_id'] == 1 || ($user['turns_run'] < $GAME_VARS['turns_before_space_attack'] || $user['ship_id'] == 1) || ($user['location'] == 1 && $GAME_VARS['attack_sol_flag'] == 0) || $GAME_VARS['attack_space_flag'] == 0){
		$can_attack = 0;
	} else {
		$can_attack = 1;
	}

//	$error_str .= (  $GAME_VARS['flag_space_attack'] == 0)."<br />";

	#there are other ships in the system
	if ($ships) {
		$error_str .= "<A HREF='location.php?show_enemy_ships=2'>".$st[59]."</A><p />";

		#loop through other players ships.
		while($ships){
			!isset($ships['config']) ? $ships['config'] = "" : 1;
			#reset cloaked ship info.
			$cloak_str_start = "";
			$cloak_str_end = "";

			#player is able to see only non-cloaked ships, unless conditions are met.
			if((!config_check("ls", $ships) && !config_check("hs", $ships)) || ($ships['clan_id'] == $user['clan_id'] && $user['clan_id'] > 0) || (config_check("ls", $ships) && config_check("sc", $ships)) || $user['login_id'] == 1){
				$error_str .= print_name($ships);

				#sets some cloak text into a string, if a ship is cloaked.
				if(config_check("ls", $ships) || config_check("hs", $ships)){
					$cloak_str_start = "<b class=cloak>";
					$cloak_str_end = "</b>";
				}

				$error_str .= "$cloak_str_start	$ships[ship_name] ($ships[class_name] w/ <b>$ships[fighters]</b> ".$cw['fighters'].")".$cloak_str_end;

			} elseif(config_check("hs", $ships) && !config_check("sc", $ships)) { #hs without scanner
				$error_str .= "<b class=cloak>::::: ".discern_size($ships['size'])." ".$cw['disturbance_detected'].":::::</b>";
			} elseif(config_check("hs", $ships) && config_check("sc", $ships)) { # hs, with scanner.
				$error_str .= "<b>".$cw['unknown_owner']."</b><b class=cloak> $ships[ship_name] ($ships[class_name] w/ <b>$ships[fighters]</b> ".$cw['fighters'].")</b>";
			} elseif(config_check("hs", $ships) && config_check("sc", $ships)) { # ls, no scanner.
				$error_str .= sprintf($st[60], $ships[class_name]);
			}

			if($ships['clan_id'] == $user['clan_id'] && $user['clan_id'] > 0 && $GAME_VARS['clan_fleet_attacking'] == 1){
				$error_str.= " - ".$cw['can_fleet'].": <b>$ships[clan_fleet_id]</b>";
			}
			//attack link won't be shown if:
			//player cannot attack
			//target is admin
			//is a clanmate
			//target in safe_turns
			//target ship is ls stealth and no scanner
			//user in holiday mode
			//(config_check("ls", $ships) || config_check("hs", $ships) && !config_check("sc", $ships))
			if($can_attack == 0 || $ships['login_id'] == 1 || ($user['clan_id'] == $ships['clan_id'] && $user['clan_id'] > 0) || ($ships['turns_run'] < $GAME_VARS['turns_safe']) || (config_check("ls", $ships) || config_check("hs", $ships) && !config_check("sc", $ships)) || checkHolidayMode( $ships['login_id'] )){
				$error_str .= "<br />";
			} else {
				$error_str .= " - <a href='attack.php?target=$ships[ship_id]'>".$cw['attack']."</a><br />";
			}
			$ships = dbr2(1);

		} #end of loop through other ships.
		unset($ships);

	} else {
		$error_str .= $st[61];
	}

/* SHOW SUMMARY OF ENEMY SHIPS	*/
} else {
	db2("select count(s.ship_id) as total, sum(s.fighters) as fighters, s.login_id, u.clan_id, u.turns_run, s.config from ${db_name}_ships s, ${db_name}_users u where s.location = '$user[location]' && s.ship_id > 1 && s.login_id != '$user[login_id]' && u.login_id = s.login_id group by config REGEXP 'hs|ls', login_id order by total");
	$ships = dbr2(1);

	if(!$ships){
		$error_str .= "<br />".$st[61];
	} else {
		if($user['login_id'] == 1 || ($user['turns_run'] < $GAME_VARS['turns_before_space_attack'] || $user['ship_id'] == 1) || ($user['location'] == 1 && $GAME_VARS['attack_sol_flag'] == 0) || $GAME_VARS['attack_space_flag'] == 0){
			$can_attack = 0;
		} else {
			$can_attack = 1;
		}
		$error_str .= "<A HREF='location.php?show_enemy_ships=1'>".$st[62]."</A><p />";
		$cloaked_ships = 0;
		$cloaked_figs = 0;
		$cloaked_attack_link = "";
		while($ships){

			!isset($ships['config']) ? $ships['config'] = "" : 1;
			#show un-cloaked ships
			if(!config_check("ls", $ships) && !config_check("hs", $ships)){
				$error_str .= print_name($ships);
				$error_str .= sprintf($st[63], $ships[total], $ships[fighters]);

				//don't show attack link if:
				//player unable to attack
				//target is admin
				//same clan as target
				//target still in safe turns
				if($can_attack == 0 || $ships['login_id'] == 1 || ($user['clan_id'] == $ships['clan_id'] && $user['clan_id'] > 0) || ($ships['turns_run'] < $GAME_VARS['turns_safe'])){
					$error_str .= "<br />";
				} else {
					db("select ship_id from ${db_name}_ships where login_id = '$ships[login_id]' && location='$user[location]' && config NOT REGEXP 'ls|hs' limit 1");
					$to_attack = dbr(1);
					$error_str .= " - <a href='attack.php?target=$to_attack[ship_id]'>".$cw['attack']."</a><br />";
				}

			//show cloaked ships if
			//is admin
			//is in same clan as ships
			//target in safe turns
			//has scanner and targets are only ls
			} elseif($user['login_id'] == 1 || ($ships['clan_id'] == $user['clan_id'] && $user['clan_id'] > 0) || ($ships['turns_run'] < $GAME_VARS['turns_safe']) || (config_check("ls", $ships) && config_check("sc", $ships))){
				$error_str .= print_name($ships);
				if(config_check("ls", $ships)){
					$error_str .= sprintf($st[64], $ships[total], $ships[fighters]);
				} else {
					$error_str .= sprintf($st[65], $ships[total], $ships[fighters]);
				}

				//don't show attack link if:
				//target is admin
				//player can't attack
				//target is in same clan
				//target in safe turns
				if($user['login_id'] == 1 || ($ships['clan_id'] == $user['clan_id'] && $user['clan_id'] > 0) || ($ships['turns_run'] < $GAME_VARS['turns_safe'])){
					$error_str .= "<br />";
				} else {
					db("select ship_id from ${db_name}_ships where login_id = '$ships[login_id]' && location='$user[location]' && config REGEXP 'ls' limit 1");
					$to_attack = dbr(1);
					$error_str .= " - <a href='attack.php?target=$to_attack[ship_id]'>".$cw['attack']."</a><br />";
				}

			//cloaked ships are all grouped together.
			} else {
				$cloaked_ships += $ships['total'];
				if(config_check("sc",$user_ship) && config_check("hs",$ships)){
					$cloaked_figs += $ships['fighters'];
					db("select ship_id from ${db_name}_ships where login_id = '$ships[login_id]' && location='$user[location]' && config REGEXP 'hs' limit 1");
					$to_attack = dbr(1);
					$cloaked_attack_link = " - <a href='attack.php?target=$to_attack[ship_id]'>".$cw['attack']."</a>";
				}
			}
			$ships = dbr2(1);
		} # end of loop of other players ships.

		#cloaked ships the player cannot tell many details about.
		//these are at the bottom of the page, and are all grouped together.
		if($cloaked_figs){
			$error_str .= sprintf($st[66], $cloaked_ships, $cloaked_figs).$cloaked_attack_link."<br />";
		} elseif($cloaked_ships){
			$error_str .= "<b class=cloak><b>$cloaked_ships</b> ".$cw['cloaked_ships']."</b><br />";
		}
		unset($ships);
	}
}


//ensure everything is outputed for this frame
$error_str .= "<br />$temp_str";

//end the cell that has the data in it, and create a cell for minimap and such data
$error_str .= "</td><td width='301' valign='top' align='center'>Mini-carte de la galaxie<br />";


//<a href=\"map.php\" target='_blank'>

if (isset($_GET['minimap'])) {
	$minimap = ($_GET['minimap']) ? 1:0;
	dbn("update ${db_name}_user_options set show_minimap=$minimap where login_id=$login_id");
	$user_options['show_minimap'] = $minimap;
}


if($user_options['show_minimap']){
	$query_string = "?id=".$user['login_id']."&game=".$game_info['db_name']."&serveur=".urlencode(URL_PREFIX)."&offx=".($star['x_loc']-$midx)."&offy=".($star['y_loc']-$midy)."&offz=".$star['z_loc'];
	$error_str .= "<table border=1 bordercolor=#808080 cellspacing=0 cellpadding=0>\n<tr>\n<td>\n";
	$error_str.='<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="300" height="300" id="Galaxie" align="middle">
	<param name="allowScriptAccess" value="sameDomain" />
	<param name="allowFullScreen" value="false" />
	<param name="wmode" value="transparent" />
	<param name="movie" value="images/Galaxie.swf'.$query_string.'" /><param name="quality" value="high" /><param name="bgcolor" value="#000000" />	<embed src="images/Galaxie.swf'.$query_string.'" quality="high" bgcolor="#000000" width="300" height="300" name="Galaxie" align="middle" allowScriptAccess="sameDomain" allowFullScreen="false" wmode="transparent" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
	</object>';
	$error_str .= '</td></tr></table><img src="images/interface/bouger_souris.png" title="Glissez-bougez la souris pour naviguer dans la carte, utilisez la molette pour zoomer/dézoomer">&nbsp;&nbsp;&nbsp;&nbsp;<a href="location.php?minimap=0">Basculer en affichage 2D</a>';
} else {

	if($GAME_VARS['uv_explored'] == 0 && $user['explored_sys'] != -1 && $user['login_id'] != 1){//unexplored
		$error_str .= "<table border=1 bordercolor=#808080 cellspacing=0 cellpadding=0>\n<tr>\n<td>\n<img src='dynamic_map.php?exp_sys={$user['explored_sys']}&loc={$user['location']}' border=0 width=300 height=300 alt=\"Mini-carte des systèmes autour de #$user[location]\" usemap='#system_map' />";
	} else {//all explored
		if($game_info['random_filename'] == -1){
			$file_rand = "";
		} else {
			$file_rand = "_".$game_info['random_filename'];
		}
		$error_str .= "<table border=1 bordercolor=#808080 cellspacing=0 cellpadding=0>\n<tr>\n<td>\n<img src=$directories[images]/${db_name}_maps/sm{$user['location']}{$file_rand}.png border=0 width=300 height=300 alt=\"Mini-carte des systèmes autour de #$user[location]\" usemap='#system_map' />";
	}

	//map for the mini-map:
	$error_str .= "<map name='system_map'>\n";
	//get location of each star linked to
	db("select star_id, x_loc, y_loc from ${db_name}_stars where star_id = '".implode('\' || `star_id` = \'', $star_links)."'");

	if(!empty($star_links)){
		//compensate for present star location. Note: -100 is half of mini-map size.
		while($loop_stars = dbr()){
			$loop_stars['x_loc'] -= $star['x_loc'] - 150;
			$loop_stars['y_loc'] -= $star['y_loc'] - 150;
			$linkInfo[(int)$loop_stars['star_id']] = $loop_stars;
		}

		//create html
		foreach ($linkInfo as $id => $s) {
			$error_str .= "<area shape='rect' coords='" . ($s['x_loc'] - 10) . "," . ($s['y_loc'] - 10) . "," . ($s['x_loc'] + 10) . "," . ($s['y_loc'] + 10) . "' href='location.php?toloc=$id' alt='Système #$id' />\n";
		}
	}

	$error_str .= "</map></td></tr></table><a href='location.php?minimap=1'>Basculer en affichage 3D</a>";

}


if(config_check("sj",$user_ship)) {
	$error_str .= "<p />".make_table2(array($cw['subspace_jump']), "s_funcs");
	$error_str .= q_row("<form name='subspace_form' action='location.php' method='POST'><center>".$cw['set_destination_jump']."<br /><input type='text' size='4' maxlength='4' name='subspace' class='inputtext' /> - <input type='submit' value='".$cw['jump']."' />", "l_col");
}


//close the table that seperates the minimap
$error_str .= "</td></tr></table>";



print_page($header,$error_str);
?>
