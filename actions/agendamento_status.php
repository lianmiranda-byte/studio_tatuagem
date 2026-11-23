<?php
require_once '../config/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = (int)$_POST['id_agendamento'];
    $status = $_POST['status_agendamento'];
    $pgto   = !empty($_POST['id_pagamento']) ? (int)$_POST['id_pagamento'] : null;

    $stmt = $pdo->prepare("UPDATE agendamento SET status_agendamento = ?, id_pagamento = ? WHERE id_agendamento = ?");
    $stmt->execute([$status, $pgto, $id]);

    setFlash('success', "Status alterado para: $status");
    header("Location: ../pages/sessao_detalhes.php?id=$id");
    exit;
}