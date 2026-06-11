<?php
$conexao = new mysqli("192.168.56.101", "biel", "gabriel123", "barbearia_adrian_souza");

if ($conexao->connect_error) {
    die("Erro de conexao: " . $conexao->connect_error);
}

$conexao->set_charset("utf8mb4");
?>