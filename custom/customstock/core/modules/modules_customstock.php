<?php

require_once DOL_DOCUMENT_ROOT.'/core/class/commondocgenerator.class.php';

abstract class ModeleNumRefCustomstock
{
    public $error = '';

    public function isEnabled()
    {
        return null;
    }

    public function info(): string
    {
        global $langs;
        $langs->load("demandestock@demandestock");
        return $langs->trans('NoDescription');
    }
    
    public function canBeActivated()
    {
        return null;
    }

    public function canBeDisabled()
    {
        return null;
    }

    public function getNextValue()
    {
        global $langs;
        return $langs->trans('NoDescription');
    }
}
