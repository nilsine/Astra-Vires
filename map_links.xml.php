<?php
header("Content-type: text/xml");
print "<?xml version='1.0' encoding=\"ISO-8859-1\" ?>
<main>\n";

$login_id = $_GET['login_id'];
$game = $_GET['game'];
if ($login_id && $login_id != 'undefined' && $game) {

	include("games/config.dat.php");
	include("includes/$game"."_vars.inc.php");

	$midx = $GAME_VARS['uv_size_x_width']/2;
	$midy = $GAME_VARS['uv_size_y_height']/2;

	$database_link = @mysql_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD) or write_to_error_log("No connection to the Database could be created.<p />The following error was reported:<br /><b>".mysql_error()."</b>");
	mysql_select_db(DATABASE, $database_link) or mysql_die("");

	$resultat = mysql_query("select explored_sys, location from $game"."_users where login_id=$login_id");
	$user = mysql_fetch_array($resultat);
	$etoiles = explode(',', $user['explored_sys']);
	foreach($etoiles as $etoile) {
		$resultat2 = mysql_query("select * from $game"."_stars where star_id=$etoile");
		$origine = mysql_fetch_array($resultat2);
		for ($i=1; $i<=7; $i++) {
			$type_lien = '';
			if ($origine['link_'.$i]) { // lien normal
				$resultat3 = mysql_query("select * from $game"."_stars where star_id=".$origine['link_'.$i]);
				$destination = mysql_fetch_array($resultat3);
				$type_lien = "normal";
			} elseif ($i == 7 && $origine['wormhole']) { // lien wormhole
				$resultat3 = mysql_query("select * from $game"."_stars where star_id=".$origine['wormhole']);
				$destination = mysql_fetch_array($resultat3);
				if ($origine['star_id'] == $destination['wormhole'])
					$type_lien = "wormhole2"; // double sens
				else
					$type_lien = "wormhole1"; // un sens
			}
			if ($type_lien) {
				print "\t<lien>\n";
				print "\t\t<origine>\n";
				print "\t\t\t<x>".($origine['x_loc']-$midx)."</x>\n";
				print "\t\t\t<y>".($origine['y_loc']-$midy)."</y>\n";
				print "\t\t\t<z>".($origine['z_loc'])."</z>\n";
				print "\t\t</origine>\n";
				print "\t\t<destination>\n";
				print "\t\t\t<x>".($destination['x_loc']-$midx)."</x>\n";
				print "\t\t\t<y>".($destination['y_loc']-$midy)."</y>\n";
				print "\t\t\t<z>".($destination['z_loc'])."</z>\n";
				print "\t\t\t<id>".($destination['star_id'])."</id>\n";
				print "\t\t\t<nom>".(trim($destination['star_name']))."</nom>\n";
				print "\t\t</destination>\n";
				print "\t\t<type>$type_lien</type>\n";
				print "\t</lien>\n";
			}
		}
	}
}

print "</main>";
?>