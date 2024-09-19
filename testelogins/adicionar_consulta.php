<?php
include('db.php');
session_start();

if ($_SESSION['tipo'] != 'veterinario') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tutor_id = $_POST['tutor_id'];
    $pet_id = $_POST['pet_id'];
    $descricao = $_POST['descricao'];
    $veterinario_id = $_SESSION['usuario_id'];

    // Inserindo a consulta no banco de dados
    $sql = "INSERT INTO consultas (descricao, data, veterinario_id, pet_id) VALUES (?, NOW(), ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$descricao, $veterinario_id, $pet_id]);

    echo "Consulta adicionada com sucesso!";
    header("Location: veterinario.php");
    exit();
}
?>
