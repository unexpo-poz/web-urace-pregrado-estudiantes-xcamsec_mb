<?php
    print <<<P1
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>

<title>Servicios UNEXPO Vicerrectorado Puerto Ordaz</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<META HTTP-EQUIV="Refresh" CONTENT="URL=https://ortsi.bqto.unexpo.edu.ve/inscripcion/hora_actual.php" target="_SELF">
</head>
<body onload="javascript:setInterval('location.reload()',60000);">
<table style="border-collapse: collapse;" border="0" 
   cellpadding="0" cellspacing="1" width="90" align="left" bgcolor="#254B72">
   <tr height="10">
      <td width="80" align="left">
    <span style="font-family:arial; font-weight:800; font-size:16px; color:white;">
P1;

	$h = "4.5";
	$hm = $h*60;
	$ms = $hm*60;
	$hora = gmdate("g:i a",time()-($ms));
    //$hora = date('h:i a',time() - 3600*date('I'));
    print "&nbsp;$hora&nbsp;";
    print <<<P2
          </span></td>
   </tr>
</table>
</body>
</html>
P2
;
?>
