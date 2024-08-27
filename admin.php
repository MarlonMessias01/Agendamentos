<?php
session_start();
include 'db.php'; // Inclua a conexão com o banco de dados

// Verifica se o usuário está logado e é administrador
if (!isset($_SESSION['user_id']) || $_SESSION['tipo_usuario'] != 'admin') {
    header('Location: index.php');
    exit();
}

// Funções de Manipulação do Banco de Dados
function adicionarServico($conn, $nome_servico, $valor) {
    $query = "INSERT INTO servicos (nome_servico, valor) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sd', $nome_servico, $valor);
    return $stmt->execute();
}

function atualizarServico($conn, $id_servico, $nome_servico, $valor) {
    $query = "UPDATE servicos SET nome_servico = ?, valor = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sdi', $nome_servico, $valor, $id_servico);
    return $stmt->execute();
}

function excluirServico($conn, $id_servico) {
    $query = "DELETE FROM servicos WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id_servico);
    return $stmt->execute();
}

function excluirAgendamento($conn, $id_agendamento) {
    $query = "DELETE FROM agendamentos WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id_agendamento);
    return $stmt->execute();
}

function mudarStatusUsuario($conn, $id_usuario, $ativo) {
    $query = "UPDATE usuarios SET ativo = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $ativo, $id_usuario);
    return $stmt->execute();
}

// Processamento dos Formulários
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['adicionar_servico'])) {
        $nome_servico = $_POST['nome_servico'];
        $valor = $_POST['valor'];
        if (adicionarServico($conn, $nome_servico, $valor)) {
            $success = "Serviço adicionado com sucesso!";
        } else {
            $error = "Erro ao adicionar o serviço.";
        }
    } elseif (isset($_POST['atualizar_servico'])) {
        $id_servico = $_POST['id_servico'];
        $nome_servico = $_POST['nome_servico'];
        $valor = $_POST['valor'];
        if (atualizarServico($conn, $id_servico, $nome_servico, $valor)) {
            $success = "Serviço atualizado com sucesso!";
        } else {
            $error = "Erro ao atualizar o serviço.";
        }
    } elseif (isset($_POST['excluir_servico'])) {
        $id_servico = $_POST['id_servico'];
        if (excluirServico($conn, $id_servico)) {
            $success = "Serviço excluído com sucesso!";
        } else {
            $error = "Erro ao excluir o serviço.";
        }
    } elseif (isset($_POST['excluir_agendamento'])) {
        $id_agendamento = $_POST['id_agendamento'];
        if (excluirAgendamento($conn, $id_agendamento)) {
            $success = "Agendamento excluído com sucesso!";
        } else {
            $error = "Erro ao excluir o agendamento.";
        }
    } elseif (isset($_POST['mudar_usuario_status'])) {
        $id_usuario = $_POST['id_usuario'];
        $status = $_POST['status'];
        if (mudarStatusUsuario($conn, $id_usuario, $status)) {
            $success = "Status do usuário alterado com sucesso!";
        } else {
            $error = "Erro ao alterar o status do usuário.";
        }
    }
}

// Consultas para Listar Serviços, Agendamentos e Usuários
$queryServicos = "SELECT * FROM servicos";
$resultServicos = $conn->query($queryServicos);

$queryAgendamentos = "SELECT * FROM agendamentos";
$resultAgendamentos = $conn->query($queryAgendamentos);

$queryUsuarios = "SELECT * FROM usuarios";
$resultUsuarios = $conn->query($queryUsuarios);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/style-admin.css">
    <title>Painel Administrativo</title>
</head>
<body>
    <div class="container">
        <a href="logout.php" class="logout-button">Sair</a>
        <a href="index.php" class="back-button">Voltar ao Dashboard</a> <!-- Botão Voltar ao Dashboard -->
        <h2>Painel Administrativo</h2>

        <?php if (isset($success)): ?>
            <div class="message success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Formulário para adicionar um novo serviço -->
        <h3>Adicionar Novo Serviço</h3>
        <form method="post" action="admin.php">
            <input type="text" name="nome_servico" placeholder="Nome do Serviço" required>
            <input type="number" step="0.01" name="valor" placeholder="Valor" required>
            <button type="submit" name="adicionar_servico">Adicionar Serviço</button>
        </form>

        <!-- Formulário para atualizar um serviço existente -->
        <h3>Atualizar Serviço</h3>
        <form method="post" action="admin.php">
            <select name="id_servico" required>
                <option value="">Selecione o Serviço</option>
                <?php while ($servico = $resultServicos->fetch_assoc()): ?>
                    <option value="<?php echo $servico['id']; ?>"><?php echo htmlspecialchars($servico['nome_servico']); ?></option>
                <?php endwhile; ?>
            </select>
            <input type="text" name="nome_servico" placeholder="Novo Nome do Serviço" required>
            <input type="number" step="0.01" name="valor" placeholder="Novo Valor" required>
            <button type="submit" name="atualizar_servico">Atualizar Serviço</button>
        </form>

        <!-- Formulário para excluir um serviço -->
        <h3>Excluir Serviço</h3>
        <form method="post" action="admin.php">
            <select name="id_servico" required>
                <option value="">Selecione o Serviço</option>
                <?php while ($servico = $resultServicos->fetch_assoc()): ?>
                    <option value="<?php echo $servico['id']; ?>"><?php echo htmlspecialchars($servico['nome_servico']); ?></option>
                <?php endwhile; ?>
            </select>
            <button type="submit" name="excluir_servico">Excluir Serviço</button>
        </form>

        <!-- Formulário para excluir um agendamento -->
        <h3>Excluir Agendamento</h3>
        <form method="post" action="admin.php">
            <select name="id_agendamento" required>
                <option value="">Selecione o Agendamento</option>
                <?php while ($agendamento = $resultAgendamentos->fetch_assoc()): ?>
                    <option value="<?php echo $agendamento['id']; ?>">
                        <?php echo "Data: " . htmlspecialchars($agendamento['data_agendamento']) . " - Hora: " . htmlspecialchars($agendamento['horario']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit" name="excluir_agendamento">Excluir Agendamento</button>
        </form>

        <!-- Formulário para mudar o status do usuário -->
        <h3>Mudar Status do Usuário</h3>
        <form method="post" action="admin.php">
            <select name="id_usuario" required>
                <option value="">Selecione o Usuário</option>
                <?php while ($usuario = $resultUsuarios->fetch_assoc()): ?>
                    <option value="<?php echo $usuario['id']; ?>"><?php echo htmlspecialchars($usuario['nome']); ?></option>
                <?php endwhile; ?>
            </select>
            <select name="status" required>
                <option value="">Selecione o Status</option>
                <option value="1">Ativo</option>
                <option value="0">Inativo</option>
            </select>
            <button type="submit" name="mudar_usuario_status">Alterar Status</button>
        </form>
    </div>
</body>
</html>