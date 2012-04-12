<?php

require_once("user.inc.php");

if($user['login_id'] != 1 && (OWNER_ID != 0 && $user['login_id'] != OWNER_ID)) {
	print_page("Admin",$st[142]);
	exit();
}

//db_connect();

//create a 'query', which can be used to determine info.
db("select * from ${db_name}_users limit 1");

//max number of players that can be shown on a graph.
$players_selected = 11;

if($graph_db < 0){
	if($graph_db == -1){
		$element = "page_views";
	} elseif($graph_db == -2){
		$element = "login_count";
	} else {
		$element = "num_games_joined";
	}
	$db = "user_accounts";
} elseif(isset($graph_db) && mysql_field_type($query, $graph_db) == "int") {
	$db = $db_name."_users";
	$element = mysql_field_name($query, $graph_db);
} elseif(!isset($graph_db)) {
	$graph_db = "";
}

if(!isset($element)){
	$element = "";
}


//type of graph.
if(isset($graph_type) && $graph_type == 2){
	$graph_type_str = "bars";
} else{
	$graph_type = 1;
	$graph_type_str = "pie";
}

if(!isset($remaining)){
	$remaining = 0;
}

if(isset($draw_graph)){ //draw a graph
	//Include the graph code
	require_once("$directories[graphs]/phplot.php");

	//Define the object
	$graph = new PHPlot;

	$data_array = array();
	$data_array[] = "";

	$legend_array = array();
	$cheap_counter = 0;
	//select the values from the db, and put them into an array.
	db2("select login_name, $element from $db where $element > 0 && login_id > 5 order by $element desc limit $players_selected");
	while($temp_store = dbr2(1)){
		$cheap_counter += $temp_store[$element];
		$data_array[] = $temp_store[$element];
		$legend_array[] = $temp_store['login_name'];
	}

	if($remaining == 1){//include remaining players, only if wanted
		db2("select sum($element) as all_users from $db where $element > 0 && login_id > 5");
		$temp_store2 = dbr2(1);
		$data_array[] = $temp_store2['all_users'] - $cheap_counter;
		$legend_array[] = $cw['all_other_players'];
	}

	if(count($data_array) < 2){//don't bother trying to make a graph, if no users.
		exit();
	}

	//Set some data
	if($graph_type==1){//text-data (no time)
		$example_data = array($data_array,$data_array);
	} else {//data-data (any extra entries are time).
		$example_data = array($data_array);
	}

	$graph->SetDataValues($example_data);
	//Error_Reporting(0);
	$graph->SetPlotType($graph_type_str);
	$graph->SetLabelScalePosition(1.27);
	$graph->SetLegend($legend_array);
	if($graph_type == 2){
		$graph->SetYLabel($element);
		$graph->SetXLabel($cw['users']);
	} else {
		$graph->SetLegendPixels(1,1,"");
	}
	//$graph->SetTitle("$element per user");


	//Draw it
	$graph->DrawGraph();



//list things to change on the graph
} else {
	print_header($cw['graphs']);
	if(isset($graph_selected) && $graph_selected == 1){
		echo sprintf($st[143], $element)."<img src='$_SERVER[PHP_SELF]?graph_db=$graph_db&draw_graph=1&graph_type=$graph_type&remaining=$remaining' alt='".sprintf($st[144], $graph_type_str, $element)."' /><br />".$st[145]."</center>";
	} else {
		$graph_selected = 0;
	}

	$type_link = "<a href=$_SERVER[PHP_SELF]?graph_db=$graph_db&element=$element&graph_selected=$graph_selected&remaining=$remaining&graph_type=";
	echo sprintf($st[146], $graph_type_str, $type_link, $type_link);
	echo $st[147]." <a href='$_SERVER[PHP_SELF]?graph_db=$graph_db&element=$element&graph_selected=$graph_selected&remaining=1&graph_type=$graph_type'>".$cw['yes']."</a> - <a href='$_SERVER[PHP_SELF]?graph_db=$graph_db&element=$element&graph_selected=$graph_selected&remaining=0&graph_type=$graph_type'>No</a><br />";

	echo $st[148];
	if($user['login_id'] == OWNER_ID){
		echo "<br /><a href='$_SERVER[PHP_SELF]?graph_db=-1&graph_selected=1&graph_type=$graph_type&remaining=$remaining'>".$cw['page_views']."</a>";
		echo "<br /><a href='$_SERVER[PHP_SELF]?graph_db=-2&graph_selected=1&graph_type=$graph_type&remaining=$remaining'>".$cw['total_login_count']."</a>";
		echo "<br /><a href='$_SERVER[PHP_SELF]?graph_db=-3&graph_selected=1&graph_type=$graph_type&remaining=$remaining'>".$cw['games_joined']."</a><br />";
	}

	for ($i=0; $i < mysql_num_fields($query); $i++) {
		$name = mysql_field_name($query, $i);

		if(mysql_field_type($query, $i) != "int" || preg_match("/^last_/",$name) || preg_match("/^show_/",$name) || $name == "login_id" || $name == "joined_game" || $name == "location" || $name == "ship_id" || $name == "on_planet" || $name == "clan_id" || $name == "second_scatter" || $name == "banned_time"){
			continue 1;
		} else {
			echo "<br /><a href='$_SERVER[PHP_SELF]?graph_db=$i&graph_selected=1&graph_type=$graph_type&remaining=$remaining'>".mysql_field_name($query, $i)."</a>";
		}
	}

	echo sprintf($st[149], $players_selected);

	echo $st[150];
	print_footer();
}



?>