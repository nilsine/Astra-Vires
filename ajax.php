<?php
switch ($_GET['cmd']) {
	case 'annuler_didac':
		include("user.inc.php");
		dbn("update user_accounts set id_didac=0 where login_id=$login_id");
		break;
	case 'suiv_didac':
		include("user.inc.php");
		db("select * from se_didacticiel where id=" . $p_user['id_didac']);
		$data = dbr();
		db("select * from se_didacticiel where ordre > " . $data['ordre'] . " order by ordre asc limit 1");
		if ($data = dbr()) {
			echo nl2br($data['texte']);
			dbn("update user_accounts set id_didac=" . $data['id'] . " where login_id=$login_id");
		} else {
			echo 'Didacticiel terminé.';
		}
		break;
	case 'prec_didac':
		include("user.inc.php");
		db("select * from se_didacticiel where id=" . $p_user['id_didac']);
		$data = dbr();
		db("select * from se_didacticiel where ordre < " . $data['ordre'] . " order by ordre desc limit 1");
		if ($data = dbr()) {
			echo nl2br($data['texte']);
			dbn("update user_accounts set id_didac=" . $data['id'] . " where login_id=$login_id");
		} else {
			echo 'Début du didacticiel.';
		}
		break;
	case 'generateVesselName':
		include("common.inc.php");
		echo generateVesselName();
		break;
	case 'modeHoliday':
		include("common.inc.php");
		db_connect();
		check_auth();

		if ( isset( $_GET['mode'] ) ) {
			setHolidayMode( (int)$_GET['mode'] );
//			echo "<script>self.location='user_extra.php';</script>";
		}

		$show_holiday_form = true;
		$show_galaxies = true;
		$holiday_msg = "<b>Mode vacances</b><br />Si vous passez en mode vacance vous ne pourrez plus gérer votre compte, vos productions seront arretées et les autres joueurs ne pourront vous attaquer.";
		if ( checkHolidayMode() ) {
			$mode = "Désactiver le mode vacances";
			$mode_val = 0;
			$show_galaxies = false;
			$holiday_msg = "Vous êtes en mode vacances, pour revenir en mode normal appuyez sur ce bouton";
		} else {
			$mode = "Activer le mode vacances";
			$mode_val = 1;
			if ( !checkHoliday24h() ) {
				$show_holiday_form = false;
			}
		}

		if ( $show_holiday_form )
		{
			echo '<input type="hidden" id="mode" value="'.$mode_val.'" />
				  <p>'.$holiday_msg.'</p><br /><input type="button" id="modeHoliday" value="'.$mode.'" />';
		}else {
			echo '<br />Option de vacances changé au cours des 24 dernières heures.';
		}

		break;
	case 'useGdt':
		include("common.inc.php");
		db_connect();
		check_auth();
		$galaxy = $_GET['galaxy'];
		$value = (int)$_GET['value'];
		if (!$p_user['gdt'] || ($p_user['gdt'] - $value < 0)) {
			$error_str = "<span class='rouge'>".$st[1812].".</span>";
		} else {
			//load GAME_VARS array for current galaxy
			require_once("$directories[includes]/${galaxy}_vars.inc.php");

			dbn("update user_accounts set gdt=gdt-$value where login_id=$p_user[login_id]");
			//dbn("update ${galaxy}_users set gdt=gdt+$value where login_id=$p_user[login_id]");
			db("select * from ${galaxy}_users where login_id=$p_user[login_id] LIMIT 1");
			$user_turns = dbr();
			$user_turns = $user_turns['turns'];

				$cyclesenp = mt_rand(50, 150) * $value;
				$cycles = $user_turns + $cyclesenp;
				if ($cycles > $GAME_VARS['max_turns'])
					$cycles = $GAME_VARS['max_turns'];
				dbn("update ${galaxy}_users set turns=$cycles where login_id=".$p_user['login_id']);

			$p_user['gdt'] -= $value;
			//$error_str = "<span class='vert'>".utf8_encode($st[1813]).".</span>";
				//$cyclesenp = cycle won   //$cycles = cycle added
			$error_str = "<span class='vert'>".sprintf($st[1802], $cyclesenp).".</span>";
		}
		$text = '';
		$text .= "".sprintf($st[1809], $p_user['gdt']).".<br/>";

		//text and form display / refresh
		if ($p_user['gdt'])
		{
			$text .= "<br/>Choisissez la galaxie dans laquelle vous souhaitez utiliser un ou plusieurs GDT.";
			$user_galaxy = array();
			db2("select name, db_name, paused, game_id, days_left, description from se_games where status = '1' order by last_reset asc");
			while ($game_stat = dbr2(1)){
				db("select login_id from ${game_stat['db_name']}_users where login_id = '$p_user[login_id]'");

				if($in_game = dbr(1)) { //player already in that game
					$user_galaxy[] = "<option value='$game_stat[db_name]'>".utf8_encode($game_stat['name'])."</option>";
				}
			}
			$text .= '<br/><select id="galaxyGdt">';
			foreach ($user_galaxy as $tmp_text){
				$text .= $tmp_text;
			}
			$text .= '</select><select id="valueGdt">';
			for ($ii = 1; ($ii <= $p_user['gdt'] && $ii <= 4); $ii++)
				$text .= '<option value="'.$ii.'">'.$ii.' GDT</option>';

			$text .= '</select>';
			$text .= '<input id="useGdt" type="button" value="Utiliser"/> ';
		}
		$text .= $error_str.'<br/><br/>';
		echo $text;
		break;
	case 'modeEmail':
		include("common.inc.php");
		db_connect();
		check_auth();
		$sendmail_am = '';
		if ($attack == 'true') $sendmail_am .= '1';
		else $sendmail_am .= '0';
		if ($message == 'true') $sendmail_am .= ' 1';
		else $sendmail_am .= ' 0';
		$query = "update user_accounts set sendemail_am='$sendmail_am' where login_id=".$p_user['login_id'];
		dbn("update user_accounts set sendemail_am='$sendmail_am' where login_id=".$p_user['login_id']);
		$p_user['sendemail_am'] = $sendmail_am;
//		echo $sendmail_am.' '.$query;
		break;
	/*
	case 'submitSignature':
		include("common.inc.php");
		db_connect();
		check_auth();
		//$signature = utf8_decode($signature);
		dbn("update user_accounts set signature = '$signature' where login_id=".$p_user['login_id']);		
		echo '<center><table><tr>Votre signature a été modifiée !</tr></table></center>';
		break;
	*/
	case 'submitSignature':
		include("common.inc.php");
		db_connect();
		check_auth();
		$signature = utf8_decode($signature);
		$signature = mysql_real_escape_string($signature);
		dbn("update user_accounts set signature = '$signature' where login_id=".$p_user['login_id']);	


		$donnees = array('type' => 'message_succes' , 'message' => utf8_encode('Votre signature a été modifiée !'));
		echo utf8_decode(json_encode($donnees));
		

		break;	
		
	case 'submitMotDePasse':
		include("common.inc.php");
		db_connect();
		check_auth();
	
	
		$donnees = array('type' => '' , 'message' => '');
	
		if($user['login_id'] == 1){ //admin pass. Not encrypted
			db("select admin_pw from se_games where db_name = '$db_name'");
			$a_pas_temp = dbr();
			$enc_newpass = $p_user['passwd'] = $a_pas_temp[0];
			$enc_oldpass = $oldpass;

			//make sure there are two letters and two nums in it.
			if(!preg_match("/[0-9].*[0-9]/", $newpass) || !preg_match("/[a-z].*[a-z]/i", $newpass)){
				print_page($cw['error'],$st[951]);
			}
		} else {//user passes are encrypted
			$enc_oldpass = md5($oldpass);
			$enc_newpass = md5($newpass);
		}

		if (!empty($newpass) && ($newpass == $newpass2)) {
			if(strlen($newpass) < 5) { //ensure length
				$temp_str = $st[952]."<br />";

			} elseif($newpass == $oldpass) { //make sure it's not the same as the old one.
				$temp_str = $st[953];

			} elseif($user['login_name'] == $newpass) { //using login name as pass
				$temp_str = $st[954];

			} elseif ($enc_oldpass == $p_user['passwd']) {
				if ($user['login_id'] == 1) {
					db2("update se_games set admin_pw='".mysql_escape_string($newpass)."' where db_name = '".$db_name."'");
					$temp_str .= $st[955];
					$p_user['passwd'] = '$newpass';

				} else {
					db("update user_accounts set passwd='$enc_newpass' where login_id='".$login_id."'");
					$p_user['passwd'] = $enc_newpass;

				}
				$temp_str = $st[956];
				$donnees['type'] = 'message_succes';
				insert_history($user['login_id'],$st[957]);

			} else {
				$temp_str = $st[958]."!";
			}
		} else {
			$temp_str = $st[959]."";

			//$temp_str .= "<a href='javascript:back()'>".$cw['go_back']."</a><br />";
		}
		//echo $st[950];
		$donnees['message'] = utf8_encode($temp_str);
		if($donnees['type'] == '')
			$donnees['type'] = 'message_echec';
		echo utf8_decode(json_encode($donnees));

		break;
		
}
