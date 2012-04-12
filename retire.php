<?php
require("user.inc.php");
$filename = 'retire.php';

if($user['login_id'] != 1 && $user['login_id'] != OWNER_ID) { 
  print_page($cw['admin'],$cw['admin_access_only']);
} elseif($target < 6) {
  print_page($cw['admin'],$st[121]);
}

#get users clan info and player info.
db("select login_id,clan_id,login_name from ${db_name}_users where login_id = '$target'");
$target_info = dbr();

if($sure != 'yes') {
  get_var('Retire',$filename,$st[122],'sure','yes');
} elseif(!$give_reason) {
	$new_page .= "<form action=retire.php method=POST name=reason>";
	while (list($var, $value) = each($HTTP_POST_VARS)) {
		$new_page .= "<input type=hidden name=$var value='$value' />";
	}
	$new_page .= "<input type=hidden name=give_reason value=1 />";
	$new_page .= sprintf($st[123], $target_info[login_name]);
	$new_page .= "<input type=text name=reason value= /><p /><input type='submit' value=".$cw['submit']." /></form>";
	print_page($cw['give_a_reason'],$new_page);

#if user is a clan leader, give option to disband clan or assign new leader
} elseif($target_info['clan_id'] > 0) {
	db("select u.login_id,u.clan_id,u.login_name,c.leader_id,c.symbol,c.sym_color,c.clan_name from ${db_name}_clans c, ${db_name}_users u where u.login_id = $target && u.clan_id = c.clan_id");
	$clan = dbr(1);
	db("select count(distinct login_id) from ${db_name}_users where clan_id = '$clan[clan_id]' && login_id > 5");
	$temp_2 = dbr();
	$clan['members'] = $temp_2[0];
	if($clan['login_id'] == $clan['leader_id']){
		if($clan['members'] > 1 && !$what_to_do){
			$new_page = $st[124];
			$new_page .= "<form action=retire.php method=POST name=retiring>";
			while (list($var, $value) = each($HTTP_POST_VARS)) {
				$new_page .= "<input type=hidden name=$var value='$value' />";
			}
			$new_page .= "<p />".$cw['disband_clan']." <input type=radio name=what_to_do value=1 CHECKED /> / ".$cw['assign_new_clan_leader']."<input type=radio name=what_to_do value=2 /><p /><input type='submit' value='".$cw['submit']."' /></form>";
			print_page($cw['retiring'],$new_page);

		#removing the clan
		} elseif($clan['members'] < 2 || $what_to_do == 1){
			dbn("update ${db_name}_users set clan_id = 0 where clan_id = $clan[clan_id]");
			dbn("update ${db_name}_planets set clan_id = -1 where clan_id = $clan[clan_id]");
			dbn("delete from ${db_name}_clans where clan_id = $clan[clan_id]");
			dbn("delete from ${db_name}_messages where clan_id = $clan[clan_id]");
			post_news(sprintf($st[125], $clan[clan_name], $clan[sym_color], $clan[symbol]), "clan, player_status");
		} elseif($what_to_do == 2 && !$leader_id){
			$new_page = $st[126];
			$new_page .= "<form action=retire.php method=POST name=retiring2>";
			#$new_page .= "<input type=hidden name=what_to_do value='$what_to_do' />";
			db2("select login_id,login_name from ${db_name}_users where clan_id = '$clan[clan_id]' && login_id != '$clan[login_id]'");
			$new_page .= "<select name=leader_id>";
			while ($member_name = dbr2(1)) {
				$new_page .= "<option value=$member_name[login_id]>$member_name[login_name]</option>";
			}
			$new_page .= "</select>";
			while (list($var, $value) = each($HTTP_POST_VARS)) {
				$new_page .= "<input type=hidden name=$var value='$value' />";
			}
			$new_page .= "<p /><input type='submit' value='".$cw['submit']."' /></form>";
			print_page($st[127],$new_page);
		} else {
			dbn("update ${db_name}_clans set leader_id = $leader_id where clan_id = $clan[clan_id]");
		}
	}
}

if(empty($reason)){
	$reason = $cw['no_reason'];
}

retire_user($target);
post_news(sprintf($st[128], $target_info[login_name], $reason), "player_status");
insert_history($user['login_id'],sprintf($st[129], $target_info[login_name]));
insert_history($target_info['login_id'],$st[130]);
print_page($cw['retired'],sprintf($st[131], $target_info[login_name]));

?>