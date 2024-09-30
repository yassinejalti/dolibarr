<?php

require_once 'modules_customstock.php';

class mod_customstock_standard extends ModeleNumRefCustomstock
{
    var $version = '1.0';
    var $prefix = 'DS';
    var $error = '';
    var $nom = 'Number';

    public function isEnabled()
    {
        return null;
    }

    public function info(): string
    {
        global $langs;
        $langs->load("customstock@customstock");
        return '';
    }

    public function getNextValue()
    {
        global $conf, $langs, $db;
        $langs->load("customstock@customstock");
        $posindice = 8;
            
        $posindice = strlen($this->prefix) + 6;
        $sql = "SELECT MAX(CAST(SUBSTRING(ref FROM ".$posindice.") AS SIGNED)) as max";
        $sql .= " FROM ".MAIN_DB_PREFIX."customstock";
        $sql .= " WHERE ref LIKE '".$db->escape($this->prefix)."_____%'";

        $resql = $db->query($sql);
        if ($resql) {
            $obj = $db->fetch_object($resql);
            if ($obj) {
                $max = intval($obj->max);
                if (!$max) {
                    $max = 0;
                }
            } else {
                $max = 0;
            }
        } else {
            dol_syslog("mod_customstock_standard::getNextValue", LOG_DEBUG);
            return -1;
        }

        $date = time();
        $yymm = dol_print_date($date, "%y%m");

        if ($max > (pow(10, 4) - 1)) {
            $num = $max + 1;
        } else {
            $num = sprintf("%04s", $max + 1);
        }

        dol_syslog("mod_customstock_standard::getNextValue return ".$this->prefix.$yymm."-".$num);
        return $this->prefix.$yymm."-".$num;
    }    
}