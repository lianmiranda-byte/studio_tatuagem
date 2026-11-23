<?php
require_once '../config/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idAg          = (int)$_POST['id_agendamento'];
    $justificativa = trim($_POST['justificativa_cancelamento']);

    $stmt = $pdo->prepare("SELECT * FROM agendamento WHERE id_agendamento = ?");
    $stmt->execute([$idAg]);
    $ag = $stmt->fetch();

    if ($ag) {
        // Verifica se faltam menos de 24 horas para o atendimento (RN05)
        $horasRestantes = (strtotime($ag['data_hora']) - time()) / 3600;
        $taxa = ($horasRestantes < 24) ? ($ag['valor_sessao'] * 0.10) : 0.00;

        $upd = $pdo->prepare("
            UPDATE agendamento 
            SET status_agendamento = 'Cancelado', 
                justificativa_cancelamento = ?, 
                taxa_cancelamento = ?
            WHERE id_agendamento = ?
        ");
        $upd->execute([$justificativa, $taxa, $idAg]);

        if ($taxa > 0) {
            setFlash('warning', "Agendamento cancelado. Como faltavam menos de 24h, foi aplicada taxa de 10% (R$ " . number_format($taxa, 2, ',', '.') . ").");
        } else {
            setFlash('info', "Agendamento cancelado com justificativa registrada.");
        }
    }
}

$destino = isset($_SESSION['cliente_id']) ? '../pages/cliente_painel.php' : '../pages/agendamentos.php';
header("Location: $destino");
exit;