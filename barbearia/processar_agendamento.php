<?php
$host    = "10.10.96.27";
$usuario = "Gabriel17";
$senha   = "17092007";
$banco   = "barbearia_adrian_souza";

$conexao = new mysqli($host, $usuario, $senha, $banco);

if ($conexao->connect_error) {
    die(json_encode(['sucesso' => false, 'mensagem' => 'Erro ao conectar ao banco.']));
}

$conexao->set_charset("utf8mb4");
$dados = [
    'nome'    => trim($_POST['agenda-name']    ?? ''),
    'data'    => trim($_POST['agenda-date']    ?? ''),
    'horario' => trim($_POST['agenda-time']    ?? ''),
    'servico' => trim($_POST['agenda-service'] ?? ''),
];

function calcularDesconto($servico) {
    $descontos = [
        'Corte Premium'      => 0,
        'Barba Premium'      => 0,
        'Combo Completo'     => 10,
        'Plano Profissional' => 15,
    ];

    // ✅ TECH FORGE 5 — VALIDAÇÃO DE REGRAS DE NEGÓCIO
    // Verifica se o serviço existe no array antes de usar
    if (isset($descontos[$servico])) {
        return $descontos[$servico];
    } else {
        return 0;
    }
}
function formatarDataHora($data, $horario) {
    return $data . ' ' . $horario . ':00';
}

// Validação básica de campos
if (empty($dados['nome']) || empty($dados['data']) || empty($dados['horario']) || empty($dados['servico'])) {
    die(json_encode(['sucesso' => false, 'mensagem' => 'Preencha todos os campos.']));
}

$dataHora = formatarDataHora($dados['data'], $dados['horario']);

if (strtotime($dataHora) < time()) {
    die(json_encode(['sucesso' => false, 'mensagem' => 'A data deve ser no futuro.']));
}

$hoje = date('Y-m-d');
$stmt = $conexao->prepare("SELECT data_hora, status FROM agendamentos WHERE DATE(data_hora) = ? ORDER BY data_hora ASC");
$stmt->bind_param("s", $hoje);
$stmt->execute();
$resultado = $stmt->get_result();
$stmt->close();

$horariosOcupados = [];

while ($linha = $resultado->fetch_assoc()) {
    // Filtro: só adiciona se o status for pendente ou confirmado
    if ($linha['status'] == 'pendente' || $linha['status'] == 'confirmado') {
        $horariosOcupados[] = date('H:i', strtotime($linha['data_hora']));
    }
}

// Verifica conflito de horário usando o array filtrado
$horarioSolicitado = $dados['horario'];

if (in_array($horarioSolicitado, $horariosOcupados)) {
    die(json_encode(['sucesso' => false, 'mensagem' => 'Este horário já está ocupado. Escolha outro.']));
}

// === BUSCAR ID DO SERVIÇO ===
$stmt = $conexao->prepare("SELECT id FROM servicos WHERE nome = ? AND ativo = 1");
$stmt->bind_param("s", $dados['servico']);
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();

if ($res->num_rows === 0) {
    die(json_encode(['sucesso' => false, 'mensagem' => 'Serviço não encontrado.']));
}

$servico   = $res->fetch_assoc();
$servicoId = $servico['id'];

// Calcula desconto usando a função criada acima
$desconto = calcularDesconto($dados['servico']);

// === INSERIR AGENDAMENTO ===
$stmt = $conexao->prepare("INSERT INTO agendamentos (cliente_nome, servico_id, data_hora, status) VALUES (?, ?, ?, 'pendente')");
$stmt->bind_param("sis", $dados['nome'], $servicoId, $dataHora);

if ($stmt->execute()) {
    echo json_encode([
        'sucesso'           => true,
        'mensagem'          => 'Agendamento realizado com sucesso!',
        'agendamento_id'    => $conexao->insert_id,
        'desconto_aplicado' => $desconto . '%',
        'dados' => [
            'nome'      => $dados['nome'],
            'servico'   => $dados['servico'],
            'data_hora' => $dataHora,
        ]
    ]);
} else {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao salvar: ' . $stmt->error]);
}

$stmt->close();
$conexao->close();
?>
