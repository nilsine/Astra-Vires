<?php
require_once("user.inc.php");

if(!isset($sys1) || !isset($sys2)) {
	echo $cw['required_parems_missing'];
	exit();
} elseif($GAME_VARS['uv_explored'] == 0 && $user['explored_sys'] != -1){
	echo $st[74];
	exit();
}

db_connect();

db("select x_loc,y_loc from ${db_name}_stars where star_id = '$sys1'");
$star_one = dbr(1);

db("select x_loc,y_loc from ${db_name}_stars where star_id = '$sys2'");
$star_two = dbr(1);

if($game_info['random_filename'] == -1){
	$file_rand = "";
} else {
	$file_rand = "_".$game_info['random_filename'];
}

$im = imagecreatefrompng("$directories[images]/${db_name}_maps/sm_full{$file_rand}.png");

$red = ImageColorAllocate($im, 255,50,50);
$red2 = ImageColorAllocate($im, 255,150,150);
$green = ImageColorAllocate($im, 50,255,50);
$green2 = ImageColorAllocate($im, 50,255,150);

imagestring($im,3,($star_one['x_loc']-10),$star_one['y_loc']-5,$cw['you_are_here'],$red2);

imagearc($im, ($star_one['x_loc']+30), ($star_one['y_loc']+25), 30, 30, 0, 360, $red); 

if($sys1 != $sys2){
	#imagestring($im,3,($star_two[x_loc]-15),$star_two[y_loc]+40,"System $sys2 here",$green2);
	imagearc($im, ($star_two['x_loc']+29), ($star_two['y_loc']+25), 35, 35, 0, 360, $green); 
}
Header("Content-type: image/png");
ImagePng($im);

ImageDestroy($im);
?>