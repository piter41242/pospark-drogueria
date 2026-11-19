window.onload = alert1();
function alert1(){
	const valores = window.location.search;

	const urlParams = new URLSearchParams(valores);
	//alert(urlParams);
	var result = urlParams.get('ps');

	//alert(result);
	if (result==="error") {
		
		alert("Ya existe una categoria con ese nombre");

	}else{

	}
}


function crear(){
	swal.fire({
		html:'<form method="POST" action="?controlador=categoria&accion=crear&dive=l4"><div class="row">'+
		'<div class="col-sm-1"></div>'+
		'<div class="col-sm-10">'+
		'<label>Crear categoria</label>'+
		'</div>'+
		'<div class="col-sm-1"></div></div><br>'+
		'<div class="row">'+
		'<div class="col-sm-1"></div>'+
		'<div class="col-sm-10">'+
		'<font size="4" face="arial black" width="100" align="left">Nombre</font>'+
		'</div>'+
		'<div class="col-sm-1"></div></div><br>'+
		'<div class="row">'+
		'<div class="col-sm-1"></div>'+
		'<div class="col-sm-10">'+
		'<input  type="text" name="nombre" class="form-control" placeholder="Ingrese el nombre de la categoria" required>'+
		'</div>'+
		'<div class="col-sm-1"></div></div><hr>'+
		'<div class="row">'+
		'<div class="col-sm-1"></div>'+
		'<div class="col-sm-10">'+
		'<input type="submit" value="Crear" class="btn btn-primary" >'+
		'</div>'+
		'<div class="col-sm-1"></div></div><hr>'+
		'</form>',
		showConfirmButton:false
	})
}
function modificar(id){
	$.ajax({
		url:"../../Controlador/controladorAjax/controladorAjax.php",
		type:'POST',
		async:true,
		data:{buscarcategoria:'',id:id},
		beforeSend:function(){

		},
		success:function(response){
			if (response=="NO") {
				console.log("ERROR DE DATOS");
			}else{
				const info=$.parseJSON(response);
				swal.fire({
					html:'<form method="POST" action="?controlador=categoria&accion=modificar"><div class="row">'+
					'<div class="col-sm-1"></div>'+
					'<div class="col-sm-10">'+
					'<label>Editar categoria</label>'+
					'</div>'+
					'<div class="col-sm-1"></div></div><br>'+
					'<div class="row">'+
					'<div class="col-sm-1"></div>'+
					'<div class="col-sm-10">'+
					'<font size="4" face="arial black" width="100" align="left">Nombre</font>'+
					'</div>'+
					'<div class="col-sm-1"></div></div><br>'+
					'<div class="row">'+
					'<div class="col-sm-1"></div>'+
					'<div class="col-sm-10">'+
					'<input type="hidden" name="id" value="'+info.idCategoria+'"><input value="'+info.nombre+'"  type="text" name="nombre" class="form-control" placeholder="Ingrese el nombre de la categoria" required>'+
					'</div>'+
					'<div class="col-sm-1"></div></div><hr>'+
					'<div class="row">'+
					'<div class="col-sm-1"></div>'+
					'<div class="col-sm-10">'+
					'<input type="submit" value="Modificar" class="btn btn-primary" >'+
					'</div>'+
					'<div class="col-sm-1"></div></div><hr>'+
					'</form>',
					showConfirmButton:false
				})

			}
		},
		error:function(error){
			console.log(error);
		}
	})
}
function eliminar(id){
	$.ajax({
		url:"../../Controlador/controladorAjax/controladorAjax.php",
		type:'POST',
		async:true,
		data:{buscarcategoria:'',id:id},
		beforeSend:function(){

		},
		success:function(response){
			if (response=="NO") {
				console.log("ERROR DE DATOS");
			}else{
				const info=$.parseJSON(response);
				swal.fire({
					html:'<form method="POST" action="?controlador=categoria&accion=eliminar&dive=l4"><div class="row">'+
					'<div class="col-sm-1"></div>'+
					'<div class="col-sm-10">'+
					'<label>Eliminar categoria</label>'+
					'</div>'+
					'<div class="col-sm-1"></div></div><br>'+
					'<div class="row">'+
					'<div class="col-sm-1"></div>'+
					'<div class="col-sm-10">'+
					'<input type="hidden" name="id" value="'+info.idCategoria+'"><font>Seguro que quiere eliminar a '+info.nombre+'</font>'+
					'</div>'+
					'<div class="col-sm-1"></div></div><hr>'+
					'<div class="row">'+
					'<div class="col-sm-1"></div>'+
					'<div class="col-sm-10">'+
					'<input type="submit" value="Eliminar" class="btn btn-danger" >'+
					'</div>'+
					'<div class="col-sm-1"></div></div><hr>'+
					'</form>',
					showConfirmButton:false
				})

			}
		},
		error:function(error){
			console.log(error);
		}
	})
}
function filtrar(){
	let id=document.getElementById("filtro").value;
	$.ajax({
		url: '../../Controlador/controladorAjax/controladorAjax.php',
		type: "POST",
		dataType: 'html',
		async:true,
		data: {filtrocategoria:'',filtro:id},	
	}).done(function(respuesta){
		$("#columnaId").html(respuesta);
	}).fail(function(){
		console.log("error");
	});
}