<?php
$titre_page = 'Inscription';
include('includes/haut_index.inc.php');
db_connect();
?>
<style type="text/css">
#accueilColonne2 {
	width: 300px;
}
</style>

			<?php if (!$_GET['lidbp'] && !$fbuser) { ?>
			<div id="accueilColonne1">
				<p>Pour jouer vous devez d'abord vous inscrire en saisissant votre pseudo, votre mot de passe ainsi que votre adresse e-mail.</p>

				<p>Tous les champs sont obligatoires.</p>
			</div>

			<div id="accueilColonne2">
				<form action="signup.php" method="post" name="inscription">
					<table class="inscription">
						<tr>
							<td class="libelleChamp">Pseudo&nbsp;<span class="obligatoire">*</span></td>
							<td><input type="text" name="pseudo" size="15" /></td>
						</tr>

						<tr>
							<td class="libelleChamp">Mot de passe&nbsp;<span class="obligatoire">*</span></td>
							<td><input type="password" name="mdp" id="mdp" size="12" /></td>
						</tr>

						<tr>
							<td class="libelleChamp">Retapez le mot de passe&nbsp;<span class="obligatoire">*</span></td>
							<td><input type="password" name="mdp2" id="mdp2" size="12" /></td>
						</tr>

						<tr>
							<td class="libelleChamp">Adresse e-mail&nbsp;<span class="obligatoire">*</span></td>
							<td><input type="text" name="email" size="20" /></td>
						</tr>

						<tr>
							<td colspan="2" align="center"><input type="submit" value="Inscription" /></td>
						</tr>
					</table>
				</form>
				<script language="JavaScript">

				function mdpIdentiques()
				{
				  if ($("#mdp").val() != $("#mdp2").val()) {
				  	alert('Les deux mots de passe doivent être identiques');
				  	return false;
				  } else {
				  	return true;
				  }
				}

				var frmvalidator  = new Validator("inscription");
				frmvalidator.addValidation("pseudo", "req", "Veuillez indiquer votre pseudo");
				frmvalidator.addValidation("pseudo", "minlen=3", "La longueur minimale de votre pseudo est de 3 caractères");
				frmvalidator.addValidation("pseudo", "maxlen=25", "La longueur maximale de votre pseudo est de 25 caractères");

				frmvalidator.addValidation("mdp", "req", "Veuillez saisir le mot de passe");
				frmvalidator.addValidation("mdp", "minlen=5", "La longueur minimale du mot de passe est de 5 caractères");

				frmvalidator.addValidation("mdp2", "req", "Veuillez saisir la confirmation du mot de passe");
				frmvalidator.setAddnlValidationFunction("mdpIdentiques");

				frmvalidator.addValidation("email", "req", "Veuillez saisir votre adresse e-mail");
				frmvalidator.addValidation("email", "email", "L'adresse e-mail saisie est incorrecte");
				</script>
				<?php
				} elseif ($_GET['lidbp'] && !$fbuser) {
					db("select * from user_accounts where login_id=".((int)$_GET['lidbp'])." and bp_user_id != 0");
					if (!dbc()) {
						echo "URL invalide, ce compte n'existe pas";
					} else {
				?>
				<div id="accueilColonne1">
				<?= $st[1821] ?><br /><br />
				<form method="post" action="signup.php" name="inscription">
					<input type="hidden" name="id" value="<?= $_GET['lidbp'] ?>" />
					<table class="inscription">
						<tr>
							<td class="libelleChamp"><?php echo $st[665]; ?></td>
							<td><input type="text" name="pseudo" size="20" /></td>
						</tr>

						<tr>
							<td align="center" colspan="2"><input type="submit" value="Inscription" /></td>
						</tr>
					</table>
				</form>
				<script language="JavaScript">
				var frmvalidator  = new Validator("inscription");
				frmvalidator.addValidation("pseudo", "req", "Veuillez indiquer votre pseudo");
				frmvalidator.addValidation("pseudo", "minlen=3", "La longueur minimale de votre pseudo est de 3 caractères");
				frmvalidator.addValidation("pseudo", "maxlen=25", "La longueur maximale de votre pseudo est de 25 caractères");
				</script>
				<?php
					}
				} elseif (!$_GET['lidbp'] && $fbuser){
					db("select * from user_accounts where fb_user_id='".((int)$fbuser->id)."'");
//					var_dump(dbc());
					if (!dbc()){
						//check if email is in db
						db("select * from user_accounts where email_address='".(trim(mysql_escape_string(strip_tags((string)$fbuser->email))))."' LIMIT 1");
						$already_user = dbr(1);
						if (!$already_user['login_id']){//is not in db...allow to start a new game
					?>
					<div id="accueilColonne1">
					  <h3>Nouveau compte</h3><br />
						<?= $st[1821] ?><br /><br />
						<form method="post" action="signup.php" name="inscription">
							<input type="hidden" name="fb_id" value="<?= $fbuser->id ?>" />
							<table class="inscription">
								<tr>
									<td class="libelleChamp"><?php echo $st[665]; ?></td>
									<td><input type="text" name="pseudo" size="20" /></td>
								</tr>

								<tr>
									<td align="center" colspan="2"><input type="submit" value="Inscription" /></td>
								</tr>
							</table>
						</form>
						<script language="JavaScript">
						var frmvalidator  = new Validator("inscription");
						frmvalidator.addValidation("pseudo", "req", "Veuillez indiquer votre pseudo");
						frmvalidator.addValidation("pseudo", "minlen=3", "La longueur minimale de votre pseudo est de 3 caractères");
						frmvalidator.addValidation("pseudo", "maxlen=25", "La longueur maximale de votre pseudo est de 25 caractères");
						</script>
					</div>
					<?php }else{// is in db...set login name of  the fb email
						$pseudo = $already_user['login_name'];
					}
					?>
					<div id="accueilColonne2">
						<h3>Associer avec un compte existant</h3>

						<form action="game_listing.php" method="post" name="connexion">
							<p>
								Pseudo<br />
								<input type="text" name="pseudo" size="18" value="<?php echo $pseudo; ?>" />
							</p>

							<p>
								Mot de passe<br />
								<input type="password" name="mdp" size="12" value="<?php echo $mdp; ?>" />&nbsp;<input type="submit" value="OK" />
							</p>
		                    <input type="hidden" name="submit" value="1" />
		                    <input type="hidden" name="associate" value="1" />
						</form>
						<script language="JavaScript">
						var frmvalidator  = new Validator("connexion");
						frmvalidator.addValidation("pseudo", "req", "Veuillez indiquer votre pseudo");
						frmvalidator.addValidation("pseudo", "minlen=3", "La longueur minimale de votre pseudo est de 3 caractères");
						frmvalidator.addValidation("pseudo", "maxlen=25", "La longueur maximale de votre pseudo est de 25 caractères");

						frmvalidator.addValidation("mdp", "req", "Veuillez saisir votre mot de passe");
						frmvalidator.addValidation("mdp", "minlen=5", "La longueur minimale du mot de passe est de 5 caractères");
						</script>
					<?php
					}else
					{//login and header to game_listing
						header('Location: game_listing.php');
					}

				}
				?>
			</div>

			<div class="spacer"></div>
		</div>

<?php include('includes/bas_index.inc.php'); ?>