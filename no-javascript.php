<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<?php
include_once('inc/config.php'); 
?>
<head>
<title><?php echo $tProceso . $lapso; ?></title>

  <style type="text/css">
          <!--
#prueba {
  overflow:hidden;
}

.titulo {
  text-align: center; 
  font-family:Arial; 
  font-size: 14px; 
  font-weight: normal;
  margin-top:0;
  margin-bottom:0;	
}

  -->
          </style>  
          
</head>


<body>

<table border="0" width="750">
		<tr>
		<td width="125">
		<p align="right" style="margin-top: 0; margin-bottom: 0">

		<img border="0" src="/img/unex15.gif" 
		     width="75" height="75"></p></td>
		<td width="500">
		<p class="titulo">
		Universidad Nacional Experimental Polit&eacute;cnica</p>
		<p class="titulo">
		Vicerrectorado <?php echo $vicerrectorado; ?></font></p>
		<p class="titulo">

		Unidad Regional de Admisi&oacute;n y Control de Estudios</font></td>
		<td width="125">&nbsp;</td>
		</tr><tr><td colspan="3" style="background-color:#99CCFF;">
		<font style="font-size:2px;"> &nbsp;</font></td></tr>
		<tr><td colspan="3"> Disculpe. Su navegador no tiene activado JavaScript.
		Active Javascript y vuelva a intentar haciendo clic
		<a href="<?php echo $raizDelSitio; ?>">aqu&iacute;</a>.
		</td></tr>
</table>
</body>
</html>
