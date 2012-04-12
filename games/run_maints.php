<?php
/*************************************
* Contains things that are generic between the maints.
* Created: 6th May 2004
* By: Moriarty
*************************************/
//putenv('APPLICATION_ENV=dev_serveur');
/************* Variables **************/


//the number of seconds between each server-wide maint. should probably be daily
$time_between_s_maints = 86400;
//$time_between_s_maints = 0;

//set to 1 to make logs of the maints, in the backups dir.
//setting to 0 will result in only making logs when the maints go bang.
$make_logs_of_maints = 1;

//Specify a maximum number of maint logs, after which the oldest will be overwritten.
$max_num_maint_logs = 7;

//1 = make backups of the entire SE DB daily (compressed if possible) to the backups dir
//0 = no backups made.
$make_database_backups = 1;

//if make_database_backups is set to 1. specify a maximum number of backups to keep, before cycling over the old ones.
$max_num_db_backups = 7;



/******************** Initialise Script *************************/
//output buffering. picks up error messages
ob_start();

//ensure all errors reported.
error_reporting(E_ALL);

//60 secs should be enough.
set_time_limit(60);

require_once("../common.inc.php");


$start_time = explode(" ",microtime());
$start_time = $start_time[1] + $start_time[0];


db_connect();
mt_srand((double)microtime()*1000000);



/****************
* Run the Maints.
****************/

$final_str = "\n\n\n<P><br /><br /><hr>Beginning Maintanence Cycle - ".date( "d/M/Y - H:i:s").".\n\n\n<br />";


/***********************
* Hourlies
***********************/

//hourlies only have minutes between them.
//the extra -60 allows for 60 seconds in which the last maint may have taken to run.
db_m("select hourly, last_hourly, db_name from se_games where paused = 0 && status = 1 && ((last_hourly - 60) + (hourly * 60) <= ".time().")");
while($temp_hour = db_mr()){
	$db_name = $temp_hour['db_name'];
	require("hourly.inc.php");
}
unset($temp_hour);

/***********************
* Dailies
***********************/

//dailies are meassured in hours between them
//-600 covers potential time last maint may have taken.
db_m("select daily, last_daily, db_name from se_games where paused = 0 && status = 1 && ((last_daily - 600) + (daily * 3600) <= ".time().")");
while($temp_day = db_mr()){
	$db_name = $temp_day['db_name'];
	require("daily.inc.php");
}
unset($temp_day);


/***********************
* Server wide maint Running
***********************/

//select time of last server wide maint.
db_m("select last_ran_s_maint from se_central_table");
$temp_serverwide = db_mr();
if($temp_serverwide['last_ran_s_maint'] + $time_between_s_maints <= time()){
	require("server_maint.php");
}




/******************
* Begin Shutdown.
******************/


//close db link
if(!empty($database_link)){
	mysql_close($database_link);
}

$final_str .= "\n\n\n<p><hr>Maintenance Cycle Ending";


$end_time = explode(" ",microtime());
$end_time = $end_time[1] + $end_time[0];
$total_time = ($end_time - $start_time);

$final_str .= "\n<br />Total Time take : $total_time<hr>";
$final_str .= "\n-------------------------------------------------------------------";

$output_store = ob_get_contents();
ob_end_clean();


//only write the log if something happened, and not directly requesting.
if(!empty($output_store) && empty($_GET['dir_req'])){
	write_to_error_log($output_store.$final_str, "", -1);
}

//user wants to see the results.
if(!empty($_GET['dir_req'])){
	echo $output_store.$final_str;

//if not requesting direct output, see if required to make logs
} elseif($make_logs_of_maints == 1) {

	if(extension_loaded("zlib")) {
		$ext = ".gz";
		$comp = true;

	} else {//unable to compress
		$ext = ".html";
		$comp = false;
	}

	//open the file for outputting
	$fp = fopen("../$directories[backups]/".DATABASE."_maint_logs-".date( "M.d.Y").$ext, "a+b");

	if($comp){ //compress before writing if possible
		$final_str = gzencode($final_str."\n\n");
	}
	fwrite($fp, $final_str);
	fclose($fp);

	//delete old maint logs.
	delete_old_backups($max_num_maint_logs, "_maint_logs");
}

exit();


/**************************** Functions ***********************/

/**********************
* Database Functions
***********************/

//send a query to the database.
function db_m($string) {
	global $db_maint_func_query, $database_link;
	$db_maint_func_query = mysql_query($string, $database_link) or mysql_die($string);
}

//returns associated array
function db_mr() {
	global $db_maint_func_query;
	return mysql_fetch_array($db_maint_func_query, MYSQL_ASSOC);
}


/*********************
* Delete logs function
*********************/

//will delete files with $file_name_string in their name, when there are more than max_num
//Note: uses the files creation time to work out the age. not its modified time.
function delete_old_backups($max_num, $file_name_string){
	global $directories, $final_str;

	$path = "../$directories[backups]";
	$dir_stream = opendir($path);
	$file_arr = array();

	//loop through the contents of the directory
	while (false !== ($file = readdir($dir_stream))) {

		//find any files that have the right name, and place them into the array.
		if(preg_match("`$file_name_string`i", $file)){
			$file_arr[$file] = filectime("$path/$file");
		}
	}
	closedir($dir_stream);

	if(count($file_arr) > $max_num){

		//sort array so get rid of oldest entries.
		arsort($file_arr);

		//keep only the newest entries (the number of them being stipulated by max_num).
		$file_arr = array_slice($file_arr, ($max_num));

		//now delete the remaining entries in the array (the old ones).
		foreach($file_arr as $filename => $asdfsdfsdfsd){
			$final_str .= "\n<br />$filename deleted.<br />";
			unlink("$path/$filename");
		}
	}
}


// a little function that will tell the time between different script locations.
//very useful for debugging.
function print_time (){
	global $maint_time, $since_last, $final_str;
	$end_time = explode(" ",microtime());
	$end_time = ($end_time[1] + $end_time[0]);
	$this_time = ($end_time - $maint_time);
	$final_str .= "\n$this_time. - Total seconds --- This Cycle: ".($this_time - $since_last[count($since_last) -1])." seconds\n<br />";
	$since_last[] = $this_time;
}


?>
