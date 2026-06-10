<?php
// Conexão simples com o banco de dados
$conexao = new mysqli("localhost", "Gabriel17", "17092007", "barbearia_adrian_souza");
if ($conexao->connect_error) {
    die("Erro de conexao: " . $conexao->connect_error);
}

$conexao->set_charset("utf8mb4");

?>
