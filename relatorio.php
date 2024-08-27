<?php
session_start();
include 'db.php'; // Inclua a conexão com o banco de dados

// Verifica se o usuário está logado e é administrador
if (!isset($_SESSION['user_id']) || $_SESSION['tipo_usuario'] != 'admin') {
    header('Location: index.php');
    exit();
}

// Define a data inicial e final ou utiliza a data atual como padrão
$dataInicial = isset($_POST['data_inicial']) ? $_POST['data_inicial'] : date('Y-m-d');
$dataFinal = isset($_POST['data_final']) ? $_POST['data_final'] : date('Y-m-d');

// Consulta para obter os dados do intervalo
$query = "SELECT s.nome_servico, COUNT(*) as total_agendamentos, SUM(s.valor) as valor_total
          FROM agendamentos a
          JOIN servicos s ON a.id_servico = s.id
          WHERE a.data_agendamento BETWEEN ? AND ?
          GROUP BY s.nome_servico";
$stmt = $conn->prepare($query);
$stmt->bind_param('ss', $dataInicial, $dataFinal);
$stmt->execute();
$result = $stmt->get_result();

$relatorio = [];
$valorTotalGeral = 0;
while ($row = $result->fetch_assoc()) {
    $relatorio[] = $row;
    $valorTotalGeral += $row['valor_total'];
}

// Consulta para obter os detalhes dos agendamentos para o intervalo
$queryDetalhes = "SELECT a.data_agendamento, a.horario, u.nome AS nome_usuario, s.nome_servico 
                  FROM agendamentos a
                  JOIN usuarios u ON a.id_usuario = u.id
                  JOIN servicos s ON a.id_servico = s.id
                  WHERE a.data_agendamento BETWEEN ? AND ?
                  ORDER BY a.data_agendamento, a.horario";
$stmtDetalhes = $conn->prepare($queryDetalhes);
$stmtDetalhes->bind_param('ss', $dataInicial, $dataFinal);
$stmtDetalhes->execute();
$resultDetalhes = $stmtDetalhes->get_result();

$detalhesAgendamentos = [];
while ($row = $resultDetalhes->fetch_assoc()) {
    $detalhesAgendamentos[] = $row;
}

$stmt->close();
$stmtDetalhes->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/style-rel.css">
    <title>Relatório de Agendamentos</title>
</head>
<body>
    <div class="container">
        <a href="logout.php" class="logout-button">Sair</a>
        <a href="index.php" class="back-button">Voltar</a> <!-- Botão Voltar ao Dashboard -->
        
        <div class="main-content">
            <h2>Relatório de Agendamentos</h2>

            <!-- Formulário para selecionar o intervalo de datas -->
            <form method="post" action="relatorio.php">
                <label for="data_inicial">Data Inicial:</label>
                <input type="date" id="data_inicial" name="data_inicial" value="<?php echo htmlspecialchars($dataInicial); ?>" required>
                <label for="data_final">Data Final:</label>
                <input type="date" id="data_final" name="data_final" value="<?php echo htmlspecialchars($dataFinal); ?>" required>
                <button type="submit">Atualizar</button>
            </form>

            <?php if (!empty($relatorio)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Serviço</th>
                            <th>Total de Agendamentos</th>
                            <th>Valor Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($relatorio as $dados): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($dados['nome_servico']); ?></td>
                                <td><?php echo htmlspecialchars($dados['total_agendamentos']); ?></td>
                                <td>R$ <?php echo number_format($dados['valor_total'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="2"><strong>Total Geral:</strong></td>
                            <td><strong>R$ <?php echo number_format($valorTotalGeral, 2, ',', '.'); ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Nenhum agendamento encontrado para o intervalo selecionado.</p>
            <?php endif; ?>
        </div>

        <!-- Agendamentos Detalhados em um aside -->
        <aside class="sidebar">
            <h3>Detalhes dos Agendamentos</h3>
            <?php if (!empty($detalhesAgendamentos)): ?>
                <ul>
                    <?php foreach ($detalhesAgendamentos as $detalhe): ?>
                        <li>
                            <span><?php echo "Data: " . date('d/m/Y', strtotime($detalhe['data_agendamento'])); ?></span>
                            <span><?php echo "Hora: " . htmlspecialchars($detalhe['horario']); ?></span>
                            <span><?php echo "Cliente: " . htmlspecialchars($detalhe['nome_usuario']); ?></span>
                            <span><?php echo "Serviço: " . htmlspecialchars($detalhe['nome_servico']); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p style="text-align: center;">Nenhum agendamento encontrado para o intervalo selecionado.</p>
            <?php endif; ?>
        </aside>
    </div>
</body>
</html>