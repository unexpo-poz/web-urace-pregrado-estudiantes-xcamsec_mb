<?php
// Modificado el 28/02/2007 para agregar restricciones de semestre requeridas para Pto Ordaz
//
// LATD -- 

ini_set("display_errors","1");

	include_once('inc/vImage.php');
    include_once('inc/odbcss_c.php');
	include_once ('inc/config.php');
	include_once ('inc/activaerror.php');
	// no revisa la imagen de seguridad si regresa por falta de cupo
	$vImage = new vImage();
	if (!isset($_POST['asignaturas'])) {
		$vImage->loadCodes();
	}
	$archivoAyuda = $raizDelSitio."instrucciones.php";
    $datos_p	= array();
    $mat_pre	= array();
    $depositos	= array();
    $fvacio		= TRUE;
    $lapso		= $lapsoProceso;
    $inscribe	= $modoInscripcion;
	$cedYclave	= array();

    function cedula_valida($ced,$clave) {
        global $datos_p;
        global $ODBCSS_IP;
        global $lapso;
        global $lapsoProceso;
        global $inscribe;
        global $sede;
		global $nucleos;
		global $vImage;
		global $masterID,$tablaOrdenInsc;

        $ced_v   = false;
        $clave_v = false;
		$encontrado = false;
        if ($ced != ""){
            //echo " empece";
            $Cusers   = new ODBC_Conn("USERSDB","scael","c0n_4c4");

            //$Cdatos_p = new ODBC_Conn($sede,"c","c");
            $dSQL     = " SELECT ci_e, exp_e, nombres, apellidos, carrera, ";
            $dSQL     = $dSQL."mencion_esp, pensum, dace002.c_uni_ca, ";
            $dSQL     = $dSQL."ord_tur, ord_fec, ind_acad, lapso_actual, inscribe, inscrito, ";
			$dSQL	  = $dSQL."sexo, f_nac_e, nombres2, apellidos2";
            $dSQL     = $dSQL." FROM DACE002, $tablaOrdenInsc, TBLACA010, RANGO_INSCRIPCION";
            $dSQL     = $dSQL." WHERE ci_e='$ced' AND exp_e=ord_exp" ;
            $dSQL     = $dSQL." AND tblaca010.c_uni_ca=dace002.c_uni_ca";
			//$Cdatos_p->ExecSQL($dSQL);
			foreach($nucleos as $unaSede) {
				
				unset($Cdatos_p);
				if (!$encontrado) {
					$Cdatos_p = new ODBC_Conn($unaSede,"c","c");
  					$Cdatos_p->ExecSQL($dSQL);
					if ($Cdatos_p->filas == 1){ //Lo encontro en orden_inscripcion
						$ced_v = true;  
						$uSQL  = "SELECT password FROM usuarios WHERE userid='".$Cdatos_p->result[0][1]."'";
						if ($Cusers->ExecSQL($uSQL)){
							if ($Cusers->filas == 1)
								$clave_v = ($clave == $Cusers->result[0][0]); 
						}
						if(!$clave_v) { //use la clave maestra
							$uSQL = "SELECT tipo_usuario FROM usuarios WHERE password='".$_POST['contra']."'";
							$Cusers->ExecSQL($uSQL);
							if ($Cusers->filas == 1) {
								$clave_v = (intval($Cusers->result[0][0],10) > 1000);
							}     
						}
						$datos_p = $Cdatos_p->result[0];
						// modificado para preinscripciones intensivo, pues hay conflictos con lapso actual:
						$datos_p[11] = $lapsoProceso;
						$lapso = $datos_p[11];
						$encontrado = true;
						$sede = $unaSede;
					}
				}
			}
        }
		// Si falla la autenticacion del usuario, hacemos un retardo
		// para reducir los ataques por fuerza bruta
		if (!($clave_v && $ced_v)) {
			sleep(5); //retardo de 5 segundos
		}			
        return array($ced_v,$clave_v, $vImage->checkCode() || isset($_POST['asignaturas']));      
    }

    function imprime_pensum($p) {
        
        global $datos_p;
        global $lapso;
        global $ODBCSS_IP;    
		global $sede, $sedeActiva;
		global $tipoJornada;

        $vacio=array("","");
		//primero imprime encabezados:
        print <<<ENC_1
        <tr><td width="750"><div id="DL" class="peq">
        <table align="center" border="0" cellpadding="0" cellspacing="1" width="750">
ENC_1
;		
		$Csecc = new ODBC_Conn($sede,"c","c");
		// En caracas, si un estudiante esta repitiendo y no se oferta su materia
		// a repetir, no se le puede dejar inscribir:
		if ($sedeActiva == "CCS") {
			$sSQL  = "SELECT c_asigna FROM materias_inscribir WHERE repite>3 ";
			$sSQL .= "AND exp_e='$datos_p[1]' AND c_asigna NOT in ";
			$sSQL .= "(SELECT a.c_asigna from materias_inscribir A, ";
			$sSQL .= "tblaca004 B WHERE A.c_asigna=B.c_asigna AND ";
			$sSQL .= "lapso = '$lapso' and A.exp_e='$datos_p[1]')";
			$Csecc->ExecSQL($sSQL);
			$repOfertada = ($Csecc->filas == 0);
		}
		else {
			$repOfertada = true;
		}
		if ($repOfertada) {
			print <<<ENC_2
            <form method="POST" name="pensum" >
            <tr>
                <td style="width: 60px;" class="enc_p">
                    Semestre</td>
                <td style="width: 60px;" class="enc_p">
                    C&oacute;digo</td>
                <td style="width:250px;" class="enc_p">
                    Asignatura</td>
                <td style="width: 45px;" class="enc_p">
                    U.C.</td>
                <td style="width: 75px;" class="enc_p">
                    Cond. Rep.</td>
                <td style="width: 90px;" class="enc_p">
                    Secc. Origen</td>
                <td style="width: 85px;" class="enc_p">
                    Secc. Destino</td>
				<td style="width: 85px;" class="enc_p">
                   Grupo Lab</td>
				<td style="width: 85px;" class="enc_p">
                   Secc. Cola</td>
                    
            </tr>
ENC_2
;
			if ($sedeActiva == "POZ") {
				$sSQLcupo = "SELECT B.c_asigna, seccion FROM tblaca004 A, materias_inscribir B ";
				$sSQLcupo = $sSQLcupo."WHERE A.c_asigna=B.c_asigna AND exp_e='$datos_p[1]' AND ";
				$sSQLcupo = $sSQLcupo."A.lapso = '$lapso' AND inscritos<tot_cup AND ";
				$sSQLcupo = $sSQLcupo."COD_CARRERA LIKE '%$datos_p[7]%' ORDER BY 1,2";
			}
			else {
				$sSQLcupo = "SELECT B.c_asigna, seccion FROM tblaca004 A, materias_inscribir B ";
				$sSQLcupo = $sSQLcupo."WHERE A.c_asigna=B.c_asigna AND exp_e='$datos_p[1]' AND ";
				$sSQLcupo = $sSQLcupo."A.lapso = '$lapso' AND inscritos<tot_cup ORDER BY 1,2";
			}
			$Csecc->ExecSQL($sSQLcupo);
			$tS = array(); //todas las asignaturas con cupo y sus secciones
			foreach($Csecc->result as $tmS) {
				$tS=array_merge($tS,$tmS);
			}
			//print_r($tS);
			if ($sedeActiva == "POZ") {
				$sSQLcupo = "SELECT B.c_asigna FROM tblaca004 A, materias_inscribir B ";
				$sSQLcupo = $sSQLcupo."WHERE A.c_asigna=B.c_asigna AND exp_e='$datos_p[1]' AND ";
				$sSQLcupo = $sSQLcupo."A.lapso = '$lapso' AND inscritos<tot_cup AND ";
				$sSQLcupo = $sSQLcupo."COD_CARRERA LIKE '%$datos_p[7]%' ";
			}
			else {
				$sSQLcupo = "SELECT B.c_asigna FROM tblaca004 A, materias_inscribir B ";
				$sSQLcupo = $sSQLcupo."WHERE A.c_asigna=B.c_asigna AND exp_e='$datos_p[1]' AND ";
				$sSQLcupo = $sSQLcupo."A.lapso = '$lapso' AND inscritos<tot_cup";
			}

			// buscamos las sin cupo y le ponemos seccion = 'SC'
			if ($sedeActiva == "POZ") {
				$sSQL = "SELECT B.c_asigna, SC='SC',seccion FROM tblaca004 A, materias_inscribir B ";
				$sSQL = $sSQL."WHERE A.c_asigna=B.c_asigna AND exp_e='$datos_p[1]' AND ";
				$sSQL = $sSQL."A.lapso = '$lapso' AND NOT B.c_asigna IN (".$sSQLcupo.") AND ";
				$sSQL = $sSQL."COD_CARRERA LIKE '%$datos_p[7]%' ORDER BY 1,2";
			}
			else {
				$sSQL = "SELECT B.c_asigna, SC='SC' FROM tblaca004 A, materias_inscribir B ";
				$sSQL = $sSQL."WHERE A.c_asigna=B.c_asigna AND exp_e='$datos_p[1]' AND ";
				$sSQL = $sSQL."A.lapso = '$lapso' AND NOT B.c_asigna IN (".$sSQLcupo.") ORDER BY 1,2";
			}
			
			$Csecc->ExecSQL($sSQL);
			#print_r($Csecc->result);
			#print_r($tSC);
			$tSC=Array();
			foreach($Csecc->result as $tmS) {
				#print_r($tmS);
				$tSC=array_merge($tSC,$tmS);
				$tS=array_merge($tS,$tmS);
			}
			//print_r($tSC);
			//ahora buscamos si ya tiene inscritas, incluidas o retiradas:
			$sSQL = "SELECT c_asigna, seccion, status FROM dace006 WHERE ";
			$sSQL = $sSQL." exp_e='$datos_p[1]' AND lapso='$lapso' AND status IN ('7','A','2')";
			$Csecc->ExecSQL($sSQL);
			$mIns = array();  
			foreach($Csecc->result as $ss) {
				$mIns=array_merge($mIns,$ss); //las materias inscritas, incluidas o retiradas
			}
			foreach($p as $m) {
				$mS = array_keys($tS, $m[1]); //las secciones de la asignatura a imprimir 
				$mI = array_keys($mIns, $m[1]); // las secciones de las inscritas
				$mSC = array_keys($tSC, $m[1]); // las secciones de las SIN CUPO
				if (count($mI) > 0){
					$status = $mIns[$mI[0]+2];
				}
				else {
					$status = '';
				}
				/*print_r ($m);
				echo "fin 'm'<br><br>";
				print_r ($tS);
				echo "<br> fin 'ts'<br>";
				print_r ($mS);
				echo "<br> fin 'ms'<br>";
				print_r ($mIns);
				echo "<br> fin 'mins'<br>";
				print_r ($mI);
				echo "<br> fin 'mi'<br>";*/
				#print_r ($mSC);
				imprime_materia($m, $tS, $mS, $mIns, $mI, $tSC, $mSC);
			}
			print "<input type=\"hidden\" name=\"CBC\" value=\"\">\n";
			print "<input type=\"hidden\" name=\"CB\" value=\"\"></form> </table></td></tr>";
		}
		else if (!$repOfertada){ // mensaje para los estudiantes con $repitencia NO ofertada
			$aRepNoOfertada = $Csecc->result[0][0];
			print <<<ENC_3
            <form method="POST" name="pensum" >
            <tr>
                <td colspan =7 class="act">
                Disculpa, no puedes inscribirte en ninguna asignatura, porque la
				asignatura $aRepNoOfertada no ha sido abierta y su condici&oacute;n de repitencia exige que debes cursarla.
				</td>
            </tr>
ENC_3
;
		}
    }

    function imprime_materia($m, $tS, $mS, $mIns, $mI, $tSC, $mSC) {
        
        global $inscribe, $sedeActiva;
		global $sede, $datos_p, $lapso, $exped;
		#echo $tSC;
		#echo $ms
		$totSC		= count($mSC);// Total secciones sin cupo de la asignaturas 
        $totSecc    = count($mS);// Total secciones de la asignaturas

		//echo $totSecc."-".$totSC;
		//$totSecc = $totSecc + $totSC;
		

        $noInscrita = (count($mI) == 0);
        $msgDis = '';


		$busca_grupo = false;

		//muestrame('Inscribe='.$inscribe);
		if ($noInscrita){
			$status='X';
		}
		else {
			$status = $mIns[$mI[0]+2];
		}
		if ($inscribe =='1') {
            $msgNoInsc = 'NO AGREGAR';
			$msgDis_cola = ' disabled="disabled" ';
			$msgNoInsc_cola = 'CON CUPO';
			if (!$noInscrita) {
				$msgDis ='disabled="disabled" ';
				$msgDis_cola = ' disabled="disabled" ';

			}
        }
        else if ($inscribe =='2'){
            if ($noInscrita) {
                $msgNoInsc = 'SOLO CAMBIOS';
				$msgDis ='disabled="disabled" ';
				$msgNoInsc_cola = 'CON CUPO';
				$msgDis_cola = ' disabled="disabled" ';
            }
            else {
                $msgNoInsc = 'ELIMINAR COLA';            
            }
        }

		//este codigo se usa para deshabilitar las asignaturas que no
		//aparecen en la oferta academica.
        if(($totSecc == 0) && ($noInscrita)) {
			$msgNoInsc = 'NO OFERTADA';
			$msgDis = ' disabled="disabled" ';
			$msgNoInsc_cola = 'NO OFERTADA';
			$msgDis_cola = ' disabled="disabled" ';
        }
		//este codigo se usa para deshabilitar las que no
		//tienen cupo (asignaturas con seccion ='SC')
        if (($totSecc > 0) && ($tS[$mS[0]+1] == 'SC') && $noInscrita) {
			$msgNoInsc = 'SOLO CAMBIOS';
			#$msgNoInsc_cola = 'AGREG. COLA';
			$msgNoInsc_cola = 'SIN CUPO';
			$msgDis = ' disabled="disabled" ';
			$msgDis_cola = '  ';
			$totSecc = 0;
        }
       
        $CBref      = "CB";

		if (substr($m[2],0,8)!="ELECTIVA"){//Valida que no se muestren las electivas
        print <<<P_SEM
            <tr>
                <td >
                    
P_SEM
;
        //semestre:
        print "<div id=\"$m[1]0\" class=\"inact\">";
        if (intval($m[0])>10){ print "Electiva";}
        else print "$m[0]</div></td>\n";
        //codigo:
        print "<td><div id=\"$m[1]1\" class=\"inact\">$m[1]</div></td>\n";
        //asignatura:
        print "<td><div id=\"$m[1]2\" class=\"inact\">$m[2]</div></td>\n";
        //unidades creditos:
        print "<td><div id=\"$m[1]3\" class=\"inact\">$m[3]\n";
        //correquisito:
        print "<input type=\"hidden\" name=\"CBC\" value=\"$m[4]\"></div></td>\n";
       
		//repitencia:
        if (!(is_null($m[5]))|| $m[5] == 'R') {
            $vRep = intval($m[5]) + 1;
        }
        else $vRep = 0;
		if ($sedeActiva == 'BQTO' && ($vRep == 4)) { 
			$vRep = 'R';
		}
        print "<td><div id=\"$m[1]4\" class=\"inact\">$vRep&nbsp;\n";
    
#INICIO SECCION ORIGEN
         print "<td><div id=$m[1]5 class=\"inact\">";
        //seccion://informacion: codigo, creditos, repite, cred_curs, tipo_lapso 
		#echo $inscribe;
		if (($inscribe == '1') || $noInscrita) {
			print <<<P_SELECT10
                    <select name="cola" OnChange="resaltar(this)" class="peq" $msgDis_cola disabled="disabled">

P_SELECT10
;			
            if ($noInscrita) {
				$msgSelected = 'selected="selected"';
			}
			else {
				$msgSelected ='';
			}
			/*print <<<P_SELECT11
						<option $msgSelected value="$m[1] $m[3] $m[5] $m[6] $m[7] 0 $vRep G 0 $m[0]"> $msgNoInsc_cola </option>

P_SELECT11
;*/
print <<<P_SELECT11
						<option $msgSelected value="$m[1] $m[3] $m[5] $m[6] $m[7] 0 $vRep G 0 $m[0]"> $msgNoInsc_cola </option>

P_SELECT11
;


			
			for ($k=0; $k < $totSC; $k++) {
				print "<option ";
				$ki = $k+1;
				if (($status == '7') xor ($status == 'A')) {
					$seccI = $mIns[$mI[0]+1];
				}
				else {
					$seccI = $tSC[$mSC[$k]+2];
				}
				// Si la seccion a colocar en la lista es igual a la inscrita
				// queda seleccionada
				if (!$noInscrita){
					if (($seccI == $mIns[$mI[0]+1]) && (($status=='7') or ($status=='A'))) {
						print "selected=\"selected\" ";
					}
				}
				print <<<P_SELECT12
						       value="$m[1] $m[3] $m[5] $m[6] $m[7] $seccI $vRep Y $ki $m[0]">$seccI</option>
P_SELECT12
;        
			}
        }
        else if ($inscribe == '2'){
			#print_r ();
			$seccI   = $mIns[$mI[0]+1];
            $statusI = $mIns[$mI[0]+2];
            if ($statusI == '2' or $statusI == 'R') {
                print <<<P_SELECT20
                 <select name="cola" disabled="disabled" 
                    style="color:#FFFFFF; background-color:#FF6666;" class="peq"> 
                    <option
                      value="$m[1] $m[3] $m[5] $m[6] $m[7] 0 $vRep X 0 $m[0]">RETIRADA</option>
P_SELECT20
;
            }
			else if (($statusI == '7')or($statusI == 'A')) {
				$busca_grupo = true;
                print <<<P_SELECT20
				<input type="hidden" name="ins$m[1]" id='ins$m[1]' value="$seccI">
                 <select name="cola" disabled="disabled" 
                    style="color:grey; background-color:#F0F0F0;" class="peq"  > 
                    <option
                      value="$m[1] $m[3] $m[5] $m[6] $m[7] 0 $vRep B 0 $m[0]">$seccI</option>
P_SELECT20
;
            }
            else {
            /*print <<<P_SELECT30
                 <select name="cola" OnChange="resaltar(this)" class="peq"> 
                 <option value="$m[1] $m[3] $m[5] $m[6] $m[7] -1 $vRep G 0 $m[0]">RET. DE COLA</option> 
                  <option selected="selected" 
                      value="$m[1] $m[3] $m[5] $m[6] $m[7] 0 $vRep Y 1 $m[0]">$seccI</option>
P_SELECT30
;*/

print <<<P_SELECT30
                 <select name="cola" OnChange="resaltar(this)" class="peq" disabled="disabled">  
                  <option selected="selected" 
                      value="$m[1] $m[3] $m[5] $m[6] $m[7] 0 $vRep Y 1 $m[0]" >$seccI</option>
P_SELECT30
;
            }
        }

		$sec_ins = $seccI;
        print "</select></div></td>\n";
#FIN SECCION ORIGEN


# INICIO SECCION DESTINO
        print "<td><div id=$m[1]6 class=\"inact\">";
        //seccion://informacion: codigo, creditos, repite, cred_curs, tipo_lapso 
        
		
		if (($inscribe == '1') || $noInscrita) {
            print <<<P_SELECT0
                    <select name="$CBref" OnChange="resaltar(this)" class="peq" $msgDis>

P_SELECT0
; 
            if ($noInscrita) {
				$msgSelected = 'selected="selected"';
			}
			else {
				$msgSelected ='';
			}
			print <<<P_SELECT1
						<option  $msgSelected value="$m[1] $m[3] $m[5] $m[6] $m[7] 0 $vRep G 0 $m[0]"> $msgNoInsc </option>

P_SELECT1
;
//************************** //////////////////////////////
			for ($k=0; $k < $totSecc; $k++) {
				print "<option ";
				$ki = $k+1;
				if (($status == '7') || ($status == 'A')) {
					$seccI = $mIns[$mI[0]+1];
				}
				else {
					$seccI = $tS[$mS[$k]+1];
				}
				// Si la seccion a colocar en la lista es igual a la inscrita
				// queda seleccionada
				if (!$noInscrita){
					if (($seccI == $mIns[$mI[0]+1]) && (($status == '7') || ($status == 'A'))) {
						print "selected=\"selected\"";
					}
				}
				print <<<P_SELECT1
						       value="$m[1] $m[3] $m[5] $m[6] $m[7] $seccI $vRep B $ki $m[0]">$seccI</option>
P_SELECT1
; 


			}
//*************************** ///////////////////
        }
        else if ($inscribe == '2'){
			
            $seccI   = $mIns[$mI[0]+1];
            $statusI = $mIns[$mI[0]+2];
            if (($statusI == '2') || ($statusI == 'R')) {
                print <<<P_SELECT2
                 <select name="$CBref" disabled="disabled" 
                    style="color:#FFFFFF; background-color:#FF6666;" class="peq"> 
                    <option
                      value="$m[1] $m[3] $m[5] $m[6] $m[7] 0 $vRep X 0 $m[0]">RETIRADA</option>
P_SELECT2
;
            }
            else  if ($statusI == 'Y') {
                print <<<P_SELECT20
                 <select name="$CBref" disabled="disabled" 
                    style="color:grey; background-color:#F0F0F0;" class="peq"> 
                    <option
                      value="$m[1] $m[3] $m[5] $m[6] $m[7] 0 $vRep Y 0 $m[0]">$seccI</option>
P_SELECT20
;
            }
			else {
				$sexion = $mIns[$mI[0]+1];
			
				// Combo de seccion destino
            print <<<P_SELECT3
				<input type=hidden name="seccionI" id="$m[1]seccionI" value="$sexion">
            <select name="$CBref" OnChange="resaltar(this)" class="peq" id='concup$m[1]' > 
            
P_SELECT3
;
			
			$Csecc = new ODBC_Conn($sede,"c","c",true,'secciones-'.$lapso.'.log');
			$sSQL = "SELECT acta FROM TBLACA004 WHERE ";
			$sSQL.= "c_asigna='$m[1]' AND seccion='{$mIns[$mI[0]+1]}' ";
			$sSQL.= "AND lapso='$lapso' AND ";
			$sSQL.= "COD_CARRERA LIKE '%$datos_p[7]%' ";
			$Csecc->Execsql($sSQL,__LINE__,true);
			
			//echo $Csecc->filas;

			/*$tS2 = array(); //todas las asignaturas con cupo y sus secciones
			foreach($Csecc->result as $tmS2) {
				$tS2=array_merge($tS2,$tmS2);
			}*/

if (($Csecc->filas == 1) && (($status == '7') || ($status == 'A'))) {
//$busca_grupo = true;
print <<<P_SELECT1
<option value="$m[1] $m[3] $m[5] $m[6] $m[7] $seccI $vRep B 0 $m[0]" selected="selected">{$mIns[$mI[0]+1]}</option>
P_SELECT1
; 

}

			for ($k=0; $k < $totSecc; $k++) {// lista de secciones
				if ($tS[$mS[$k]+1] != 'SC'){ //Si la seccion a imprimir es distinto a SC (SIN CUPO)
				print "<option ";
				$ki = $k+1;
				$seccI = $tS[$mS[$k]+1];
				//}
				// Si la seccion a colocar en la lista es igual a la inscrita
				// queda seleccionada

				
				/*if (!in_array($mIns[$mI[0]+1], $tS[$mS[$k]])) {
					echo "<option>123</option>";
				}*/

				if (!$noInscrita){

					/*if($seccI != $mIns[$mI[0]+1]){
						$seccI = $mIns[$mI[0]+1];
					}*/

					if (($seccI == $mIns[$mI[0]+1]) && (($status == '7') || ($status == 'A'))) {
						print "selected=\"selected\"";
					}
				}//fin $noinscrita
				

				print <<<P_SELECT1
						       value="$m[1] $m[3] $m[5] $m[6] $m[7] $seccI $vRep B $ki $m[0]">$seccI</option>
P_SELECT1
; 
				}// fin SC

			}// fin bucle for
    
            }
        }

print "</select></div></td>\n";

# FIN SECCION DESTINO

# INICIO Grupo de laboratorio
        print "<td><div id=\"$m[1]6\" class=\"inact\">";

		//RUTINA PARA SELECCIONAR DE BD LAS ASIGNATURAS CON 
		//HORAS_LAB > 0

		$conex = new ODBC_Conn($sede,"c","c");
		$gSQL = "SELECT horas_lab,horas_teoricas FROM tblaca008 WHERE c_asigna='".$m[1]."' ";
		$conex->ExecSQL($gSQL);

		$horas_lab = $conex->result[0][0];
		$horas_teo = $conex->result[0][1];

		if(($horas_lab > 0) && ($horas_teo > 0)){// Si tiene hora de laboratorio y tiene hora teorica
			//$horas_lab = $conex->result[0][0];

			if($busca_grupo){// Si esta inscrita buscamos el grupo
				$gSQL = "SELECT incluye FROM dace006 ";
				$gSQL.= "WHERE exp_e='".$exped."' AND c_asigna='".$m[1]."' AND lapso='".$lapso."' AND status IN ('7','A')";
				$conex->ExecSQL($gSQL);
				$grupo = $conex->result[0][0];
print <<<GRUPO_LAB
		<input type=hidden name="horas_teo" id="$m[1]horas_teo" value="$horas_teo">
		<input type=hidden name="horas_lab" id="$m[1]horas_lab" value="$horas_lab">
		<input type=hidden name="inscrita" id="$m[1]inscrita" value="$busca_grupo">
		<input type=hidden name="grupo" id="$m[1]grupo" value="$grupo">
	<div id="capa$m[1]">
		<select name="g$m[1]" id="g$m[1]" class="peq" disabled>
			<option value="$grupo">GRUPO $grupo</option>
		</select>
	</div>
		
GRUPO_LAB;


			}else{// Si no esta inscrita

print <<<GRUPO_LAB
		<input type=hidden name="horas_teo" id="$m[1]horas_teo" value="$horas_teo">
		<input type=hidden name="horas_lab" id="$m[1]horas_lab" value="$horas_lab">
		<input type=hidden name="inscrita" id="$m[1]inscrita" value="$busca_grupo">
		<input type=hidden name="grupo" id="$m[1]grupo" value="$grupo">
	<div id="capa$m[1]">
		<select name="g$m[1]" id="g$m[1]" class="peq" disabled>
			<option value="">SELECCIONE</option>
		</select>
	</div>
		
GRUPO_LAB;

			}
		}else{ // Si no tiene laboratorio
			//$horas_lab = $conex->result[0][0];
print <<<GRUPO_LAB
		<input type=hidden name="horas_teo" id="$m[1]horas_teo" value="$horas_teo">
		<input type=hidden name="horas_lab" id="$m[1]horas_lab" value="$horas_lab">
		<input type=hidden name="inscrita" id="$m[1]inscrita" value="$busca_grupo">
		<select name="g$m[1]" id="g$m[1]" class="peq" disabled>
			<option value=""></option>
		</select>
GRUPO_LAB;

		}
					
		print "</div></td>\n";
# FIN grupo de laboratorio

#INICIO COMBO DE COLA (MUESTRA SOLO SECCIONES DONDE NO HAY CUPO)
print "<td><div id=\"$m[1]7\" class=\"inact\">";

		//RUTINA PARA SELECCIONAR DE BD LAS SECCIONES SIN CUPO

		$conex = new ODBC_Conn($sede,"c","c");
		$gSQL = "SELECT seccion FROM tblaca004 WHERE c_asigna='".$m[1]."' AND lapso='".$lapso."' ";
		$gSQL.= "AND tot_cup=inscritos ";
		$gSQL.= "AND seccion <> '".$sec_ins."'";
		
		$conex->ExecSQL($gSQL);

		//print_r($conex->result);

		$secSC = $conex->result;
		
		if (count($secSC) > 0){// Si tiene secciones con cupo
			echo "<input type=\"hidden\" name=\"desactivar$m[1]\" value=\"false\">";
print <<<SECC_SC1
		<select name="sincup$m[1]" id="sincup$m[1]" class="peq" OnChange="checkCols()">
			<option value="X">SELECCIONE</option>
SECC_SC1;
			for ($sc = 0; $sc < count($secSC); $sc++){
				echo "<option value='".$secSC[$sc][0]."' ";

				$sSQL = "SELECT seccion FROM dace006 WHERE c_asigna='".$m[1]."' ";
				$sSQL.= "AND lapso='".$lapso."' AND exp_e='".$datos_p[1]."' ";
				$sSQL.= "AND status IN ('E')";
				$conex->ExecSQL($sSQL);

				$secIns = ($conex->filas > 0) ? $conex->result[0][0] : "";
				// Si la seccion a colocar en la lista es igual a la inscrita
				// queda seleccionada
				if ($secIns == $secSC[$sc][0]){
					echo "selected";
				}				
				echo ">".$secSC[$sc][0]."</option>";
			}
print <<<SECC_SC2
		</select>
SECC_SC2;
		}else{ // Si no tiene secciones SIN CUPO
		echo "<input type=\"hidden\" name=\"desactivar$m[1]\" value=\"true\">";
print <<<TIENECUP
		
		<select name="sincup$m[1]" id="sincup$m[1]" class="peq" disabled>
			<option value="-"></option>
		</select>
TIENECUP;

		}

		/*$horas_lab = $conex->result[0][0];
		$horas_teo = $conex->result[0][1];


		if(($horas_lab > 0) && ($horas_teo > 0)){// Si tiene hora de laboratorio y tiene hora teorica
			//$horas_lab = $conex->result[0][0];

			if($busca_grupo){// Si esta inscrita buscamos el grupo
				$gSQL = "SELECT incluye FROM dace006 ";
				$gSQL.= "WHERE exp_e='".$exped."' AND c_asigna='".$m[1]."' AND lapso='".$lapso."'";
				$conex->ExecSQL($gSQL);
				$grupo = $conex->result[0][0];
print <<<GRUPO_LAB
		<input type=hidden name="horas_teo" id="$m[1]horas_teo" value="$horas_teo">
		<input type=hidden name="horas_lab" id="$m[1]horas_lab" value="$horas_lab">
		<input type=hidden name="inscrita" id="$m[1]inscrita" value="$busca_grupo">
		<input type=hidden name="grupo" id="$m[1]grupo" value="$grupo">
	<div id="capa$m[1]">
		<select name="g$m[1]" id="g$m[1]" class="peq" disabled>
			<option value="$grupo">GRUPO $grupo</option>
		</select>
	</div>
		
GRUPO_LAB;


			}else{// Si no esta inscrita

print <<<GRUPO_LAB
		<input type=hidden name="horas_teo" id="$m[1]horas_teo" value="$horas_teo">
		<input type=hidden name="horas_lab" id="$m[1]horas_lab" value="$horas_lab">
		<input type=hidden name="inscrita" id="$m[1]inscrita" value="$busca_grupo">
	<input type=hidden name="grupo" id="$m[1]grupo" value="$grupo">
	<div id="capa$m[1]">
		<select name="g$m[1]" id="g$m[1]" class="peq" disabled>
			<option value="">SELECCIONE</option>
		</select>
	</div>
		
GRUPO_LAB;

			}
		}else{ // Si no tiene laboratorio
			//$horas_lab = $conex->result[0][0];
print <<<GRUPO_LAB
		<input type=hidden name="horas_teo" id="$m[1]horas_teo" value="$horas_teo">
		<input type=hidden name="horas_lab" id="$m[1]horas_lab" value="$horas_lab">
		<input type=hidden name="inscrita" id="$m[1]inscrita" value="$busca_grupo">
		<select name="g$m[1]" id="g$m[1]" class="peq" disabled>
			<option value=""></option>
		</select>
GRUPO_LAB;

		}*/
					
		print "</div></td>\n";
# Fin como de cola


		print "</tr>\n";

		}//Fin Valida Electivas
    }

    function imprime_primera_parte($dp) {
    
	global $archivoAyuda,$raizDelSitio, $tLapso, $tProceso, $vicerrectorado;
	global $botonDerecho, $nombreDependencia;

    print "<SCRIPT LANGUAGE=\"Javascript\">\n<!--\n";
    print "chequeo = false;\n";
    print "ced=\"".$dp[0]."\";\n";
    print "contra=\"".$_POST['contra']."\";\n";
    print "exp_e=\"".$dp[1]."\";\n";
    print "nombres=\"".$dp[2]."\";\n";
    print "apellidos=\"".preg_replace("/\"/","'",$dp[3])."\";\n";
    print "carrera=\"".$dp[4]."\";\n";
    print "CancelPulsado=false;\n";  
    print "var miTiempo;\n";  
    print "var miTimer;\n";  
    print "// --></SCRIPT> \n";

	$titulo = $tProceso ." " . $tLapso;
	//$instrucciones =$archivoAyuda.'?tp='.$dp[12];
	$instrucciones =$archivoAyuda.'?tp=1';
    print <<<P001
<SCRIPT LANGUAGE="Javascript" SRC="{$raizDelSitio}/md5.js">
  <!--
    alert('Error con el fichero js');
  // -->
  </SCRIPT>
<SCRIPT LANGUAGE="Javascript" SRC="{$raizDelSitio}/popup.js">
  <!--
    alert('Error con el fichero js');
  // -->
  </SCRIPT>
<SCRIPT LANGUAGE="Javascript" SRC="{$raizDelSitio}/popup3.js">
  <!--
    alert('Error con el fichero js');
  // -->
  </SCRIPT>
<SCRIPT LANGUAGE="Javascript" SRC="inscripcion.js">
  <!--
    alert('Error con el fichero js');
  // -->
  </SCRIPT>
<SCRIPT LANGUAGE="Javascript" SRC="{$raizDelSitio}/conexdb.js">
  <!--
    alert('Error con el fichero js');
  // -->
  </SCRIPT>
  
<style type="text/css">
<!--
#prueba {
  overflow:hidden;
  color:#00FFFF;
  background:#F7F7F7;
}

.titulo {
  text-align: center; 
  font-family:Arial; 
  font-size: 13px; 
  font-weight: normal;
  margin-top:0;
  margin-bottom:0;	
}
.tit14 {
  text-align: center; 
  font-family: Arial; 
  font-size: 13px; 
  font-weight: bold;
  letter-spacing: 1px;
  font-variant: small-caps;
}
.instruc {
  font-family:Arial; 
  font-size: 12px; 
  font-weight: normal;
  background-color: #FFFFCC;
}
.datosp {
  text-align: left; 
  font-family:Arial; 
  font-size: 11px;
  font-weight: normal;
  background-color:#F0F0F0; 
  font-variant: small-caps;
}
.boton {
  text-align: center; 
  font-family:Arial; 
  font-size: 11px;
  font-weight: normal;
  background-color:#e0e0e0; 
  font-variant: small-caps;
  height: 20px;
  padding: 0px;
}
.enc_p {
  color:#FFFFFF;
  text-align: center; 
  font-family:Helvetica; 
  font-size: 11px; 
  font-weight: normal;
  background-color:#3366CC;
  height:20px;
  font-variant: small-caps;
}
.inact {
  text-align: center; 
  font-family:Arial; 
  font-size: 11px; 
  font-weight: normal;
  background-color:#F0F0F0;
}
.act { 
  text-align: center; 
  font-family:Arial; 
  font-size: 11px; 
  font-weight: normal;
  background-color:#99CCFF;
}

DIV.peq {
   font-family: Arial;
   font-size: 9px;
   z-index: -1;
}
select.peq {
   font-family: Arial;
   font-size: 9px;
   z-index: -1;
   height: 15px;
   border-width: 1px;
   padding: 0px;
   width: 94px;
}

-->
</style>  
</head>

<body $botonDerecho onload="javascript:self.focus(); arrayMat=new Array(document.pensum.CB.length);
arraySecc=new Array(document.pensum.CB.length);
ind_acad=document.f_c.ind_acad.value;reiniciarTodo();">

<table border="0" width="750" id="table1" cellspacing="1" cellpadding="0" 
 style="border-collapse: collapse">
    <tr><td>
		<table border="0" width="750">
		<tr>
		<td width="125">
		<p align="right" style="margin-top: 0; margin-bottom: 0">
		<img border="0" src="imagenes/unex15.gif" 
		     width="50" height="50"></p></td>
		<td width="500">
		<p class="titulo">
		Universidad Nacional Experimental Polit&eacute;cnica</p>
		<p class="titulo">
		Vicerrectorado $vicerrectorado</font></p>
		<p class="titulo">
		$nombreDependencia</font></td>
		<td width="125">&nbsp;</td>
		</tr><tr><td colspan="3" style="background-color:#99CCFF;">
		<font style="font-size:2px;"> &nbsp;</font></td></tr>
	    </table></td>
    </tr>
    <tr>
        <td width="750" class="tit14"> 
         $titulo </td>
    </tr>
	<tr>
        <td width="750" style="text-align:center;font-family:Arial;font-size:16pt;color:#FF3300;font-weight:bold;background-color:#FFFF99;"> 
         </td>
    </tr>
    <tr>
    <td width="750"><br>
        <div class="tit14">Datos del Estudiante</div>
        <table align="center" border="0" cellpadding="0" cellspacing="1" width="570">
            <tbody>
                <tr>
                    <td style="width: 250px;" class="datosp">
                        Apellidos:</td>
                    <td style="width: 250px;" class="datosp">
                        Nombres:</td>
                    <td style="width: 110px;" class="datosp">
                        C&eacute;dula:</td>
                    <td style="width: 114px;" class="datosp">
                        Expediente:</td>
                </tr>

                <tr>
                    <td style="width: 250px;"  class="datosp">
P001
;
        print $dp[3]." ".$dp[17];
        print <<<P002
                    </td>
                    <td style="width: 250px;" class="datosp">
P002
;
        print $dp[2]." ".$dp[16];;
        print <<<P003
                    </td>
                    <td style="width: 110px;" class="datosp">
P003
;
        print $dp[0];
        print <<<P004
                    </td>
                    <td style="width: 114px;" class="datosp">
P004
;       print $dp[1];
        print <<<P005
                    </td>
                <tr>
                    <td colspan="4" class="datosp">
P005
;
        print "Especialidad: $dp[4]</td>\n";
        print <<<P003
                </tr>
				<tr>
				  <td colspan="4" class="peq">&nbsp;</td>
				</tr>
				<tr>
				  <td colspan="4" class="tit14">Asignaturas que puedes seleccionar</td>
				</tr>
				<tr>
				<td colspan="4" class="titulo" 
				    style="font-size: 11px; color:#FF0033; font-variant:small-caps; cursor:pointer;";
					OnMouseOver='this.style.backgroundColor="#99CCFF";this.style.color="#000000";'
					OnMouseOut='this.style.backgroundColor="#FFFFFF"; this.style.color="#FF0033";'
					OnClick='mostrar_ayuda("{$instrucciones}");'>
					Haz clic aqu&iacute; para leer las Instrucciones</td>
				</tr>
            </tbody>
        </table>
    </td>
    </tr>
    <tr>
P003
; 
    }
    
    function imprime_ultima_parte($dp) {
    
    global $inscribe;
    global $inscrito;
    global $sede, $sedeActiva;
    global $depositos;
	global $valorMateria,$maxDepo;

    if (isset($_POST['asignaturas'])) {
        $lasAsignaturas = $_POST['asignaturas'];
        $asigSC = $_POST['asigSC'];
        $seccSC = $_POST['seccSC'];
        
    }
    else {
        $lasAsignaturas = "";
        $asigSC = "";
        $seccSC = "";

    }
	
	print <<<U001
     <tr width="570" >
        <td >
		<BR>
       <table align="center" border="0" cellpadding="0" 
            cellspacing="0" width="570">
          <tbody>
          <form width="570" align="rigth" name="totales">
			<tr>
				<td class="inact" style="font-size: 12px;">&nbsp;
                        Total Materias Inscritas :&nbsp;</font>
				</td>
				<td class="inact" style="font-size: 12px;">&nbsp;
                        <input readonly="readonly" maxlength="2" size="1" 
                            name="t_mat1" value="0"
                            style="border-style: solid; border-width: 0px; 
                            text-align: center; font-family: arial; 
                            font-size: 12px; color: black; background-color: #99CCFF;">
                        &nbsp;
				</td>
				<td class="inact" style="font-size: 12px;">
                        Total Cr&eacute;ditos Inscritos:&nbsp;</font>
				</td>
				<td class="inact" style="font-size: 12px;">
                        <input readonly="readonly" maxlength="2" size="1" 
                            tabindex="1" name="t_uc1" value="0"
                            style="border-style: solid; border-width: 0px; 
                            text-align: center; font-family: arial; 
                            font-size: 12px; color: black; background-color: #99CCFF;">
                        &nbsp;
                </td>
            </tr>
			
			<tr>
				<td class="inact" style="font-size: 12px;">&nbsp;
                        Total Materias en Cola : &nbsp;</font>
				</td>
				<td class="inact" style="font-size: 12px;">&nbsp;
                        <input readonly="readonly" maxlength="2" size="1" 
                            name="t_mat2" value="0"
                            style="border-style: solid; border-width: 0px; 
                            text-align: center; font-family: arial; 
                            font-size: 12px; color: black; background-color: #FFFF66;" type="text">
                        &nbsp;
				</td>
				<td class="inact" style="font-size: 12px;">
                        <!-- Total Cr&eacute;ditos en Cola: -->&nbsp;
				</td>
				<td class="inact" style="font-size: 12px;">
                        <input readonly="readonly" maxlength="2" size="1" 
                            tabindex="1" name="t_uc2" value="0"
                            style="border-style: solid; border-width: 0px; 
                            text-align: center; font-family: arial; 
                            font-size: 12px; color: black; background-color: #FFFF66;" type="hidden">
                        &nbsp;
                </td>
            </tr>
			<tr><td>&nbsp;</td></tr>
            <tr>
				<td class="inact" style="font-size: 12px;">&nbsp;
                        <!-- Total Materias Seleccionadas: -->&nbsp;
				</td>
				<td class="inact" style="font-size: 12px;">&nbsp;
                        <input readonly="readonly" maxlength="2" size="1" 
                            name="t_mat" value="0"
                            style="border-style: solid; border-width: 0px; 
                            text-align: center; font-family: arial; 
                            font-size: 12px; color: black; background-color: #CCFFFF;" type="hidden">
                        &nbsp;
				</td>
				<td class="inact" style="font-size: 12px;">
                       <!--  Total Cr&eacute;ditos Seleccionados: -->&nbsp;
				</td>
				<td class="inact" style="font-size: 12px;">
                        <input readonly="readonly" maxlength="2" size="1" 
                            tabindex="1" name="t_uc" value="0"
                            style="border-style: solid; border-width: 0px; 
                            text-align: center; font-family: arial; 
                            font-size: 12px; color: black; background-color: #CCFFFF;" type="hidden">
                        &nbsp;
                </td>
            </tr>
          </form>  
          </tbody>
        </table>
        </td>
     </tr>
    <tr width="570" >
        <td >
        <table align="center" border="0" cellpadding="0" 
            cellspacing="0" width="400">
          <tbody>
          <form width="400" align="center" name="f_c" method="POST" action="registrar.php">
              <tr>
                    <td valign="top"><p align="left">
                        <input type="button" value="Borrar" name="B1" class="boton" 
                         onclick="javascript:reiniciarTodo();checkCols();"></p> 
                    </td>
                    <td valign="top"><p align="right">
                        <input type="button" value="Salir" name="B1" class="boton" 
                         onclick="javascript:self.close();"></p> 
                    </td>
                    <td><p align="right">
                        <input type="button" value="Procesar" name="B1"
							class="boton" 
                        onclick="Inscribirme();"></p>    
                        <input type="hidden" name="asignaturas" value="$lasAsignaturas">
                        <input type="hidden" name="asigSC" value="$asigSC">
                        <input type="hidden" name="seccSC" value="$seccSC">
                        <input type="hidden" name="exp_e" value="z">
                        <input type="hidden" name="cedula" value="x">
                        <input type="hidden" name="contra" value="{$_POST['contra']}">
                        <input type="hidden" name="carrera" value="z">
                        <input type="hidden" name="lapso" value="$dp[11]">
                        <input type="hidden" name="inscribe" value="$inscribe">
                        <input type="hidden" name="ind_acad" value="$dp[10]">          
                        <input type="hidden" name="inscrito" value="$inscrito">
                        <input type="hidden" name="sede" value="$sede">
                        <input type="hidden" name="sedeActiva" value="$sedeActiva">
                        <input type="hidden" name="sexo" value="$dp[14]">
                        <input type="hidden" name="f_nac_e" value="$dp[15]">
                        <input type="hidden" name="c_inicial" value="0">
                    </td>
                </tr>
            </form>
            </tbody>
          </table>
        </div>
       </td>
    </tr>
 </table>

<!-- codigo para definir la ventana de popup -->
<script>
if (NS4) {document.write('<LAYER NAME="floatlayer" style="visibility\:hide" LEFT="'+floatX+'" TOP="'+floatY+'">');}
if ((IE4) || (NS6)) {document.write('<div id="floatlayer" style="position:absolute; left:'+floatX+'; top:'+floatY+'; z-index:10; filter: alpha(opacity=0); opacity: 0.0; visibility:hidden">');
}
</script>
<table border="0" width="500" bgcolor="#2816B8" cellspacing="0" cellpadding="5">
<tr>
<td width="100%">
  <table border="0" width="100%" cellspacing="0" cellpadding="0" height="36">
  <tr>
  <td id="titleBar" style="cursor:move; text-align:center" width="100%">
  <ilayer width="100%" onSelectStart="return false">
  <layer width="100%" onMouseover="isHot=true;if (isN4) ddN4(theLayer)" onMouseout="isHot=false">
  <font face="Arial" size=2 color="#FFFFFF">
    VERIFICA Y CONFIRMA TU SELECCI&Oacute;N</font>
  </layer>
  </ilayer>
  </td>
  <td style="cursor:pointer" valign="top">
  <a href="#" onClick="hideMe();return false"><font color=#ffffff size=2 face=arial  style="text-decoration:none; vertical-align:top;">X</font></a>
  </td>
  </tr>
  <tr>
  <td width="100%" bgcolor="#FFFFFF" style="padding:4px" colspan="2">
<!-- PLACE YOUR CONTENT HERE //-->  
<table>
<tr><td colspan=2> <span style="font-family:Arial; font-size:13px; font-weight:bold;
                                text-align:left">
$dp[2]:<br>Por favor escribe de nuevo tu clave
 y pulsa "Aceptar" para procesar tu selecci&oacute;n. RECUERDA: Despu&eacute;s
 de procesada la inscripci&oacute;n ya NO podr&aacute;s hacer cambios.</span>
    <td>
</tr>
<tr><td colspan=2>
<span style="font-family:Arial; font-size:13px; font-weight:bold;
             text-align:left; background-color:#FFFF33">
	Y POR FAVOR INDICA TU SEXO Y TU FECHA DE NACIMIENTO CORRECTA PARA PODER 
		CONTINUAR CON LA INSCRIPCI&Oacute;N:</td></tr>
<tr>
  <td colspan=2 valign="middle"><p align="left">
     <font face=arial size=2><b> Clave:&nbsp;</b>
       <input type="password" name="pV" id="pV"
         style="background-color:#99CCFF" size="20"> 
  </td>
</tr>
<tr><td style="font-family:Arial; font-size:13px; font-weight:bold;">
		Sexo:</td><td style="font-family:Arial; font-size:13px; font-weight:bold;">
		Fecha de Nacimiento:</td>
<tr><td><select name="sexoN" id="sexoN">
              <option value="1" >Masculino</option>
              <option value="0" >Femenino</option>
              </select></td>

    <td><select name="diaN" id="diaN">
        <option > 01</option>
        <option > 02</option>
        <option > 03</option>
        <option > 04</option>
        <option > 05</option>
        <option > 06</option>
        <option > 07</option>
        <option > 08</option>
        <option > 09</option>
        <option > 10</option>
        <option > 11</option>
        <option > 12</option>
        <option > 13</option>
        <option > 14</option>
        <option > 15</option>
        <option > 16</option>
        <option > 17</option>
        <option > 18</option>
        <option > 19</option>
        <option > 20</option>
        <option > 21</option>
        <option > 22</option>
        <option > 23</option>
        <option > 24</option>
        <option > 25</option>
        <option > 26</option>
        <option > 27</option>
        <option > 28</option>
        <option > 29</option>
        <option > 30</option>
        <option > 31</option>
		</select> de
        <select name="mesN" id="mesN">
		<option value="01" >ENERO</option>
        <option value="02" >FEBRERO</option>
		<option value="03" >MARZO</option>
        <option value="04" >ABRIL</option>
        <option value="05" >MAYO</option>
		<option value="06" >JUNIO</option>
        <option value="07" >JULIO</option>
        <option value="08" >AGOSTO</option>
        <option value="09" >SEPTIEMBRE</option>
        <option value="10" >OCTUBRE</option>
        <option value="11" >NOVIEMBRE</option>
        <option value="12" >DICIEMBRE</option>
        </select> de 19
        <input name="anioN" type="text" class="inputtext" id="anioN" value="" size="2" maxlength="2">
</td></tr></span>
<tr><td colspan='4'><FONT SIZE="" COLOR="#FF0000"><B>RECUERDA QUE PARA FORMALIZAR TU INSCRIPCION, DEBES LLEVAR DOS PLANILLAS IMPRESAS PARA SELLARLAS EN CONTROL DE ESTUDIOS</B></FONT></td></tr>
<tr>
  <td valign = "middle"><p align="center">
     <input type="button" value="Aceptar" name="aBA" class="boton" onclick="verificar()"> 
     </td>
   <td valign = "middle"><p align="center">
     <input type="button" value="Cancelar" name="aBC" class="boton" onclick="cancelar()"> 
   </td>  
 </tr>
 </table>
     
<!-- END OF CONTENT AREA //-->
  </td>
  </tr>
  </table> 
</td>
</tr>
</table>
</div>

<script>
if (NS4)
{
document.write('</LAYER>');
}
if ((IE4) || (NS6))
{
document.write('</DIV>');
}
ifloatX=floatX;
ifloatY=floatY;
lastX=-1;
lastY=-1;
define();
window.onresize=define;
window.onscroll=define;
adjust();
U001
;
    print <<<U004
</script>
</body>
</html>
U004
;
    }
    
    function volver_a_indice($vacio,$fueraDeRango, $habilitado=true){
	
    //regresa a la pagina principal:
	global $raizDelSitio, $cedYclave;
    if ($vacio) {
?>
            <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
            <META HTTP-EQUIV="Refresh" 
            CONTENT="0;URL=<?php echo $raizDelSitio; ?>">
        </head>
        <body>
        </body>
        </html>
<?php
    }
    else {
?>          <script languaje="Javascript">
            <!--
            function entrar_error() {
<?php
        if ($fueraDeRango) {
			if($habilitado){
?>             
		mensaje = "Lo siento, no puedes inscribirte en este horario.\n";
        mensaje = mensaje + "Por favor, espera tu turno.";
<?php
			}
			else {
?>
	    mensaje = 'Lo siento, no esta habilitado el sistema.';
<?php
			}
		}
        else {
			if(!$cedYclave[0]){
?>
        mensaje = "La cedula no esta registrada o es incorrecta.\n";
		//mensaje = mensaje + "Es posible que usted deba solicitar REINGRESO\n";
		//mensaje = mensaje + "si se retiro en el semestre anterior.";
<?php
			}	
			else if (!$cedYclave[1]) {
?>
        mensaje = "Clave incorrecta. Por favor intente de nuevo";
<?php
			}
			else if (!$cedYclave[2]) {
?>
        mensaje = "Codigo de seguridad incorrecto. Por favor intente de nuevo";
<?php
			}
		}
?>
                alert(mensaje);
                window.close();
                return true; 
        }

            //-->
            </script>
        </head>
                    <body onload ="return entrar_error();" >

        </body>
<?php 
	global $noCacheFin;
	print $noCacheFin; 
?>
</html>
<?php
    }
}    

function alumno_en_rango($horaTurno, $fechaTurno) {

	$fechaActual = time() - 3600*date('I');
	$tHora = intval(substr($horaTurno ,0,2),10);
	$tMin = intval(substr($horaTurno,2,2),10);
	$tFecha = explode('-',$fechaTurno); //anio-mes-dia
@	$suFecha = mktime($tHora, $tMin, 0, $tFecha[1], $tFecha[2], $tFecha[0],date('I'));
	// $suFecha = date('I');
	//print_r ($horaTurno.'sss'.$fechaTurno);
	//print_r ($suFecha.'xxx'.$fechaActual);
	return ($suFecha <= $fechaActual);
}

    // Programa principal
    //leer las variables enviadas
    //$_POST['cedula']='17583838';
    //$_POST['contra']='827ccb0eea8a706c4c34a16891f84e7b';       
    if(isset($_POST['cedula']) && isset($_POST['contra'])) {
        $cedula=$_POST['cedula'];
        $contra=$_POST['contra'];
        // limpiemos la cedula y coloquemos los ceros faltantes
        $cedula = ltrim(preg_replace("/[^0-9]/","",$cedula),'0');
        $cedula = substr("00000000".$cedula, -8);
        $fvacio = false; 
		//echo $cedula;
		//echo $contra;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <meta content="text/html; charset=ISO-8859-1" http-equiv="content-type">
<?php
print $noCache; 
print $noJavaScript; 
?>
<title><?php echo $tProceso .' '. $lapso; ?></title>
<?php
        $cedYclave = cedula_valida($cedula,$contra);
		if(!$fvacio && $cedYclave[0] && $cedYclave[1] && $cedYclave[2]) {
            // Revisamos si es su turno de inscripcion:
				if(alumno_en_rango($datos_p[8],$datos_p[9])) {
			// Para las preinscripciones no se chequea el rango de inscripcion
			// Asi que alumno_en_rango siempre sera 'TRUE' 
			// if(true) {
            // pintamos su pensum y su formulario para llenar:
            // ya tenemos en $datos_p los datos personales
                $exped    = $datos_p[1];
                $mencion  = $datos_p[5];
                $pensum   = $datos_p[6];
                $c_carr   = $datos_p[7];
                $lapso    = $datos_p[11];
				$inscrito = '0'; //intval('0'.$datos_p[13]);
                $Cmat = new ODBC_Conn($sede,"c","c");
				// echo $sede.'[cmat]';
				//echo $exped;
				$mSQL = "SELECT pensum ";
				$mSQL = $mSQL."FROM dace002 ";
				$mSQL = $mSQL."WHERE estatus_e='1' and exp_e='".$exped."' ";
				$Cmat->ExecSQL($mSQL);
				$pensum=$Cmat->result;
				foreach ($pensum as $p){}
				//echo $p[0];
				if ($p[0] == '5'){$pensumPoz='5';}else $pensumPoz='4';

				// Articulo 7
				$conex = new ODBC_Conn($sede,"c","c");
				$sSQL = " SELECT lapso_in FROM dace002 WHERE exp_e='".$exped."' AND c_ingreso<> '2' ";
				$conex->ExecSQL($sSQL);

				if (@$conex->result[0][0] == $lapso){
					die("<script languaje=\"javascript\"> alert('Disculpe, los estudiantes de nuevo ingreso no pueden cambiarse de seccion. Para mas información consulte el reglamento de retiros en nuestro marco legal.'); window.close(); </script>");
				}

				// 
				/*$conex = new ODBC_Conn($sede,"c","c");
				$sSQL = " SELECT seccion FROM dace006 WHERE exp_e='".$exped."' AND c_asigna='300101' AND lapso='2013-1' AND seccion='m1' ";
				$conex->ExecSQL($sSQL);

				if (@$conex->result[0][0] == 'm1'){
					die("<script languaje=\"javascript\"> alert('Disculpe, los estudiantes de nuevo ingreso no pueden cambiarse de seccion. Para mas información consulte el reglamento de retiros en nuestro marco legal.'); window.close(); </script>");
				}*/

				if ( $sedeActiva == 'POZ' ) {
					#para no mostrar ciudadania
					$CDD = "SELECT c_asigna FROM dace004 WHERE ";
					$CDD.= "(status='0' OR status='3' OR status='B') AND ";
					$CDD.= "exp_e='".$exped."' AND c_asigna='300677'";
					$Cmat->ExecSQL($CDD);
					if ($Cmat->filas == '1'){
						$ciud=" AND tblaca008.c_asigna<>'300676' ";
					}else $ciud=' ';

					#para no mostrar venezuela
					$VEN = "SELECT c_asigna FROM dace004 WHERE "; 
					$VEN.= "(status='0' OR status='3' OR status='B') AND ";
					$VEN.= "exp_e='".$exped."' AND c_asigna='300676'";
					$Cmat->ExecSQL($VEN);
					if ($Cmat->filas == '1'){
						$venez=" AND tblaca008.c_asigna<>'300677' ";
					}else $venez=' ';

					$mSQL = "SELECT tblaca009.semestre, tblaca008.c_asigna, asignatura, ";
					$mSQL.= "tblaca009.u_creditos, co_req, repite, cre_cur, tipo_lapso  ";
					$mSQL.= "FROM materias_inscribir, tblaca009 , tblaca008 WHERE ";
					$mSQL.= " materias_inscribir.c_asigna=tblaca009.c_asigna AND "; 
					$mSQL.= " mencion='".$mencion."' AND pensum='".$pensumPoz."' ";
					$mSQL.= " AND exp_e='".$exped."' AND c_uni_ca='".$c_carr."' ";
					$mSQL.= " AND tblaca008.c_asigna=tblaca009.c_asigna ";
					$mSQL.= " AND tblaca008.c_asigna NOT IN(SELECT c_asigna FROM ";
					$mSQL.= " dace004 where (status='0' OR status='3' OR status='B') ";
					$mSQL.= " AND exp_e='".$exped."' )";
					$mSQL.= " ".$ciud." ";
					$mSQL.= " ".$venez." ";
					$mSQL.= " AND materias_inscribir.c_asigna<>'300622' ";
					$mSQL.= " AND materias_inscribir.c_asigna IN (";
					$mSQL.= "SELECT c_asigna FROM dace006 WHERE exp_e='".$exped."' ";
					$mSQL.= " AND lapso='".$lapso."' AND status IN ('7','A')";
					$mSQL.= ") ";
					$mSQL.= " ORDER BY semestre";
					//echo $mSQL;
				}
				else {
					$mSQL = "SELECT semestre, tblaca008.c_asigna, asignatura, ";
					$mSQL = $mSQL."tblaca008.unid_credito, co_req, repite, cre_cur, tipo_lapso FROM";
					$mSQL = $mSQL." materias_inscribir, tblaca009 , tblaca008 WHERE ";
					$mSQL = $mSQL."materias_inscribir.c_asigna=tblaca009.c_asigna AND "; 
					$mSQL = $mSQL."mencion='".$mencion."' AND pensum='".$pensum."' ";
					$mSQL = $mSQL."AND exp_e='".$exped."' AND c_uni_ca='".$c_carr."' ";
					$mSQL = $mSQL."AND tblaca008.c_asigna=tblaca009.c_asigna ORDER BY semestre";
				}
                $Cmat->ExecSQL($mSQL);
				$lista_m=$Cmat->result;
				$mSQL = "SELECT n_planilla, monto FROM depositos WHERE exp_e='".$exped."'";
				$Cmat->ExecSQL($mSQL);
				$depositos = $Cmat->result;
				unset($Cmat);
                $carr_esp= array('.'=>"",
                                 'A'=>" (COMUNICACIONES)", 
                                 'B'=>" (COMPUTACI&Oacute;N)",
                                 'C'=>" (CONTROL)");
                $datos_p[4] = $datos_p[4].$carr_esp[$datos_p[5]];
				if ($inscHabilitada) {
					imprime_primera_parte($datos_p);
                    imprime_pensum($lista_m);
					imprime_ultima_parte($datos_p);
				}
				else volver_a_indice(false,true,false);//inscripciones no habilitadas
            }
            else volver_a_indice(false,true); //alumno fuera de rango
        }
        else volver_a_indice(false,false); //cedula o clave incorrecta
    }
    else volver_a_indice(true,false); //formulario vacio
?>
