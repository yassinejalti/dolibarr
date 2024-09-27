<?php
/* Copyright (C) 2024 SuperAdmin
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    customstock/lib/customstock.lib.php
 * \ingroup customstock
 * \brief   Library files with common functions for CustomStock
 */


 function customstock_prepare_head(Customstock $object): array
 {
	 global $langs, $conf;

	 $langs->load("customstock@customstock");
 
	 $h = 0;
	 $head = array();
 
	 $head[$h][0] = dol_buildpath("/customstock/card.php",2)."?id=".$object->id;
	 $head[$h][1] = $langs->trans("customstock");
	 $head[$h][2] = 'card';
	 $h++;
 
	 $head[$h][0] = dol_buildpath("/customstock/note.php",2)."?id=".$object->id;
	 $head[$h][1] = $langs->trans("Notes");
	 $head[$h][2] = 'note';
	 $h++;
 
	 return $head;
 }
 

/**
 * Prepare admin pages header
 *
 * @return array
 */
function customstockAdminPrepareHead()
{
	global $langs, $conf;

	// global $db;
	// $extrafields = new ExtraFields($db);
	// $extrafields->fetch_name_optionals_label('myobject');

	$langs->load("customstock@customstock");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/customstock/admin/setup.php", 1);
	$head[$h][1] = $langs->trans("Settings");
	$head[$h][2] = 'settings';
	$h++;

	/*
	$head[$h][0] = dol_buildpath("/customstock/admin/myobject_extrafields.php", 1);
	$head[$h][1] = $langs->trans("ExtraFields");
	$nbExtrafields = is_countable($extrafields->attributes['myobject']['label']) ? count($extrafields->attributes['myobject']['label']) : 0;
	if ($nbExtrafields > 0) {
		$head[$h][1] .= ' <span class="badge">' . $nbExtrafields . '</span>';
	}
	$head[$h][2] = 'myobject_extrafields';
	$h++;
	*/

	$head[$h][0] = dol_buildpath("/customstock/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@customstock:/customstock/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@customstock:/customstock/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, null, $head, $h, 'customstock@customstock');

	complete_head_from_modules($conf, $langs, null, $head, $h, 'customstock@customstock', 'remove');

	return $head;
}

function get_ds_type($db) {
    global $conf, $langs;
    $ds_type = array();

    $sql = "SELECT * FROM ".MAIN_DB_PREFIX."c_customstock_type";
    $sql .= " WHERE active = 1";

    $resql = $db->query($sql);
    if ($resql) {
        $num_rows = $db->num_rows($resql);
        while ($obj = $db->fetch_object($resql)) {
            $ds_type[$obj->rowid] = $obj->label;
        }

        return $num_rows > 0 ? $ds_type : 0;
    } else {
        setEventMessage('Error: ' . $db->lasterror(), 'errors');
        return -1;
    }
}

function selectType($fieldname = '', $selected, $db){

	$html =
	'<select id="'.dol_escape_htmltag($fieldname).'" name="'.dol_escape_htmltag($fieldname).'" class="flat miniwith200" >';
	$html .= '<option value ="-1"></option>';

	$ds_type  = get_ds_type($db);

	if (!empty($ds_type) && is_array($ds_type)){
		foreach ($ds_type as $key => $value) {
			$select = $key == $selected ?'selected':'';
			$html .= '<option '.$select.' value="'.dol_escape_htmltag($key).'">'.dol_escape_htmltag($value).'</option>';
		}
	}
	$html.='</select>';
	include_once DOL_DOCUMENT_ROOT.'/core/lib.php';
	$html .= ajax_combobox($fieldname);

	return $html;
}

function customstockPrepareHead($object)
{
	global $db, $langs, $conf;

	$langs->load("customstock@customstock");

	$showtabofpagecontact = 1;
	$showtabofpagenote = 1;
	$showtabofpagedocument = 1;
	$showtabofpageagenda = 1;

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/customstock/customstock_card.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("customstock");
	$head[$h][2] = 'card';
	$h++;

	if ($showtabofpagecontact) {
		$head[$h][0] = dol_buildpath("/customstock/customstock_contact.php", 1).'?id='.$object->id;
		$head[$h][1] = $langs->trans("Contacts");
		$head[$h][2] = 'contact';
		$h++;
	}

	if ($showtabofpagenote) {
		if (isset($object->fields['note_public']) || isset($object->fields['note_private'])) {
			$nbNote = 0;
			if (!empty($object->note_private)) {
				$nbNote++;
			}
			if (!empty($object->note_public)) {
				$nbNote++;
			}
			$head[$h][0] = dol_buildpath('/customstock/customstock_note.php', 1).'?id='.$object->id;
			$head[$h][1] = $langs->trans('Notes');
			if ($nbNote > 0) {
				$head[$h][1] .= (!getDolGlobalInt('MAIN_OPTIMIZEFORTEXTBROWSER') ? '<span class="badge marginleftonlyshort">'.$nbNote.'</span>' : '');
			}
			$head[$h][2] = 'note';
			$h++;
		}
	}

	if ($showtabofpagedocument) {
		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
		require_once DOL_DOCUMENT_ROOT.'/core/class/link.class.php';
		$upload_dir = $conf->customstock->dir_output."/customstock/".dol_sanitizeFileName($object->ref);
		$nbFiles = count(dol_dir_list($upload_dir, 'files', 0, '', '(\.meta|_preview.*\.png)$'));
		$nbLinks = Link::count($db, $object->element, $object->id);
		$head[$h][0] = dol_buildpath("/customstock/customstock_document.php", 1).'?id='.$object->id;
		$head[$h][1] = $langs->trans('Documents');
		if (($nbFiles + $nbLinks) > 0) {
			$head[$h][1] .= '<span class="badge marginleftonlyshort">'.($nbFiles + $nbLinks).'</span>';
		}
		$head[$h][2] = 'document';
		$h++;
	}

	if ($showtabofpageagenda) {
		$head[$h][0] = dol_buildpath("/customstock/customstock_agenda.php", 1).'?id='.$object->id;
		$head[$h][1] = $langs->trans("Events");
		$head[$h][2] = 'agenda';
		$h++;
	}

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'customstock@customstock');

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'customstock@customstock', 'remove');

	return $head;
}
