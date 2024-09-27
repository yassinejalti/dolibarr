<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <mailto:rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <mailto:eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <mailto:regis.houssin@inodbox.com>
 * Copyright (C) 2015      Jean-François Ferry	<mailto:jfefe@aternatik.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *	\file       demandestock/demandestockindex.php
 *	\ingroup    demandestock
 *	\brief      Home page of demandestock top menu
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/demandestock/class/demandestock.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/html.formproduct.class.php';
require_once DOL_DOCUMENT_ROOT . '/custom/demandestock/lib/demandestock.lib.php';
require_once DOL_DOCUMENT_ROOT . '/projet/class/project.class.php';

// Load translation files required by the page
$langs->loadLangs(array("demandestock@demandestock"));

$action = GETPOST('action', 'aZ09');
$id  =  GETPOST(  'id', "int" );
$max = 5;
$now = dol_now();


$obj_demande = GETPOST("object_demande");
$date_demande = dol_mktime(12,0,0, GETPOST('date_demandemonth'), GETPOST('date_demandeday'), GETPOST('date_demandeyear'));
$desired_date = dol_mktime(12,0,0, GETPOST('date_souhaitemonth'), GETPOST('date_souhaiteday'), GETPOST('date_souhaiteyear'));
$projectid = GETPOST('projectid');
$fk_warehouse = GETPOST('fk_warehouse');
$fk_project = GETPOST('fk_project');


// Security check - Protection if external user
$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0) {
	$action = '';
	$socid = $user->socid;
}

$obj = new Demandestock($db);


if ($id > 0 ) {
	$res = $obj->fetch($id);
}


// Security check (enable the most restrictive one)
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//if (!isModEnabled('demandestock')) {
//	accessforbidden('Module not enabled');
//}
//if (! $user->hasRight('demandestock', 'myobject', 'read')) {
//	accessforbidden();
//}
//restrictedArea($user, 'demandestock', 0, 'demandestock_myobject', 'myobject', '', 'rowid');
//if (empty($user->admin)) {
//	accessforbidden('Must be admin');
//}


/*
 * Actions
 */


//action=add&token=0953df526fa45c54037437a4866664a1
if ($action == 'add') {


	$errors =  0;
	if ($date_demande < 0 ){
		$errors++;
		setEventMessage("pls fill the date demande ", "errors");
	}
	if (empty($obj_demande) ){
		$errors++;
		setEventMessage("pls fill the object demande ", "errors");
	}
	if (!$errors) {
		$obj->fk_project = $projectid;
		$obj->fk_warehouse = $fk_warehouse;
		$obj->object_demande =$obj_demande;
		$obj->date_demande = $date_demande;
		$obj->date_souhaite = $desired_date;

		$res = $obj->create($user);

		if ($res > 0) {
			setEventMessage("object created successfully","success" );
			header("Location: ".$_SERVER["PHP_SELF"]."?id=".$obj->id);
		}
		else {
			setEventMessage($obj->error, "error");
		}
	}else{
		$action = "create";
	}
}




/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);
$formproduct = new FormProduct($db);

llxHeader("", $langs->trans("DemandeStockArea"), '', '', 0, 0, '', '', '', 'mod-demandestock page-index');

if ($action == "create"){


	print load_fiche_titre($langs->trans("DemandeStockArea"), '', 'demandestock.png@demandestock');


	print '<form name="demandestock" action="'.$_SERVER["PHP_SELF"].'" method="POST">';
	print '<input type="hidden" name="action" value="add">';
	print '<input type="hidden" name="token" value="'.newToken().'">';

	print dol_get_fiche_head();

	print '<table class="border centepercent">';
	print '<tbody>';

	print '<tr class="field_ref"> <td class="titlefieldcreate fieldrequired">'.$langs->trans(key: "Ref").'</td>';
	print '<td class="valuefieldcreate">'.$langs->trans (key: "Draft").'</td></tr>';
	print '</tr>';

	print '<tr class="field_object"> <td class="titlefieldcreate fieldrequired">'.$langs->trans(key: "ObjectDemande").' : </td>';
	print '<td class="valuefieldcreate">';
	print '<input name="object_demande" type="text" value="'.dol_escape_htmltag($obj_demande).'">';
	'</td></tr>';


	print '<tr class="field_date_demande"> <td class="titlefieldcreate fieldrequired">'.$langs->trans(key: "DateDemande").' : </td>';
	print '<td class="valuefieldcreate">';
	print $form->selectDate('', "date_demande" ,0, 0, 0, "date_demande", 1, 1);
	print '</td>';
	print '</tr>';

	print '<tr class="field_date_souhaite"> <td class="titlefieldcreate">'.$langs->trans(key: "DateSouhaite").' : </td>';
	print '<td class="valuefieldcreate">';
	print $form->selectDate('', "date_souhaite" ,0, 0, 0, "date_souhaite", 1, 1);
	print '</td>';
	print '</tr>';

	print '<tr class="field_warehouse"> <td class="titlefieldcreate">'.$langs->trans(key: "Warehouse").' : </td>';
	print '<td class="valuefieldcreate">';
	print img_picto($langs->trans("DefaultWarehouse"), 'stock', 'class="pictofixedwidth"');
	print $formproduct->selectWarehouses($fk_warehouse,"fk_warehouse",'',1);
	print '</td>';
	print '</tr>';

	print '<tr class="field_projectid"> <td class="titlefieldcreate">'.$langs->trans(key: "Project").' : </td>';
	print '<td class="valuefieldcreate">';
	print img_picto($langs->trans("DefaultProject"), 'project', 'class="pictofixedwidth"');
	print $form->selectProjects($projectid);
	print '</td>';
	print '</tr>';

	print '</tbody>';
	print '</table>';

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel("Save");
	print '</form>';
}
else{
	if ($obj->id > 0 ){

		$head = demandestock_prepare_head($obj);
		print dol_get_fiche_head( $head , 'card', $langs->trans("Fiche") , -1);

		$linkback = '<a href="'.dol_buildpath('/demandestock/list.php',2).'">'.$langs->trans('BackToList').'</a>';

		$morehtmlref = "";
		dol_banner_tab($obj,'id', $linkback, 1,"rowid","ref",$morehtmlref);
		print '<div class="fichecenter"><div class="fichethirdleft">';

		print '<div class="underbanner" clearboth ></div>';

		print '<table class="border centpercent">';

		print '<tbody>';

		print '<tr> <td class="titlefield" >'.$langs->trans('ObjectDemande').'  </td>';
		print '<td colspan="3" >'.$obj->object_demande.'</td></tr>';


		print '<tr> <td class="titlefield" >'.$langs->trans('DateDemande').'  </td>';
		print '<td colspan =" 3" >';
		print dol_print_date($obj->date_demande, "day");
		print '</td></tr>';

		print '<tr> <td class="titlefield" >'.$langs->trans('DateSouhaite').'  </td>';
		print '<td colspan =" 3" >';
		print dol_print_date($obj->date_souhaite, "day");
		print '</td></tr>';

		print '<tr> <td class="titlefield" >'.$langs->trans('Project').'  </td>';
		print '<td colspan =" 3" >';
		$obj_pro = new Project($db);
		$obj_pro->fetch($obj->fk_project);
		print img_picto($langs->trans('DefaultProject'),'project','class="pictofixedwidth"');
		print $obj_pro->getNomUrl();
		print '</td></tr>';

		print '<tr> <td class="titlefield" >'.$langs->trans('Warehouse').'  </td>';
		print '<td colspan =" 3" >';
		$obj_depot = new Entrepot($db);
		$obj_depot->fetch($obj->fk_warehouse);
		print img_picto($langs->trans('DefaultWarehouse'),'stock','class="pictofixedwidth"');
		print $obj_depot->getNomUrl();
		print '</td></tr>';


		print '</tbody>';

		print '</table>';

	}


}



/* BEGIN MODULEBUILDER DRAFT MYOBJECT
// Draft MyObject
if (isModEnabled('demandestock') && $user->hasRight('demandestock', 'read')) {
	$langs->load("orders");

	$sql = "SELECT c.rowid, c.ref, c.ref_client, c.total_ht, c.tva as total_tva, c.total_ttc, s.rowid as socid, s.nom as name, s.client, s.canvas";
	$sql.= ", s.code_client";
	$sql.= " FROM ".MAIN_DB_PREFIX."commande as c";
	$sql.= ", ".MAIN_DB_PREFIX."societe as s";
	$sql.= " WHERE c.fk_soc = s.rowid";
	$sql.= " AND c.fk_statut = 0";
	$sql.= " AND c.entity IN (".getEntity('commande').")";
	if ($socid)	$sql.= " AND c.fk_soc = ".((int) $socid);

	$resql = $db->query($sql);
	if ($resql)
	{
		$total = 0;
		$num = $db->num_rows($resql);

		print '<table class="noborder centpercent">';
		print '<tr class="liste_titre">';
		print '<th colspan="3">'.$langs->trans("DraftMyObjects").($num?'<span class="badge marginleftonlyshort">'.$num.'</span>':'').'</th></tr>';

		$var = true;
		if ($num > 0)
		{
			$i = 0;
			while ($i < $num)
			{

				$obj = $db->fetch_object($resql);
				print '<tr class="oddeven"><td class="nowrap">';

				$myobjectstatic->id=$obj->rowid;
				$myobjectstatic->ref=$obj->ref;
				$myobjectstatic->ref_client=$obj->ref_client;
				$myobjectstatic->total_ht = $obj->total_ht;
				$myobjectstatic->total_tva = $obj->total_tva;
				$myobjectstatic->total_ttc = $obj->total_ttc;

				print $myobjectstatic->getNomUrl(1);
				print '</td>';
				print '<td class="nowrap">';
				print '</td>';
				print '<td class="right" class="nowrap">'.price($obj->total_ttc).'</td></tr>';
				$i++;
				$total += $obj->total_ttc;
			}
			if ($total>0)
			{

				print '<tr class="liste_total"><td>'.$langs->trans("Total").'</td><td colspan="2" class="right">'.price($total)."</td></tr>";
			}
		}
		else
		{

			print '<tr class="oddeven"><td colspan="3" class="opacitymedium">'.$langs->trans("NoOrder").'</td></tr>';
		}
		print "</table><br>";

		$db->free($resql);
	}
	else
	{
		dol_print_error($db);
	}
}
END MODULEBUILDER DRAFT MYOBJECT */


print '</div><div class="fichetwothirdright">';


$NBMAX = getDolGlobalInt('MAIN_SIZE_SHORTLIST_LIMIT');
$max = getDolGlobalInt('MAIN_SIZE_SHORTLIST_LIMIT');

/* BEGIN MODULEBUILDER LASTMODIFIED MYOBJECT
// Last modified myobject
if (isModEnabled('demandestock') && $user->hasRight('demandestock', 'read')) {
	$sql = "SELECT s.rowid, s.ref, s.label, s.date_creation, s.tms";
	$sql.= " FROM ".MAIN_DB_PREFIX."demandestock_myobject as s";
	$sql.= " WHERE s.entity IN (".getEntity($myobjectstatic->element).")";
	//if ($socid)	$sql.= " AND s.rowid = $socid";
	$sql .= " ORDER BY s.tms DESC";
	$sql .= $db->plimit($max, 0);

	$resql = $db->query($sql);
	if ($resql)
	{
		$num = $db->num_rows($resql);
		$i = 0;

		print '<table class="noborder centpercent">';
		print '<tr class="liste_titre">';
		print '<th colspan="2">';
		print $langs->trans("BoxTitleLatestModifiedMyObjects", $max);
		print '</th>';
		print '<th class="right">'.$langs->trans("DateModificationShort").'</th>';
		print '</tr>';
		if ($num)
		{
			while ($i < $num)
			{
				$objp = $db->fetch_object($resql);

				$myobjectstatic->id=$objp->rowid;
				$myobjectstatic->ref=$objp->ref;
				$myobjectstatic->label=$objp->label;
				$myobjectstatic->status = $objp->status;

				print '<tr class="oddeven">';
				print '<td class="nowrap">'.$myobjectstatic->getNomUrl(1).'</td>';
				print '<td class="right nowrap">';
				print "</td>";
				print '<td class="right nowrap">'.dol_print_date($db->jdate($objp->tms), 'day')."</td>";
				print '</tr>';
				$i++;
			}

			$db->free($resql);
		} else {
			print '<tr class="oddeven"><td colspan="3" class="opacitymedium">'.$langs->trans("None").'</td></tr>';
		}
		print "</table><br>";
	}
}
*/

print '</div></div>';

// End of page
llxFooter();
$db->close();
