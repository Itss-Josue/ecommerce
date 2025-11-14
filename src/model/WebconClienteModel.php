<?php
/**
 * WebconClienteModel - Modelo para clientes de WEBCON
 */

require_once __DIR__ . '/../library/conexion_webcon.php';

class WebconClienteModel {
    public function getClientes($busqueda = '') {
        $db = new ConexionWebcon();
        $conn = $db->conectar();
        
        try {
            // Primero, listar todas las tablas para debug
            $tables = $conn->query("SHOW TABLES");
            $availableTables = [];
            while ($table = $tables->fetch_array()) {
                $availableTables[] = $table[0];
            }
            
            error_log("Tablas disponibles en WEBCON: " . implode(', ', $availableTables));
            
            // Buscar tabla de clientes
            $tableName = null;
            foreach (['clientes', 'cliente', 'users', 'usuarios'] as $possibleTable) {
                if (in_array($possibleTable, $availableTables)) {
                    $tableName = $possibleTable;
                    break;
                }
            }
            
            if (!$tableName) {
                return []; // No existe tabla de clientes
            }
            
            // Obtener columnas de la tabla
            $columns = $conn->query("SHOW COLUMNS FROM $tableName");
            $columnNames = [];
            while ($col = $columns->fetch_assoc()) {
                $columnNames[] = $col['Field'];
            }
            
            error_log("Columnas en $tableName: " . implode(', ', $columnNames));
            
            if (!empty($busqueda)) {
                // Buscar columnas para búsqueda
                $searchColumns = [];
                foreach (['razon_social', 'nombre', 'email', 'contacto', 'nombres', 'apellidos', 'name'] as $possibleCol) {
                    if (in_array($possibleCol, $columnNames)) {
                        $searchColumns[] = $possibleCol;
                    }
                }
                
                if (empty($searchColumns)) {
                    // Usar la primera columna de texto que encontremos
                    foreach ($columnNames as $col) {
                        if (!in_array($col, ['id', 'created_at', 'updated_at'])) {
                            $searchColumns[] = $col;
                            break;
                        }
                    }
                }
                
                $searchConditions = [];
                foreach ($searchColumns as $col) {
                    $searchConditions[] = "$col LIKE ?";
                }
                $conditions = implode(' OR ', $searchConditions);
                
                $sql = "SELECT * FROM $tableName WHERE $conditions ORDER BY id DESC LIMIT 100";
                $stmt = $conn->prepare($sql);
                $busquedaParam = "%" . $busqueda . "%";
                
                // Bind parameters
                $bindTypes = str_repeat("s", count($searchColumns));
                $bindParams = array_fill(0, count($searchColumns), $busquedaParam);
                $stmt->bind_param($bindTypes, ...$bindParams);
            } else {
                $sql = "SELECT * FROM $tableName ORDER BY id DESC LIMIT 100";
                $stmt = $conn->prepare($sql);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $clientes = array();
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $clientes[] = $row;
                }
            }
            
            $stmt->close();
            $db->desconectar();
            return $clientes;
            
        } catch (Exception $e) {
            $db->desconectar();
            error_log("Error en getClientes: " . $e->getMessage());
            return [];
        }
    }

    public function getClienteById($id) {
        $db = new ConexionWebcon();
        $conn = $db->conectar();
        
        try {
            // Buscar tabla de clientes
            $tables = $conn->query("SHOW TABLES");
            $tableName = null;
            while ($table = $tables->fetch_array()) {
                if (in_array($table[0], ['clientes', 'cliente', 'users', 'usuarios'])) {
                    $tableName = $table[0];
                    break;
                }
            }
            
            if (!$tableName) {
                return null;
            }
            
            $sql = "SELECT * FROM $tableName WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $cliente = $result->fetch_assoc();
            
            $stmt->close();
            $db->desconectar();
            return $cliente;
            
        } catch (Exception $e) {
            $db->desconectar();
            error_log("Error en getClienteById: " . $e->getMessage());
            return null;
        }
    }

    public function getTotalClientes() {
        $db = new ConexionWebcon();
        $conn = $db->conectar();
        
        try {
            // Buscar tabla de clientes
            $tables = $conn->query("SHOW TABLES");
            $tableName = null;
            while ($table = $tables->fetch_array()) {
                if (in_array($table[0], ['clientes', 'cliente', 'users', 'usuarios'])) {
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
            error_log("Error en getTotalClientes: " . $e->getMessage());
            return 0;
        }
    }
}
?>