
function personal() {
    var li = document.getElementById('divPersonal');
    if (li.style.display === "none") {
        document.getElementById('divPersonal').style.display = 'block';
    } else {
        document.getElementById('divPersonal').style.display = 'none';
    }

}
function seguridad() {
    var li = document.getElementById('divSeguridad');
    if (li.style.display === "none") {
        document.getElementById('divSeguridad').style.display = 'block';
    } else {
        document.getElementById('divSeguridad').style.display = 'none';
    }

}
function mano(a) {
    if (navigator.appName === "Netscape") {
        a.style.cursor = 'pointer';
    } else {
        a.style.cursor = 'hand';
    }
}
function validar() {
    var nombre = document.getElementById('txtnombre').value;
    if (nombre === "") {
        swal('nombre es requerido');
    }
}

