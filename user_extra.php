<?php
require_once("common.inc.php");
require_once('includes/session_funcs.inc.php');

//Connect to the database
db_connect();

if ($_GET['sid']) {
	db("select login_name, bp_user_id from user_accounts where session_id='".mysql_real_escape_string(stripslashes($_GET['sid']))."'");
	$data = dbr();
	login_to_server($data['login_name'], '', $data['bp_user_id']);

} elseif(empty($_COOKIE['session_id']) || empty($_COOKIE['login_id']) || isset($_POST['submit'])){

	login_to_server();

//user already logged in. but check session details.
}
else {
	check_auth();

	if($login_id == 1) { //admin trying to continue old session.
		echo $st[793];
		exit();
	}
}

$rs = "<br /><br />".$st[794];


//print_header("Game Listings");

$nomPage = 'user_extra';

require('includes/haut_index.inc.php');



	$show_holiday_form = true;
	$show_galaxies = true;
	$holiday_msg = "<h3>Mode vacances</h3>Si vous passez en mode vacance vous ne pourrez plus gérer votre compte, vos productions seront arretées et les autres joueurs ne pourront vous attaquer.<br>";
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
	if(filter_var($p_user['email_address'], FILTER_VALIDATE_EMAIL))
		$has_email = true;
	else
		$has_email = false;

	if ( isset( $_POST['mode'] ) ) {
		setHolidayMode( (int)$_POST['mode'] );
		echo "<script>self.location='user_extra.php';</script>";
	}

	?>

	<script language="JavaScript">
	//Ajax : Mode vacances
		jQuery(document).ready(function($) {
			$('#modeHoliday').live('click', function() {
				$.get('ajax.php',
				{ cmd: 'modeHoliday',
				  mode: $('#mode').val()
				},
				function(data) {
					$('#vacances').html(data);
				});
			});
<?php if ($has_email){?>
	//Ajax : modeEmail 
			$('#notifications_email input').click( function() {
				var inputs = $('#notifications_email input');
				inputs.attr('disabled', 'disabled');
				$.get('ajax.php',
					{ cmd: 'modeEmail',
					  attack: $('#e_atta').is(':checked'),
					  message: $('#e_mess').is(':checked')
					},
					function(data) {
						inputs.removeAttr("disabled");
					});
			});
		});
<?php }?>
	//Système d'onglets
	$(document).ready(function() {
	
		$("#ongletsuserextra").tabs({ fx: { opacity: 'toggle', duration: 'fast' } });
		
	    $('#modifier_mdp').click (function() { 
				$.getJSON('ajax.php',
				{ cmd: 'submitMotDePasse',
				   oldpass : $('#oldpass').val(),
				   newpass : $('#newpass').val(),
				   newpass2 : $('#newpass2').val()
				  
				},
				function(data) {
					$('#reponse_mdp').removeClass().addClass(data['type']);	
					$('#reponse_mdp').html(data['message']);
					$('#reponse_mdp').show('highlight', 'slow');
				});
	    }); 
	    
	    $('#modifier_signature').click (function() { 
				$.getJSON('ajax.php',
				{ cmd: 'submitSignature',
				   signature : $('#new_sign').val()
				  
				},
				function(data) {
				    $('#reponse_signature').removeClass().addClass(data['type']);
					$('#reponse_signature').html(data['message']);
					$('#reponse_signature').show('highlight', 'slow');
				});
	    }); 	    
	}); 

	</script> 
	


<div id="ongletsuserextra">
    <ul>
    	<li><a href="#profil"><span>Profil</span></a></li>
    	<li><a href="#vacances"><span>Mode Vacances</span></a></li>
    	<li><a href="#parrainage"><span>Parrainage</span></a></li>
    </ul>
    <div id="profil">
	<?php
	echo'<center><b>'.$st[980].'</b><br /></center>';

	// change password


		echo $st[946];
		echo "<table>";
		echo "<tr><td align='right'>".$st[947].":</td><td><input id='oldpass' type='password' name='oldpass' /></td></tr>";
		echo "<tr><td align='right'>".$st[948].":</td><td><input id='newpass' type='password' name='newpass' /></td></tr>";
		echo "<tr><td align='right'>".$st[949].":</td><td><input id='newpass2' type='password' name='newpass2' /></td></tr>";
		echo "<tr><td></td><td><input id='modifier_mdp' type='button' value='".$st[950]."' /></td></tr>";
		echo "</table>";
		echo "<div id='reponse_mdp'></div>";
		
	// e-mail
	echo'<br /><br /><center><b>'.$st[1983].'</b><br /></center>';
	 if ($has_email){?>
	<div id="notifications_email" style="width: 785px; margin: 2px 0 0 195px; padding: 5px;">
	<label for="e_atta" class="hand"><input type="checkbox" id="e_atta" <?php if ($p_user['sendemail_am'][0] == 1) echo 'checked="checked"';?>/>Recevez un e-mail sur une attaque</label>
	<br/>
	<label for="e_mess" class="hand"><input type="checkbox" id="e_mess" <?php if ($p_user['sendemail_am'][2] == 1) echo 'checked="checked"';?>/>Recevez un e-mail sur un nouveau message</label>
	<?php //var_dump($p_user);?>
	</div>
<?php }

	
	// Signature 
	db("select signature from user_accounts where login_id = ".$login_id."");
	$sign = dbr();

	echo '<br /><br /><p><center><strong>Votre signature </strong></center></p>';
	echo '<input type="hidden" name="changesignature" value="changed" />';
	
	echo '<center><p><TEXTAREA id ="new_sign" name="nouvellesignature" rows=6 COLS=60>'.htmlentities($sign[0]).'</TEXTAREA></center></p>';
	echo '<center>'.htmlentities('Balises autorisées : <b> ; <i> ; <img> ; <marquee> ; <font> ; <center>').'</center>';
	echo '<p><center><input type="button" id="modifier_signature" name="submitsignature" value="Modifier votre signature" /></center>';
	echo '<div id="reponse_signature"></div>';
	
	//Fin préférences du compte
	
	?>
    </div>
    <div id="vacances">
    <?php
	//Gestion mode vacances		
		if ( $show_holiday_form )
		{	
			
			echo '<input type="hidden" id="mode" value="'.$mode_val.'" />
				  <p>'.$holiday_msg.'</p><br /><input type="button" id="modeHoliday" value="'.$mode.'" />';
		}else {
			echo '<br />Mode vacance changé au cours des 24 dernières heures.';
		}
	?>
</div>
<div id="parrainage">
		<?php
			
	//Gestion mode vacances			
		$text = '';

		echo'<h3>'.$st[1803].'</h3>';
		$text .= '<p>'.$st[1804].'</p>';
		$text .= '<p>'.$st[1805].'</p><br />';

		$text .= "<center><span class='code'>http://www.astravires.fr/?pid=".$login_id."</span><br /></center>";

		$text .= '<br /><p>'.$st[1806].'</p><br />';

		$text .= "<center><span class='code'>[url=http://www.astravires.fr/?pid=".$login_id."]Astra Vires: Jeu gratuit jouable par navigateur[/url]</span></center><br /><br />";

		$text .= "<p><h3>".$cw['vos_gdt']."</h3>";
		$text .= "<script>
				jQuery(document).ready(function($) {
					$('#useGdt').live('click', function() {
						$.get('ajax.php',
						{ cmd: 'useGdt',
						  galaxy: $('#galaxyGdt').val(),
						  value: $('#valueGdt').val()
						},
						function(data) {
							$('#result').html(data);
						});
					});
				});
				</script><span id=\"result\">";
		$text .= "".sprintf($st[1809], $p_user['gdt']).".";
		if ($p_user['gdt'])
		{
	//		$text .= ", <a href='parrainage.php?agdt=1'>".$st[1810]."</a> (".$st[1811].")";
			$text .= "<br/><br/>Choisissez la galaxie dans laquelle vous souhaitez utiliser un ou plusieurs GDT.";
			$user_galaxy = array();
			db2("select name, db_name, paused, game_id, days_left, description from se_games where status = '1' order by last_reset asc");
			while ($game_stat = dbr2(1)){

				db("select login_id from ${game_stat['db_name']}_users where login_id = '$p_user[login_id]'");

				if($in_game = dbr(1)) { //player already in that game
					$user_galaxy[] = "<option value='$game_stat[db_name]'>$game_stat[name]</option>";
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
			$text .= '<input id="useGdt" type="button" value="Utiliser"/><br/><br/>';
			$text .= '</span>';
		}
		$text .= "</p><br />";

		$text .= "<br /><h3>".$cw['vos_filleuls']."</h3>";
		$text .= "<p>".$st[1808]."</p>";

		db("select * from user_accounts where id_parrain=$login_id");
		while ($data = dbr()) {
			$actif = ($data['parrainage_actif']) ? "<span class='vert'>".$cw['actif']."</span>":"<span class='rouge'>".$cw['non_actif']."</span>";
			$text .=  "<b>".$data['login_name']."</b> - $actif<br />";
		}

		if (!dbc()) $text .=  "<i>".$st[1807]."</i>";

		echo $text;
		?>
		<?php
// for testing DONT delete
//		var_dump($fbuser);
//		echo '<br/>';
//		$asd = fb_wallpost_wosdk($fbuser->id, 'testing', 'link title', 'http://digitizor.com/2011/02/04/facebook-post-without-phpsdk/', 'short desc', 'http://chipchick.com/wp-content/uploads/2007/12/asd3wbk_front_wipod_g_rdax_328x350.gif', 'action name', 'action link');
//		$asd = fb_wallpost_wosdk_api('Astra Vires', '{*actor*} just joined Astra Vires!', 'Astra Vires is a game that ... ');
//		var_dump($asd);
		?>
		
	</div>
    </div>
</div>

<?php


	$filename = 'options.php';
$error_str = "";
// change player options
if(isset($_GET['player_op']) && $_GET['player_op'] == 1){
	$error_str .= $st[925];
	$error_str .= make_table(array("",""));
	$error_str .= "<form method='post' action='user_extra.php'>";
	if($user['login_id'] != 1 && $user['login_id'] != OWNER_ID){#admins can't alter icq etc.
		$error_str .= quick_row("<br />".$st[926].":"," <input type='text' name='aim' value='$p_user[aim]' />");
		$error_str .= quick_row("<br />".$st[927]." #:"," <input type='text' name='icq' value='$p_user[icq]' />");
		$error_str .= quick_row("<br />".$st[928].":"," <input type='text' name='yim' value='$p_user[yim]' />");
		$error_str .= quick_row("<br />".$st[929].":"," <input type='text' name='msn' value='$p_user[msn]' />");
	} else {
		$error_str .= "<br />".$st[930]."<br />";
	}
	$error_str .= quick_row($st[931],"<p />".$cw['note'].": <b class='b1'>".$st[932]."<br /><textarea name=sig cols=25 rows=10>".stripslashes($user['sig'])."</textarea>");
	$error_str .= "</table><input type='hidden' name='player_op' value='2' /><br /><br />";
	$error_str .= "<input type='submit' name='submit' /></form><br /><br />";
	print_page($st[933],$error_str);

} elseif(isset($_POST['player_op']) && $_POST['player_op'] == 2){
	if($user['login_id'] != 1) { #admins can't alter icq etc
		dbn("update user_accounts set aim = '".mysql_escape_string(strip_tags((string)$_POST['aim']))."', icq = '".(int)$_POST['icq']."', yim = '".mysql_escape_string(strip_tags((string)$_POST['yim']))."', msn = '".mysql_escape_string(strip_tags((string)$_POST['msn']))."' where login_id = '$user[login_id]'");
	}
	dbn("update ${db_name}_users set sig = '".mysql_escape_string(strip_tags((string)$_POST['sig']))."' where login_id = '$user[login_id]'");
	$error_str .= $st[934];
}

#save changes to vars
if(isset($_POST['save_vars'])) {
	foreach($_POST as $var => $value) {
		$option_check="";
		if($var == 'save_vars') {
			continue;
		} else {

			//ensure all legal
			$var = mysql_escape_string((string)$var);
			$value = (int)$value;

			#ensure option is in range
			db("select min,max from option_list where name='$var'");
			$option_check=dbr(1);

			#option out of range
			if($value < $option_check['min'] || $value > $option_check['max']){
				$error_str .= "<br /><b class='b1'>$var</b> ".$st[935].".";

			} else { #option in range
				dbn("update ${db_name}_user_options set $var = '$value' where login_id = '$user[login_id]'");
				$user_options[$var] = $value;
				$error_str .= "<br /><b class='b1'>$var</b> ".$st[936]." <b>$value</b>";
			}
		}
	}#end while
}

// retire
if(isset($_REQUEST['retire'])) {
	if(!isset($_POST['sure'])) {
		$retire_text_xtra = "";
		if($GAME_VARS['retire_period'] != 0){
			$retire_text_xtra = sprint($st[937], $GAME_VARS[retire_period]);
		}
		get_var($cw['retire'],"user_extra.php","<p /><b class='b1'>".$cw['warning']."!</b> ".$st[938].$retire_text_xtra,'sure','yes');

	} else {
		if ($user['clan_id'] > 0) {
			db("select leader_id from ${db_name}_clans where clan_id = '$user[clan_id]'");
			$temp_1 = dbr();
			db("select count(distinct login_id) from ${db_name}_users where clan_id = '$user[clan_id]' && login_id > 5");
			$temp_2 = dbr();
			$clan = array('members' => $temp_1[0], 'leader' => $temp_2[0]);
			if($clan['members'] > 1 && $user['login_id'] == $clan['leader_id'] && !isset($_POST['what_to_do'])){
				$new_page = $st[939];
				$new_page .= "<form action='user_extra.php' method='POST' name='retiring'>";

				foreach($_POST as $var => $value){
					$new_page .= "<input type='hidden' name='$var' value='$value' />";
				}
				$new_page .= "<p />".$st[940]." <input type=radio name=what_to_do value=1 CHECKED /> / ".$st[941]."<input type='radio' name='what_to_do' value='2' /><p /><input type='submit' value='Submit' /></form>";
				print_page($cw['retiring'],$new_page);

			//disbanding the clan
			} elseif($clan['members'] < 2 || (isset($_POST['what_to_do']) && $_POST['what_to_do'] == 1)){
				dbn("update ${db_name}_users set clan_id = 0 where clan_id = '$user[clan_id]'");
				dbn("update ${db_name}_planets set clan_id = -1 where clan_id = '$user[clan_id]'");
				dbn("delete from ${db_name}_clans where clan_id = '$user[clan_id]'");
				dbn("delete from ${db_name}_messages where clan_id = '$user[clan_id]'");

			//choosing a new leader
			} elseif(isset($_POST['what_to_do']) && $_POST['what_to_do'] == 2 && !isset($_POST['leader_id'])){
				$new_page = $st[942];
				$new_page .= "<form action='options.php' method='POST' name='retiring2'>";

				db2("select login_id,login_name from ${db_name}_users where clan_id = '$user[clan_id]' && login_id != '$user[login_id]'");
				$new_page .= "<select name='leader_id'>";
				while ($member_name = dbr2(1)) {
					$new_page .= "<option value='$member_name[login_id]'>$member_name[login_name]</option>";
				}
				$new_page .= "</select>";

				foreach($_POST as $var => $value){
					$new_page .= "<input type='hidden' name='$var' value='$value' />";
				}

				$new_page .= "<p /><input type='submit' value='Submit' /></form>";
				echo $new_page;
			}
		}

		retire_user($user['login_id']);
		$rs = "<p /><a href='game_listing.php'>".$st[944]."</a>";
		print_header($cw['account_removed']);
		insert_history($user['login_id'],$cw['retired_from_game']);
		echo $st[945];
		print_footer();
	}
}
                                                                                                                                                                                                        

if($user['login_id'] != 1){
	//$error_str .= "<p /><a href='options.php?retire=1'>".$st[982]."</a>"; //Quitter la galaxie à enlever
}

		?>
	</div>



	<div class="spacer"></div>

</div>

<!--<div id="accueilPrincipal2"></div>-->

<?php
//print_footer($nom_page_analytics);

include('includes/bas_index.inc.php');
?>
