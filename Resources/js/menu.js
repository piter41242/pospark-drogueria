// menu.js
$(document).ready(main);
var contador = 1;

function main() {
    $('.menu_bar').click(function(e) {
        e.preventDefault();

        if (contador == 1) {
            // Abrir menú
            $('nav').animate({
                left: '0'
            });
            // Ocultar contenido
            document.getElementById('idForm').style.display = 'none';
            // Añadir clase para transición
            document.getElementById('idForm').classList.add('menu-open');
            contador = 0;
        } else {
            // Cerrar menú
            $('nav').animate({
                left: '-100%'
            });
            // Mostrar contenido
            document.getElementById('idForm').style.display = 'block';
            document.getElementById('idForm').classList.remove('menu-open');
            contador = 1;
        }
    });

    // Cerrar menú al hacer clic en un enlace (móvil)
    $('nav ul li a').click(function() {
        if ($(window).width() <= 800) {
            $('nav').animate({
                left: '-100%'
            });
            document.getElementById('idForm').style.display = 'block';
            document.getElementById('idForm').classList.remove('menu-open');
            contador = 1;
        }
    });

    // Ajustar menú al redimensionar
    $(window).resize(function() {
        if ($(window).width() > 800) {
            $('nav').css('left', '0');
            document.getElementById('idForm').style.display = 'block';
            document.getElementById('idForm').classList.remove('menu-open');
            contador = 1;
        } else {
            if (contador == 1) {
                $('nav').css('left', '-100%');
            }
        }
    });

    // Inicializar: en móvil ocultar menú
    if ($(window).width() <= 800) {
        $('nav').css('left', '-100%');
    }
}