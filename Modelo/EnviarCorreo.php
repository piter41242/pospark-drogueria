<?php
// Ajusta la ruta según donde hayas instalado Composer
require_once __DIR__ . '/../vendor/autoload.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EnviarCorreo {
    
    public static function notificarUsuario($correoDestino, $nombreUsuario, $accion) {
        // Si el usuario no tiene correo, no hacemos nada
        if (empty($correoDestino)) return false;

        $mail = new PHPMailer(true);

        try {
            // Configuración del servidor SMTP (Gmail)
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            // CAMBIA ESTO por tu correo real de Gmail
            $mail->Username   = 'piter41242@gmail.com'; 
            // CAMBIA ESTO por la Contraseña de Aplicación de 16 caracteres
            $mail->Password   = 'prfu lxcz ywdl vanx'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            // Remitente (El correo que aparecerá como "De")
            $mail->setFrom('piter41242@gmail.com', 'Droguería Central');

            // Destinatario
            $mail->addAddress($correoDestino, $nombreUsuario);

            // Contenido
            $mail->isHTML(true);
            
            if ($accion === 'crear') {
                $mail->Subject = 'Bienvenido al Sistema - Tu cuenta ha sido creada';
                $mail->Body    = "Hola <b>{$nombreUsuario}</b>,<br><br>Tu usuario ha sido registrado exitosamente en el sistema de la Droguería. Ya puedes iniciar sesión con las credenciales que te proporcionó el administrador.";
            } else {
                $mail->Subject = 'Actualización de datos - Sistema Droguería';
                $mail->Body    = "Hola <b>{$nombreUsuario}</b>,<br><br>Te informamos que tus datos de usuario han sido actualizados en el sistema por un administrador.";
            }

            $mail->send();
            return true;
        } catch (Exception $e) {
            // Para depurar, podrías guardar $mail->ErrorInfo en un log
            return false;
        }
    }
}
?>