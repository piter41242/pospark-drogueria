function validacion(){
		var dominio = "administrador.html";
		var form = document.formLogin;
		var usuario = form.usuario.value;
		var pass = form.passt.value;
	if (usuario == "admin") {
		if (pass == "1234") {
			window.location.href=dominio;
		}else{
			alert("[ERROR] Usuario o contraseña incorrectos");
		}								
	}else{
		if (usuario == "vendedor" && pass == "1234"){
				dominio = "vendedor.html";
				window.location.href=dominio;
		
		}else{
			alert("[ERROR] Usuario o contraseña incorrectos");
		}
					
	}
}