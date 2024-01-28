<?php
 //-------------------------------------------------------------- 
 // Clase cliente para Conexion ODBC local This is the ODBC Socket Server class PHP client class
 // (c) 2005 Luis Tarazona - UNEXPO 
 $ODBCC_conBitacora = true;
 $ODBCC_sinBitacora = false;

 class ODBC_Conn {
	var $result = array(); // Resultado en una tabla
    var $result_h = array(); // Nombres de los campos en la tabla result  
	var $status =""; // Estado: 'OK', 'Tipo_de_Error'
    var $filas = 0; // Total de registros obtenidos
    var $fmodif= 0; // Total de registros modificados  
	var $usuario = "";
	var $clave = "";
	var $DSN = "";
	var $noSELECT = false; //Indica si es una consulta (SELECT) o no
	var $aBitacora = "";
	var $habBitacora = false;
	var $registroB = "";
	var $fecha = "";
	var $conex; //La manija de la conexion ODBC

	function escribirBitacora() {
			//file_put_contents($this->aBitacora, $this->registroB, FILE_APPEND);
			$fbit = @fopen($this->aBitacora,'a+');
			@fwrite($fbit, $this->registroB);
			@fclose($fbit);
			$this->registroB = "";
	}

	function iniciarTransaccion($msg='') {
		if (odbc_autocommit($this->conex, false)){
			$ti = true;
		}
		else {
			$ti = $false;
			$msg .= ' ERROR';
		}
		$this->registroB .=  "\n*:".$this->fecha."[".$_SERVER['REMOTE_ADDR']."] ".$msg."\n";
		$this->escribirBitacora();
		return $ti;
	}
	
	function finalizarTransaccion($msg='') {
		if (odbc_commit($this->conex)){
			$ti = true;
		}
		else {
			$ti = $false;
			$msg .= " ERROR";
		}
		$this->registroB .= "*:".$this->fecha."[".$_SERVER['REMOTE_ADDR']."] ".$msg."\n\n";
		$this->escribirBitacora();
		odbc_autocommit($this->conex, true);
		return $ti;
	}

	function deshacerTransaccion($msg='') {
		if (odbc_rollback($this->conex)){
			$ti = true;
		}
		else {
			$ti = $false;
			$msg .= " ERROR";
		}
		$this->registroB .= "*:".$this->fecha."[".$_SERVER['REMOTE_ADDR']."] ".$msg."\n\n";
		$this->escribirBitacora();
		odbc_autocommit($this->conex, true);
		return $ti;
	}



	// Constructor	 
    function ODBC_Conn($SDSN, $Susuario, $Sclave, $habBitacora = false, $aBitacora="errores.log") {
        $this->DSN = $SDSN;
		$this->usuario = $Susuario;
		$this->clave = $Sclave;
		$this->habBitacora = $habBitacora;
		$this->aBitacora = $aBitacora;
		list($usec, $sec) = explode(" ", microtime()); 
		$csec = substr(substr("000000".$usec,-6),0,2); //devuelve las centesimas de segundo
		$this->fecha  = '['.date('d/m/Y ',time() - 3600*date('I'));
		$this->fecha .= date('H:i:s.', time() - 3600*date('I')).$csec.']';
		$this->conex = odbc_connect($this->DSN, $this->usuario, $this->clave);
		//$this->conex = $conex;
		if(! $this->conex){
			$this->registroB = $this->registroB . " {".odbc_errormsg()."}\n";
			$this->escribirBitacora();
			//die ($this->registroB);
			return true;
		}
    }
	// Convierte el resultado de la consulta a una tabla
    function result2array($respuesta) {
        unset($this->result);
        $this->result=array();
        unset($this->result_h);
        $this->result_h=array();
		if ($this->noSELECT) { // No es un select
			$this->fmodif = odbc_num_rows($respuesta);
			$this->filas = 0;
		}
		else { 
			$cols = odbc_num_fields($respuesta);
			for ($i=0; $i < $cols; $i++){
				$this->result_h[$i] = odbc_field_name($respuesta,$i+1);
			}
		$i = 0;
		while (0 <($cols = odbc_fetch_into($respuesta,$this->result[$i]))){
			$i++;
		}
		array_pop($this->result); // remueve registro vacio generado al fallar odbc_feth_into
			$this->filas = $i;
			$this->fmodif = 0;	
		}
		if (odbc_autocommit($this->conex)) {
			odbc_free_result($respuesta);
		}
	}
	
	
	function ExecSQL($sSQL, $linea = "N_E", $actBitacora = false) {

		//print '<hd><br><pre>'. $sSQL . '</pre><br>'; 
		$todoOK = true;
		if (odbc_autocommit($this->conex)) {
			$autoCommStr = 'C:';
		}
		else {
			$autoCommStr = '*:';
		}

		$this->noSELECT = (strpos(strtoupper($sSQL), 'SELECT') === false);
		//$conex = odbc_connect($this->DSN, $this->usuario, $this->clave);
		$this->registroB .= $autoCommStr.$this->fecha."[".$_SERVER['REMOTE_ADDR']."]";
		$this->registroB .= "[".$linea."] ".$sSQL;
		$this->filas = 0;
		$this->fmodif = 0;
		$respuesta = odbc_do($this->conex, $sSQL);		
		if (!$respuesta) {
			$this->registroB .= " [".$this->filas ."][".$this->fmodif."] ";
			$this->registroB = $this->registroB . " {".odbc_errormsg()."}\n";
			$this->escribirBitacora();
			//die ($this->registroB);
			return true;
		}
		$this->status = odbc_errormsg();
		if ($this->status == '') {
			$this->status = 'OK';
		}
		$this->result2array($respuesta);
		$this->registroB .= " [".$this->filas ."][".$this->fmodif."] ";
		$this->registroB .= $this->status."\n";
		if ($actBitacora && $this->habBitacora){
			$this->escribirBitacora();
		}
		usleep(20000);
		return true;
	}
 }//class
// El servidor ODBCSS a usar:

function muestrame($variable) {
		print '<pre>';
		print_r($variable);
		print '</pre>';
	}
?>
