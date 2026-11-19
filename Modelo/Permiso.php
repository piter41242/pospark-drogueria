<?php
class Permiso
{

    public function consultarSinDesarrollador(){
        $list = [];
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->prepare("SELECT * FROM permiso WHERE idPermiso <> 1");
        $sql->execute();
        foreach ($sql->fetchAll() as $row) $list[] = $row;
        return $list;
    }
}
?>