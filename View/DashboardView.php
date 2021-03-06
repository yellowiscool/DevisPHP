<?php
class DashboardView {
	public static function showInfosCredit($credit) {
		$credit = htmlspecialchars($credit);
		$nom = htmlspecialchars($_SESSION['user']->name);
		$nom_entreprise = $_SESSION['user']->company ? '<small>de '.htmlspecialchars($_SESSION['user']->company).'</small>' : '';
		echo <<<END
	<div class="well">
	<h3>
	Bonjour $nom $nom_entreprise
	</h3>
	<hr/>
END;
		if ($_SESSION['user']->isAdmin)
		{
			$url_paypal = CNavigation::generateUrlToApp('Paypal', 'liste');
			echo <<<END
	<a href="$url_paypal" class="btn btn-warning float_right">Consulter le journal Paypal</a>
	<p>Vous êtes administrateur du site.</p>
	
END;
		}
		else
		{
			$url_user = CNavigation::generateUrlToApp('User');
			$url_credit = CNavigation::generateUrlToApp('Paypal');
			echo <<<END
	<div class="float_right">
	<a href="$url_user" class="btn btn-primary">Modifier les informations du profil</a>
	<a href="$url_credit" class="btn btn-success">Rajouter du crédit</a>
	</div>
	<p> Vous avez <strong>$credit</strong> € de crédit.</p>

	</div>
END;
//'
		}
	}
	
	public static function showListTitle() {
		echo "<h2>Les demandes de devis que j'ai acheté</h2>\n<br/>\n";
	}
}
?>
