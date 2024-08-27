<?php
$host = 'localhost';
$db = 'agendamento';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}
?>
<?php
// Dados de conexão com o banco de dados
$servername = "localhost"; // ou o nome do seu servidor de banco de dados
$username = "root"; // ou o nome de usuário do seu banco de dados
$password = ""; // senha do seu banco de dados
$dbname = "agendamento"; // nome do seu banco de dados

// Criar a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar a conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
?>
