<?php

if (getenv('APPLICATION_ENV') == 'development') {
	$config = array('hote' => 'localhost',
					'db' => 'astravires',
					'user' => 'root',
					'mdp' => 'root',
					'url_prefix' => 'http://astravires.localhost');
    $log_errors = 0;

} elseif (getenv('APPLICATION_ENV') == 'dev_serveur') {
        $config = array('hote' => 'localhost',
                                        'db' => 'astravires_dev',
                                        'user' => 'astravires_dev',
                                        'mdp' => '',
                                        'url_prefix' => 'http://www-dev.astravires.fr');
    $log_errors = 0;

} elseif (getenv('APPLICATION_ENV') == 'test') {
	$config = array('hote' => 'localhost',
					'db' => 'astravires_test',
					'user' => 'astravires_test',
					'mdp' => '',
					'url_prefix' => 'http://www-test.astravires.fr');
    $log_errors = 1;
} else {
	$config = array('hote' => 'localhost',
					'db' => 'astravires',
					'user' => 'astravires',
					'mdp' => '',
					'url_prefix' => 'http://www.astravires.fr');
    ini_set('display_errors', '0');
    $log_errors = 1;
}

//Host of the computer that holds the database. if the same computer as the web-host, leave as "localhost".
define("DATABASE_HOST", $config['hote']);

//The name of the database within which SE resides
define("DATABASE", $config['db']);

//The username required to access the database
define("DATABASE_USER", $config['user']);

//The password required to access the database.
define("DATABASE_PASSWORD", $config['mdp']);


//Send the authorisation mail. Set to 1 to send, and 0 not to send.
define("SENDMail", 0);


//Enter Your email address here.
//An e-mail will be sent here whenever the maints go wrong. But ONLY if the maints go wrong.
//Leave blank to not send any e-mails. even if the server goes up in a big ball of smoke. :)
define("SERVER_ADMIN_EMAIL", "info@astravires.fr");


//Whatever you want to call the server
define("SERVER_NAME", "Astra Vires");

//the part of the URL that you have to type in to get to SE should go here.
//uncomment the IF statement if you want the players to have the option of using SSL
//no tailing slash.
/*if(!empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], "https://") !== false){
	define("URL_PREFIX", "https://your-url.com");
} else {*/
	define("URL_PREFIX", $config['url_prefix']);
//}


//version of the code. Suffix an 'M' if modified from a release
$code_base = "Purely SE 3.07M";

//lenth of a user may be inactive for before they are automatically logged out. In seconds.
define("SESSION_TIME_LIMIT", 1800);


//The in-game login-id of the server owner. (used for developer level access).
//Enter 0 if you do not know it, or do not want it. It is NOT advised u use the admin's ID (1).
define("OWNER_ID", 1);



//The name of the log file.
$log_name = "errorlog.html";

//Error Reporting
//This will dictate the errors that will be reported when the script is run.
//Only show critical or fatal errors. You can change this to show more errors (see manual: 'error_reporting' function)
//error_reporting  (E_ERROR);


//The below determines if page processing times are shown.
//comment out the single line below to not show them.

$start_time = 0;

//OR
//uncomment the below two lines to have page processing functional.

#$start_time = explode(" ",microtime());
#$start_time = $start_time[1] + $start_time[0];

//Facebook application details
define('YOUR_APP_ID', '');
define('YOUR_APP_SECRET', '');
