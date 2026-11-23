<?php
require_once '../config/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $sql = "UPDATE cliente SET 
                    nome_cliente = :nome,
                    cpf_cliente = :cpf,
                    email_cliente = :email,
                    telefone_cliente = :tel,
                    data_nascimento = :nasc,
                    alergias = :alergias,
                    observacoes_medicas = :obs,
                    instagram_cliente = :insta
                WHERE id_cliente = :id";
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
            ':id'       => (int)$_POST['id_cliente']
        ]);
        setFlash('success', 'Dados do cliente atualizados!');
    } catch (PDOException $e) {
        setFlash('danger', 'Erro ao atualizar: ' . $e->getMessage());
    }
}
header('Location: ../pages/clientes.php');
exit;