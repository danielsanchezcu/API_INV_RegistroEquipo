<?php
// Incluir conexión a la base de datos
include "conexion.php";

$conexion = new Conexion();

header('Content-Type: application/json');

$metodo = $_SERVER['REQUEST_METHOD'];

//Mostrar el método HTTP utilizado
print_r($metodo);

switch ($metodo) {

    //SELECT Consultar registros
    case 'GET':
       echo "Consulta de registros";
       consulta($conexion);
        break;

    //INSERT Insertar registros
    case 'POST':
        echo "Inserción de registros";
        insertar($conexion);
        break;


    //UPDATE Actualizar registros
    case 'PUT':
        echo "Actualización de registros";
        actualizar($conexion);
        break;

        
    //DELETE Eliminar registros
   case 'DELETE':
        echo "Eliminación de registros";
        $datos = json_decode(file_get_contents("php://input"), true);
        $id = $datos['id_equipo'] ?? null;
        if ($id !== null) {
            borrar($conexion, $id);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "ID no proporcionado"]);
        }
        break;

    default: 
        echo "Método no soportado";
        break;
}

function consulta($conexion){
    $sql= "SELECT * FROM equipo";
    $resultado = $conexion->query($sql);

    if ($resultado) {
        $datos= array();
        while($fila = $resultado->fetch(PDO::FETCH_ASSOC)) {
            $datos[] = $fila;
        }
      echo json_encode($datos);
    }
}

function insertar($conexion){
    $dato = json_decode(file_get_contents("php://input"), true);
    $tipo = $dato['tipo'];
    $marca = $dato['marca'];
    $modelo = $dato['modelo'];
    $serial = $dato['serial'];
    $estado = $dato['estado'];
    $placa = $dato['placa_inventario'];
    $ram = $dato['ram'];
    $disco = $dato['disco_duro'];
    $fecha_adquisicion = $dato['fecha_adquisicion'];
    $procesador = $dato['procesador'];
    $sistema_operativo = $dato['sistema_operativo'];
    $observaciones = $dato['observaciones'];
    
    $sql= "INSERT INTO equipo (
        tipo,marca,modelo,serial,estado,placa_inventario,ram,disco_duro,
        fecha_adquisicion,procesador,sistema_operativo,observaciones  
        ) VALUES ('$tipo', '$marca', '$modelo', '$serial', '$estado', '$placa', '$ram', '$disco', '$fecha_adquisicion', '$procesador', '$sistema_operativo', '$observaciones')"; 
    $resultado = $conexion->query($sql);
    
    if ($resultado) {
        $dato['id'] = $conexion->lastInsertId(); 
        echo json_encode($dato);
    } else {
        echo json_encode(array("error" => "Error al insertar el registro"));
        
    }

}

function borrar($conexion, $id) {
    try {
        $sql = "DELETE FROM equipo WHERE id_equipo = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount() > 0) {
            http_response_code(200);
            echo json_encode(["mensaje" => "Equipo con ID $id eliminado correctamente"]);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "No se encontró el equipo con ID $id"]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function actualizar($conexion) {
    $dato = json_decode(file_get_contents("php://input"), true);

    $id_equipo = $dato['id_equipo'] ?? null;

    if (!$id_equipo) {
        http_response_code(400);
        echo json_encode(["error" => "ID no proporcionado para la actualización"]);
        return;
    }

    // Campos válidos de la tabla
    $campos_validos = [
        "tipo", "marca", "modelo", "serial", "estado", "placa_inventario",
        "ram", "disco_duro", "fecha_adquisicion", "procesador",
        "sistema_operativo", "observaciones"
    ];

    // Preparar la parte SET del UPDATE dinámicamente
    $set = [];
    $valores = [];

    foreach ($campos_validos as $campo) {
        if (isset($dato[$campo])) {
            $set[] = "$campo = :$campo";
            $valores[":$campo"] = $dato[$campo];
        }
    }

    if (empty($set)) {
        http_response_code(400);
        echo json_encode(["error" => "No se proporcionaron campos para actualizar"]);
        return;
    }

    $valores[":id_equipo"] = $id_equipo;
    $sql = "UPDATE equipo SET " . implode(", ", $set) . " WHERE id_equipo = :id_equipo";

    try {
        $stmt = $conexion->prepare($sql);
        $stmt->execute($valores);

        if ($stmt->rowCount() > 0) {
            echo json_encode(["mensaje" => "Equipo actualizado correctamente"]);
        } else {
            echo json_encode(["mensaje" => "No se realizaron cambios o el equipo no existe"]);
        }

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

?>

