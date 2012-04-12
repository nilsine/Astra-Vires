<?php
$nomPage = 'accueil';
include('includes/haut_index.inc.php');
?>

			<div id="accueilColonne1">
				Astra Vires est un <b>jeu gratuit jouable par navigateur</b> de <b>stratégie</b> et de <b>conquête spatiale</b>.<br /><br />

				Démarrez avec un vaisseau, explorez la galaxie et devenez un chef militaire <b>redoutable</b> !
			</div>

			<div id="accueilColonne2">
				<ul>
					<li>Affrontez de <b>vrais joueurs</b> 24h/24 dans un monde <b>persistant</b></li>
					<li>Jeu <b>gratuit</b> sans téléchargement accessible de <b>partout</b></li>
					<li><b>Jouez</b> comme vous avez envie: guerre, commerce, minage, chef de guilde, etc</li>
					<li>Gameplay <b>innovant</b></li>
				</ul>
			</div>
			<?php
				$auto_login = chechAutoLoginCookie();
				$pseudo = $mdp = $ckecked = '';
				if ( is_array( $auto_login ) && count( $auto_login ) > 0 ) {
					$pseudo = $auto_login['pseudo'];
					$mdp = $auto_login['mdp'];
					$ckecked = 'checked="checked"';
				}
			?>
			<div id="accueilColonne3">
				<h3>Connexion</h3>

				<form action="game_listing.php" method="post" name="connexion">
					<p>
						Pseudo<br />
						<input type="text" name="pseudo" size="18" value="<?php echo $pseudo; ?>" />
					</p>

					<p>
						Mot de passe<br />
						<input type="password" name="mdp" size="12" value="<?php echo $mdp; ?>" />&nbsp;<input type="submit" value="OK" />
					</p>
                    <p>
                        <input type="checkbox" name="auto_login" id="auto_login" value="1" <?php echo $ckecked; ?> />&nbsp;<label for="auto_login">Connexion automatique</label>
                    </p>
                    <input type="hidden" name="submit" value="1" />
					<a href="change_pass.php?stage_one=1">Mot de passe oublié ?</a>
				</form><br />
				<script language="JavaScript">
				var frmvalidator  = new Validator("connexion");
				frmvalidator.addValidation("pseudo", "req", "Veuillez indiquer votre pseudo");
				frmvalidator.addValidation("pseudo", "minlen=3", "La longueur minimale de votre pseudo est de 3 caractères");
				frmvalidator.addValidation("pseudo", "maxlen=25", "La longueur maximale de votre pseudo est de 25 caractères");

				frmvalidator.addValidation("mdp", "req", "Veuillez saisir votre mot de passe");
				frmvalidator.addValidation("mdp", "minlen=5", "La longueur minimale du mot de passe est de 5 caractères");
				</script>
				<div id="fb-root"></div>
				<script src="http://connect.facebook.net/fr_FR/all.js"></script>
				<script>
					FB.init({
						appId:'<?php echo YOUR_APP_ID;?>', cookie:true,
						status:true, xfbml:true
					});
					FB.Event.subscribe('auth.login', function(response) {
						window.location = "inscription.php";
					});
				</script>
				<?php if ($fbuser) { ?>
					<b>Bienvenue <?= $fbuser->name ?></b>
					<br/>
					<a href="inscription.php">Connexion automatique avec Facebook</a>
				<?php } else { ?>
					<fb:login-button perms="email,publish_stream,offline_access">Connexion avec Facebook</fb:login-button>
				<?php } ?>
			</div>

            <div id="accueilJouez">
                <a href="inscription.php" title="Cliquez pour vous inscrire"><img src="images/interface/jouez.png" alt="Jouez maintenant !" /></a>
            </div>

			<div class="spacer"></div>
		</div>
		
		<div id="carte">
		    <?php $url = "images/Galaxie_public.swf"; ?>		
		    <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="700" height="200" id="Galaxie" align="middle">
	            <param name="allowScriptAccess" value="sameDomain" />
	            <param name="allowFullScreen" value="false" />
	            <param name="wmode" value="transparent" />
	            <param name="movie" value="<?= $url ?>" /><param name="quality" value="high" /><param name="bgcolor" value="#000000" />	<embed src="<?= $url ?>" quality="high" bgcolor="#000000" width="700" height="200" name="Galaxie" align="middle" allowScriptAccess="sameDomain" allowFullScreen="false" wmode="transparent" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
        	</object>
		    
		</div>

<?php include('includes/bas_index.inc.php'); ?>
