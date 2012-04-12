<?php
include('common.inc.php');

db_connect();

switch ($argv[1]) {
	case 'inscriptions':
		db("select login_id from user_accounts where login_name != '' and signed_up > ".(time() - 24*3600));
		echo dbc();
		echo "\n";
		db("select login_id from user_accounts where login_name != '' and bp_user_id != 0 and signed_up > ".(time() - 24*3600));
		echo dbc();
		break;
	case 'connectes':
		db("select login_id from user_accounts where last_request > ".(time() - 300));
		echo dbc();
		echo "\n";
		db("select login_id from user_accounts where bp_user_id != 0 and last_request > ".(time() - 300));
		echo dbc();
		break;
	case 'nbjoueurs':
		db("select login_id from user_accounts where login_count >= 4 and last_request > ".(time() - 4*24*3600));
		echo dbc();
		echo "\n";
		db("select login_id from user_accounts where bp_user_id != 0 and login_count >= 4 and last_request > ".(time() - 4*24*3600));
		echo dbc();
		break;
	case 'ressources':
		$gal = $argv[2];
		db("select avg(metal) as titane from ${gal}_stars where star_id > 1");
		$data = dbr();
		echo $data['titane'];
		echo "\n";
		db("select avg(fuel) as larium from ${gal}_stars where star_id > 1");
		$data = dbr();
		echo $data['larium'];
		break;
	default:
		echo 0;		
}

