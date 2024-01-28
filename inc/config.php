<?php
$enProduccion		= false;
$raizDelSitio		= 'http://'.$_SERVER['SERVER_NAME'].'/web/urace/pregrado/estudiantes/camsec_mb/';
$urlDelSitio		= 'http://'.$_SERVER['SERVER_NAME'].'/web/urace';

$lapsoProceso		= '2016-U';
$tLapso				= ' Lapso '.$lapsoProceso;

$laBitacora	 = $_SERVER[DOCUMENT_ROOT].'/log/pregrado/estudiantes/cambio/camsec-'.$lapsoProceso.'.log';

$inscHabilitada		= false;
$modoInscripcion	= '2'; // 1: Inscripcion, 2: Inclusion y Retiro

if ($modoInscripcion == '1'){
	$tProceso	= 'Inscripci&oacute;n de Alumnos Regulares Pregrado Ingenier&iacute;a (Cola) MODO 1';
}
else if ($modoInscripcion == '2'){
	$tProceso	= 'Cambio de Seccion';
}

//Si se requiere imprimir en planilla un mensaje extra, activar esto y colocarlo
// en inc/msgExtra.php
$mensajeExtra		= false; 
//Las distintas sedes de UNEXPO - actualizar de acuerdo a la necesidad
$sedesUNEXPO = array (	'CCS' => array('BQTO','CARORA'), 
						'CCS_'  => array('DACECCS'),
						'POZ'  => array('CENTURA-DACE')
						//'POZ'  => array('DACEPOZ')
				);

//$sedeActiva = 'BQTO';
//$sedeActiva = 'CCS';
$sedeActiva = 'POZ';
$pensumPoz = '5';

$nucleos = $sedesUNEXPO[$sedeActiva];

//$vicerrectorado		= "Luis Caballero Mej&iacute;as";
//$vicerrectorado		= "Barquisimeto";
$vicerrectorado		= "Puerto Ordaz";
$nombreDependencia = 'Unidad Regional de Admisi&oacute;n y Control de Estudios';

// * * * * * OJO OJO OJO OJO * * * * * 
// Cambiar esto manualmente de acuerdo a la jornada.
// Tipo de jornada
//	0 : deshabilitado 
//	1 : solo preinscritos en las materias preinscritas.
//	2 : solo preinscritos, pero pueden cambiar las materias.
//	3 : todos preinscritos o no preinscritos
$tipoJornada = 0;
$tablaOrdenInsc = 'ORDEN_INSCRIPCION';

//Unidad Tributaria y Costo de las materias:
$unidadTributaria	= 33600;
$valorPreMateria	= 0.2*$unidadTributaria;
$valorMateria		= 89720;
// Maximo numero de depositos a presentar:
$maxDepo			= 8;

// Proteccion de las paginas contra boton derecho, no javascript y navegadores no soportados:
if ($enProduccion){
	$botonDerecho = 'oncontextmenu="return false"';
	$noJavaScript = '<noscript><meta http-equiv="REFRESH" content="0;URL=no-javascript.php"></noscript>';
	$noCache	  = "<meta http-equiv=\"Pragma\" content=\"no-cache\">\n";
	$noCache	 .= '<meta http-equiv="Expires" content="-1">';
	$noCacheFin	  = '<head><meta http-equiv="Pragma" content="no-cache"></head>';
}
else {
	$botonDerecho = '';
	$noJavaScript = '';
	$noCache	  = '';
	$noCacheFin	  = '';
}
?>