<?php
include('db.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Consulta ao banco de dados para verificar o email
    $sql = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    // Verifica se o usuário existe e se a senha está correta
    if ($usuario && password_verify($senha, $usuario['senha'])) {
        // Armazena as informações do usuário na sessão
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['tipo'] = $usuario['tipo'];
        
        // Armazena o clinica_id na sessão se o usuário for veterinário
        if ($usuario['tipo'] == 'veterinario') {
            $_SESSION['clinica_id'] = $usuario['clinica_id']; // Adicione esta linha
        }

        // Redireciona o usuário com base no tipo de conta
        if ($usuario['tipo'] == 'tutor') {
            header("Location: tutor.php");
        } elseif ($usuario['tipo'] == 'veterinario') {
            header("Location: veterinario.php");
        }
        exit();
    } else {
        // Mensagem de erro para login inválido
        echo "Email ou senha incorretos!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="form-container login-form">
        <form method="POST" action="login.php">
            <h1>Login</h1>
            
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>

            <label for="senha">Senha:</label>
            <input type="password" name="senha" id="senha" required>

            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>
