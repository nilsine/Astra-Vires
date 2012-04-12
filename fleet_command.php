<?php

/************************************************************
*			Fleet command Script
* Created
* By: Moriarty
* On: 22/2/03
************************************************************/

require_once("user.inc.php");


ship_status_checker();


//$output_str = "Due to a recent bungle by the head of Sol Fleet Administration, Fleet Command is not yet fully functional. <br />We appologise for the Administrators Ineptness, and will have the service functioning ASAP.<p />";


if(isset($fleet_id_2) && $fleet_id_2 != 0){
	$fleet_id = $fleet_id_2;
}


/*************
* Change Fleets
**************/
if(isset($fleet_manip) && ($fleet_manip == 0 || $fleet_manip == 1)) {

	$output_str .= "<br />".change_fleet_num($fleet_id,$fleet_manip,$do_ship,$cw['ship_id']);


/*************
* Destroy Ships
**************/
}elseif(isset($fleet_manip) && $fleet_manip == 2) {
	$rs .= "<br /><a href='fleet_command.php'>".$cw['back_to_fleet_command']."</a>";
	if(!$do_ship && $fleet_id == 0){
		print_page($cw['self_destruct'],$st[563]);	
	}


	//blow up a fleet
	if(isset($fleet_id) && $fleet_id != 0) {
		if($user_ship['fleet_id'] == $fleet_id){
			print_page($cw['self_destruct'],$st[564]);
		}
		db("select count(*) from ${db_name}_ships where fleet_id = '$fleet_id' && login_id = '$user[login_id]'");
		$target_ship = dbr();
		$maths = $target_ship[0];

	//blow up selected ships
	} else {
		$maths = count($do_ship);
	}

	$total_cost = $maths * $cost_to_destroy;

	if($user['cash'] < $total_cost) {
		print_page($cw['self_destruct'],sprintf($st[565], $cost_to_destroy));

	} elseif(!isset($sure)) {
		$output_str .= sprintf($st[566], $maths);
		$output_str .= "<form name=destroy_ships action=fleet_command.php method=POST>";
		$i=0;

		#print hidden form data that tells whether fleet or not-fleet destruction.
		if($fleet_id != 0) {
			$output_str .= "<input type=hidden name=fleet_id value='$fleet_id' />";			
		} else {
			foreach($do_ship as $var) {
				$output_str .= "<input type=hidden name=do_ship[$i] value='$var' />";
				$i++;
			}
		}

		$output_str .= '<input type=hidden name=fleet_manip value=2 />';
		$output_str .= '<input type=hidden name=sure value=yes />'; 
		$output_str .= "<input type='submit' name='submit' value='".$cw['yes'] ."' /> <input type='Button' width='30' value='".$cw['no']."' onclick='javascript: history.back()' /> </form>";
		print_page($cw['Sure?'],$output_str);

	} else {
		if(isset($fleet_id) && $fleet_id != 0) {
			dbn("delete from ${db_name}_ships where fleet_id = '$fleet_id' && login_id = '$user[login_id]'");
		} else {
			$del_str = "";
			foreach($do_ship as $var) {
				$del_str .= "ship_id = '$var' || ";
			}
			$del_str = preg_replace("/\|\| $/", "", $del_str);
			dbn("delete from ${db_name}_ships where login_id = '$user[login_id]' && ship_id != '$user[ship_id]' && (".$del_str.")");
		}
//		post_news(sprintf($st[566], $user[login_name], $maths), $cw['ship']);
		take_cash($total_cost);

		if($maths > 100){
			$out = $st[568];
			give_cash(10);
		} elseif($maths > 60){
			$out = $st[569];
			give_cash(5);
		} elseif($maths > 30){
			$out = $st[570];
		} elseif($maths > 10){
			$out = $st[571];
		} else {
			$out = $st[572];
		}
	
		$output_str .= sprintf($st[573], $maths, $total_cost).$out."<br /><br />";
	}
}




/*************
* Default page
**************/


#find out the basic stats of the fleet.
db("select count(ship_id) as ships, count(distinct shipclass) as types, count(distinct fleet_id) as fleets, sum(cargo_bays) as cargo_cap from ${db_name}_ships where login_id = '$user[login_id]'");
$fleet_count = dbr();

#user has no ships
if(!$fleet_count){
	print_page($cw['fleet_command'],$st[574]);
}


if(isset($sort_ships)){
	if($sorted_ships==1){
		$going = "asc";
		$sorted_ships=2;
	} else {
		$going = "desc";
		$sorted_ships=1;
	}

	$select_ships_sql = "select ship_name, class_name_abbr, location, shipclass, fighters ,max_fighters, shields, max_shields, armour, max_armour, cargo_bays, metal, fuel, elect, colon, fleet_id, clan_fleet_id, config, ship_id from ${db_name}_ships where login_id = '$user[login_id]' order by '$sort_ships' $going";
} else {
	$select_ships_sql = "select ship_name, class_name_abbr, location, shipclass, fighters ,max_fighters, shields, max_shields, armour, max_armour, cargo_bays, metal, fuel, elect, colon, fleet_id, clan_fleet_id, config, ship_id from ${db_name}_ships where login_id = '$user[login_id]' order by  location asc, fleet_id asc, fighters desc, ship_name asc";
	$sorted_ships=1;
}


/*************
* List User Ships
**************/
if($ship_listing != -1) {

	$output_str.= sprintf($st[575], $fleet_count[ships], $fleet_count[types], $fleet_count[fleets], $fleet_count[cargo_cap]);

	#ensure have enough ships to bother with allowing fleet movements and stuff.
	if($fleet_count['ships'] > 1) {
		$output_str .= "<FORM method='post' action='fleet_command.php' name='fleet_specials'>";

	}

	//$table_head_array = array("<a href='fleet_command.php?sort_ships=ship_name&sorted_ships=$sorted_ships'>".$cw['ship_name']."","<a href='fleet_ command.php?sort_ships=class_name_abbr&sorted_ships=$sorted_ships'>".$cw['ship_class']."</a>","<a href='fleet_command.php?sort_ships=location&sorted_ships=$sorted_ships'>".$cw['location']."</a>","<a href='fleet_command.php?sort_ships=fighters&sorted_ships=$sorted_ships'>".$cw['fighters']."</a>","<a href='fleet_command.php?sort_ships=shields&sorted_ships=$sorted_ships'>".$cw['shields']."</a>","<a href='fleet_command.php?sort_ships=armour&sorted_ships=$sorted_ships'>".$cw['armour']."</a>","<a href='fleet_command.php?sort_ships=cargo_bays&sorted_ships=$sorted_ships'>".$cw['cargo_bays']."</a>","<a href='fleet_command.php?sort_ships=fleet_id&sorted_ships=$sorted_ships'>".$cw['fleet']."</a>","<a href='fleet_command.php?sort_ships=clan_fleet_id&sorted_ships=$sorted_ships'>".$cw['can_fleet']."</a>","<a href='fleet_command.php?sort_ships=config&sorted_ships=$sorted_ships'>".$cw['upgrades']."</a>",$cw['specials'],'&nbsp;');
    // suppression du tri par colonne
    $table_head_array = array($cw['ship_name'], $cw['ship_class'], $cw['location'], $cw['fighters'], $cw['shields'], $cw['armour'], $cw['cargo_bays'], $cw['fleet'], $cw['can_fleet'], $cw['upgrades'], $cw['specials'],'&nbsp;');

	if($user['clan_id'] < 1 || $GAME_VARS['clan_fleet_attacking'] == 0){
		unset($table_head_array[8]);
	}

	$ship_listing = checkbox_ship_list($select_ships_sql, 1);

	$output_str .= $ship_listing;

	if($fleet_count['ships'] > 1) {
		$output_str .= "<h3>Pour les vaisseaux sélectionnés :</h3>";

		//$output_str .= "<br /><br /><FORM method='post' action='fleet_command.php' name='fleet_specials'>";
		$output_str .= "<input type='radio' name='fleet_manip' value='0' id='changement2' checked />&nbsp;<label for='changement2'>".$cw['changement_flotte'] . "</label> vers la ";
		$output_str .= strtolower($st[577])." <input type='text' name='fleet_id' value='' max='3' size='3' class='inputtext' /><br />";
		$output_str.= "<input type='radio' name='fleet_manip' value='2' id='destruction2' />&nbsp;<label for='destruction2'>".$cw['self_destruct']."</label> ".$st[1984]."<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;";
		
		if($user['clan_id'] > 0 && $GAME_VARS['clan_fleet_attacking'] == 1){
			$output_str .= $cw['clan_fleet']."<input TYPE='radio' NAME='fleet_manip' value=1 />";
		}
		$output_str.= "<input type='submit' value=".$cw['process']." /><br />";
		$output_str .= "</form><p />";
	}

} else {
	$output_str .= $st[578];

}

$ship_stuff = "";
if($user_ship['empty_bays'] != $user_ship['cargo_bays']) {
	$ship_stuff .= q_row("<a href='location.php?jettison=1'>".$cw['jettison_cargo']."</a>", "l_col");
}

if(config_check("er", $user_ship)) {
	$ship_stuff .= q_row("<a href='location.php?emergency_return=1'>".$cw['emergency_return']."!</a>", "l_col");
}

if($user['genesis'] > 0) {
	$ship_stuff .= q_row("<a href='planet_build.php?location=$user[location]'>".$cw['use_genesis_device']."</a>&nbsp;(<b>$user[genesis]</b>)", "l_col");
}

if($user['gdt'] > 0) {
	$ship_stuff .= q_row("<a href='location.php?tempo=1'>".$cw['deployer_bulle_tempo']."</a>&nbsp;(<b>".$user['gdt']."</b>)", "l_col");
}

if($user['alpha'] > 0) {
	$ship_stuff .= q_row("<a href='bombs.php?alpha=1'>".$cw['deploy_alpha_bomb']."</a>&nbsp;(<b>$user[alpha]</b>)", "l_col");
}
if($user['gamma'] > 0) {
	$ship_stuff .= q_row("<a href='bombs.php?bomb_type=1'>".$cw['detonate_gamma_bomb']."</a>&nbsp;(<b>$user[gamma]</b>)", "l_col");
}
if($user['delta'] > 0) {
	$ship_stuff .= q_row("<a href='bombs.php?bomb_type=2'>".$cw['delta_bomb_purge']."</a>!", "l_col");
}

//only make the table if it will have content
if(!empty($ship_stuff)){
	$output_str .= "<p />".make_table2(array($cw['ship_equipement_functions']), "s_funcs");
	$output_str .= $ship_stuff."</table>";
}


print_page($cw['fleet_command'],$output_str);
?>
