<?php
require_once '../config/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    try {
        if ($acao === 'novo_produto') {
            $stmt = $pdo->prepare("INSERT INTO produto (nome_produto, marca, categoria, preco_unitario, quantidade_estoque, estoque_minimo) VALUES (?, ?, ?, ?, 0, ?)");
            $stmt->execute([
                $_POST['nome_produto'],
                $_POST['marca'],
                $_POST['categoria'],
                $_POST['preco_unitario'],
                $_POST['estoque_minimo']
            ]);
            setFlash('success', 'Novo produto cadastrado com saldo 0!');
        } 
        elseif ($acao === 'movimentar') {
            $pdo->beginTransaction();

            $id_produto = (int)$_POST['id_produto'];
            $tipo       = $_POST['tipo_movimentacao'];
            $qtd        = (int)$_POST['quantidade'];
            $obs        = $_POST['observacao'];

            if ($tipo === 'SAIDA') {
                $check = $pdo->prepare("SELECT quantidade_estoque FROM produto WHERE id_produto = ? FOR UPDATE");
                $check->execute([$id_produto]);
                if ($check->fetchColumn() < $qtd) {
                    throw new Exception("Saldo insuficiente para dar saída!");
                }
                $pdo->prepare("UPDATE produto SET quantidade_estoque = quantidade_estoque - ? WHERE id_produto = ?")->execute([$qtd, $id_produto]);
            } else {
                $pdo->prepare("UPDATE produto SET quantidade_estoque = quantidade_estoque + ? WHERE id_produto = ?")->execute([$qtd, $id_produto]);
            }

            $pdo->prepare("INSERT INTO estoque (id_produto, tipo_movimentacao, quantidade, observacao) VALUES (?, ?, ?, ?)")
                ->execute([$id_produto, $tipo, $qtd, $obs]);

            $pdo->commit();
            setFlash('success', 'Movimentação registrada com sucesso!');
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        setFlash('danger', $e->getMessage());
    }
}
header('Location: ../pages/estoque.php');
exit;