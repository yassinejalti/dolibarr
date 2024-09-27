<?php


class Customstock extends CommonObject {

    public $element = "";

    public $table_element = "customstock";

    public $lines = array();

    public $fk_project;

    public $object_demande;

    public $date_demande;

    public $date_souhaite;

    public $fk_warehouse;

    public $fk_user_create;

    public $fk_user_modify;

    public $fk_user_valid;

    public $fk_statut;

    public $type_demande;

    public $picto = "customstock@customstock";

    const STATUS_DRAFT = 0;

    const STATUS_VALIDATED = 1;

    const STATUS_SENDED = 2;

    const STATUS_EN_COURS = 3;

    const STATUS_REFUSED = 4;

    const STATUS_CLOSED = 5;

    const STATUS_CANCELED = 6;

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($user, $notrigger = 0) {
        global $conf, $hookmanager;
        $error = 0;
        $now = dol_now();
        $this->db->begin();
        
        $sql = "INSERT INTO " . MAIN_DB_PREFIX . "Customstock (";
        $sql .= "ref,";
        $sql .= "fk_project,";
        $sql .= "object_demande,";
        $sql .= "date_demande,";
        $sql .= "desired_date,";
        $sql .= "type_demande,";
        $sql .= "fk_warehouse,";
        $sql .= "fk_user_create,";
        $sql .= "fk_statut,";
        $sql .= "date_creation";
        $sql .= ") VALUES (";
        $sql .= "'PROV',";
        $sql .= ($this->fk_project ? $this->fk_project : 'NULL') . ",";
        $sql .= "'" . $this->object_demande . "',";
        $sql .= "'" . $this->db->idate($this->date_demande) . "',";
        $sql .= "'" . $this->db->idate($this->date_souhaite) . "',";
        $sql .= "'" . $this->type_demande . "',";
        $sql .= ($this->fk_warehouse ? $this->fk_warehouse : 'NULL') . ",";
        $sql .= $user->id . ",";
        $sql .= self::STATUS_DRAFT . ",";
        $sql .= "'" . $this->db->idate($now) . "'";
        $sql .= ")";
        
        
        $resql = $this->db->query($sql);
        if (!$resql) {
            $error++;
            $this->errors[] = $this->db->lasterror();
        }

        if (!$error) {
            $this->id = $this->db->last_insert_id($this->db->prefix() . $this->table_element);
            $this->ref = '(PROV' . $this->id . ')';

            $sql = "UPDATE " . MAIN_DB_PREFIX . "Customstock SET ref = '" . $this->db->escape($this->ref) . "' WHERE rowid = " . $this->id;
            $resqlupd = $this->db->query($sql);
            
            if (!$resqlupd) {
                $error++;
                $this->errors[] = $this->db->lasterror();
            } else {
                $this->ref = '(PROV' . $this->id . ')';
            }
        }

        // Commit or rollback
        if ($error) {
            foreach ($this->errors as $errormsg) {
                $this->error .= ($this->error ? '<br>' : '') . $errormsg;
            }
            $this->db->rollback();
            return -1;
        } else {
            $this->db->commit();
            return 1;
        }

    }

    public function fetch($id, $ref = ""){

        $this->db->begin();

        $sql = "SELECT ";
        $sql .= "d.rowid, ";
        $sql .= "d.ref, ";
        $sql .= "d.fk_project, ";
        $sql .= "d.object_demande, ";
        $sql .= "d.desired_date, ";
        $sql .= "d.date_demande, ";
        $sql .= "d.type_demande, ";
        $sql .= "d.date_creation, ";
        $sql .= "d.fk_warehouse, ";
        $sql .= "d.fk_user_modify, ";
        $sql .= "d.fk_user_valid, ";
        $sql .= "d.fk_statut, ";
        $sql .= "d.note_private, ";
        $sql .= "d.note_public ";    

        $sql .= " from llx_customstock as d";
        $sql .= " where d.rowid = ".intval($id);


        $resql = $this->db->query( $sql);
        if ($resql) {
            $num = $this->db->num_rows( $resql);
            if ($num > 0) {
                $obj = $this->db->fetch_object($resql);
        
                $this->id = $obj->rowid;
                $this->ref = $obj->ref;
                $this->object_demande = $obj->object_demande;
        
                $this->fk_project = $obj->fk_project;
                $this->fk_warehouse = $obj->fk_warehouse;
                $this->fk_user_modify = $obj->fk_user_modify;
                $this->fk_user_valid = $obj->fk_user_valid;
        
                $this->date_demande = $obj->date_demande;
                $this->date_souhaite = $obj->desired_date;
                $this->type_demande = $obj->type_demande;
                $this->date_creation = $this->db->jdate($obj->date_creation);
        
                $this->date_modification = $this->db->jdate($obj->date_modif);
                $this->date_validation = $this->db->jdate($obj->date_valid);
                $this->status = $obj->fk_statut;
                $this->note_private = $obj->note_private;
                $this->note_public = $obj->note_public;
            }

            $this->db->free($resql);
            if($num){
                return 1;
            }
            else{
                return 0;
            }


        }else{
            $this->error = "error :".$this->db->lasterror();
            return -1 ;

        }

    }


    public function getLibStatut($mode = 0)
	{
		return $this->LibStatut($this->status, $mode);
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *    	Return label of a status (draft, validated, ...)
	 *
	 *    	@param      int			$status		Id status
	 *    	@param      int			$mode      	0=Long label, 1=Short label, 2=Picto + Short label, 3=Picto, 4=Picto + Long label, 5=Short label + Picto, 6=Long label + Picto
	 *    	@return     string		Label
	 */
	public function LibStatut($status, $mode = 0)
	{

		// Init/load array of translation of status
		if (empty($this->labelStatus) || empty($this->labelStatusShort)) {
			global $langs;
			$this->labelStatus[self::STATUS_DRAFT] = $langs->transnoentitiesnoconv("Draft");
			$this->labelStatus[1] = $langs->transnoentitiesnoconv("Validated");
			$this->labelStatus[2] = $langs->transnoentitiesnoconv("Disabled");
			$this->labelStatusShort[0] = $langs->transnoentitiesnoconv("DraftShort");
			$this->labelStatusShort[1] = $langs->transnoentitiesnoconv("ValidatedShort");
		}

		if ($status == self::STATUS_DRAFT) {
			$statusType = 'status0';
		} elseif ($status == self::STATUS_VALIDATED) {
			$statusType = 'status1';
		} elseif ($status == self::STATUS_EN_COURS) {
			$statusType = 'status4';
		} elseif ($status == self::STATUS_REFUSED) {
			$statusType = 'status9';
		} elseif ($status == self::STATUS_CLOSED) {
			$statusType = 'status6';
		}


		return dolGetStatus($this->labelStatus[$status], $this->labelStatusShort[$status], '', $statusType, $mode);
	}


}
