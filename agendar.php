<?php
session_start();
include 'db.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_usuario = $_SESSION['user_id'];
    $id_servico = $_POST['id_servico'];
    $data_agendamento = $_POST['data_agendamento'];
    $horario = $_POST['horario'];

    // Verifica se a data e horário já estão ocupados
    $stmt = $pdo->prepare("SELECT * FROM agendamentos WHERE data_agendamento = ? AND horario = ? AND status = 'ocupado'");
    $stmt->execute([$data_agendamento, $horario]);
    $agendamentoExistente = $stmt->fetch();

    if ($agendamentoExistente) {
        // Caso o horário já esteja ocupado
        $error = "O horário selecionado já está ocupado. Por favor, escolha outro.";
        header("Location: index.php?error=" . urlencode($error));
        exit();
    } else {
        // Insere o novo agendamento
        $stmt = $pdo->prepare("INSERT INTO agendamentos (id_usuario, id_servico, data_agendamento, horario, status) VALUES (?, ?, ?, ?, 'ocupado')");
        $stmt->execute([$id_usuario, $id_servico, $data_agendamento, $horario]);

        $success = "Agendamento realizado com sucesso!";
        header("Location: index.php?success=" . urlencode($success));
        exit();
    }
} else {
    // Redireciona para o dashboard caso o acesso a este arquivo não seja via POST
    header('Location: index.php');
    exit();
}
?>