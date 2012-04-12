<?php
/*
Script for dynamically generating star maps (local and universal), based upon what the user has explored.
Copyright Jonathan "Moriarty" 2003,2004
Created 12th July 2003
//Last audited: 23/5/04 by Moriarty
*/

require_once("user.inc.php");

$UNI['map_border'] = 25; //border on all sides around the image (stops numbers going off the edge) (pixels).
$UNI['numsystems'] = $game_info['num_stars'];
$UNI['num_size'] = 2; //font size for system numbers (on map).
$UNI['bg_color'] = array(0,0,0); //background colour of map
$UNI['link_color'] = array(140,140,140); //colour of links between systems
$UNI['num_color'] = array(0,255,255);//Most system numbers
$UNI['unexp_colour'] = array(0,130,255);//link only system numbers
$UNI['num_color2'] = array(255,255,255);//Current system number
$UNI['num_color3'] = array(255,0,0);//Sol color
$UNI['star_color'] = array(255,255,255);
$UNI['worm_one_way_color'] = array(230,230,64); //yellow
$UNI['worm_two_way_color'] = array(0,230,0); //green
$UNI['label_color'] = array(0, 255, 0);
$UNI['localmapwidth'] = 300; //width of 'local area' map.
$UNI['localmapheight'] = 300; //height of 'local area' map.
$sol = 1; // system Sol is in.

if(!isset($_GET['large_map'])){
	$large_map = false;
} else {
	$large_map = true;
}
if($user['explored_sys'] == -1){
	exit();
}

$exp_sys_arr = explode(",", $user['explored_sys']);
$systems = array();
$all_links = array();


db("select * from ${db_name}_stars");
while($temp_star_store = dbr(1)){
	$temp_star_store['links'] = array($temp_star_store['link_1'], $temp_star_store['link_2'], $temp_star_store['link_3'], $temp_star_store['link_4'], $temp_star_store['link_5'], $temp_star_store['link_6']);
	$systems[$temp_star_store['star_id']] = $temp_star_store;
}
unset($temp_star_store);

if($large_map){//creating large maps
	$size_x = $GAME_VARS['uv_size_x_width'] + ($UNI['map_border'] * 2);
	$size_y = $GAME_VARS['uv_size_y_height'] + ($UNI['map_border'] * 2);
	$offset_x = $UNI['map_border'];
	$offset_y = $UNI['map_border'];
	$central_star = 1;
} else {
	$size_x = $UNI['localmapwidth'];
	$size_y = $UNI['localmapwidth'];
	$offset_x = -$systems[$user['location']]['x_loc'] + ($UNI['localmapwidth'] / 2);
	$offset_y = -$systems[$user['location']]['y_loc'] + ($UNI['localmapwidth'] / 2);
	$central_star = $user['location'];
}

$im = imagecreatetruecolor($size_x, $size_y);

//allocate the colours
$color_bg = ImageColorAllocate($im, $UNI['bg_color'][0], $UNI['bg_color'][1], $UNI['bg_color'][2]);
$color_st = ImageColorAllocate($im, $UNI['num_color'][0], $UNI['num_color'][1], $UNI['num_color'][2]);
$color_sd = ImageColorAllocate($im, $UNI['star_color'][0], $UNI['star_color'][1], $UNI['star_color'][2] );
$color_linked = ImageColorAllocate($im, $UNI['unexp_colour'][0], $UNI['unexp_colour'][1], $UNI['unexp_colour'][2] );
$color_sl = ImageColorAllocate($im, $UNI['link_color'][0], $UNI['link_color'][1], $UNI['link_color'][2] );
$color_sh = ImageColorAllocate($im, $UNI['num_color3'][0], $UNI['num_color3'][1], $UNI['num_color3'][2] );
$color_l = ImageColorAllocate($im, $UNI['label_color'][0], $UNI['label_color'][1], $UNI['label_color'][2] );
$worm_1way_color = ImageColorAllocate($im,$UNI['worm_one_way_color'][0], $UNI['worm_one_way_color'][1], $UNI['worm_one_way_color'][2] );
$worm_2way_color = ImageColorAllocate($im,$UNI['worm_two_way_color'][0], $UNI['worm_two_way_color'][1], $UNI['worm_two_way_color'][2] );


//process stars
foreach($systems as $star_id => $star){
	if(!empty($star['links'])){//don't link all systems to 1 automatically.
		$star_links = $star['links'];

		//don't make a star's links if that star is not explored.
		if(array_search($star_id, $exp_sys_arr) === false){
			continue;
		} else {
			//collect all valid links.
			$all_links = array_merge($all_links, $star['links']);
			$all_links[] = $star['wormhole'];
		}

		//make star links
		foreach($star_links as $link){
			if($link < 1){
				continue 1;
			}

			//set $other_star to the link destination.
			$other_star = $systems[$link];
			imageline($im, ($star['x_loc'] + $offset_x), ($star['y_loc'] + $offset_y), ($other_star['x_loc'] + $offset_x), ($other_star['y_loc'] + $offset_y), $color_sl);
		}
	}

	if($large_map){
		if(!empty($star['wormhole'])) {//Wormhole link drawing
			$other_star = $systems[$star['wormhole']];
			if($other_star['wormhole'] == $star_id){ //two way
				imageline($im, ($star['x_loc'] + $offset_x), ($star['y_loc'] + $offset_y), ($other_star['x_loc'] + $offset_x), ($other_star['y_loc'] + $offset_y), $worm_2way_color);
			} else { //one way
				imageline($im, ($star['x_loc'] + $offset_x), ($star['y_loc'] + $offset_y), ($other_star['x_loc'] + $offset_x), ($other_star['y_loc'] + $offset_y), $worm_1way_color);
			}
		}
	}
}

//remove duplicate links
$all_links = array_unique($all_links);
array_unshift($all_links, 0);


$earthIm = imagecreatefrompng($directories['images'].'/map/earth.png');
$earthDim = array(imagesx($earthIm), imagesy($earthIm));
$earthPos = array(-$earthDim[0] / 2, -$earthDim[1] / 2);

$starIm = imagecreatefrompng($directories['images'].'/map/star.png');
$starDim = array(imagesx($starIm), imagesy($starIm));
$starPos = array(-$starDim[0] / 2, -$starDim[1] / 2);

//place the star itself. This is done after the lines, so the text comes out on top.
foreach($systems as $star_id => $star){

	//Place and Highlight central system
	if($star_id == $sol) {
		imagecopy($im, $earthIm, $star['x_loc'] + $offset_x + $earthPos[0], $star['y_loc'] + $offset_y + $earthPos[1], 0, 0, $earthDim[0], $earthDim[1]);
		imagestring($im, $UNI['num_size'], ($star['x_loc'] + $offset_x - 3), ($star['y_loc'] + $offset_y + 9), $star_id, $color_sh);

	//place normal Star
	} else {

		imagecopy($im, $starIm, $star['x_loc'] + $offset_x + $starPos[0], $star['y_loc'] + $offset_y + $starPos[1], 0, 0, $starDim[0], $starDim[1]);

		if($GAME_VARS['uv_show_warp_numbers'] == 1){

			//brighter colour number for explored systems
			if(array_search($star_id, $exp_sys_arr) !== false) {
				imagestring($im, $UNI['num_size'], ($star['x_loc'] + $offset_x -4), ($star['y_loc'] + $offset_y + 4), $star_id, $color_st);

			//unexplored sys that are linked, darker colour
			} elseif(array_search($star_id, $all_links) !== false){
				imagestring($im, $UNI['num_size'], ($star['x_loc'] + $offset_x - 4), ($star['y_loc'] + $offset_y + 4), $star_id, $color_linked);
			}
		}
	}
}

Header("Content-type: image/png");
ImagePng($im);
ImageDestroy($im);
exit();
?>