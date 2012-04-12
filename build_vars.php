<?php
/*
A script for creating the db_vars_inc file.
This script can be used stand-alone (password in password.php), or by way of direct calling.
Created:
By: Jonathan "Moriarty"
On: 14/4/03
*/

//Get the db connectivity details, as well as game_dir.
require_once("common.inc.php");

//ensure a DB has been selected
if(!isset($db_name)){
	echo "No database was selected.<form action=$_SERVER[PHP_SELF] method=post><input type=hidden name=pass value=$pass /><br /> Database: <input type=text name=db_name /><p /><input type='submit' /></form>";
	exit();
}

//location of the file in relation to the games directory.
//allow a pre-stated one if needed.
if(!isset($file_loc)){
	$file_loc = "./$directories[includes]/${db_name}_vars.inc.php";
}

//open a stream
$stream = @fopen($file_loc, "w");

//ensure a stream could be created
if(empty($stream)){
	echo "Unable to create db_vars.inc.php in the specified location.<p />Ensure you have the necassary permissions, and that the sub-directory $db_name does exist.";
	exit();

//extra error checking.
} elseif (!is_writable($file_loc)) {
	echo "Unable to write to the specified file for some reason. Ensure permissions are valid.";
	exit();
}



//start the output string
$output_str = "<?php\n//Database: $db_name, ".date("F j, Y, g:i:s a")."\n\n\$GAME_VARS = array(\n";


//do the DB stuff
db_connect();
db("select name,${db_name}_value as value from se_db_vars order by name");
while($db_var = dbr()){
	//$output_str .= "\$$db_var[name] = '$db_var[value]';\n";
	$output_str .= "\t'$db_var[name]' => $db_var[value],\n";
}

$output_str .= ");\n\n";

//output the final file
if(!fwrite($stream, $output_str."?>")){
	echo "For some reason the file could not be written to. Ensure permissions are valid.";

//only output text for admin.
} elseif(preg_match("/admin\.php/", $_SERVER['PHP_SELF'])) {
	echo "Variables successfully created for table set <b>$db_name</b>";
}
fclose($stream);
?>