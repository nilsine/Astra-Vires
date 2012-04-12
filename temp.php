<?php
include("games/config.dat.php");

$database_link = @mysql_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD) or write_to_error_log("No connection to the Database could be created.<p />The following error was reported:<br /><b>".mysql_error()."</b>");
mysql_select_db(DATABASE, $database_link) or mysql_die("");


$resultat = mysql_query("select * from alpha_stars where star_id!=1");
while ($data = mysql_fetch_array($resultat)) {
	mysql_query("update alpha_stars set z_loc=".(rand(-30, 30))." where star_id=".$data['star_id']);
}

//for ($i=1; $i<=350; $i++) print "$i,";

/*include("includes/langage_en3.inc.php");

foreach($cw as $c => $v) {
	print strip_tags($v)."<br />";
}

foreach($st as $c => $v) {
	print strip_tags($v)."<br />";
}*/
?>