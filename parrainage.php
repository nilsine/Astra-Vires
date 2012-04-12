<?php
require_once("user.inc.php");

$text = '';

if ($_GET['agdt']) {
	$text .= "<p>";
	if (!$p_user['gdt']) {
		$text .= "<span class='rouge'>".$st[1812]."</span>";
	} else {
		dbn("update user_accounts set gdt=gdt-1 where login_id=$login_id");
		dbn("update ${db_name}_users set gdt=gdt+1 where login_id=$login_id");
		$p_user['gdt']--;
		$text .= "<span class='vert'>".$st[1813]."</span>";
	}
	$text .= "</p>";
}

$text .= '<h3>'.$st[1803].'</h3>';
$text .= '<p>'.$st[1804].'</p>';
$text .= '<p>'.$st[1805].'<p>';

$text .= "<span class='code'>http://www.astravires.fr/?pid=".$user['login_id']."</span>";

$text .= '<p>'.$st[1806].'</p>';

$text .= "<span class='code'>[url=http://www.astravires.fr/?pid=".$user['login_id']."]Astra Vires: Jeu gratuit jouable par navigateur[/url]</span><br />";

$text .= "<p><h3>".$cw['vos_gdt']."</h3>";
$text .= "".sprintf($st[1809], $p_user['gdt']);
if ($p_user['gdt']) $text .= ", <a href='parrainage.php?agdt=1'>".$st[1810]."</a> (".$st[1811].")";
$text .= "</p>";

$text .= "<h3>".$cw['vos_filleuls']."</h3>";
$text .= "<p>".$st[1808]."</p>";

db("select * from user_accounts where id_parrain=$login_id");
while ($data = dbr()) {
	$actif = ($data['parrainage_actif']) ? "<span class='vert'>".$cw['actif']."</span>":"<span class='rouge'>".$cw['non_actif']."</span>";
	$text .=  "<b>".$data['login_name']."</b> - $actif<br />";
}

if (!dbc()) $text .=  "<i>".$st[1807]."</i>";

$rs = "<p /><a href='location.php'>".$cw['back_star_system']."</a>";
// print page
print_page($cw['parrainage'], $text);
