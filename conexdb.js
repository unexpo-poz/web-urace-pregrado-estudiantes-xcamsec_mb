var xmlHttp;
var depositosOK;
var consultando;

function revisarDepositos(str) { 
	var url="revisar_dep.php?depositos="+str;
	xmlHttp=GetXmlHttpObject(stateChanged);
	xmlHttp.open("GET", url , true);
	xmlHttp.send(null);
} 

function stateChanged() { 
	if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete") { 
		respuesta = xmlHttp.responseText;
		//alert(respuesta);
		if (xmlHttp.responseText.indexOf("OK") >= 0) {
			document.f_c.submit();
			return true;
		}
		else {
			arrDep = respuesta.split(' ');
			errStr  = 'Error!\n Los siguientes depositos ya estan\n ';
			errStr += 'registrados por los expedientes indicados:\n';
			i = 0;
			while (i < arrDep.length) {
				errStr += 'Planilla: '+arrDep[i]+', Exp: '+arrDep[i+1] +'\n';
				with (document.f_c){
					for(j=0; j < p_dep.length;j++){
						if (p_dep[j].value !== '' && p_dep[j].value == arrDep[i]) {
							p_dep[j].style.backgroundColor="#FF0066";
							//p_dep[j].style.color="#FFFFFF";
						}
					}

				}
				i +=2;
			}
			errStr += '\nLos datos duplicados se indican en fondo rojo.';
			alert(errStr);
			cancelar();
		} 
	}
}

function GetXmlHttpObject(handler) { 
	
	var objXmlHttp=null;

	if (navigator.userAgent.indexOf("Opera")>=0) {
		alert("Opera no esta soportado en este sistema"); 
		window.close;
		return; 
	}
	if (navigator.userAgent.indexOf("MSIE")>=0) { 
		var strName="Msxml2.XMLHTTP";
		if (navigator.appVersion.indexOf("MSIE 5.5")>=0) {
			strName="Microsoft.XMLHTTP";
		} 
		try { 
			objXmlHttp=new ActiveXObject(strName);
			objXmlHttp.onreadystatechange=handler; 
			return objXmlHttp;
		} 
		catch(e) { 
			alert("Error. Los controles ActiveX pueden estar deshabilitados") ;
			return ;
		} 
	} 
	if (navigator.userAgent.indexOf("Mozilla")>=0) {
		objXmlHttp=new XMLHttpRequest();
		objXmlHttp.onload=handler;
		objXmlHttp.onerror=handler; 
		return objXmlHttp;
	}
} 

