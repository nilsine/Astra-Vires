<?php
require("user.inc.php");

$status_bar_help = "?topic=Planets";

ship_status_checker();

mt_srand((double)microtime()*1000000);
$planet_img = mt_rand(1,15);

//maximum planetary population.
$max_pop = mt_rand(14000000,15000000);
$error_str = "";

// checks
get_star();
if($user['genesis'] < 1) {
	$error_str .= $st[88];
} elseif($star['planetary_slots'] < 1) {
	$error_str .= $st[89];
} elseif($user['location'] == 1) {
	$error_str = $st[90];
} elseif($user['ship_id'] <= 1 && $user['login_id'] > 1) {
	$error_str = $st[91];
} elseif($star['event_random'] > 0 && $user['login_id'] != 1) {
	$error_str = $st[92];
} elseif ($user['turns_run'] < $GAME_VARS['turns_before_planet_attack'] && !isset($letme) && $user['login_id'] != 1) {
	print_page($cw['no_landing'],sprintf($st[93], $GAME_VARS[turns_before_planet_attack]));
} elseif($user['turns'] < 5) {
	$error_str = $st[94];
} elseif(empty($planet_name)) {
	get_var($st[95],'planet_build.php',$st[96],'planet_name','');
} elseif(strlen($planet_name) < 3) {
	$rs = "<p /><a href='javascript:history.back()'>".$cw['try_again']."</a>";
	print_page($cw['invalide_name'],$st[97]);
} else {
	$planet_name = correct_name($planet_name);
	if(!$planet_name || $planet_name == " " || $planet_name == "") {
		$rs = "<p /><a href='javascript:history.back()'>".$cw['try_again']."</a>";
		print_page($cw['invalide_name'],$st[98]);
	}

	// remove gen device, but not from admin.
	if($user['login_id'] > 1){
		dbn("update ${db_name}_users set genesis = genesis - 1 where login_id = $user[login_id]");
	}
	charge_turns(5);

	if($user['clan_id']) {
		$clan_id = $user['clan_id'];
	} else {
		$clan_id = -1;
	}
	

	// build the new planet 
	dbn("insert into ${db_name}_planets (planet_name,location,login_id,login_name,clan_id,planet_img, max_population) values ('$planet_name', '$user[location]', '$user[login_id]', '$user[login_name]', '$clan_id', '$planet_img', '$max_pop')");
	$last_planet = mysql_insert_id();

	dbn("update ${db_name}_stars set planetary_slots = planetary_slots - 1 where star_id = $star[star_id]");

	post_news(sprintf($st[99], $user[login_name], $planet_name), $cw['planet']);
	$error_str .= sprintf($st[100], $last_planet);
}

print_page($cw['planet_built'],$error_str);
?>
