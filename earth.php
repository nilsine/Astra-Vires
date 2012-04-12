<?php
/********
* Script containing Earth related functions
* //Last audited: 23/5/04 by Moriarty
********/

require_once("user.inc.php");

//am allowed onto this page if dead but not SD.
ship_status_checker(1);


$rs = "<p /><a href='$_SERVER[PHP_SELF]'>".$cw['return_to_earth']."</a>";
$out = "";


//ensure user is in system 1 before continuing.
if ($user['location'] != 1) {
	$rs = "";
	print_page($cw['not_in_sol'],$st[429]);
}


//load fleet with colonists.
if(isset($_REQUEST['all_colon'])){

	if(!avail_check(4001)){ //ensure item has been 'developed'
		$out .= $st[430];
	} else {
		$out .= fill_fleet($cw['colon'], $st[431], $cw['colonists'], $GAME_VARS['cost_colonist'], 1)."<p />";
		empty_bays($user_ship);
	}

//individual ship load
} elseif(isset($_REQUEST['colonist'])) {
	$fill = 0;
	if($user['cash'] < ($user_ship['empty_bays'] * $GAME_VARS['cost_colonist'])){
		$fill = floor($user['cash']/$GAME_VARS['cost_colonist']);
	} else {
		$fill = (int)$_REQUEST['amount'];
	}

	$amount = (int)$_POST['amount'];
	if(!avail_check(4001)){
		$out .= $st[432];

	} elseif($user['turns'] < 1) {
		$out .= $st[433];

	} elseif($amount <= 0) {
		get_var('Take Colonists',$_SERVER['PHP_SELF'],"<a href='$_SERVER[PHP_SELF]?all_colon=1'>Fill Ship</a><p />Combien de colons voulez-vous acheter ?<br />Chaque colon coûte <b>$GAME_VARS[cost_colonist]</b> crédits.<p />",'amount',$fill);

	} elseif($fill < 1) {
		$out .= $st[1892]."<p />";

	}elseif($amount > $user_ship['empty_bays']) {
		$out .= "You can't carry that many colonists.<p />";

	} elseif(($amount * $GAME_VARS['cost_colonist']) > $user['cash']) {
		$out .= "You can't afford that many colonists.<p />";

	} else {
		take_cash($GAME_VARS['cost_colonist'] * $amount);
		charge_turns(1);
		dbn("update ${db_name}_ships set colon = colon + '$amount' where ship_id = '$user[ship_id]'");
		$user_ship['colon'] += $amount;
		$user_ship['empty_bays'] -= $amount;
	}


//the ship shop. listing ships
} elseif(isset($_GET['ship_shop'])) {
	$out .= $st[1800].'<br /><br />';

	$array_ships = array("Cargo" => "", "Bataille" => "", "Transport" => "", "Modulaires" => "", "Autres" => "",);

	$ship_types = load_ship_types(0);
	foreach($ship_types as $type_id => $ship_stats){
		if($type_id < 3 || $ship_stats['tcost'] != 0){//skip the EP and SD, as well as BM ships.
			continue;
		} else{

			$buy_many_link = "<a href='ship_build.php?mass=$type_id'>".$cw['buy_many']."</a>";

			if($ship_stats['type'] == "Cargo") {
				$type = "Cargo";

			} elseif($ship_stats['type'] == "Bataille" || $ship_stats['type'] == 'Flagship') {
				$type = "Bataille";

			} elseif($ship_stats['type'] == "Modulaire") {
				$type = "Modulaires";

			} elseif(eregi("Transport",$ship_stats['type'])) {
				$type = "Transport";

			} else {
				$type = "Autres";

				//A special ship
				if(config_check("oo", $ship_stats)) {
					$buy_many_link = "";

					//had a brob before, so this one costs more.
					if($user['one_brob'] > 0){
						$ship_stats['cost'] = $ship_stats['cost'] * $user['one_brob'];
					}
				}
			}
			$ship_stats['cost'] = nombre($ship_stats['cost'], 0, ',', ' ');
			$array_ships[$type] .= "\n".make_row(array("<a href='ship_build.php?ship_type=$type_id'>$ship_stats[name]</a>", "$ship_stats[class_abbr]","<b>$ship_stats[cost]</b>", "<a href='ship_build.php?ship_type=$type_id'>".$cw['buy_one']."</a>", $buy_many_link, popup_help("help.php?popup=1&ship_info=$type_id&db_name=$db_name",300,600)."<b></b></a>"));
		}
	}

	foreach($array_ships as $type => $content){
		if(($type == 'Bataille') || ($type == 'Transport')){
			$out .= " Vaisseaux de <b class='b1'>$type</b>".make_table(array($cw['ship_name'],$cw['abbrv'],$cw['cost']));
		}
		else
		{
			$out .= " Vaisseaux <b class='b1'>$type</b>".make_table(array($cw['ship_name'],$cw['abbrv'],$cw['cost']));
		}


		$out .= $content."</table><p />";
	}


	$out .= "<p /><a href='help.php?ship_info=-1' target='_blank'>".$st[1891]."</a>";

//load the default earth page
} else {
	if($user_options['show_pics']){
		//$out .= " <img src='$directories[images]/places/earth.jpg' alt='A Picture of Earth' /><br />";
	}

	//$out .= "<b>EARTH</b> - <b class='b1'>E</b>normous <b class='b1'>A</b>nd <b class='b1'>R</b>ound <b class='b1'>T</b>erran <b class='b1'>H</b>omeworld<p />";
	$out .= "<div><div style='float:left;padding:6px;'><a href='$_SERVER[PHP_SELF]?ship_shop=1'><img src='images/interface/ship_shop.jpg' border=0></a><br>";
	$out .= "<a href='$_SERVER[PHP_SELF]?ship_shop=1'>".$cw['seatogu']."</a>";
	
	if($user['ship_id'] != 1){
		$out .= " - <a href='ship_build.php?duplicate=1'>".$cw['ship_duplicator']."</a>";
	}
	
	$out .= "</div>";

	//$out .= "- <a href='new_ship.php?templates=1'>Templates</a><br />";

	#user is only able to access bilkos, seatogus & bobs if user has no ship (admin excempt)
	if($user['ship_id']!= 1 || $user['login_id'] == 1){
		$out .= "<div style='padding:6px;'><a href='equip_shop.php'><img src='images/interface/equipment_shop.jpg' border=0></a><br><a href='equip_shop.php'>".$cw['wally']."</a></div></div>";
		$out .= "<div><div style='float:left;padding:6px;'><a href='upgrade.php'><img src='images/interface/upgrade_store.jpg' border=0></a><br><a href='upgrade.php'>".$cw['vladimir']."</a></div>";
	}
	
	$out .= "<div style='padding:6px;'><a href='bilkos.php'><img src='images/interface/bilko.jpg' border=0></a><br><a href='bilkos.php'>".$st[788]."</a></div></div>";

	if(($user['ship_id']!= 1 || $user['login_id'] == 1) && avail_check(4001)){
		$out .= "<div style='padding:6px;'><a href='earth.php?colonist=1'><img src='images/interface/ville.jpg' border=0></a><br><a href='earth.php?colonist=1'>".$cw['colonist_recruitment']."</a> - <a href='earth.php?all_colon=1'>".$cw['fill_fleet']."</a></div>";
	}

	$rs = "<p /><a href='location.php'>".$cw['takeoff']."</a><br />";
}

print_page($cw['welcome_earth'], $out);
?>
