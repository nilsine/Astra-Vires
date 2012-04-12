<?php
/*************************
* Script for running maints that are server-wide.
* Also makes backups of the DB.
* Created: 6/May/2004
* By: Moriarty
**************************/

//start counting
$maint_time = explode(" ",microtime());
$maint_time = $maint_time[1] + $maint_time[0];
$since_last = array(0 => 0);

$final_str .= "\n\n<hr><p>Beginning Server Maintenance...<p>";


/**********************
* Quick Maints
**********************/
//Change the tip for the day.
db("select tip_id from daily_tips order by RAND() limit 1");
$tip_count = dbr(1);
dbn("update se_central_table set todays_tip = '$tip_count[tip_id]'");
$final_str .= "\n<br />New tip chosen - tip # $tip_count[tip_id]...<br />";



//delete accounts that have not been authorised within a week
$time_to_del_from = time() - 604800;
dbn("delete from user_accounts where signed_up <= '$time_to_del_from' && session_exp = 0 && login_id > 5 && login_count = 0");
$final_str .= "\n<br />".mysql_affected_rows()." unauthorised user accounts deleted...<br />";



//change AI passwords daily. Wouldn't do to have someone get access to them.
$p_pass = md5(create_rand_string(50));
dbn("update user_accounts set passwd = '$p_pass' where login_id = '2' || login_id = 3 || login_id = 4 || login_id = 5");
$final_str .= "\n<br />Special accounts pass changed...<br />";



//delete user history older than 3 weeks.
dbn("delete from user_history where timestamp < ".time()."-1814400");
$final_str .= "\n<br />".mysql_affected_rows()." old rows from the user_history deleted...<br />";



//delete posts to the central forum that are older than 3 weeks.
dbn("delete from se_central_messages where timestamp < ".time()."-1814400");
$final_str .= "\n<br />".mysql_affected_rows()." messages from the central forum were deleted...<br />";
print_time();


//backup the DB if requested. & delete old files
if($make_database_backups == 1){
	backup_db();
	print_time();

	delete_old_backups($max_num_db_backups, "_db_backup");
	print_time();
}


//build a new index of the stylesheets directory.
build_ss_index();
print_time();


dbn("update se_central_table set last_ran_s_maint = '".time()."'");

//print that maint was run, and how long it took.
$end_time = explode(" ",microtime());
$end_time = $end_time[1] + $end_time[0];
$total_time = ($end_time - $maint_time);

$final_str .= "\n<p>... All done in $total_time seconds.<br />\n";

return 1;


/******************************* Functions *********************************/

//This function will create a backup of the entire database.
//it will try to compress the resulting file if it can.
function backup_db(){
	global $db_name, $final_str, $directories, $comp, $db_func_query;

	set_time_limit(500); //this could take time (get's done inside 1 sec on my comp, but servers arn't that dedicated. Plus they have more db entries. :)

	//can compress the files.
	if(extension_loaded("zlib")) {
		$ext = ".gz";
		$comp = true;

	} else {//unable to compress
		$ext = ".sql";
		$comp = false;
	}

	//specify, and open the file.
	$file_name = DATABASE."_db_backup-".date( "G.i-M.d.Y").$ext;
	$fp = fopen("../$directories[backups]/$file_name", "wb");

	$counter = 0;


	//select all tables from DB and loop through them
	db2("SHOW TABLE STATUS");
	while($tables = dbr2(1)){
		$counter ++;
		$tab_str = "\n\nDROP TABLE IF EXISTS $tables[Name];\nCREATE TABLE $tables[Name] (";
		$col_count = 0;

		//create table structure for table
		db("SHOW COLUMNS FROM $tables[Name]");
		while($cols = dbr(1)){

			$tab_str .= "\n$cols[Field] $cols[Type] ";

			if(!isset($cols['null'])){ //set not null
				$tab_str .= "NOT NULL";
			} else {
				$tab_str .= "NULL";
			}

			if($cols['Default'] != ''){ //place default
				$tab_str .= " default '$cols[Default]'";
			}

			if($cols['Extra'] == 'auto_increment'){ //place auto incremement
				$tab_str .= " auto_increment";
			}

			$tab_str .= ",";//end row
			$col_count++;

		}//end looping through columns
		unset($cols);
		mysql_free_result($db_func_query);

		$keys_array = array();
		//work out what the keys are for the table.
		db("SHOW INDEX FROM $tables[Name]");
		while($keys = dbr(1)){
			if($keys['Non_unique'] == 0 && $keys['Key_name'] != "PRIMARY") { //a unique key.
				$keys_array['UNIQUE'] = (isset($keys_array['UNIQUE']) ? $keys_array['UNIQUE'] : "")."UNIQUE KEY $keys[Key_name] ($keys[Column_name]), ";

			} else { //non primary (foreign?)
				$keys_array[$keys['Key_name']] = (isset($keys_array[$keys['Key_name']]) ? $keys_array[$keys['Key_name']] : "")."$keys[Column_name], ";
			}
		}//end collecting key info
		unset($keys);
		mysql_free_result($db_func_query);

		//loop through the array of keys, and list the keys at the bottom
		foreach($keys_array as $key_type => $val){
			$tab_str .= "\n";
			if($key_type != "UNIQUE"){
				if($key_type != "PRIMARY"){
					$tab_str .= "KEY ";
				} else {
					$key_type .= " KEY"; //apend "key" to "primary"
				}
				$tab_str .= "$key_type (".preg_replace("/, $/","",$val)."),";
			} else {
				$tab_str .= $val;
			}
		} //end sorting out key info
		unset($keys_array);


		//remove trailing comma, and end table creation.
		$tab_str = preg_replace("/, $/","",$tab_str)."\n)ENGINE=$tables[Engine];";

		if($comp){ //compress before writing
			$tab_str = gzencode($tab_str."\n\n");
		}
		//save table structure to file now.
		fwrite($fp, $tab_str);
		fflush($fp);
		$tab_str = "";

		$ins_count = 0;

		//get the table's actual data for the inserts
		//MUST use numerical array (rather than associative)
		db("select * from $tables[Name]");
		while($rows = dbr()){
			$tab_str .= "\nINSERT INTO $tables[Name] VALUES (";

			$ins_count++;//increment row counter

			//create the values for the insert statement
			for($i=0; $i<$col_count; $i++){
				$tab_str .= "'".mysql_escape_string($rows[$i])."', ";
			}
			$tab_str = preg_replace("/, $/","",$tab_str).");";//remove trailing comma

			//every 100 rows, dump them to the file (save memory, and stops from crashing).
			//100 seems to be the optimal number.
			/*
			Do it every 1000, it takes about 5 times as long.
			do it every 10, it takes about 3 times as long.
			But 90-100 seems to be a magic number. At least one my PC.
			*/

			if($ins_count == 100){

				if($comp){ //compress before writing
					$tab_str = gzencode($tab_str);
				}
				//save table structure to file now.
				fwrite($fp, $tab_str);
				fflush($fp);
				$tab_str = "";

				$tab_str = "";//clear tab_str. otherwise might have major issues.
				$ins_count = 0;//reset counter
			}
		}
		unset($rows);
		mysql_free_result($db_func_query);

		if($comp){ //compress before writing
			$tab_str = gzencode($tab_str."\n\n");
		}
		//save table structure to file now.
		fwrite($fp, $tab_str);
		fflush($fp);
		$tab_str = "";

	} //end table loop

	//all done
	fclose($fp);
	$final_str .= "\n<br />$counter tables backed up to '$file_name'...<br />\n";
}



//A function that will create an index of the style-sheets directory.
//outputs the resulting index to file 'ss_index.php'
function build_ss_index (){
	global $directories, $final_str;

	//set the path, then open it
	$path = "../$directories[stylesheets]";
	$dir_stream = opendir($path);

	$output_str = '$ss_index_array = array(';

	//loop through the files in the dir.
	while (false !== ($file = readdir($dir_stream))) {


		//only use .css type files
		if(!eregi("\.css", $file)){
			continue 1;
		}

		//get num of the style sheet.
		$file_num = preg_replace("/([a-z]*)([0-9]*)\.css/i","\\2",$file);


		$creator_name = "";
		$ss_name = "";
		$b_col = "";

		//open file
		$f_stream = fopen($path."/".$file, "rb");
		$line_num = 1;

		//loop through the file and get the required bits.
		while (!feof($f_stream)) { //get what's needed
			$present_line = fgets($f_stream);

			//work out who made it
			if($line_num == 1){
				$creator_name = trim(preg_replace("/\/\* Created By (.*) \*\//i", "\\1", $present_line));

			//work out it's title
			} elseif($line_num == 2){
				$ss_name = trim(preg_replace("/\/\* (.*) \*\//i", "\\1", $present_line));

			} elseif(preg_match("/b\.b1/i", $present_line)){
				//get the colour of the b1-bold class. it can be either text, or hex. And trim.
				$b_col = trim(preg_replace("/b.b1 \{color\: ([a-z]{1,}|\#[a-f0-9]{6}).*/i", "\\1", $present_line));
				break 1; //once we have the bold, we can quit this file.
			} 
			$line_num ++;
		}
		fclose($f_stream);


		//create the output stream
		$output_str .= "\n'$file_num' => array('creator' => '$creator_name', 'ss_name' => '$ss_name', 'b_col' => '$b_col'),";

	} //end loop through directory
	closedir($dir_stream);

	$header_txt = "<?php\n/*********************\n* An index of the stylesheets in '$path'\n* Created: ".date("M d - H:i")."\n* By Moriarty's Index Maker\n********************/\n\n";

	//remove trailing comma
	$output_str = $header_txt.preg_replace("/,$/","", $output_str)."\n);\n?>";

	//output final file
	$f_stream = fopen("../$directories[includes]/ss_index.php", "wb");
	fwrite($f_stream, $output_str);
	fclose($f_stream);

	$final_str .= "\n<br />New Stylesheet Index Created...<br />\n";
}

?>