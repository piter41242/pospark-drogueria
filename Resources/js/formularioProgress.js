//Solo texto y espacios
function soloLetras(e) {
    var key = e.keyCode || e.which,
    tecla = String.fromCharCode(key).toLowerCase(),
    letras = " áéíóúabcdefghijklmnñopqrstuvwxyz",
    especiales = [8, 37, 39, 46],
    tecla_especial = false;

    for (var i in especiales) {
      if (key == especiales[i]) {
        tecla_especial = true;
        break;
    }
}

if (letras.indexOf(tecla) == -1 && !tecla_especial) {
  return false;
}
}
//Solo Numeros
function soloNumeros(e){
    var key = e.charCode;
    //console.log(key);
    return key >= 48 && key <= 57;
}

$(document).ready(function(){

var current_fs, next_fs, previous_fs; //fieldsets
var opacity;
var current = 1;
var steps = $("fieldset").length;
setProgressBar(current);

//Solo números
$("input.dni").bind('keypress', function(event) {
  var regex = new RegExp("^[0-9]+$");
  var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
  if (!regex.test(key)) {
    event.preventDefault();
    return false;
}
});

$(".next").click(function(){
    var correo = document.getElementById("correo").value;
    var clave = document.getElementById("clave").value;
    var clave2 = document.getElementById("clave2").value;
    
    
    if (correo != "") {

        var emailField = document.getElementById('email');


        var validEmail =  /^\w+([.-_+]?\w+)*@\w+([.-]?\w+)*(\.\w{2,10})+$/;


        if( validEmail.test(emailField.value) ){
            if (clave!="") {
                if (clave==clave2) {

                    current_fs = $(this).parent();
                    next_fs = $(this).parent().next();


                    $("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");


                    next_fs.show(); 

                    current_fs.animate({opacity: 0}, {
                        step: function(now) {

                            opacity = 1 - now;

                            current_fs.css({
                                'display': 'none',
                                'position': 'relative'
                            });
                            next_fs.css({'opacity': opacity});
                        }, 
                        duration: 500
                    });
                    setProgressBar(++current);
                    return true;
                }else{
                    document.getElementById("clave2").style.border = '1px solid red';
                    document.getElementById('alertClave2').style.display = 'block';
                    document.getElementById('alertCorreo').style.display = 'none';
                    document.getElementById('alertCorreoValido').style.display = 'none';
                    document.getElementById('alertClave').style.display = 'none';
                    document.getElementById("email").style.border = 'none';
                    document.getElementById("clave").style.border = 'none';
                }
            }else{
                document.getElementById("clave").style.border = '1px solid red';
                document.getElementById("email").style.border = 'none';
                document.getElementById('alertClave').style.display = 'block';
                document.getElementById('alertCorreo').style.display = 'none';
                document.getElementById('alertCorreoValido').style.display = 'none';
            }


        }else{
            document.getElementById("email").style.border = '1px solid red';
            document.getElementById('alertCorreo').style.display = 'none';
            document.getElementById('alertCorreoValido').style.display = 'block';

            return false;
        }
    }else {
        document.getElementById("email").style.border = '1px solid red';
        document.getElementById('alertCorreo').style.display = 'block';
        document.getElementById('alertCorreoValido').style.display = 'none';
    }


});
$(".nextPersonal").click(function(){
    var nombres = document.getElementById("nombres").value;
    var apellidos = document.getElementById("apellidos").value;
    var numero = document.getElementById("numero").value;
    var telefono = document.getElementById("telefono").value;
    
    
    if (nombres != "") {

        if( apellidos != ""){
            if (numero != "") {
                if (telefono!="") {
                    current_fs = $(this).parent();
                    next_fs = $(this).parent().next();


                    $("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");


                    next_fs.show(); 

                    current_fs.animate({opacity: 0}, {
                        step: function(now) {

                            opacity = 1 - now;

                            current_fs.css({
                                'display': 'none',
                                'position': 'relative'
                            });
                            next_fs.css({'opacity': opacity});
                        }, 
                        duration: 500
                    });
                    setProgressBar(++current);
                    return true;
                }else{
                    document.getElementById("telefono").style.border = '1px solid red';
                    document.getElementById('alertTelefono').style.display = 'block';
                    document.getElementById("numero").style.border = 'none';
                    document.getElementById('alertNumero').style.display = 'none';
                    document.getElementById("apellidos").style.border = 'none';
                    document.getElementById('alertApellidos').style.display = 'none';
                    document.getElementById('alertNombres').style.display = 'none';
                    document.getElementById("nombres").style.border = 'none';
                    return false; 
                }
                
            }
            else{
                document.getElementById("numero").style.border = '1px solid red';
                document.getElementById('alertNumero').style.display = 'block';
                document.getElementById("apellidos").style.border = 'none';
                document.getElementById('alertApellidos').style.display = 'none';
                document.getElementById('alertNombres').style.display = 'none';
                document.getElementById("nombres").style.border = 'none';
                return false; 
            }
        }else{
            document.getElementById("apellidos").style.border = '1px solid red';
            document.getElementById('alertApellidos').style.display = 'block';
            document.getElementById('alertNombres').style.display = 'none';
            document.getElementById("nombres").style.border = 'none';
            return false;
        }
    }else {
        document.getElementById("nombres").style.border = '1px solid red';
        document.getElementById('alertNombres').style.display = 'block';
        
    }
});
$(".nextFinal").click(function(){
    var nombreRP = document.getElementById("nombreRP").value;
    var apellidoRP = document.getElementById("apellidoRP").value;
    var telefonoRP = document.getElementById("telefonoRP").value;
    var direccionRP = document.getElementById("direccionRP").value;
    var nombreRF = document.getElementById("nombreRF").value;
    var apellidoRF = document.getElementById("apellidoRF").value;
    var telefonoRF = document.getElementById("telefonoRF").value;
    var direccionRF = document.getElementById("direccionRF").value;
    var di = document.getElementById("DI").value;    
    var rp = document.getElementById("RP").value;
    var rf = document.getElementById("RF").value;
    var rl = document.getElementById("RL").value;
    
    if (nombreRP != "") {
        if (apellidoRP!="") {
            if (direccionRP!="") {
                if (telefonoRP!="") {
                    if (nombreRF!="") {
                        if (apellidoRF!="") {
                            if (direccionRF!="") {
                                if (telefonoRF!="") {
                                    if (di!="") {
                                        if (rl!="") {
                                            if (rp!="") {
                                                if (rf!="") {
                                                 

                                                 
                                                           current_fs = $(this).parent();
                                                            next_fs = $(this).parent().next();


                                                            $("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");


                                                            next_fs.show(); 

                                                            current_fs.animate({opacity: 0}, {
                                                                step: function(now) {

                                                                    opacity = 1 - now;

                                                                    current_fs.css({
                                                                        'display': 'none',
                                                                        'position': 'relative'
                                                                    });
                                                                    next_fs.css({'opacity': opacity});
                                                                }, 
                                                                duration: 500
                                                            });
                                                            setProgressBar(++current);
                                                            return true;
                                                        
                                                    }  
                                

                                             }else{
                                                window.scroll(0,1280);
                                                document.getElementById("RF").style.border = '1px solid red';
                                                document.getElementById('alertRF').style.display = 'block';
                                                document.getElementById("RP").style.border = 'none';
                                                document.getElementById('alertRP').style.display = 'none';
                                                document.getElementById("RL").style.border = 'none';
                                                document.getElementById('alertRL').style.display = 'none';
                                                document.getElementById("DI").style.border = 'none';
                                                document.getElementById('alertDI').style.display = 'none';
                                                document.getElementById("telefonoRF").style.border = 'none';
                                                document.getElementById('alertTelefonoRF').style.display = 'none';
                                                document.getElementById("direccionRF").style.border = 'none';
                                                document.getElementById('alertDireccionRF').style.display = 'none';
                                                document.getElementById("apellidoRF").style.border = 'none';
                                                document.getElementById('alertApellidoRF').style.display = 'none';
                                                document.getElementById("nombreRF").style.border = 'none';
                                                document.getElementById('alertNombreRF').style.display = 'none';
                                                document.getElementById("telefonoRP").style.border = 'none';
                                                document.getElementById('alertTelefonoRP').style.display = 'none';
                                                document.getElementById("direccionRP").style.border = 'none';
                                                document.getElementById('alertDireccionRP').style.display = 'none';
                                                document.getElementById("apellidoRP").style.border = 'none';
                                                document.getElementById('alertApellidoRP').style.display = 'none';
                                                document.getElementById("nombreRP").style.border = 'none';
                                                document.getElementById('alertNombreRP').style.display = 'none';
                                            }

                                        }else{
                                            window.scroll(0,1180);
                                            document.getElementById("RP").style.border = '1px solid red';
                                            document.getElementById('alertRP').style.display = 'block';
                                            document.getElementById("RL").style.border = 'none';
                                            document.getElementById('alertRL').style.display = 'none';
                                            document.getElementById("DI").style.border = 'none';
                                            document.getElementById('alertDI').style.display = 'none';
                                            document.getElementById("telefonoRF").style.border = 'none';
                                            document.getElementById('alertTelefonoRF').style.display = 'none';
                                            document.getElementById("direccionRF").style.border = 'none';
                                            document.getElementById('alertDireccionRF').style.display = 'none';
                                            document.getElementById("apellidoRF").style.border = 'none';
                                            document.getElementById('alertApellidoRF').style.display = 'none';
                                            document.getElementById("nombreRF").style.border = 'none';
                                            document.getElementById('alertNombreRF').style.display = 'none';
                                            document.getElementById("telefonoRP").style.border = 'none';
                                            document.getElementById('alertTelefonoRP').style.display = 'none';
                                            document.getElementById("direccionRP").style.border = 'none';
                                            document.getElementById('alertDireccionRP').style.display = 'none';
                                            document.getElementById("apellidoRP").style.border = 'none';
                                            document.getElementById('alertApellidoRP').style.display = 'none';
                                            document.getElementById("nombreRP").style.border = 'none';
                                            document.getElementById('alertNombreRP').style.display = 'none';
                                        }

                                    }else{
                                        window.scroll(0,1180);
                                        document.getElementById("RL").style.border = '1px solid red';
                                        document.getElementById('alertRL').style.display = 'block';
                                        document.getElementById("DI").style.border = 'none';
                                        document.getElementById('alertDI').style.display = 'none';
                                        document.getElementById("telefonoRF").style.border = 'none';
                                        document.getElementById('alertTelefonoRF').style.display = 'none';
                                        document.getElementById("direccionRF").style.border = 'none';
                                        document.getElementById('alertDireccionRF').style.display = 'none';
                                        document.getElementById("apellidoRF").style.border = 'none';
                                        document.getElementById('alertApellidoRF').style.display = 'none';
                                        document.getElementById("nombreRF").style.border = 'none';
                                        document.getElementById('alertNombreRF').style.display = 'none';
                                        document.getElementById("telefonoRP").style.border = 'none';
                                        document.getElementById('alertTelefonoRP').style.display = 'none';
                                        document.getElementById("direccionRP").style.border = 'none';
                                        document.getElementById('alertDireccionRP').style.display = 'none';
                                        document.getElementById("apellidoRP").style.border = 'none';
                                        document.getElementById('alertApellidoRP').style.display = 'none';
                                        document.getElementById("nombreRP").style.border = 'none';
                                        document.getElementById('alertNombreRP').style.display = 'none';
                                    }

                                }
                                else{
                                    window.scroll(0,1080);
                                    document.getElementById("DI").style.border = '1px solid red';
                                    document.getElementById('alertDI').style.display = 'block';
                                    document.getElementById("telefonoRF").style.border = 'none';
                                    document.getElementById('alertTelefonoRF').style.display = 'none';
                                    document.getElementById("direccionRF").style.border = 'none';
                                    document.getElementById('alertDireccionRF').style.display = 'none';
                                    document.getElementById("apellidoRF").style.border = 'none';
                                    document.getElementById('alertApellidoRF').style.display = 'none';
                                    document.getElementById("nombreRF").style.border = 'none';
                                    document.getElementById('alertNombreRF').style.display = 'none';
                                    document.getElementById("telefonoRP").style.border = 'none';
                                    document.getElementById('alertTelefonoRP').style.display = 'none';
                                    document.getElementById("direccionRP").style.border = 'none';
                                    document.getElementById('alertDireccionRP').style.display = 'none';
                                    document.getElementById("apellidoRP").style.border = 'none';
                                    document.getElementById('alertApellidoRP').style.display = 'none';
                                    document.getElementById("nombreRP").style.border = 'none';
                                    document.getElementById('alertNombreRP').style.display = 'none';
                                }

                            }else{
                                window.scroll(0,580);
                                document.getElementById("telefonoRF").style.border = '1px solid red';
                                document.getElementById('alertTelefonoRF').style.display = 'block';
                                document.getElementById("direccionRF").style.border = 'none';
                                document.getElementById('alertDireccionRF').style.display = 'none';
                                document.getElementById("apellidoRF").style.border = 'none';
                                document.getElementById('alertApellidoRF').style.display = 'none';
                                document.getElementById("nombreRF").style.border = 'none';
                                document.getElementById('alertNombreRF').style.display = 'none';
                                document.getElementById("telefonoRP").style.border = 'none';
                                document.getElementById('alertTelefonoRP').style.display = 'none';
                                document.getElementById("direccionRP").style.border = 'none';
                                document.getElementById('alertDireccionRP').style.display = 'none';
                                document.getElementById("apellidoRP").style.border = 'none';
                                document.getElementById('alertApellidoRP').style.display = 'none';
                                document.getElementById("nombreRP").style.border = 'none';
                                document.getElementById('alertNombreRP').style.display = 'none';
                            }

                        }else{
                            window.scroll(0,580);
                            document.getElementById("direccionRF").style.border = '1px solid red';
                            document.getElementById('alertDireccionRF').style.display = 'block';
                            document.getElementById("apellidoRF").style.border = 'none';
                            document.getElementById('alertApellidoRF').style.display = 'none';
                            document.getElementById("nombreRF").style.border = 'none';
                            document.getElementById('alertNombreRF').style.display = 'none';
                            document.getElementById("telefonoRP").style.border = 'none';
                            document.getElementById('alertTelefonoRP').style.display = 'none';
                            document.getElementById("direccionRP").style.border = 'none';
                            document.getElementById('alertDireccionRP').style.display = 'none';
                            document.getElementById("apellidoRP").style.border = 'none';
                            document.getElementById('alertApellidoRP').style.display = 'none';
                            document.getElementById("nombreRP").style.border = 'none';
                            document.getElementById('alertNombreRP').style.display = 'none';
                        }

                    }else{
                       window.scroll(0,580);
                       document.getElementById("apellidoRF").style.border = '1px solid red';
                       document.getElementById('alertApellidoRF').style.display = 'block';
                       document.getElementById("nombreRF").style.border = 'none';
                       document.getElementById('alertNombreRF').style.display = 'none';
                       document.getElementById("telefonoRP").style.border = 'none';
                       document.getElementById('alertTelefonoRP').style.display = 'none';
                       document.getElementById("direccionRP").style.border = 'none';
                       document.getElementById('alertDireccionRP').style.display = 'none';
                       document.getElementById("apellidoRP").style.border = 'none';
                       document.getElementById('alertApellidoRP').style.display = 'none';
                       document.getElementById("nombreRP").style.border = 'none';
                       document.getElementById('alertNombreRP').style.display = 'none'; 
                   }

               }else{
                window.scroll(0,500);
                document.getElementById("nombreRF").style.border = '1px solid red';
                document.getElementById('alertNombreRF').style.display = 'block';
                document.getElementById("telefonoRP").style.border = 'none';
                document.getElementById('alertTelefonoRP').style.display = 'none';
                document.getElementById("direccionRP").style.border = 'none';
                document.getElementById('alertDireccionRP').style.display = 'none';
                document.getElementById("apellidoRP").style.border = 'none';
                document.getElementById('alertApellidoRP').style.display = 'none';
                document.getElementById("nombreRP").style.border = 'none';
                document.getElementById('alertNombreRP').style.display = 'none';
            }

        }else{
            window.scroll(0,150);
            document.getElementById("telefonoRP").style.border = '1px solid red';
            document.getElementById('alertTelefonoRP').style.display = 'block';
            document.getElementById("direccionRP").style.border = 'none';
            document.getElementById('alertDireccionRP').style.display = 'none';
            document.getElementById("apellidoRP").style.border = 'none';
            document.getElementById('alertApellidoRP').style.display = 'none';
            document.getElementById("nombreRP").style.border = 'none';
            document.getElementById('alertNombreRP').style.display = 'none';
        }


    }else{
        window.scroll(0,120);
        document.getElementById("direccionRP").style.border = '1px solid red';
        document.getElementById('alertDireccionRP').style.display = 'block';
        document.getElementById("apellidoRP").style.border = 'none';
        document.getElementById('alertApellidoRP').style.display = 'none';
        document.getElementById("nombreRP").style.border = 'none';
        document.getElementById('alertNombreRP').style.display = 'none';
    }
}else{
    window.scroll(0,70);
    document.getElementById("apellidoRP").style.border = '1px solid red';
    document.getElementById('alertApellidoRP').style.display = 'block';
    document.getElementById("nombreRP").style.border = 'none';
    document.getElementById('alertNombreRP').style.display = 'none';
}

}else {
    window.scroll(0,40);
    document.getElementById("nombreRP").style.border = '1px solid red';
    document.getElementById('alertNombreRP').style.display = 'block';

}


});
$(".previous").click(function(){


    current_fs = $(this).parent();
    previous_fs = $(this).parent().prev();

        //Remove class active
        $("#progressbar li").eq($("fieldset").index(current_fs)).removeClass("active");
        
        //show the previous fieldset
        previous_fs.show();

        //hide the current fieldset with style
        current_fs.animate({opacity: 0}, {
            step: function(now) {
                // for making fielset appear animation
                opacity = 1 - now;

                current_fs.css({
                    'display': 'none',
                    'position': 'relative'
                });
                previous_fs.css({'opacity': opacity});
            }, 
            duration: 500
        });
        setProgressBar(--current);
    });
function setProgressBar(curStep){
    var percent = parseFloat(100 / steps) * curStep;
    percent = percent.toFixed();
    $(".progress-bar")
    .css("width",percent+"%")   
}

$(".submit").click(function(){
    return false;
});

});
