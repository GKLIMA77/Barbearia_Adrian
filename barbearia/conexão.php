<?php
$servername = "10.10.96.27"; // IP do Servidor XAMPP
$username = "Gabriel17"; 
$password = "17092007";
$dbname = "barbearia_adrian_souza";

// Criar conexão usando MySQLi
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
?>
