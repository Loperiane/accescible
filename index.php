<?php
session_start();
/** Importation de l'autoloader **/

require 'class/Autoloader.php';
$autoload = new Autoloader;
$autoload->register();

if (isset($_GET['deconnexion']))
{
  require 'deconn.php';
}


//******Connect BD********

require 'connData.php';

$manageU = new UtilisateurManager($bdd);


/***********traitement sur la page index*/

	//On récupère les infos saisies dans les imputs
	$Courriel = htmlspecialchars($_POST['Courriel']);
	$Mdp = sha1($_POST['Mot_de_passe']);

	// on créé une instance de UtilisateurManager
	$managerU = new UtilisateurManager ($bdd);

	//on verifie que le compte existe
	$exist = $managerU->exists($Courriel, $Mdp);

	/**********SI SE CONNECTER*************/

	if (isset($_POST['se_connecter']))
	{
		//si n'existe on informe;
		if (!$exist) {
				echo '<div id="notif" class="notif error"> <h2>Courriel ou mot de passe incorrect. </h2></div><script type="text/javascript"> window.setTimeout("location=(\'index.php\');",1000) </script>';
		}
			//Si existe
		elseif ($exist) {
				//le manager instancie cet utilisateur via getUtilisateur qui renvoie un objet Utilisateur
				$ut = $managerU->getUtilisateur($Courriel, $Mdp);

				//On verfie que pas banni
			if ($ut->getValide() == 1) {
					//on verifie que compte n'est pas banni avec la donnée "valide" dela bd (1= bani, 0 = Ok);
					echo '<div id="notif" class="notif error"> <h2>Courriel non valide</h2></div><script type="text/javascript"> window.setTimeout("location=(\'index.php\');",1000) </script>';
			}
			// Si compte valide on accede au compte
			elseif ($ut->getValide() == 0) {
					//créé les parametres de session
				$_SESSION['emailU'] = $ut->getEmailU();
				$_SESSION['mdpU'] = $ut->getMdpU();
				$_SESSION['confirme'] = $ut->getConfirme();
				//on informe que logged est Ok pour redirection sur l'espace des gens inscrits
				$_SESSION['logged'] = true;
				//informe et on redirige
				echo '<div id="notif" class="notif success"> <h2>Content de vous retrouver </h2></div><script type="text/javascript"> window.setTimeout("location=(\'userCarte.php\');",1000) </script>';
			}
		}
	}

	/**********SI CLIQUE S'INSCRIRE*************/
	elseif (isset($_POST['boutInscription']))
	{
		//On vérifie tout de même que si compte existe
		if ($exist)//si c'est le cas idem que btn "connexion"
		{
			//le manager instancie cet utilisateur
			$ut = $managerU->getUtilisateur($Courriel, $Mdp);
			//on verifie que compte n'est pas banni avec la donnée "valide" dela bd
			if ($ut->getValide() == 1) {
				echo '<div id="notif" class="notif error"> <h2>Courriel non valide</h2></div><script type="text/javascript"> window.setTimeout("location=(\'index.php\');",1000) </script>';
			}
			elseif ($ut->getValide() == 0)
			{
				//on créer la session
				$_SESSION['emailU'] = $ut->getEmailU();
				$_SESSION['mdpU'] = $ut->getMdpU();
				$_SESSION['confirme'] = $ut->getConfirme();
				//on informe que logged est Ok pour redirection sur l'espace des gens inscrits
				$_SESSION['logged'] = true;
			//on redirige
			echo '<div id="notif" class="notif warning"> <h2>Connexion réussie, attention vous avez cliqué sur Inscription</h2></div><script type="text/javascript"> window.setTimeout("location=(\'userCarte.php\');",1100) </script>';
			}
		}
		elseif (!$exist)//Si aucun compte normalement
		{
			//on vérifie toutefois que l'email est pas pris avec verifEmailLibre()
			$emailPris = $managerU->verifEmailLibre($Courriel);
			if ($emailPris)
			{
				echo '<div id="notif" class="notif warning"> <h2>Ce courriel est déjà utilisé. </h2></div><script type="text/javascript"> window.setTimeout("location=(\'index.php\');",1000) </script>';
			}
			elseif (!$emailPris)
			{
				// date du jour
				$date = date('d-m-Y');
				//création d'une Key pour envoie de mail
				$longueurKey = 16;
				$key = "";
				for($i=1;$i<$longueurKey;$i++)
				{
					$key .= mt_rand(0,9);
				}
				//on crée un Objet utilisateur, qu'on hydrate ac les données récupérées
				$utilisateur = new Utilisateur ([
					'emailU'=>$Courriel,
					'mdpU'=>$Mdp,
					'nomU'=>'',
					'prenomU'=>'',
					'adresseU'=>'',
					'villeU'=>'',
					'cpU'=>'',
					'telU'=>'',
					'dateU'=>$date,
					'signalU'=>'1',
					'valide'=>'0',
					'confirmKey'=>$key,
					'confirme'=> '0',
					'admin'=>'0'
				]);

				//on appelle la fonction ajout avec en param l'objet utilisateur
				$managerU->add($utilisateur);
				//on envoie un mail de confirmation
				$managerU->envoieMail($Courriel, $key);
				echo '<div id="notif" class="notif Info"> <h2>Inscription réussie. Bienvenue. Un email de confirmation vous a été envoyé.</h2></div><script type="text/javascript"> window.setTimeout("location=(\'userCarte.php\');",1200) </script>';
			}
		}
	}
?>

<!DOCTYPE html>
<html lang="fr">
	<head>
		<!-- Integration de toutes les metas et autres link-->
		<?php
		$type = 'index';
		include 'head.php';
		?>
		<title>Acces'Cible-Accueil</title>
	</head>

	<body>
		<div class="site-container">
    <?php $nav_en_cours ='index';
    include'header.php'; ?>
		<div class="site-pusher">
		<div class="site-content">
    <div class="container">
	<main>
		<div class="indexMain">
		<div class="videoIndex">
			<video controls preload='auto' poster='img/logo73.svg'>
			<source src="img/rendu_anim_handicap.mp4" type="video/mp4">
			Your browser does not support the video tag.
		</video >
		</div>
		<br>

		<div class="form--connection">
			<!-- CONN ET INSCRIPTION !-->
			<form name ="formConn" method="post" id="button" style="text-align:center">
				<input class="button--index connexion" id="Courriel" type="email" name="Courriel" placeholder="dupont@gmail.com" value="<?php if (!empty($_POST['Courriel'])) {echo stripcslashes(htmlspecialchars($_POST['Courriel'], ENT_QUOTES));} ?>"  required maxlength="100"><br>
				<input class="button--index inscription" id="Mot_de_passe" type="password"
					name="Mot_de_passe" placeholder="Mot de passe" required maxlength="50"><br>
				<button class="button--index connexion" id="se_connecter" type="submit"
				name="se_connecter" value="se connecter" formaction = "index.php">Connexion</button>
				<button class="button--index inscription" type="submit" name="boutInscription" formaction="index.php">
					Inscription</button>
			</form>
		</div>
		</div>
	</main>
		<?php include 'footer.php';?>
	</div>
  </div>
  <div class="site-cache" id="site-cache"></div>
  </div>
  </div>
		<noscript>
			<div id="erreur"><b>Votre navigateur ne prend pas en charge JavaScript!</b> Veuillez activer JavaScript afin de profiter pleinement du site.</div>
		</noscript>
	</body>
	<script src="js/app.js"></script>
   <script>
    var forEach=function(t,o,r){if("[object Object]"===Object.prototype.toString.call(t))for(var c in t)Object.prototype.hasOwnProperty.call(t,c)&&o.call(r,t[c],c,t);else for(var e=0,l=t.length;l>e;e++)o.call(r,t[e],e,t)};

    var hamburgers = document.querySelectorAll(".hamburger");
    if (hamburgers.length > 0) {
      forEach(hamburgers, function(hamburger) {
        hamburger.addEventListener("click", function() {
          this.classList.toggle("is-active");
        }, false);
      });
    }
  </script>
</html>
