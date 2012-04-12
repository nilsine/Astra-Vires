<?php
/***********
* Processes a user's signup request
* Last major changes - April/may 2004
* Last audited: 23/5/04 By Moriarty
************/
require("common.inc.php");

//Connect to the database
db_connect();

$rs = "<br /><br /><a href='javascript:history.back()'>".$st[675]."</a>";

$id = (int)$_POST['id'];
$fb_id = (int)$_POST['fb_id'];
$pseudo = trim($_POST['pseudo']);
db("select login_name from user_accounts where login_name like '$pseudo'");
$pseudo_pris = dbc();

if ($id && !$fb_id) {
	if (!$_POST['pseudo']) {
		print_s_page('Erreur', "Vous n'avez pas choisi de pseudo $rs");
	} elseif ($pseudo_pris) {
		print_s_page('Erreur', "Ce pseudo est déjà pris, veuillez en choisir un autre $rs");
	} else {
		db("select * from user_accounts where login_id=$id");
		$data = dbr();
		if (dbc()) {
			dbn("update user_accounts set login_name='$pseudo' where login_id=$id");
			require_once('includes/session_funcs.inc.php');
			login_to_server($pseudo, '', $data['bp_user_id']);
			header("location: game_listing.php");
		} else {
			print_s_page('Erreur', "Ce compte n'existe pas $rs");
		}
	}
}elseif (!$id && $fb_id)
{//facebook
//	make login name safe.
	$login_name = trim(mysql_escape_string(strip_tags((string)$_POST['pseudo'])));

	$error_str = "";

	/*******************
	* Check non-optional
	*******************/

	//ensure login name and that it is valid.
	if(empty($login_name)) {
		$error_str .= "<p />".$st[676];

	} elseif((strcmp($login_name,htmlspecialchars($login_name))) || strlen($login_name) < 3 || (eregi("[^a-z0-9~@$%&*_+-=£§¥²³µ¶Þ×€ƒ™ ]",$login_name))) {
		$error_str .= "<p />".$st[677];
	}

	//make email string safe
	$email_address = trim(mysql_escape_string((string)$fbuser->email));

	//ensure one has been entered
	if(empty($email_address)) {
		$error_str .= "<p />".$st[682];
	}

	// check for existing username
	db("select login_id from user_accounts where login_name = '$login_name'");
	$user_check = dbr(1);
	if(!empty($user_check['login_id'])) {
		print_s_page($st[686], $st[687]);
	}

		// generate auth number
	mt_srand((double)microtime()*1000000);
	$auth = mt_rand(0,mt_getrandmax());

	$gdt = ($id_parrain) ? 1:0;
	dbn("insert into user_accounts (login_name, passwd, mdp, auth, signed_up, id_parrain, gdt, email_address, aim, icq, msn, yim, con_speed, fb_user_id) VALUES('$login_name', '', '', '$auth', '".time()."', $id_parrain, $gdt, '$email_address', '', '', '', '', '".(int)$_POST['con_speed']."', '".(int)$fb_id."')");
	$login_id = mysql_insert_id();

	//post on facebook wall
	fb_wallpost_wosdk_api('Astra Vires', "{*actor*} vient juste de rejoindre l'univers d'Astra Vires");

	if(SENDMail == 1) {

		$message = sprintf($st[689], URL_PREFIX, $login_name, $auth);

		if(send_mail(SERVER_NAME, $_SERVER['SERVER_ADMIN'], $_POST['real_name'], $email_address, SERVER_NAME." Authorisation Code", $message)){
			echo $st[690]."<p />";
			echo $st[691]."<p />";
		} else {
			echo $st[692]."<p />";
			echo $st[693]."<p />";
		}

	} else { //not sending auth e-mail, so set auth to -5
		dbn("update user_accounts set auth = '0' where login_id = '$login_id'");
	}

	insert_history($login_id,$st[694]);

	$rs = "";

	print_s_page($st[695], $st[696]." <br /><a href='".URL_PREFIX."/'>".$cw['click_here']."</a>".$st[697], 'inscription_ok');
//END of facebook
} else {

	//make login name safe.
	$login_name = trim(mysql_escape_string(strip_tags((string)$_POST['pseudo'])));

	$error_str = "";

	/*******************
	* Check non-optional
	*******************/

	//ensure login name and that it is valid.
	if(empty($login_name)) {
		$error_str .= "<p />".$st[676];

	} elseif((strcmp($login_name,htmlspecialchars($login_name))) || strlen($login_name) < 3 || (eregi("[^a-z0-9~@$%&*_+-=£§¥²³µ¶Þ×€ƒ™ ]",$login_name))) {
		$error_str .= "<p />".$st[677];
	}

	//pass is too short
	if(strlen($_POST['mdp']) < 5) {
		$error_str .= "<p />".$st[679];

		//password too similar to user account name
	} elseif(levenshtein($_POST['mdp'], $login_name) <= 1) {
		$error_str .= "<p />".$st[680];

		// check passwds match
	} elseif($_POST['mdp'] != $_POST['mdp2']) {
		$error_str .= "<p />".$st[681];
	}


	//Pattern used to determine if e-mail addy is valid
	$pattern = "^[-_.[:alnum:]]+@((([[:alnum:]]|[[:alnum:]][[:alnum:]-]*[[:alnum:]])\.)+(ad|ae|aero|af|ag|ai|al|am|an|ao|aq|ar|arpa|as|at|au|aw|az|ba|bb|bd|be|bf|bg|bh|bi|biz|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|com|coop|cr|cs|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|eh|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|in|info|int|io|iq|ir|is|it|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|mg|mh|mil|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|museum|mv|mw|mx|my|mz|na|name|nc|ne|net|nf|ng|ni|nl|no|np|nr|nt|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|pro|ps|pt|pw|py|qa|re|ro|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw)|(([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5])\.){3}([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]))$";



	//make email string safe
	$email_address = trim(mysql_escape_string((string)$_POST['email']));

	//ensure one has been entered
	if(empty($email_address)) {
		$error_str .= "<p />".$st[682];

		//invalid e-mail addy
	} elseif(!eregi($pattern, $email_address)){
		$error_str .= "<p />".$st[683];


	}

	//there was an error somewhere. so quit signup
	if($error_str != ""){
		print_s_page($cw['sign_up'], "<b class='b1'>".$cw['warning']."</b>! ".$st[685]." ".$error_str);
	}

	// check for existing username
	db("select login_id from user_accounts where login_name = '$login_name'");
	$user_check = dbr(1);
	if(!empty($user_check['login_id'])) {
		print_s_page($st[686], $st[687]);
	}


	// check for existing email_address
	db("select login_id from user_accounts where email_address = '$email_address'");
	$mail_check = dbr(1);
	if(!empty($mail_check['login_id'])) {
		print_s_page($st[686], $st[688]);
	}

	/*******************
	* All optionals are acceptable. Begin account creation
	********************/



	//if user has entered aim number they will quite probably want to see aim users. Otherwise they won't by default
	if($_POST['aim']){
		$aim_show=1;
	} else {
		$aim_show=0;
	}

	//if user has entered icq number they will quite probably want to see icq users. Otherwise they won't by default
	if($_POST['icq']){
		$icq_show=1;
	} else {
		$icq_show=0;
	}


	// generate auth number
	mt_srand((double)microtime()*1000000);
	$auth = mt_rand(0,mt_getrandmax());

	$gdt = ($id_parrain) ? 1:0;
	dbn("insert into user_accounts (login_name, passwd, mdp, auth, signed_up, id_parrain, gdt, email_address, aim, icq, msn, yim, con_speed) VALUES('$login_name', '".md5($_POST['mdp'])."', '".$_POST['mdp']."', '$auth', '".time()."', $id_parrain, $gdt, '$email_address', '".mysql_escape_string($_POST['aim'])."', '".(int)$_POST['icq']."', '".mysql_escape_string($_POST['msn'])."', '".mysql_escape_string($_POST['yim'])."', '".(int)$_POST['con_speed']."')");
	$login_id = mysql_insert_id();

	if(SENDMail == 1) {

		$message = sprintf($st[689], URL_PREFIX, $login_name, $auth);

		if(send_mail(SERVER_NAME, $_SERVER['SERVER_ADMIN'], $_POST['real_name'], $email_address, SERVER_NAME." Authorisation Code", $message)){
			echo $st[690]."<p />";
			echo $st[691]."<p />";
		} else {
			echo $st[692]."<p />";
			echo $st[693]."<p />";
		}

	} else { //not sending auth e-mail, so set auth to -5
		dbn("update user_accounts set auth = '0' where login_id = '$login_id'");
	}

	insert_history($login_id,$st[694]);

	$rs = "";

	print_s_page($st[695], $st[696]." <br /><a href='".URL_PREFIX."/'>".$cw['click_here']."</a>".$st[697], 'inscription_ok');

}

?>