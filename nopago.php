<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Restringido | Droguería</title>
    
    <!-- Bootstrap y Iconos -->
    <link rel="stylesheet" href="Resources/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #a3c5eb; /* El mismo azul claro de tu sistema */
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            font-family: system-ui, -apple-system, sans-serif;
        }
        .card-bloqueo {
            background: #1e293b; /* Fondo oscuro premium */
            border-radius: 24px;
            border: 1px solid #334155;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            padding: 40px;
            text-align: center;
            max-width: 500px;
            width: 90%;
            color: #f8fafc;
        }
        .icon-container {
            background: rgba(239, 68, 68, 0.15); /* Rojo suave */
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        .icon-container i {
            color: #ef4444;
            font-size: 3.5rem;
        }
        .titulo {
            font-weight: 800;
            font-size: 1.8rem;
            margin-bottom: 15px;
            color: #f8fafc;
        }
        .mensaje {
            color: #94a3b8;
            font-size: 1.05rem;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .btn-soporte {
            background: #10b981; /* Verde esmeralda */
            color: white;
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 700;
            font-size: 1.05rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }
        .btn-soporte:hover {
            background: #059669;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
        }
    </style>
</head>
<body>

    <div class="card-bloqueo">
        <div class="icon-container">
            <i class="bi bi-shield-lock-fill"></i>
        </div>
        
        <h1 class="titulo">Sistema Suspendido</h1>
        
        <p class="mensaje">
            El acceso a este sistema ha sido <strong>bloqueado temporalmente</strong>.<br><br>
            No se ha detectado el pago correspondiente al servicio de hosting y mantenimiento de la plataforma para el periodo en curso.
        </p>

        <div class="alert" style="background: #0f172a; border: 1px solid #334155; color: #cbd5e1; border-radius: 12px; font-size: 0.9rem;">
            <i class="bi bi-info-circle me-2" style="color: #3b82f6;"></i>
            Si ya realizaste el pago, por favor envía el comprobante a soporte para reactivar tu cuenta de inmediato.
        </div>

        <a href="mailto:evensoftla21@gmail.com?subject=Pago%20Hosting%20-%20Reactivaci%C3%B3n" class="btn-soporte mt-3">
            <i class="bi bi-envelope-paper-fill"></i> Contactar a Soporte
        </a>
    </div>

</body>
</html>