<?php
setlocale(LC_ALL, 'fr_FR');
header('Content-Type: text/html; charset=ISO-8859-1');
require_once("dir_names.php");
require_once("$directories[includes]/costs.php");
require_once("$directories[games]/config.dat.php");
require_once("$directories[includes]/langage_en.inc.php");


require_once("bp_api.php");


//magic quotes on. take slashes out
if (get_magic_quotes_gpc() == 1) {
	recursive_stripslashes($_GET);
	recursive_stripslashes($_POST);
	recursive_stripslashes($_COOKIE);
}


//get session details
if(isset($_COOKIE['login_id'])){
	$login_id = (int)$_COOKIE['login_id']; //set login_id
} else {
	$login_id = 0;
}

if(isset($_COOKIE['session_id'])){
	$session_id = mysql_escape_string((string)$_COOKIE['session_id']); //set session_id
} else {
	$session_id = 0;
}

$id_parrain = 0;
if (!$_COOKIE['id_parrain']) {
	if ($_GET['pid']) {
		setcookie('id_parrain', $_GET['pid'], time() + 15 * 86400);
	}
} else {
	$id_parrain = $_COOKIE['id_parrain'];
}


//initial declarations for certain global vars
//not particularly necessary, but just to make sure.
if(!eregi($cw['run']."_",$_SERVER['PHP_SELF'])){ //for maints
	$db_name = "";
}

//contains information about the user from the user_account table (no game specific data)
$p_user = array();

//contains information about the game (from se_games table)
$game_info = array();

//needed for wokring out the table tags.
$show_bars = false;

//seed random number generator. Use IP address as part of seed
mt_srand((double)microtime()*1000000 + ip2long($_SERVER['REMOTE_ADDR']));


/**********************
Page Display Functions
***********************/

function print_header($title, $header='') {
	global $user_options, $directories, $chat, $user;

	if($user_options['color_scheme']) {
		$style = "style".$user_options['color_scheme'].".css";
	} else {
		$style = "style1.css";
	}
	@header("Content-type: text/html; charset=iso-8859-1");
//HTML start of page.
	if ($user_options['montrer_chat']) {
		require_once dirname(__FILE__)."/chat/src/phpfreechat.class.php";
		require_once dirname(__FILE__)."/chat/config.php";
		$params['nick'] = $user['login_name'];
		$chat = new phpFreeChat($params);
	}
?>
<?php //require_once("ClickTale/ClickTaleTop.php"); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<?= $header ?>
<title>[ Astra Vires - <? echo $title;?> ]</title>
<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" href="style.css" />
<!--[if lte IE 7]>
<style type="text/css">
html .jqueryslidemenu{height: 1%;} /*Holly Hack for IE7 and below*/
</style>
<![endif]-->
<script language="JavaScript" src="<?php echo URL_PREFIX."/".$directories['includes']; ?>/javascript.js" type="text/javascript"></script>
<script language="JavaScript" src="<?= URL_PREFIX ?>/js/jquery-1.3.2.min.js" type="text/javascript"></script>
<script language="JavaScript" src="<?= URL_PREFIX ?>/js/jquery.boxy.js" type="text/javascript"></script>
<script language="JavaScript" src="<?= URL_PREFIX ?>/js/jqueryslidemenu.js" type="text/javascript"></script>
<script language="JavaScript" src="<?= URL_PREFIX ?>/js/general.js" type="text/javascript"></script>



</head>
<body>

<div id="curseur" class="infobulle"></div>

<div id="main">

<div id="popup">
	<div id="popup_contenu">&nbsp;</div><br />
	<a href="#null" onclick="jQuery('#popup').hide();">Fermer la fenêtre</a>
</div>
<?php
} //end print_header function



//prints the bottom of a page.
function print_footer($nom_page_analytics = '') {
	global $rs, $start_time, $database_link, $show_bars, $p_user, $chat, $user_options;

	echo $rs;

	//these lines are used to determine how long a page took to process. Directly linked to time starter in config.dat.php.
	if($start_time > 0){
		$end_time = explode(" ",microtime());
		$end_time = $end_time[1] + $end_time[0];
		$total_time = ($end_time - $start_time);
		printf($st[1118],$total_time);
	}

	if ($user_options['montrer_chat']) {
		//$chat->printChat();
	}

	print "<br /><br /><br /><div align='center'>";

	if ($p_user['bp_user_id'])
		print 'Réalisation <a href="http://www.nilsine.fr/" target="_blank">Nilsine</a> - Reproduction totale ou partielle interdite';
	else
		//print "<a href='http://www.jeuxvideo.com/boite-a-idees/00005261-astra-vires-web-boite-a-idees.php' target='_blank'>Votez pour Astra Vires sur jeuxvideo.com</a>";

	print "</div>";

	//close the display table.
	//determine if have to close an extra table or not.
	//if there are no bars, there are no tables
	if($show_bars){
		//we have two tables to shut.
		echo "\n</td></tr></table>";
		echo "\n</td></tr></table>";
	}
?>
</div>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-712399-17");
pageTracker._trackPageview("<?= $nom_page_analytics ?>");
} catch(err) {}</script>

<script type="text/javascript">
var uservoiceOptions = {
  /* required */
  key: 'nilsine',
  host: 'nilsine.uservoice.com',
  forum: '64261',
  showTab: true,
  /* optional */
  alignment: 'right',
  background_color:'#f00',
  text_color: 'white',
  hover_color: '#06C',
  lang: 'fr'
};

function _loadUserVoice() {
  var s = document.createElement('script');
  s.setAttribute('type', 'text/javascript');
  s.setAttribute('src', ("https:" == document.location.protocol ? "https://" : "http://") + "cdn.uservoice.com/javascripts/widgets/tab.js");
  document.getElementsByTagName('head')[0].appendChild(s);
}
_loadSuper = window.onload;
window.onload = (typeof window.onload != 'function') ? _loadUserVoice : function() { _loadSuper(); _loadUserVoice(); };
</script>

<?php
	echo "\n</body>\n</html>";
	//require_once("ClickTale/ClickTaleBottom.php");

	//just to be safe. ;)
	if(!empty($database_link)){
		mysql_close($database_link);
	}
	exit();
}

//Print a page consisting of the header and the footer only, along with content.
function print_s_page($title, $text, $nom_page_analytics = ''){
	print_header($title);
	echo $text;
	print_footer($nom_page_analytics);
}

/**********************
Input Checking Functions
**********************/

//allows alphanumeric and the some other characters but no spaces
function valid_input($input) {
	return eregi("^([a-z0-9])+$",$input);
}


//allows alphanumeric and the some other characters, as well as spaces. removes HTML and PHP.
function correct_name($input) {
	$input = strip_tags(htmlspecialchars($input, ENT_QUOTES));
//	return trim(preg_replace("[^a-z0-9~@$%&*_+-=£§¥ .]", "", $input));
//	return trim(htmlspecialchars(substr($input, 0, 30)));
	return trim(substr($input, 0, 30));
}


//function to remove all slashes (useful for magic quotes);
function recursive_stripslashes(&$var) {
	foreach($var AS $key => $value) {
		if (is_array($value)) {
			recursive_stripslashes($value);
		} else {
			$var[$key] = stripslashes($value);
		}
	}
}

function nombre($nb, $dec=0) {
	$nb = number_format($nb, $dec, ',', ' ');
	return str_replace(' ', '&nbsp;', $nb);
}


/**********************
* Database Functions
**********************/
/*
Function: connect to the database. Will write to the error log if cannot connect.
*/
function db_connect(){
	global $database_link;
	$database_link = @mysql_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD) or write_to_error_log($st[1119].mysql_error()."</b>");
	mysql_select_db(DATABASE, $database_link) or mysql_die("");
}

//send a query to the database.
//note: db_func_query is manually cleared in server_maint.inc in the db backup function (obvious reasons).
function db($string) {
	global $db_func_query, $database_link;
	$db_func_query = mysql_query($string, $database_link) or mysql_die($string);
}

//collect results of query made by db() function
function dbr($rest_type = 0) {
	global $db_func_query;
	if($rest_type == 0){
		return mysql_fetch_array($db_func_query, MYSQL_BOTH);
	} else {
		return mysql_fetch_array($db_func_query, MYSQL_ASSOC);
	}
}

function dbc()
{
	global $db_func_query;
	return mysql_num_rows($db_func_query);
}

//send a query to the database.
function db2($string) {
	global $db_func_query2, $database_link;
	$db_func_query2 = mysql_query($string, $database_link) or mysql_die($string);
}

//collect results of query made by db2() function
function dbr2($rest_type = 0) {
	global $db_func_query2;
	if($rest_type == 0){
		return mysql_fetch_array($db_func_query2, MYSQL_BOTH);
	} else {
		return mysql_fetch_array($db_func_query2, MYSQL_ASSOC);
	}
}

//send an update or insert query to the database. no select's.
function dbn($string) {
	global $database_link;
	mysql_query($string, $database_link) or mysql_die($string);
}


/*
is called when there is a mysql error.
kills the script after trying seeing if it can repair the error, and then logging it.
*/
function mysql_die($string) {
	$error_txt = sprintf($st[1120], $string).mysql_errno().$st[1121].mysql_error();
	fix_table();
	write_to_error_log($error_txt);
}


/*
Nifty little function that allows for servers to be left alone for ages by the server admin (not that i'd ever condone such an action :) ).
if there is a mysql error, this function is called to see if the error is a corrupt table.
if it is a corrupt table, it will run the mysql REPAIR function on it.
99 times out of a 100 this will get everything working again.
*/
function fix_table(){
	$err_str = mysql_error();
	if(mysql_errno() == 1016){ //can't open table error.

		//break down the error message so only left with table name.
		$table_to_repair = str_replace($st[1122], "", $err_str);
		$table_to_repair = str_replace(".MYD'. (errno: 144)", "", $table_to_repair);
		$table_to_repair = str_replace(".MYI'. (errno: 145)", "", $table_to_repair);
		dbn("REPAIR TABLE $table_to_repair");
	}
}


/*
function that will write a number of vars to the error log.
inputs:
write_text = text to write
to_user = any custom message that is to be shown to the user
clear_log = 1 = clear the log, -1 = maintenance entry to log, 0 = nothing.
*/
function write_to_error_log($write_text, $to_user = "", $clear_log = 0){
	global $user, $log_errors, $log_name, $directories;

	if($clear_log == -1){ //being called from /games dir
		$pre_file = "../";
	} else {
		$pre_file = "./";
	}
	$file_loc = $pre_file."$directories[games]/"; //directory the error log is in.

	if($clear_log == 0){ //making an entry into the log

		//collect as many variables as we can.
		//the pregs are used to replace tags with []. otherwise XML will read them as elements of it's own.
		ob_start();

		//just in-case a second passes between start of dump, and end of. that'd mess up the reporting! :)
		$temp_time = time();

		echo "\n\n<t_{$temp_time}>";
		echo "\n<timestamp>{$temp_time}</timestamp>";
		echo "\n<user>$user[login_name]</user>";
		echo "\n<file>$_SERVER[PHP_SELF]</file>";
		echo "\n<get_vars>";
		echo preg_replace("/(\<|\>)/e", "'\\1' == '<' ? '[' : ']'", var_dump($_GET))."</get_vars>";
		echo "\n<post_vars>";
		echo preg_replace("/(\<|\>)/e", "'\\1' == '<' ? '[' : ']'", var_dump($_POST))."</post_vars>";
		echo "\n<cookie_vars>";
		echo preg_replace("/(\<|\>)/e", "'\\1' == '<' ? '[' : ']'", var_dump($_COOKIE))."</cookie_vars>";
		echo "\n<backtrace>";
		echo preg_replace("/(\<|\>)/e", "'\\1' == '<' ? '[' : ']'", var_dump(debug_backtrace()))."</backtrace>"; //very useful!
		echo "\n<error_message>[blockquote]".preg_replace("/(\<|\>)/e", "'\\1' == '<' ? '[' : ']'", $write_text)."[/blockquote]</error_message>";
		echo "\n</t_{$temp_time}>\n\n";
		$output_store_xml = ob_get_clean();

		ob_start();//this will be dumped to screen if can't dump to file.
		echo "\n\n\n<hr><p />Date: ".date( "M d - H:i:s")."<br />".$cw['user'].": $user[login_name] <br />".$cw['file'].": $_SERVER[PHP_SELF] <p />".$st[1123];
		echo var_dump($_POST);
		echo "</pre>\n<p />".$st[1124];
		echo var_dump($_GET);
		echo "</pre>\n<p /> ".$st[1125];
		echo var_dump($_COOKIE);
		echo "</pre>\n<p />".$st[1126];
		echo var_dump(debug_backtrace()); //very useful function!
		echo $st[1127]." <blockquote>$write_text</blockquote>";

		$output_store_html = ob_get_clean();

		//errors are not to be logged, but printed to screen.
		if($log_errors == 0){
			if(!empty($to_user)){ //custom user error code.
				exit($st[1128].$to_user.$output_store_html);
			} else {
				print_header($cw['Error']);
				echo "<a href='javascript: history.back()'>".$st[1129].$output_store_html;
				print_footer();
			}
		}

		//just in case things go wrong later.
		$user_report = $st[1130].$output_store_html;

		$open_method = "a+"; //method to be used to open the file

	} elseif($clear_log == -1) {//saving a maint entry to the log
		$user_report = "";
		$open_method = "a+";
		$output_store_xml = "\n<date>".date( "M d - H:i:s")."</date>\n$write_text";
		$file_loc = ""; //maints are in same dir as error log, so don't need this set.

	} else { //emptying the log
		echo $st[1131]."<a href='location.php'>Star System</a>\n\n";
		$open_method = "w";
		$output_store_xml = "<last_emptied>".date( "M d - H:i:s")."</last_emptied>";
	}


	//ensure a stream was created
	if(!$stream = @fopen($file_loc.$log_name, $open_method)){
		echo sprintf($st[1132], $log_name).$user_report;

	//extra error checking (permissions are fickle on linux!).
	} elseif (!is_writable($file_loc.$log_name)) {
		echo $st[1133].$user_report;

	//output the final file, and complain if it wasn't possible
	} elseif(!fwrite($stream, $output_store_xml)){
		echo $st[1134].$user_report;

	} else { //close the log, and exit.
		fclose($stream);
		if($clear_log != 0){//no special output needed for emptying or maint
			if($clear_log == -1 && SERVER_ADMIN_EMAIL != ""){ //entry is from a fowled up maint.

				$mess_txt = $st[1135].date( "d/M/Y - H:i:s").$st[1136].URL_PREFIX.$st[1137];

				send_mail(SERVER_NAME, $_SERVER['SERVER_ADMIN'], $cw['server_admin'], SERVER_ADMIN_EMAIL, $st[1138], $mess_txt);
			}
			exit();
		}
		if(!empty($to_user)){
			exit($to_user.$st[1139]);
		} else {
			echo $st[1140];
		}
	}
	exit();
}



/**********************
HTML Table Functions
***********************/

// will output the beginning of a properly formatted table putting
//the values of the passed array in as the table headers;
// - expects an array.
function make_table($input = "", $width = "") {
	$ret_str = "<table cellspacing='1' cellpadding='2' border='0' $width><tr bgcolor='#555555'>";
	foreach($input as $value) {
		$ret_str .= "\n<th>$value</th>";
	}
	return $ret_str."\n</tr>";
}

//function for making a nice and pretty table
function make_table2($input = "", $css_class = ""){
	$ret_str = "\n\n<table class='$css_class'>\n<tr>";
	foreach($input as $value) {
		$ret_str .= "\n<th class='$css_class'>\n$value\n</th>";
	}
	return $ret_str."\n</tr>";
}

function q_row($input, $css_class){
	return "\n<tr><td class='$css_class'>\n$input\n</td></tr>";
}

//outputs a row of a table with the number values made bold;
// -- expects a array.
function make_row($input, $bgcolor='333333', $rowspan = null) {
	global $row_counter;

	if ($bgcolor == '333333') $bgcolor = ($row_counter % 2 == 0) ? '444444':'333333';

	$ret_str = "\n<tr bgcolor='#$bgcolor' align='left'>";

	foreach($input as $key => $value) {

		$str_rowspan = ($rowspan && $rowspan[$key]>1 )? "rowspan='$rowspan[$key]' class='fleet_number'": " ";


		if(! is_numeric($value)) { //only make numbers bold
			$ret_str .= "\n<td $str_rowspan align=center>$value</td>";
		} else {
			
			$value = nombre($value);
			$ret_str .= "\n<td $str_rowspan align=center>$value</td>";
		}
	}
	$row_counter++;
	return $ret_str."\n</tr>";

}

//outputs a two entry table row. the value is bolded if it's numerical;
//-- two strings / numbers
function quick_row($name,$value) {
	$ret_str = "\n<tr align='left'>";
	$ret_str .= "\n<td bgcolor='#555555' nowrap>$name</td>";
	if(! is_numeric($value)) {
		$ret_str .= "\n<td bgcolor='#333333'>$value</td>";
	} else {
		$value = nombre($value);
		$ret_str .= "\n<td bgcolor='#333333'>$value</td>";
	}
	return $ret_str."\n</tr>";
}



/**********
Data update/insertion Functions
**********/

//function to insert an entry into the user_history table
function insert_history($l_id, $i_text){
	global $db_name;

	preg_match('`/([^/]*)$`Uis', $_SERVER['PHP_SELF'], $res);
	if(empty($db_name)){
		$db_name = "None";
	}
	dbn("insert into user_history VALUES ('$l_id','".time()."','$db_name','".mysql_escape_string($i_text)." - Page ".$res[1]."','$_SERVER[REMOTE_ADDR]','$_SERVER[HTTP_USER_AGENT]')");
}

//post an entry into the news
function post_news($headline, $topic = "other") {
	global $db_name;

	db_connect();
	dbn("insert into ${db_name}_news (timestamp, headline, topic_set) values (".time().",'".mysql_escape_string($headline)."','$topic')");
	/*	News topic sets:
	'admin', 'attacking', 'bomb', 'clan', 'game_status', 'maint', 'other', 'player_status', 'planet', 'random_event', 'ship'
	*/
}

//function that will send a header correct e-mail, or return failure if it doesn't work
function send_mail($myname, $myemail, $contactname, $contactemail, $subject, $message) {
	$headers = "MIME-Version: 1.0\n";
	$headers .= "Content-type: text/plain; charset=iso-8859-1\n";
	$headers .= "X-Priority: 1\n";
	$headers .= "X-MSMail-Priority: High\n";
	$headers .= "X-Mailer: php\n";
	$headers .= "From: \"".$myname."\" <".$myemail.">\n";
	return (mail("\"".$contactname."\" <".$contactemail.">", $subject, $message, $headers));
}


/********************
Ship Information Functions
********************/

//function to figure out the size of a ship in textual terms
function discern_size ($size){
	if($size == 1){
		return $cw['tiny'];
	} elseif($size == 2){
		return $cw['very_small'];
	} elseif($size == 3){
		return $cw['small'];
	} elseif($size == 4){
		return $cw['medium'];
	} elseif($size == 5){
		return $cw['large'];
	} elseif($size == 6){
		return $cw['very_large'];
	} elseif($size == 7){
		return $cw['huge'];
	} elseif($size == 8){
		return $cw['gigantic'];
	}
}

//load ship types from database.
//will load whichever type is stipulated in the $ship_id, or all of them if it is set to 0 or lower.
function load_ship_types($ship_id) {
	global $db_name, $GAME_VARS;
	$ship_types = array();

	if(empty($db_name)){
		exit($st[1141]);
	}
	$ship_id_txt = ""; //load selected ship sql string
	if($ship_id > 0){
		$ship_id_txt = "&& type_id = '$ship_id'";
	}
	if($GAME_VARS['alternate_play_2'] > 0) { //only select developed ships. - as well as the EP.
		db("select s.* from se_ship_types s, se_admin_ships a, se_development_time y where (s.type_id = a.ship_type_id && s.type_id = y.item_id && a.{$db_name}_ship_status = 1 && s.auction != 1 && (y.${db_name}_available = 1) || s.type_id = 2) $ship_id_txt order by type_id");
	} else { //select all non-auction ships that admin leaves available.
		db("select s.* from se_ship_types s, se_admin_ships a where s.type_id = a.ship_type_id && a.{$db_name}_ship_status = 1 $ship_id_txt order by type_id");
	}
	if($ship_id > 0){//return selected ship type.
		$this_type = dbr(1);
		$this_type['cost'] += $this_type['fighters'] * $GAME_VARS['fighter_cost_earth'];
		return $this_type;

	} else {//not-selected specific ship type
		while($this_type = dbr(1)) {
			$this_type['cost'] += $this_type['fighters'] * $GAME_VARS['fighter_cost_earth'];
			$ship_types[$this_type['type_id']] = $this_type;
		}
	}
	return $ship_types;
}


//function that will check if $to_check is installed in $ship or not.
function config_check($to_check, $ship){

	//using strpos because phpman says it's faster than strstr, and uses less memory
	if(strpos($ship['config'], $to_check) === false){
		return false;
	} else {
		return true;
	}
//config_set set('bs','sh','hs','ls','na','po','so','oo','sv','sw','er','sj','ws','e1','e2','fr','sc','bo')
}


//list the configs that are installed on the ship.
//details = 1 - show details for configs
//details = 0 - return list of names of configs.
//lookup = the configs to lookup. left blank it will return all
function config_list($details, $lookup = -1){

	//empty config, so return nothing.
	if($lookup == ""){
		return $cw['None'];
	}

	$temp_where = "";

	//list selected $lookup configs, rather than all
	if($lookup != -1){

		$temp_where = " where ";

		//loop through all entries in lookup and add them to the where
		foreach(explode(",",$lookup) as $val){
			$temp_where .= "config_id = '$val' || ";
		}
		$temp_where = preg_replace("/\|\| $/", "", $temp_where);
	}

	$ret_str = "";

	db("select * from se_config_list $temp_where");

	//showing only title of configs.
	if($details == 0){

		while($list_conf = dbr(1)){
			$ret_str .= $list_conf['short_for']."<br />";
		}


	//showing all info about the configs.
	} else {

		//make tables with all information for all of resultaint configs.
		while($list_conf = dbr(1)){
			$ret_str .= make_table(array("",""),"WIDTH=95%");
			$ret_str .= quick_row($st[1142],$list_conf['config_id']);
			$ret_str .= quick_row($st[1143],$list_conf['short_for']);
			$ret_str .= quick_row($st[1144],$list_conf['type']);
			$ret_str .= quick_row($st[1145],$list_conf['description']);
			$ret_str .= quick_row($st[1146],$list_conf['does_to_ship']);
			$ret_str .= "</table><br />";
		}
	}

	return $ret_str;

}

/********************
Authorisation Checking Functions
********************/

//function that will create a hash of the user agent information plus IP.
function hash_user_agent () {
	return md5($_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR']);
	}


//function that will check to see if a player is logged in using session_id's.
//if user is the admin, it will set db_name, and game_info
function check_auth() {
	global $session_id, $login_id, $db_name, $p_user, $game_info;

	//get all details for the user with that sessionid/login_id combo
	//if the admin, don't use the session_id as a key
	db("select * from user_accounts where (login_id = '$login_id' && session_id = '$_COOKIE[session_id]') || (login_id = 1 && '$login_id' = 1)");
	$p_user = dbr(1);

	//admin session id/ session_exp
	if($login_id == 1){
		db("select * from se_games where session_id = '$session_id'");
		$game_info = dbr(1);
		$p_user['session_id'] = $game_info['session_id'];
		$p_user['session_exp'] = $game_info['session_exp'];
		$p_user['user_agent'] = $game_info['user_agent'];
		$db_name = $game_info['db_name'];
	}

	//echo $p_user['session_exp']."<br />".time();
	$next_exp = time() + SESSION_TIME_LIMIT;

	$agent_hash = hash_user_agent();

	//session is invalid.
	if($session_id == '' || $login_id == 0 || $session_id != $p_user['session_id'] || $p_user['session_exp'] < time() || $agent_hash != $p_user['user_agent']) {//session expired or invalid
		SetCookie("p_pass","",0);
		SetCookie("session_id",0,0);
		SetCookie("login_id",0,0);
		flush();
		if(!empty($login_id)){
			insert_history((int)$login_id, $st[1147]);
		}
		echo "<script>self.location='".URL_PREFIX."/';</script>";
		exit();

	} elseif($login_id != 1) { //session o.k.
		//if the user isn't in a game, and is pretending to be, throw them back to gamelisting.
		//if game is not set
		//and player is not looking at game_listing (which doesn't require db_name)
		//and player is not using logout.php for logout_game_listing
		//then send user to game-listing
//var_dump(strstr($_SERVER['PHP_SELF'], 'logout.php'));
		setAutoLoginCookie( $p_user['login_id'], $p_user['login_name'], $p_user['mdp'] ); // set the auto login cookie
		if($p_user['in_game'] == "" && strstr($_SERVER['PHP_SELF'], 'game_listing.php') === false && strstr($_SERVER['PHP_SELF'], 'ajax.php') === false && strstr($_SERVER['PHP_SELF'], 'user_extra.php') === false && ((strstr($_SERVER['PHP_SELF'], 'logout.php') !== false && (!isset($_GET['logout_game_listing']) || isset($_GET['comp_logout']) || isset($_GET['logout_single_game']))) || (strstr($_SERVER['PHP_SELF'], 'logout.php')) === false)){
			echo "<script>self.location='game_listing.php';</script>";
			exit();
		}

		dbn("update user_accounts set session_exp = '$next_exp', page_views = page_views + 1 where login_id = '$login_id'");
		$p_user['page_views'] ++;
		$p_user['session_exp'] = $next_exp;
		$db_name = $p_user['in_game'];

	} elseif($login_id == 1){ //update admin session time
		setAutoLoginCookie( $p_user['login_id'], $p_user['login_name'], $p_user['mdp'] ); // set the auto login cookie
		dbn("update se_games set session_exp = '$next_exp' where db_name = '$db_name'");
		$p_user['session_exp'] = $next_exp;
	}
}



/********************
Calculating Functions
*********************/

//function used to work out players scores
function score_func($login_id,$full){
	global $GAME_VARS, $db_name, $directories;
/*
Listed below are all of the score methods, and some info on them.
0 = Scores are off.
1 = (fighter kills + (ship kills * 10)) - (fighter kills * 0.75 + (ship kills *5))
2 = ship points killed - (ship points lost * 0.5)
*/
	if(!isset($GAME_VARS)){//the game vars arn't set, so load them
		require_once("./$directories[includes]/${db_name}_vars.inc.php");
	}
	if($full != 1) { //updating own score
		$extra_text = "login_id = '$login_id'";

	} else { //admin updating all scores
		$extra_text = "login_id > 5";
	}

	if($GAME_VARS['score_method'] == 1){ //only kills,are taken into account.
		dbn("update ${db_name}_users set score = (fighters_killed + (ships_killed * 10)) - (fighters_lost * 0.75 + (ships_lost * 5)) where ".$extra_text);
	} elseif($GAME_VARS['score_method'] == 2){ //point values
		$resultat = mysql_query("select sum(point_value) as points from ${db_name}_ships where login_id=$login_id");
		$data = mysql_fetch_array($resultat);
		$points = $data['points'] * 0.1;
		dbn("update ${db_name}_users set score = ships_killed_points - (ships_lost_points * 0.5) + $points where ".$extra_text);
	}
}

//function used to calculate the percentage of something. returns 0 if either input 0
function calc_perc($num1,$num2){
	if($num1 == 0 || $num2 == 0){
		return "$num1 (0%)";
	} else {
		return number_format($num1)." (".number_format(($num1 / $num2) * 100, 2, '.','')."%)";
	}
}

//function to figure out how many empty cargo bays there are on the ship.
function empty_bays(&$ship) {
	$ship['empty_bays'] = $ship['cargo_bays'] - $ship['metal'] - $ship['fuel'] - $ship['elect'] - $ship['colon'];
}

function format_nb($nb) {
	return number_format($nb);
}


/*********************
Misc Functions
*********************/

//function that will create a help-link.
function popup_help($topic, $width, $height, $string = "Info"){
	return "<a href='#' onclick=\"popup('{$topic}',{$width},{$height})\">$string</a>";
}


//pilfered from the net. and altered it a little for good measure.
//creates a alpha-numeric string of $length. contains uper and lower case chars).
function create_rand_string ($length) {
	// salt to select chars from
	$salt = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	$ret_str = "";

	for ($i=0;$i<$length;$i++){ // loop and create password
		$ret_str .= substr($salt, mt_rand() % strlen($salt), 1);
	}
	return $ret_str;
}


//makes a ship using the parts specified in $ship_parts (array), ship_owner (also array)
//returns id of ship inserted.
function make_ship ($ship_parts, $ship_owner) {
	global $db_name;
	dbn("insert into ${db_name}_ships (ship_name, login_id, clan_id, shipclass, class_name, class_name_abbr, fighters, max_fighters, max_shields, armour, max_armour, cargo_bays, mine_rate_metal, mine_rate_fuel, config, size, upgrade_slots, move_turn_cost, point_value, num_ot, num_dt, num_pc, num_ew) values('".mysql_real_escape_string(trim((string)$ship_parts['ship_name']))."', '$ship_owner[login_id]', '$ship_owner[clan_id]', '$ship_parts[type_id]', '$ship_parts[name]', '$ship_parts[class_abbr]', '$ship_parts[fighters]', '$ship_parts[max_fighters]', '$ship_parts[max_shields]', '$ship_parts[max_armour]', '$ship_parts[max_armour]', '$ship_parts[cargo_bays]', '$ship_parts[mine_rate_metal]', '$ship_parts[mine_rate_fuel]', '$ship_parts[config]', '$ship_parts[size]', '$ship_parts[upgrade_slots]', '$ship_parts[move_turn_cost]', '$ship_parts[point_value]', '$ship_parts[num_ot]', '$ship_parts[num_dt]', '$ship_parts[num_pc]', '$ship_parts[num_ew]')");
	return mysql_insert_id();
}


//a function that will give a player the first ship.
function give_first_ship($login_id, $clan_id, $s_name = "Un-Named"){
	global $GAME_VARS;
	//create user's first ship
	$ship_stats = load_ship_types($GAME_VARS['start_ship']);

	//ensure valid name
	$ship_stats['ship_name'] = correct_name($s_name);

	//create array with owner details in
	$ship_owner = array('login_id' => $login_id, 'clan_id' => 0);

	//make the ship
	return make_ship($ship_stats, $ship_owner);
}


//function that will create an options list.
//allow_selection - dictates if html form for choosing, or HTML for just viewing
//present_values - array containing the present values for the options
function list_options($allow_selection, $present_values){
	$ret_str = "";

	//using already called db query, as this function prints from admin vars and player vars.
	while($gen_options = dbr2(1)){

		//radio boxes.
		if(preg_match("/ &&& /", $gen_options['description'])){
			$ct = 0;
			$desc_vars = preg_split("/ &&& /", $gen_options['description']);
			$ret_str .= "\n<p /><table border=3 cellspacing=1 width=450>";
			$ret_str .= "\n<tr bgcolor='#333333'>\n<td colspan=2><b><font color='#AAAAEE'>$gen_options[name]</font></b></td></tr><tr bgcolor='#555555'>\n<td colspan=2>$desc_vars[0]</td></tr>\n";

			$checked = array_fill(0, 15,"");
			$checked[$present_values[$gen_options['name']]] = 'checked';

			$sec_count = 1; //used to extract definitions from defs array (arrays start at 0).

			//loop through the possible selections for each option
			for($ct=$gen_options['min']; $ct <= $gen_options['max']; $ct++){
				if($allow_selection == 1){
					$ret_str .= "\n<tr><td width=50><input type=radio name='{$gen_options['name']}' value='$ct'{$checked[$ct]} /></td><td width=400>".$desc_vars[$sec_count]."</td></tr>";
				} else {
					$ret_str .= "\n<tr><td width=50>$checked[$ct]&nbsp;</td><td width=400>$desc_vars[$sec_count]</td></tr>";
				}
				$sec_count ++;
			}

			$ret_str .= "\n</table>\n";

		//numerical choice
		} else {
			$ret_str .= "\n<p /><table border=3 cellspacing=1 width=450>\n<tr bgcolor='#333333'>\n<td width='250'><b><font color='#AAAAEE'>$gen_options[name]</font></b></td>\n<td align='right' width=200>";

			if($allow_selection == 1){ //selectable
				$ret_str .= "Min: <b>$gen_options[min]</b>, Max: <b>$gen_options[max]</b> - <input type='text' name='$gen_options[name]' size='4' value='".$present_values[$gen_options['name']]."' />";

			} else {//for viewing only
				$ret_str .= "= <b>".$present_values[$gen_options['name']]."</b>";
			}
			$ret_str .= "\n</td></tr>\n<tr bgcolor='#555555'>\n<td colspan='2'>\n<blockquote>$gen_options[description]\n</blockquote>\n</td></tr></table>\n";
		}
	}
	return $ret_str;
}


//function that will load the db vars from the DB and place into GAME_VARS.
function load_admin_vars (){
	global $GAME_VARS, $db_name;
	$GAME_VARS = array();
	db2("select ${db_name}_value as value, name from se_db_vars");
	while($result = dbr2(1)){
		$GAME_VARS[$result['name']] = $result['value'];
	}
}


//this function will parse a XML file, returning an array which has valid HTML in it (html within the xml file must have square brackets rather than angle brackets if it is to be converted by this function).
//resulting array has data-structure similar to that of the source XML file.
//written by Moriarty on 20/May/04
//p.s. i HATE xml parsing! :'{}
function load_xml ($file, $error_log = 0){

	//getting error log, which doesn't have the global tags on it. so we have to add them manually.
	if($error_log == 1){
		$xml_file_load = "<all_contents>".file_get_contents($file)."</all_contents>";
	} else {
		$xml_file_load = file_get_contents($file);
	}

	$xml_stream_handle = xml_parser_create('iso-8859-1');
	//stay in case given.
	xml_parser_set_option($xml_stream_handle, XML_OPTION_CASE_FOLDING, 0);
	xml_parse_into_struct($xml_stream_handle, $xml_file_load, $xml_values);

//xml debugging stuff
//echo xml_error_string(xml_get_error_code($xml_stream_handle));
//echo xml_get_current_line_number($xml_stream_handle);

	xml_parser_free($xml_stream_handle);
	$results = array();
	$present_1_deep = "";
	$present_2_deep = "";
	foreach($xml_values as $content){

		//ignore all encompassing level 1 thing, as well as white-space.
		if($content['level'] == 1 || $content['type'] == 'cdata'){
			continue 1;
		}

		//is an opening tag, that encompases data, so we get ready to make it an element in results array.
		if($content['type'] == 'open' && $content['level'] == 2){
			$present_1_deep = $content['tag'];

		//is the title for this particular sub-entry.
		} elseif($content['tag'] == 'title') {
			//make ready for becoming a header, and remember for later data in this subentry
			$present_2_deep = str_replace(" ", "_", $content['value']);

			//insert it's contents into the array.
			if(!empty($content['value'])){
				$content['value'] = preg_replace("/(\[|\])/e", "'\\1' == '[' ? '<' : '>'", $content['value']);
				$results[$present_1_deep][$present_2_deep] = array($content['tag'] => $content['value']);
			}

		//is some data. so best make save it in file array
		} elseif(!empty($content['value'])) {

			//replace any square brackets with angle bracket equivalent.
			$content['value'] = preg_replace("/(\[|\])/e", "'\\1' == '[' ? '<' : '>'", $content['value']);
			$results[$present_1_deep][$present_2_deep][$content['tag']] = $content['value'];
		}
	}
	return $results;
}

function check_allopass($recall, $auth) {
	if( trim($recall) == "" ) {
		// La variable RECALL est vide, renvoi de l'internaute
		// vers une page d'erreur
		return false;
	}
	// $recall contient le code d'accès
	$recall = urlencode($recall);

	// $auth doit contenir l'identifiant de VOTRE document
	$auth = urlencode($auth);

	/**
   * envoi de la requête vers le serveur AlloPass
   * dans la variable $r[0] on aura la réponse du serveur
   * dans la variable $r[1] on aura le code du pays d'appel de l'internaute
   * (FR,BE,UK,DE,CH,CA,LU,IT,ES,AT,...)
   * Dans le cas du multicode, on aura également $r[2],$r[3] etc...
   * contenant à chaque fois le résultat et le code pays.
   */

	$r = @file("http://www.allopass.com/check/vf.php4?CODE=$recall&AUTH=$auth");
	// on teste la réponse du serveur
	if( substr( $r[0],0,2 ) != "OK" ) {
		// Le serveur a répondu ERR ou NOK : l'accès est donc refusé
		return false;
	}
	return true;
}

function nfr($n, $dec=0)
{
	return number_format($n, $desc, ',', ' ');
}

function chechAutoLoginCookie() {
	if ( isset( $_COOKIE['astravires_auto_login'] ) ) {
		$login_id = end( explode( '|', $_COOKIE['astravires_auto_login'] ) );
		if ( is_numeric( $login_id ) && $login_id > 0 ) {
			db_connect();
			db( "select login_id, login_name, mdp from user_accounts where login_id = ".$login_id." and auto_login = 1 limit 1" );
			$p_user = dbr(1);

			$db_hash = md5( $p_user['login_id'] . $p_user['login_name'] . $p_user['mdp'] ) . '|' . $p_user['login_id'];
			if ( $db_hash == $_COOKIE['astravires_auto_login'] ) {
				return array( 'pseudo' => $p_user['login_name'], 'mdp' => $p_user['mdp'] );
			}

			return false;
		}

		return false;
	}

	return false;
}

// set the autologin cookie
function setAutoLoginCookie( $login_id, $login_name, $mdp ) {
	if ( isset( $_POST['auto_login'] ) && $_POST['auto_login'] && !isset( $_COOKIE['astravires_auto_login'] ) ) {
		dbn( "update user_accounts set auto_login = 1 where login_id = $login_id limit 1" );
		$hash = md5( $login_id . $login_name . $mdp ) . '|' . $login_id;
		$expire = time() + ( 7 * 24 * 60 * 60 );
		setcookie( 'astravires_auto_login', $hash, $expire );
	}
	elseif ( isset( $_COOKIE['astravires_auto_login'] ) && isset( $_POST['submit'] ) && !isset( $_POST['auto_login'] ) )
	{
		dbn( "update user_accounts set auto_login = 0 where login_id = $login_id limit 1" );
		setcookie( 'astravires_auto_login', '', time() - 3600 );
	}
}

// check if the user is in holiday mode
function checkHolidayMode( $login_id = null ) {
	global $p_user;

	$user = ( $login_id != null ) ? $login_id : $p_user['login_id'];
	db( "SELECT COUNT(*) AS cnt FROM users_holiday_mode WHERE login_id = " . $user . " AND mode = 1 LIMIT 1" );
	$res = dbr(1);

	return $res['cnt'];
}

// check if the user has used the holiday option the last 24 hours
function checkHoliday24h() {
	global $p_user;
	db( "SELECT TIMEDIFF( NOW(), last_activated ) AS diff FROM users_holiday_mode WHERE login_id = " . $p_user['login_id'] . " AND mode = 0 LIMIT 1" );
	$res = dbr(1);
	$diff = (int)$res['diff']; // get only the hours because the format is 00:00:00

	return ( $diff >= 24 || $res['diff'] == NULL ) ? true : false; // return true if last activation is more than 24 hours or if the user hasn't used the option yet
}

function setHolidayMode( $mode ) {
	global $p_user;

	db( "SELECT COUNT(*) AS cnt FROM users_holiday_mode WHERE login_id = " . $p_user['login_id'] . " LIMIT 1" );
	$res = dbr(1);

	switch ( $mode ) {
		case 1:
			if ( $res['cnt'] == 1 )
				$sql = "UPDATE users_holiday_mode SET mode = 1, last_activated = NOW() WHERE login_id = " . $p_user['login_id'] . " LIMIT 1";
			else
				$sql = "INSERT INTO users_holiday_mode ( login_id, mode, last_activated ) VALUES ( '".$p_user['login_id']."', '".$mode."', NOW() )";
			break;
		case 0:
			$sql = "UPDATE users_holiday_mode SET mode = 0 WHERE login_id = " . $p_user['login_id'] . " LIMIT 1";
			break;
		default: return;
	}

	db( $sql );
}

//génère automatiquement un nom de vaisseau aléatoire

function generateVesselName() {
	db_connect();

	db("SELECT content FROM vessels WHERE name = 'Grecs'");

	$serialized_greeks = dbr(1);

	db("SELECT content FROM vessels WHERE name = 'Stars'");

	$serialized_stars = dbr(1);

	//la liste de noms est désérialisée
	$greeks = unserialize($serialized_greeks['content']);
	$stars = unserialize($serialized_stars['content']);


	$greek = array_rand($greeks);
	$star = array_rand($stars);

	$name = $greeks[$greek] . " " . $stars[$star];


	//si un vaisseau a déjà un nom identique, incrémenter le numéro
	$counter = "2";
	$new_name = $name;

	if(shipExists($new_name)) {
		while(shipExists($new_name)) {
		$new_name = $name;
		$new_name = $new_name . " ". roman($counter);
		$counter = $counter + 1;
		}
	}


	return $new_name;
}


//vérifie l'existence d'un vaisseau dans la base
function shipExists($ship_name) {
	db_connect();

	global $db_name;
    if($db_name == "")
		$db_name = "bes";
	$query = "SELECT * FROM ${db_name}_ships where ship_name =  '$ship_name' ";
	db($query);
	$ship = dbr(1);
	if(!$ship)
		return false;
	else
		return true;
}

//convertit un chiffre arabe en chiffre romain.
//trouvé sur google.

function roman($arabic)
{
	$ones = Array("", "I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX");
	$tens = Array("", "X", "XX", "XXX", "XL", "L", "LX", "LXX", "LXXX", "XC");
	$hundreds = Array("", "C", "CC", "CCC", "CD", "D", "DC", "DCC", "DCCC", "CM");
	$thousands = Array("", "M", "MM", "MMM", "MMMM");

	if ($arabic > 4999)
	{
		// For large numbers (five thousand and above), a bar is placed above a base numeral to indicate multiplication by 1000.
		// Since it is not possible to illustrate this in plain ASCII, this function will refuse to convert numbers above 4999.
		die("Cannot represent numbers larger than 4999 in plain ASCII.");
	}
	elseif ($arabic == 0)
	{
		// About 725, Bede or one of his colleagues used the letter N, the initial of nullae,
		// in a table of epacts, all written in Roman numerals, to indicate zero.
		return "N";
	}
	else
	{
		$roman = $thousands[($arabic - fmod($arabic, 1000)) / 1000];
		$arabic = fmod($arabic, 1000);
		$roman .= $hundreds[($arabic - fmod($arabic, 100)) / 100];
		$arabic = fmod($arabic, 100);
		$roman .= $tens[($arabic - fmod($arabic, 10)) / 10];
		$arabic = fmod($arabic, 10);
		$roman .= $ones[($arabic - fmod($arabic, 1)) / 1];
		$arabic = fmod($arabic, 1);

		return $roman;
	}
}

function get_facebook_cookie($app_id = YOUR_APP_ID, $app_secret = YOUR_APP_SECRET) {
	$args = array();
	parse_str(trim($_COOKIE['fbs_' . $app_id], '\\"'), $args);
	ksort($args);
	$payload = '';
	foreach ($args as $key => $value)
		if ($key != 'sig')
			$payload .= $key . '=' . $value;
	if (md5($payload . $app_secret) != $args['sig'])
		return null;
	return $args;
}

function get_facebook_user(){
	$cookie = get_facebook_cookie();

	if ($cookie)
	{
		$user = json_decode(file_get_contents('https://graph.facebook.com/me?access_token='.$cookie['access_token']));
		if (!empty($user->name) && !empty($user->email))
			return $user;
		else
			return FALSE;
	}
	else
		return FALSE;
}


function fb_wallpost_wosdk_api($title, $caption, $description = "Astra Vires est un jeu gratuit jouable par navigateur de stratégie et de conquête spatiale. Démarrez avec un vaisseau, explorez la galaxie et devenez un chef militaire redoutable !", $cookie = ''){
	if (empty($cookie))
	{//post for logged in user
		$cookie = get_facebook_cookie();
		$cookie = $cookie['access_token'];
	}
	$attachment = '
	{
		"name": "'.utf8_encode($title).'",
		"href": "http://www.astravires.fr",
		"caption": "'.utf8_encode($caption).'",
		"description": "'.utf8_encode($description).'",
		"media": [
	    {
	        "type": "image",
	        "src": "http://www.astravires.fr/images/logofb.jpg",
	        "href": "http://www.astravires.fr"
	    }]
	}';

	$action_links = array( array('text' => utf8_encode('Jouer à Astra Vires !'), 'href' => 'http://www.astravires.fr'));
	$action_links = json_encode($action_links);
	$action_links = urlencode($action_links);
	$attachment = urlencode($attachment);

	if ( ($cookie !="") && ($title != "") && ($caption != "")  ) {
		$result = json_decode(file_get_contents("https://api.facebook.com/method/stream.publish?access_token=".$cookie."&attachment=$attachment&action_links=$action_links"));
		return $result;
	} else {
		return 0;
	}
}

$fbuser = get_facebook_user();
?>
