<?php
/*********************************
*
* This file contains costs for things in it.
* Created:
* By: Moriarty
* On: 16/3/02
*
**********************************/


/************
BM Upgrades
*************/
#turret costs - based on size of ship
$genesis_c = 5000000;
$terra_i_c = 250000;


// Support Unit Cost
#turret costs based on size of ship
$genesis_t = 0;
$terra_i_t = 500;

#maximum amount of each weapon system allowed on the ship.
$max_pc = 5;
$max_ew = 5;


#damages for the upgrades.
$dt_damage = 220;
$ot_damage = 200;
$pc_damage = 420;
$ew_damage = 600;



/************
Earth Equip Shop
*************/
$shield_cost = 35;
$armour_cost = 70;


/************
Planet Costs
*************/
$shield_gen_cost = 50000;
$research_fac_cost = 100000;
$mining_drone_cost = 1000;

$max_drones = 1000;

/**********************
Earth Upgrade store
**********************/
//increases in capacity:
$fighter_inc = 300;
$shield_inc = 100;
$cargo_inc = 100;
$armour_inc = 50;

//costs
$basic_cost = 5000;		//cost of the 3 basic upgrades.


$ramjet_cost = 20000;

#maximum number of each turret type:
$max_ot = 5;
$max_dt = 5;

/******************
* Cost to do things.
******************/
$cost_per_transfer = 500;
$cost_to_destroy = 100;

$simulate_attack_turn_cost = 5;

$max_non_warship_fighters = 5000;


//function get's the cost from the DB.
function get_cost($lookup){
	db2("select cost, tech_cost from se_config_list where config_id = '$lookup'");
	return dbr2(1);
}

?>