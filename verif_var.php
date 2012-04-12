<?php
include("includes/langage_en.inc.php");

function verif_var($var) {
	$rep = opendir('./');
	$trouve = false;
	while ($fichier = readdir($rep)) {
		if ($fichier != '.' && $fichier != '..') {
			if (is_file($fichier)) {
				$php = file_get_contents($fichier);
				if (substr_count($php, $var)) {
					$trouve = true;
//					print "trouve dans $fichier <br />";
					break;
				}
			}
		}
	}
	return $trouve;
}

$langage = file_get_contents('includes/langage_en2.inc.php');

foreach($cw as $c => $v) {
	$var = "\$cw['$c']";
	if (!verif_var($var)) {
		print "$var<br />";
		$langage = str_replace($var, '//', $langage);
		flush();
	}
}

foreach($st as $c => $v) {
	$var = "\$st[$c]";
	if (!verif_var($var)) {
		print "$var<br />";
		$langage = str_replace($var, '//', $langage);
		flush();
	}
}

file_put_contents('includes/langage_en3.inc.php', $langage);
?>