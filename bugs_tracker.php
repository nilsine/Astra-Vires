<?php
//Bugtracking and featurerequest systems. created by DJCapelis. with fixes by Moriarty.
require_once("common.inc.php");

db_connect();

function create_tracking_table($substatus, $textstat) {
	global $status, $login_id;

	$action = "";
	$issue['id'] = "Bug ID";
	$issue['title'] = "Bug Title";
	$issue['status'] = "Status";
	$issue['login_id'] = "Submitted By";
	$issue['action'] = "Action(s)";
	$out = make_table($issue,"WIDTH=55%");

	$counter = 0;
	db("SELECT id, title, login_id FROM server_issuetracking WHERE status= ".$status.$substatus);

	while($issues=dbr(1)) {
		$action = "<a href='$_SERVER[PHP_SELF]?status=$status&id=$issues[id]&action=view'>View</a>";
		if($login_id == OWNER_ID) { //server admin only
			if($status == 1) {
				$action .= " - <a href='$_SERVER[PHP_SELF]?&status=$status&id=$issues[id]&action=forward'>Outstanding</a>";
				$action .= " - <a href='$_SERVER[PHP_SELF]?&status=$status&id=$issues[id]&action=resolve'>Close</a>";
			} elseif($status == 2) {
				$action .= " - <a href='$_SERVER[PHP_SELF]?&status=$status&id=$issues[id]&action=forward'>Close</a>";
				$action .= " - <a href='$_SERVER[PHP_SELF]?&status=$status&id=$issues[id]&action=up'>Up</a>";
				$action .= " - <a href='$_SERVER[PHP_SELF]?&status=$status&id=$issues[id]&action=down'>Down</a>";
			} elseif($status == 3) {
				$action .= " - <a href='$_SERVER[PHP_SELF]?&status=$status&id=$issues[id]&action=reopen'>Reopen</a>";
				$action .= " - <a href='$_SERVER[PHP_SELF]?&status=$status&id=$issues[id]&action=up'>Up</a>";
				$action .= " - <a href='$_SERVER[PHP_SELF]?&status=$status&id=$issues[id]&action=down'>Down</a>";
			}
		} //end of admin only.
		$issue['id'] = $issues['id'];
		$issue['title'] = $issues['title'];
		$issue['status'] = $textstat;

		db2("select login_name from user_accounts where login_id = '$issues[login_id]'");
		$temp_store = dbr2(1);

		$issue['login_id'] = $temp_store['login_name'];
		$issue['action'] = $action;
		$out .= make_row($issue);
		$counter ++;
	}

	if($counter == 0) { //if no bugs
		$issue['id'] = "NONE";
		$issue['title'] = "";
		$issue['status'] = "";
		$issue['login_id'] = "";
		$issue['action'] = "";
		$out .= make_row($issue);
	}
	$out .= "</table>";
	return " - $counter Entries<br />".$out;
}
//function to figure out details about a bug
function bug_details($in_status){
	preg_match_all("/[0-9]/", $in_status, $ar_status);

	if($ar_status[0][0] == 1){
		$cent_txt = "Open";
		$end_txt = "Un-Assigned";
	} elseif($ar_status[0][0] == 2) {
		$cent_txt = "Outstanding";
		if($ar_status[0][1] == 1){
			$end_txt = "Critical";
		} elseif($ar_status[0][1] == 2) {
			$end_txt = "Major";
		} elseif($ar_status[0][1] == 3) {
			$end_txt = "Minor";
		} elseif($ar_status[0][1] == 4) {
			$end_txt = "Trivial";
		} elseif($ar_status[0][1] == 5) {
			$end_txt = "Quickie";
		} else {
			$end_txt = "Un-Assigned";
		}
	} elseif($ar_status[0][0] == 3) {
		$cent_txt = "Closed";
		if($ar_status[0][1] == 1){
			$end_txt = "Fixed";
		} elseif($ar_status[0][1] == 2) {
			$end_txt = "Left Unfixed";
		} elseif($ar_status[0][1] == 3) {
			$end_txt = "Duplicate";
		} elseif($ar_status[0][1] == 4) {
			$end_txt = "Invalid";
		} elseif($ar_status[0][1] == 5) {
			$end_txt = "Feature";
		} else {
			$end_txt = "Un-Assigned";
		}
	}
	return quick_row("Status", $cent_txt).quick_row("Type", $end_txt);
}


/**************end of functions **************/


if(!empty($_GET['action'])){
	$action = $_GET['action'];
}

if(!empty($_GET['status'])) {
	$status = $_GET['status'];
} else {
	$status = 1;
}

//performing an action that involves moving the item.
if(isset($action) && $action != "view") {
	if($login_id != OWNER_ID) 	{
		echo "<script>window.location=('$_SERVER[PHP_SELF]?status=$status')</script>";
		exit();
	}
	db("SELECT status FROM server_issuetracking WHERE id = $id");
	$issue = dbr(1);
	$stats = $issue['status'];
	$first = $stats{0};
	$second = $stats{1};
	if($action == "forward") {
		if($first == 3) {
			echo "<script>window.location=('$_SERVER[PHP_SELF]?status=$status')</script>";
			exit();
		} 
		dbn("UPDATE server_issuetracking SET status = ".($first + 1)."1 WHERE id = $id");
		echo "<script>window.location=('$_SERVER[PHP_SELF]?status=".($status + 1)."')</script>";
		exit();
	} elseif($action == "up") {
		if($second == 1) {
			$second = 6;
		}
		dbn("UPDATE server_issuetracking SET status = ".$first.($second - 1)." WHERE id = $id");
		echo "<script>window.location=('$_SERVER[PHP_SELF]?status=$status')</script>";
		exit();
	} elseif($action == "down") {
		if($second == 5) {
			$second = 0;
		}
		dbn("UPDATE server_issuetracking SET status = ".$first.($second + 1)." WHERE id = $id");
		echo "<script>window.location=('$_SERVER[PHP_SELF]?status=$status')</script>";
		exit();
	} elseif($action == "reopen") {
		dbn(" UPDATE server_issuetracking SET status = 10 WHERE id = $id");
		echo "<script>window.location=('$_SERVER[PHP_SELF]?status=1')</script>";
		exit();
	} elseif($action == "resolve") {
		dbn("UPDATE server_issuetracking SET status = 31 WHERE id = $id");
		echo "<script>window.location=('$_SERVER[PHP_SELF]?status=3')</script>";
		exit();
	}
}

//showing a bug's details
if(isset($action) && $action == "view" && !isset($submit)) {
	db("SELECT * FROM server_issuetracking WHERE id = '$id'");
	$issue=dbr(1);

	print_header("View Issue Number " . $issue['id'] . "");
	echo "<p /><center><font size=+1>Issue #<b> $issue[id]:</b></font><p />";
	echo "<a href='$_SERVER[PHP_SELF]?status=$status'>Back to Bug Tracking System</a></center>";
	echo "<p />".make_table(array("",""));
	echo quick_row("Title", nl2br(strip_tags(stripslashes($issue['title']), '<br /><b>')));
	echo bug_details($issue['STATUS']);

	echo quick_row("Game Afflicted", $issue['game']);

	db2("select login_name from user_accounts where login_id = '$issue[login_id]'");
	$temp_l_name = dbr2(1);

	echo quick_row("Reported By", "<b class='b1'>".$temp_l_name['login_name']."</b>");
	echo quick_row("Reported On", "<b>".date("M d Y - H:i", $issue['creation'])."</b>");

	echo quick_row("Description", nl2br(strip_tags(stripslashes($issue['description']), '<br /><b>')));
	echo "</table>";
	echo "<p />";
	if($login_id == OWNER_ID){
		echo " - <a href='$_SERVER[PHP_SELF]?&status=$status&id=$issue[id]&action=forward'>Outstanding</a> - <a href='$_SERVER[PHP_SELF]?&status=$status&id=$issue[id]&action=resolve'>Close</a><p />";
	}
	echo "Update:<br />";
	echo "<form method='post'>";
	echo "<input type='hidden' name='submit' value='true' />";
	echo "<textarea name='update' cols='70' rows='7'>";
	echo "</textarea>";
	echo "<br /><input type='submit' value='Submit Update' />";
	if($login_id == OWNER_ID){ //only the owner may change the status
		echo "<br />Set to Outstanding - <select name=outstanding_status>";
		echo "<option value=>";
		echo "<option value=21>Critical";
		echo "<option value=22>Major";
		echo "<option value=23>Minor";
		echo "<option value=24>Trivial";
		echo "<option value=25>Quickie";
		echo "</select><br />Set to Closed - ";
		echo "<select name=close_status>";
		echo "<option value=>";
		echo "<option value=31>Fixed";
		echo "<option value=32>Left";
		echo "<option value=33>Duplicate";
		echo "<option value=34>Invalid";
		echo "<option value=35>Feature";
		echo "</select>";
	}
	echo "</form>";
	
	echo "<center><a href='$_SERVER[PHP_SELF]?status=$status'>Back to Bug Tracking System</a></center>";
	print_footer();
}

//showing a bugs details after having submitted an update
if(isset($action) && $action == "view" && isset($submit)) {
	db("SELECT login_id, description, STATUS FROM server_issuetracking WHERE id = ".$_GET['id']);
	$issue=dbr(1);

	db("SELECT login_name FROM user_accounts WHERE login_id = ".$login_id);
	$username = dbr(1);

	$newtext = $issue['description'];
	if(!empty($_POST['update'])){//only update if text not empty
		$newtext = $issue['description']."<br /><br />\n\n<b>UPDATE</b> - <b class='b1'>$username[login_name]</b> - </b>- ".date( "M d - H:i")."<br />\n".$_POST['update'];
	}

	//update status. but only if Server admin
	if($_POST['outstanding_status'] > 0 && $login_id == OWNER_ID){
		$new_status = $_POST['outstanding_status'];

	}elseif($_POST['close_status'] > 0 && $login_id == OWNER_ID){
		$new_status = $_POST['close_status'];

	} else { //no status change
		$new_status = $issue['STATUS'];
	}

	dbn("UPDATE server_issuetracking SET description = '".mysql_escape_string($newtext)."', STATUS='$new_status' WHERE id = '$id'");
	echo "<script>window.location=('$_SERVER[PHP_SELF]?status=$status')</script>";
	exit();
}

print_header("Bug Tracking System");
echo "<br />";
echo "<center>";
echo "<font size=+1>Bug Tracking System:<br /></font>";
echo "<br />";
echo "- <a href='bugs_submit.php?&status=".$status."'>Open a Bug</a> -<br />";
echo "<font size=-1>";
echo "<a href='$_SERVER[PHP_SELF]?status=1'>View Open Bug(s)</a> - ";
echo "<a href='$_SERVER[PHP_SELF]?status=2'>View Outstanding Bug(s)</a> - ";
echo "<a href='$_SERVER[PHP_SELF]?status=3'>View Closed Bug(s)</a>";
echo "</font>";
echo "</center>";
echo "<p />";
echo "<font size=+1>";
if($status == 1) {
	echo "Open Bug(s):";
	echo "</font>";
	echo(create_tracking_table(0,'OPEN'));
} elseif($status == 2) {
	echo "Outstanding Bug(s):";
	echo "</font>";
	echo "</p><p />Critical";
	echo(create_tracking_table(1,'CRITICAL'));
	echo "</p><p />Major";
	echo(create_tracking_table(2,'MAJOR'));
	echo "</p><p />Minor";
	echo(create_tracking_table(3,'MINOR'));
	echo "</p><p />Trivial";
	echo(create_tracking_table(4,'TRIVIAL'));
	echo "</p><p />Quickies";
	echo(create_tracking_table(5,'QUICKIE'));
} elseif($status == 3) {
	echo "Closed Bug(s):";
	echo "</font>";
	echo "</p><p />Fixed";
	echo(create_tracking_table(1,'FIXED'));
	echo "</p><p />Left unfixed";
	echo(create_tracking_table(2,'LEFT'));
	echo "</p><p />Duplicate - Duplicate report";
	echo(create_tracking_table(3,'DUPLICATE'));
	echo "</p><p />Invalid - Not a bug, or works fine";
	echo(create_tracking_table(4,'INVALID'));
	echo "</p><p />Feature - It's supposed to do that!!";
	echo(create_tracking_table(5,'FEATURE'));
}
echo "</p>";
echo "<br />";
//server admin gets link back to game when opened from in-game.
if(!preg_match("/game_listing\.php/",$_SERVER['HTTP_REFERER']) && $login_id == OWNER_ID){
	echo "<a href='javascript:close()'>Close Window</a>";
} else {
	echo "<a href='game_listing.php'>Back to Gamelisting</a>";
}
print_footer();
?>
