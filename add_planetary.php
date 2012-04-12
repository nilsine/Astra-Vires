<?php
/*
A script that contains the code for some of the planetary functions.
Created: 
By: Moriarty
Date: 14/6/02
Last audited: 23/5/04 by Moriarty
*/

require_once("user.inc.php");

$planet_id = (int)$_REQUEST['planet_id'];

db("select * from ${db_name}_planets where planet_id = '$planet_id'");
$planet = dbr(1);


$out = "";
$header = $cw['error'];
$status_bar_help = "?topic=Planets";

if($user['location'] != $planet['location']) {
	$out = $st[107];

} elseif($planet['login_id'] != $user['login_id']) {
	$out = $st[108];

#build a research facility
}elseif ($GAME_VARS['uv_num_bmrkt'] > 0 && isset($_REQUEST['research_fac'])) {
	$header = $cw['research_facility'];
	$status_bar_help = "?topic=Blackmarkets";

	#check to see how many research centres the user has.
	db("select count(planet_id) from ${db_name}_planets where research_fac = 1 && login_id = '$user[login_id]'");
	$num_research = dbr();
	if($user['cash'] < $research_fac_cost) {
		$out .= $st[109];
	}elseif(!avail_check(4000)){
		$out .= $st[110];
	} elseif($planet['research_fac'] != 0) {
		$out .= $st[111];
	} elseif($num_research[0] > 1) {
		$out .= $st[112];
	} elseif(!isset($_POST['sure'])) {
		get_var($cw['buy_research_facility'],$_SERVER['PHP_SELF'],sprintf($st[113], $GAME_VARS[hourly_tech]).popup_help("help.php?topic=Blackmarkets&sub_topic=Research_Facilities_and_Support_Units&popup=1", 500, 400, $cw['click_here']).".",'sure','yes');
	} else {
		take_cash($research_fac_cost);
		$out .= sprintf($st[114], $planet[planet_name], $research_fac_cost);
		dbn("update ${db_name}_planets set research_fac = '1' where planet_id = '$planet[planet_id]'");
	}

#build a shield generator
}elseif(isset($_REQUEST['shield_gen'])) {
	$header = $cw['shield_generator_construction'];
	if($user['cash'] < $shield_gen_cost) {
		$out .= $st[115];
	}elseif(!avail_check(3000)){
		$out .= $st[110];
	} elseif($planet['shield_gen'] > 0) {
		$out .= $st[116];
	} elseif(!isset($sure)) {
		get_var($cw['buy_shield_generator'],'add_planetary.php',$st[117].popup_help("help.php?topic=Planets&popup=1&sub_topic=Shield_Generators", 400, 220, $cw['click_here']).".",'sure','yes');
	} else {
		take_cash($shield_gen_cost);
		$out .= sprintf($st[118], $planet[planet_name], $shield_gen_cost);
		dbn("update ${db_name}_planets set shield_gen = '3' where planet_id = '$planet[planet_id]'");
	}

} else {
	$out = $st[119];
}

print_page($header,$out."<p /><a href='planet.php?planet_id=$planet_id'>".$st[120]."</a>");
?>