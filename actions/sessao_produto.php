<?php
require_once '../config/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_agendamento = (int) $_POST['id_agendamento'];
    $id_produto     = (int) $_POST['id_produto'];
    $quantidade     = (int) $_POST['quantidade_utilizada'];
    $valor_cobrado  = (float) $_POST['valor_cobrado']; // 0.00 para descartáveis, valor > 0 para joias vendidas

    try {
        $pdo->beginTransaction();

        // 1. Verifica se há estoque suficiente
        $stmtCheck = $pdo->prepare("SELECT nome_produto, quantidade_estoque FROM produto WHERE id_produto = ? FOR UPDATE");
        $stmtCheck->execute([$id_produto]);
        $produto = $stmtCheck->fetch();

        if (!$produto || $produto['quantidade_estoque'] < $quantidade) {
            throw new Exception("Estoque insuficiente para o produto selecionado.");
        }

        // 2. Vincula o produto ao agendamento
        $stmtInsert = $pdo->prepare("
            INSERT INTO agendamento_produto (id_agendamento, id_produto, quantidade_utilizada, valor_cobrado)
            VALUES (?, ?, ?, ?)
        ");
        $stmtInsert->execute([$id_agendamento, $id_produto, $quantidade, $valor_cobrado]);

        // 3. Desconta da tabela 'produto'
        $stmtUpdate = $pdo->prepare("UPDATE produto SET quantidade_estoque = quantidade_estoque - ? WHERE id_produto = ?");
        $stmtUpdate->execute([$quantidade, $id_produto]);

        // 4. Registra no histórico de auditoria 'estoque'
        $stmtLog = $pdo->prepare("
            INSERT INTO estoque (id_produto, tipo_movimentacao, quantidade, observacao)
            VALUES (?, 'SAIDA', ?, ?)
        ");
        $stmtLog->execute([$id_produto, $quantidade, "Uso no agendamento #$id_agendamento"]);

        $pdo->commit();
        setFlash('success', "Insumo lançado e estoque atualizado com sucesso!");
    } catch (Exception $e) {
        $pdo->rollBack();
        setFlash('danger', "Erro: " . $e->getMessage());
    }

    header("Location: ../pages/sessao_detalhes.php?id=" . $id_agendamento);
    exit;
}