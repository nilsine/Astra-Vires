<?php
require_once("user.inc.php");

ship_status_checker();


db("select port_id from ${db_name}_ports where location = '$user[location]'");
$ports = dbr(1);
if($user['location'] != 1 && !$ports){
	print_page($st[321]);
}

if($user['location'] == 1){
	$rs = "<p /><a href='earth.php'>".$cw['return_to_earth']."</a>";
} else {
	$rs = "<p /><a href='port.php'>".$cw['back_to_the_port']."</a>";
}
#percentage new bids must increase over old ones.
$rate = 5;

#work out the number of seconds an item is to remain in bilkos for.
$bilkos_seconds = $GAME_VARS['bilkos_time'] * 3600;

$text .= $st[322];
db("select count(item_id),item_type from ${db_name}_bilkos where active = 1 && timestamp + $bilkos_seconds > ".time()." group by item_type");

for($i=1;$i<=5;$i++){
	$count=dbr();
	if($count['item_type']){
		$out[$count['item_type']] = $count[0];
	}
}

for($i=1;$i<=5;$i++){
	if(!$out[$i]){
		$out[$i] = 0;
	}
}

$text .= "<br /><a href='bilkos.php?view=1'>Vaisseaux</a> - (<b>$out[1]</b>)";
$text .= "<br /><a href='bilkos.php?view=2'>Equipements</a> - (<b>$out[2]</b>)";
$text .= "<br /><a href='bilkos.php?view=3'>Améliorations</a> - (<b>$out[3]</b>)";
$text .= "<br /><a href='bilkos.php?view=4'>Divers</a> - (<b>$out[4]</b>)";
$text .= "<br /><a href='bilkos.php?view=5'>Planètes</a> - (<b>$out[5]</b>)";


db("select count(item_id) from ${db_name}_bilkos where active=0 && bidder_id='$user[login_id]'");
$do_now=dbr();

if($do_now[0] > 0 && !$show_won && !$collect){
	$text .= "<p /><a href='bilkos.php?show_won=1'>".$cw['collect_item']."(s)</a> - (<b>$do_now[0]</b>)";
}

$text .= "<p />";

/*
upgrades
up2 = terra maelstrom
all below require 1 free upgrade slot:
upbs = battleship
upfig600 = increase fighter cap by 600
upfig1500 = increase fighter cap by 1500
upattack = +200 shield cap. +600 fighter cap.

equip
warpack = 2alpha + 4 gammas
deltabomb = 1 Delta Bomb

planetary
4-9 = shield capacity =X000, charge rate = *X

misc
10 - 80 = X turns.
*/

if(isset($collect)){
	db("select item_type,bidder_id,item_code,active from ${db_name}_bilkos where item_id = $collect");
	$item=dbr(1);
	$all_done = 0;
	if($user['login_id'] != $item['bidder_id']){
		$text .= $st[323];
	} elseif($item['active'] == 1) {
		$text .= $st[324];
	} elseif($user['location'] != 1) {
		$text .= $st[325];
	} else {
		if($item['item_type'] == 1){ #ships
			$num_ships = ship_counter($user['login_id']);

			$item['item_code'] = str_replace("ship","",$item['item_code']);
			db("select * from se_ship_types where type_id = '$item[item_code]'");
			$ship_stats=dbr(1);

			if(config_check("bs",$ship_stats)){
				$w_ship = 1;
					$num_ships['applic'] = $num_ships['warships'];
			}else {
				$num_ships['applic'] = $num_ships['other_ships'];
				$w_ship = 0;
			}

			if ($num_ships['war_reached'] == 1 && $w_ship == 1) { # check to ensure they are not trying to buy too many warships
				$text .= sprintf($st[326], $num_ships[warships], $GAME_VARS[max_warships]);
			}elseif ($num_ships['other_reached'] == 1 && $w_ship == 0) { # check to ensure they are not trying to buy too many other ships
				$text .= sprintf($st[327], $num_ships[other_ships], $GAME_VARS[max_other_ships]);
			// test incohérent
			//}elseif(config_check("na",$user_ship)){
			//	$text .= "You may not install a battleship upgrade on a ship that has the na config (Cannot attack).";

			} else {

				if(empty($user_ship['fleet_id']) || $user_ship['fleet_id'] < 1){
					$user_ship['fleet_id'] = 1;
				}

				#delete old EP's
				dbn("delete from ${db_name}_ships where login_id = '$user[login_id]' && class_name REGEXP 'Escape'");

				$q_string = "insert into ${db_name}_ships (";
				$q_string = $q_string . "ship_name, login_id, clan_id, shipclass, class_name, class_name_abbr, fighters, max_fighters, max_shields, armour, max_armour, cargo_bays, mine_rate_metal, mine_rate_fuel, config,size, point_value, upgrade_slots, num_pc, num_ew, fleet_id";
				$q_string = $q_string . ") values(";
				$q_string = $q_string . "'$ship_stats[name]', '$user[login_id]', '$user[clan_id]', '$item[item_code]', '$ship_stats[name]', '$ship_stats[class_abbr]', '$ship_stats[fighters]', '$ship_stats[max_fighters]', '$ship_stats[max_shields]', '$ship_stats[max_armour]', '$ship_stats[max_armour]', '$ship_stats[cargo_bays]', '$ship_stats[mine_rate_metal]', '$ship_stats[mine_rate_fuel]', '$ship_stats[config]', '$ship_stats[size]', '$ship_stats[point_value]', '$ship_stats[upgrade_slots]', '$ship_stats[num_pc]', '$ship_stats[num_ew]', '$user_ship[fleet_id]')";
				dbn($q_string);
				$user['ship_id'] = mysql_insert_id();

				db("select * from ${db_name}_ships where ship_id = '$user[ship_id]'");
				$user_ship = dbr(1);
				dbn("update ${db_name}_users set ship_id = '$user[ship_id]' where login_id = '$user[login_id]'");
				$text .= sprintf($st[328], $ship_stats[name]);
				$all_done = 1;
			}
		} elseif($item['item_type'] == 3){ #upgrades
			if($item['item_code'] == "up2"){ #terra maelstrom
				if(config_check("sw",$user_ship)) {
					$text .= $st[329];
				} elseif(config_check("sv",$user_ship)) {
					$text .= $st[330];
					$user_ship['config'] = str_replace("sv","sw",$user_ship['config']);
					dbn("update ${db_name}_ships set config = '$user_ship[config]' where ship_id = '$user_ship[ship_id]'");
					$all_done = 1;
				} else {
					$text .= $st[331];
				}
			} elseif($user_ship['upgrade_slots'] < 1) { #Ensure enough free slots.
				$text .= $st[332];
			} elseif($item['item_code'] == "upbs"){ #battleship upgrade

				$num_ships = ship_counter($user['login_id']);

				if(config_check("bs",$user_ship)){
					$text .= $st[333];
				} elseif ($num_ships['war_reached'] == 1) { # check to ensure they are not trying to create too many warships
					$text .= sprintf($st[334], $GAME_VARS[max_warships]);
				} else {
					$text .= sprintf($st[335], $max_non_warship_fighters);
					$user_ship['config'] = $user_ship['config'].",bs";
					dbn("update ${db_name}_ships set config = '$user_ship[config]', upgrade_slots = upgrade_slots -1 where ship_id = '$user_ship[ship_id]'");
					$all_done = 1;
				}
			} elseif($item[item_code] == "fig600"){ #600 fig cap
				if($user_ship[max_fighters] + 600 > $max_non_warship_fighters && !config_check("bs",$user_ship)) {
					$text .= sprintf($st[336], $max_non_warship_fighters);
				} else {
					$text .= "Here's 600 more Fighter Capacity.";
					$user_ship[max_fighters] = $user_ship[max_fighters] + 600;
					dbn("update ${db_name}_ships set max_fighters = '$user_ship[max_fighters]', upgrade_slots = upgrade_slots -1 where ship_id = '$user_ship[ship_id]'");
					$all_done = 1;
				}
			} elseif($item[item_code] == "fig1500"){ #1500 fig cap
				if($user_ship[max_fighters] + 1500 > $max_non_warship_fighters && !config_check("bs",$user_ship)) {
					$text .= sprintf($st[337], $max_non_warship_fighters);
				} else {
					$text .= "Here's 1500 more Fighter Capacity.";
					$user_ship[max_fighters] = $user_ship[max_fighters] + 1500;
					dbn("update ${db_name}_ships set max_fighters = '$user_ship[max_fighters]', upgrade_slots = upgrade_slots -1 where ship_id = '$user_ship[ship_id]'");
					$all_done = 1;
				}
			} elseif($item[item_code] == "attack_pack"){ #attack pack
				if(config_check("sj",$user_ship)){
					$text .= $st[338];
				} elseif($user_ship[max_fighters] + 600 > $max_non_warship_fighters && !config_check("bs",$user_ship)) {
					$text .= sprintf($st[339], $max_non_warship_fighters);
				} else {
					$text .= $st[340] ;
					$user_ship[max_fighters] = $user_ship[max_fighters] + 600;
					$user_ship[max_shields] = $user_ship[max_shields] + 200;
					dbn("update ${db_name}_ships set max_fighters = '$user_ship[max_fighters]',max_shields = '$user_ship[max_shields]', upgrade_slots = upgrade_slots -1 where ship_id = '$user_ship[ship_id]'");
					$all_done = 1;
				}
			}

		} elseif($item[item_type] == 2){ #equipment
			if($item[item_code] == "warpack"){
				$text .= $st[341];
				dbn("update ${db_name}_users set gamma = gamma+'4', alpha=alpha+2 where login_id = '$item[bidder_id]'");
				$all_done = 1;
			}elseif($item[item_code] == $cw['deltabomb']){ #delta bomb
				if($user[delta] == 1){
					$text .= $st[342];
				} else {
					$text .= $st[343];
					dbn("update ${db_name}_users set delta = 1 where login_id = '$item[bidder_id]'");
					$all_done = 1;
				}
			}
		} elseif($item[item_type] == 4){ #misc
			if($item[item_code] >9 && $item[item_code] < 101){
				if($user['turns'] + $item['item_code'] > $GAME_VARS['max_turns']){
					$text .= $st[344];
				} else {
					$text .= sprintf($st[345], $item[item_code]);
					$user[turns] += $item[item_code];
					dbn("update ${db_name}_users set turns = turns+'$item[item_code]' where login_id = '$item[bidder_id]'");
					$all_done = 1;
				}
			}
		} elseif($item[item_type] == 5){ #Planetary
			if($destination){
				db("select login_id, shield_gen, planet_name, planet_id from ${db_name}_planets where planet_id = '$destination'");
				$planets=dbr(1);
				if($planets[login_id] != $user[login_id]){
					$text .= "That Planet does not belong to you.";
				} elseif($item[item_code] >3 && $item[item_code] <10) {
					if($planets[shield_gen] > 3) {
						$text .= $st[346];
					}
					$charge_cap = $item[item_code] * 1000;
					$text .= sprintf($st[347], $planets[planet_name], $item[item_code], $charge_cap, $item[item_code]);
					dbn("update ${db_name}_planets set shield_gen = '$item[item_code]' where planet_id = '$planets[planet_id]'");
					$all_done = 1;
				}
			} else {
				db("select planet_name,planet_id from ${db_name}_planets where planet_id != 1 && login_id = '$user[login_id]'");
				$planets=dbr(1);
				if(!$planets){
					$text .= $st[348];
				} else {
					$text .= $st[349];
					$text .= "<form method=post action=bilkos.php name=despatch_form>";
					$text .= "<input type=hidden name=collect value=$collect />";
					$text .= "<select name=destination>";
					while($planets){
						$text .= "<option value=$planets[planet_id]> $planets[planet_name] ";
						$planets=dbr(1);
					}
					$text .= "</select>";
					$text .= "<p /><input type='submit' value='".$cw['install']."' /></form><p />";
				}
			}
		}

		if($all_done==1){ #remove lot from auction
			dbn("delete from ${db_name}_bilkos where item_id = $collect");
		}
	}

} elseif(isset($bid)){
	db("select * from ${db_name}_bilkos where item_id = $bid");
	$item=dbr(1);
	if($item[active] = 0){
		$text .= $st[350];
	} elseif($item[timestamp] + $bilkos_seconds < time()){
		$text .= $st[351];
	} elseif($new_bid){
		settype($new_bid, "integer");
		$new_price = round(($item[going_price] /100) * $rate) + $item[going_price];
		if($new_bid > $user[cash]){
			$text .= $st[352];
		} elseif($new_price > $new_bid) {
			$text .= sprintf($st[353], $rate, $new_price);
		} elseif($new_bid < 1) {
			$text .= $st[354];
		} else {
			if($item['bidder_id'] > 0){
				dbn("update ${db_name}_users set cash= cash + '$item[going_price]' where login_id='$item[bidder_id]'");
				dbn("insert into ${db_name}_messages (timestamp,sender_name, sender_id, login_id, text) values(".time().",'Bilkos','$user[login_id]','$item[bidder_id]','Your bid on the <b class=\'b1\'>$item[item_name]</b> has been beaten by <b class=\'b1\'>$user[login_name]</b> who has put a new bid of <b>$new_bid</b> Credits on the item.<p />The lot will remain open for a further <b>$GAME_VARS[bilkos_time] hrs</b>. If there are no new bidders, then <b class=\'b1\'>$user[login_name]</b> will take the lot.<p />You have been refunded the money you deposited on the lot.')");
			}
			dbn("update ${db_name}_bilkos set timestamp=".time().", bidder_id=$user[login_id],going_price = '$new_bid' where item_id = '$bid'");
			take_cash($new_bid);
			$text .= sprintf($st[355], $GAME_VARS[bilkos_time], $item[item_name]);
		}
	} else {
		$new_price = round(($item[going_price] /100) * $rate) + $item[going_price];
		$text .= sprintf($st[356], $item[item_name], $item[going_price], $new_price);
		$text .= "<form method=post action=bilkos.php name=bid_form>";
		$text .= "<input type=hidden name=bid value=$bid />";
		$text .= "<input type=text name=new_bid size=10 />";
		$text .= " - <input type='submit' value=".$cw['bid']." /></form><p />";
		$rs = "<a href='bilkos.php'>Back to Bilkos</a>";
		print_page($st[357],$text);
	}
} elseif($view){ #Show all items in a particular catagory.
	$text .= $st[358]."<p />";
	db2("select item_name,descr,timestamp,going_price,bidder_id,item_id from ${db_name}_bilkos where item_type = $view && active=1 && timestamp + '$bilkos_seconds' > ".time()." order by timestamp asc, item_name asc");
	$items=dbr2(1);

	if(!$items){
		$text .= $st[359];
	} else {
		$text .= make_table(array($cw['item_name'],$cw['description'],$cw['open_till'],$cw['present_price'],$cw['present_bidder']));
		while($items) {
			$items['going_price'] = $items['going_price'];
			if($items['bidder_id'] > 0){
				db("select login_name,login_id,clan_sym,clan_sym_color from ${db_name}_users where login_id = $items[bidder_id]");
				$bidder=dbr(1);
				$items['bidder_id'] = print_name($bidder);
				$items['timestamp'] = date( "M d - H:i",$items['timestamp']+$bilkos_seconds);
			} else {
				$items['bidder_id'] = "None Yet";
				$items['timestamp'] = date( "M d - H:i",$items['timestamp'] + ($bilkos_seconds * 2) );
			}
			$items['item_id'] = " - <a href='bilkos.php?bid=$items[item_id]'>".$cw['bid']."</a>";
			$text .= make_row($items);
			$items = dbr2(1);
		}
		$text .= "</table>";
	}

}
if($show_won) { #Show items user has won.
	db2("select item_name,item_type,item_id from ${db_name}_bilkos where active=0 && bidder_id='$user[login_id]'");
	$collect=dbr2(1);
	if($collect){
		$text .= "<p />";
		$text .= $st[360];

		$text .= make_table(array("Item Name","Item Type"));
		while($collect) {
			if($collect[item_type] == 1){
				$collect[item_type] = $cw['ship'];
			} elseif($collect[item_type] == 2){
				$collect[item_type] = $cw['equipment'];
			} elseif($collect[item_type] == 3){
				$collect[item_type] = $cw['upgrade'];
			} elseif($collect[item_type] == 4){
				$collect[item_type] = $cw['misc'];
			} elseif($collect[item_type] == 5){
				$collect[item_type] = $cw['planetary'];
			}
			$collect[item_id] = " - <a href='bilkos.php?show_won=1&collect=$collect[item_id]'>Collect</a>";
			$text .= make_row($collect);
			$collect = dbr2(1);
		}
		$text .= "</table>";
	}

}

print_page($st[361],$text);

?>
