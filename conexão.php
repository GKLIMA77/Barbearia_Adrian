<?php
$host    = "10.10.96.27";
$usuario = "Gabriel17";
$senha   = "17092007";
$banco   = "barbearia_adrian_souza";

$conn = new mysqli($host, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Falha na conexao: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>