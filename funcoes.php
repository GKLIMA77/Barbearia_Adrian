<?php

// Função para mostrar agendamentos de hoje
function mostrarAgendamentosHoje($conexao) {
    
    // Busca TODOS os agendamentos do banco
    $sql = "SELECT a.cliente_nome, s.nome AS servico, s.preco, a.data_hora, a.status
            FROM agendamentos a
            JOIN servicos s ON s.id = a.servico_id";
    
    $resultado = $conexao->query($sql);
    $agendamentos = [];
    
    // Guarda todos os agendamentos em um array
    while ($linha = $resultado->fetch_assoc()) {
        $agendamentos[] = $linha;
    }
    
    // Pega a data de hoje
    $hoje = date('Y-m-d');
    $agendamentosHoje = [];
    
    // Filtra apenas os agendamentos de hoje
    foreach ($agendamentos as $agendamento) {
        $dataAgendamento = date('Y-m-d', strtotime($agendamento['data_hora']));
        
        if ($dataAgendamento == $hoje) {
            $agendamentosHoje[] = $agendamento;
        }
    }
    
    // Se não houver agendamentos, avisa
    if (empty($agendamentosHoje)) {
        echo "Nenhum agendamento para hoje.";
        return;
    }
    
    // Mostra a tabela
    echo "<table border='1' cellpadding='10' style='margin-top: 20px;'>";
    echo "<tr>";
    echo "<th>Horário</th>";
    echo "<th>Cliente</th>";
    echo "<th>Serviço</th>";
    echo "<th>Valor</th>";
    echo "<th>Status</th>";
    echo "</tr>";
    
    // Exibe cada agendamento de hoje
    foreach ($agendamentosHoje as $agendamento) {
        $horario = date('H:i', strtotime($agendamento['data_hora']));
        
        echo "<tr>";
        echo "<td>" . $horario . "</td>";
        echo "<td>" . $agendamento['cliente_nome'] . "</td>";
        echo "<td>" . $agendamento['servico'] . "</td>";
        echo "<td>R$ " . $agendamento['preco'] . "</td>";
        echo "<td>" . $agendamento['status'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
}

?>
