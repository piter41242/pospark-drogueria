<?php
class Usuario
{
  
    private $idUsuario;
    private $nombre;
    private $usuario;
    private $clave;
    private $telefono;
    private $email;
    private $permiso;
    private $idSede;
    private $estado;

    public function __construct($idUsuario = null, $nombre = null, $usuario = null, $clave = null, $telefono = null, $email = null, $permiso = null, $idSede = null, $estado = null)
    {
        $this->idUsuario = $idUsuario;
        $this->nombre    = $nombre;
        $this->usuario   = $usuario;
        $this->clave     = $clave;
        $this->telefono  = $telefono;
        $this->email     = $email;
        $this->permiso   = $permiso;
        $this->idSede    = $idSede;
        $this->estado    = $estado;
    }


    public function buscarUsuario($usuario){
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->prepare("
            SELECT u.*, s.nombre AS nombreSede
            FROM usuario u
            INNER JOIN sede s ON s.idSede = u.idSede
            WHERE u.usuario = ?
        ");
        $sql->execute(array($usuario));
        return $sql->fetch();
    }

    public function consultarTodosInner($idSede){
        $lista = [];
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->prepare("
            SELECT u.idUsuario, u.nombre, u.usuario, u.clave, u.telefono, u.email,
                   p.idPermiso, p.nombre AS nombrePermiso,
                   s.idSede, s.nombre AS nombreSede,
                   e.nombre AS nombreEstado, u.estado
            FROM usuario u
            INNER JOIN permiso p ON p.idPermiso = u.permiso
            INNER JOIN sede   s ON s.idSede    = u.idSede
            INNER JOIN estado e ON e.idEstado  = u.estado
            WHERE u.idSede = ?
        ");
        $sql->execute(array($idSede));
        foreach ($sql->fetchAll() as $row) $lista[] = $row;
        return $lista;
    }

    public function consultarTodosGlobal(){
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->query("
            SELECT u.idUsuario, u.nombre, u.usuario, u.clave, u.telefono, u.email,
                   p.idPermiso, p.nombre AS nombrePermiso,
                   s.idSede, s.nombre AS nombreSede,
                   e.nombre AS nombreEstado, u.estado
            FROM usuario u 
            INNER JOIN permiso p ON p.idPermiso = u.permiso 
            INNER JOIN sede s ON s.idSede = u.idSede 
            INNER JOIN estado e ON e.idEstado = u.estado 
            ORDER BY u.idUsuario ASC
        ");
        return $sql->fetchAll();
    }

    public function crear($nombre, $usuario, $clave, $telefono, $email, $permiso, $idSede, $estado){
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->prepare("
            INSERT INTO usuario (nombre, usuario, clave, telefono, email, permiso, idSede, estado)
            VALUES (?,?,?,?,?,?,?,?)
        ");
        return $sql->execute(array($nombre, $usuario, $clave, $telefono, $email, $permiso, $idSede, $estado));
    }

    public function modificar($idUsuario, $nombre, $usuario, $clave, $telefono, $email, $permiso, $idSede, $estado){
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->prepare("
            UPDATE usuario
            SET nombre=?, usuario=?, clave=?, telefono=?, email=?, permiso=?, idSede=?, estado=?
            WHERE idUsuario=?
        ");
        return $sql->execute(array($nombre, $usuario, $clave, $telefono, $email, $permiso, $idSede, $estado, $idUsuario));
    }

    public function borrar($id){
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->prepare("DELETE FROM usuario WHERE idUsuario=?");
        return $sql->execute(array($id));
    }

    public function buscarIdInner($id){
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->prepare("
            SELECT u.idUsuario, u.nombre, u.usuario, u.clave, u.telefono, u.email,
                   p.idPermiso, p.nombre AS nombrePermiso,
                   s.idSede, s.nombre AS nombreSede,
                   e.nombre AS nombreEstado, u.estado
            FROM usuario u
            INNER JOIN permiso p ON p.idPermiso = u.permiso
            INNER JOIN sede   s ON s.idSede    = u.idSede
            INNER JOIN estado e ON e.idEstado  = u.estado
            WHERE u.idUsuario = ?
        ");
        $sql->execute(array($id));
        return $sql->fetch();
    }

    public function contarUsuario($idSede){
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->prepare("SELECT COUNT(idUsuario) AS total FROM usuario WHERE idSede=?");
        $sql->execute(array($idSede));
        $r = $sql->fetch();
        return $r['total'];
    }

    public function obtenerResumenDashboard($idSede){
        $conexionBD = BD::crearInstancia();

        $sql = $conexionBD->prepare("SELECT * FROM v_stock WHERE idSede=? AND alerta=1 ORDER BY stock_disponible ASC LIMIT 10");
        $sql->execute(array($idSede));
        $stockBajo = $sql->fetchAll();

        $sql = $conexionBD->prepare("
            SELECT l.*, p.nombre AS producto, s.nombre AS sede
            FROM lote l
            JOIN producto p ON p.idProducto=l.idProducto
            JOIN sede s ON s.idSede=l.idSede
            WHERE l.idSede=? AND l.estado=1 AND l.cantidad_actual>0
              AND l.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 90 DAY)
            ORDER BY l.fecha_vencimiento ASC LIMIT 10
        ");
        $sql->execute(array($idSede));
        $porVencer = $sql->fetchAll();

        $sql = $conexionBD->prepare("
            SELECT c.*, cl.nombre AS cliente
            FROM carrito c
            JOIN cliente cl ON cl.idCliente=c.idCliente
            WHERE c.idSede=? AND c.estado IN ('ABIERTO','EN_RUTA')
            ORDER BY c.fecha_apertura DESC LIMIT 10
        ");
        $sql->execute(array($idSede));
        $carritos = $sql->fetchAll();

        return [
            'stockBajo' => $stockBajo,
            'porVencer' => $porVencer,
            'carritos'  => $carritos
        ];
    }
        // Consultar con Paginación y Filtro (Para Administrador)
    public function consultarPaginado($idSede, $busqueda, $inicio, $limite) {
        $conexionBD = BD::crearInstancia();
        $where = "u.idSede = ?";
        $params = [$idSede];
        
        if (!empty($busqueda)) {
            $where .= " AND (u.nombre LIKE ? OR u.usuario LIKE ?)";
            $params[] = "%$busqueda%";
            $params[] = "%$busqueda%";
        }
        
        $sql = $conexionBD->prepare("
            SELECT u.*, p.nombre AS nombrePermiso, s.nombre AS nombreSede, e.nombre AS nombreEstado 
            FROM usuario u 
            INNER JOIN permiso p ON p.idPermiso = u.permiso 
            INNER JOIN sede s ON s.idSede = u.idSede 
            INNER JOIN estado e ON e.idEstado = u.estado 
            WHERE $where
            ORDER BY u.idUsuario ASC
            LIMIT $inicio, $limite
        ");
        $sql->execute($params);
        return $sql->fetchAll();
    }

    // Contar total para el paginador (Administrador)
    public function contarTotal($idSede, $busqueda) {
        $conexionBD = BD::crearInstancia();
        $where = "idSede = ?";
        $params = [$idSede];
        
        if (!empty($busqueda)) {
            $where .= " AND (nombre LIKE ? OR usuario LIKE ?)";
            $params[] = "%$busqueda%";
            $params[] = "%$busqueda%";
        }
        
        $sql = $conexionBD->prepare("SELECT COUNT(*) as total FROM usuario WHERE $where");
        $sql->execute($params);
        $r = $sql->fetch();
        return $r['total'];
    }

        // Consultar Global con Paginación (Solo para Desarrollador: ve solo Administradores de empresas)
    public function consultarGlobalPaginado($busqueda, $inicio, $limite) {
        $conexionBD = BD::crearInstancia();
        // El desarrollador solo ve a los administradores (permiso 2)
        $where = "u.permiso = 2"; 
        $params = [];
        
        if (!empty($busqueda)) {
            $where .= " AND (u.nombre LIKE ? OR u.usuario LIKE ?)";
            $params[] = "%$busqueda%";
            $params[] = "%$busqueda%";
        }
        
        $sql = $conexionBD->prepare("
            SELECT u.*, p.nombre AS nombrePermiso, s.nombre AS nombreSede, e.nombre AS nombreEstado, n.razon_social as nombreNegocio
            FROM usuario u 
            INNER JOIN permiso p ON p.idPermiso = u.permiso 
            INNER JOIN sede s ON s.idSede = u.idSede 
            INNER JOIN estado e ON e.idEstado = u.estado 
            INNER JOIN negocio n ON n.idNegocio = s.idNegocio
            WHERE $where
            ORDER BY u.idUsuario ASC
            LIMIT $inicio, $limite
        ");
        $sql->execute($params);
        return $sql->fetchAll();
    }

    // Contar Global
    public function contarGlobal($busqueda) {
        $conexionBD = BD::crearInstancia();
        $where = "1=1";
        $params = [];
        
        if (!empty($busqueda)) {
            $where = "nombre LIKE ? OR usuario LIKE ?";
            $params[] = "%$busqueda%";
            $params[] = "%$busqueda%";
        }
        
        $sql = $conexionBD->prepare("SELECT COUNT(*) as total FROM usuario WHERE $where");
        $sql->execute($params);
        $r = $sql->fetch();
        return $r['total'];
    }
        // Validar si el nombre de usuario ya existe
    public function existeUsuario($usuario, $idUsuario = null) {
        $conexionBD = BD::crearInstancia();
        $sql = "SELECT COUNT(*) as total FROM usuario WHERE usuario = ?";
        $params = [$usuario];
        
        if ($idUsuario !== null) {
            $sql .= " AND idUsuario != ?";
            $params[] = $idUsuario;
        }
        
        $stmt = $conexionBD->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['total'] > 0;
    }
}
?>