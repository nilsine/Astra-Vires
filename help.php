<?php
/********************
* Allows the player to learn how to play the game
* Last Major re-write: 24/May/04 by Moriarty
********************/


//if popup, won't need user.inc
//also sets bottom link to whatever is suitable.
if(isset($_GET['popup'])){
	$popup = true;
	require_once("common.inc.php");
//	$b_link = "<p><center><a href=\"javascript:window.close()\">".$cw['close_window']."</a></center>";

} else {
	$popup = false;
	require_once("user.inc.php");
	$b_link = "<br /><br /><a href='#top'>".$cw['top']."</a><br />";
}

//empty $rs.
$rs = "";

if(empty($db_name) && !empty($_GET['db_name'])){
	$db_name = mysql_escape_string((string)$_GET['db_name']);
}
if(!empty($db_name)){
	require_once("$directories[includes]/${db_name}_vars.inc.php");
}

//set $topic and $sub_topic
$topic = isset($_GET['topic']) ? $_GET['topic'] : "Index";
$sub_topic = isset($_GET['sub_topic']) ? $_GET['sub_topic'] : "";

$out_str = "";

//Connect to the database
db_connect();


/************
* Game Variable Listings
*************/
if(isset($_GET['list_vars'])) {
	$help_type = $cw['game_variables'];

	if(!isset($db_name)){
		$out_str .= $st[811];
	} elseif($GAME_VARS['admin_var_show'] == 0) { 
		$out_str .= $st[812];
	} else {
		load_admin_vars();

		//this line required as list_options uses this for it's v listing.
		db2("select name, min, max, description from se_db_vars order by name");
		$out_str .= list_options(0, $GAME_VARS);
	}


/************
* Stories
*************/

} elseif(isset($_GET['story'])) {

	$help_type = $cw['game_stories'];

	//load stories
	$results = load_xml("$directories[includes]/stories.xml");

	$header_str = "";
	$content_str = "";

	 //list stories from arrray.
	foreach($results['story'] as $key => $stories_array){
		$header_str .= "\n<a href='#$key'>$stories_array[title]</a><br />";
		$content_str .= "\n<a name='$key'><center><b>$stories_array[title]</b></center></a><br>$stories_array[content] <p />".$cw['written_by']." <b class=b1>$stories_array[author]</b><br />".$cw['dated']." <b>$stories_array[date]</b><p /><a href='#top'>".$cw['top']."</a>";
	}

	$out_str .= "<h3><b>".$cw['solar_empire_stories']."</b></h3>".$cw['list_of_stories'].":<br /><br />".$header_str."<br /><br />".$content_str;


/************
* Ship Listing
*************/

} elseif(isset($_GET['ship_info'])) {
	$shipno = (int)$_GET['ship_info'];


	//string that is used for regular table for ships. is different for popups.
	$table_str = "<p />".make_table(array("",""),"WIDTH=75%");

	if($shipno < 0) {//list stats of all ships:
		//will load the selected ship details automatically.
		$ship_types = load_ship_types($shipno);
		$out_str .= "<h3><b>".$cw['ship_listing']."</b></h3>".$st[813];
		if($shipno == -1){
			 $out_str .= " ".$st[814]."<p />";
		} elseif($shipno==-2){
			$out_str .= " ".$st[815]."<p />";
		}
		$help_type = $cw['complete_ship_listing'];

	//list stats for a selected ship
	} else {
		$ship_types[] = load_ship_types($shipno);
		if($popup){//popup needs different table
			$table_str = "<br /><center><table width=250 height=250 cellspacing=0 cellpadding=3 border=1><tr><td colspan=2 align='center' bgcolor='#555555'>";
		}
		$help_type = $cw['ship_details'];
	}

	//loop through all ships
	foreach($ship_types as $ship_class => $ship_stats){
		if($ship_class == 1){ //skip ship type 1
			continue 1;
		}

		//list selected ships only
		if((($ship_stats['tcost'] > 0 && $shipno == -2) || ($ship_stats['tcost'] <= 0 && $shipno == -1)) || $shipno > 1){
			$ship_stats['cost'] = number_format($ship_stats['cost']);
			$ship_stats['tcost'] = number_format($ship_stats['tcost']);
			$out_str .= $table_str; //popup needs different table made
			$img_txt = "<tr><td colspan=2><center><a href=./images/ships/ship_{$ship_stats['type_id']}.jpg target='_blank'><img border=0 height=120 width=160 src='./images/ships/ship_${ship_stats['type_id']}_tn.jpg' /></a></center></td></tr>";
			$out_str .= "<tr><td colspan=2 align='center' bgcolor='#555555'><b>$ship_stats[name]</b> ($ship_stats[class_abbr]) </td></tr>$img_txt";
			$out_str .= quick_row("<b>".$cw['size']."</b>",discern_size($ship_stats['size']));
			$out_str .= quick_row("<b>".$cw['type']."</b>","$ship_stats[type]");
			$out_str .= quick_row("<b>".$cw['fighters']."</b>","$ship_stats[fighters]/$ship_stats[max_fighters]");
			$out_str .= quick_row("<b>".$cw['max_shields']."</b>","$ship_stats[max_shields]");
			$out_str .= quick_row("<b>".$cw['armour']."</b>","$ship_stats[max_armour]");
			$out_str .= quick_row("<b>".$cw['cargo_bays']."</b>","$ship_stats[cargo_bays]");
			if($GAME_VARS['alternate_play_1'] == 1){
				$out_str .= quick_row("<b>".$cw['mining_rate'].": ".$cw['metal']."</b>","$ship_stats[mine_rate_metal]");
				$out_str .= quick_row("<b>".$cw['mining_rate'].": ".$cw['fuel']."</b>","$ship_stats[mine_rate_fuel]");
			} else {
				$quick_maths = $ship_stats['mine_rate_metal'] + $ship_stats['mine_rate_fuel'];
				$out_str .= quick_row("<b>".$cw['mining_rate']."</b>","$quick_maths");
			}
			if($GAME_VARS['ship_warp_cost'] < 0){
				$out_str .= quick_row("<b>".$cw['move_cost']." (</b>".$cw['turns']."<b>)</b>","$ship_stats[move_turn_cost]");
			}
			if (!$ship_stats['config']) {
				$out_str .= quick_row("<b>".$cw['specials']."</b>",$cw['none']);
			} else {
				$out_str .= quick_row("<b>".$cw['specials']."</b>",config_list(0, $ship_stats['config']));
			}
			$out_str .= quick_row("<b>".$cw['upgrade_pods']."</b>","$ship_stats[upgrade_slots]");
			$out_str .= quick_row("<b>".$cw['description']."</b>","$ship_stats[descr]");
			$out_str .= quick_row("<b>".$cw['cost']."</b>","$ship_stats[cost]");
			if($ship_stats['type_id'] >= 300){
				$out_str .= quick_row("<b>".$cw['tech_support_cost']."</b>","$ship_stats[tcost]");
			}
			$out_str .= "</table><br />";
		}
	} //end ship listing loop


	$out_str .= "<p /><br /><br /><br />";
	//don't show the specials link if in a pop-up window.
	if($popup){
		$out_str .= "<br /><b>".$cw['specials_meanings']."</b>".config_list(1, $ship_stats['config']);
	}

/******************
* Upgrades listing/information
******************/
} elseif(isset($_GET['upgrades'])){
	//listing info for specific config
	if($popup){
		$out_str .= config_list(1, mysql_escape_string($_GET['chosen']));
	//show infor for all configs
	} else {
		$out_str .= config_list(1);
	}
	$out_str .= $b_link;

$help_type = $cw['upgrades'];

/************
* Random Events
*************/

} elseif(isset($random)) {

	$out_str .= "<h3><b>".$cw['random_events']."</b></h3>";
	$out_str .= $st[816];
	$out_str .= $st[817];
	$out_str .= make_table(array("",""),"WIDTH=75%");
	$out_str .= quick_row("<b class='b1'>".$cw['name']."</b>",$st[818]);
	$out_str .= quick_row("<b class='b1'>".$cw['type']."</b>",$st[819]);
	$out_str .= quick_row("<b class='b1'>".$cw['when']."</b>",$st[820]);
	$out_str .= quick_row("<b class='b1'>".$cw['level']."</b>",$st[821]);
	$out_str .= quick_row("<b class='b1'>".$cw['information']."</b>",$st[822]);
	$out_str .= quick_row("<b class='b1'>".$cw['description']."</b>",$st[823]);
	$out_str .= quick_row("<b class='b1'>".$cw['notes']."</b>",$st[824]);
	$out_str .= "</table><br />";

	//Solar Storm
	$out_str .= make_table(array("",""),"WIDTH=75%");
	$out_str .= quick_row($cw['name'],"<b class='b1'>".$cw['solar_storm']."</b>");
	$out_str .= quick_row($cw['type'],$cw['Active']);
	$out_str .= quick_row($cw['when'],$cw['Hourly']);
	$out_str .= quick_row($cw['level'],"2+");
	$out_str .= quick_row($cw['information'],$st[825]);
	$out_str .= quick_row($cw['description'],$st[826]);
	$out_str .= quick_row($cw['notes'],$st[827]);
	$out_str .= "</table><br />";

	//Black Hole
	$out_str .= make_table(array("",""),"WIDTH=75%");
	$out_str .= quick_row($cw['name'],"<b class='b1'>".$st[828]."</b>");
	$out_str .= quick_row($cw['type'],$cw['stationary']);
	$out_str .= quick_row($cw['when'],$st[829]);
	$out_str .= quick_row($cw['level'],"3");
	$out_str .= quick_row($cw['information'],$st[830]);
	$out_str .= quick_row($cw['description'],$st[831]);
	$out_str .= quick_row($cw['notes'],$st[832]);
	$out_str .= "</table><br />";

/************
* Planet Information
*************/

} elseif(isset($planet)) {
		$out_str .= "<h3><b>".$cw['planets']."</b></h3>".$st[833]."<br /><br />";
		$out_str .= "<br /><a href='#basics'>".$cw['planet_basics']."</a>";
		$out_str .= "<br /><a href='#colonists'>".$cw['colonists']."</a>";
		$out_str .= "<br /><a href='#shields'>".$cw['shield_generators']."</a>";
		$out_str .= "<br /><a href='#allocate_to_fleet'>".$st[834]."</a>";
		
		$out_str .= "<br /><br /><br /><a name=basics><b>".$cw['planet_basics']."</b></a><br /><br />".$st[835];
		
		$out_str .= "<br /><br /><br /><a name=colonists><b>".$st[836]."</b></a><br /><br />".$st[837];
		$out_str .= make_table(array($cw['item'],$cw['mineral_requirements'] , $st[838],$cw['produces']));
		$out_str .= make_row(array($cw['fighters'], "1 ".$cw['electronics'].", 3 ".$cw['metals'].", 2 ".$cw['fuels'], "100 ".$cw['colonists'], "6 ".$cw['fighters']));
		$out_str .= make_row(array($cw['electronics'], "4 ".$cw['metals'].", 3 ".$cw['fuels'], "75 ".$cw['colonists'], "3 ".$cw['electronics']));
		$out_str .= "</table><p /><b class='b1'>".$st[839]."</b><p />";
		
		$out_str .= make_table(array($cw['item'], $st[840], $st[841]));
		$out_str .= make_row(array($st[842].":", "0.243% ".$st[840], "6% ".$st[841], ""));
		$out_str .= make_row(array($st[843], "0.584% ".$st[840], "12% ".$st[841], ""));
		$out_str .= "</table>";
		$out_str .= "<p /><b>".$st[844]."</b>: ".$st[845];
		$out_str .= "<br /><b class='b1'>".$st[846]."!</b>";

		$out_str .= "<br /><br /><br /><a name=shields><b>".$cw['shield_generators']."</b></a><br /><br />".$st[847];

	$help_type = $cw['planets'];


/************
* Misc Information
*************/

} elseif(isset($misc)) {
	$out_str .= "<h3><b>".$st[848];

	$out_str .= "<br /><a href='#misc_turns'>".$cw['turns']."</a>";
	$out_str .= "<br /><a href='#misc_attack'>".$cw['attacking']."</a>";
	$out_str .= "<br /><a href='#misc_mining'>".$cw['mining']."</a>";
	$out_str .= "<br /><a href='#misc_moving'>".$st[849]."</a>";
	$out_str .= "<br /><a href='#misc_command'>".$cw['commanding_ships']."</a>";



	$out_str .= "<br /><br /><br /><b><a name=misc_turns>".$cw['turns']."</a></b><br />";
	$out_str .= sprintf($st[850], $GAME_VARS[hourly_turns]);
	$out_str .= make_table(array($cw['action'],$cw['turn_cost']));
	if($GAME_VARS['ship_warp_cost'] < 0){
		$out_str .= quick_row($cw['moving'],$st[851]);
	} else {
		$out_str .= quick_row($cw['moving'],$GAME_VARS['ship_warp_cost']);
	}
	$out_str .= quick_row($st[852],$GAME_VARS['attack_turn_cost_space']);
	$out_str .= quick_row($st[853],$GAME_VARS['attack_turn_cost_planet']);
	$out_str .= quick_row($st[854],"1");
	$out_str .= quick_row($st[855],"1 ".$st[857]);
	$out_str .= quick_row($st[856],$st[858]);
	$out_str .= "</table><br />".$st[859];


	$out_str .= "<br /><br /><br /><b><a name=misc_attack>".$cw['attacking']."</a></b><br />";
	$out_str .= $st[860]; 
	

	$out_str .= $st[861];
	
	$out_str .= "<br /><br /><br /><b><a name=misc_moving>".$st[862].":<br /><br />";

	$out_str .= make_table(array("",""),"WIDTH=75%");
	$out_str .= quick_row($cw['name'],$st[863]);
	$out_str .= quick_row($st[864],$cw['none']);
	$out_str .= quick_row($cw['description'],$st[865]);
	$out_str .= quick_row($cw['turn_cost'],$st[866].": 1");
	$out_str .= "</table><br />";

	$out_str .= make_table(array("",""),"WIDTH=75%");
	$out_str .= quick_row($cw['name'],$st[867]);
	$out_str .= quick_row($st[864],$cw['one']);
	$out_str .= quick_row($cw['description'],$st[868]);
	$out_str .= quick_row($cw['turn_cost'],$st[866].": 1");
	$out_str .= "</table><br />";

	$out_str .= make_table(array("",""),"WIDTH=75%");
	$out_str .= quick_row($cw['name'],$st[869]);
	$out_str .= quick_row($st[864],$st[870]);
	$out_str .= quick_row($cw['description'],$st[871]);
	$out_str .= quick_row($cw['turn_cost'],$st[872]);

	$out_str .= "</table><br />".$st[873];


	$out_str .= "<br /><br /><br /><b><a name=misc_command>".$cw['commanding_ships']."</a></b><br />".$st[874];

	$help_type = $st[875];


} elseif(isset($_GET['tools'])){
	$help_type = $cw['tools'];

	if(empty($GAME_VARS)){
		$out_str .= $st[876];
	} else {
		$out_str .= "<center><b>".$st[877]."</b></center><br />".sprintf($st[878], $game_info[name]);
		$out_str .= "<br />".$st[879]."<br />";
		$out_str .= make_table(array($cw['item'], $st[880]));
		$out_str .= quick_row($cw['fighters'],"$GAME_VARS[fighter_cost_earth]");
		$out_str .= quick_row($cw['metal'],"$GAME_VARS[buy_metal]");
		$out_str .= quick_row($cw['fuel'],"$GAME_VARS[buy_fuel]");
		$out_str .= quick_row($cw['electronics'],"$GAME_VARS[buy_elect]");
		$out_str .= "</table>";
		$out_str .= "<p /><br />".$st[881];
		$out_str .= make_table(array($cw['item'], $cw['materials_required'], $st[882], $st[883], $st[884]));

		$num_e = 60;
		$num_f = 60;

		$total_cos_figs = ($GAME_VARS['buy_elect'] + ($GAME_VARS['buy_metal'] * 3) + ($GAME_VARS['buy_fuel'] * 2)) * 10;
		$out_str .= make_row(array($cw['fighter'], "10 ".$cw['electronics'].", 30 ".$cw['metals'].", 20 ".$cw['fuels'], "60", $total_cos_figs, $avg_f_cost = $total_cos_figs / $num_f));

		$total_cos_elect = (($GAME_VARS['buy_metal'] * 4) + ($GAME_VARS['buy_fuel'] * 3)) * 20;
		$out_str .= make_row(array($cw['electronics'], "80 ".$cw['metals'].", 60 ".$cw['fuels'], "60", $total_cos_elect, $avg_e_cost = $total_cos_elect / $num_e));
		$out_str .= "</table><p />";

		$out_str .= $st[885]."<br/><b class='b1'>";
		if($avg_e_cost > $GAME_VARS['buy_elect']){
			$out_str .= $st[886];
		} else {
			$out_str .= $st[887];
		}

		$out_str .= "</b><br />".$cw['and']."<br /><b class='b1'>";

		if($avg_f_cost > $GAME_VARS['fighter_cost_earth']){
			$out_str .= $st[888];
		} else {
			$out_str .= $st[889];
		}
		$out_str .= "</a>";

	}


//load the server rules from the rules.htm file.
} elseif(isset($_GET['server_rules'])){
	$help_type = $cw['server_rules'];
	ob_start();
	include_once("rules.htm");
	$out_str .= ob_get_contents();
	ob_end_clean();


/************
* Help content from the XML file
*************/

} else {
	$help_array = load_xml("$directories[includes]/help_fr.xml");

	$header_str = "";
	$content_str = "";
	$page_top = "";

	$printable_topic_str = str_replace("_", " ", $topic);
	$help_type = $printable_topic_str;

	//user has selected a sub-topic to load
	if($sub_topic != ""){
		//no such help topic
		if(!isset($help_array[$topic."_help"][$sub_topic])){
			$content_str = "$st[890].$b_link";

		} else {//selected help topic contents.
			$printable_sub_str = str_replace("_", " ", $sub_topic);
			$content_str = "<center><b>$printable_sub_str</b></center>".$help_array[$topic."_help"][$sub_topic]['content'].$b_link;
		}

	//list the contents for that topic
	} else {
		if(!isset($help_array[$topic."_help"])){
			$topic = $cw['index'];
		}

		//list contents of topic.
		foreach($help_array[$topic."_help"] as $subtopic => $sub_array){

			if($subtopic == $topic){ //topics contents is the same as the topic, we will use this as a header
				$page_top = "<h3>$printable_topic_str ".$cw['help']."</h3><p />\n$sub_array[content]<p /><br />";
				continue 1;

			} elseif($subtopic != "-"){//empty subtopic's are skipped, as they are empty
				$header_str .= "\n<a href='#$subtopic'>$sub_array[title]</a><br />";
				$content_str .= "\n<p /><a name='$subtopic'><b>$sub_array[title]</b></a><br />";
			}

			$content_str .= "\n$sub_array[content] $b_link";
		}

		$out_str .= $page_top;
		if($header_str != ""){//only show this if it's not empty
			$out_str .=$header_str."<br /><br />";
		}
	}
	$out_str .= $content_str;
}


if (!$popup) print_header($cw['help']." - $help_type");


#prints help left column
echo "<a name=top> </a>";
if(!$popup){

	echo '<table border=0 cellspacing=0 cellpadding=0>';
	echo '<tr><td valign=top width=150>';
	echo "<center><img src=$directories[images]/logos/se_small.gif border=0 /></center><br /><br />";
	echo $cw['solar_empire_help']."<br /><br />";

	echo $st[891].":<br /><b class='b1'>$help_type</b>";

	echo "\n<br /><br /><br />".$st[892].":<ul>";
	echo "\n<li><a href='help.php?topic=Getting_Started'>".$st[893]."</a>";
	echo "\n<li><a href='help.php?misc=1'>".$st[875]."</a>";

	if(!isset($GAME_VARS['clan_member_limit']) || ($GAME_VARS['clan_member_limit'] > 0 && $GAME_VARS['clans_max'] > 0)){
		echo "\n<li><a href='help.php?topic=Clans'>".$cw['clans']."</a>";
	}

	echo "\n<li><a href='help.php?topic=Combat'>".$cw['combat']."</a>";
	echo "\n<li><a href='help.php?topic=Equipment'>".$cw['equipment']."</a>";
	echo "\n<li><a href='help.php?topic=Upgrades'>".$cw['upgrades_accessories']."</a>";
	echo "\n<li><a href='help.php?planet=1'>".$cw['planets']."</a>";
	echo "\n<li><a href='help.php?story=1'>".$st[894]."</a>";

	if(!isset($GAME_VARS['random_events']) || $GAME_VARS['random_events'] > 0){
//		echo "\n<li><a href='help.php?random=1'>".$cw['random_events']."</a>";
	}

	if(!isset($GAME_VARS['uv_num_bmrkt']) || $GAME_VARS['uv_num_bmrkt'] > 0){
		echo "\n<li><a href='help.php?topic=Blackmarkets'>".$cw['research_blackmarkets']."</a>";
	}

	if(!empty($db_name)){
		echo "\n<li><a href='help.php?ship_info=-1'>".$cw['ship_listings']."</a>";
	}
//	echo "\n<li><a href='help.php?topic=Technical_Information'>".$cw['technical_information']."</a>";
	//echo "\n<li><a href='help.php?list_vars=1'>Game Variables</a>";
	echo "\n<li><a href='help.php?server_rules=1'>".$cw['server_rules']."</a>";
	//echo "\n<li><a href='help.php?tools=1'>Tools</a>";

	echo "</ul></td><td valign=top><br />";
}

echo $out_str;

if (!$popup) print_footer();


?>