<?php
require_once("user.inc.php");
/*
Page that lists the messages for a user.
*/

$error_str = "";

//Deleted single message
if(isset($_GET['killmsg'])) {
	dbn("delete from ${db_name}_messages where message_id = '".mysql_escape_string($_GET['killmsg'])."' && login_id = '$user[login_id]'");
	$error_str .= $cw['message_deleted'].".<p />";
}

//Delete selected messages
if(!empty($_POST['del_mess'])) {
	$message_string = "";

	foreach($_POST['del_mess'] as $mess_id){
		$message_string .= "message_id = '".(int)$mess_id."' || ";
	}
	$message_string = preg_replace("/\|\| $/", "", $message_string);
	dbn("delete from ${db_name}_messages where login_id = '$user[login_id]' && ($message_string)");
	$error_str .= mysql_affected_rows()." ".$cw['message_deleted'].".<p />";
}

//get number of messages, to determine what to show the user.
db("select count(message_id) from ${db_name}_messages where login_id = '$user[login_id]'");
$counted = dbr();

$error_str .= "<br /><a href='message.php'>".$cw['send_message']."</a><br /><br />";

//print stuff to allow deletion of many messages.
if($counted[0] > 1){
	$error_str .= "<FORM method=POST action=$_SERVER[PHP_SELF] name=message_form>";
	$error_str .= "<a href=javascript:TickAll(\"message_form\")>".$cw['invert_message_selection']."</a><p />";
}

if($counted[0] == 0){ //no messages
	$error_str .= $st[76];

} else {
	
	$col_arr = array('#330000', '#432100', '#332000', '#996666');

	$error_str .= print_messages($user['login_id'], 1, 0, $col_arr);
	if($counted[0] > 1) {
		$error_str .= "<a href=javascript:TickAll(\"message_form\")>".$cw['invert_message_selection']."</a>";
		$error_str .= " - <input type='submit' value='".$cw['delete_selected']."' /></form>";
	}
	
	dbn("update ${db_name}_users set last_access_msg='".time()."' where login_id = '$user[login_id]'");
	$user['last_access_msg'] = time();
}

print_page($cw['messages'],$error_str);
?>
