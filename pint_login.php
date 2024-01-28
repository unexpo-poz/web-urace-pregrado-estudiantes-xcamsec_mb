<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<?php
include_once('inc/config.php'); 
?>
<head>
<title><?php echo $tProceso .' '. $lapsoProceso; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<?php 
print $noCache; 
print $noJavaScript; 
?>
<script languaje="Javascript">
<!--
	//alert('hola opera');
	
	//alert(navigator.appName);
	/*if ((navigator.appName == "Microsoft Internet Explorer" )){
		alert("Disculpe, su cliente http no esta soportado en este sistema. Use Mozilla Firefox para acceder al sistema."); 
		location.replace("no-soportado.php");	//	return; 
	}*/
//-->
</script>          

</head>

<frameset cols="750" rows="145,33,*" border=1 framespacing=0 frameborder="no">
    <frame src="pint_login_titulo.php" MARGINWIDTH="0" MARGINHEIGHT="0" NORESIZE scrolling="no">
    <frameset cols="960,170,*" border=3 framespacing="0" frameborder="no">
        <frame src="pint_login_hora.php" MARGINWIDTH="0" MARGINHEIGHT="0" NORESIZE scrolling="no">
        <frame src="hora_actual.php" MARGINWIDTH="0" MARGINHEIGHT="0" NORESIZE scrolling="no">
   </frameset>
    <frame src="pint_login_form.php" MARGINWIDTH="0" MARGINHEIGHT="0" NORESIZE scrolling="no">
</frameset><noframes></noframes>
<body <?php echo $botonDerecho ?> onLoad="javascript:self.name='login_insc';" bgcolor="#FFFFFF">
</body>
<?php print $noCacheFin; ?>
</html>
