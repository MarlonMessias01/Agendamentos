<?php
session_start();
include 'db.php'; // Inclui a conexão com o banco de dados

// Verifica se o usuário está logado e é administrador
if (!isset($_SESSION['user_id']) || $_SESSION['tipo_usuario'] != 'admin') {
    header('Location: login.php'); // Redireciona para a página de login se o usuário não for admin
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/style-panel.css"> <!-- Adicione o estilo para esta página -->
    <title>Painel Administrativo</title>
</head>
<body>
    <div class="container">
        <h2>Bem-vindo, Administrador!</h2>
        <p>Escolha a ação que deseja realizar:</p>
        
        <div class="button-group">
            <a href="index.php" class="btn">Ir para Agendamentos</a>
            <a href="admin.php" class="btn">Gerenciar Sistema</a>
        </div>
    </div>
</body>
</html>
