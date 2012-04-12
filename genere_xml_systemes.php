<?php
$sortie = "<?xml version='1.0' encoding=\"ISO-8859-1\" ?>
<main>\n";

$game = $_GET['game'];
if ($game) {

	include("games/config.dat.php");
	include("includes/$game"."_vars.inc.php");
	
	$midx = $GAME_VARS['uv_size_x_width']/2;
	$midy = $GAME_VARS['uv_size_y_height']/2;

	$database_link = @mysql_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD) or write_to_error_log("No connection to the Database could be created.<p />The following error was reported:<br /><b>".mysql_error()."</b>");
	mysql_select_db(DATABASE, $database_link) or mysql_die("");


	$resultat2 = mysql_query("select * from $game"."_stars");
	while ($systeme = mysql_fetch_array($resultat2)) {
		$sortie .= "\t<systeme>\n";
		$sortie .= "\t\t<x>".($systeme['x_loc']-$midx)."</x>\n";
		$sortie .= "\t\t<y>".($systeme['y_loc']-$midy)."</y>\n";
		$sortie .= "\t\t<z>".($systeme['z_loc'])."</z>\n";
		$sortie .= "\t\t<id>".($systeme['star_id'])."</id>\n";
		$sortie .= "\t\t<nom>".($systeme['star_name'])."</nom>\n";
		$sortie .= "\t</systeme>\n";
	}

	$sortie .= "</main>";

	$fichier = fopen("includes/systemes_$game.xml", "w");
	fwrite($fichier, $sortie);
	fclose($fichier);
}
?>