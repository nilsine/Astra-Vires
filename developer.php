<?php
/*******************
* This page allows the server operator to do god-like things.
* Created By Moriarty
* Last audited: 24/5/04 by Moriarty
*********************/

error_reporting(E_ALL);
require_once("user.inc.php");

//only the server admin may use this page!
if($user['login_id'] != OWNER_ID || !isset($user['login_id']) || OWNER_ID == 0){
	print_page("Error","Error");
}

$out_str = "";


//developer sends a message
if(isset($_REQUEST['send_message'])){
	if(empty($_POST['text']) || !isset($_POST['target'])){
		$mess_str = "\n<select name='target'>";
		$mess_str .= "\n<option value='-1'> All Admins";
		$mess_str .= "\n<option value='-2'> All Players in all games";
		$mess_str .= "\n<option value='-3'> All Game forums";

		//loop through the games.
		db("select game_id, name from se_games");
		while($dest = dbr(1)){
			$mess_str .= "\n<option value='$dest[game_id]'> Players in '$dest[name]'";
		}
		$mess_str .= "\n</select><br />";

		get_var('Send Message','developer.php',"Select the group of people you would like to send the message to:<br /><br /> $mess_str<br /><br />Enter your message below (note: HTML is useable. Message codes are not):",'text',"");

	//one of the pre-defined destinations.
	} else{
		$target = (int)$_POST['target'];
		if($target == -1){
			$send_to = "All the Admins";
		} elseif($target == -2){
			$send_to = "all the players in all the games";
		} elseif($target == -3){
			$send_to = "all the game forums";
		} else {
			$send_to = "all players";
		}

		db("select game_id, db_name from se_games");
		while($dest = dbr(1)){

			//message only to recipients of this one game, or all players in all games
			if(($target > 0 && $dest['game_id'] == $target) || $target == -2){
				$out_str .= "<p />".message_all_players($text,$dest['db_name'], $send_to,"<font color='lime'>The Server Operator</font>");

			} elseif($target == -1 || $target == -3){//all admins or all forums
				if($target == -1){
					$dest_id = 1;
					$extra_txt = mysql_escape_string("Message to <b class='b1'>All Admins</b> from <font color='lime'>The Server Operator</font>:<p /> ".$text);
				} else {
					$dest_id = -1;
					$extra_txt = mysql_escape_string("Message to <b class='b1'>All Game Forums</b> from <font color='lime'>The Server Operator</font>:<p /> ".$text);
				}
				dbn("insert into {$dest['db_name']}_messages (timestamp,sender_name, sender_id, login_id, text) values(".time().",'$user[login_name]','$user[login_id]','$dest_id','$extra_txt')");
			}
		}
	}

//empty the error log.
} elseif(isset($_REQUEST['empty_log'])) {
	if(!isset($_POST['sure'])){
		get_var('Erase log',"developer.php","Are you Sure you want to erase the error log?",'sure','yes');
	} else {
		write_to_error_log("", "", 1);
	}


//empty the error log.
} elseif(isset($_GET['show_log'])) {

	$results = load_xml("$directories[games]/$log_name", 1);

	$header_str = "";
	$content_str = "";

	$b_link = "<br /><br /><a href='#top'>Top</a><br />";

	//loop through the entries
	foreach($results as $key => $content_array){

		//contains the time of last emptying.
		if(isset($content_array[$key]['last_emptied'])){
			$header_str = "<a name='top'>Last Emptied: <b>".$content_array[$key]['last_emptied']."</b></a> - <a href='$_SERVER[PHP_SELF]?empty_log=1'>Empty Now</a><p />\n";

		//must be an error report then
		} else {
			//state username is undefined if none was taken.
			if(!isset($content_array['']['user'])){
				$content_array['']['user'] = "Undefined";
			}

			//get the time that the error was logged.
			$reported_time = date("D dS M - h:m:s", str_replace("t_", "", $key));

			//set up for this entry
			$header_str .= "\n<a href='#$key'>".$content_array['']['file']."</a> >>> ".$content_array['']['user']." <<< $reported_time<br />";
			$content_str .= "\n<p /><br /><a name='$key'><b>$reported_time</b></a>".make_table(array("Area","Details"), "width='90%'");

			//loop through elements, and print them
			foreach($content_array[''] as $sub_title => $sub_content){

				//make timestamp into date
				if($sub_title == 'timestamp'){
					$sub_content = date("D dS M - h:m:s", $sub_content);

				//check to see if there are any entries in this dump.
				} elseif(strpos($sub_content, "array(0) {") === 0){
					$sub_content = "Empty";

				//if it is the error message, use blockquote to print it.
				} elseif($sub_title == 'error_message') {
					$sub_content = "<blockquote>$sub_content</blockquote>";

				//will probably need to <pre> it if it's anything else
				} else {
					$sub_content = "<pre>$sub_content</pre>";
				}
				$content_str .= quick_row($sub_title, $sub_content);
			}

			$content_str .= "</table> $b_link<hr width='50%' />";
		}
	}

	print_page("Error Log", $header_str.$content_str);
	exit();




//run the maints.
} elseif(isset($_GET['run_maints'])) {
	echo "Running Maints<p /><hr><br />";

	//need to switch to the maints dir, or the require's in the maints all break. :)
	chdir($directories['games']);

	//set so outputs to screen.
	$_GET['dir_req'] = 1;
	include_once("run_maints.php");
	exit();



//Server wide information listing

//show stats for the server
} elseif(isset($_GET['server_details'])) {

	$out_str .= "<p /><b class='b1'>Generic Server Information</b>";
	db("select count(login_id),sum(login_count),sum(num_games_joined), sum(page_views) from user_accounts where login_id > 5");
	$serv1 = dbr();

	db("select count(game_id) from se_games where status = 1");
	$serv2 = dbr();

	$out_str .= make_table(array("",""));
	$out_str .= quick_row("Total Games:","$serv2[0]");
	$out_str .= quick_row("Total Accounts:","$serv1[0]");
	$out_str .= quick_row("Total Logins:","$serv1[1]");
	$out_str .= quick_row("Total page views:","$serv1[3]");
	$out_str .= quick_row("Avg. Logins/Player:",number_format($serv1[1]/$serv1[0],2));
	$out_str .= quick_row("Avg. Games Joined/Player:",number_format($serv1[2]/$serv1[0],2));
	$out_str .= quick_row("Avg. Page Views/Player:",number_format($serv1[3]/$serv1[0],2));
	$out_str .= "</table><br /><br /><br />";

	$out_str .= "<b class='b1'>MySQL Server Details</b><br />".preg_replace("/  /","<br />", mysql_stat($database_link));

	//work out the root SE server directory
	$se_root_dir = str_replace("developer.php", "", $_SERVER['SCRIPT_FILENAME']);

	$out_str .= "<p /><b class='b1'>Disk Usage/Space</b><br />HardDisk Capacity: ".number_format(disk_total_space($se_root_dir) / 1024 / 1024)." MBytes";
	$out_str .= "<br />HardDisk Space Free: ".number_format(disk_free_space($se_root_dir) / 1024 / 1024)." MBytes";

	//open directory stream so can work out size of SE installation.
	$dir_array = array($se_root_dir);
	$file_size = 0;

	//recursively look through the directories within the SE structure.
	while (list(, $dir_value) = each ($dir_array)) {

		//change directory to next directory in listings
		chdir($dir_value);
		$dir_stream = opendir($dir_value); 

		//loop through the contents of the directory to get individual file-sizes
		while (false !== ($file = readdir($dir_stream))) {

			//if it is a directory, add to the array (make sure not . or ..) so can look into it later
			if(is_dir($file) && $file != "." && $file != ".."){
				$dir_array[] = $dir_value.$file."/";

			} elseif(is_file($file)) {
				$file_size += filesize($file);
			}
		}
		closedir($dir_stream);
	}
	$out_str .= "<p>SE File Installation size: ".number_format($file_size / 1024)." KBytes";


	print_page("Server Details", $out_str);
//List information about each of the games.
} elseif(isset($_GET['game_details'])){

	$out_str .= "<p />Game Details:";
	//loop through games
	db2("select * from se_games order by name");
	while ($game = dbr2()){
		$db_name = $game['db_name'];
		db("select count(login_id),sum(cash),sum(turns),sum(turns_run),sum(ships_killed), sum(fighters_lost) as lost_fighters, sum(fighters_killed) as killed_fighters from ${db_name}_users where login_id > 5");
		$ct = dbr();
		db("select count(login_id) from ${db_name}_users where ship_id != 1 && login_id > 5");
		$ct2 = dbr();
		db("select count(login_id),sum(fighters) from ${db_name}_ships where login_id > 5");
		$ct3 = dbr();
		db("select count(planet_id),sum(fighters),sum(colon),sum(elect),sum(metal),sum(fuel) from ${db_name}_planets where login_id != 1");
		$ct4 = dbr();
		db("select count(distinct clan_id),count(login_id) from ${db_name}_users where clan_id > 0 && login_id > 5");
		$ct5 = dbr();
		db("select count(*) from ${db_name}_news");
		$ct6 = dbr();
		db("select count(message_id) from ${db_name}_messages where login_id = -1");
		$forum_posts = dbr();
		db("select count(message_id) from ${db_name}_messages where login_id > 1");
		$player_mess = dbr();
		db("select count(message_id) from ${db_name}_messages where login_id = -5");
		$clan_forum_posts = dbr();
		$out_str .= "<table border=0 cellpadding=5><tr valign=top><td colspan=3>";
		$out_str .= make_table(array("",""));
		$out_str .= quick_row("Game Name:","$game[name]");
		$out_str .= quick_row("Game ID:","$game[game_id]");
		$out_str .= quick_row("db_name: ","$game[db_name]");
		$out_str .= quick_row("Paused: ","$game[paused]");
		$out_str .= quick_row("Status: ","$game[status]");
		$out_str .= quick_row("","");
		$out_str .= quick_row("","");
		$out_str .= quick_row("Admin Name:","$game[admin_name]");
		$out_str .= quick_row("Description:","$game[description]");
		$out_str .= quick_row("Intro Message:","$game[intro_message]");
		$out_str .= quick_row("Num Stars:","$game[num_stars]");
		$out_str .= quick_row("","");
		$out_str .= "</table></td></tr><tr><td>";

		$out_str .= make_table(array("",""));
		$out_str .= quick_row("News Posts","$ct6[0]");
		$out_str .= quick_row("Forum Posts","$forum_posts[0]");
		$out_str .= quick_row("Player Messages","$player_mess[0]");
		$out_str .= quick_row("Clan Forum Posts","$clan_forum_posts[0]");
		$out_str .= "</table><br />";

		$out_str .= make_table(array("",""));
		$out_str .= quick_row("Players","<b>".($ct[0])."</b>");
		$out_str .= quick_row("Players Alive",calc_perc($ct2[0],$ct[0]));
		$out_str .= quick_row("Cash",number_format($ct[1]));
		$out_str .= quick_row("Cash Average",number_format(round(($ct[1] * 100/$ct[0]) / 100)));
		$out_str .= quick_row("Turns",$ct[2]);
		$out_str .= quick_row("Turns Average",number_format($ct[2]/$ct[0]),2);
		$out_str .= quick_row("Turns Run",$ct[3]);
		$out_str .= quick_row("Turns Run Average",number_format($ct[3]/$ct[0]),2);
		$out_str .= "</table></td><td>";
		//new grid

		$out_str .= make_table(array("",""));
		$out_str .= quick_row("Ships","<b>$ct3[0]</b>");
		$out_str .= quick_row("Ships Average",round($ct3[0]/$ct[0]));
		$out_str .= quick_row("Fighters",$ct3[1]);
		$out_str .= quick_row("Avg. Fighters/Ship",round(($ct3[1] * 100/$ct3[0]) / 100));
		$out_str .= "</table><br />";

		$out_str .= make_table(array("",""));
		$out_str .= quick_row("Planets","<b>$ct4[0]</b>");
		$out_str .= quick_row("Planets Average",number_format($ct4[0]/$ct[0],3));
		$out_str .= quick_row("Planet Colonists","<b>$ct4[2]</b>");
		$out_str .= quick_row("Planet Metal","<b>$ct4[4]</b>");
		$out_str .= quick_row("Planet Fuel","<b>$ct4[5]</b>");
		$out_str .= quick_row("Planet Electronics","<b>$ct4[3]</b>");
		$out_str .= quick_row("Planet Fighters",$ct4[1]);
		if($ct4[1] > 0){
			$out_str .= quick_row("Avg. Fighters/Planet",number_format(($ct4[1] * 100/$ct4[0]) / 100,2));
		} else {
			$out_str .= quick_row("Fighters Average","0%)");
		}
		$out_str .= "</table></td><td>";
		//new grid

		$out_str .= make_table(array("",""));
		$out_str .= quick_row("Kills",$ct[4]);
		$out_str .= quick_row("Kills Average",round(($ct[4] * 100/$ct[0]) / 100));
		$out_str .= quick_row("Fighters Killed",$ct['killed_fighters']);
		$out_str .= quick_row("Fighters Killed Average",round(($ct['killed_fighters'] * 100/$ct[0]) / 100));
		$out_str .= quick_row("Fighters Lost",$ct['lost_fighters']);
		$out_str .= quick_row("Fighters Lost Average",round(($ct['lost_fighters'] * 100/$ct[0]) / 100));

		$out_str .= "</table><br />";
		$out_str .= make_table(array("",""));
		$out_str .= quick_row("Clans","<b>$ct5[0]</b>");
		$out_str .= quick_row("Total Clan <br />Membership",$ct5[1]);
		if($ct5[1] > 0){
			$out_str .= quick_row("Average Clan <br />Membership",round(($ct5[1] * 100/$ct5[0]) / 100));
		}
		$out_str .= "</table><br /><br />";
		$out_str .= "</table><br /><br />";
	}
	print_page("GameDetails", $out_str);

//optimise the DB tables.
}elseif(isset($_GET['optimise'])){
	$tables_str = "";
	$counter = 0;
	//select all tables from the DB
	$table_result = mysql_list_tables(DATABASE);
	while ($row = mysql_fetch_row($table_result)) {
		$tables_str .= " `$row[0]`, ";
		$counter++;
	}
	$tables_str = preg_replace("/, $/", "", $tables_str);
	dbn("OPTIMIZE TABLE $tables_str");
	$out_str .= "$counter tables were optimised in database <b class='b1'>".DATABASE."</b>";

//show php info
} elseif(isset($_GET['php_info'])){
	phpinfo();
	exit();

//post some server news
} elseif(isset($_REQUEST['post_s_news'])){

	//not given anything to post.
	if(empty($_POST['text'])){
		$out = "Enter the server news below.<p />HTML is Valid. message codes are not.<p /><form method='post' action='$_SERVER[PHP_SELF]' name='server_news'><input type='hidden' value='1' name='post_s_news' /><textarea name='text' COLS='60' ROWS='10'></textarea><br /><input type='submit' /></form>";
		print_page("Post Server news", $out);
	} else {
		$file_loc = "./$directories[includes]/server_news.inc.htm";
		if ($file_contents = file_get_contents($file_loc)){

			$date = date("h:i - M d, Y",time());
			$output_stuff = "\n<p /><TABLE BORDER='1' BORDERCOLOR='#000000' CELLSPACING='0' CELLPADDING='5'><TR><TD BGCOLOR='#333333'><B CLASS='b1'>$user[login_name]</B> - $date</TD></TR><TR><TD BGCOLOR='#333333'>".stripslashes($text)."</TD></TR></TABLE>\n\n".$file_contents;

			$file_stream = fopen($file_loc, "wb") or die("Unable to open file for writing");
			fwrite($file_stream, $output_stuff) or die("unable to write to file");
			fclose($file_stream);
			$out_str .= "Server news posted.";
		} else {
			$out_str .= "Unable to open file.";
		}
	}

}

#list all the dev options
$out_str .= "<p /><br />Server Functions";
$out_str .= "<br /><a href='$_SERVER[PHP_SELF]?server_details=1'>Server Details</a>";
$out_str .= "<br /><a href='$_SERVER[PHP_SELF]?game_details=1'>Game Details</a>";
$out_str .= "<br /><a href='$_SERVER[PHP_SELF]?run_maints=1'>Run Maints</a>";
$out_str .= "<br /><a href='graph.php'>Graphs</a>";
$out_str .= "<br /><a href='$_SERVER[PHP_SELF]?php_info=1' target='_blank'>PHP Info</a>";
$out_str .= "<br /><a href='$_SERVER[PHP_SELF]?optimise=1'>Optimise Tables</a>";

$out_str .= "<p />Communications";
$out_str .= "<br /><a href='$_SERVER[PHP_SELF]?send_message=1'>Message People</a>";
$out_str .= "<br /><a href='$_SERVER[PHP_SELF]?post_s_news=1'>Post Server News</a>";

$out_str .= "<p />Error Logging";
//get the size of the error log
$f_size = filesize($directories['games']."/".$log_name);

//if size is less than 100 bytes, we can assume it is empty of errors
if($f_size < 100){
	$f_size .= " Bytes - Empty";
} else {
	$f_size .= " Bytes - !!!Errors Reported!!!";
}
$out_str .= "<br /><a href='$_SERVER[PHP_SELF]?show_log=1'>Show error log</a> - $f_size";
$out_str .= "<br /><a href='$_SERVER[PHP_SELF]?empty_log=1'>Empty Error Log</a>";

print_page("Server Admin",$out_str);

?>