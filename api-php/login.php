<?php
// login.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once 'db.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->username) && !empty($data->password)) {
    $query = "SELECT id, username, password, nombre, email FROM usuarios WHERE username = :username LIMIT 0,1";
    $stmt = $conn->prepare($query);
    
    $stmt->bindParam(':username', $data->username);
    $stmt->execute();
    
    $num = $stmt->rowCount();
    
    if ($num > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $id = $row['id'];
        $username = $row['username'];
        $nombre = $row['nombre'];
        $email = $row['email'];
        $hashed_password = $row['password'];
        
        // Verifica si la contraseña enviada coincide con la de la BD (soporta texto plano para pruebas y password_hash)
        if (password_verify($data->password, $hashed_password) || $data->password === $hashed_password) {
            
            // Genera un token aleatorio simple
            $token = bin2hex(random_bytes(16));
            
            echo json_encode(array(
                "success" => true,
                "message" => "Login exitoso.",
                "usuario" => array(
                    "id" => $id,
                    "username" => $username,
                    "nombre" => $nombre,
                    "email" => $email
                ),
                "token" => $token
            ));
        } else {
            echo json_encode(array("success" => false, "message" => "Contraseña incorrecta."));
        }
    } else {
        echo json_encode(array("success" => false, "message" => "El usuario no existe."));
    }
} else {
    echo json_encode(array("success" => false, "message" => "Datos incompletos."));
}
?>
