<?php
require_once("user.inc.php");

$text='';
$filename='planet_list.php';

#little code to allow users to sort planets asc, desc in a number of criteria
if(isset($sort_planets)){
	if($sorted==1){
		$going = "asc";
		$sorted=2;
	} else {
		$going = "desc";
		$sorted=1;
	}
	db("select planet_img,planet_name,location,fighters,colon,cash,metal,fuel,elect from ${db_name}_planets where login_id = $user[login_id] and location != 1 order by '$sort_planets' $going");
} else {
	db("select planet_img,planet_name,location,fighters,colon,cash,metal,fuel,elect from ${db_name}_planets where login_id = $user[login_id] and location != 1 order by fighters desc, planet_name asc");
	$sorted = "";
}

$clan_planet = dbr(1);

if($clan_planet) {
	$text .= make_table(array('',"<a href='$filename?sort_planets=planet_name&sorted=$sorted'>".$cw['planet_name']."</a>","<a href='$filename?sort_planets=location&sorted=$sorted'>".$cw['location']."</a>","<a href='$filename?sort_planets=fighters&sorted=$sorted'>".$cw['fighters']."</a>","<a href='$filename?sort_planets=colon&sorted=$sorted'>".$cw['colonists']."</a>","<a href='$filename?sort_planets=cash&sorted=$sorted'>".$cw['cash']."</a>","<a href='$filename?sort_planets=metal&sorted=$sorted'>".$cw['metal']."</a>","<a href='$filename?sort_planets=fuel&sorted=$sorted'>".$cw['fuel']."</a>","<a href='$filename?sort_planets=elect&sorted=$sorted'>".$cw['electronics']."</a>"));
	while($clan_planet) {
		$clan_planet['planet_img'] = '<img src="images/planets/'.$clan_planet['planet_img'].'_tn.jpg" border=0>';
		$text .= make_row($clan_planet);
		$clan_planet = dbr(1);
	}
	$text .= "</table><br />";
} else {
	$text .= $st[1815];
}

	

$rs = "<p /><a href='location.php'>".$cw['back_star_system']."</a>";
// print page
print_page($cw['planet_list'], $text);
?>