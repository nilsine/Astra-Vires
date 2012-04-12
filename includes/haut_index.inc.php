<?php
//require_once("ClickTale/ClickTaleTop.php");
require_once('common.inc.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title><?= ($titre_page) ? "[ Astra Vires - $titre_page ]":'[ Astra Vires ]' ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<meta name="description" content="Astra Vires est un jeu gratuit innovant jouable par navigateur de stratégie et de conquête spatiale." />
		<meta property="og:title" content="Jeu gratuit et innovant de conquête spatiale jouable par navigateur" />
    	<meta property="og:site_name" content="Astra Vires" />
    	<meta property="og:image" content="http://www.astravires.fr/images/interface/accueilFond.jpg" />
    	<meta property="fb:admins" content="1361167519" />
		<link rel="stylesheet" href="style_index.css" />
  		<link href="/stylesheets/theme_jquery.css" rel="stylesheet" type="text/css"/>
		<?php if ($nomPage == 'game_listing' || $nomPage == 'user_extra' || $nomPage == 'histoires'): ?>
		<style type="text/css">
			body {
				background-image: url('images/interface/accueilFondGrand.jpg');
			}
		</style>
		<?php endif; ?>
		<script language="JavaScript" src="js/jquery-1.3.2.min.js" type="text/javascript"></script>
		<script language="JavaScript" src="js/jquery.imgzoom.min.js" type="text/javascript"></script>
		<script language="JavaScript" src="js/gen_validatorv31.js" type="text/javascript"></script>
	
		<script type="text/javascript">
		 $(document).ready(function () {
		 	$('img.thumbnail').imgZoom({loadingImg: 'images/interface/ajax-loader.gif'});
		 });
		 </script>

		<script language="JavaScript" src="js/jquery-ui-1.8.11.custom.min.js" type="text/javascript"></script>
		<script type="text/javascript" src="js/jquery.blockUI.js"></script> 


	</head>

	<body>

	<div id="accueilPrincipal">
			<div id="accueilMenu">
			    <ul>
				<?php if (!($_GET['lidbp'] || $_GET['sid'] || $p_user['bp_user_id'] || $p_user['login_id'])): ?>
					<li><a href="http://www.astravires.fr/">Accueil</a></li>
					<li><a href="inscription.php">Inscription</a></li>
					<li><a href="captures-ecran.php">Captures d'écran</a></li>
					<li><a href="histoire.html">Histoire des Galaxies</a></li>
                    <li><a href="/forum" target="_blank">Forum</a></li>
				<?php endif; ?>
				<?php if (($nomPage == 'game_listing' || $nomPage == 'user_extra') && !isset($_REQUEST['game_selected'])): ?>
					<li><a href="game_listing.php">Galaxies</a></li>
					<li><a href="user_extra.php">Options & parrainage</a></li>
                  	<li><a href="/forum" target="_blank">Forum</a></li>
                  	<li><a href="logout.php?logout_game_listing=1">Déconnexion</a></li>
				<?php endif; ?>
				</ul>
			</div>
