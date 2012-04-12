<?php
/**********
* Shows the large universe map.
* Can find systems, show dynmaic, regular or printable maps.
* Last audited: 23/5/04 By Moriarty
***********/

require_once("user.inc.php");
$key_text = "";

//find out random string that is attached to the file-names.
if($game_info['random_filename'] == -1){
	$file_rand = "";
} else {
	$file_rand = "_".$game_info['random_filename'];
}

//printable map.
if(isset($_GET['print']) && ($GAME_VARS['uv_explored'] == 1 || $user['explored_sys'] == -1)) {
	$map_url = "$directories[images]/${db_name}_maps/psm_full{$file_rand}.png";

//find system, but only if allowed.
} elseif(isset($_POST['find']) && (($GAME_VARS['uv_explored'] == 1 || $user['explored_sys'] == -1) || $user['login_id'] == 1)) {
	$find = (int)$_POST['find'];
	db("select count(star_id) from ${db_name}_stars");
	$max_sect = dbr();

	//no such system
	if($find < 1 || $find > $max_sect[0] || !$find) {
		$rs = "<p /><a href=\"javascript: history.back()\">".$cw['back']."</a>";
		print_s_page($cw['error'], sprintf($st[85], $max_sect[0]));
	} else {
		$map_url = "star_find.php?sys1=$user[location]&sys2=$find";
	}

//show dynamic big-map
}elseif($GAME_VARS['uv_explored'] == 0 && $user['explored_sys'] != -1 && $user['login_id'] != 1){ 
	//trying to search unexplored map.
	if(isset($_POST['find'])){
		print_s_page($cw['error'], $st[86]);
	}
	$map_url = "dynamic_map.php?exp_sys={$user['explored_sys']}&loc=1&large_map=1";
	$key_text = "<p />".$cw['star_numbers'].":<br /><font color=#00FFFF>".$cw['light_blue']."</font> = ".$cw['explored_systems'].".<br /><font color=#0088FF>".$cw['dark_blue']."</font> = ".$cw['unexplored_systems'].".<p />";

//show static big-map
} else {
	$map_url = "$directories[images]/${db_name}_maps/sm_full{$file_rand}.png";
}

if(isset($_GET['print'])) {
	$link_url = "<a href='map.php'>".$cw['normal_map']."</a> - ";
} else {
	$link_url = "<a href='map.php?print=1'>".$cw['printable_map']."</a> - ";
}


$out = "<center>
<form action='map.php' method='POST'><b>".$cw['find_system'].": </b><input type='text' size='4' name='find' /> <input type='submit' value='".$cw['search']."' /></form><br />
<img src='$map_url' border='0' alt='".$st[87]."'>
<br />";

if($GAME_VARS['uv_wormholes'] == 1){
	$out .= $cw['key'].":<br />".$cw['wormholes'].":<br /><font color=#FFFF44>".$cw['yellow_lines']."</font> = ".$cw['one_way_wormholes'].".<br /><font color=#00FF00>".$cw['green_lines']."</font> = ".$cw['double_way_wormholes']."<p />";
}
$out .= $key_text;
$out .= $link_url;
$rs = "<a href='javascript:self.close();'>".$cw['close_map']."</a>";

print_s_page($cw['universal_starmap'], $out);
?>
