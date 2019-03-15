<? 
    $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?>";  
	//**********************************************************************************************************
    // V1.0 : Script pluviométrie
    //*************************************** ******************************************************************
    $api_cumul = getArg("apic", true, 'undefined');
	$action = getArg("action", true, '');
    $api_script = getArg('eedomus_controller_module_id'); 
	$arg_value = getArg("value", false, 0);
	$type = getArg("type", false, '');
    $xml .= "<MYRAIN>";
	if ($action == 'updateconso') {
		$maintenant = date("H").":".date("i");
		$xml .= "<APPEL>".$maintenant." ".$api_script."</APPEL>";
	}
    // s'assurer qu'il y a bien un compteur cumulé défini
	$type_cumul = false;
	if ($api_cumul != 'undefined' && $api_cumul != '' && $api_cumul != 'plugin.parameters.APIC') {
		$type_cumul = true;
		$api_compteur = $api_cumul;
		$xml .= "<COMPTEUR>API ".$api_compteur."</COMPTEUR>";
	}
	if ($type_cumul) {
		if ($action == 'updateconso') {
			$mesure = "";
			// chargement des données du compteur, initialisation si nécessaire
          	$preload = loadVariable('MYRAING_RELEVES_'.$api_compteur);
			if ($preload != '' && substr($preload, 0, 8) != "## ERROR") {	
				$tab_releves = $preload;
			} else {
				$tab_releves = array ("jour_global" => 0.00, "jour_prec_global" => 0.0, 
									 "mois_global" => 0.00, "mois_prec_global" => 0.0,
									 "annee_global" => 0.00, "annee_prec_global" => 0.0, "annee_prec_global_2" => 0.0, "annee_prec_global_3" => 0.0, "annee_prec_global_4" => 0.0, "annee_prec_global_5" => 0.0,
									 "lastmesure" => date('d')."-00:00");
			}
			//**********************************************************************************
			// Mise à jour de la consommation
            $value = getValue($api_compteur);
            $etat_compteur = $value['value'];
            $xml .= "<VALCOMPTEUR>".$etat_compteur."</VALCOMPTEUR>";
            $releve_conso = 0;
            // restitution du précédent relevé du compteur 
			$preload = loadVariable('MYRAING_LASTRELEVE_'.$api_compteur);
			if ($preload == 0 || ($preload != '' && substr($preload, 0, 8) != "## ERROR")) {
				$dernier_releve = $preload;
			} else {
				$dernier_releve = $etat_compteur;
			}
			$xml .= "<LASTVALCOMPTEUR>".$dernier_releve."</LASTVALCOMPTEUR>";
			// si compteur < dernier relevé
			if ($etat_compteur < $dernier_releve) {
				$releve_conso = round($etat_compteur, 1);
			} else {
				$releve_conso = round(($etat_compteur - $dernier_releve), 1);
			}
			// mise à jour dernier relevé
			saveVariable('MYRAING_LASTRELEVE_'.$api_compteur, $etat_compteur);
			$lasttime = substr($tab_releves['lastmesure'], 3, 5);
			$lastday = substr($tab_releves['lastmesure'], 0, 2);
			$razday = false;
			$razmois = false;
			$razannee = false;
			// si dernière mesure veille
			if ($lastday != date('d')) {
				$razday = true;
				if (date('j') == 1) {
					$razmois = true;
				}
				if (date('n') == 1 && $razmois) {
					$razannee = true;
				}
			}
				
			$releve_jour_global = $tab_releves['jour_global'];
			$releve_jour_prec_global = $tab_releves['jour_prec_global'];
			$releve_mois_global = $tab_releves['mois_global'];
			$releve_mois_prec_global = $tab_releves['mois_prec_global'];
			$releve_annee_global = $tab_releves['annee_global'];
			$releve_annee_prec_global = $tab_releves['annee_prec_global'];
			$releve_annee_prec_global_2 = $tab_releves['annee_prec_global_2'];
			$releve_annee_prec_global_3 = $tab_releves['annee_prec_global_3'];
			$releve_annee_prec_global_4 = $tab_releves['annee_prec_global_4'];
			$releve_annee_prec_global_5 = $tab_releves['annee_prec_global_5'];
			// ajout de la consommation au compteur 
			$releve_jour_global += $releve_conso;
			$releve_mois_global += $releve_conso;
			$releve_annee_global += $releve_conso;
			// REMISES A ZERO
			if ($razday) {
				$releve_jour_prec_global = $releve_jour_global;
				$releve_jour_global = 0;
			}
			if ($razmois) {
				$releve_mois_prec_global = $releve_mois_global;
				$releve_mois_global = 0;
			}
			if ($razannee) {
				$releve_annee_prec_global_5 = $releve_annee_prec_global_4;
				$releve_annee_prec_global_4 = $releve_annee_prec_global_3;
				$releve_annee_prec_global_3 = $releve_annee_prec_global_2;
				$releve_annee_prec_global_2 = $releve_annee_prec_global;
				$releve_annee_prec_global = $releve_annee_global;
				$releve_annee_global = 0;
			}
			$tab_releves['jour_global'] = $releve_jour_global;
			$tab_releves['jour_prec_global'] = $releve_jour_prec_global;
			$tab_releves['mois_global'] = $releve_mois_global;
			$tab_releves['mois_prec_global'] = $releve_mois_prec_global;
			$tab_releves['annee_global'] = $releve_annee_global;
			$tab_releves['annee_prec_global'] = $releve_annee_prec_global;
			$tab_releves['annee_prec_global_2'] = $releve_annee_prec_global_2;
			$tab_releves['annee_prec_global_3'] = $releve_annee_prec_global_3;
			$tab_releves['annee_prec_global_4'] = $releve_annee_prec_global_4;
			$tab_releves['annee_prec_global_5'] = $releve_annee_prec_global_5;
			$tab_releves['lastmesure'] = date('d')."-".$maintenant;
			saveVariable('MYRAING_RELEVES_'.$api_compteur, $tab_releves);
			$mesure = "Conso ".$releve_conso." - Jour ".$releve_jour_global." mm";
			$xml .= "<STATUT>".$mesure."</STATUT>";
		}
	} else if ($action == 'updateconso') {
	   	$xml .= "<STATUT>En attente compteur...</STATUT>";
		$xml .= "<COMPTEUR>INCONNU</COMPTEUR>";
	}
		
	// ***********************************************************************************
    // lecture des capteurs
    if ($action == 'read') {
        $tab_init = array ("jour_global" => 0.0, "jour_prec_global" => 0.0, 
								   "mois_global" => 0.0, "mois_prec_global" => 0.0,
								   "annee_global" => 0.0, "annee_prec_global" => 0.0, "annee_prec_global_2" => 0.0, "annee_prec_global_3" => 0.0, "annee_prec_global_4" => 0.0, "annee_prec_global_5" => 0.0,
								   "lastmesure" => date('d')."-00:00");
     	$preload = loadVariable('MYRAING_RELEVES_'.$api_compteur);
		if ($preload != '' && substr($preload, 0, 8) != "## ERROR") {
			$tab_init = $preload;
		}
        $xml .= "<JOUR_GLOBAL>".round($tab_init['jour_global'],1)."</JOUR_GLOBAL>";
        $xml .= "<MOIS_GLOBAL>".round($tab_init['mois_global'],1)."</MOIS_GLOBAL>";
        $xml .= "<ANNEE_GLOBAL>".round($tab_init['annee_global'],1)."</ANNEE_GLOBAL>";
        $xml .= "<ANNEE_PREC_GLOBAL>".round($tab_init['annee_prec_global'],1)."</ANNEE_PREC_GLOBAL>";
		$xml .= "<ANNEE_PREC_GLOBAL_2>".round($tab_init['annee_prec_global_2'],1)."</ANNEE_PREC_GLOBAL_2>";
		$xml .= "<ANNEE_PREC_GLOBAL_3>".round($tab_init['annee_prec_global_3'],1)."</ANNEE_PREC_GLOBAL_3>";
		$xml .= "<ANNEE_PREC_GLOBAL_4>".round($tab_init['annee_prec_global_4'],1)."</ANNEE_PREC_GLOBAL_4>";
		$xml .= "<ANNEE_PREC_GLOBAL_5>".round($tab_init['annee_prec_global_5'],1)."</ANNEE_PREC_GLOBAL_5>";
        $xml .= "<JOUR_PREC_GLOBAL>".round($tab_init['jour_prec_global'],1)."</JOUR_PREC_GLOBAL>";
        $xml .= "<MOIS_PREC_GLOBAL>".round($tab_init['mois_prec_global'],1)."</MOIS_PREC_GLOBAL>";
		$xml .= "<LASTMESURE>".$tab_init['lastmesure']."</LASTMESURE>";
		$preload = loadVariable('MYRAING_LASTRELEVE_'.$api_compteur);
		$xml .= "<LASTRELEVE>".$preload."</LASTRELEVE>";
	}
	// ***********************************************************************************
    // mise à zéro manuelle
    if ($action == 'raz') {
		
		$tab_init = array ("jour_global" => 0.0, "jour_prec_global" => 0.0, 
						   "mois_global" => 0.0, "mois_prec_global" => 0.0,
						   "annee_global" => 0.0, "annee_prec_global" => 0.0, "annee_prec_global_2" => 0.0, "annee_prec_global_3" => 0.0, "annee_prec_global_4" => 0.0, "annee_prec_global_5" => 0.0,
						   "lastmesure" => date('d')."-00:00");
		saveVariable('MYRAING_RELEVES_'.$api_compteur, $tab_init);
        saveVariable('MYRAING_LASTRELEVE_'.$api_compteur, $arg_value);
		$xml .= "<RAZ_RESULT>API ".$api_compteur." - ".$arg_value." OK</RAZ_RESULT>";
		
	}
	// ***********************************************************************************
	// mise à jour manuelle
    if ($action == 'maj') {
		$tab_reinit = array ("jour_global" => 0.0, "jour_prec_global" => 0.0, 
							"mois_global" => 0.0, "mois_prec_global" => 0.0,
							"annee_global" => 0.0, "annee_prec_global" => 0.0, "annee_prec_global_2" => 0.0, "annee_prec_global_3" => 0.0, "annee_prec_global_4" => 0.0, "annee_prec_global_5" => 0.0,
							"lastmesure" => date('d')."-00:00");
		$xml .= "<MAJ>".$type." - ".$arg_value."</MAJ>";
		$preload = loadVariable('MYRAING_RELEVES_'.$api_compteur);
		if ($preload != '' && substr($preload, 0, 8) != "## ERROR") {
			$tab_reinit = $preload;
			if ($type == 'JOUR_GLOBAL' && $arg_value != "") {
				$tab_reinit['jour_global'] = $arg_value;
				$xml .= "<MAJ_RESULT>OK</MAJ_RESULT>";
			}
			if ($type == 'JOUR_PREC_GLOBAL' && $arg_value != "") {
				$tab_reinit['jour_prec_global'] = $arg_value;
				$xml .= "<MAJ_RESULT>OK</MAJ_RESULT>";
			}
			if ($type == 'MOIS_GLOBAL' && $arg_value != "") {
				$tab_reinit['mois_global'] = $arg_value;
				$xml .= "<MAJ_RESULT>OK</MAJ_RESULT>";
			}
			if ($type == 'MOIS_PREC_GLOBAL' && $arg_value != "") {
				$tab_reinit['mois_prec_global'] = $arg_value;
				$xml .= "<MAJ_RESULT>OK</MAJ_RESULT>";
			}
			if ($type == 'ANNEE_GLOBAL' && $arg_value != "") {
				$tab_reinit['annee_global'] = $arg_value;
				$xml .= "<MAJ_RESULT>OK</MAJ_RESULT>";
			}
			if ($type == 'ANNEE_PREC_GLOBAL' && $arg_value != "") {
				$tab_reinit['annee_prec_global'] = $arg_value;
				$xml .= "<MAJ_RESULT>OK</MAJ_RESULT>";
			}
			if ($type == 'ANNEE_PREC_GLOBAL_2' && $arg_value != "") {
				$tab_reinit['annee_prec_global_2'] = $arg_value;
				$xml .= "<MAJ_RESULT>OK</MAJ_RESULT>";
			}
			if ($type == 'ANNEE_PREC_GLOBAL_3' && $arg_value != "") {
				$tab_reinit['annee_prec_global_3'] = $arg_value;
				$xml .= "<MAJ_RESULT>OK</MAJ_RESULT>";
			}
			if ($type == 'ANNEE_PREC_GLOBAL_4' && $arg_value != "") {
				$tab_reinit['annee_prec_global_4'] = $arg_value;
				$xml .= "<MAJ_RESULT>OK</MAJ_RESULT>";
			}
			if ($type == 'ANNEE_PREC_GLOBAL_5' && $arg_value != "") {
				$tab_reinit['annee_prec_global_5'] = $arg_value;
				$xml .= "<MAJ_RESULT>OK</MAJ_RESULT>";
			}
			saveVariable('MYRAING_RELEVES_'.$api_compteur, $tab_reinit);
		}
    }
    $xml .= "</MYRAIN>";
	sdk_header('text/xml');
	echo $xml;
?>