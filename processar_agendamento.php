<?php
// Inicia buffer de saída para que possamos enviar apenas JSON ao final.
ob_start();

// Não mostra erros diretamente no navegador para evitar vazamento de dados.
ini_set('display_errors', 0);

// Define o cabeçalho de resposta como JSON UTF-8.
header('Content-Type: application/json; charset=utf-8');

// Cria a conexão com o banco de dados MySQL.
$conexao = new mysqli("localhost", "Gabriel17", "17092007", "barbearia_adrian_souza");

// Verifica se a conexão falhou e retorna erro em JSON.
if ($conexao->connect_error) {
    ob_clean();
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro de conexao: ' . $conexao->connect_error
    ]);
    exit;
}

// Recebe os dados enviados pelo formulário via POST e remove espaços extras.
$nome    = trim($_POST['agenda-name']    ?? '');
$data    = trim($_POST['agenda-date']    ?? '');
$horario = trim($_POST['agenda-time']    ?? '');
$servico = trim($_POST['agenda-service'] ?? '');

// Verifica se todos os campos obrigatórios foram preenchidos.
if (empty($nome) || empty($data) || empty($horario) || empty($servico)) {
    ob_clean();
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Preencha todos os campos.'
    ]);
    exit;
}

// Combina data e horário no formato compatível com o banco.
$dataHora = $data . ' ' . $horario . ':00';

// Busca o ID do serviço informado no banco, apenas se ele estiver ativo.
$stmt = $conexao->prepare("SELECT id FROM servicos WHERE nome = ? AND ativo = 1");
$stmt->bind_param("s", $servico);
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();

// Se não encontrou o serviço, retorna erro.
if ($res->num_rows === 0) {
    ob_clean();
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Servico nao encontrado.'
    ]);
    exit;
}

// Obtém o ID do serviço encontrado para usar na tabela de agendamentos.
$servicoId = $res->fetch_assoc()['id'];

// Prepara a inserção do novo agendamento com status pendente.
$stmt = $conexao->prepare(
    "INSERT INTO agendamentos (cliente_nome, servico_id, data_hora, status) VALUES (?, ?, ?, 'pendente')"
);
$stmt->bind_param("sis", $nome, $servicoId, $dataHora);

// Executa a inserção e retorna resposta em JSON de acordo com o resultado.
if ($stmt->execute()) {
    ob_clean();
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Agendamento realizado com sucesso!'
    ]);
} else {
    ob_clean();
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao salvar: ' . $stmt->error
    ]);
}

// Fecha statement e conexão com o banco.
$stmt->close();
$conexao->close();