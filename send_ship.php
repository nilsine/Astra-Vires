<?php
require("user.inc.php");

ship_status_checker();
$text = "";
$min_b4_trans = $GAME_VARS['min_before_transfer'];
$rs = "<p /><br /><a href='player_info.php?target=$target'>".$cw['back_player_info']."</a>";

if ($user['joined_game'] > (time() - ($min_b4_trans * 86400)) && $user['login_id'] != 1) {
	print_page($st[732],sprintf($st[733], $min_b4_trans));
} elseif(!isset($target)) { #ensure target has been selected
	print_page($st[732],$st[734]);
}

#get information from DB about target player.
db("select login_id, login_name, clan_id from ${db_name}_users where login_id = $target");
$target = dbr();

if($GAME_VARS['sudden_death'] == 1 && $user['login_id'] != 1) { //SD check
	#ensure target isn't dead.
	db("select count(ship_id) from ${db_name}_ships where login_id = '$target[login_id]'");
	$count = dbr();

	if(!isset($count[0]) || (isset($count[0]) && $count[0] <= 0) ) {
		print_page($cw['sudden_death'],$st[735]);
	}

} elseif($user_ship['shipclass'] < 3){
	print_page($cw['Error'],$st[736]);
}


if(isset($do_ship)) { //user has selected stuff to transfer
	$num_ships = count($do_ship);
	$target_ship_count = ship_counter($target['login_id']);
	$estimated_cost = $num_ships * $cost_per_transfer;
	$loop_txt = "";
	$rs .= "<p /><a href='send_ship.php?target=$target[login_id]'>".$cw['tranfer_another_ship']."</a><br />";
	#var_dump($target_ship_count);

		db2("select * from user_accounts where login_id = '".$target['login_id']."' LIMIT 1");
		$user_transferto = dbr2();
		db2("select * from user_accounts where login_id = '".$user['login_id']."' LIMIT 1");
		$user_transferfrom = dbr2();

	if($user['cash'] < $estimated_cost) { # ensure have enough cash
		$text .=sprintf($st[737], $estimated_cost, $estimated_cost).$cw['credits'].".";
	} elseif($num_ships < 1){
		$text .= $st[738];
	} elseif($target_ship_count['other_reached'] == 1 && $target_ship_count['war_reached'] == 1){
		$text .= "<b class='b1'>$target[login_name]</b> ".$st[739];
	} elseif($user_transferfrom['last_ip'] == $user_transferto['last_ip']){
		$text .= sprintf($st[1890], $user_transferto['login_name']);
	} else { //can transfer ships.

		$transfer_counter = 0;


		//loops through the ships.
		foreach($do_ship as $ship_id) {
			if($ship_id == 1){ //safety check. Don't want to transfer an SD.
				continue;
			}

			db("select config REGEXP 'bs' as is_warship, ship_name, login_id, config from ${db_name}_ships where ship_id = '$ship_id'");
			$this_ship = dbr(1);

			if(empty($this_ship)){
				$loop_txt .= "$ship_id $st[740]<br />";
			} elseif($this_ship['login_id'] != $user['login_id']){ //not users ship
				$loop_txt .= "$this_ship[ship_name] $st[741]<br />";
				continue;
			} elseif(config_check("oo", $this_ship)){ //trying to transfer a flagship
				$loop_txt .= $st[742]."<br />";
				continue;
			} elseif($target_ship_count['warships'] >= $GAME_VARS['max_warships'] && $this_ship['is_warship'] == 1){
				$loop_txt .= "$target[login_name] $st[743]<br />";
				continue;
			} elseif($target_ship_count['other_ships'] >= $GAME_VARS['max_other_ships'] && $this_ship['is_warship'] == 0){
				$loop_txt .= "$target[login_name] $st[744]<br />";
				continue;
			} elseif($ship_id == $user_ship['ship_id']){
				$loop_txt .= $st[745];
			} else {
				$loop_txt .= "<b class='b1'>$this_ship[ship_name]</b> $st[746]<br />";

				dbn("update ${db_name}_ships set login_id = '$target[login_id]', fleet_id = '1', clan_id = $target[clan_id], metal=0, fuel=0, elect=0, colon=0 where ship_id = '$ship_id'");

				//ensure don't go over the limit
				if($this_ship['is_warship'] == 1){
					$target_ship_count['warships'] ++;
				} else {
					$target_ship_count['other_ships'] ++;
				}
				$transfer_counter ++;
			}
		}
		$text .= sprintf($st[747], $transfer_counter, $num_ships)."<p />".$loop_txt;

		if($transfer_counter > 0){
			$total_cost = $cost_per_transfer * $transfer_counter;
			$text .= "<p />$st[7480] $total_cost ".$cw['credits'];
			take_cash($total_cost);

			post_news("<b class='b1'>".sprintf($st[748], $user[login_name], $transfer_counter, $target[login_name]), "ship");
			send_message($target['login_id'],sprintf($st[749], $transfer_counter, $user[login_name]));
			insert_history($user['login_id'],sprintf($st[750], $transfer_counter, $target[login_name]));
		}
	}
	print_page($cw['transfer_ship'],$text);

}

$text .= sprintf($st[751], $target[login_name])."<br /><br />";
$text .= "<b class='b1'>".$st[752]."<br />";
$text .= "<form action=send_ship.php method=POST name=transfer_ships><table>";

db("select ship_name, class_name, location, fighters, max_fighters, shields, max_shields, armour, max_armour, config, ship_id from ${db_name}_ships where login_id = '$user[login_id]' && ship_id != '$user[ship_id]' order by class_name");
$ships = dbr(1);

if(!isset($ships)){	#ensure there are some ships to display
	$text .= $st[753];
} else {
	$text .= make_table(array("Nom du vaisseau","Type de vaisseau","Emplacement","Chasseurs","Boucliers", "Coques","Configuration"));
	while($ships) {
		$ships['fighters'] = $ships['fighters']." / ".$ships['max_fighters'];
		$ships['shields'] = $ships['shields']." / ".$ships['max_shields'];
		$ships['armour'] = $ships['armour']." / ".$ships['max_armour'];

		#remove the un-necassaries from the array. As well as their numerical counterparts (it's a multi-indexed array).
		unset ($ships['max_fighters']);
		unset ($ships['max_shields']);
		unset ($ships['max_armour']);

		$ships['ship_id'] = "<input type=checkbox name=do_ship[$ships[ship_id]] value=$ships[ship_id] /> - <a href='send_ship.php?target=$target[login_id]&do_ship[$ships[ship_id]]=$ships[ship_id]'>".$cw['sign_over']."</a>";
		$text .= make_row($ships);
		$ships = dbr(1);
	}
}

$text .= "</table><br /><input type=hidden name=target value=$target[login_id] /><input type='submit' name='submit' value='Envoyer les vaisseaux' /> - <a href=javascript:TickAll(\"transfer_ships\")>".$st[754]."</a><br /></form>";

$text .= "<br /><a href='send_ship.php?target=$target[login_id]'>".$st[755]."</a>";

print_page($cw['transfer_ship_registration'],$text);

?>