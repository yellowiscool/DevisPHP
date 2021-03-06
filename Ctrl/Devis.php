<?php

define('NO_LOGIN_REQUIRED', true);

class Devis
{

	public static $variables = array(
			'sujet' => '',
			'description' => '',
			'type' => '',
			'subtype' => '',
			'delai' => '',
			'financement' => '',
			'budget' => '',
			'objectif' => '',
			'nom' => '',
			'cp' => '',
			'dep' => '',
			'mail' => '',
			'tel' => '');

	protected function design_de()
	{
		$GLOBALS['PREFIX_VIEW'] = 'de_';
		CHead::delCSS('application');
		CHead::addCSS('DevisEquitable');
	}

	public function categories() {
		if (isset($_REQUEST['cat']))
		{
			$cats = Categories::$liste;
			$_REQUEST['AJAX_MODE'] = true;

			DevisView::showSousCatSelect(
				isset($cats[$_REQUEST['cat']]) ?
					$cats[$_REQUEST['cat']][1] : array());
		}
		else
		{
			CTools::hackError();
		}
	}

	public function index() {
		if (isset($_REQUEST['cat']))
		{
			$this->categories();
		}
		else
		{
			$this->design_de();

			CNavigation::setTitle(_('Nouvelle demande de devis'));

			$params = isset($_SESSION['devis_submit']) ? $_SESSION['devis_submit'] : self::$variables;
			if (isset($_REQUEST['categorie']))
				$params['type'] = $_REQUEST['categorie'];

			if (isset($_REQUEST['dep']))
				$params['dep'] = $_REQUEST['dep'];

			DevisView::showForm($params);
			unset($_SESSION['mail_error']);
		}
	}

	public function submit() {

		$devis = null;
		$modification = false;
		$ancien_devis = null;
		$etape_nouveau_devis = 0;

		if (isset($_SESSION['logged']) && isset($_POST['devis_id']))
		{
			$devis = R::load('devis', intval($_POST['devis_id']));
			if ($devis && isset($_POST['submit'])) {
				if ($_SESSION['user']->isAdmin) {
					switch ($_POST['submit']) {
					case 'Supprimer':
						$devis->etape = -1;
						R::store($devis);
						new CMessage(_('La demande de devis a été supprimée'));
						break;
					case 'Valider':
						$devis->etape = 1;
						R::store($devis);
						new CMessage(_('La demande de devis a été validée'));
						$mail = MMail::newMail()
							->setSubject(_('Votre demande de devis a été validée sur devis-equitable.com'))
							->setTo(array($devis->mail => $devis->nom))
							->setBody(_("Votre demande de devis a été validée par les administrateurs de Devis Equitable.\nNous allons sélectionner pour vous 3 artisans de qualité qui vous communiqueront un devis gratuit dans les plus brefs délais.\n\nMerci de votre confiance."));
	  					MMail::attacherHtml($mail, 'demande_validee');
						MMail::send($mail);

						$url = CNavigation::generateUrlToApp('Notifications', null, array('devis' => $devis->getID()));
						new CMessage('<a href="'.$url.'"><h3>'._('Envoyer les notifications aux artisans.').'</h3></a>', 'info');
						break;
					case 'Invalider':
						$devis->etape = 0;
						R::store($devis);
						new CMessage(_('La demande de devis a été invalidée'));
						break;
					case 'Enregistrer':
						// On sauvegarde l'ancien devis
						$etape_nouveau_devis = $devis->etape;
						$devis->etape = -2;
						R::store($devis);
						$modification = true;
						$ancien_devis = $devis;
						break;
					default:
						CTools::hackError();
					}

					if ($_POST['submit'] !== 'Enregistrer')
						CNavigation::redirectToApp('Dashboard', 'liste');
				}
				else if ($_POST['submit'] === 'Acheter')
				{
					CNavigation::redirectToApp('Dashboard', 'acheter',array(
						'id' => $devis->getID()));
				}
			}
		}

		if (CNavigation::isValidSubmit(array('type', 'nom','mail', 'tel'), $_POST))
		{
			$values = array_merge(self::$variables, $_POST);

			if (!filter_var($_POST['mail'], FILTER_VALIDATE_EMAIL))
			{
				new CMessage(_('Veuillez indiquer une adresse mail correcte.'), 'error');
				$_SESSION['devis_submit'] = $values;
				$_SESSION['mail_error'] = true;
				CNavigation::redirectToApp('Devis');
			}

			$devis = R::dispense('devis');
			$devis->etape = $etape_nouveau_devis;

			$devis->sujet = $values['sujet'];
			if (strlen($devis->sujet) === 0)
				$devis->sujet = 'Pas de sujet';

			$devis->description = $values['description'];
			$type = $values['type'];
			$subtype = $values['subtype'];
			Categories::validerIDs($type, $subtype);
			$devis->type = $type;
			$devis->subtype = $subtype;
			$devis->delai = Delais::validerID($values['delai']);
			$devis->budget = intval($values['budget']);
			$devis->financement = Financements::validerID($values['financement']);
			$devis->objectif = Objectifs::validerID($values['objectif']);
			$devis->nom = $values['nom'];
			$devis->cp = $values['cp'];
			$devis->dep = Regions::validerID($values['dep']);
			$devis->mail = $values['mail'];
			$devis->tel = $values['tel'];

			if (!$modification)
			{
				$devis->date_creation = time();
				$devis->nb_achats = 0;
			}
			else
			{
				$devis->date_creation = $ancien_devis->date_creation;
				$devis->nb_achats = $ancien_devis->nb_achats;
			}

			R::store($devis);

			if ($modification) {
				new CMessage(_('Le devis a correctement été mis à jour'));
				CNavigation::redirectToApp('Dashboard', 'view', array('id' => $devis->getId()));
			}
			else
			{
				new CMessage(_('Votre demande de devis a bien été enregistrée. Vous allez recevoir un mail de confirmation'));

				$_SESSION['enregistrement_ok'] = true;

				// On enregistre les variables de contacts pour les devis suivants (héhé)
				$_SESSION['devis_submit'] = array_merge(self::$variables, array(
					'nom' => $values['nom'],
					'cp' => $values['cp'],
					'dep' => $values['dep'],
					'mail' => $values['mail'],
					'tel' => $values['tel']));

				$mail = MMail::newMail()
					->setSubject(_('Devis en attente de validation par un administrateur.'))
  					->setTo(array($values['mail'] => $values['nom']))
  					->setBody(_("Bonjour,\n\nvotre demande de devis a bien été prise en compte.\nUn administrateur va prendre contact avec vous dans les plus brefs délais afin de valider votre demande puis sélectionner pour vous trois artisans de qualité qui vous communiqueront un devis gratuit dans les plus brefs délais.\n\nMerci de votre confiance."));
  				MMail::attacherHtml($mail, 'devis_attente_admin');

				MMail::send($mail);

				$mail = MMail::newMail()
					->setSubject(_('Nouvelle demande de devis'))
  					->setTo($GLOBALS['mail_admin'])
  					->setBody(_('Une nouvelle demande de devis a été envoyée'));
  				MMail::attacherHtml($mail, 'nouvelle_demande_devis');

				MMail::send($mail);

				CNavigation::redirectToApp('Devis', 'ok');
			}

		}
		else
		{
			CTools::hackError();
		}
	}

	public function ok() {
		if (!isset($_SESSION['enregistrement_ok'])) {
			CNavigation::redirectToApp('Devis');
		}
		unset($_SESSION['enregistrement_ok']);
		$this->design_de();
		CNavigation::setTitle(_('Enregistrement réussi'));
		DevisView::showBoutonNouveauDevis();
	}

}
?>
