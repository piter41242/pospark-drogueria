<?php
class Sede
{
    private $idSede;
    private $idNegocio;
    private $nombre;
    private $direccion;
    private $telefono;
    private $ciudad;
    private $codigo_dane;
    private $estado;

    public function __construct($idSede = null, $idNegocio = null, $nombre = null, $direccion = null, $telefono = null, $ciudad = null, $codigo_dane = null, $estado = 1)
    {
        $this->idSede      = $idSede;
        $this->idNegocio   = $idNegocio;
        $this->nombre      = $nombre;
        $this->direccion   = $direccion;
        $this->telefono    = $telefono;
        $this->ciudad      = $ciudad;
        $this->codigo_dane = $codigo_dane;
        $this->estado      = $estado;
    }

    public function consultarTodos()
    {
        $list = [];
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->query("
            SELECT s.*, n.razon_social AS nombreNegocio
            FROM sede s
            INNER JOIN negocio n ON n.idNegocio = s.idNegocio
            ORDER BY s.idSede ASC
        ");
        foreach ($sql->fetchAll() as $row) $list[] = $row;
        return $list;
    }

    public function consultarPorNegocio($idNegocio)
    {
        $list = [];
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->prepare("
            SELECT s.*, n.razon_social AS nombreNegocio
            FROM sede s
            INNER JOIN negocio n ON n.idNegocio = s.idNegocio
            WHERE s.idNegocio = ?
            ORDER BY s.idSede ASC
        ");
        $sql->execute(array($idNegocio));
        foreach ($sql->fetchAll() as $row) $list[] = $row;
        return $list;
    }

    public function buscarId($id)
    {
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->prepare("
            SELECT s.*, n.razon_social AS nombreNegocio
            FROM sede s
            INNER JOIN negocio n ON n.idNegocio = s.idNegocio
            WHERE s.idSede = ?
        ");
        $sql->execute(array($id));
        return $sql->fetch();
    }

    public function crear($idNegocio, $nombre, $direccion, $telefono, $ciudad, $codigo_dane, $estado)
    {
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->prepare("
            INSERT INTO sede (idNegocio, nombre, direccion, telefono, ciudad, codigo_dane, estado)
            VALUES (?,?,?,?,?,?,?)
        ");
        return $sql->execute(array($idNegocio, $nombre, $direccion, $telefono, $ciudad, $codigo_dane, $estado));
    }

    public function modificar($idSede, $idNegocio, $nombre, $direccion, $telefono, $ciudad, $codigo_dane, $estado)
    {
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->prepare("
            UPDATE sede
            SET idNegocio=?, nombre=?, direccion=?, telefono=?, ciudad=?, codigo_dane=?, estado=?
            WHERE idSede=?
        ");
        return $sql->execute(array($idNegocio, $nombre, $direccion, $telefono, $ciudad, $codigo_dane, $estado, $idSede));
    }

    public function borrar($id)
    {
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->prepare("DELETE FROM sede WHERE idSede=?");
        return $sql->execute(array($id));
    }

    public function tieneDependencias($id)
    {
        $conexionBD = BD::crearInstancia();
        $tablas = ['usuario', 'cliente', 'lote', 'carrito', 'venta', 'compra', 'kardex', 'cierre', 'cajon', 'gastos', 'base', 'domiciliario'];
        foreach ($tablas as $t) {
            $sql = $conexionBD->prepare("SELECT COUNT(*) AS total FROM $t WHERE idSede = ?");
            $sql->execute(array($id));
            if ($sql->fetch()['total'] > 0) return true;
        }
        return false;
    }
    // Obtener sedes paginadas (para Admin y Desarrollador)
    public function consultarPaginado($idNegocio, $busqueda, $inicio, $limite)
    {
        $conexionBD = BD::crearInstancia();
        $where = "1=1";
        $params = [];

        // Filtro estricto por negocio
        
        if ($idNegocio !== null) {
            $where .= " AND s.idNegocio = ?";
            $params[] = $idNegocio;
        }

        // Filtro de búsqueda
        if (!empty($busqueda)) {
            $where .= " AND (s.nombre LIKE ? OR s.ciudad LIKE ? OR s.direccion LIKE ?)";
            $params[] = "%$busqueda%";
            $params[] = "%$busqueda%";
            $params[] = "%$busqueda%";
        }

        $sql = $conexionBD->prepare("
            SELECT s.*, n.razon_social AS nombreNegocio
            FROM sede s
            INNER JOIN negocio n ON n.idNegocio = s.idNegocio
            WHERE $where
            ORDER BY s.idSede ASC
            LIMIT $inicio, $limite
        ");
        $sql->execute($params);
        return $sql->fetchAll();
    }

    public function contarTotal($idNegocio, $busqueda)
    {
        $conexionBD = BD::crearInstancia();
        $where = "1=1";
        $params = [];

        if ($idNegocio !== null) {
            $where .= " AND idNegocio = ?";
            $params[] = $idNegocio;
        }

        if (!empty($busqueda)) {
            $where .= " AND (nombre LIKE ? OR ciudad LIKE ? OR direccion LIKE ?)";
            $params[] = "%$busqueda%";
            $params[] = "%$busqueda%";
            $params[] = "%$busqueda%";
        }

        $sql = $conexionBD->prepare("SELECT COUNT(*) as total FROM sede WHERE $where");
        $sql->execute($params);
        $r = $sql->fetch();
        return $r['total'];
    }
    public function existeSede($nombre, $idNegocio, $idSede = null)
    {
        $conexionBD = BD::crearInstancia();
        // Si estamos editando, excluimos la sede actual de la búsqueda
        $sql = "SELECT COUNT(*) as total FROM sede WHERE nombre = ? AND idNegocio = ?";
        $params = [$nombre, $idNegocio];

        if ($idSede !== null) {
            $sql .= " AND idSede != ?";
            $params[] = $idSede;
        }

        $stmt = $conexionBD->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['total'] > 0;
    }
}
