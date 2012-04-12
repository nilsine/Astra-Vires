<?php
require_once("user.inc.php");

//the number of news posts to show at once.
$news_posts_show = $user_options['news_back'];


$text = "";


//news search
if(isset($_POST['term']) && $_POST['term'] != "") {
	$rs = "<p /><a href='news.php'>".$st[914]."</a><br />";

	//fill the _POST array with stuff if it's empty
	$post_check = array('term' => "", 'admin' => "", 'attacking' => "", 'bomb' => "", 'clan' => "", 'game_status' => "", 'maint' => "", 'other' => "", 'player_status' => "", 'planet' => "", 'random_event' => "", 'ship' => "");

	//place new data into _POST array. the contents of _POST take precidence.
	$_POST = $_POST + $post_check;

	//THIS LOOp is needed so opera can remember what is turned on or not.
	foreach($_POST as $key => $val){
		if($val == "on"){
			$_POST[$key] = "checked='on'";
		}
	}

	search_the_db2($_POST['term'], $_POST['any_all']);
}


//fill the _POST array with stuff if it's empty
$post_check = array('term' => "", 'admin' => "checked='on'", 'attacking' => "checked='on'", 'bomb' => "checked='on'", 'clan' => "checked='on'", 'game_status' => "checked='on'", 'maint' => "checked='on'", 'other' => "checked='on'", 'player_status' => "checked='on'", 'planet' => "checked='on'", 'random_event' => "checked='on'", 'ship' => "checked='on'");

//place new data into _POST array. the contents of _POST take precidence.
$_POST = $_POST + $post_check;

/******************************* start of news search bar *****************************/

$text .= news_search_bar();

/******************************* end of news search bar *****************************/

db("select count(*) from ${db_name}_news");
$news_ents = dbr();

$text .= sprintf($st[915],$news_ents[0])."<p />";

if(isset($_GET['prev'])){
	$prev = (int)$_GET['prev'];
} else {
	$prev = 0;
}

//work out where the next set of posts will be
$next_set = $prev + $news_posts_show;

/************************************ work out back/forward links ***********************/

//there are more posts back at the start
if($prev > 0){
	$temp_num = $prev - $news_posts_show;
	$temp_num2 = $prev; //necesssary, as $prev is used later in it's old state.

	if($temp_num < 0){ //make sure not going too low
		$temp_num = 0;
		$temp_num2 = $news_posts_show;
	}

	$link_str_back = "<-- <a href='news.php?prev=$temp_num'>".sprintf($st[918], $temp_num, $temp_num2)."</a>|";

//at the start of the news
} else {
	$link_str_back = "";
}

//if there are more news entries, provide a link to them
if($news_ents[0] > $next_set) {
	$temp_num = $next_set + $news_posts_show;
	$link_str_for = "|<a href='news.php?prev=$next_set'>".sprintf($st[918], $next_set, ($next_set + $news_posts_show))." --></a>";

//there are no more entries
} else {
	$link_str_for = "";
}

/************************************ end back/forward links ***********************/


$text .= $link_str_back.$link_str_for."<p />".sprintf($st[919],$prev,$next_set).".<br />";


db("select * from ${db_name}_news where topic_set != 'random_event' order by timestamp desc LIMIT $prev, $news_posts_show");

$text .= make_table(array("",""));
$news = dbr(1);
while($news) {
	$text .= quick_row("<b>".date("M d - H:i",$news['timestamp']),stripslashes($news['headline']));
	$news = dbr(1);
}

$text .= "</table><br />".$link_str_back.$link_str_for;

print_page($cw['news'],$text);


//function that returns a string with the search bar in it.
function news_search_bar(){
	global $GAME_VARS, $any_all, $cw, $st;


	//needed to remember the "any_all" radiobox status
	$any_all = array(1 => '', 2 => '');
	if(isset($_POST['any_all']) && $_POST['any_all'] == 2){
		$any_all[2] = "checked='on'";
	} else {
		$any_all[1] = "checked='on'";
	}

	$text = "";

	//show the search bar at the top of the news page.
	$top_row_search = array("<input type='text' name='term' size='20' value='$_POST[term]'/>","<input type='radio' name='any_all' value='1' $any_all[1]> <b>".$cw['all']."</b>","<input type='checkbox' name = 'admin' $_POST[admin]/>".$cw['admin'],"<input type='checkbox' name = 'attacking' $_POST[attacking]/> ".$cw['attacks'],"<input type='checkbox' name = 'bomb'  $_POST[bomb]/> ".$cw['bombs'], "<input type='checkbox' name = 'clan'  $_POST[clan]/> ".$cw['clans'], "<input type='checkbox' name = 'game_status' $_POST[game_status]/> ".$cw['game_status']);

	$middle_row_search = array("<input type='submit' value=Search />","<input type='radio' name='any_all' value='2' $any_all[2]> <b>".$cw['any']."</b>","<input type='checkbox' name = 'maint'  $_POST[maint]/> ".$cw['maints'], "<input type='checkbox' name = 'other' $_POST[other]/> ".$cw['other'], "<input type='checkbox' name = 'player_status'  $_POST[player_status]/> ".$cw['player_status'], "<input type='checkbox' name = 'planet' $_POST[planet]/> ".$cw['planets'], "<input type='checkbox' name = 'random_event' $_POST[random_event]/> ".$cw['random_events'], "<input type='checkbox' name = 'ship'  $_POST[ship]/> ".$cw['ships']);

	//don't show random_event search if there are no random events in the universe
	if($GAME_VARS['random_events'] == 0){
		unset($middle_row_search[6]);
	}


	$text .= $st[920];
	$text .= "<FORM method='POST' action='news.php'>";

	$text .= make_table($top_row_search);
	$text .= make_row($middle_row_search);
	$text .= "</table>";

	//'admin', 'attacking', 'bomb', 'clan', 'game_status', 'maint', 'other', 'player_status', 'planet', 'random_event', 'ship'

	$text .= "</form><p />";
	return $text;
}


//new search function for searching the news
function search_the_db2($orig_search, $all_any){
	global $db_name, $cw, $st;

	//initialise an array containing topic headings
	$topic_array = array('admin' => 0, 'attacking' => 0, 'bomb' => 0, 'clan' => 0, 'game_status' => 0, 'maint' => 0, 'other' => 0, 'player_status' => 0, 'planet' => 0, 'random_event' => 0, 'ship' => 0);

	//make sure doesn't contain nasties, or mysql REGEXP trip-ups
	$term = trim(mysql_escape_string(preg_replace("/(\^|\\$|\.|\*|\+|\?|\||\(|\)|\{|\}|\,|\=|\:)/", "", $orig_search)));


	$keywords = preg_split ("/\s/", $term);

	//create the sql query
	$sql_query = ""; //clear query text


	//want to search for all of the relevent terms
	if($all_any == 1){
		$operator = " &&";
		$text_any_all = $cw['all'];

	} else { //searching for any terms.
		$operator = " ||";
		$text_any_all = $cw['any'];
	}

	//loop through keywords
	foreach($keywords as $value){

		//add to sql_query text.
		$sql_query .= "$operator headline REGEXP '$value'";
	}

	//get rid of leading operator, place surrounding brackets around it
	$sql_query = "(".preg_replace("/^".preg_quote($operator)." /", "", $sql_query).") && ( ";


	//deal with the topics
	$counter = 0;
	$topic_sql_q = "";
	foreach($topic_array as $key =>$val){
		if(!empty($_POST[$key])){
			$topic_sql_q .= " || topic_set LIKE '$key'";
			$counter ++;
		}
	}

	//make sure at least some topics were selected
	if($counter == 0){
		print_page($st[921], $st[922]);
	}

	//get rid of lead || and add a trailing bracket )
	$sql_query .= preg_replace("/^ \|\| /", "", $topic_sql_q).")";


	$t_str = "";
	$counter = 0;

	db("select timestamp, headline from ${db_name}_news where $sql_query");
	while($results = dbr(1)){
		$counter ++;
		$t_str .= quick_row("<b>".date("M d - H:i",$results['timestamp']),stripslashes($results['headline']));
	}

	$text = "<a href='$_SERVER[PHP_SELF]'>".$st[923]."</a><p /><b>$counter</b> ".sprintf($st[924],$orig_search,$text_any_all).".";

	//if there were results, print a nice page.
	if($counter != 0){
		$text .= "<p />".make_table(array("","")).$t_str."</table>";
	}

	print_page($st[920], news_search_bar().$text);
}

?>
