<?php
require_once("common.inc.php");

db_connect();

print_header($cw['new_account']);

//echo "<center><br /><a href='/'><img src=$directories[images]/se_logo.jpg border=0 /></a><br /></center>";

//include_once("rules.htm");

if (!$_GET['id']) {
?>

<center><br />
<FORM method=POST action="signup.php" name=signup>
<blockquote><?php echo $st[664]; ?> 
<table cellspacing=1 cellpadding=2 border=0><tr bgcolor=#555555>
<tr>
<td bgcolor=#555555 align=right><b><?php echo $st[665]; ?>:</b></td>
<td bgcolor=#333333><input name='l_name' value='' size=20 />
</td>
</tr>
<tr><td bgcolor=#555555 align=right><B><? echo $cw['password'] ; ?>:</B></td>
<td bgcolor=#333333><input type=password name='passwd' value='' size=20 /></td></tr>
<tr><td bgcolor=#555555 align=right><B><?php echo $st[666] ; ?>:</B></td>
<td bgcolor=#333333><input type=password name='passwd_verify' value='' size=20 /></td></tr>
<tr><td bgcolor=#555555 align=right><B><?php echo $st[667]; ?>:</B></td>
<td bgcolor=#333333><input name='real_name' value='' size=20 /></td></tr>
<tr><td bgcolor=#555555 align=right><B><?php echo $st[668]; ?>:</B><br /><?php echo $st[669]; ?></td>
<td bgcolor=#333333><input name='email_address' value='' size=20 /></td></tr>
<tr><td  bgcolor=#555555 align=right><B><?php echo $st[670]; ?>:</B><br /><?php echo $st[671]; ?></td>
<td bgcolor=#333333><input name='email_address_verify' value='' size=20 /></td></tr>
<!--<tr><td bgcolor=#555555 align=right><B>Internet Connection Speed:</B><br />(Used to determine user options.)</td>
<td bgcolor=#333333><input type=radio name='con_speed' value=1 />Slow
<br /><input type=radio name='con_speed' value=2 checked />Average
<br /><input type=radio name='con_speed' value=3 />Fast</td></tr>-->
<input type="hidden" name="con_speed" value="3">

<tr align=right><td><br /><br /><?php echo $st[672]; ?></tr></td>
<tr><td  bgcolor=#555555 align=right>AIM<B>:</B></td>
<td bgcolor=#333333><input name='aim' value='' size=20 /></td></tr>
<tr><td bgcolor=#555555 align=right>ICQ<B>:</B></td>
<td bgcolor=#333333><input name='icq' value='' size=20 /></td></tr>
<tr><td bgcolor=#555555 align=right>MSN<B>:</B></td>
<td bgcolor=#333333><input name='msn' value='' size=20 /></td></tr>
<tr><td bgcolor=#555555 align=right>Yahoo<B>:</B></td>
<td bgcolor=#333333><input name='yim' value='' size=20 /></td></tr>
</td></tr></table>

</blockquote>
<p /><?php echo $st[673]; ?>
<br /><center><input type='submit' value="<?= $cw['submit'] ?>" /></center> 
</form><p />
<small><?php echo $st[674]; ?></small>

<?php
} else {
	db("select * from user_accounts where login_id=".((int)$_GET['id']));
	if (!dbc()) {
		echo "URL invalide, ce compte n'existe pas";
	} else {
		
?>

<?= $st[1821] ?><br /><br />
<FORM method="POST" action="signup.php" name="signup">
<input type=="hidden" name="id" value="<?= $_GET['id'] ?>" />
<table cellspacing=1 cellpadding=2 border=0>
	<tr>
		<td bgcolor=#555555 align=right><b><?php echo $st[665]; ?>:</b></td>
		<td bgcolor=#333333><input name="pseudo" size="20" /></td>
	</tr>
	
	<tr>
		<td align="center" colspan="2"><input type="submit" value="Inscription" /></td>
	</tr>
</table>


<?php
	}
}


print_footer();
?>