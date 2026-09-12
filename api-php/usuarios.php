<?php
// usuarios.php - API RESTful CRUD de Usuarios
// Permite GET (Listar / Obtener), POST (Crear), PUT (Actualizar) y DELETE (Eliminar)

// Configuración de CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Respuesta inmediata para peticiones de pre-vuelo (Preflight OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Inclusión de la conexión a la base de datos
include_once 'db.php';

if (!isset($conn) || !$conn) {
    http_response_code(500);
    echo json_encode(array(
        "success" => false, 
        "message" => "Error interno: no se pudo conectar con la base de datos."
    ));
    exit();
}

// Detección del método HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Soporte para override de método si el cliente no soporta PUT o DELETE nativo
if ($method === 'POST' && isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
    $method = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
}

// Lectura del cuerpo JSON de la petición
$raw_input = file_get_contents("php://input");
$data = json_decode($raw_input);

switch ($method) {

    // ==========================================
    // GET: Listar todos o buscar un usuario
    // Ejemplo todos: GET /usuarios.php
    // Ejemplo por ID: GET /usuarios.php?id=1
    // ==========================================
    case 'GET':
        try {
            if (isset($_GET['id']) && !empty($_GET['id'])) {
                // Obtener usuario por ID
                $query = "SELECT id, username, nombre, email, created_at FROM usuarios WHERE id = :id LIMIT 1";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':id', $_GET['id'], PDO::PARAM_INT);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                    echo json_encode(array(
                        "success" => true,
                        "data" => $usuario
                    ));
                } else {
                    http_response_code(404);
                    echo json_encode(array(
                        "success" => false,
                        "message" => "Usuario no encontrado."
                    ));
                }
            } else {
                // Listar todos los usuarios
                $query = "SELECT id, username, nombre, email, created_at FROM usuarios ORDER BY id DESC";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode(array(
                    "success" => true,
                    "count" => count($usuarios),
                    "data" => $usuarios
                ));
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(array("success" => false, "message" => "Error en la consulta: " . $e->getMessage()));
        }
        break;

    // ==========================================
    // POST: Crear un nuevo usuario
    // Body JSON: { "username": "...", "password": "...", "nombre": "...", "email": "..." }
    // ==========================================
    case 'POST':
        if (
            empty($data->username) || 
            empty($data->password) || 
            empty($data->nombre) || 
            empty($data->email)
        ) {
            http_response_code(400);
            echo json_encode(array(
                "success" => false, 
                "message" => "Datos incompletos. Se requiere username, password, nombre y email."
            ));
            break;
        }

        try {
            // Verificar si el username ya existe
            $checkQuery = "SELECT id FROM usuarios WHERE username = :username LIMIT 1";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bindParam(':username', $data->username);
            $checkStmt->execute();

            if ($checkStmt->rowCount() > 0) {
                http_response_code(409);
                echo json_encode(array(
                    "success" => false, 
                    "message" => "El nombre de usuario '{$data->username}' ya se encuentra registrado."
                ));
                break;
            }

            // Insertar nuevo usuario (hash seguro compatible con login.php)
            $hashed_password = password_hash($data->password, PASSWORD_BCRYPT);

            $insertQuery = "INSERT INTO usuarios (username, password, nombre, email) VALUES (:username, :password, :nombre, :email)";
            $insertStmt = $conn->prepare($insertQuery);
            $insertStmt->bindParam(':username', $data->username);
            $insertStmt->bindParam(':password', $hashed_password);
            $insertStmt->bindParam(':nombre', $data->nombre);
            $insertStmt->bindParam(':email', $data->email);

            if ($insertStmt->execute()) {
                $newId = $conn->lastInsertId();
                http_response_code(201);
                echo json_encode(array(
                    "success" => true,
                    "message" => "Usuario creado exitosamente.",
                    "id" => (int)$newId
                ));
            } else {
                http_response_code(500);
                echo json_encode(array("success" => false, "message" => "No se pudo registrar el usuario."));
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(array("success" => false, "message" => "Error al crear usuario: " . $e->getMessage()));
        }
        break;

    // ==========================================
    // PUT: Actualizar un usuario existente
    // Body JSON: { "id": 1, "username": "...", "nombre": "...", "email": "...", "password": "opcional" }
    // ==========================================
    case 'PUT':
        $id = $data->id ?? $_GET['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(array(
                "success" => false, 
                "message" => "Debe proporcionar el ID del usuario a actualizar."
            ));
            break;
        }

        try {
            // Verificar existencia del usuario
            $checkQuery = "SELECT id, password FROM usuarios WHERE id = :id LIMIT 1";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $checkStmt->execute();

            if ($checkStmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(array("success" => false, "message" => "El usuario con ID {$id} no existe."));
                break;
            }

            $userActual = $checkStmt->fetch(PDO::FETCH_ASSOC);

            // Si se intenta cambiar username, comprobar que no esté tomado por otro usuario
            if (!empty($data->username)) {
                $uniqueQuery = "SELECT id FROM usuarios WHERE username = :username AND id != :id LIMIT 1";
                $uniqueStmt = $conn->prepare($uniqueQuery);
                $uniqueStmt->bindParam(':username', $data->username);
                $uniqueStmt->bindParam(':id', $id, PDO::PARAM_INT);
                $uniqueStmt->execute();

                if ($uniqueStmt->rowCount() > 0) {
                    http_response_code(409);
                    echo json_encode(array(
                        "success" => false, 
                        "message" => "El nombre de usuario '{$data->username}' ya está en uso por otro registro."
                    ));
                    break;
                }
            }

            // Construir campos a actualizar de manera dinámica
            $fields = array();
            $params = array(':id' => (int)$id);

            if (!empty($data->username)) {
                $fields[] = "username = :username";
                $params[':username'] = $data->username;
            }
            if (!empty($data->nombre)) {
                $fields[] = "nombre = :nombre";
                $params[':nombre'] = $data->nombre;
            }
            if (!empty($data->email)) {
                $fields[] = "email = :email";
                $params[':email'] = $data->email;
            }
            if (!empty($data->password)) {
                $fields[] = "password = :password";
                $params[':password'] = password_hash($data->password, PASSWORD_BCRYPT);
            }

            if (empty($fields)) {
                echo json_encode(array(
                    "success" => true, 
                    "message" => "No se enviaron campos para modificar."
                ));
                break;
            }

            $updateQuery = "UPDATE usuarios SET " . implode(", ", $fields) . " WHERE id = :id";
            $updateStmt = $conn->prepare($updateQuery);

            foreach ($params as $key => $val) {
                $updateStmt->bindValue($key, $val);
            }

            if ($updateStmt->execute()) {
                echo json_encode(array(
                    "success" => true,
                    "message" => "Usuario actualizado correctamente."
                ));
            } else {
                http_response_code(500);
                echo json_encode(array("success" => false, "message" => "No se pudo actualizar el usuario."));
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(array("success" => false, "message" => "Error al actualizar usuario: " . $e->getMessage()));
        }
        break;

    // ==========================================
    // DELETE: Eliminar un usuario
    // Ejemplo por query param: DELETE /usuarios.php?id=1
    // Ejemplo por body JSON: { "id": 1 }
    // ==========================================
    case 'DELETE':
        $id = $data->id ?? $_GET['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(array(
                "success" => false, 
                "message" => "Debe proporcionar el ID del usuario a eliminar."
            ));
            break;
        }

        try {
            // Verificar existencia previa
            $checkQuery = "SELECT id FROM usuarios WHERE id = :id LIMIT 1";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $checkStmt->execute();

            if ($checkStmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(array("success" => false, "message" => "El usuario con ID {$id} no existe."));
                break;
            }

            // Eliminar registro
            $deleteQuery = "DELETE FROM usuarios WHERE id = :id";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($deleteStmt->execute()) {
                echo json_encode(array(
                    "success" => true,
                    "message" => "Usuario eliminado exitosamente."
                ));
            } else {
                http_response_code(500);
                echo json_encode(array("success" => false, "message" => "No se pudo eliminar el usuario."));
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(array("success" => false, "message" => "Error al eliminar usuario: " . $e->getMessage()));
        }
        break;

    // ==========================================
    // Método no permitido
    // ==========================================
    default:
        http_response_code(405);
        echo json_encode(array(
            "success" => false,
            "message" => "Método HTTP '{$method}' no permitido."
        ));
        break;
}
?>
