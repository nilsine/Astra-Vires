<?php
/*************************************
* PHP Hourly maint.
* Created: 20/April/2004
* By: Moriarty
**************************************/


/*********************  Intialisations *********************/

//start counting
$maint_time = explode(" ",microtime());
$maint_time = $maint_time[1] + $maint_time[0];
$since_last = array(0 => 0);

require_once("../$directories[includes]/${db_name}_vars.inc.php");

db("select count(star_id) from ${db_name}_stars");
$num_ss_temp = dbr();
$num_stars = $num_ss_temp[0];

$final_str .= sprintf($st[1832], $db_name);



/**********************
Run the relevent functions
**********************/

//give turns
dbn("update ${db_name}_users set turns = turns + '$GAME_VARS[hourly_turns]' where login_id not in ( (select login_id from users_holiday_mode where mode = 1) )");
dbn("update ${db_name}_users set turns = '$GAME_VARS[max_turns]' where turns > '$GAME_VARS[max_turns]'");
$final_str .= $st[1833];
print_time();


//scatter ships
if($GAME_VARS['keep_sol_clear'] == 1){
	scatter_ships(1);
	$final_str .= $st[1834];
	print_time();
}

//update shields and BO armour
if($GAME_VARS['hourly_shields'] > 0){
	update_shields();
	$final_str .= $st[1835];
	print_time();
}

//run the planet production routines
process_planets();
$final_str .= $st[1836];
print_time();

//increment research tech point things
update_research();
$final_str .= $st[1837];
print_time();

//random event running
if($GAME_VARS['random_events'] > 0){
	run_random_events();
	$final_str .= $st[1838];
	print_time();
}

//do the mining for ships and planets
process_mining();
$final_str .= $st[1839];
print_time();

//running the tech_development code
if($GAME_VARS['alternate_play_2'] == 1){
	release_tech();
	$final_str .= $st[1840];
	print_time();
}

//update bilkos
process_bilko();
$final_str .= $st[1841];
print_time();


//update bilkos
calc_values();
$final_str .= $st[1842];
print_time();

$final_str .= "Vérification multi-compte";
//check for multis
db("select *, count(last_ip) as nb from user_accounts where login_id>5 and last_ip != '' and passwd != '' and ban=0 and login_count>0 group by last_ip, passwd having nb > 1 order by last_ip");
while ($data = dbr()) {
	$final_str .= $data['login_name']  . ' - ' . $data['nb'] . '<br />';
	mysql_query("update user_accounts set ban=1, raison='Multi-compte interdit' where last_ip='" . $data['last_ip'] ."' AND passwd = '" . $data['passwd'] ."' ORDER BY `signed_up` DESC LIMIT ".($data['nb'] - 1));
//	$out .= "<br/>update user_accounts set ban=0, raison='Multi-compte interdit' where last_ip='" . $data['last_ip'] ."' AND passwd = '" . $data['passwd'] ."' ORDER BY `signed_up` DESC LIMIT ".($data['nb'] - 1)."<br/>";
}


//print that maint was run, and how long it took.
$end_time = explode(" ",microtime());
$end_time = $end_time[1] + $end_time[0];
$total_time = ($end_time - $maint_time);

$final_str .= sprintf($st[1843], $total_time);
//post_news("Hourly Maintenance run for this game in <b>$total_time</b> seconds", "maint");



dbn("update se_games set last_hourly = '".time()."' where db_name = '$db_name'");

return 1;


/****************************** Functions **********************************/


#=======================================
# Ship Scatter
#=======================================
//$give_warning = 1 means give a warning. 0 means don't
//$scat_sys = the location to scatter ships from.
function scatter_ships ($scat_sys, $give_warning = 1) {
	global $GAME_VARS, $db_name, $num_stars, $final_str, $st, $cw;

	$not_reset = array();
	$victims = array();
	$counter = 0;

	//select all ships in sol to scatter. then scatter them
	db("select s.login_id,s.ship_name,s.ship_id,u.ship_id as command from ${db_name}_ships s, ${db_name}_users u where s.location = '$scat_sys' && s.login_id != '1' && u.turns_run > '$GAME_VARS[turns_safe]' && u.login_id = s.login_id && u.second_scatter = 1");

	while($scat_ship = dbr(1)) {
		$go_to = mt_rand(2, $num_stars-1);
		dbn("update ${db_name}_ships set location = '$go_to', mine_mode='0' where ship_id = '$scat_ship[ship_id]'");
		dbn("update ${db_name}_users set second_scatter = 0 where login_id = '$scat_ship[login_id]'");

		empty($victims[$scat_ship['login_id']]) ? $victims[$scat_ship['login_id']] = "" : 1; //declare if empty

		#scattering the ship the user is commanding? then set user[location] to there as well.
		if($scat_ship['ship_id'] == $scat_ship['command']){
			dbn("update ${db_name}_users set location = '$go_to' where login_id = '$scat_ship[login_id]'");

			//prepend user command ship entry
			$victims[$scat_ship['login_id']] = sprintf($st[1844], $scat_ship[ship_name], $go_to).$victims[$scat_ship['login_id']];
		} else { //normal ship for message user.
			$victims[$scat_ship['login_id']] .= sprintf($st[1845],$scat_ship[ship_name],$go_to);
		}
		$counter++;
	}
	$final_str .= sprintf($st[1846],$counter).count($victims).$cw['users'].".";

	//send a message to any user who had ships scattered.
	foreach($victims as $vic_id => $message){
		system_message(sprintf($st[1847], $scat_sys, $message),$st[1848], $vic_id);
	}



	//select users who are at risk of being scattered and send them a message.
	db("select s.login_id, count(s.ship_id) as ship_count from ${db_name}_ships s, ${db_name}_users u where s.location = '$scat_sys' && s.login_id != 1 && u.turns_run > '$GAME_VARS[turns_safe]' && u.login_id = s.login_id && u.second_scatter = 0 group by u.login_id");

	while($mess_users = dbr(1)) {
		system_message(sprintf($st[1849], $mess_users[ship_count],$scat_sys), $st[1848], $mess_users['login_id']);
		dbn("update ${db_name}_users set second_scatter = 1 where login_id = '$mess_users[login_id]'");
		$not_reset[] = $mess_users['login_id'];
	}

	//code to update the users that do not have ships in sol this maint, but did last maint.
	$no_update = "";
	foreach ($not_reset as $l_id) {
		$no_update .= " login_id !='$l_id' && ";
	}
	if(!empty($no_update)){
		dbn("update ${db_name}_users set second_scatter = 0 where".preg_replace("/\&\& $/", "", $no_update));
	}

	$final_str .= "\n<br />".count($not_reset).$st[1850];
}




#=======================================
# Shields & Armour
#=======================================
function update_shields() {
	global $GAME_VARS, $db_name, $st, $cw;
	//ship shields
	dbn("update ${db_name}_ships set shields = shields + '$GAME_VARS[hourly_shields]' where config REGEXP 'fr'");
	dbn("update ${db_name}_ships set shields = shields + ('$GAME_VARS[hourly_shields]' * 0.5) where config REGEXP 'bs'");
	dbn("update ${db_name}_ships set shields = shields + ('$GAME_VARS[hourly_shields]' * 1.5) where config REGEXP 'sv'");
	dbn("update ${db_name}_ships set shields = shields + ('$GAME_VARS[hourly_shields]' * 2) where config REGEXP 'sw'");
	dbn("update ${db_name}_ships set shields = shields + ('$GAME_VARS[hourly_shields]' * 0.25) where config REGEXP 'sh'");
	dbn("update ${db_name}_ships set shields = shields + '$GAME_VARS[hourly_shields]'");
	dbn("update ${db_name}_ships set shields = max_shields where shields > max_shields");

	//planetary shield generator
	dbn("update ${db_name}_planets set shield_charge = shield_charge + ('$GAME_VARS[hourly_shields]' * shield_gen) where shield_gen > 0");
	dbn("update ${db_name}_planets set shield_charge = shield_gen * 1000 where shield_charge > shield_gen * 1000");

	//work out how much armour to regen.
	$hourly_armour = floor($GAME_VARS['hourly_shields'] / $GAME_VARS['armour_multiplier']);
	if($hourly_armour < 1){
		$hourly_armour = 1;
	}
	dbn("update ${db_name}_ships set armour = armour + '$hourly_armour' where config REGEXP 'bo'");
	dbn("update ${db_name}_ships set armour = max_armour where armour > max_armour");
}



#=======================================
# Planets
#=======================================

function process_planets() {
	global $db_name, $final_str, $st, $cw;

	db2("select * from ${db_name}_planets where login_id not in ( (select login_id from users_holiday_mode where mode = 1) )");
	while($planet = dbr2(1)) {
		$out = "";
		$out_fighter = "";
		$out_elect = "";

		#fighter production. 6 fighters for 1 elect, 3 metals, 2 fuels + 100 cols
		if($planet['alloc_fight'] >= 100 && $planet['fuel'] >= 2 && $planet['metal'] >= 3 && $planet['elect'] >= 1) {
			$fighter_amount = floor($planet['alloc_fight'] / 100);
			if($planet['fuel'] < $fighter_amount * 2) {
				$fighter_amount = floor($planet['fuel'] * 0.5);
			}
			if($planet['metal'] < $fighter_amount * 3) {
				$fighter_amount = floor($planet['metal'] * 0.332); #ad-infinitum :)
			}
			if($planet['elect'] < $fighter_amount) {
				$fighter_amount = $planet['elect'];
			}
			if($fighter_amount > 0) {
				$planet['fuel'] -= $f_use = $fighter_amount * 2;
				$planet['metal'] -= $m_use = $fighter_amount * 3;
				$planet['elect'] -= $fighter_amount;
				$figs_made = $fighter_amount * 6;
				$temp556 = $fighter_amount * 6;

				dbn("update ${db_name}_planets set fighters = fighters + '$figs_made', fuel = fuel - '$f_use', metal = metal - '$m_use', elect = elect - '$fighter_amount' where planet_id = '$planet[planet_id]'");

				$out_fighter = sprintf($st[1851], $f_use, $m_use, $fighter_amount, $planet[alloc_fight], $temp556) ;
				$final_str .= sprintf($st[1852], $temp556, $planet[planet_name]);
			}
		}

		#Electronics production. 4 metals, 3 fuels + 75 colonists. = 3 elects
		if($planet['alloc_elect'] >= 75 && $planet['fuel'] >= 3 && $planet['metal'] >= 4) {
			$elect_amount = floor($planet['alloc_elect'] / 75);
			if($planet['fuel'] < $elect_amount * 3) {
				$elect_amount = floor($planet['fuel'] * 0.332); #ad-infinitum :)
			}
			if($planet['metal'] < $elect_amount * 4) {
				$elect_amount = floor($planet['metal'] * 0.25);
			}
			if($elect_amount > 0) {
				$planet['fuel'] -= $f_use = $elect_amount * 3;
				$planet['metal'] -= $m_use = $elect_amount * 4;
				dbn("update ${db_name}_planets set elect = elect + ('$elect_amount' * 3), fuel = fuel - '$f_use', metal = metal - '$m_use' where planet_id = '$planet[planet_id]'");
				$out_elect = sprintf($st[1853], $f_use, $m_use, $planet[alloc_elect], $elect_amount);
				$final_str .= sprintf($st[1854], $elect_amount, $planet[planet_name]);
			}
		}
	}

	#Taxes!
	#6% tax rate to all colonists - over 24hrs....
	dbn("update ${db_name}_planets set cash = cash + (colon * 0.00243) where login_id not in ( (select login_id from users_holiday_mode where mode = 1) )");

	#colonist growth. - 12%
	dbn("update ${db_name}_planets set colon = ((colon - alloc_fight - alloc_elect) * 1.00584) + alloc_fight + alloc_elect where login_id not in ( (select login_id from users_holiday_mode where mode = 1) )");
	dbn("update ${db_name}_planets set colon = max_population where colon > max_population and login_id not in ( (select login_id from users_holiday_mode where mode = 1) )");

	#ensure not going to have negative allocated colonists
	dbn("update ${db_name}_planets set alloc_fight = colon where alloc_fight > colon and login_id not in ( (select login_id from users_holiday_mode where mode = 1) )");
	dbn("update ${db_name}_planets set alloc_elect = (colon - alloc_fight) where alloc_elect > (colon - alloc_fight) and login_id not in ( (select login_id from users_holiday_mode where mode = 1) )");

}



#=======================================
# Random events
#=======================================
function run_random_events () {
	global $db_name, $random_events, $final_str, $num_stars, $GAME_VARS, $st, $cw;

	//solar storm takes out shields
	db("select star_id from ${db_name}_stars where event_random = '12'");
	while($to_do = dbr(1)) {
		dbn("update ${db_name}_ships set shields = 0 where location = '$to_do[star_id]' && login_id != '1'");
	}


	#remove Solar Storm
	db("select star_id,star_name from ${db_name}_stars where event_random = '12' order by RAND()");
	while($star_var = dbr(1)){
		 //chance of removal based on SS count
		$temp = (1800 / ($GAME_VARS['random_events'] * $num_stars)) + 4;
		if(mt_rand(0, $temp) == 0) {
			dbn("update ${db_name}_stars set event_random = 0 where star_id = '$star_var[star_id]'");
			post_news(sprintf($st[1855],$star_var[star_name],$star_var[star_id]), "random_event");
			$final_str .= $st[1856];
		}
	}


	#create Solar Storm
	$temp = (2000 / ($GAME_VARS['random_events'] * $num_stars)) + 1;

	if (mt_rand(0, $temp) < 2) {
		$to_go = mt_rand(1,$num_stars);
		db("select event_random,star_name from ${db_name}_stars where star_id = '$to_go'");
		$is_it = dbr(1);
		if ($is_it['event_random'] == 0) {
			dbn("update ${db_name}_stars set event_random = 12 where star_id = '$to_go'");
			post_news(sprintf($st[1857],$is_it[star_name],$to_go), "random_event");
			$final_str .= sprintf($st[1858],$to_go);
		}
	}
}


#=======================================
# Mining
#=======================================
function process_mining () {
	global $db_name, $final_str, $GAME_VARS, $st, $cw;

	if($GAME_VARS['alternate_play_1'] == 1){//alternate
		db2("select s.ship_id, s.location, s.mine_rate_metal, s.mine_rate_fuel, s.cargo_bays, s.metal, s.fuel, s.elect, s.colon, star.metal AS star_metal, star.fuel AS star_fuel, star.star_id, s.mine_mode			from ${db_name}_stars star, ${db_name}_ships s		where star.star_id = s.location && s.location != 1 && (s.cargo_bays - s.metal - s.fuel - s.elect - s.colon) > 0 && ((s.mine_mode = 1 && s.mine_rate_metal > 0 && star.metal > 0) || (s.mine_mode = 2 && s.mine_rate_fuel > 0 && star.fuel > 0)) && s.login_id not in ( (select login_id from users_holiday_mode where mode = 1) )");
	} else {//normal
		db2("select s.ship_id, s.location, (s.mine_rate_metal + s.mine_rate_fuel) as mine_rate_metal, (s.mine_rate_metal + s.mine_rate_fuel) as mine_rate_fuel, s.cargo_bays, s.metal, s.fuel, s.elect, s.colon, star.metal AS star_metal, star.fuel AS star_fuel, star.star_id, s.mine_mode		from ${db_name}_stars star, ${db_name}_ships s		where star.star_id = s.location && s.location != 1 && (s.cargo_bays - s.metal - s.fuel - s.elect - s.colon) > 0 && mine_rate_metal > 0 && ((s.mine_mode = 1 && star.metal > 0) || (s.mine_mode = 2 && star.fuel > 0)) && s.login_id not in ( (select login_id from users_holiday_mode where mode = 1) )");
	}

	$metal_fuel = array();

	while($ships = dbr2(1)){

		//new star system. get metal content
		if(!isset($metal_fuel[$ships['star_id']]['metal'])){
			$metal_fuel[$ships['star_id']]['metal'] = $ships['star_metal'];
		}

		//new star system. get fuel content
		if(!isset($metal_fuel[$ships['star_id']]['fuel'])){
			$metal_fuel[$ships['star_id']]['fuel'] = $ships['star_fuel'];
		}

		if($ships['mine_mode'] == 1){
			$str_txt = 'metal';
		} else {
			$str_txt = 'fuel';
		}

		//run out of that resource in this system.
		if($metal_fuel[$ships['star_id']][$str_txt] <= 0){
			continue 1;
		}

		//make up an amount of metal/fuel to mine.
		$temp_amount = $ships['mine_rate_'.$str_txt] + mt_rand(-1,1);
		empty_bays($ships);

		//if this takes to cargo capacity, lower temp_amount
		if($ships['empty_bays'] <= $temp_amount){
			$temp_amount = $ships['empty_bays'];
		}

		//update the
		dbn("update ${db_name}_ships set $str_txt = $str_txt + '$temp_amount' where ship_id = '$ships[ship_id]'");
		//update ship and star resources.
		$metal_fuel[$ships['star_id']][$str_txt] -= $temp_amount;


	}

	$final_str .= "<br />\n".count($metal_fuel).$st[1859];
	print_time();

	//************************planetary mining *****************************
	$planet_count = 0;
	db2("select p.planet_id, p.location, p.drones_alloc_fuel, p.drones_alloc_metal, star.fuel AS star_fuel, star.metal AS star_metal, star.star_id			 from ${db_name}_stars star, ${db_name}_planets p where ((star.fuel > 0 && p.drones_alloc_fuel > 0) || (star.metal > 0 && p.drones_alloc_metal > 0)) && star.star_id = p.location && p.login_id not in ( (select login_id from users_holiday_mode where mode = 1) )");

	#planetary mining loop
	while($planet = dbr2(1)) {

		//new star system. get metal content
		if(!isset($metal_fuel[$planet['star_id']]['metal'])){
			$metal_fuel[$planet['star_id']]['metal'] = $planet['star_metal'];
		}

		//new star system. get fuel content
		if(!isset($metal_fuel[$planet['star_id']]['fuel'])){
			$metal_fuel[$planet['star_id']]['fuel'] = $planet['star_fuel'];
		}


		$fuel_to_mine = 0;
		$metal_to_mine = 0;

		if($planet['drones_alloc_fuel'] > 0){
			$fuel_to_mine = $planet['drones_alloc_fuel'];

			//don't overmine the system.
			if($fuel_to_mine > $metal_fuel[$planet['star_id']]['fuel']){
				$fuel_to_mine = $metal_fuel[$planet['star_id']]['fuel'];
			}
			if($fuel_to_mine < 1){//no negative by accident.
				$fuel_to_mine = 0;
			}
		}
		if($planet['drones_alloc_metal'] > 0){
			$metal_to_mine = $planet['drones_alloc_metal'];

			//don't overmine the system.
			if($metal_to_mine > $metal_fuel[$planet['star_id']]['metal']){
				$metal_to_mine = $metal_fuel[$planet['star_id']]['metal'];
			}
			if($metal_to_mine < 1){//no negative by accident.
				$metal_to_mine = 0;
			}
		}
		$metal_fuel[$planet['star_id']]['fuel'] -= $fuel_to_mine;
		$metal_fuel[$planet['star_id']]['metal'] -= $metal_to_mine;

		//update the planet
		dbn("update ${db_name}_planets set fuel = fuel + '$fuel_to_mine', metal = metal + '$metal_to_mine' where planet_id = '$planet[planet_id]'");
		$planet_count++;

	}//end planetary mining loop.


	//loop through all mined star systems and update them with new values
	foreach($metal_fuel as $id => $arr){
		dbn("update ${db_name}_stars set metal = '$arr[metal]', fuel='$arr[fuel]' where star_id = '$id'");
	}

	//ensure no negative amounts of metal/fuel in star systems.
	dbn("update ${db_name}_stars set metal = 0 where metal < 0");
	dbn("update ${db_name}_stars set fuel = 0 where fuel < 0");

	$final_str .= sprintf($st[1860],$planet_count);
	$final_str .= print_time().$st[1861].count($metal_fuel);
}




#=======================================
# Time passing system
#=======================================
# AE - 7388 avg-run; 200 hourly (4800); 16 days played (75000); end of SD - increaae 1k in 1 day - 9%
# 292429 - 16 users - 18276 avg - 24%

# Blitz - 14367 avg-run; 300 hourly (21600); 8 days played (170000); start of SD - increase 2k in 1 day - 8.4
# 967728 - 29 users - 33369 avg - 19.6%

# slow - 339 avg-run; 300 hourly (2400); 14 days played (30000); early on - increase 75 in 1 day - 1
# 16784 - 13 users - 1291 avg - 4%
#
# number of times over the hourly that the active users have gone.
#


function release_tech (){
	global $final_str, $db_name, $GAME_VARS, $st, $cw;
	db("select sum(turns_run) as run_count, count(login_id) as user_count from ${db_name}_users where login_id > 5 && turns_run > '$GAME_VARS[start_turns]'");
	$game_details = dbr();

	#ensure that we won't end up dividing by 0 (game just started)
	if($game_details['run_count'] > 0 && $game_details['user_count'] > 0){

		$avg_run = 0;
		$time_pass = 0;

		#work out the total number of hours worth of turns each user has used on average.
		$avg_run = floor($game_details['run_count'] / $game_details['user_count'])+1;

		#generate a random number
		$rand_num = mt_rand(-10,10);
		if($rand_num != 0){
			$avg_run *= ($rand_num / 100) + 1;
			$avg_run = floor($avg_run);
		}

		#work out the number of periods (hourly turn collections) that have passed. This is the base unit for time.
		if($avg_run <= 0){ //avoid div by 0 error;
			$time_pass = 0;
		} else {
			$time_pass = floor($avg_run / $GAME_VARS['hourly_turns']);
		}

		db("select item_name from se_development_time where ${db_name}_available = 0 && year_set_${GAME_VARS['alternate_play_2']} <= '$time_pass' order by item_id asc");

		$item_counter = 0;
		$item_text = "";

		#loop through the items
		while ($now_av = dbr(1)){
			$item_counter ++;
			$item_text .= "<br />".$now_av['item_name'];
		}

		#finish off the job
		if($item_counter > 0){

			db("select login_id from ${db_name}_users where login_id > 5");

			#loop through the users to send each a message.
			while ($user_id = dbr(1)){
				system_message(sprintf($st[1862],$item_text),$st[1886],$user_id['login_id']);
			}

			#make items available in the DB
			dbn("update se_development_time set ${db_name}_available = 1 where ${db_name}_available = 0 && year_set_${GAME_VARS['alternate_play_2']} <= '$time_pass'");
			$final_str .= $st[1863].$item_text;
		}
	}
}

#=======================================
# Bilkos
#=======================================
function process_bilko () {
	global $final_str, $db_name, $GAME_VARS, $st, $cw;

	$bil_seconds = $GAME_VARS['bilkos_time'] * 3600;
	//delete old
	dbn("delete from ${db_name}_bilkos where timestamp <= ".time()."- ($bil_seconds * 2) && bidder_id = 0 && active = 1");
//
	//inform winners
	db("select bidder_id, item_name, item_id from ${db_name}_bilkos where timestamp <= ".(time() - $bil_seconds)." && active = 1 && bidder_id > 0");

	while($lots = dbr(1)){
		system_message(sprintf($st[1864],$lots[item_id],$lots[item_name]), $st[361], $lots['bidder_id']);
		dbn("update ${db_name}_bilkos set active=0 where item_id = '$lots[item_id]'");
		$final_str .= sprintf($st[1865], $lots[bidder_id], $lots[item_name]);
	}


#number generated is random. between 0 and 99.
#3 per 4 hours or so a new item may be added. (75 = 3/4 of 100)
#if > 90, then planetary item.
# > 78 then misc
# > 55 then ships
# > 45 then equipment
# > 25 then upgrades

	$turnip = mt_rand(0,99);

	if($turnip > 25){
		$i_name = "";

		if($turnip > 90){ #planetary.
			$i_type = 5;
			if($turnip > 94.5 && avail_check_hour(3000)){
				$i_code = mt_rand(4,9);
				$i_name = sprintf($st[1866],$i_code);
				$i_price = $i_code * 4000;
				$i_descr = sprintf($st[1867],$i_code);
			}
		} elseif($turnip > 78){ #misc - turns.
			$i_type = 4;
			$i_code = mt_rand(10,90);
			$i_name = sprintf($st[1868],$i_code);
			$i_price = $i_code * 110;
			$i_descr = sprintf($st[1869],$i_code);
		} elseif($turnip > 55){ #ships
			#get a random ship and put it up for auction.
			#Put stuff into DB
			$i_type = 1;

			db("select s.type_id, s.name, s.cost, s.max_shields, s.fighters, s.max_fighters, s.max_armour, s.upgrade_slots, s.config, s.descr from se_ship_types s, se_development_time y where s.config NOT REGEXP 'oo' && s.type_id > 2 && auction =1 && s.type_id = y.item_id && ((y.${db_name}_available = 1 && $GAME_VARS[alternate_play_2] = 1) || $GAME_VARS[alternate_play_2] = 0) order by RAND() limit 1");
			$ships = dbr(1);
			if($ships){
				$i_code = 'ship'.$ships['type_id'];
				$i_name = $ships['name'];
				$i_price = $ships['cost'] + ($ships['fighters'] * $GAME_VARS['fighter_cost_earth']);
				$i_descr = sprintf($st[1870],$ships[max_shields],$ships[fighters],$ships[max_fighters],$ships[max_armour],$ships[upgrade_slots],$ships[config],$ships[descr]);
			}
		} elseif($turnip > 45){ #equipment
			$i_type = 2;
			if($turnip > 49 && $GAME_VARS['bomb_flag'] < 2 && avail_check_hour(3002)){
				$i_code="warpack";
				$i_name=$st[1871];
				$i_price= $GAME_VARS['bomb_cost'] * 4;
				$i_descr=$st[1872];
			} elseif($turnip < 50 && avail_check_hour(1003)) {
				$i_code="deltabomb";
				$i_name=$st[1873];
				$i_price= 15 * $GAME_VARS['bomb_cost'];
				$i_descr=$st[1874];
			}
		} elseif($turnip > 25) { # upgrades
			$i_type = 3;
			if($turnip > 41 && avail_check_hour(3003)){
				$i_code="fig1500";
				$i_name=$st[1875];
				$i_price=50000;
				$i_descr=$st[1876];
			} elseif($turnip > 37){
				$i_code="attack_pack";
				$i_name=$st[1877];
				$i_price=25000;
				$i_descr=$st[1878];
			} elseif($turnip > 32.5){
				$i_code="fig600";
				$i_name=$st[1879];
				$i_price=8000;
				$i_descr=$st[1880];
			} elseif($turnip > 28 && avail_check_hour(3004)){
				$i_code="upbs";
				$i_name=$st[1881];

				db("select description,does_to_ship,cost from se_config_list where config_id = 'bs'");
				$bs_result = dbr(1);

				$i_price = $bs_result['cost'];
				$i_descr = $bs_result['description']."<br />".$bs_result['does_to_ship'];
			} elseif($turnip < 29 && $GAME_VARS['enable_superweapons'] == 1 && avail_check_hour(3005)) {
				$i_code="up2";
				$i_name=$cw['terra_maelstrom']. " (".$cw['upgrade'].")";

				db("select description,does_to_ship,cost from se_config_list where config_id = 'sw'");
				$sw_result = dbr(1);

				$i_price = $sw_result['cost'];
				$i_descr = $sw_result['description']."<br />".$sw_result['does_to_ship'];
			}
		}
	#$i_type
	#$i_code
	#$i_name
	#$i_price
	#$i_descr
		if(!empty($i_name)){
			dbn("insert into ${db_name}_bilkos (timestamp,item_type,item_code,item_name,going_price,descr,active) values(".time().",'$i_type','$i_code','".mysql_real_escape_string(stripslashes($i_name))."','$i_price','".mysql_real_escape_string(stripslashes($i_descr))."',1)");
			$final_str .= sprintf($st[1882],$i_name);
		}
	}
}


//function that will attempt to calculate the value of the player.
function calc_values(){
	global $db_name, $final_str, $GAME_VARS, $mining_drone_cost, $research_fac_cost, $st, $cw;

	//work out the value of all extra equip.
	//delta bomb costs MINIMUM of 15 regular bombs
	db2("select sum(cash + (genesis * $GAME_VARS[cost_genesis_device]) + ((alpha + gamma + (delta * 15)) * $GAME_VARS[bomb_cost])) as value, login_id from ${db_name}_users group by login_id");

	//loop through all players
	while($p_val = dbr2(1)){
		$t_value = $p_val['value'];
		$l_id = $p_val['login_id'];

		//work out value of ships
		db("select sum(s.cost + (u.fighters * $GAME_VARS[fighter_cost_earth]) + (u.metal * $GAME_VARS[buy_metal]) + (u.fuel * $GAME_VARS[buy_fuel]) + (u.elect * $GAME_VARS[buy_elect]) + (u.colon * $GAME_VARS[cost_colonist])) as value from ${db_name}_ships u, se_ship_types s where u.login_id = '$l_id' && u.shipclass = s.type_id");
		$temp = dbr(1);
		$t_value += $temp['value'];


		//value of planets
		db("select sum(cash + (fighters * $GAME_VARS[fighter_cost_earth]) + (metal * $GAME_VARS[buy_metal]) + (fuel * $GAME_VARS[buy_fuel]) + (elect * $GAME_VARS[buy_elect]) + (colon * $GAME_VARS[cost_colonist]) + (research_fac * $research_fac_cost) + (mining_drones * $mining_drone_cost)) as value from ${db_name}_planets where login_id = '$l_id'");
		$temp = dbr(1);
		$t_value += $temp['value'];

		dbn("update ${db_name}_users set approx_value = '$t_value' where login_id = '$p_val[login_id]'");
	}
}



//function to process research and give players tech points
//number of tech points given is based on the percent colonists on the planet versus max colonists.
//so if hourly_tech is 10, then player will need at least 10% of the max pop to get any tech points.
//planet also requires at least 50% of max population.
function update_research () {
	global $GAME_VARS, $db_name, $final_str, $st, $cw;
	dbn("update ${db_name}_planets set tech = tech + ROUND('$GAME_VARS[hourly_tech]' * ((colon + 1) / max_population)) where research_fac > 0 && colon > (max_population / 2)");
	$final_str .= "\n<br />".mysql_affected_rows().$st[1883];
}



//send message to a user.
function system_message($text, $sender_text, $to){
	global $db_name;
	dbn("insert into ${db_name}_messages (timestamp, sender_name, sender_id, login_id, text) values(".time().",'".mysql_real_escape_string(stripslashes($sender_text))."','-1','".mysql_real_escape_string(stripslashes($to))."','".mysql_real_escape_string(stripslashes($text))."')");
}


//check the availability of an item.
function avail_check_hour($sent_id){
	global $db_name, $GAME_VARS, $st, $cw;
	if($GAME_VARS['alternate_play_2'] ==1){
		db("select ${db_name}_available from se_development_time where item_id = '$sent_id'");
		$ret_id = dbr();
		return $ret_id[0];
	} else {
		return 1;
	}
}


?>
