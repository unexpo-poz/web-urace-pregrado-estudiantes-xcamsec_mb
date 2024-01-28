<?php
include_once('inc/odbcss_c.php');
include_once ('inc/config.php');

$c_asigna = $_POST['c_asigna'];
$seccion = $_POST['seccion'];

//echo $_POST['c_asigna']."-".$_POST['seccion'];

// Si llegan los datos y la seccion es diferente a grupo
if ((isset($_POST['c_asigna'])) and (substr($_POST['seccion'],0,1) != 'G')){
	
	$conex = new ODBC_Conn($sedesUNEXPO[$sedeActiva][0],"c","c");

	$gSQL = "SELECT grupo FROM tblaca004_lab WHERE inscritos<tot_cup AND lapso='".$lapsoProceso."'  ";
	$gSQL.= "AND c_asigna='".$c_asigna."' AND seccion='".$seccion."' ";

	//echo $gSQL;

	@$conex->ExecSQL($gSQL);

	if ($conex->filas > 0){
print <<<GRUPO_LAB
	<select name="g$c_asigna" id="g$c_asigna" class="peq">
		<option value="">SELECCIONE</option>
GRUPO_LAB;

	for ($i=0;$i < $conex->filas;$i++){
			echo "<option value=\"".$conex->result[$i][0]."\">GRUPO ".$conex->result[$i][0]."</option>";
		}	
print <<<GRUPO_LAB
	</select>	
GRUPO_LAB;

	}else{
print <<<GRUPO_LAB
	<select name="g$c_asigna" id="g$c_asigna" class="peq" disabled>
		<option value="">SIN-CUPO</option>
	</select>	
GRUPO_LAB;

	}
}else{
print <<<GRUPO_LAB
	<select name="g$c_asigna" id="g$c_asigna" class="peq" disabled>
		<option value="{$_POST['seccion']}">GRUPO {$_POST['seccion']}</option>
	</select>	
GRUPO_LAB;

}




//<select name="g$c_asigna" id="g$c_asigna" class="peq" disabled>
//</select>

?>