<?php
/**
 * WebconProyectoModel - Modelo para proyectos de WEBCON
 */

require_once __DIR__ . '/../library/conexion_webcon.php';

class WebconProyectoModel {
    public function getProyectos() {
        $db = new ConexionWebcon();
        $conn = $db->conectar();
        
        try {
            // Listar tablas disponibles
            $tables = $conn->query("SHOW TABLES");
            $availableTables = [];
            while ($table = $tables->fetch_array()) {
                $availableTables[] = $table[0];
            }
            
            // Buscar tabla de proyectos
            $tableName = null;
            foreach (['proyectos', 'proyecto', 'projects'] as $possibleTable) {
                if (in_array($possibleTable, $availableTables)) {
                    $tableName = $possibleTable;
                    break;
                }
            }
            
            if (!$tableName) {
                return []; // No existe tabla de proyectos
            }
            
            // Buscar tabla de clientes para JOIN
            $clientesTable = null;
            foreach (['clientes', 'cliente'] as $possibleTable) {
                if (in_array($possibleTable, $availableTables)) {
                    $clientesTable = $possibleTable;
                    break;
                }
            }
            
            if ($clientesTable) {
                // Verificar si existe la columna cliente_id en proyectos
                $columns = $conn->query("SHOW COLUMNS FROM $tableName");
                $hasClienteId = false;
                while ($col = $columns->fetch_assoc()) {
                    if ($col['Field'] == 'cliente_id') {
                        $hasClienteId = true;
                        break;
                    }
                }
                
                if ($hasClienteId) {
                    $sql = "SELECT p.*, c.nombre as cliente_nombre 
                            FROM $tableName p 
                            LEFT JOIN $clientesTable c ON p.cliente_id = c.id 
                            ORDER BY p.id DESC LIMIT 100";
                } else {
                    $sql = "SELECT * FROM $tableName ORDER BY id DESC LIMIT 100";
                }
            } else {
                $sql = "SELECT * FROM $tableName ORDER BY id DESC LIMIT 100";
            }
            
            $result = $conn->query($sql);
            
            $proyectos = array();
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $proyectos[] = $row;
                }
            }
            
            $db->desconectar();
            return $proyectos;
            
        } catch (Exception $e) {
            $db->desconectar();
            error_log("Error en getProyectos: " . $e->getMessage());
            return [];
        }
    }

    public function getTotalProyectos() {
        $db = new ConexionWebcon();
        $conn = $db->conectar();
        
        try {
            // Buscar tabla de proyectos
            $tables = $conn->query("SHOW TABLES");
            $tableName = null;
            while ($table = $tables->fetch_array()) {
                if (in_array($table[0], ['proyectos', 'proyecto', 'projects'])) {
                    $tableName = $table[0];
                    break;
                }
            }
            
            if (!$tableName) {
                return 0;
            }
            
            $sql = "SELECT COUNT(*) as total FROM $tableName";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();
            
            $db->desconectar();
            return $row['total'];
            
        } catch (Exception $e) {
            $db->desconectar();
            error_log("Error en getTotalProyectos: " . $e->getMessage());
            return 0;
        }
    }
}
?>