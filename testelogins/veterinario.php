<?php
include('db.php');
session_start();

// Verificar se o usuário é um veterinário
if ($_SESSION['tipo'] != 'veterinario') {
    echo "Acesso negado!";
    exit();
}

// Verificar se o clinica_id está definido na sessão
if (!isset($_SESSION['clinica_id'])) {
    echo "Erro: O ID da clínica não foi encontrado!";
    exit();
}

$clinica_id = $_SESSION['clinica_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome_tutor = $_POST['nome_tutor'];
    $email_tutor = $_POST['email_tutor'];
    $nome_pet = $_POST['nome_pet'];
    $data_consulta = $_POST['data_consulta'];
    $hora_consulta = $_POST['hora_consulta'];
    $status = 'agendada';

    // Verificar se o tutor já existe
    $sql = "SELECT id FROM usuarios WHERE nome = ? AND email = ? AND tipo = 'tutor'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nome_tutor, $email_tutor]);
    $tutor = $stmt->fetch();

    if ($tutor) {
        $tutor_id = $tutor['id'];
    } else {
        echo "Tutor não encontrado! Por favor, adicione o tutor primeiro.";
        exit();
    }

    // Inserir a consulta
    $sql = "INSERT INTO consultas_marcadas (nome_animal, proprietario, data_consulta, hora_consulta, status) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nome_pet, $nome_tutor, $data_consulta, $hora_consulta, $status]);

    echo "Consulta marcada com sucesso!";
}

// Buscar todos os clientes associados à clínica do veterinário
$sql_clientes = "SELECT * FROM clientes WHERE clinica_id = ?";
$stmt_clientes = $pdo->prepare($sql_clientes);
$stmt_clientes->execute([$clinica_id]);
$clientes = $stmt_clientes->fetchAll(PDO::FETCH_ASSOC);

// Buscar todos os tutores associados à clínica do veterinário
$sql_tutores = "SELECT * FROM tutores WHERE clinica_id = ?";
$stmt_tutores = $pdo->prepare($sql_tutores);
$stmt_tutores->execute([$clinica_id]);
$tutores = $stmt_tutores->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área do Veterinário</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Área do Veterinário</h1>

    <!-- Tabela de Clientes da Clínica -->
    <h2>Lista de Clientes da Clínica</h2>
    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>Email</th>
                <th>Telefone</th>
                <th>Endereço</th>
                <th>Cidade</th>
                <th>Estado</th>
                <th>CEP</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clientes as $cliente): ?>
            <tr>
                <td><?php echo htmlspecialchars($cliente['nome']); ?></td>
                <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                <td><?php echo htmlspecialchars($cliente['telefone']); ?></td>
                <td><?php echo htmlspecialchars($cliente['endereco']); ?></td>
                <td><?php echo htmlspecialchars($cliente['cidade']); ?></td>
                <td><?php echo htmlspecialchars($cliente['estado']); ?></td>
                <td><?php echo htmlspecialchars($cliente['cep']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Tabela de Tutores da Clínica -->
    <h2>Lista de Tutores da Clínica</h2>
    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tutores as $tutor): ?>
            <tr>
                <td><?php echo htmlspecialchars($tutor['nome']); ?></td>
                <td><?php echo htmlspecialchars($tutor['email']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Formulário Único para Adicionar Tutor, Pet e Consulta -->
    <h2>Adicionar Pet e Consulta</h2>
    <form method="POST">
        <fieldset>
            <legend>Dados do Tutor</legend>
            <input type="text" name="nome_tutor" placeholder="Nome do Tutor" required>
            <input type="email" name="email_tutor" placeholder="Email do Tutor" required>
        </fieldset>

        <fieldset>
            <legend>Dados do Pet</legend>
            <input type="text" name="nome_pet" placeholder="Nome do Pet" required>
            <input type="text" name="raca_pet" placeholder="Raça do Pet">
        </fieldset>

        <fieldset>
            <legend>Dados da Consulta</legend>
            <input type="date" name="data_consulta" required>
            <input type="time" name="hora_consulta" required>
            <textarea name="descricao_consulta" placeholder="Descrição da Consulta" required></textarea>
        </fieldset>

        <button type="submit">Cadastrar</button>
    </form>
</body>
</html>
