<?php
/*
This script allows a player to change their password if they've forgotton it.
Author: Jonathan "Moriarty"
Copyright 2003 (c).
Created 8/Sept/03
*/
require_once("common.inc.php");

//check to see if it'll be possible to send an e-mail
/*if(!ini_get("sendmail_path")){
	die($st[1021]);
}*/




$back_link = "<a href='javascript: history.back()'>".$cw['back']."</a>";



//get users mail addy
if(isset($_GET['stage_one'])){
	print_header($cw['enter_address']);
	echo $st[1022];
	echo "<form action='$_SERVER[PHP_SELF]' method=POST name=stage_one><input type=hidden name='stage_two' value='1' /><input type=text name='mail_addy' value='' /><p /><input type='submit' /></form>";
	print_footer();

//user entered e-mail addy. Check it and send e-mail if valid
} elseif(isset($_POST['stage_two'])){
	print_header($cw['password_changing']);

	if(empty($_POST['mail_addy'])){
		echo $st[1023].". $back_link.";
	} else{

		//Connect to the database
		db_connect();

		db("select login_id, email_address, real_name from user_accounts where email_address = '".mysql_escape_string($_POST['mail_addy'])."'");
		$account_details = dbr(1);

		if(empty($account_details['login_id'])){ //couldn't find account
			echo $st[1024]."$back_link";

		} else {
			//create the random string
			$changing_data = create_rand_string(32);

			//enter number, and timestamp into db.
			dbn("update user_accounts set pass_change = '".$changing_data."*".time()."' where login_id = '$account_details[login_id]'");

			//create the url
			$url = URL_PREFIX."/change_pass.php?data_var=".$changing_data."&lid=".$account_details['login_id'];

$message = sprintf($st[1025], $account_details[real_name]).SERVER_NAME.sprintf($st[1026], $url);

			//try to send the mail
			if(send_mail(SERVER_NAME, $_SERVER['SERVER_ADMIN'], $account_details['real_name'], $account_details['email_address'], SERVER_NAME.$cw['password_reset'], $message)){
				echo $st[1027];
			} else {
				echo $st[1028];
				echo $st[1029];
			}
		}
	}

//user has clicked the link
} elseif(!empty($_REQUEST['data_var']) && !empty($_REQUEST['lid'])){
	//ensure no mysql injection attacks
	$data_var = mysql_escape_string($_REQUEST['data_var']);
	$lid = mysql_escape_string((int)$_REQUEST['lid']);

	//Connect to the database
	db_connect();

	print_header($cw['enter_new_password']);

	db("select login_id, login_name, pass_change from user_accounts where login_id = '$lid'");
	$account_details = dbr(1);
	if(empty($account_details)){
		echo $st[1030] ;
	} else {
		//break down pass_change into an array - data (element 0) and timestamp (element 1)
		$broken_down = explode("*", $account_details['pass_change']);

		if($broken_down[0] != $data_var){ //data not correct
			die($st[1031]);
		} elseif($broken_down[1] + 86400 < time()){ //timed out
			die($st[1032]);
		} elseif(empty($_POST['new_pass']) || empty($_POST['new_pass2'])){ //yet to enter new pass

			//re-update db, so as we make sure we don't run out of time whilst changing the pass
			dbn("update user_accounts set pass_change = '".$data_var."*".time()."' where login_id = '$lid'");

			echo "<form method=post action='$_SERVER[PHP_SELF]'><input type=hidden name='data_var' value='$data_var' /><input type=hidden name='lid' value='$lid' />".$cw['login_name']."<b class='b1'>$account_details[login_name]</b><p />".$st[1033]."<p /><input type=password name='new_pass' value='' /> ".$cw['new_password']."<br /><input type=password name='new_pass2' value='' /> ".$cw['new_password_again']."<p /><input type='submit' /><p />".$st[1034];

		} elseif($_POST['new_pass'] != $_POST['new_pass2']) { //compare passes
			echo $st[1035].". $back_link";
		} elseif($_POST['new_pass'] == $account_details['login_name']) { //pass is same as username
			echo $st[1036].". $back_link";
		} elseif(strlen($_POST['new_pass']) < 5) { //pass length check
			echo $st[1037].". $back_link";
		} else { //compare passes
			echo $st[1038]." <a href='".URL_PREFIX."/'>".$cw['login']."</a> ".$st[1039];

			//remove pass_change data, and input new pass.
			dbn("update user_accounts set pass_change = '', passwd='".md5(mysql_escape_string($_POST['new_pass']))."' where login_id = '$lid'");
		}
	}

//nothing entered
} else {
	echo $st[1040];
}

print_footer();
?>