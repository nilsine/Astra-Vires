<?php
require_once("user.inc.php");

set_time_limit(60); //a full minute to run it.

$bu_start_time = explode(" ",microtime());
$bu_start_time = $bu_start_time[1] + $bu_start_time[0];

if(isset($exp_sys)){
	return 0;

//(server) admin only.
} elseif($user['login_id'] != 1 && $user['login_id'] != OWNER_ID) {
	print_page('Error','Admin only');

} elseif(empty($sure) && isset($build_universe)) {
	$sure_str = "Are you sure you want to build a new universe?<p />This may take some time.";
	get_var('Build Uni','build_universe.php',$sure_str,'sure','yes');

} elseif(isset($process)) {

	if(!isset($preview)) { //only output text for html pages. 
		print_header("Build Universe","../");
	}

	mt_srand((float)microtime()*1000000);
	/*************************************************/
	/********************VARIABLES********************/
	/*************************************************/
	$UNI = array();
	$UNI['size_x'] = get_db_var('uv_size_x_width');
	$UNI['size_y'] = get_db_var('uv_size_y_height');
	$UNI['numsystems'] = get_db_var('uv_num_stars');
	$UNI['map_border'] = 25; //border on all sides around the image (stops numbers going off the edge) (pixels).
	$UNI['num_size'] = 2; //font size for system numbers (on map).
	$UNI['bg_color'] = array(0,0,0); //background colour of map
	$UNI['link_color'] = array(90,90,90); //colour of links between systems
	$UNI['num_color'] = array(0,255,255);//Most system numbers
	$UNI['num_color2'] = array(255,255,255);//Current system number
	$UNI['num_color3'] = array(255,0,0);//Sol color
	$UNI['star_color'] = array(255,255,255);
	$UNI['worm_one_way_color'] = array(230,230,64); //yellow
	$UNI['worm_two_way_color'] = array(0,230,0); //green
	$UNI['label_color'] = array(0, 255, 0);
	$UNI['localmapwidth'] = 200; //width of 'local area' map.
	$UNI['localmapheight'] = 200; //height of 'local area' map.
	$UNI['mindist'] = get_db_var('uv_min_star_dist');
	$UNI['minfuel'] = get_db_var('uv_fuel_min');
	$UNI['maxfuel'] = get_db_var('uv_fuel_max');
	$UNI['fuelpercent'] = get_db_var('uv_fuel_percent');
	$UNI['minmetal'] = get_db_var('uv_metal_min');
	$UNI['maxmetal'] = get_db_var('uv_metal_max');
	$UNI['metalpercent'] = get_db_var('uv_metal_percent');
	$UNI['map_layout'] = get_db_var('uv_map_layout');
	$UNI['uv_planets'] = get_db_var('uv_planets');
	$UNI['uv_planet_slots'] = get_db_var('uv_planet_slots');
	$UNI['wormholes'] = get_db_var('uv_wormholes');
	$UNI['num_ports'] = get_db_var('uv_num_ports');
	$UNI['num_bms'] = get_db_var('uv_num_bmrkt');
	$UNI['year_act'] = get_db_var('alternate_play_2');
	$UNI['random_events'] = get_db_var('random_events');
	$UNI['link_dist'] = get_db_var('uv_max_link_dist'); //maximum distance between linked star systems (pixels).
	$UNI['minlinks'] = 2; //miniumum number of links a system may have.
	$UNI['maxlinks'] = 6; //maximum number of links a system may have.
	$UNI['print_bg_color'] = array(255,255,255); //background colour of printable map.
	$UNI['print_link_color'] = array(200,200,200); //link colour for printable map
	$UNI['print_num_color'] = array(0,0,0);//Most system numbers for printably map
	$UNI['print_star_color'] = array(0,0,0); //star colour for printable map
	$UNI['print_label_color'] = array(0, 0, 0);

	if(!isset($gen_new_maps)){//don't make a new uni for map making
		$systems = array( array('num' => 0, 'x_loc' => $UNI['size_x']/2, 'y_loc' => $UNI['size_y']/2, 'links' => '', 'name' => 'Sol', 'fuel' => 0, 'metal' => 0, 'wormhole' => 0,'planetary_slots' => 0, 'event_random' => 0) );
		$ports = array( array('location' => 0) );
		$bmarks = array();
	}

	if(isset($build_universe) || isset($preview) || isset($gen_new_maps)) {

		 //only output text for html page. not map preview png
		if(isset($build_universe)){
			print("Generating Systems...<br />");
			flush();
		}
		if(!isset($gen_new_maps)){ //don't make a new uni for map making
			make_systems_1($systems);
		}

		 //only output text for html page. not map preview png
		if(isset($build_universe)) {
			print_time();
			print("Linking Systems...<br />");
			flush();
		}
		if(!isset($gen_new_maps)){//don't make a new uni for map making
			link_systems_1($systems);
		}

		if(isset($build_universe)){//generating a new universe
			print_time();
			print("Adding Minerals...<br />");flush();
			add_minerals($systems);
			print_time();
			print("Adding Starports...<br />");flush();
			add_starports($ports);
			print_time();
			if($UNI['num_bms'] > 0){
				print("Adding Blackmarkets...<br />");flush();
				add_blackmarket_se1($bmarks);
				print_time();
				$bm_allowed = "${db_name}_bmrkt";
			} else {
				$bm_allowed = "";
			}
			print("Saving Universe...<br />");flush();
			save_universe_se1($systems, $ports, $bmarks, "${db_name}_stars", "${db_name}_ports", $bm_allowed);
			print_time();
			random_event_placer();

			if($UNI['uv_planets'] >= 1){
				print("Creating pre-genned planets...<br />");flush();
				create_random_planets();
				print_time();
			}
			if($UNI['uv_planet_slots'] >= 1){
				print("Creating planetary Slots...<br />");flush();
				place_planetary_slots();
				print_time();
			}
			#year based system implementation.
			if($UNI['year_act'] > 0){
				dbn("update se_development_time set {$db_name}_available = 0 where year_set_{$UNI['year_act']} > 0");
				dbn("update se_development_time set {$db_name}_available = 1 where year_set_{$UNI['year_act']} = 0");
				print "Year reset to 0...<br />";
			}

			if(!extension_loaded("gd") && !extension_loaded("gd2")){
				print("<p /><b class='b1'>Warning</b>!<br />You do not have the <b class='b1'>gd</b> module installed with this PHP installation, therefore the maps cannot be generated.<p />To fix this, find and install the GD library, or get the server operator to do it if it's a paid-for server.<br />And if you can't do the above, u shouldn't be operating a server.");
			} else {
				set_time_limit(20); //another minute to make the images

				$random_filename = mt_rand(0,mt_getrandmax());

				print("<br />Deleting old images...<br />");flush();
				clear_images("$directories[images]/${db_name}_maps");
				print_time();
				print("Rendering global map...<br />");flush();
				render_global($db_name);
				print_time();
				print("Rendering local maps...<br />");flush();
				render_local($db_name);
				print_time();
				dbn("update se_games set paused = 1, random_filename = '$random_filename' where db_name = '$db_name'");
				print("Game Paused");
				print("<div id='done'>Finished.<script>document.all.done.scrollIntoView();</script></div>");
			}
			print_footer();

		} elseif(isset($gen_new_maps)){ //generating some new maps for some reason
			print("<br />Deleting old images...<br />");flush();
			clear_images("$directories[images]/${db_name}_maps");
			print_time();

			print("Rendering global map...<br />");flush();
			render_global($db_name);
			print_time();

			if(isset($all)){ //render locals as well as globals.
				print("Rendering local maps...<br />");flush();
				render_local($db_name);
				print_time();
			}
			print_footer();

		} else { //previewing universes
			render_global($db_name);
		}
	}

} else {
	$out_str = "Choose something to do with the universe generator.<br />Only the bottom choice will re-generate the universe!";
	$out_str .= "<p /><a href='$_SERVER[PHP_SELF]?preview=1&process=1'>Preview</a> a universe that uses your present variable settings. This won't do anything to the present game!!!";
	$out_str .= "<p /><br />Generate a <a href='$_SERVER[PHP_SELF]?build_universe=1&process=1'>new universe</a>!<br />";

	print_page('Universe Generation',$out_str);
}








/*************************************************/
/********************FUNCTIONS********************/
/*************************************************/

//get's a variable from the database, and returns it's value
function get_db_var($name) {
	global $db_name;
	db("select ${db_name}_value as value from se_db_vars where name = '$name'");
	$result = dbr(1);
	return $result['value'];
}

//deletes all image files.
function clear_images($path) {
	$dir = opendir($path);
	while($filename = readdir($dir)) {
		if(eregi("\\.png$", $filename)) {
			unlink("$path/$filename");
		}
	}
	closedir($dir);
}

//add starports to the universe.
function add_starports(&$ports) {
	global $UNI;

	for($i = 0; $i < ($UNI['num_ports'] - 1); $i++) {
		$new_port = array('location' => 0);

		$new_port['location'] = mt_rand(2, $UNI['numsystems']);

		//ensure no more than 1 per system. But ONLY if there are enough systems!!!
		if($UNI['num_ports'] < $UNI['numsystems']){
			while(system_has_port($ports, $new_port)) {
				$new_port['location'] = mt_rand(2, $UNI['numsystems']);
			}
		} else {
			$new_port['location'] = mt_rand(2, $UNI['numsystems']);
		}

		$ports[] = $new_port;

	}
}

//add BM's to the universe
function add_blackmarket_se1(&$bmarks) {
	global $UNI;

	$bm_names = array("Dodgy Dave", "Stinkin Sid", "Goodie-bag Central", "The Department of Corruption", "The Ultimate Goodies Store", "Stompin Jim", "The War Cabinet", "Jim  -Dead Eye- Smarms", "One Eyed Doyle", "The Ministry of Offence");
	$bm_type = 0;

	for($i = 0; $i < $UNI['num_bms']; $i++) {
		$new_bm = array('location' => 0, 'bmrkt_type' => "", 'bm_name' => "");

		$new_bm['bm_name'] = $bm_names[array_rand($bm_names)];
		#less blackmarkets than types
		if($UNI['num_bms'] < 2){
			$new_bm['bm_type'] = 0;

		} else {//increase the bm_type until we get to 2, then reset to 0.
			if($bm_type == 2){
				$bm_type = 0;
			} elseif($i > 0) {
				$bm_type++;
			}
		}

		$new_bm['bm_type'] = $bm_type;

		$new_bm['location'] = mt_rand(2, $UNI['numsystems']);

		//ensure no more than 1 per system. But ONLY if there are enough systems!!!
		if($UNI['num_bms'] < $UNI['numsystems']){
			while(system_has_port($bmarks, $new_bm)) {
				$new_bm['location'] = mt_rand(2, $UNI['numsystems']);
			}
		} else {
			$new_bm['location'] = mt_rand(2, $UNI['numsystems']);
		}

		$bmarks[] = $new_bm;
	}
}


//function that will pre-generate planets.
function create_random_planets (){
	global $UNI,$db_name,$systems;
	#pre-generated planets
	dbn("TRUNCATE TABLE ${db_name}_planets");
	print "Old planets wiped\n<br />";

	//sum total metal & fuel in the universe.
	db("select sum(metal) as metal, sum(fuel) as fuel from ${db_name}_stars");
	$mineral_sum = dbr(1);
	$metal_sum = round($mineral_sum['metal'] / ($UNI['numsystems'] - 1));
	$fuel_sum = round($mineral_sum['fuel'] / ($UNI['numsystems'] - 1));

	for($ct = 1; $ct <= $UNI['uv_planets']; $ct++) {
		$planet_loc = mt_rand(2, $UNI['numsystems']);

		if($systems[$planet_loc - 1]['event_random'] != 0){ //no planets in random event systems
			continue 1;
		}
		$planet_name = $systems[$planet_loc -1]['name']." #".$ct;
		$planetary_metal = round((mt_rand(1, 50) / 100) * $metal_sum);
		$planetary_fuel = round((mt_rand(1, 50) / 100) * $fuel_sum);
		$planet_img = mt_rand(1, 15);
		$max_pop = mt_rand(14000000,15000000);
		$planetary_figs = round(($planetary_metal + $planetary_fuel) * 1.1);
		$p_id = $ct + 1;
		dbn("insert into ${db_name}_planets (planet_id, planet_name, location, login_id, login_name, fighters, cash, clan_id, metal, fuel, pass, planet_img, max_population) values('$p_id', '$planet_name', $planet_loc, 4, 'Un-Owned', '$planetary_figs', 1, 0, '$planetary_metal', '$planetary_fuel', 0, '$planet_img', '$max_pop')");
		//print "Planet $ct created at $planet_loc\n<br />";
	}
	print "Randomly Placed Planets Done.\n<br />";

#if pre-genned planets are off, then planetary slots will be implemented elsewhere.
}


//function that will place planetary slots randomly.
function place_planetary_slots(){
	global $db_name, $UNI;
	dbn("update ${db_name}_stars set planetary_slots = (ROUND(RAND() * $UNI[uv_planet_slots]))");
}


//function that places random events around the universe.
function random_event_placer(){
	global $UNI, $systems, $db_name;

	//high level random events
	if($UNI['random_events'] == 3){
		//black holes
		$to_do = ceil($UNI['numsystems'] / 110);
		for($i=1; $i <= $to_do; $i++){
			$place = mt_rand(2, $UNI['numsystems']);
			dbn("update ${db_name}_stars set event_random = 1, star_name = 'Black Hole', planetary_slots = 0 where star_id = '$place'");
			$systems[$place - 1]['event_random'] = 1;
			$systems[$place - 1]['name'] = "Black Hole";
			$systems[$place - 1]['planetary_slots'] = 0;//no planets in BH systems.
		}
	}
}

//check to see if a system already has a port
function system_has_port(&$ports, $s_port) {
	foreach($ports as $port) {
		if($port['location'] == $s_port['location']) {
			return true;
		}
	}
	return false;
}

//save the universe
function save_universe_se1(&$systems, &$ports, &$bmarks, $table_stars, $table_ports, $table_bms, $delete=true) {
	global $UNI,$db_name;

	if($delete) {
		if(!empty($table_stars)) {
			dbn("TRUNCATE TABLE $table_stars");
		}
		if(!empty($table_ports)) {
			dbn("TRUNCATE TABLE $table_ports");
		}
		if(!empty($table_bms)) {
			dbn("TRUNCATE TABLE $table_bms");
		}
	}

	if(!empty($table_stars)) {
		foreach($systems as $system) {
			$link_arr = array();
			if((string)$system['links'] != ""){//don't link all systems to 1 automatically.
				$link_arr = array_map("plus_one", explode(',', $system['links']));
			}
			$link_arr = array_pad($link_arr, $UNI['maxlinks'], 0);
			dbn("insert into $table_stars set star_id = ".($system['num'] + 1).", star_name = \"".addslashes($system['name'])."\", x_loc = $system[x_loc], y_loc = $system[y_loc], link_1 = '$link_arr[0]', link_2 = '$link_arr[1]', link_3 = '$link_arr[2]', link_4 = '$link_arr[3]', link_5 = '$link_arr[4]', link_6 = '$link_arr[5]', metal = '$system[metal]', fuel = '$system[fuel]', wormhole = '$system[wormhole]'");
		}
		dbn("update se_games set num_stars = '$UNI[numsystems]' where db_name = '$db_name'");
	}

	if(!empty($table_ports)) {
		foreach($ports as $port) {
			dbn("insert into $table_ports set location = ".($port['location'] + 1));
		}
	}

	if(!empty($table_bms)) {
		foreach($bmarks as $bmark) {
			dbn("insert into $table_bms set location = '$bmark[location]', bmrkt_type = '$bmark[bm_type]', bm_name = '$bmark[bm_name]'");
		}
	}
}

//add minerals to the systems
function add_minerals(&$systems) {
	global $UNI;

	foreach($systems as $system) {
		if(empty($system['num'])) {
			continue;
		}
		if(mt_rand(0,100) < $UNI['fuelpercent']) {
			$systems[$system['num']]['fuel'] = mt_rand($UNI['minfuel'],$UNI['maxfuel']);
		}
		if(mt_rand(0,100) < $UNI['metalpercent']) {
			$d3 = $systems[$system['num']]['metal'] = mt_rand($UNI['minmetal'],$UNI['maxmetal']);
		}
	}
}

/*
for($i = $this->systems; $i -= 1; ){ 
	$g = false; 
	$star_count = count($x); 
	while($g != true){ 
		$g = true; 
		//Generate a random star location 
		$thisx = mt_rand($this->galaxy_padding[0][0], $max_x); 
		$thisy = mt_rand($this->galaxy_padding[1][0], $max_y); 
		// A speed fix, dramatically reduces time taken!!
		$tmp = array($thisx - $this->star_padding[0][0], $thisx + $this->star_padding[0][1], $thisy - $this->star_padding[1][0], $thisy + $this->star_padding[1][1]); 
		//Check to see if the star too close to this position already exists.
		//Another speed fix, replaced count($x) with $i, saved about 0.1 seconds!
		//Yet another speed fix, $a++ replaced by $a += 1, supposedly faster! 

		for($a = $star_count; $a--; ){ 
			if($x[$a] > $tmp[0] && $x[$a] < $tmp[1] && $y[$a] > $tmp[2] && $y[$a] < $tmp[3]){ 
			$invalid['stars'] += 1; 
			$g = false; 
			break; 
		}
	} 
    //Do this when an appropriate location is found
    $x[] = $thisx; 
    $y[] = $thisy;
	*/


//create the star systems
function make_systems_1(&$systems) {
	global $UNI, $tables;
	db("select * from se_svr_star_names order by rand()");

	//work out centres.
	$centre = round(($UNI['size_x'] + $UNI['size_y']) / 4); //approx centre of map
	$centre_x = round($UNI['size_x'] / 2); //centre of map width
	$centre_y = round($UNI['size_y'] / 2); //centre of map height

	/**********************
	* Random Star layout
	***********************/
	if($UNI['map_layout'] == 0) { //random layout

		//just need to ensure not too close.
		/*-------------------------------------------------------*/
		$loop_eval = "
		do {
			\$systems[\$count]['x_loc'] = mt_rand(0, $UNI[size_x]);
			\$systems[\$count]['y_loc'] = mt_rand(0, $UNI[size_y]);
		} while(system_too_close(\$systems[\$count],\$systems,$UNI[mindist]));";
		/*-------------------------------------------------------*/


	/**********************
	* Grid Star layout
	***********************/
	} elseif($UNI['map_layout'] == 1) { //grid layout
		
		//work out num cols and rows etc.
		$rows = round(sqrt($UNI['numsystems']));
		$row_dist = round($UNI['size_y'] / $rows);
		$per_col = round($UNI['numsystems'] / $rows);
		$col_dist = round($UNI['size_x'] / $per_col);
		$row_count = 0;
		$col_count = 0;

		/*-------------------------------------------------------*/
		$loop_eval = "
		if(\$row_count > \$rows){//create a new column
			\$row_count = 0;
			\$col_count++;
		}
		\$systems[\$count]['x_loc'] = \$col_dist * \$col_count;
		\$systems[\$count]['y_loc'] = \$row_dist * \$row_count;
		\$row_count++;
		while(system_too_close(\$systems[\$count],\$systems,$UNI[mindist])) {
			\$systems[\$count]['x_loc'] = mt_rand(0, $UNI[size_x]);
			\$systems[\$count]['y_loc'] = mt_rand(0, $UNI[size_y]);
		}";
		/*-------------------------------------------------------*/

	/**********************
	* Galactic Core Star layout
	***********************/
	} elseif($UNI['map_layout'] == 2){ //galactic core

		$one_quart = round($centre / 4);

		/*-------------------------------------------------------*/
		$loop_eval = "
		\$basis = mt_rand(0,100);
		if(\$basis > 75){ //within centre quarter
			\$div_by = 4;
		} elseif(\$basis > 50){ //within centre half
			\$div_by = 3;
		} elseif(\$basis > 25){ //within half
			\$div_by = 2;
		} else { //anywhere
			\$div_by = 1;
		}
		do {
			\$systems[\$count]['x_loc'] = mt_rand(0, $UNI[size_x]);
			\$systems[\$count]['y_loc'] = mt_rand(0, $UNI[size_y]);
		} while((get_sys_dist(\$systems[0],\$systems[\$count]) > ($centre * 2)/\$div_by) || system_too_close(\$systems[\$count],\$systems,$UNI[mindist]));";
		/*-------------------------------------------------------*/

	/**********************
	* Clusters layout
	***********************/
	} elseif($UNI['map_layout'] == 3){ //clusters
		$num_clus = round(sqrt($UNI['numsystems'])) - 1; //number of clusters
		$stars_per_cluster = round($UNI['numsystems'] / $num_clus); //stars per cluster
		$cluster_size = round(($centre / ($num_clus * 0.55)) / 2); //size of cluster in pixels
		$offset_cluster_x = $UNI['size_x'] - $cluster_size;
		$offset_cluster_y = $UNI['size_y'] - $cluster_size;
		$sec_count = 0;
		$basis_x = $centre_x;
		$basis_y = $centre_y;

		/*-------------------------------------------------------*/
		$loop_eval = "
		if(\$sec_count > \$stars_per_cluster){ //create new cluster
			\$basis_x = mt_rand(\$cluster_size, \$offset_cluster_x);
			\$basis_y = mt_rand(\$cluster_size, \$offset_cluster_y);
			\$sec_count = 0;
		}

		\$systems[\$count]['x_loc'] = mt_rand(0, \$cluster_size); //x_loc - within cluster

		if(mt_rand(0,100) > 50) { //decide offset from center of cluster.
			\$systems[\$count]['x_loc'] += \$basis_x;
		} else {
			\$systems[\$count]['x_loc'] = \$basis_x - \$systems[\$count]['x_loc'];
		}
		\$systems[\$count]['y_loc'] = mt_rand(0, \$cluster_size); //y_loc - within cluster

		if(mt_rand(0,100) > 50) { //decide offset from center of cluster.
			\$systems[\$count]['y_loc'] += \$basis_y;
		} else {
			\$systems[\$count]['y_loc'] = \$basis_y - \$systems[\$count]['y_loc'];
		}
		while(system_too_close(\$systems[\$count],\$systems,$UNI[mindist])) {
			\$systems[\$count]['x_loc'] = mt_rand(0,$UNI[size_x]);
			\$systems[\$count]['y_loc'] = mt_rand(0,$UNI[size_y]);
		}
		\$sec_count++;";
		/*-------------------------------------------------------*/


	/**********************
	* Circle layout
	***********************/
	} elseif($UNI['map_layout'] == 4){ //Circle layout

		/*-------------------------------------------------------*/
		$loop_eval = "
		do{
			\$systems[\$count]['x_loc'] = mt_rand(0,$UNI[size_x]);
			\$systems[\$count]['y_loc'] = mt_rand(0,$UNI[size_y]);
		} while((get_sys_dist(\$systems[0],\$systems[\$count]) > \$centre) || system_too_close(\$systems[\$count],\$systems,$UNI[mindist]));";
		/*-------------------------------------------------------*/


	/**********************
	* Ring layout
	***********************/
	} elseif($UNI['map_layout'] == 5){ //ring layout

		$degrees_between_stars = 360 / ($UNI['numsystems'] - 1); //number of degrees between each star
		$present_degrees = 0;

		//ellipse code based on code nabbed from:
		//http://www.geek.casaforge.com/code/ellipse.html
		//PLus KC's nabbed stuff. :) (he comes in useful occassionally ;p )
		/*-------------------------------------------------------*/
		$loop_eval = "
			\$systems[\$count]['x_loc'] = \$centre_x * cos(deg2rad(\$present_degrees)) + \$centre_x;
			\$systems[\$count]['y_loc'] = \$centre_y * sin(deg2rad(\$present_degrees)) + \$centre_y;
			\$present_degrees += \$degrees_between_stars;
		";
		/*-------------------------------------------------------*/

	}

	while(($count = count($systems)) < $UNI['numsystems']) {
		$result = dbr(1); //get name for system.

		//create the new star system.
		$systems[] = array('z_loc' => mt_rand(-30, 30), 'num' => $count, 'links' => '', 'name' => $result['name'], 'fuel' => 0, 'metal' => 0, 'wormhole' => 0, 'event_random' => 0);
		eval($loop_eval);
	}

}


//aims to stop one way links from being created
//checks all systems that have already been linked to see what links have already been created to this location.
function pre_linked($systems, $present_system){
	$links_array = array();
	foreach($systems as $new_sys){
		if((string)$new_sys['links'] != ""){
			$present_links = explode(',', $new_sys['links']);
			if(in_array($present_system, $present_links)){
				$links_array[] = $systems[$new_sys['num']];
			}
		}
	}
	return $links_array;
}


//link the systems
function link_systems_1(&$systems) {
	global $UNI;

	foreach($systems as $system) {
		$numlinks = mt_rand($UNI['minlinks'],$UNI['maxlinks']);

		//find the closest systems to the present system. when $numlinks closest found, link them
		foreach(get_closest_systems($system,$systems,$numlinks) as $linksys) {
			make_link($systems[$system['num']],$systems[$linksys['num']]);
		}
	}

	//add wormholes if appropriate
	if($UNI['wormholes'] > 0 && $UNI['numsystems'] > 15){
		$num_worms = ceil($UNI['numsystems'] / 35);//num wormholes to make

		$worms_placed = array();

		for($a=1; $a <= $num_worms; $a++){//loop through

			$start_loc = mt_rand(2,$UNI['numsystems']);
			while(system_has_wormhole($worms_placed, $start_loc)) {
				$start_loc = mt_rand(2,$UNI['numsystems']);
			}
			$worms_placed[] = $start_loc;//push into wormhole checking array.


			$end_loc = mt_rand(1,$UNI['numsystems']);
			while(system_has_wormhole($worms_placed, $end_loc)) {
				$end_loc = mt_rand(1,$UNI['numsystems']);
			}
			$worms_placed[] = $end_loc;//push into wormhole checking array.

			//make them permanent
			$systems[$start_loc -1]['wormhole'] = $end_loc;
			if (mt_rand(0,10) > 5) {//two way wormhole
				$systems[$end_loc -1]['wormhole'] = $start_loc;
			}
		}
	}
}

//check to see if a star system has a wormhole in it already.
function system_has_wormhole($worms_placed, $this_worm) {
	foreach($worms_placed as $worm) {
		if($worm == $this_worm) {
			return true;
		}
	}
	return false;
}

//function that determines if it's ok to link to a system
function ok_to_link($sys1, $sys2) {
	global $UNI;

	//linking to itself.
	if($sys1['num'] == $sys2['num']) {
		return false;
	}
	
	$sys2_links = explode(',', $sys2['links']);

	//return o.k. if target still has empty links || already linked.
	if((count($sys2_links) < $UNI['maxlinks']) || in_array($sys1['num'], $sys2_links)) {
		return true;
	} else {
		return false;
	}
}

//find the closest systems to link to.
/*
$sys =  linking from
$systems = all systens
$howmany = number of closest systems to return
*/
function get_closest_systems($sys,$systems,$howmany) {
	global $UNI;

	//check to see which systems have already linked to this one.
	$systems_to_link = pre_linked($systems, $sys['num']);
	$howmany -= count($systems_to_link);
	if($howmany < 1){
		return $systems_to_link;
	}

	//establish the distance of all stars in relation to this one
	$dists = array();
	foreach($systems as $system) {
		if(ok_to_link($sys, $system)) {
			$dists[$system['num']] = get_sys_dist($sys,$system);
		}
	}
	reset($dists);
	asort($dists,SORT_NUMERIC);

	//link to as many of the closest systems as can.
	while(count($systems_to_link) < $howmany) {
		if(!$present_sys = each($dists)) {//get a system out of the dist array. RETURN if none.
			return $systems_to_link;
		}

		//too far away to be linked to (Sol System excepted).
		if($present_sys['value'] > $UNI['link_dist'] && $UNI['link_dist'] > 0 && $sys['num'] != 0){
			return $systems_to_link;
		}

		$systems_to_link[] = $systems[$present_sys['key']];
	}
	return $systems_to_link;
}

//work out if a system is too close to another system
function system_too_close($sys,&$systems,$within) {
	foreach($systems as $system) {
		if($system['num'] == $sys['num']) {//same system
			continue;
		}
		if($dist = get_sys_dist($sys,$system) < $within) {//too close
			return true;
		}
	}
	return false;
}

//make a single link between two systems.
function make_link(&$sys1,&$sys2) {
	if((string)$sys1['links'] != "") {
		$sys1warps = explode(',',$sys1['links']);
		if(!in_array($sys2['num'],$sys1warps)) {
			$sys1warps[] = $sys2['num'];
			$sys1['links'] = implode(',',$sys1warps);
		}
	} else {
		$sys1['links'] = $sys2['num'];
	}
	if((string)$sys2['links'] != "") {
		$sys2warps = explode(',',$sys2['links']);
		if(!in_array($sys1['num'],$sys2warps)) {
			$sys2warps[] = $sys1['num'];
			$sys2['links'] = implode(',',$sys2warps);
		}
	} else {
		$sys2['links'] = $sys1['num'];
	}
}

//work out the distance (in pixels) between two star systems.
function get_sys_dist(&$sys1,&$sys2) {
	return (int)round(sqrt(pow($sys1['x_loc']-$sys2['x_loc'],2) + pow($sys1['y_loc']-$sys2['y_loc'],2)));
}

//generate the three global maps.
function render_global($game_id) {
	global $UNI, $systems, $preview, $loc, $exp_sys, $random_filename, $gen_new_maps, $directories;

	$size_x = $UNI['size_x'] + ($UNI['map_border'] * 2);
	$size_y = $UNI['size_y'] + ($UNI['map_border'] * 2);
	$offset_x = $UNI['map_border'];
	$offset_y = $UNI['map_border'];

	$central_star = 1; //this star is the hub of the universe.

	$uv_show_warp_numbers = get_db_var('uv_show_warp_numbers');
	$numsize = $UNI['num_size'];

	$im = imagecreatetruecolor($size_x, $size_y);

	$earthIm = imagecreatefrompng($directories['images'].'/map/earth.png');
	$earthDim = array(imagesx($earthIm), imagesy($earthIm));
	$earthPos = array(-$earthDim[0] / 2, -$earthDim[1] / 2);

	$starIm = imagecreatefrompng($directories['images'].'/map/star.png');
	$starDim = array(imagesx($starIm), imagesy($starIm));
	$starPos = array(-$starDim[0] / 2, -$starDim[1] / 2);

	//allocate the colours
	$color_bg = ImageColorAllocate($im, $UNI['bg_color'][0], $UNI['bg_color'][1], $UNI['bg_color'][2]);
	$color_st = ImageColorAllocate($im, $UNI['num_color'][0], $UNI['num_color'][1], $UNI['num_color'][2]);
	$color_sd = ImageColorAllocate($im, $UNI['star_color'][0], $UNI['star_color'][1], $UNI['star_color'][2] );
	$color_sl = ImageColorAllocate($im, $UNI['link_color'][0], $UNI['link_color'][1], $UNI['link_color'][2] );
	$color_sh = ImageColorAllocate($im, $UNI['num_color3'][0], $UNI['num_color3'][1], $UNI['num_color3'][2] );
	$color_l = ImageColorAllocate($im, $UNI['label_color'][0], $UNI['label_color'][1], $UNI['label_color'][2] );
	$worm_1way_color = ImageColorAllocate($im,$UNI['worm_one_way_color'][0], $UNI['worm_one_way_color'][1], $UNI['worm_one_way_color'][2] );
	$worm_2way_color = ImageColorAllocate($im,$UNI['worm_two_way_color'][0], $UNI['worm_two_way_color'][1], $UNI['worm_two_way_color'][2] );

	//get the star systems from the Db if using pre-genned map.
	if(isset($gen_new_maps)){
		db("select (star_id -1) as num, x_loc, y_loc, wormhole, CONCAT(link_1 -1, ',', link_2 -1, ',', link_3 -1, ',', link_4 -1, ',', link_5 -1, ',', link_6 -1) as links from ${game_id}_stars order by star_id asc");
		while($systems[] = dbr(1));//dump all entries into $systems.
		unset($systems[count($systems)-1]); //remove a surplus entry
	}


	//process stars
	foreach($systems as $star){
		if(!empty($star['links'])){//don't link all systems to 1 automatically.
			$star_links = array_map("plus_one", explode(',', $star['links']));
			$star_id = $star['num'] + 1;

			foreach($star_links as $link){ //make star links
				if($link < 1){
					continue 1;
				}
				$other_star = $systems[$link -1];//set other_star to the link destination.
				imageline($im, ($star['x_loc'] + $offset_x), ($star['y_loc'] + $offset_y), ($other_star['x_loc'] + $offset_x), ($other_star['y_loc'] + $offset_y), $color_sl);
			}
		}

		if(!empty($star['wormhole'])) {//Wormhole Manipulation
			$other_star = $systems[$star['wormhole'] -1];
			if($other_star['wormhole'] == $star_id){ //two way
				imageline($im, ($star['x_loc'] + $offset_x), ($star['y_loc'] + $offset_y), ($other_star['x_loc'] + $offset_x), ($other_star['y_loc'] + $offset_y), $worm_2way_color);
			} else { //one way
				imageline($im, ($star['x_loc'] + $offset_x), ($star['y_loc'] + $offset_y), ($other_star['x_loc'] + $offset_x), ($other_star['y_loc'] + $offset_y), $worm_1way_color);
			}
		}
	}


	$central_star = 0; //hack to take into account arrays start with 0.

	foreach($systems as $star){ //place the star itself. This is done after the lines, so the text comes on top.

		$off = array(-5, 4); //offset of text from star location

		if ($star['num'] == $central_star) {
			imagecopy($im, $earthIm, $star['x_loc'] + $offset_x + $earthPos[0], $star['y_loc'] + $offset_y + $earthPos[1], 0, 0, $earthDim[0], $earthDim[1]);
		} else {
			imagecopy($im, $starIm, $star['x_loc'] + $offset_x + $starPos[0], $star['y_loc'] + $offset_y + $starPos[1], 0, 0, $starDim[0], $starDim[1]);
		}

		//only show warp numbers if admin wants them.
		if($uv_show_warp_numbers == 1){

			if ($central_star === $star['num']) {
				$off = array(-5, 9);

				//write the star number under the star
				imagestring($im, $UNI['num_size'] + 2, $star['x_loc'] + $offset_x + $off[0], $star['y_loc'] + $offset_y + $off[1], $star['num'] + 1, $color_st);
			} else {
				imagestring($im, $UNI['num_size'], $star['x_loc'] + $offset_x + $off[0], $star['y_loc'] + $offset_y + $off[1], $star['num'] + 1, $color_st);
			}
		}
	}


	//for just a preview we can quit while we're ahead.	
	if(isset($preview)){
		Header("Content-type: image/png");
		ImagePng($im);
		ImageDestroy($im);
		exit();
	}

	//Draw title centered on the horizontal
	imagestring($im, 5, (($size_x/2)-80), 5, "Universal Star Map", $color_l);

	//Create buffer image
	$bb_im = imagecreatetruecolor(($UNI['size_x'] + $UNI['localmapwidth']), ($UNI['size_y'] + $UNI['localmapheight']));

	ImageColorAllocate($im, $UNI['bg_color'][0], $UNI['bg_color'][1], $UNI['bg_color'][2]);
	ImageCopy($bb_im, $im, ($UNI['localmapwidth'] / 2), ($UNI['localmapheight'] / 2), $offset_x, $offset_y, $size_x, $size_y);

	//Create printable map
	$p_im = imagecreatetruecolor($size_x, $size_y);
	ImageColorAllocate($p_im, $UNI['print_bg_color'][0], $UNI['print_bg_color'][1], $UNI['print_bg_color'][2]);
	ImageCopy($p_im, $im, 0, 0, 0, 0, $size_x, $size_y);

	//Replace colors
	$index = ImageColorExact($p_im, $UNI['bg_color'][0], $UNI['bg_color'][1], $UNI['bg_color'][2]);
	ImageColorSet($p_im, $index, $UNI['print_bg_color'][0], $UNI['print_bg_color'][1], $UNI['print_bg_color'][2]);
	$index = ImageColorExact($p_im, $UNI['link_color'][0], $UNI['link_color'][1], $UNI['link_color'][2]);
	ImageColorSet($p_im, $index, $UNI['print_link_color'][0], $UNI['print_link_color'][1], $UNI['print_link_color'][2]);
	$index = ImageColorExact($p_im, $UNI['num_color'][0], $UNI['num_color'][1], $UNI['num_color'][2]);
	ImageColorSet($p_im, $index, $UNI['print_num_color'][0], $UNI['print_num_color'][1], $UNI['print_num_color'][2]);
	$index = ImageColorExact($p_im, $UNI['num_color3'][0], $UNI['num_color3'][1], $UNI['num_color3'][2]);
	ImageColorSet($p_im, $index, $UNI['print_num_color'][0], $UNI['print_num_color'][1], $UNI['print_num_color'][2]);
	$index = ImageColorExact($p_im, $UNI['star_color'][0], $UNI['star_color'][1], $UNI['star_color'][2]);
	ImageColorSet($p_im, $index, $UNI['print_star_color'][0], $UNI['print_star_color'][1], $UNI['print_star_color'][2]);

	//Draw new label
	ImageFilledRectangle($p_im, 0, 0, $size_x, $UNI['map_border'], ImageColorExact($p_im, $UNI['print_bg_color'][0], $UNI['print_bg_color'][1], $UNI['print_bg_color'][2]));
	imagestring($p_im, 5, (($size_x/2)-80), 5, "Printable Star Map", ImageColorExact($p_im, $UNI['print_label_color'][0], $UNI['print_label_color'][1], $UNI['print_label_color'][2]));

	//get random filename. primarily for it re-genning maps.
	if(empty($random_filename)){
		db("select random_filename from se_games where db_name = '$game_id'");
		$temp_filename = dbr(1);
		$random_filename = $temp_filename['random_filename'];
	}

	//Save map and finish
	ImagePng($im, "$directories[images]/${game_id}_maps/sm_full_{$random_filename}.png");
	ImageDestroy($im);
	ImagePng($bb_im, "$directories[images]/${game_id}_maps/bb_full_{$random_filename}.png");
	ImageDestroy($bb_im);
	ImagePng($p_im, "$directories[images]/${game_id}_maps/psm_full_{$random_filename}.png");
	ImageDestroy($p_im);
}

//draw the local maps.
function render_local($game_id) {
	global $UNI, $random_filename, $directories;

	$full_map = imagecreatefrompng("$directories[images]/${game_id}_maps/bb_full_{$random_filename}.png");

	db("select star_id, x_loc, y_loc from ${game_id}_stars");
	while($star = dbr()) {

		$im = imagecreatetruecolor($UNI['localmapwidth'], $UNI['localmapheight']);

		imagecopy($im, $full_map, 0, 0, $star['x_loc'], $star['y_loc'], $UNI['localmapwidth'], $UNI['localmapheight']);

		ImagePng($im, "$directories[images]/${game_id}_maps/sm{$star['star_id']}_{$random_filename}.png");

		ImageDestroy($im);
	}

	ImageDestroy($full_map);
}


function plus_one($a) {
	return $a + 1;
}

$since_last = array(0 => 0);

// a little function that will tell the time between different script locations.
function print_time (){
	global $bu_start_time, $since_last;
	$end_time = explode(" ",microtime());
	$end_time = ($end_time[1] + $end_time[0]);
	$this_time = ($end_time - $bu_start_time);
	echo $this_time." Total seconds --- This Cycle: ".($this_time - $since_last[count($since_last) -1])." seconds<p />";
	$since_last[] = $this_time;
}

?>