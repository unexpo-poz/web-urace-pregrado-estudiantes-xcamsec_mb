<?php
	
    include_once('../inc/odbcss_c.php');

	function depositos_correctos() {
        
               
        $todook = true;       
        $dep	= array();
        $dep	= explode(" ",$_GET['depositos']);
        array_pop($dep);
        $total_d = count($dep);
		if ($total_d > 0) {
			$listaPlanillas = implode("','",$dep);
			$listaPlanillas = "('".$listaPlanillas."')";
            $Cdep = new ODBC_Conn($_GET['sede'],"c","c");
            $dSQL = "SELECT n_planilla, exp_e FROM depositos WHERE n_planilla in ".$listaPlanillas;
            $Cdep->ExecSQL($dSQL);
            if($Cdep->filas > 0) {
				$todook = false;
				$pDup = array();  
				foreach($Cdep->result as $pe) {
					$pDup = array_merge($pDup,$pe);
				}
				$pDuplicadas = implode(" ",$pDup);
  				return $pDuplicadas;
            }
  			else {
				return 'OK';
            }
        }
		else {
			return 'OK';
		}
	}            

print depositos_correctos();
?>