<?php
class Negocio
{
    public static function consultarTodos(){
        $list = [];
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->query("SELECT * FROM negocio");
        foreach ($sql->fetchAll() as $row) $list[] = $row;
        return $list;
    }

    public static function buscarId($id){
        $conexionBD = BD::crearInstancia();
        $sql = $conexionBD->prepare("SELECT * FROM negocio WHERE idNegocio=?");
        $sql->execute(array($id));
        return $sql->fetch();
    }
}
?>