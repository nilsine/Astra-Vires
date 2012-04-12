<?php

//Connect to the database if not already.
db_connect();



//Function that will log a user into gamelisting, or the admin into location.php
function login_to_server ($pseudo='', $mdp='', $bpUserId=0, $returnSession = false, $fbUserId=0){
	global $p_user, $db_name, $directories, $st, $cw;
	$login_name = mysql_escape_string(($pseudo) ? $pseudo:(string)$_POST['pseudo']);

	$agent_hash = hash_user_agent();

	/********************** Admin Login *******************/
	if($login_name == "Admin"){
		$password = mysql_escape_string((string)$_POST['mdp']);
		db("select * from se_games where admin_pw = '$password'");
		$games_info = dbr(1);
		if(empty($games_info)){ //invalid admin login
			insert_history(1, "Bad login Attempt");
			sleep(3); //so as to minimise trouble caused by people trying to guess the pass, and who don't know about the back button. :)
			exit("Login Failed. Do no pass go, do not collect your new Harvestor Mammoth.");

		} else { //Admin successfully logged into game
			$db_name = $games_info['db_name'];
			$session = create_rand_string(32);
			SetCookie("login_id", 1, 0);
			SetCookie("login_name", "Admin",time()+2592000);
			SetCookie("session_id", $session, 0);
			flush(); //send cookies immediatly

			$expire = time() + SESSION_TIME_LIMIT;
			insert_history(1, "Successfully logged into $db_name");

			dbn("update ${db_name}_users set game_login_count = game_login_count + 1 where login_id = '1'");
			dbn("update se_games set session_id = '$session', session_exp = '$expire', user_agent = '$agent_hash' where db_name = '$db_name'");

			echo "<script>self.location='location.php';</script> <noscript>You cannot login without JavaScript. Please enable Javascript, or use a browser that supports it.</noscript>";
			exit();
		}
	}elseif(preg_match("/^admin$/i",$login_name)){//other spelling of admin.
		sleep(5);
		exit("Sod off - you can't even spell 'admin' properly can you?");
	}

	/*************************User Login************************/
	db("select * from user_accounts where login_name = '$login_name'");
	$p_user = dbr(1);

	if(!isset($_POST['enc_pass']) || $mdp){//user entered pass on login form
		$enc_pass = md5(($mdp) ? $mdp:$_POST['mdp']);
		$pre_enc_pass = 0;

	} else { //pass coming from being hidden in auth. so set pre_enc to ensure auth is checked.
		$enc_pass = $_POST['enc_pass'];
		$pre_enc_pass = 1;
	}

	if (empty($p_user)) { //incorrect username
		print_header($cw['login_problem']);
		echo "<blockquote>".sprintf($st[1816], $login_name)."<br />
		".$st[1817]."<p />
		<p /> <a href='inscription.php'>
		".$cw['sign_up2']."</a> <p /> <a href=\"".URL_PREFIX."/index.php\">".$st[1818]."</a></b></blockquote>";
		print_footer();

	} elseif(($enc_pass != $p_user['passwd']) && !$bpUserId && !$fbUserId) { //incorrect password
		print_header($cw['bad_passwd']);
		echo "<blockquote><b>".$st[1819]."<br />".$st[1820]."
		<p /><a href=\"javascript:history.back()\">".$st[1818]."</a></b><p />".$st[789]." ? <a href=change_pass.php?stage_one=1>".$cw['click_here']."</a></blockquote><p />";
		insert_history($p_user['login_id'], $cw['bad_login']);
		print_footer();

	} elseif($p_user['bp_user_id'] && !$bpUserId) { // joueur BP connexion classique
		print_header("Problème de connexion");
		echo "<blockquote><b>Erreur</b><br /><br />Il semble que vous vous soyez inscrit via notre partenaire <a href='http://www.bigpoint.com/' target='_blank'>BigPoint</a>, veuillez utiliser <a href='http://fr.bigpoint.com/games/astravires/' target='_blank'>la fiche jeu Astra Vires</a> sur son portail pour vous connecter.</blockquote><p />";
		insert_history($p_user['login_id'], 'Joueur BP connexion classique');
		print_footer();

	//valid username/pass combination.
	//But MUST enter a auth code to continue, as pre_enc_pass was set.
	//or no auth code yet entered, and sendmail is set
	} elseif($pre_enc_pass == 1 || $p_user['auth'] != 0 || $bpUserId) {

		//get user to enter auth code.
		if((empty($_POST['auth_code']) || ($_POST['auth_code'] != $p_user['auth'] && $p_user['auth'] != 0)) && !$bpUserId) {
			print_header("Authorisation Code Required");
			$rs = "";
			if(empty($_POST['auth_code'])){
				echo "Please enter the Authorisation Code that was sent to your email address:<br /><br />";
			} else {
				echo "Authorisation Code did not match.<br />";
			}
			echo "<form name=get_var_form action=$_SERVER[PHP_SELF] method=POST>";
			echo "<input type=hidden name=l_name value='$login_name'><input type=hidden name=enc_pass value='$enc_pass'>";
			echo "<input type=text name=auth_code value='' size=20> - ";
			echo "<input type=submit value=Submit></form>";
			print_footer();
		} elseif($_POST['auth_code'] == $p_user['auth'] || $bpUserId) {
			dbn("update user_accounts set auth = '0' where login_id = '$p_user[login_id]'");
		} else {
			print_page("hmm","Something Broke");
		}
	}

/*****************User successfully logged in***********************/

	if ($p_user['mdp']) setAutoLoginCookie( $p_user['login_id'], $p_user['login_name'], $p_user['mdp'] );

	$session = create_rand_string(32);

	SetCookie("login_id",$p_user['login_id'],time()+2592000);
	SetCookie("login_name",$p_user['login_name'],time()+2592000);
	SetCookie("session_id",$session,0);

	$expire = time() + SESSION_TIME_LIMIT;

	if (!$returnSession) {
		dbn("update user_accounts set last_login = ".time().", session_id = '$session', session_exp = '$expire', last_ip = '".$_SERVER['REMOTE_ADDR']."', login_count = login_count + 1, user_agent = '$agent_hash' where login_id = '$p_user[login_id]'");
		insert_history($p_user['login_id'], "Logged Into GameList");
	} else {
		dbn("update user_accounts set session_id = '$session', session_exp = '$expire' where login_id = '$p_user[login_id]'");
	}

	// update the password in clear to delete the encrypted one in the future
	dbn("update user_accounts set mdp = '" . $_POST['mdp'] . "' where login_id = '" . $p_user[login_id] . "'");

	if($p_user['last_login'] == 0 && !$returnSession) { //first login. show them the story.
		print_header("Histoire");

		//load story
		$results = load_xml("$directories[includes]/stories.xml");
		$story = $results['story']['Histoire'];
		echo "<a href='game_listing.php'>Continuer</a><br /><br />";
		echo "\n<a name=top><center><b>$story[title]</b></center></a><br>$story[content] <p />Ecrit par <b class=b1>$story[author]</b>";
		echo "<br /><br /><a href='game_listing.php'>Continuer</a>";
		$rs = '';
		print_footer();
	}

	if ($returnSession) return $session;
}

?>