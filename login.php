<?php
session_start();
include 'db.php'; // Inclua a conexão com o banco de dados

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $stmt = $conn->prepare("SELECT id, tipo_usuario FROM usuarios WHERE email = ? AND senha = ?");
    $stmt->bind_param("ss", $email, $senha);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['tipo_usuario'] = $user['tipo_usuario'];

        if ($user['tipo_usuario'] == 'admin') {
            header('Location: admin_panel.php');
        } else {
            header('Location: login.php');
        }
        exit();
    } else {
        $error = "Email ou senha inválidos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/login.css">
    <title>Login</title>
</head>
<body>
    <div class="login-container">
        <div class="login-image">
            <img src="imagens/login.png" alt="Imagem de Login">
        </div>
        <div class="login-form">
            <form method="post" action="">
                <h1>Seja Bem Vindo (a)</h1>
                <h2>Realize o Login!</h2>
                <?php if (isset($error)): ?>
                    <div class="message error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="senha" placeholder="Senha" required>
                <button type="submit">Entrar</button>
            </form>
            <p>Não tem uma conta? <a href="register.php">Registre-se aqui</a></p>
        </div>
    </div>
</body>
</html>