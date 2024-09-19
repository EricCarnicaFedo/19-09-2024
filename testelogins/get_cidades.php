<?php
include('db.php');

$estado_id = isset($_GET['estado_id']) ? intval($_GET['estado_id']) : 0;

if ($estado_id > 0) {
    $sql = "SELECT id, nome FROM cidades WHERE estado_id = ? ORDER BY nome";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$estado_id]);
    $cidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($cidades);
}
?>
