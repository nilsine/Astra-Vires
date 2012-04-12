<?php
require_once("user.inc.php");

$filename = 'options.php';
$error_str = "";

// change player options
if(isset($_GET['player_op']) && $_GET['player_op'] == 1){
	$error_str .= $st[925];
	$error_str .= make_table(array("",""));
	$error_str .= "<form method='post' action='options.php'>";
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
			$retire_text_xtra = sprintf($st[937], $GAME_VARS[retire_period]);
		}
		get_var($cw['retire'],"options.php","<p /><b class='b1'>".$cw['warning']."!</b> ".$st[938].$retire_text_xtra,'sure','yes');

	} else {
		if ($user['clan_id'] > 0) {
			db("select leader_id from ${db_name}_clans where clan_id = '$user[clan_id]'");
			$temp_1 = dbr();
			db("select count(distinct login_id) from ${db_name}_users where clan_id = '$user[clan_id]' && login_id > 5");
			$temp_2 = dbr();
			$clan = array('members' => $temp_1[0], 'leader' => $temp_2[0]);
			if($clan['members'] > 1 && $user['login_id'] == $clan['leader_id'] && !isset($_POST['what_to_do'])){
				$new_page = $st[939];
				$new_page .= "<form action='options.php' method='POST' name='retiring'>";

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
				print_page($st[943],$new_page);
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


// change password
if(isset($_REQUEST['changepass'])) {

	$rs = "<br /><a href='options.php'>".$cw['back_to_options']."</a>";

	if(isset($_GET['changepass']) && $_GET['changepass'] == 'change') {
		$temp_str = $st[946];
		$temp_str .= "<table><form action='options.php' method='post'><input type='hidden' name='changepass' value='changed' />";
		$temp_str .= "<tr><td align='right'>".$st[947].":</td><td><input type='password' name='oldpass' /></td></tr>";
		$temp_str .= "<tr><td align='right'>".$st[948].":</td><td><input type='password' name='newpass' /></td></tr>";
		$temp_str .= "<tr><td align='right'>".$st[949].":</td><td><input type='password' name='newpass2' /></td></tr>";
		$temp_str .= "<tr><td></td><td><input type='submit' value='".$st[950]."' /></td></tr></form></table><br />";
		print_page($st[950],$temp_str);

	} elseif ($_POST['changepass'] == 'changed') {
		if($user['login_id'] == 1){ //admin pass. Not encrypted
			db("select admin_pw from se_games where db_name = '$db_name'");
			$a_pas_temp = dbr();
			$enc_newpass = $p_user['passwd'] = $a_pas_temp[0];
			$enc_oldpass = $_POST['oldpass'];

			//make sure there are two letters and two nums in it.
			if(!preg_match("/[0-9].*[0-9]/", $_POST['newpass']) || !preg_match("/[a-z].*[a-z]/i", $_POST['newpass'])){
				print_page($cw['error'],$st[951]);
			}
		} else {//user passes are encrypted
			$enc_oldpass = md5($_POST['oldpass']);
			$enc_newpass = md5($_POST['newpass']);
		}

		if (!empty($_POST['newpass']) && ($_POST['newpass'] == $_POST['newpass2'])) {
			if(strlen($_POST['newpass']) < 5) { //ensure length
				$temp_str = $st[952]."<br />";
				$temp_str .= "<p /><a href='javascript:history.back()'>".$cw['back_pass-change_form']."</a>";

			} elseif($_POST['newpass'] == $_POST['oldpass']) { //make sure it's not the same as the old one.
				$temp_str = $st[953];
				$temp_str = "<p /><a href='javascript:history.back()'>".$cw['back_pass-change_form']."</a>";

			} elseif($user['login_name'] == $_POST['newpass']) { //using login name as pass
				$temp_str = $st[954];
				$temp_str .= "<p /><a href='javascript:history.back()'>".$cw['back_pass-change_form']."</a>";

			} elseif ($enc_oldpass == $p_user['passwd']) {
				if ($user['login_id'] == 1) {
					dbn("update se_games set admin_pw='".mysql_escape_string($_POST['newpass'])."' where db_name = '$db_name'");
					$temp_str .= $st[955];
					$p_user['passwd'] = '$newpass';

				} else {
					dbn("update user_accounts set passwd='$enc_newpass' where login_id='$user[login_id]'");
					$p_user['passwd'] = $enc_newpass;
				}
				$temp_str = $st[956];
				insert_history($user['login_id'],$st[957]);

			} else {
				$temp_str = $st[958]."!<br /><br />";
				$temp_str .= "<a href='javascript:back()'>".$cw['go_back']."</a><br />";
			}
		} else {
			$temp_str = $st[959]."!<br />";
			$temp_str .= "<a href='javascript:back()'>".$cw['go_back']."</a><br />";
		}
		print_page($st[950],$temp_str);
	}
}


//

// change colour scheme
if(isset($_GET['scheme'])) {

	$error_str .= $st[960];
	$error_str .= "<FORM method='POST' action='options.php'>";

	//get the file that has the index of the style-sheets.
	require_once("$directories[includes]/ss_index.php");

	asort($ss_index_array);//order by id.
	foreach($ss_index_array as $key => $details){

	//check the present style-sheet
		if($key == $user_options['color_scheme']){
			$checked = " checked";
		} else {
			$checked = "";
		}
		//sever wide default
		if($key == $p_user['default_color_scheme']){
			$s_def = $st[961];
		} else {
			$s_def = "";
		}

		$error_str .= "<br /><input type='radio' name='style' value='$key' $checked /> <b><font color='$details[b_col]'>$details[ss_name]</font></b> - $details[creator]\n".$s_def;
	}

	$error_str .= "<p /><input type='submit' value='".$cw['submit']."' />";
	$error_str .= "</form>";
	print_page($cw['select_scheme'],$error_str);

#demo of style sheet
} elseif (isset($_POST['style'])) {

	$temp = $directories['stylesheets']."/style".$style.".css";

	echo"<html>\n";
	echo"<head>\n";
	echo"<title>[ ".$cw['solar_empire']." - ".SERVER_NAME." : ".$st[962]." ]</title>\n";
	echo "<link rel='stylesheet' href='$temp' />\n";
	echo "</head>\n";
	echo "<body>\n";
	print_leftbar();
	print_topbar();

	$error_str .=$st[963];

	$error_str .= "<p />".$st[964].".\n";
	$error_str .= "<br /><b>".$st[965].".</b>\n";
	$error_str .= "<br /><b class='b1'>".$st[966]." 1</b>\n";
	$error_str .= "<br /><b class=b2>".$st[966]." 2</b>\n";
	$error_str .= "<br /><b class=b3>".$st[966]." 3</b>\n";
	$error_str .= "<br /><a href=''>".$st[967]."</a>\n";
	$error_str .= "<br /><a href='location.php'>".$st[968]."</a>\n";
	$error_str .= "<br /><b class=cloak>".$st[969]."</b>\n";

	$error_str .= "<p />".$st[970].":\n";
	$error_str .= "<br /><a href='options.php?scheme=1'>".$st[971]."</a>.\n";
	$error_str .= "<br /><a href='options.php?keep=$style'>".$st[972]."\n";

	$error_str .= "<p /><a href='options.php?keep_server=$style'>".$st[973].".\n";

	echo $error_str;
	echo "</td></tr></table>\n";
	echo "</body>\n</html>\n";
	exit();

#keep new style sheet. for game
} elseif (isset($_GET['keep'])) {
	$user_options['color_scheme'] = $_GET['keep'];
	dbn("update ${db_name}_user_options set color_scheme = '".(int)$_GET['keep']."' where login_id = '$user[login_id]'");
	$error_str .=$st[974]." <b>$_GET[keep]</b>.";
	$error_str .="<br />".$st[975];
	$rs .= "<br /><a href='options.php'>".$cw['back_to_options']."</a>";
	print_page($st[976],$error_str);

#keep new style sheet. for server
} elseif (isset($_GET['keep_server'])) {

	dbn("update user_accounts set default_color_scheme = '".(int)$_GET['keep_server']."' where login_id = '$user[login_id]'");
	$error_str .=$st[977]." <b>$_GET[keep_server]</b>.";
	$error_str .="<br />".$st[978];
	$rs .= "<br /><a href='options.php'>".$cw['back_to_options']."</a>";
	print_page($st[976],$error_str);
}




#print main page
$error_str .= "<p />".$st[979];
$error_str .= "<p /><a href='options.php?changepass=change'>".$st[980]."</a>";
//$error_str .= "<br /><a href='options.php?scheme=1'>Change your Colour Scheme</a>";
$error_str .= "<br /><a href='options.php?player_op=1'>".$st[981];


if($user['login_id'] != 1){
	$error_str .= "<p /><a href='options.php?retire=1'>".$st[982]."</a>";
}

#list other options
$error_str .= "<p />".$st[983].".<form method='POST' name='get_var_form' action='options.php'>";

$error_str .= "<br /><input type='submit' value=\"".$cw['submit_vars']."\" />";

#select and output all the user options
db2("select * from option_list order by name asc");

$error_str .= list_options(1, $user_options);

$error_str .= "<br /><input type='hidden' name='save_vars' value='1' /><input type='submit' value=\"".$cw['submit_vars']."\" /></form>";

print_page($st[984], $error_str);

?>
