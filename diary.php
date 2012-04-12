<?php
require_once("user.inc.php");
$filename = 'diary.php';

$text = "";

//max number of entries in diary.
if($user['login_id'] != 1 && ($user['login_id'] != OWNER_ID && OWNER_ID != 1)){
	$max = 50;
} elseif($user['login_id'] == 1) {
	$max = 200;
} else {
	$max = 5000;
}

db("select count(entry_id) from ${db_name}_diary where login_id = $user[login_id]");
$num_ent = dbr();

$rs = "<p /><a href='$_SERVER[PHP_SELF]'>".$cw['back_to_diary']."</a><br />";


// Adds
if(isset($add)) {
	if($num_ent[0] > $max) {
		print_page($cw['error'],$st[151]);
	}
	$text .= "<FORM method=POST action=diary.php>";
	$text .= "<p /><br /><br />".$cw['diary_entry'].":<br />";
	$text .= "<textarea name=add_ent value='' cols=50 rows=20 wrap=soft></textarea>";
	$text .= "<p /><input type='submit' value=".$cw['submit']." /></form><p />";
	print_page($cw['add_diary_entry'],$text);
}

//adding an entry into the DB.
if(isset($add_ent) || isset($log_ent)){
	if($num_ent[0] > $max) {
		print_page($cw['error'],$st[151]);
	}


	if(isset($log_ent)){//entry coming in from a log.
		db("select text,sender_name from ${db_name}_messages where message_id = '$log_ent' && (login_id = '$user[login_id]' || login_id < 0)");
		$message_text = dbr(1);
		$add_ent = "$message_text[sender_name]\n\n\n$message_text[text]";
	}
	$add_ent = addslashes(strip_tags($add_ent));
	dbn("insert into ${db_name}_diary (timestamp,login_id, entry) values(".time().",'$user[login_id]','$add_ent')");
	$text .= $st[152];
}


//Deletes
if(isset($delete)) { //delete single
	dbn("delete from ${db_name}_diary where entry_id='$delete' && login_id = '$user[login_id]'");
} elseif(isset($delete_all)){ //delete all
	if(!isset($sure)) {
		get_var('Delete all','diary.php',$st[153],'sure','yes');
		$rs = "<a href='$_SERVER[PHP_SELF]'>".$cw['back_to_diary']."</a>";
	} else{
		dbn("delete from ${db_name}_diary where login_id = '$user[login_id]'");
		$text .= $st[154];
		$rs = "<a href='$_SERVER[PHP_SELF]'>".$cw['back_to_diary']."</a>";
	}
}elseif(isset($del_select)){ //delete selected
	if(empty($del_ent)){
		$text .= $st[155];
	} else {
		$del_str = "";
		foreach($del_ent as $value){
			$del_str .= "entry_id = '$value' || ";
		}
		$del_str = preg_replace("/\|\| $/", "", $del_str);
		dbn("delete from ${db_name}_diary where login_id = '$user[login_id]' && (".$del_str.")");
		$num_del = mysql_affected_rows();
		$text .= sprintf($st[156], $num_del);
	}
}


//Edits
if(isset($edit)){//edit screen
	db("select * from ${db_name}_diary where entry_id = '$edit' && login_id = '$user[login_id]'");
	$entry = dbr(1);
	$entry_txt = stripslashes($entry['entry']);
	$text .= "<FORM method=POST action=diary.php>";
	$text .= "<input type=hidden name=edit2 value='$entry[entry_id]' />";
	$text .= "<p />".$cw['change_text_here'].":<br />";
	$text .= "<textarea name=edit_ent cols=50 rows=20 wrap=soft>$entry_txt</textarea>";
	$text .= "<p /><input type='submit' value=".$cw['submit']." /></form><p />";
	print_page($cw['add_diary_entry'],$text);

} elseif(isset($edit2)){//saving edited entry
	$edit_ent = addslashes($edit_ent);
	dbn("update ${db_name}_diary set entry = '$edit_ent' where entry_id='$edit2' && login_id = '$user[login_id]'");
	$text .= $st[157];
}



//Lists

//Top of front diary page
db("select count(entry_id) from ${db_name}_diary where login_id = '$user[login_id]'");
$num_ent = dbr();
$text .= sprintf($st[158], $max, $num_ent[0]);
if($user['login_id'] == 1){
	$text .= $st[159];
}

if(!$num_ent[0]){//no entries in diary
	if($num_ent[0] < $max) {
		$text .= "<p /><br /><a href='$_SERVER[PHP_SELF]?add=1'>".$cw['add_entry']."</a>";
	} else {
		$text .= $st[151];
	}
	$text .= $st[160];
} else {

	if($num_ent[0] < $max) {
		$text .= "<p /><br /><a href='$filename?add=1'>".$cw['add_entry']."</a>";
	} else {
		$text .= $st[161];
	}

	$text .= $st[162];
	$text .= make_table(array($cw['date_entered'],$cw['entry']));
	if($num_ent[0] > 1){
		$text .= "<FORM method=POST action=diary.php name=quick_del><input type=hidden name=del_select value=1 />";
	}

	db2("select * from ${db_name}_diary where login_id = '$user[login_id]' order by timestamp desc");

	while($entry = dbr2(1)) {//list entries
		$entry['entry'] = stripslashes($entry['entry']);
		$entry['entry'] = mcit($entry['entry']);
		$e_num = $entry['entry_id'];
		$entry['entry_id'] = "- <a href='$filename?edit=$e_num'>".$cw['edit']."</a> - <a href='$filename?delete=$e_num'>".$cw['delete']."</a>";
		if($num_ent[0] > 1){
			 $entry['entry_id'].= "- <input type=checkbox name=del_ent[$e_num] value=$e_num />";
		 }
		$text .= make_row(array("<b>".date("M d - H:i",$entry['timestamp'])."</b>",$entry['entry'],$entry['entry_id']));
	}
	$text .= "</table><br />";
}

if ($num_ent[0] > 1){//show the big delete options
	$text .= "<br /><input type='submit' value='".$cw['delete_selected_entries']."' />  - <a href=javascript:TickAll(\"quick_del\")>".$cw['invert_entry_selection']."</a></form><br />";
	$text .= "<br /><a href='$filename?delete_all=1'>".$st[163];
}
$rs = "<p /><a href='location.php'>".$cw['back_star_system']."</a>";
// print page
print_page($st[164],$text);
?>