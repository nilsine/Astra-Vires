<?php
require("user.inc.php");

if($user['turns_run'] < $GAME_VARS[$st[1069]] && $user['login_id'] != 1) {
	print_page($cw['bomb'],sprintf($st[1070], $GAME_VARS[turns_before_space_attack]));
}

if($user['ship_id'] == 1 && $user['login_id'] != 1) {
	print_page($cw['bomb'],$st[1071]);
}

ship_status_checker();

$error_str = "";

#Alpha Bomb
if($alpha) {
// checks

#	db(attack_planet_check($db_name,$user));
#	$planets = dbr();

	if(empty($planets) || $user['login_id'] == 1) {
		if($user['alpha'] < 1) {
			$error_str = $st[1072];
		} elseif($GAME_VARS['attack_sol_flag'] == 0 && $user['location'] == 1 && $user['login_id'] != 1) {
			$error_str = $st[1073];
		} elseif(!isset($sure)) {
			get_var($cw['use_alpha_bomb'],'bombs.php',$st[1074],'sure','');
		} else {
			if($user['login_id'] > 1){
				dbn("update ${db_name}_users set alpha = alpha - 1 where login_id = $user[login_id]");
			}

			post_news("<b class='b1'>$user[login_name]</b> ".$st[1075]." #$user_ship[location]", $cw['bomb'].", ".$cw['attacking']);
			get_star();

			$lastresort = mysql_query("select s.ship_id,s.ship_name,s.login_id,s.class_name from ${db_name}_ships s, ${db_name}_users u where s.location = '$user[location]' && u.login_id != 1 && s.ship_id > 1 && s.login_id = u.login_id && u.turns_run > '$GAME_VARS[turns_safe]'") or mysql_die("");

			$ship_counter = 0;
			$victims = array();
			while($target_ship = mysql_fetch_array($lastresort)) {
				dbn("update ${db_name}_ships set shields = 0 where ship_id = '$target_ship[ship_id]'");
				$ship_counter++;
				$victims[$target_ship['login_id']] .= "\n<br /><b class='b1'>$target_ship[ship_name]</b> ($target_ship[class_name])";
			}

			#loop to send out a message to each player.
			foreach($victims as $victim_id => $ship_list) {
				$ships_hit = substr_count($ship_list, "<b class='b1'>");

				#don't send a message to the user if they are hit by a bomb.
				if($victim_id == $user['login_id']){
					continue;
				}
				send_message($victim_id,sprintf($st[1076], $user[login_name], $user_ship[location], $ships_hit, $ship_list));
			}

			$error_str .= sprintf($st[1077], $star[star_id], $ship_counter);

		}
		
		db("select * from ${db_name}_users where login_id = '$login_id'");
		$user = dbr(1);
		db("select * from ${db_name}_ships where ship_id = '$user[ship_id]'");
		$user_ship = dbr(1);
		empty_bays($user_ship);
	} else {
		$error_str = $st[1078];
	}

print_page($cw['alpha_bomb'],$error_str);
}

#===========
#Damage Bombs
#===========

#determine type of bomb
if($bomb_type == 1){ #gamma bomb
	$b_text = $cw['gamma'];
	$sql_text = $cw['gamma'];
} elseif($bomb_type == 2){ #delta Bomb
	$b_text = $cw['delta'];
	$sql_text = $cw['delta'];
}

// checks
#db(attack_planet_check($db_name,$user));
#$planets = dbr();

if(empty($planets) || $user['login_id'] == 1) {
	if($user['gamma'] < 1 && $bomb_type==1) {
		$error_str = $st[1079];
	} elseif($user['delta'] < 1 && $bomb_type==2) {
		$error_str = $st[1080];
	} elseif($GAME_VARS['attack_sol_flag'] == 0 && $user['location'] == 1 && $user['login_id'] != 1) {
		$error_str = $st[1081];
	} elseif(!isset($sure)) {
		get_var(sprintf($st[1082], $b_text),'bombs.php',sprintf($st[1083], $b_text),'sure','');
	} else {

		if($user['login_id'] > 1){
			dbn("update ${db_name}_users set ${b_text} = ${b_text} - 1 where login_id = $user[login_id]");
		}

		post_news("<b class='b1'>$user[login_name]</b> ".sprintf($st[1084], $b_text)."<b>$user_ship[location]</b>",$cw['bomb'].", ".$cw['attacking']);

		get_star();
		if($user['login_id'] == 1) {
			$bomb_damage = 10000;
		} elseif($bomb_type==1) { #gamma bomb
			$bomb_damage = 200;
		} elseif($bomb_type==2){ #delta bomb
			#clear all shields on all ships before we start.
			db("select s.ship_id from ${db_name}_ships s, ${db_name}_users u where s.location = '$user[location]' && u.login_id	!= 1 && s.ship_id > 1 && s.login_id = u.login_id && u.turns_run > '$turns_safe'");

			while($target_ship = dbr(1)){
				dbn("update ${db_name}_ships set shields = 0 where ship_id = '$target_ship[ship_id]'");
			}
			$target_ship = "";

			$bomb_damage = 5000;
		}

		if ($star['event_random'] == 2){
			$bomb_damage *= 3;
		}

		$ship_counter = 0;
		$dam_victim = array();
		$destroyed_ships = 0;

		$lastresort = mysql_query("select s.fighters,s.shields,s.ship_id,s.metal,s.fuel,s.location,s.login_id,s.class_name,s.ship_name,s.point_value,u.login_name from ${db_name}_ships s,${db_name}_users u where s.location = '$user[location]' && s.ship_id > '1' && s.login_id >'1' && s.login_id = u.login_id && u.turns_run >= '$turns_safe'") or mysql_die("Bombs are messed up.");

		$elim = 0;

		#loop through players to damage.
		while($target_ship = mysql_fetch_array($lastresort)) {
			#db("select login_name,login_id,ship_id from ${db_name}_users where login_id = '$target_ship[login_id]'");
			#$target = dbr();


			$ship_counter++;

			$temp121 = 0;
			$temp121 = damage_ship($bomb_damage,0,0,$user,$target_ship,$target_ship);
				
			#Used to limit messages sent, so each player only gets 1 message.
			$dam_victim[$target_ship['login_id']] .= "\n<br /><b class='b1'>$target_ship[ship_name]</b> ($target_ship[class_name])";
			if($temp121 > 0) {
				$dam_victim[$target_ship['login_id']] .= $st[1085];
				$elim++;
			}
		} # end bomb while loop.

		$elim = 0;
		#loop to send out a message to each player.
		foreach($dam_victim as $victim_id => $ship_list) {
			
			$ships_hit = substr_count($ship_list, "<b class='b1'>");
			$ships_killed = substr_count($ship_list, $st[1085]);
			$elim += $ships_killed;

			#don't send a message to the user.
			if($victim_id == $user['login_id']){
				continue;
			}
			send_message($victim_id, sprintf($st[1084], $user[login_name], $b_text, $user_ship[location], $ships_hit, $bomb_damage, $ships_killed, $ship_list));
		}

		if($elim == 0){
			$elim = "None";
		}

		$error_str .= sprintf($st[1087], $b_text, $star[star_id], $ship_counter).$ship_counter*$bomb_damage.sprintf($st[1088], $bomb_damage, $elim);
		$error_str .= $st[1089];
		$error_str .= "<p /><b class='b2'>kaaaaBBBBBBOOOOOOOOOOOOOOOOOOOOOOOOOOOOOMMMMMMMMM!!!!!!!</b>"; 
	}

	db("select * from ${db_name}_users where login_id = '$user[login_id]'");
	$user = dbr(1);
	db("select * from ${db_name}_ships where ship_id = '$user[ship_id]'");
	$user_ship = dbr(1);
	empty_bays($user_ship);
} else {
	$error_str = $st[1080];

}


//function that damages a ship with a specified amount of damage.
//send a negative number as the first arguement to destroy a ship outright.
function damage_ship($amount,$fig_dam,$s_dam,$from,$target,$target_ship) {
	global $db_name,$query;

	//set the shields down first off (if needed).
	if($s_dam > 0){
		$target_ship['shields'] -= $s_dam;
		if($target_ship['shields'] < 0){
			$target_ship['shields'] == 0;
		}
		dbn("update ${db_name}_ships set shields = shields - '$s_dam' where ship_id = '$target_ship[ship_id]'");
	}

	//take the fighters down next (if needed).
	if($fig_dam > 0){
		$target_ship['fighters'] -= $fig_dam;
		if($target_ship['fighters'] < 0){
			$target_ship['fighters'] == 0;
		}
		dbn("update ${db_name}_ships set fighters = fighters - '$fig_dam' where ship_id = '$target_ship[ship_id]'");
	}

	//don't want to hurt the admin now do we?
	if($target['login_id'] != 1) {

		//only play with the amount distribution if there is no value to amount
		if($amount > 0){
			
			$shield_damage = $amount;
			if($shield_damage > $target_ship['shields']) {
				$shield_damage = $target_ship['shields'];
			}
			$amount -= $shield_damage;

		}
		if($amount >= $target_ship['fighters'] || $amount < 0) {	//destroy ship
			//Minerals go to the system
			if($from['location'] != 1 && ($target_ship['fuel'] > 0 || $target_ship['metal']) > 0){
				dbn("update ${db_name}_stars set fuel = fuel + ".round($target_ship[fuel]*(mt_rand(20,80)/100)).", metal = metal + ".round($target_ship[metal]*(mt_rand(40,90)/100))." where star_id = $target_ship[location]");
			}

			dbn("delete from ${db_name}_ships where ship_id = '$target_ship[ship_id]'");
			dbn("update ${db_name}_users set fighters_killed = fighters_killed + '$target_ship[fighters]', ships_killed = ships_killed + '1', ships_killed_points = ships_killed_points + '$target_ship[point_value]' where login_id = '$from[login_id]'");

			dbn("update ${db_name}_users set fighters_lost = fighters_lost + '$target_ship[fighters]', ships_lost = ships_lost + '1', ships_lost_points = ships_lost_points + '$target_ship[point_value]' where login_id = '$target[login_id]'");

			if(eregi($cw['escape'],$target_ship['class_name'])) { // escape pod lost
				dbn("update ${db_name}_users set location = '1', ship_id = '1', last_attack = ".time().", last_attack_by = '$from[login_name]', explored_sys = '1' where login_id = '$target[login_id]'");
				wipe_player($target['login_id'], $target['clan_id']);
				return 1;
			} else { // normal ship lost
				//don't bother putting an AI into a new ship to command etc.
				if($target['login_id'] > 5) {

					if($target['ship_id'] != $target_ship['ship_id']) {
						$new_ship_id = $target['ship_id'];

					} else {
						db("select ship_id from ${db_name}_ships where login_id = '$target_ship[login_id]' LIMIT 1");
						$other_ship = dbr();

						if(!empty($other_ship['ship_id'])) { // jump to other ship
							$new_ship_id = $other_ship['ship_id'];

						} else {	// build the escape pod 
							create_escape_pod($target);
							return 2;
						}
					}
					// set ships_killed

					if($target['login_id'] > 5) {
						db("select location from ${db_name}_ships where ship_id = '$new_ship_id'");
						$other_ship = dbr();
					} else {
						$other_ship['location'] = 1;
					}

					dbn("update ${db_name}_users set ship_id = '$new_ship_id', location = '$other_ship[location]', last_attack =".time().", last_attack_by = '$from[login_name]' where login_id = '$target[login_id]'");
				}
			}
			return 1;
		} else { // ship not destroyed
			dbn("update ${db_name}_users set last_attack = ".time().", last_attack_by = '$from[login_name]' where login_id = '$target[login_id]'");
			dbn("update ${db_name}_ships set fighters = fighters - '$amount', shields = shields - '$shield_damage' where ship_id = '$target_ship[ship_id]'");
			dbn("update ${db_name}_users set fighters_lost = fighters_lost + '$amount' where login_id = '$target[login_id]'");
			dbn("update ${db_name}_users set fighters_killed = fighters_killed + '$amount' where login_id = '$from[login_id]'");
			return 0;
		}
	}
	return 0;
}


print_page(sprintf($st[1091], $b_text),$error_str);
?>
