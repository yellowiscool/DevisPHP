<?php
class DevisView {
	
	const texte_categorie = 'Pas de sous-catégorie';

	public static function showSousCatSelect($categorie = array(), $selected_id = null, $all_allowed = false) {

		if ($all_allowed)
		{
			$selected = $selected_id === '*' ? ' selected' : ''; 
			echo "\t\t\t\t\t<option value=\"*\"$selected>Toutes</option>\n";
		}

		$selected = $selected_id === -1 ? ' selected' : ''; 
		echo '<option value="-1"',$selected,'>',self::texte_categorie,'</option>';

			foreach ($categorie as $id => $sc) {
				$hsc = htmlspecialchars($sc);
				$selected = $id === $selected_id ? ' selected' : '';
				echo "\n<option value=\"$id\"$selected>$hsc</option>";
			}
	}

	public static function showCatSelect($a_selected_id, $a_sub_selected_id, $adisabled = false, $all_allowed = false)
	{
		$selected_id = $a_selected_id;
		$sub_selected_id = $a_sub_selected_id;

		Categories::validerIDs($selected_id, $sub_selected_id);

		$selected_id = $a_selected_id === '*' ? -1 : $selected_id;
		$sub_selected_id = $a_sub_selected_id === '*' ? '*' : $sub_selected_id;
		
		$disabled = $adisabled ? ' disabled' : '';
		
		if ($adisabled)
		{
			$c = Categories::$liste[$selected_id];
			echo "\t\t\t<input type=\"text\" name=\"type\" id=\"input_type\" class=\"span4\" required$disabled value=\"",htmlspecialchars($c[0]),"\"/>\n";
			$sc = isset($c[1][$sub_selected_id]) ? $c[1][$sub_selected_id] : self::texte_categorie;
			echo "\t\t\t<input type=\"text\" name=\"subtype\" id=\"input_subtype\" class=\"span4\"$disabled value=\"",htmlspecialchars($sc),"\"/>\n";
		}
		else
		{
			echo "\t\t\t<select name=\"type\" id=\"input_type\" class=\"span4\" required$disabled>\n";
			if ($all_allowed)
			{
				$selected = $a_selected_id === '*' ? ' selected' : ''; 
				echo "\t\t\t\t\t<option value=\"*\"$selected>Toutes</option>\n";
			}

			$first = null;

			foreach (Categories::$liste as $id => $c) {
				$selected = '';
				if ($id === $selected_id) {
					$selected = ' selected';
					$first = $c[1];
				}
				$hc = htmlspecialchars($c[0]);
				echo "\t\t\t\t<option value=\"$id\"$selected>$hc</option>\n";
			}
			echo "\t\t\t</select>\n";
			
			echo "\t\t\t<select name=\"subtype\" id=\"input_subtype\" class=\"span4\"$disabled>\n";
			self::showSousCatSelect(is_null($first) ? array() : $first, $sub_selected_id, $all_allowed);
			echo "\t\t\t</select>\n";
		}
	}

	public static function showDepartementSelect($a_id_dep, $adisabled = false, $all_allowed = false)
	{
		$id_dep = $a_id_dep === '*' ? -1 : Regions::validerID($a_id_dep);

		$disabled = $adisabled ? ' disabled' : '';
		if (!$adisabled)
		{
			echo "\t\t\t<select name=\"dep\" id=\"input_dep\" class=\"span4\"$disabled>\n";
			if ($all_allowed)
			{
				$selected = $a_id_dep === '*' ? ' selected' : ''; 
				echo "\t\t\t\t\t<option value=\"*\"$selected>Tous</option>\n";
			}
		}

		foreach (Regions::$liste as $region => $departements) {
			if (!$adisabled) {
				$hr = htmlspecialchars($region);
				echo "\t\t\t\t<optgroup label=\"$hr\">\n";
			}

			foreach ($departements as $id => $dep) {
				$hd = htmlspecialchars($dep);
				$id_display = $id === 201 ? '2A' : ($id === 202 ? '2B' : ($id < 10 ? '0'.$id : $id)); 
				if ($adisabled && $id_dep === $id)
				{
					echo "\t\t\t<input type=\"text\" name=\"dep\" id=\"input_dep\" class=\"span4\" value=\"$id_display - $hd\"$disabled/>\n";
				}
				else
				{
					$selected = $id === $id_dep ? ' selected' : '';
					echo "\t\t\t\t\t<option value=\"$id\"$selected>$id_display - $hd</option>\n";
				}
			}
			if (!$adisabled) echo "\t\t\t\t</optgroup>\n";
		}
		if (!$adisabled) echo "\t\t\t</select>\n";
	}

	public static function showForm($values, $mode = 'nouveau', $devis_id = null, $adisabled = false, $valider = 0, $masquer_infos = false) {

		$values = array_map('htmlspecialchars', $values); // <3

		$disabled = $adisabled ? ' disabled' : '';

		$url_submit = CNavigation::generateUrlToApp('Devis', 'submit');
		echo "<form action=\"$url_submit\" name=\"registration_form\" method=\"post\" class=\"form-horizontal\">\n";

		if ($devis_id) {
			echo "\t<input type=\"hidden\" name=\"devis_id\" value=\"",intval($devis_id),"\"/>\n";
		}

		echo <<<END
<fieldset>
	<legend>Demande</legend>
	<div class="control-group">
		<label for="input_sujet" class="control-label">Sujet</label>
		<div class="controls">
			<input name="sujet" id="input_sujet" type="text" autofocus class="span6" value="{$values['sujet']}" maxlength="80"$disabled/>
		</div>
	</div>
	<div class="control-group">
		<label for="input_description" class="control-label">Description</label>
		<div class="controls">
			<textarea name="description" id="input_description" class="span6" rows="5"$disabled>{$values['description']}</textarea>
		</div>
	</div>
	<div class="control-group">
		<label for="input_type" class="control-label">Catégorie</label>
		<div class="controls">
END;
			self::showCatSelect($values['type'], $values['subtype'], $adisabled);

			if (!$adisabled)
			{
				echo "\t\t\t<p class=\"help-block\">Sélectionnez une catégorie et une sous-catégorie pour améliorer la visibilité de votre demande de devis pour les professionnels.</p>\n";
			}
		echo <<<END
		</div>
	</div>
</fieldset>
<fieldset>
	<legend>Informations ?</legend>
	<div class="control-group">
		<label for="input_delai" class="control-label">Délai prévu</label>
		<div class="controls">
END;
		$id_delai = Delais::validerID($values['delai']);
		if ($adisabled)
		{
			echo "\t\t\t<input type=\"text\" name=\"delai\" id=\"input_delai\" class=\"span4\" value=\"",
			htmlspecialchars(Delais::$liste[$id_delai]),"\"$disabled>\n";
		}
		else
		{
			echo "\t\t\t<select name=\"delai\" id=\"input_delai\" class=\"span4\"$disabled>\n";
				foreach (Delais::$liste as $id => $c) {
					$selected = $id_delai === $id ? ' selected' : '';
					$hc = htmlspecialchars($c);
					echo "\t\t\t\t<option value=\"$id\"$selected>$hc</option>\n";
				}
			echo "\t\t\t</select>\n";
		}
		echo <<<END
		</div>
	</div>
	
	<div class="control-group">
		<label for="input_budget" class="control-label">Budget</label>
		<div class="controls">
			<div class="input-prepend">
                <span class="add-on">€</span>
END;
			$type = $adisabled ? 'text' : 'number';
			echo "\t\t\t<input name=\"budget\" class=\"span2\" id=\"input_budget\" size=\"16\" type=\"$type\" min=\"0\" value=\"{$values['budget']}\",$disabled>\n";

			if (!$adisabled) {
				echo "\t\t\t<p class=\"help-block\">Saisissez le budget que vous souhaitez investir. Laissez le champ vide pour ne pas indiquer de budget.</p>\n";
			}
		echo <<<END
              </div>
		</div>
	</div>
	
	<div class="control-group">
		<label for="input_financement" class="control-label">Financement</label>
		<div class="controls">
END;
		$id_financement = Financements::validerID($values['financement']);

		if ($adisabled)
		{
			echo "\t\t\t<input type=\"text\" name=\"financement\" id=\"input_financement\" class=\"span4\" value=\"", htmlspecialchars(Financements::$liste[$id_financement]),"\"$disabled>\n";
		}
		else
		{
			echo "\t\t\t<select name=\"financement\" id=\"input_financement\" class=\"span4\"$disabled>\n";
			foreach (Financements::$liste as $id => $c) {
				$selected = $id_financement === $id ? ' selected' : '';
				$hc = htmlspecialchars($c);
				echo "\t\t\t\t<option value=\"$id\"$selected>$hc</option>\n";
			}
			echo "\t\t\t</select>\n";
		}
		echo <<<END
		</div>
	</div>
	
	<div class="control-group">
		<label for="input_objectif" class="control-label">Objectif de la demande</label>
		<div class="controls">
END;
		$id_objectif = Objectifs::validerID($values['objectif']);

		if ($adisabled)
		{
			echo "\t\t\t<input type=\"text\" name=\"objectif\" id=\"input_objectif\" class=\"span4\" value=\"",
			htmlspecialchars(Objectifs::$liste[$id_objectif]),"\"$disabled>\n";
		}
		else
		{
			echo "\t\t\t<select name=\"objectif\" id=\"input_objectif\" class=\"span4\"$disabled>\n";
			foreach (Objectifs::$liste as $id => $c) {
				$selected = $id_objectif === $id ? ' selected' : '';
				$hc = htmlspecialchars($c);
				echo "\t\t\t\t<option value=\"$id\"$selected>$hc</option>\n";
			}
			echo "\t\t\t</select>\n";
		}
		echo <<<END
		</div>
	</div>

</fieldset>
<fieldset>
	<legend>Coordonées</legend>
END;
if (!$masquer_infos)
{
	echo <<<END
	<div class="control-group">
		<label for="input_nom" class="control-label">Nom et Prénom</label>
		<div class="controls">
			<input name="nom" id="input_nom" type="text" required class="span4" value="{$values['nom']}" maxlength="80"$disabled/>
		</div>
	</div>
END;
}
echo <<<END
	<div class="control-group">
		<label for="input_cp" class="control-label">Code postal</label>
		<div class="controls">
			<input name="cp" id="input_cp" type="text" class="span1" value="{$values['cp']}"$disabled/>
		</div>
	</div>
	<div class="control-group">
		<label for="input_dep" class="control-label">Département</label>
		<div class="controls">
END;
		self::showDepartementSelect($values['dep'], $adisabled);
		echo <<<END
		</div>
	</div>
END;
if ($masquer_infos)
{
	echo <<<END
	<div class="alert alert-info">Vous devez acheter la demande de devis pour obtenir les coordonnées <em>complètes</em>.</div> 
END;
}
else
{
	$class_error = isset($_SESSION['mail_error']) ? ' error' : '';
	$msg_error = isset($_SESSION['mail_error']) ? 'Veuillez entrer une adresse email valide. Elle sera utilisée pour vous proposer les offres de devis.' : '';
	$autofocus_error = isset($_SESSION['mail_error']) ? ' autofocus' : 'L\'adresse email sera utilisée pour vous proposer les offres de devis.';

	echo <<<END
	<div class="control-group$class_error">
		<label for="input_mail" class="control-label">Adresse email</label>
		<div class="controls">
			<input name="mail" id="input_mail" type="email" required class="span4" value="{$values['mail']}"$autofocus_error maxlength="80"$disabled/>
END;
		if (isset($_SESSION['logged']) && strlen($values['mail']) > 0)
		{
			echo "\t\t\t<a href=\"mailto:",
			htmlspecialchars($values['nom']),
			rawurlencode(' <'.$values['mail'].'>'),"\" class=\"btn btn-inverse\">Envoyer un email</a>\n";
		}
		echo <<<END
			<p class="help-block">$msg_error</p>
		</div>
	</div>
	<div class="control-group">
		<label for="input_tel" class="control-label">Téléphone</label>
		<div class="controls">
			<input name="tel" id="input_tel" type="tel" class="span4" value="{$values['tel']}" maxlength="80"$disabled/>
		</div>
	</div>
END;
}
echo <<<END
</fieldset>
	<div class="form-actions">
END;
	if ($mode !== 'nouveau')
			echo "\t\t", '<a href="',CNavigation::generateUrlToApp('Dashboard', 'liste'),'" class="btn btn-large">Retour à la liste</a>', "\n";

	switch ($mode)
	{
		case 'nouveau':
			echo "\t\t", '<input type="submit" class="btn btn-large btn-primary" name="submit" value="Enregistrer" />', "\n";
			break;

		case 'artisan':
			echo "\t\t", '<input type="submit" class="btn btn-large btn-warning" name="submit" value="Acheter" />', "\n";
			break;

		case 'admin':
			if ($valider == 0)
			{
				echo "\t\t", '<input type="submit" class="btn btn-large btn-success" name="submit" value="Valider" />', "\n";
				echo "\t\t", '<input type="submit" class="btn btn-large btn-danger" name="submit" value="Supprimer" />', "\n";
			}
			else if ($valider == 1)
			{
				echo "\t\t", '<input type="submit" class="btn btn-large btn-warning" name="submit" value="Invalider" />', "\n";
			}

			echo "\t\t", '<input type="submit" class="btn btn-large btn-primary" name="submit" value="Enregistrer" />', "\n";
			break;

		}
	echo "\t</div>\n</form>\n";
	}

	public static function showBoutonNouveauDevis() {
	echo <<<END
	<a href="" class="btn btn-primary btn-large">Nouvelle demande de devis</a>
END;
	}
	
	public static function showList($devis, $montrer_achetes = true)
	{
		if ($devis)
		{
			CHead::addJS('jquery.tablesorter.min');
			echo <<<END
			<table class="table table-striped table-bordered devis_list">
				<thead><tr>
					<th class="header">Id</th>
					<th class="header yellow">Date</th>
					<th class="header orange">Code postal</th>
					<th class="header green">Catégorie</th>
					<th class="header blue">Sujet</th>
					<th class="header purple">Budget</th>
				</tr></thead>
				<tbody>
END;
			foreach ($devis as $d)
			{
				$url = CNavigation::generateUrlToApp('Dashboard', 'view', array('id' => $d->getID()));
				$c = Categories::$liste[$d['type']];
				$sc = isset($c[1][$d['subtype']]) ? $c[1][$d['subtype']] : self::texte_categorie;

				$c_achete = $montrer_achetes && in_array($d, $_SESSION['user']->sharedDevis);
				$achete =  $c_achete ? ' class="achete"' : '';
				$hdate = AbstractView::formateDate($d['date_creation']);
				$hdate_code = $d['date_creation'] ? intval($d['date_creation']) : 0;
				$id = $d->getID();
				$hcp = htmlspecialchars($d['cp']);
				$hcat = htmlspecialchars($c[0]).' <br/> '.htmlspecialchars($sc);
				$hsujet = wordwrap(htmlspecialchars($d['sujet']),30, "<br/>", true);
				$hbudget = htmlspecialchars($d['budget']);
				
				echo <<<END
	<tr$achete>
		<td><a href="$url">
			<span class="badge badge-info">$id</span>
END;
				if ($c_achete) echo '&nbsp;<span class="badge badge-warning">Acheté</span>';	
				echo <<<END
		</a></td>
		<td>
			<span style="display:none;">$hdate_code</span>
			<a href="$url">$hdate</a>
		</td>
		<td><a href="$url">$hcp</a></td>
		<td><a href="$url">$hcat</a></td>
		<td><a href="$url">$hsujet</a></td>
		<td><a href="$url">$hbudget</a></td>
	</tr>
END;
			}

			echo "</tbody></table>";
		}
		else
		{
			echo <<<END
<div class="alert alert-block alert-warning">
<p>Il n'y a aucune demande de devis.</p>
</div>
END;
//'
		}
	}

	public static function showChoixListe($etape) {

		echo '<a href="',
			CNavigation::generateMergedUrl('Dashboard', 'liste', array('etape' => 'validees')),
			'" class="btn btn-large btn-inverse"',$etape === '= 1' ? 'disabled': '',
			'><i class="icon-white icon-ok"></i> Validés</a>', "\n\t";
		
		echo '<a href="',
			CNavigation::generateMergedUrl('Dashboard', 'liste', array('etape' => 'validation')),
			'" class="btn btn-large btn-inverse"',$etape === '= 0' ? 'disabled': '',
			'><i class="icon-white icon-inbox"></i> À valider</a>', "\n\t";
		
		echo '<a href="',
			CNavigation::generateMergedUrl('Dashboard', 'liste', array('etape' => 'historique')),
			'" class="btn btn-large btn-inverse"',$etape === '< -1' ? 'disabled': '',
			'><i class="icon-white icon-list-alt"></i> Historique</a>', "\n\t";
		
		echo '<a href="',
			CNavigation::generateMergedUrl('Dashboard', 'liste', array('etape' => 'poubelle')),
			'" class="btn btn-large btn-inverse"',$etape === '= -1' ? 'disabled': '',
			'><i class="icon-white icon-trash"></i> Poubelle</a>', "\n\t";

		echo '<hr class="small-hr"/>';
	}

	public static function showFormSelectionList($type, $subtype, $dep) {
	$action_form = CNavigation::generateMergedUrl('Dashboard', 'liste', array(
		'type' => '-type-',
		'subtype' => '-subtype-',
		'dep' => '-dep-'));
	$reset_url = CNavigation::generateUrlToApp('Dashboard', 'liste', array(
		'type' => '*',
		'subtype' => '*',
		'dep' => '*'));
	echo <<<END
<form action="$action_form" name="selection_categories" method="get" class="form-horizontal">
<fieldset>
	<h4>Filtrer</h4>
	<div class="control-group">
		<label for="input_type" class="control-label">Département</label>
		<div class="controls">
END;
		self::showDepartementSelect($dep, false, true);
		echo <<<END
		</div>
	</div>
	<div class="control-group">
		<label for="input_type" class="control-label">Catégorie</label>
		<div class="controls">
END;
		self::showCatSelect($type, $subtype, false, true);
		echo <<<END
		<a href="$reset_url" class="btn btn-inverse float_right">Reset</a>
		</div>
	</div>
</fieldset>
END;
	}
}
?>
