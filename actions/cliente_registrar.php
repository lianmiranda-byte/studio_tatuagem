<?php
require_once '../config/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $cpf   = preg_replace('/\D/', '', $_POST['cpf_cliente']);
        $email = trim($_POST['email_cliente']);
        $senha = password_hash($_POST['senha_cliente'], PASSWORD_DEFAULT);

        if (strlen($cpf) !== 11) {
            throw new Exception("O CPF deve conter exatamente 11 dígitos.");
        }

        // Verifica se o cliente já foi pré-cadastrado pelo estúdio sem senha
        $stmtBusca = $pdo->prepare("SELECT id_cliente, senha_cliente FROM cliente WHERE cpf_cliente = ? OR email_cliente = ?");
        $stmtBusca->execute([$cpf, $email]);
        $existente = $stmtBusca->fetch();

        if ($existente) {
            if (!empty($existente['senha_cliente'])) {
                throw new Exception("Já existe uma conta cadastrada com este CPF ou E-mail. Faça login.");
            }
            // Ativa a conta do cliente pré-cadastrado
            $stmtUpd = $pdo->prepare("
                UPDATE cliente SET 
                    nome_cliente = ?, email_cliente = ?, senha_cliente = ?, telefone_cliente = ?, 
                    data_nascimento = ?, alergias = ?, observacoes_medicas = ?, instagram_cliente = ?, contato_emergencia = ?
                WHERE id_cliente = ?
            ");
            $stmtUpd->execute([
                $_POST['nome_cliente'], $email, $senha, $_POST['telefone_cliente'],
                $_POST['data_nascimento'], $_POST['alergias'] ?: 'Nenhuma',
                $_POST['observacoes_medicas'] ?: 'Sem observações',
                $_POST['instagram_cliente'] ?: 'Não informado',
                $_POST['contato_emergencia'] ?: null,
                $existente['id_cliente']
            ]);
            $idCliente = $existente['id_cliente'];
        } else {
            $stmtIns = $pdo->prepare("
                INSERT INTO cliente (nome_cliente, cpf_cliente, email_cliente, senha_cliente, telefone_cliente, data_nascimento, alergias, observacoes_medicas, instagram_cliente, contato_emergencia)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtIns->execute([
                $_POST['nome_cliente'], $cpf, $email, $senha, $_POST['telefone_cliente'],
                $_POST['data_nascimento'], $_POST['alergias'] ?: 'Nenhuma',
                $_POST['observacoes_medicas'] ?: 'Sem observações',
                $_POST['instagram_cliente'] ?: 'Não informado',
                $_POST['contato_emergencia'] ?: null
            ]);
            $idCliente = $pdo->lastInsertId();
        }

        // Loga o cliente automaticamente após o cadastro
        $_SESSION['cliente_id']   = $idCliente;
        $_SESSION['cliente_nome'] = $_POST['nome_cliente'];
        setFlash('success', 'Bem-vindo(a) ao estúdio Verso Sobre Pele!');
        header('Location: ../pages/cliente_painel.php');
        exit;

    } catch (Exception $e) {
        setFlash('danger', $e->getMessage());
        header('Location: ../cadastro.php');
        exit;
    }
}