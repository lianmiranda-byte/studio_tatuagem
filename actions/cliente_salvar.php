<?php
require_once '../config/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $sql = "INSERT INTO cliente (nome_cliente, cpf_cliente, email_cliente, telefone_cliente, data_nascimento, alergias, observacoes_medicas, instagram_cliente)
                VALUES (:nome, :cpf, :email, :tel, :nasc, :alergias, :obs, :insta)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nome'     => $_POST['nome_cliente'],
            ':cpf'      => preg_replace('/\D/', '', $_POST['cpf_cliente']),
            ':email'    => !empty($_POST['email_cliente']) ? $_POST['email_cliente'] : null,
            ':tel'      => $_POST['telefone_cliente'],
            ':nasc'     => $_POST['data_nascimento'],
            ':alergias' => $_POST['alergias'] ?: 'Nenhuma',
            ':obs'      => $_POST['observacoes_medicas'] ?: 'Sem observações',
            ':insta'    => $_POST['instagram_cliente'] ?: 'Não informado',
        ]);
        setFlash('success', 'Cliente cadastrado com sucesso!');
    } catch (PDOException $e) {
        setFlash('danger', 'Erro ao cadastrar (verifique se CPF ou E-mail já existem): ' . $e->getMessage());
    }
}
header('Location: ../pages/clientes.php');
exit;