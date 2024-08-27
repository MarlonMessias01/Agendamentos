<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Verifique o tipo de usuário
$tipoUsuario = $_SESSION['tipo_usuario'] ?? '';

// Função para gerar horários de 5 em 5 minutos entre 09:00 e 18:00
function gerarHorariosDisponiveis($inicio, $fim, $intervalo) {
    $horarios = [];
    $horaAtual = strtotime($inicio);
    $horaFim = strtotime($fim);

    while ($horaAtual <= $horaFim) {
        $horarios[] = date('H:i', $horaAtual);
        $horaAtual = strtotime("+{$intervalo} minutes", $horaAtual);
    }

    return $horarios;
}

// Filtrar horários passados se a data de agendamento for a data atual
$dataAtual = date('Y-m-d');
if (isset($_POST['data_agendamento']) && $_POST['data_agendamento'] == $dataAtual) {
    $horaAtual = date('H:i');
    $horariosDisponiveis = array_filter($horariosDisponiveis, function($horario) use ($horaAtual) {
        return $horario > $horaAtual;
    });
}

// Gerando os horários
$horariosDisponiveis = gerarHorariosDisponiveis('09:00', '18:00', 5);

// Exibir serviços
$stmtServicos = $pdo->query("SELECT * FROM servicos");
$servicos = $stmtServicos->fetchAll();

// Exibir agendamentos ocupados
$stmtAgendamentos = $pdo->prepare("SELECT * FROM agendamentos WHERE status = 'ocupado' AND data_agendamento = ?");
$stmtAgendamentos->execute([$_POST['data_agendamento'] ?? $dataAtual]);
$agendamentosOcupados = $stmtAgendamentos->fetchAll(PDO::FETCH_ASSOC);

// Convertendo agendamentos ocupados para um array de horários
$horariosOcupados = [];
foreach ($agendamentosOcupados as $agendamento) {
    $horariosOcupados[] = $agendamento['horario'];
}

// Filtrando horários disponíveis
$horariosDisponiveis = array_diff($horariosDisponiveis, $horariosOcupados);

// Mensagens de sucesso ou erro
$success = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';

// Função para formatar a data no formato brasileiro
function formatarData($data) {
    return date('d/m/Y', strtotime($data));
}

// Processar agendamento
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_servico'], $_POST['data_agendamento'], $_POST['horario'])) {
    $idServico = $_POST['id_servico'];
    $dataAgendamento = $_POST['data_agendamento'];
    $horario = $_POST['horario'];

    // Verificar se o horário já está ocupado
    $stmtVerificaHorario = $pdo->prepare("SELECT * FROM agendamentos WHERE data_agendamento = ? AND horario = ? AND status = 'ocupado'");
    $stmtVerificaHorario->execute([$dataAgendamento, $horario]);
    
    if ($stmtVerificaHorario->rowCount() > 0) {
        $error = "Horário já ocupado!";
    } else {
        // Inserir novo agendamento
        $stmtInserir = $pdo->prepare("INSERT INTO agendamentos (id_usuario, id_servico, data_agendamento, horario, status) VALUES (?, ?, ?, ?, 'ocupado')");
        if ($stmtInserir->execute([$user_id, $idServico, $dataAgendamento, $horario])) {
            $success = "Agendamento realizado com sucesso!";
        } else {
            $error = "Erro ao agendar o serviço.";
        }

        // Atualizar lista de horários disponíveis
        $horariosOcupados[] = $horario; // Adiciona o novo horário ocupado à lista
        $horariosDisponiveis = array_diff(gerarHorariosDisponiveis('09:00', '18:00', 5), $horariosOcupados);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/style.css">
    <title>Página Principal</title>
</head>
<body>
    <div class="container">
        <div class="main-content">
            <h2>Painel de Agendamentos</h2>
            <a href="logout.php" class="btn sair">Sair</a>
            <a href="admin_painel.php" class="btn voltar">Voltar</a>

            <?php if ($success): ?>
                <div class="message success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($tipoUsuario === 'admin'): ?>
                <a href="relatorio.php" class="btn">Gerar Relatório</a>
            <?php endif; ?>

            <h3>Serviços Disponíveis</h3>
            <ul>
                <?php foreach ($servicos as $servico): ?>
                    <li>
                        <span><?php echo $servico['nome_servico']; ?></span>
                        <span>R$ <?php echo number_format($servico['valor'], 2, ',', '.'); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>

            <h3>Agendar Serviço</h3>
            <form method="post" action="">
                <label>Serviço:</label>
                <select name="id_servico" multiple required>
                    <?php foreach ($servicos as $servico): ?>
                        <option value="<?php echo $servico['id']; ?>"><?php echo $servico['nome_servico']; ?></option>
                    <?php endforeach; ?>
                </select>
                <label>Data:</label>
                <input type="date" name="data_agendamento" value="<?php echo $dataAtual; ?>" required>
                <label>Horário:</label>
                <select name="horario" required>
                    <?php foreach ($horariosDisponiveis as $horario): ?>
                        <option value="<?php echo $horario; ?>"><?php echo $horario; ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Agendar</button>
            </form>
        </div>

        <!-- Agendamentos Ocupados em um aside -->
        <aside class="sidebar">
            <h3>Horarios Ocupados</h3>
            <ul>
                <?php if ($agendamentosOcupados): ?>
                    <?php foreach ($agendamentosOcupados as $agendamento): ?>
                        <li>
                            <span class="data"><?php echo "Data: " . formatarData($agendamento['data_agendamento']); ?></span>
                            <span class="horario"><?php echo "Horário: " . $agendamento['horario']; ?></span>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center;">Nenhum agendamento ocupado no momento.</p>
                <?php endif; ?>
            </ul>
        </aside>
    </div>
</body>
</html>