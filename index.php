<!-- ================= index.php ================= -->

<?php
// ── Conexão com o Banco de Dados (arquivo separado) ─────────────────────────
include __DIR__ . '/conexao.php';
include __DIR__ . '/funcoes.php';

// ── Array estruturado de descontos por serviço ────────────────────────────────
$descontos = [
    'Corte Premium'      => 0,
    'Barba Premium'      => 0,
    'Combo Completo'     => 10,
    'Plano Profissional' => 15,
];

// ── Função com parâmetro e return — aplica desconto ──────────────────────────
function aplicarDesconto($preco, $percentual) {
    if ($percentual <= 0) {
        return $preco;
    }
    return $preco - ($preco * $percentual / 100);
}

// ── Função de validação de regra de negócio ───────────────────────────────────
function precoValido($preco) {
    if ($preco <= 0) {
        return false;
    }
    return true;
}

// ── Buscar serviços do banco ──────────────────────────────────────────────────
$resultado = $conexao->query("SELECT * FROM servicos WHERE ativo = 1 ORDER BY preco ASC");

$servicos = [];
while ($linha = $resultado->fetch_assoc()) {
    $servicos[] = $linha;
}

// ── Buscar agendamentos recentes do banco ─────────────────────────────────────
$resAgendamentos = $conexao->query(
    "SELECT a.cliente_nome, s.nome AS servico_nome, a.data_hora, a.status
     FROM agendamentos a
     JOIN servicos s ON s.id = a.servico_id
     ORDER BY a.criado_em DESC
     "
);

$agendamentosRecentes = [];
while ($linha = $resAgendamentos->fetch_assoc()) {
    $agendamentosRecentes[] = $linha;
}
?>

<?php include("header.php"); ?>

<!-- ================= HERO ================= -->

<section class="hero" id="inicio">

<div class="hero-content">

<p class="mini-texto">BARBEARIA PREMIUM</p>

<h1>Seu estilo começa aqui.</h1>

<p>
Mais do que cortes, entregamos estilo,
presença e personalidade.
</p>

<a href="#agenda" class="btn-principal">
Agendar Horário
</a>

</div>

</section>

<!-- ================= SOBRE ================= -->

<section class="sobre" id="sobre">

<div class="sobre-img">

<img src="imagens/Adrian.png" alt="Barbeiro Adrian Souza">

</div>

<div class="sobre-texto">

<p class="mini-texto">SOBRE</p>

<h2>Barbearia Adrian Souza</h2>

<p>
Especialistas em cortes modernos,
degradê, barba e estilo masculino premium.
</p>

<div class="info-boxes">

<div class="info">
<h3>+5</h3>
<span>Anos de experiência</span>
</div>

<div class="info">
<h3>100%</h3>
<span>Clientes satisfeitos</span>
</div>

</div>

</div>

</section>

<!-- ================= SERVIÇOS ================= -->

<section class="planos" id="servicos">

<div class="container">

<div class="section-topo">

<p class="mini-texto">PLANOS MENSAIS</p>

<h2>Escolha o plano ideal</h2>

</div>

<div class="row g-4 justify-content-center">

<?php
// ── FOREACH — percorre serviços vindos do banco ───────────────────────────────
foreach ($servicos as $sv):
    $desc       = $descontos[$sv['nome']] ?? 0;
    $precoFinal = aplicarDesconto((float)$sv['preco'], $desc);
    $destaque   = ($sv['nome'] === 'Plano Profissional');

    // IF — valida se o preço é válido antes de exibir
    if (!precoValido((float)$sv['preco'])) continue;
?>

<div class="col-lg-3 col-md-6">
<div class="card plano-card h-100 border-0 <?php if ($destaque) echo 'plano-destaque position-relative'; ?>">

<div class="card-body p-4 text-center">

<p class="plano-titulo <?php if ($destaque) echo 'destaque-titulo'; ?>">
  <?php echo htmlspecialchars($sv['nome']); ?>
</p>

<h3 class="preco-plano <?php if ($destaque) echo 'destaque-preco'; ?>">
  R$ <?php echo number_format((float)$sv['preco'], 2, ',', '.'); ?>
</h3>

<?php if ($desc > 0): ?>
  <!-- Badge Bootstrap — componente 1 -->
  <span class="badge bg-success mb-2"><?php echo $desc; ?>% de desconto</span>
  <p style="font-size:.85rem; color:#aaa;">
    Por R$ <?php echo number_format($precoFinal, 2, ',', '.'); ?>
  </p>
<?php endif; ?>

<ul class="plano-list list-unstyled mt-3 mb-4">
  <li><i class="fa-solid fa-check"></i> Atendimento premium</li>
  <li><i class="fa-solid fa-check"></i> Profissionais especializados</li>
  <?php if ($destaque): ?>
  <li><i class="fa-solid fa-check"></i> Atendimento prioritário</li>
  <?php endif; ?>
</ul>

<a href="#agenda" class="btn btn-warning plano-btn <?php if ($destaque) echo 'destaque-btn'; ?>">
  Contratar Agora
</a>

</div>
</div>
</div>

<?php endforeach; ?>

</div>

</div>

</section>

<!-- ================= AGENDA ================= -->

<section class="agenda" id="agenda">

<div class="container">

<div class="section-topo">

<p class="mini-texto">AGENDAMENTO</p>

<h2>Agende seu atendimento</h2>

<p class="agenda-descricao">Escolha a data, o horário e o serviço desejado. Preencha seus dados e confirme o agendamento.</p>

</div>

<div class="agenda-card">

<!-- ALERTA DE SUCESSO -->
<div id="alerta-sucesso" class="alert alert-success alert-dismissible fade show" style="display: none;">
  <strong>✓ Sucesso!</strong> <span id="alerta-msg-sucesso"></span>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

<!-- ALERTA DE ERRO -->
<div id="alerta-erro" class="alert alert-danger alert-dismissible fade show" style="display: none;">
  <strong>✗ Erro!</strong> <span id="alerta-msg-erro"></span>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

<form id="form-agendamento">

<div class="form-row">

<div class="form-group">

<label for="agenda-name">Seu nome</label>

<input type="text"
id="agenda-name"
name="agenda-name"
class="form-control"
placeholder="Digite seu nome"
required>

</div>

<div class="form-group">

<label for="agenda-date">Data do atendimento</label>

<input type="date"
id="agenda-date"
name="agenda-date"
class="form-control"
required>

</div>

<div class="form-group">

<label for="agenda-time">Horário</label>

<select id="agenda-time"
name="agenda-time"
class="form-control"
required>

<option value="">
Selecione o horário
</option>

<option value="09:00">09:00</option>
<option value="10:30">10:30</option>
<option value="12:00">12:00</option>
<option value="14:00">14:00</option>
<option value="15:30">15:30</option>
<option value="17:00">17:00</option>
<option value="18:30">18:30</option>

</select>

</div>

</div>

<div class="form-row">

<div class="form-group full-width">

<label for="agenda-service">
Serviço desejado
</label>

<select id="agenda-service"
name="agenda-service"
class="form-control"
required>

<option value="">
Selecione o serviço
</option>

<option value="Corte Premium">Corte Premium</option>

<option value="Barba Premium">Barba Premium</option>

<option value="Combo Completo">Combo Completo</option>

<option value="Plano Profissional">Plano Profissional</option>

</select>

</div>

</div>

<button type="submit"
class="btn btn-warning mt-4" id="btn-submit">

Confirmar agendamento

</button>

</form>

</div>

</div>

</section>

<!-- ================= FEEDBACKS ================= -->

<section class="feedbacks" id="feedbacks">

<div class="container">

<div class="section-topo">

<p class="mini-texto">FEEDBACKS</p>

<h2>Clientes Satisfeitos</h2>

</div>

<div class="row g-4">

<!-- CLIENTE 1 -->

<div class="col-md-4">

<div class="card feedback-card border-0 h-100">

<div class="card-body p-4">

<div class="d-flex align-items-center mb-4">

<img src="imagens/Satisfeito1.png"
class="cliente-img"
alt="Cliente 1">

<div class="ms-3">

<h5>Felipe Martins</h5>

<span class="text-secondary">
Cliente Premium
</span>

</div>

</div>

<div class="stars">★★★★★</div>

<p>
Atendimento impecável do início ao fim.
</p>

</div>

</div>

</div>

<!-- CLIENTE 2 -->

<div class="col-md-4">

<div class="card feedback-card border-warning h-100">

<div class="card-body p-4">

<div class="d-flex align-items-center mb-4">

<img src="imagens/Satisfeito2.png"
class="cliente-img"
alt="Cliente 2">

<div class="ms-3">

<h5>Gustavo Henrique</h5>

<span class="text-secondary">
Cliente VIP
</span>

</div>

</div>

<div class="stars">★★★★★</div>

<p>
Atendimento rápido, profissional
e de qualidade.
</p>

</div>

</div>

</div>

<!-- CLIENTE 3 -->

<div class="col-md-4">

<div class="card feedback-card border-0 h-100">

<div class="card-body p-4">

<div class="d-flex align-items-center mb-4">

<img src="imagens/Satisfeito3.png"
class="cliente-img"
alt="Cliente 3">

<div class="ms-3">

<h5>Lucas Ferreira</h5>

<span class="text-secondary">
Cliente desde 2023
</span>

</div>

</div>

<div class="stars">★★★★★</div>

<p>
Experiência de alto nível.
Ambiente moderno e organizado.
</p>

</div>

</div>

</div>

</div>

</div>

</section>

<!-- ================= CONTATO ================= -->

<section class="contato" id="contato">

<div class="contato-box">

<h2>Agende seu horário agora</h2>

<p>
Clique abaixo e fale diretamente
pelo WhatsApp.
</p>

<a href="https://api.whatsapp.com/send?phone=5544997306220&text=Olá%20Adrian,%20gostaria%20de%20agendar%20um%20horário!" 
   class="btn-principal" target="_blank">
Chamar no WhatsApp
</a>

</div>

</section>

<!-- ================= SCRIPT DE AGENDAMENTO ================= -->

<script>
document.getElementById('form-agendamento').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Limpar alertas anteriores
    document.getElementById('alerta-sucesso').style.display = 'none';
    document.getElementById('alerta-erro').style.display = 'none';
    
    // Desabilitar botão durante envio
    const btnSubmit = document.getElementById('btn-submit');
    const textoBotao = btnSubmit.innerText;
    btnSubmit.disabled = true;
    btnSubmit.innerText = 'Processando...';
    
    try {
        const formData = new FormData(this);
        
        const response = await fetch('processar_agendamento.php', {
            method: 'POST',
            body: formData
        });
        
        const resultado = await response.json();
        
        if (resultado.sucesso) {
            // Mostrar alerta de sucesso
            document.getElementById('alerta-msg-sucesso').innerText = resultado.mensagem;
            document.getElementById('alerta-sucesso').style.display = 'block';
            
            // Limpar formulário
            this.reset();
            
            // Scroll para o alerta
            document.getElementById('alerta-sucesso').scrollIntoView({ behavior: 'smooth' });
            
        } else {
            // Mostrar alerta de erro
            document.getElementById('alerta-msg-erro').innerText = resultado.mensagem;
            document.getElementById('alerta-erro').style.display = 'block';
        }
        
    } catch (erro) {
        console.error('Erro:', erro);
        document.getElementById('alerta-msg-erro').innerText = 'Erro: ' + erro.message;
        document.getElementById('alerta-erro').style.display = 'block';
    }
    
    // Reabilitar botão
    btnSubmit.disabled = false;
    btnSubmit.innerText = textoBotao;
});

// Definir data mínima como hoje
document.getElementById('agenda-date').min = new Date().toISOString().split('T')[0];
</script>

<!-- ================= AGENDAMENTOS (dados do banco) ================= -->

<section class="feedbacks" id="recentes">
<div class="container">

<div class="section-topo">
  <h2>Agendamentos</h2>
</div>

<div class="row g-4 justify-content-center">

<?php
// ── FOREACH — exibe agendamentos vindos do banco ──────────────────────────────
foreach ($agendamentosRecentes as $ag):

  // IF — define cor do badge conforme status
  if ($ag['status'] === 'confirmado') {
      $cor = 'success';
  } elseif ($ag['status'] === 'pendente') {
      $cor = 'warning text-dark';
  } else {
      $cor = 'danger';
  }

  $dataFormatada = date('d/m/Y H:i', strtotime($ag['data_hora']));
?>

<div class="col-md-4">
<div class="card feedback-card border-0 h-100">
<div class="card-body p-4 text-center">

  <h5><?php echo htmlspecialchars($ag['cliente_nome']); ?></h5>
  <p class="text-secondary"><?php echo htmlspecialchars($ag['servico_nome']); ?></p>
  <p style="font-size:.85rem; color:#aaa;"><?php echo $dataFormatada; ?></p>

  <!-- Badge Bootstrap — componente 2 -->
  <span class="badge bg-<?php echo $cor; ?>">
    <?php echo ucfirst($ag['status']); ?>
  </span>

</div>
</div>
</div>

<?php endforeach; ?>

</div>

<div class="section-topo" style="margin-top: 60px; text-align: center;">
  <h2>Agendamentos de Hoje</h2>
  <p style="color: #ccc; margin-top: 20px;">Não há nenhum agendamento para hoje.</p>
</div>

</div>
</section>

<?php include("footer.php"); ?>