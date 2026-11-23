<?php
require_once '../config/conexao.php';

if (isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM cliente WHERE id_cliente = ?");
        $stmt->execute([(int)$_GET['id']]);
        setFlash('success', 'Cliente excluído.');
    } catch (PDOException $e) {
        setFlash('danger', 'Não é possível excluir: este cliente possui agendamentos vinculados.');
    }
}
header('Location: ../pages/clientes.php');
exit;