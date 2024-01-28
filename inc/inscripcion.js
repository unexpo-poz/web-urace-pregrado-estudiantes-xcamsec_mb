
function vrLCM(){
	 return (document.f_c.sedeActiva.value.indexOf("CCS") >= 0);
}

function vrPOZ(){
	 return (document.f_c.sedeActiva.value.indexOf("POZ") >= 0);
}

function marcarAsignaturas(asignaturas,asigSC) {

    var cod_uc = new Array();
    scod_uc = "";
    asigs = asignaturas.split(" ");
    with (document.pensum) {
        i = 0; 
        j = 0;
        while (j < asignaturas.length){
            i = 0;
            while(i < (CB.length - 1)){
                cod_uc = CB[i].value.split(" ");  
                if ((cod_uc[0] == asigs[j]) && (cod_uc[0] != asigSC )){
                    CB[i].selectedIndex = parseInt(asigs[j+3],10); 
                }
                i++;
            }
            j = j + 4;
        } 
    }
}

function prepdata(fp,fd) {
    
    fd.cedula.value = ced;
    fd.exp_e.value = exp_e;
    fd.contra.value = contra;
    fd.carrera.value = carrera;
    with (fd) {
		//alert(asigSC.value);
        if(asigSC.value != "") {
            marcarAsignaturas(asignaturas.value, asigSC.value);            
            scMsg = "Lo siento, ya no hay cupo en \n";
            scMsg = scMsg + "la sección: " + seccSC.value + "\nde la asignatura: " + asigSC.value;
            scMsg = scMsg + "\n Por favor, modifique su selección";
            asigSC.value ="";
            alert(scMsg);
       }
        else asignaturas.value = "";
    }
    
    var cod_uc = new Array();
    scod_uc ="";
	fd.asignaturas.value = "";
    with(fp) {
        i = 0;
        while(i < (CB.length - 1)){
		  cod_uc = CB[i].value.split(" ");
		  if (cod_uc[5] !='0'){
            // $_POST['asignaturas'] lleva : CODIGO1 SECCION1 condREP1 COLOR1 CODIGO2 SECCION2 condREP2 COLOR2...    
            //scod_uc = cod_uc[0] + " " + cod_uc[5] + " " + cod_uc[6] + " " + cod_uc[8] + " " + cod_uc[7];
			scod_uc = cod_uc[0] + " " + cod_uc[5] + " " + cod_uc[6] + " " + cod_uc[7];
			fd.asignaturas.value = fd.asignaturas.value + scod_uc  + " "; 
          }
		  cod_uc = cola[i].value.split(" ");
		  if (cod_uc[5] !='0'){
            // $_POST['asignaturas'] lleva : CODIGO1 SECCION1 condREP1 COLOR1 CODIGO2 SECCION2 condREP2 COLOR2...    
            //scod_uc = cod_uc[0] + " " + cod_uc[5] + " " + cod_uc[6] + " " + cod_uc[8] + " " + cod_uc[7];
			scod_uc = cod_uc[0] + " " + cod_uc[5] + " " + cod_uc[6] + " " + cod_uc[7];;
			fd.asignaturas.value = fd.asignaturas.value + scod_uc  + " "; 
          }
          i++;
        }
    }
    //registra sexo y fecha de nac:
	if (fd.c_inicial.value != "0"){
		laFechaS =	1900 + parseInt(document.getElementById('anioN').value,10); 
		laFechaS += '-';
		laFechaS +=	document.getElementById('mesN').selectedIndex + 1;
		laFechaS += '-';
		laFechaS +=	document.getElementById('diaN').selectedIndex + 1; 
		document.f_c.f_nac_e.value = laFechaS;
		elSexo  = parseInt(document.getElementById('sexoN').value,10);
		aSexo   = Array('1','2','1');
		document.f_c.sexo.value = aSexo[elSexo];
	}
    if(fd.inscribe.value == fd.inscrito.value) {
        fd.submit();
        return true;
    }
    return true;
}

function actualizarTotales(fp,ft,$update) {
      
    ct_mat		= 0;
    ct_uc		= 0;
	ct_mat1		= 0;
    ct_uc1		= 0;
	ct_mat2		= 0;
    ct_uc2		= 0;
    k =fp.CB.length - 1;
	c =fp.cola.length;
	//alert ('cola= '+c);
    with(fp) {
       j = 0;
       while(j < k){
		//	alert('j='+j+'{'+CB[j].value+'} indice='+CB[j].selectedIndex);
          if (CB[j].selectedIndex != '0'){ 
              cod_uc = CB[j].value.split(" ");               
              uc   = parseInt(cod_uc[1],10);
              ct_mat++;
              ct_uc+=uc;
			  ct_mat1++;
              ct_uc1+=uc;
          }
          j++;
       }
	   d = 0;
	   while(d < c){
			//alert('d='+d+'{'+cola[d].value+'} indice='+cola[d].selectedIndex);
          if (cola[d].selectedIndex != '0'){ 
              cod_uc = cola[d].value.split(" ");               
              uc   = parseInt(cod_uc[1],10);
              ct_mat++;
              ct_uc+=uc;
			  ct_mat2++;
              ct_uc2+=uc;
          }
          d++;
       }
    }
    if ($update){
        with(ft){
            t_mat.value=ct_mat;
            t_uc.value =ct_uc;
			t_mat1.value=ct_mat1;
            t_uc1.value =ct_uc1;
			t_mat2.value=ct_mat2;
            t_uc2.value =ct_uc2;
        }
		return true;
    }
    else return ct_uc;
}
   
function correqInscrito(matAinscribir,correq) {
// matAinscribir: cadena con materias que el estudiante ha inscrito o seleccionado
// correq       : cadena con los correquisitos que deben verificarse. 
// La funcion devuelve un arreglo con dos valores: 
// cOK : verdadero si TODOS los correquisitos estan seleccionados o inscritos
// cFaltantes : Una lista separada por espacios de los correquisitos que le faltan por inscribir
	//alert('materia='+m+' indice='+j);
	cOK = true;
	cFaltantes = '';
	if (correq == ""){
		return Array(cOK,cFaltantes);
	}
	cola = correq.lastIndexOf("_");
	if(cola >=0) {
		cq = correq.substring(0,correq.lastIndexOf("_"));
	}
	else {
		cq = correq;
	}
	aCorreq = cq.split("_");
	for (i=0;i < aCorreq.length; i++ ){
		//alert('MI=['+matAinscribir+'] co=['+aCorreq[i]+']');
		if (matAinscribir.indexOf(aCorreq[i]) < 0) { // No esta el correquisito
			cFaltantes = cFaltantes + aCorreq[i] + ", ";
			cOK = false;
		}
	}
	//elimino la ultima coma que se agrega a cfaltantes:
	cFaltantes = cFaltantes.substring(0,cFaltantes.lastIndexOf(","));
	return Array(cOK,cFaltantes);
}

// Determina si las materias seleccionadas estan dentro de N semestres consecutivos.
// Si no lo estan, devuelve "false" y muestra un mensaje de error.

function materiasEnNsemestres(N, fp) {

	var selOK = ! vrPOZ(); //si es otro vicerrectorado, no chequear
	var masBajo = 10000; //ridiculamente grande para inicializarlo correctamente en la primera revision
	var masAlto = 0;
	var temp = 0;
	if (selOK) {
		return(true);
	}
		//alert("vine a chequear")

    with (fp) {
        for(j=0;j < (CB.length - 1); j++){
            if (CB[j].selectedIndex != '0'){
                cod_uc = CB[j].value.split(" ");                    
                temp = parseInt( "0"+cod_uc[9],10);	//el semestre esta en la posicion 9 ( a partir de cero) en el
													//chorizo del valor de la opcion seleccionada (ver planilla_r.php, linea 285) 
				//que hacer en el caso de electivas??? semestre > 10 en Bqto
				if (temp>10) {
					temp = temp - 2; //esto es arbitrario, no se como ajustarlo!!!
				}
				// actualizar los limites
				if (temp > masAlto) {
					masAlto = temp;
				}
				if (temp < masBajo) {
					masBajo = temp;
				}
            }
			if (cola[j].selectedIndex != '0'){
                cod_uc = cola[j].value.split(" ");                    
                temp = parseInt( "0"+cod_uc[9],10);	//el semestre esta en la posicion 9 ( a partir de cero) en el
													//chorizo del valor de la opcion seleccionada (ver planilla_r.php, linea 285) 
				//que hacer en el caso de electivas??? semestre > 10 en Bqto
				if (temp>10) {
					temp = temp - 2; //esto es arbitrario, no se como ajustarlo!!!
				}
				// actualizar los limites
				if (temp > masAlto) {
					masAlto = temp;
				}
				if (temp < masBajo) {
					masBajo = temp;
				}
            }
        }
	}
	//Revisamos como va la seleccion, si esta repartida en mas de N semestres seguidos
	selOK =  (masAlto - masBajo) < N;
	if (selOK) {
		return (true)
	}
	else { // mensaje de error
		mensaje = "       RESTRICCION DE SEMESTRE! \n";
		mensaje = mensaje + " Disculpa, la asignatura seleccionada viola la \n";
		mensaje = mensaje + " norma de que puedes inscribir materias en un \n";
		mensaje = mensaje + " maximo de " + N + " semestres consecutivos.\n " 
		mensaje = mensaje + " Semestre mas alto: " + masAlto + ".\n Semetre mas bajo: " + masBajo + "."; 
		alert(mensaje)
		return(false)
    }
}


function correquisitoOK(fp) {

    var cOK = true;
    var matAInscr = "";
	var correq = "";
    with (fp) {
        for(j=0;j < (CB.length - 1); j++){
            if (CB[j].selectedIndex != '0'){
                cod_uc = CB[j].value.split(" ");                    
                arrayMat[j] = cod_uc[0]+" ";
            }
            else arrayMat[j] = "";
        }
        matAInscr = arrayMat.join("");
		//alert(matAInscr);
        for(j=0;j < (CB.length - 1); j++){
           if (CB[j].selectedIndex != '0'){
			   //alert(CB[j].value);
			   chequeo = correqInscrito(matAInscr,CBC[j].value);
               if (!chequeo[0]) {
                   correq = correq + "Para poder inscribir " + arrayMat[j];
                   correq = correq + " debes inscribir:\n" + chequeo[1] +"\n"; 
                   cOK = false;
				   break;
               } 
           }

       }
    }
    if (!cOK){
        alert("Conflicto de correquisito:\n" + correq);
		return(false);
    }
	else {
		return(materiasEnNsemestres(3, fp)); //chequea para POZ si las materias no estan en mas de 3 semestres consecutivos
		//return(true)
	}
}


function actualizarSecciones() {

    with (document.pensum) {
        for(j=0;j < (CB.length - 1); j++){             
            arraySecc[j] = CB[j].selectedIndex;
        }
    }
}

function estadoAnterior(lSeccion){

    with (document.pensum) {
        for(j=0;j < (CB.length - 1); j++){
            cod_ucSel = lSeccion.value.split(" "); 
            cod_uc    = CB[j].value.split(" ");            
            if (cod_ucSel[0] == cod_uc[0]){
                        
                lSeccion.options[arraySecc[j]].selected = true;
            }
        }

    }
}


function calcularMaxCargaCCS() {
    
    iMateria = -1; //indica que ninguna materia genera exceso de creditos
    limite   = 21;
    obligatoria  = 0; //0=no obliga, 1=obliga pero puede ver otras, 2=sola
	seleccionada = 0;
	noHayMarcadas = true;
    with (document.pensum) {
        for(j=0;j < (CB.length - 1); j++){
            vcod_uc  = CB[j].value.split(" ");
            vuc      = parseInt(vcod_uc[1],10);
            vrepite  = parseInt('0'+vcod_uc[2],10);
            vcre_cur = parseInt(vcod_uc[3],10);
            if ((vrepite == 2) && (obligatoria <2)) {
					limite = 9;
                    obligatoria = 1;
                    if(noHayMarcadas) {
						iMateria = j;
					}
					if (CB[j].selectedIndex !='0') {
						seleccionada +=1;
						noHayMarcadas = false;
					}
			}
			else if (vrepite > 2) {
                    limite = vuc;
                    obligatoria = 2;
                    if(noHayMarcadas) {
						iMateria = j;
					}
					if (CB[j].selectedIndex !='0') {
						seleccionada +=1;
						noHayMarcadas = false;
					}
			}
		//alert('['+noHayMarcadas+']['+vcod_uc[0]+']['+iMateria+']['+obligatoria+']['+seleccionada+']');
		}
	}
    return(Array(iMateria,limite,obligatoria, seleccionada));
}


function excesoDeCreditosCCS(lSeccion) {
    
	// lSeccion es un campo que contiene la sgte informacion 
	// separada por espacios: 
	//      [0]              [1]          [2]              [3]                        [4] 
	// codigo_asignatura, creditos, veces_que_repite, cred_curs_ultima_repitencia, tipo_lapso 
    
    exceso  = false;
    ecod_uc  = lSeccion.value.split(" ");               
    ucm     = parseInt(ecod_uc[1],10);
    repite  = parseInt('0'+ ecod_uc[2],10);
    cre_cur = parseInt(ecod_uc[3],10);
    total_uc= parseInt(document.totales.t_uc.value,10);
    total_mat= parseInt(document.totales.t_mat.value,10);
    indice = parseFloat(document.f_c.ind_acad.value);

    maxCarga = new Array(3) //contiene maximo de creditos, condicion que aplica 
                            //y puntero a la materia que limita.
	if (actualizarTotales(document.pensum,document.totales, false) == total_uc) {
        ucm = 0;
    }
    if (lSeccion.selectedIndex == '0') {
        ucm = -ucm;
    }
	if (ucm>0) {
		total_mat +=1;
	}
	maxCarga = calcularMaxCargaCCS(); //Array(Imateria, limite, obligatoria);

    iMateria    = maxCarga[0];
	maxCreditos = maxCarga[1];
    obligatoria = maxCarga[2];
    seleccionada = maxCarga[3];
	crAinsc  = total_uc + ucm;

	noPuedeEliminarla = (ucm < 0) && (repite > 1) && (total_mat > 1) &&(seleccionada == 0);
	
	if (iMateria >= 0) {
        matLim = document.pensum.CB[iMateria].value.split(" ");
        }
    else {
         matLim = "";
    }

	if (noPuedeEliminarla) {
		// alert('no puede eliminarla');
		if (repite == 2) {
			maxCreditos = 9;
		}
		else if (repite > 2) {
			maxCreditos = -ucm;
		}
		matLim[0] = ecod_uc[0];
	}

    if ((crAinsc > maxCreditos) || ((matLim !="") &&  (seleccionada == 0) && (crAinsc > 0)) || noPuedeEliminarla) {
        exceso = true;
        mens1 = "    PROBLEMA DE EXCESO DE CRÉDITOS:\nNo puedes ";
        (ucm > 0) ? mAQ = "agregar" : mAQ = "borrar";
        mens1  = mens1 + mAQ + " esta asignatura.\n"
        mcausa = "Tu límite es ";
		if (matLim !="") {
			mcausa = "La condición de repitencia de la asignatura \n";
            mcausa = mcausa + matLim[0] + " te obliga a cursarla ";
			mensLC = "con un limite de " + maxCreditos + " créditos\n";
			mensCS = "";       
		}
		else {
			mensLC = maxCreditos + " créditos\n";
			mensCS = " y estas intentando inscribir " + crAinsc + " créditos.\n";      
		}
    }

    if (exceso) {
        alert(mens1 + mcausa + mensLC + mensCS);
    }
    return exceso;
}


function calcularMaxCargaBQTO() {
    
    iMateria = -1; //indica que ninguna materia genera exceso de creditos
    limite   = 21;
    veces    = '';
    with (document.pensum) {
        for(j=0;j < (CB.length - 1); j++){
            vcod_uc  = CB[j].value.split(" ");
            vuc      = parseInt(vcod_uc[1],10);
            vrepite  = vcod_uc[2];
            cre_cur = parseInt('0'+vcod_uc[3],10);
            vt_lapso = vcod_uc[4];
            if ((vt_lapso !='I') && (CB[j].selectedIndex !='0')) {
                switch(vrepite) {
                    case '':
                            break;
                    case '0' :
                    case 'R' : //repite por primera vez
                            if (veces == '') {
                                limite = cre_cur;
                                iMateria = j;
                            }
                            else if((veces == '0')||(veces == 'R')){
                                if (limite < cre_cur) {
                                    limite = cre_cur;
                                    iMateria = j;
                                }
                            }
                            veces = vrepite;
                            break;
                    case '1' : //repite por 2da vez
                            if ((veces =='') || (veces =='0')) {
                                (cre_cur > 10) ? limite = 10 : limite = cre_cur;
                                iMateria = j;
                                veces = '1';
                            }
                            else if (veces == '1') {
                                if (limite < cre_cur ) {
                                    limite = cre_cur;
                                    iMateria = j;
                                }
                                if (limite > 10) {
                                    limite = 10;
                                }  

                            }
                            break;
                    case '2' : //repite por tercera vez : debe verla solita
                            if (veces != '2') {
                                limite = vuc;
                                veces = '2';
                                iMateria = j
                            }
                } //switch (repite)
            }
        }
    }
    return(Array(iMateria,limite,veces));
}

function excesoDeCreditosBQTO(lSeccion) {
    
	// lSeccion es un campo que contiene la sgte informacion 
	// separada por espacios: 
	//      [0]              [1]          [2]              [3]                        [4] 
	// codigo_asignatura, creditos, veces_que_repite, cred_curs_ultima_repitencia, tipo_lapso 
    
    exceso  = false;
    ecod_uc  = lSeccion.value.split(" ");               
    ucm     = parseInt(ecod_uc[1],10);
    repite  = ecod_uc[2];
    cre_cur = parseInt(ecod_uc[3],10);
    t_lapso = ecod_uc[4];
    total_uc= parseInt(document.totales.t_uc.value);
    total_mat= parseInt(document.totales.t_mat.value,10);
    indice = parseFloat(document.f_c.ind_acad.value);
    maxCarga = new Array(3) //contiene maximo de creditos, condicion que aplica 
                            //y puntero a la materia que limita.
    if(indice >= 6.0) {
        CreditosAdic = 2;
    }
    else {
        CreditosAdic = 0;
    }
    //alert("seccion=" + lSeccion.selectedIndex );
    if (actualizarTotales(document.pensum,document.totales, false) == total_uc) {
        ucm = 0
    }
    if (lSeccion.selectedIndex == '0') {
        ucm = -ucm;
    }
	if (ucm>0) {
		total_mat +=1;
	}

	maxCarga = calcularMaxCargaBQTO(); //Array(Imateria, limite, veces);

    iMateria = maxCarga[0];
    limite   = maxCarga[1];
    veces    = maxCarga[2];
    crAinsc  = total_uc + ucm;

    (veces =='2') ? maxCreditos = limite : maxCreditos = limite + CreditosAdic;
    
	if (iMateria >= 0) {
        matLim = document.pensum.CB[iMateria].value.split(" ");
        }
    else {
         matLim = "";
    }
    if (crAinsc > maxCreditos){
        exceso = true;
        mens1 = "    PROBLEMA DE EXCESO DE CRÉDITOS:\nNo puedes ";
        (ucm > 0) ? mAQ = "agregar" : mAQ = "borrar";
        mens1  = mens1 + mAQ + " esta asignatura.\n"
        mensLC = maxCreditos + " créditos\n";
        mensCS = " y estas intentando inscribir " + crAinsc + " créditos.\n";       
        mcausa = "Tu límite es ";
        if (veces != '') {
            mcausa = "La condición de repitencia de la asignatura \n";
            mcausa = mcausa + matLim[0] + " te limita a ";
        }
    }
    if (exceso) {
        alert(mens1 + mcausa + mensLC + mensCS);
    }
    return exceso;
}


//Desde aqui modificacion realizada en POZ el 23/02/2007

function calcularMaxCargaPOZ() {
    
    iMateria = -1; //indica que ninguna materia genera exceso de creditos
    limite   = 22;
    veces    = '';
	matAinsc= 0;
    with (document.pensum) {
        for(j=0;j < (CB.length - 1); j++){
			//alert(CB[j].value);
			//alert(cola[j].value);
			vcod_uc  = CB[j].value.split(" ");
			vuc      = parseInt(vcod_uc[1],10);
            vrepite  = vcod_uc[2];
            cre_cur  = parseInt('0'+vcod_uc[3],10);
            vt_lapso = vcod_uc[4];
			// Repite la primera vez (18 uc si inscribe la materia)
			if ((vt_lapso !='I') && (CB[j].selectedIndex !='0')) {
			
			if ((vrepite =='1') && ((veces =='') || (veces =='1'))) {
								limite = 18 ;
								//alert (vrepite);
								//alert (limite);
                                iMateria = j;
                                veces = '1';
                            }
                   
			// Para la segunda vez en adelante
              if ((vrepite >'1') && ((veces =='') || (veces =='1'))) {
								//alert (vrepite);
                                matAinsc = 2;
								//alert (limite);
                                iMateria = j;
                                veces = vrepite;
                            }             
         
                            
            }
        }
    }
    return(Array(iMateria,limite,veces,matAinsc));
}

function excesoDeCreditosPOZ(lSeccion) {
    
	// lSeccion es un campo que contiene la sgte informacion 
	// separada por espacios: 
	//      [0]              [1]          [2]              [3]                        [4] 
	// codigo_asignatura, creditos, veces_que_repite, cred_curs_ultima_repitencia, tipo_lapso 
    
    exceso  = false;
    ecod_uc  = lSeccion.value.split(" ");               
    ucm     = parseInt(ecod_uc[1],10);
    repite  = ecod_uc[2];
    cre_cur = parseInt(ecod_uc[3],10);
    t_lapso = ecod_uc[4];
    total_uc= parseInt(document.totales.t_uc.value);
    total_mat= parseInt(document.totales.t_mat.value,10);
    indice = parseFloat(document.f_c.ind_acad.value);
    maxCarga = new Array(4) //contiene maximo de creditos, condicion que aplica 
                            //y puntero a la materia que limita.
    
    //alert("seccion=" + lSeccion.selectedIndex );
    if (actualizarTotales(document.pensum,document.totales, true) == total_uc) {
        ucm = 0
    }
    if (lSeccion.selectedIndex == '0') {
        ucm = -ucm;
    }
	if (ucm>0) {
		total_mat +=1;
	}

	maxCarga = calcularMaxCargaPOZ(); //Array(Imateria, limite, veces);

    iMateria = maxCarga[0];
    limite   = maxCarga[1];
    veces    = maxCarga[2];
	matAinsc = maxCarga[3];
    crAinsc  = total_uc + ucm;

    (veces =='1') ? maxCreditos = limite : maxCreditos = limite ;
	
    
	if (iMateria >= 0) {
		matLim = document.pensum.CB[iMateria].value.split(" ");
        }
    else {
         matLim = "";
		 
    }
    if (matAinsc!=0 ){
    
		if ( total_mat > matAinsc){
        exceso = true;
        mens1 = "    PROBLEMA DE EXCESO DE CRÉDITOS:\nNo puedes ";
        (ucm > 0) ? mAQ = "agregar" : mAQ = "borrar";
        mens1  = mens1 + mAQ + " esta asignatura.\n"
        mensLC = matAinsc + " materias.\n";
        mensCS = " y estas intentando inscribir " + total_mat + " materias.\n";       
        mcausa = "Tu límite es ";
        if (veces != '') {
            mcausa = "La condición de repitencia de la asignatura \n";
            mcausa = mcausa + matLim[0] + " te limita a ";
        }
    }
	}
    else if (crAinsc > maxCreditos){
			exceso = true;
			mens1 = "    PROBLEMA DE EXCESO DE CRÉDITOS:\nNo puedes ";
			(ucm > 0) ? mAQ = "agregar" : mAQ = "borrar";
			mens1  = mens1 + mAQ + " esta asignatura.\n"
			mensLC = maxCreditos + " créditos\n";
			mensCS = " y estas intentando inscribir " + crAinsc + " créditos.\n";       
			mcausa = "Tu límite es ";
			if (veces != '') {
				mcausa = "La condición de repitencia de la asignatura \n";
				mcausa = mcausa + matLim[0] + " te limita a ";
				}
			 }
		
	if (exceso) {
        alert(mens1 + mcausa + mensLC + mensCS);
    }
    return exceso;
	}
 
	
// Fin de la Modificacion

function cambiarColor(lSeccion) {
    cod_uc = lSeccion.value.split(" ");
    for(i=0;i<7;i++){
        identCol = cod_uc[0]+i; //identificador de division
		text_color = '#000000';
        switch (cod_uc[7]) { // de acuerdo a la seleccion y estatus, se establece el color:
            case 'G' :  lcolor='#F0F0F0'; //gris : NO SELECCIONADO
                        break;
            case 'B' :  lcolor='#99CCFF'; //azul : INSCRITO
                        break;
            case 'X' :  lcolor='#FF6666'; //rojo : RETIRO
						text_color ='#FFFFFF';
                        break;
			case 'Y' :  lcolor='#FFFF66'; //amarillo : EN COLA
						text_color ='#000000';
                        break;
        }
        document.getElementById(identCol).style.background = lcolor;
        document.getElementById(identCol).style.color = text_color;
    }

}

//
// Esta es la funcion que se llama cada vez que se selecciona o deselecciona una materia
//
function resaltar(lSeccion) {
	// Nota: dentro de correquisitoOK se revisa la restriccion de semestre para POZ
	if (correquisitoOK(document.pensum)) {
		if (vrLCM()) {
			excesoC = excesoDeCreditosCCS(lSeccion);
		}
		if (vrPOZ()) {
			excesoC = excesoDeCreditosPOZ(lSeccion);
		}
		else {
			excesoC = excesoDeCreditosBQTO(lSeccion);
		}
		if (!excesoC){
             cambiarColor(lSeccion);
		}
		else {
			estadoAnterior(lSeccion);
		}
	}
	else {
		estadoAnterior(lSeccion);
	}
    actualizarSecciones();
    actualizarTotales(document.pensum,document.totales, true);
}


function reiniciarTodo() {
    //return true;
    with (document) {
        ind_acad = f_c.ind_acad.value;
        pensum.reset();
        totales.reset();
        actualizarTotales(pensum,totales, true); 
        actualizarSecciones(); 
        prepdata(pensum,f_c);
        for(j=0;j < (pensum.CB.length - 1); j++) {
            cambiarColor(pensum.CB[j]);
        }
		for(j=0;j < (pensum.CB.length - 1); j++) {
            cambiarColor(pensum.cola[j]);
        }
    }
	//Actualizamos sexo y fecha de nacimiento:
	//por cortesia, femenino primero (cambiamos M=2, F=1
	//aunque en la base de datos es al reves OJO!
	laFechaS = document.f_c.f_nac_e.value+"---"; //por si la fecha esta en blanco
	laFecha  = new Array();
	laFecha = laFechaS.split('-'); //anio,mes,dia
	//	alert('['+laFecha+']'+laFecha[2]+laFecha[1]+laFecha[0]);
	if (laFechaS != ""){
		document.getElementById('diaN').selectedIndex = laFecha[2] - 1; 
		document.getElementById('mesN').selectedIndex = laFecha[1] - 1;
		document.getElementById('anioN').value = laFecha[0].substr(2,4); 
	}
	elSexo  = parseInt('0'+document.f_c.sexo.value,10);
	aSexo   = Array('1','2','1');
	document.getElementById('sexoN').value = aSexo[elSexo];
	document.f_c.c_inicial.value = "1"; //marcamos como validada la fecha
}

function fadePopIE(speed){
	//alert(miTiempo);
	if ((miTiempo > 0) && (miTiempo <= 101)) {
		document.getElementById('floatlayer').style.filter="alpha(opacity="+miTiempo+")";
		miTiempo=miTiempo-speed;
		miTimer = setTimeout("fadePopIE("+speed+")","20");
	}
	else if (miTiempo<=0){
		document.getElementById('floatlayer').style.visibility="hidden";
		clearTimeout(miTimer);
	}
	else clearTimeout(miTimer);
}

function fadePopMOZ(speed){
	//alert(miTiempo);
	if ((miTiempo > 0) && (miTiempo <= 101)) {
		document.getElementById('floatlayer').style.opacity=miTiempo/100;
		miTiempo=miTiempo-speed;
		miTimer = setTimeout("fadePopMOZ("+speed+")","20");
	}
	else if (miTiempo<=0){
		document.getElementById('floatlayer').style.visibility="hidden";
		clearTimeout(miTimer);
	}
	else clearTimeout(miTimer);
}

function desvanecer(speed) {
	miTiempo = 100;
	if (speed < 0) {
		miTiempo = 1;
	}
	//alert(miTiempo);
	if (IE4){
		miTimer = setTimeout("fadePopIE("+speed+")","20");
	}
	else if (NS6){
		miTimer = setTimeout("fadePopMOZ("+speed+")","20");
	}
}

function tieneRetiradas() {
	i = 0;
	tiene = false;
	with (document.pensum) {
		while(i < (CB.length - 1) && !tiene){
			tiene = (CB[i].value.indexOf(" X ") >= 0);
			i++;
		}
	}
	return tiene;
}

function verificar(){
    var dia = parseInt (document.getElementById('diaN').selectedIndex) + 1;
    var mes = parseInt (document.getElementById('mesN').selectedIndex) + 1;
    var anyo = parseInt ('0'+document.getElementById('anioN').value,10) + 1900;
	clearTimeout(miTiempo);
    if (CancelPulsado){
        return false;
    }
	if (FechaValida(dia,mes,anyo)){
		vcontra = hex_md5(document.getElementById('pV').value);
		if(vcontra == contra){
			prepdata(document.pensum,document.f_c);
			if ((document.totales.t_mat.value != "0")|| tieneRetiradas()) {
				document.getElementById('pV').value="";
				desvanecer(20);
				document.f_c.submit();
				return true;
			}
			else {
				alert('Debes seleccionar al menos una materia');
				return false;
			}
		}
		else {
			alert('Clave incorrecta.\n Por favor intente de nuevo');
			document.getElementById('pV').value="";
			document.getElementById('pV').focus();
			return false;
		}
	}
}
 

function cancelar() {
    CancelPulsado = true;
    document.getElementById('pV').value="";
    //hideMe();
	desvanecer(10);
}
function Inscribirme(){

    //if( parseInt(document.totales.t_uc.value)>0){
    prepdata(document.pensum,document.f_c)
    if ((document.totales.t_mat.value != "0" || tieneRetiradas())) {
			CancelPulsado = false;        
			showMe();
    }
    else {
        alert('Debes seleccionar al menos una materia');
    }
}

function anyoBisiesto(anyo)
 {
  var fin = anyo;
  if (fin % 4 != 0)
    return false;
    else
     {
      if (fin % 100 == 0)
       {
        if (fin % 400 == 0)
         {
          return true;
         }
          else
           {
            return false;
           }
       }
        else
         {
          return true;
         }
     }
 }

function FechaValida(dia,mes,anyo)
 {
  var anyohoy = new Date();
  var Mensaje = "";
  var yearhoy = anyohoy.getYear();
  if (yearhoy < 1999)
    yearhoy = yearhoy + 1900;
  if(anyoBisiesto(anyo))
    febrero = 29;
    else
      febrero = 28;
   if ((mes == 2) && (dia > febrero))
    {
     Mensaje += "- Día de nacimiento inválido\r\n";
    }
   if (((mes == 4) || (mes == 6) || (mes == 9) || (mes == 11)) && (dia > 30))
    {
     Mensaje += "- Día de nacimiento inválido\r\n";
    }
   if ((anyo<1935) || (yearhoy - anyo < 15))
    {
     Mensaje += "- Año de nacimiento___ inválido\r\n" + anyo;
    }
   if (Mensaje != "")
   {
	   alert(Mensaje);
	   return false;
   }
   else {
	   return true;
   }
 }
 function mostrar_ayuda(ayudaURL) {
		window.open(ayudaURL,"instruciones","left=0,top=0,width=700,height=250,scrollbars=0,resizable=0,status=0");
 }
