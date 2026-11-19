<?php
class BD{
    private static $instancia = null;

    public static function crearInstancia(){
        if(!isset(self::$instancia)){
            $opcionesPDO = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            try {
                self::$instancia = new PDO(
                    'mysql:host=localhost;dbname=drogueria_dian;charset=utf8mb4',
                    'root',
                    'Evensoft41243509',
                    $opcionesPDO
                );
            } catch (PDOException $e) {
                die("Error de conexión: " . $e->getMessage());
            }
        }
        return self::$instancia;
    }
}
?>