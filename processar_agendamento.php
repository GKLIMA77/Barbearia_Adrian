<?php
// ================= processar_agendamento.php =================
ob_start();
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

include("conexao.php");

// ── Array estruturado de serviços com preço e desconto ────────────────────────
$servicosDisponiveis = [
    'Corte Premium'      => ['preco' => 45.00,  'desconto' => 0],
    'Barba Premium'      => ['preco' => 35.00,  'desconto' => 0],
    'Combo Completo'     => ['preco' => 70.00,  'desconto' => 10],
    'Plano Profissional' => ['preco' => 120.00, 'desconto' => 15],
];

// ── Função — aplica desconto e retorna preço final ────────────────────────────
function aplicarDesconto($preco, $percentual) {
    if ($percentual <= 0) {
        return $preco;
    }
    return $preco - ($preco * $percentual / 100);
}

// ── Função — valida se o preço é positivo ─────────────────────────────────────
function precoValido($preco) {
    if ($preco <= 0) {
        return false;
    }
    return true;
}

// ── Função — valida campos obrigatórios ───────────────────────────────────────
function validarCampos($nome, $data, $horario, $servico) {
    if (empty($nome) || empty($data) || empty($horario) || empty($servico)) {
        return 'Preencha todos os campos.';
    }
    return null;
}

// ── Função — valida regra de negócio do serviço ───────────────────────────────
function validarServico($servico, $tabela) {
    if (!isset($tabela[$servico])) {
        return 'Servico invalido.';
    }
    if ($tabela[$servico]['preco'] <= 0) {
        return 'Servico com preco invalido.';
    }
    return null;
}

// ── Função — filtra serviços com desconto ─────────────────────────────────────
function filtrarServicosComDesconto($tabela) {
    $resultado = [];
    foreach ($tabela as $nome => $dados) {
        if ($dados['desconto'] > 0) {
            $resultado[] = $nome;
        }
    }
    return $resultado;
}

// ── Função — formata data e hora ──────────────────────────────────────────────
function formatarDataHora($data, $horario) {
    return $data . ' ' . $horario . ':00';
}

// ── Função — responde em JSON e encerra ───────────────────────────────────────
function responder($sucesso, $mensagem, $extra = []) {
    ob_clean();
    echo json_encode(array_merge(['sucesso' => $sucesso, 'mensagem' => $mensagem], $extra));
    exit;
}

// ── Receber dados do formulário ───────────────────────────────────────────────
$nome    = trim($_POST['agenda-nome']    ?? '');
$data    = trim($_POST['agenda-data']    ?? '');
$horario = trim($_POST['agenda-horario']    ?? '');
$servico = trim($_POST['agenda-servico'] ?? '');

// ── Validar campos ────────────────────────────────────────────────────────────
$erroCampos = validarCampos($nome, $data, $horario, $servico);
if ($erroCampos !== null) {
    responder(false, $erroCampos);
}

// ── Validar serviço ───────────────────────────────────────────────────────────
$erroServico = validarServico($servico, $servicosDisponiveis);
if ($erroServico !== null) {
    responder(false, $erroServico);
}

$dataHora            = formatarDataHora($data, $horario);
$desconto            = $servicosDisponiveis[$servico]['desconto'];
$servicosComDesconto = filtrarServicosComDesconto($servicosDisponiveis);

// ── Buscar ID do serviço no banco ─────────────────────────────────────────────
$stmt = $conexao->prepare("SELECT id FROM servicos WHERE nome = ? AND ativo = 1");
$stmt->bind_param("s", $servico);
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();

if ($res->num_rows === 0) {
    responder(false, 'Servico nao encontrado no banco.');
}

$servicoId = $res->fetch_assoc()['id'];

// ── Inserir agendamento ───────────────────────────────────────────────────────
$stmt = $conexao->prepare("INSERT INTO agendamentos (cliente_nome, servico_id, data_hora, status) VALUES (?, ?, ?, 'pendente')");
$stmt->bind_param("sis", $nome, $servicoId, $dataHora);

if ($stmt->execute()) {
    responder(true, 'Agendamento realizado com sucesso!', [
        'agendamento_id'    => $conexao->insert_id,
        'desconto_aplicado' => $desconto . '%',
        'servicos_promocao' => $servicosComDesconto,
    ]);
} else {
    responder(false, 'Erro ao salvar: ' . $stmt->error);
}

$stmt->close();
$conexao->close();
