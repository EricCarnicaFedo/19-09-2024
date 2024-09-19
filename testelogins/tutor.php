<?php
include('db.php');
session_start();

// Verificar se o usuário é um tutor
if ($_SESSION['tipo'] != 'tutor') {
    header("Location: login.php");
    exit();
}

$tutor_id = $_SESSION['usuario_id'];

// Inserção de pet e consulta, se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome_pet = $_POST['nome_pet'];
    $especie_pet = $_POST['especie_pet'];
    $descricao_consulta = $_POST['descricao_consulta'];
    $data_consulta = $_POST['data_consulta'];

    try {
        // Inserir o pet
        $sql = "INSERT INTO pets (nome, especie, tutor_id) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome_pet, $especie_pet, $tutor_id]);

        // Obter o ID do pet recém-inserido
        $pet_id = $pdo->lastInsertId();

        // Inserir a consulta
        $sql_consulta = "INSERT INTO consultas_marcadas (pet_id, descricao, data_consulta) VALUES (?, ?, ?)";
        $stmt_consulta = $pdo->prepare($sql_consulta);
        $stmt_consulta->execute([$pet_id, $descricao_consulta, $data_consulta]);

        echo "Pet e consulta cadastrados com sucesso!";
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }
}

// Selecionando os pets e suas consultas do tutor logado
$pets = $pdo->prepare("
    SELECT pets.id, pets.nome, pets.especie, consultas_marcadas.descricao, consultas_marcadas.data_consulta
    FROM pets
    LEFT JOIN consultas_marcadas ON pets.id = consultas_marcadas.pet_id
    WHERE pets.tutor_id = ?
");
$pets->execute([$tutor_id]);
$pets = $pets->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seus Pets e Consultas</title>
</head>
<body>
    <h1>Seus Pets e Consultas</h1>

    <ul>
        <?php foreach ($pets as $pet): ?>
            <li>
                <strong><?= htmlspecialchars($pet['nome']) ?> (<?= htmlspecialchars($pet['especie']) ?>)</strong><br>
                <?php if ($pet['descricao']): ?>
                    Consulta: <?= htmlspecialchars($pet['descricao']) ?> em <?= htmlspecialchars($pet['data_consulta']) ?>
                <?php else: ?>
                    Nenhuma consulta registrada
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <h2>Cadastrar Novo Pet e Consulta</h2>
    <form method="POST">
        <fieldset>
            <legend>Dados do Pet</legend>
            <input type="text" name="nome_pet" placeholder="Nome do Pet" required>
            <input type="text" name="especie_pet" placeholder="Espécie do Pet" required>
        </fieldset>

        <fieldset>
            <legend>Dados da Consulta</legend>
            <textarea name="descricao_consulta" placeholder="Descrição da Consulta" required></textarea>
            <input type="date" name="data_consulta" required>
        </fieldset>

        <button type="submit">Cadastrar</button>
    </form>
</body>
</html>
