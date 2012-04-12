<?php
//Bugtracking and featurerequest systems. created by DJCapelis. with fixes by Moriarty.
require_once("common.inc.php");

db_connect();

if(isset($_POST['submit'])) {
	dbn("INSERT INTO server_issuetracking (id, creation, title, description, status, login_id, game) VALUES ('', '".time()."', '".mysql_escape_string($_POST['title2'])."', '".mysql_escape_string($_POST['text'])."', '10','".$login_id."', '".mysql_escape_string($_POST['game_n'])."')");
	echo "<script>window.alert(\"Your request has been submitted.\");window.location=('bugs_tracker.php');</script>";
	exit();
}

print_header($cw['open_a_bug']);
?>
 <table>
 <font size=+1>
 <center>
 <?php
 echo $cw['open_a_bug'];
 ?>
 </center>
 </font>
 <form method="post">
 <input type="hidden" name="submit" value="true" />
 <tr><td align="right">Give a SHORT title</td><td class="givebg"><input name=title2 size=30 /></input></td></tr>
  <tr><td align="right">Game it appeared in</td><td class="givebg"><input name=game_n size=30 /></input></td></tr>
 <tr><td align="right">Please be <b class='b1'>very</b><br />detailed in your<br />description:</td><td class="givebg"><textarea name="text" cols="55" rows="10"></textarea></td></tr>
 <tr><td colspan="2" align="center"><input type="submit" value="Submit Bug" />
 </form>
 <br /><br />
<center><a href='bugs_tracker.php'>Back to Bug Tracking System</a></center>
</table>
<?php
print_footer();
?>
