<?php
require_once 'config/conexao.php';

if (isset($_SESSION['usuario_id'])) { header('Location: index.php'); exit; }
if (isset($_SESSION['cliente_id'])) { header('Location: pages/cliente_painel.php'); exit; }

// Cria Gerente padrão caso não exista
if ($pdo->query("SELECT COUNT(*) FROM usuario")->fetchColumn() == 0) {
    $hash = password_hash('123456', PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO usuario (nome, email, senha, nivel) VALUES ('Gerente Verso Sobre Pele', 'admin@studio.com', ?, 'Gerente')")->execute([$hash]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    // 1. Tenta autenticar como Gerente ou Funcionário
    $stmtUser = $pdo->prepare("SELECT * FROM usuario WHERE email = ?");
    $stmtUser->execute([$email]);
    $user = $stmtUser->fetch();

    if ($user && password_verify($senha, $user['senha'])) {
        $_SESSION['usuario_id']    = $user['id_usuario'];
        $_SESSION['usuario_nome']  = $user['nome'];
        $_SESSION['usuario_nivel'] = $user['nivel'];
        header('Location: index.php');
        exit;
    }

    // 2. Tenta autenticar como Cliente
    $stmtCli = $pdo->prepare("SELECT * FROM cliente WHERE email_cliente = ?");
    $stmtCli->execute([$email]);
    $cli = $stmtCli->fetch();

    if ($cli && !empty($cli['senha_cliente']) && password_verify($senha, $cli['senha_cliente'])) {
        $_SESSION['cliente_id']   = $cli['id_cliente'];
        $_SESSION['cliente_nome'] = $cli['nome_cliente'];
        header('Location: pages/cliente_painel.php');
        exit;
    }

    $erro = "E-mail ou senha inválidos.";
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Verso Sobre Pele</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/studio_tatuagem/assets/css/style.css" rel="stylesheet">
</head>
<body class="login-page d-flex align-items-center justify-content-center vh-100">
    <div style="width: 100%; max-width: 440px;" class="px-3">
        
        <!-- Abas Login / Cadastro -->
        <ul class="nav nav-pills nav-justified mb-3 bg-dark p-1 rounded-3 border border-secondary">
            <li class="nav-item">
                <a class="nav-link active bg-primary" href="login.php"><i class="bi bi-box-arrow-in-right"></i> Entrar</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-light" href="cadastro.php"><i class="bi bi-person-plus"></i> Cadastro Cliente</a>
            </li>
        </ul>

        <div class="card login-card p-4">
            <div class="text-center mb-4">
                <i class="bi bi-droplet-half text-danger fs-1"></i>
                <h4 class="mt-2 fw-bold">VERSO SOBRE PELE</h4>
                <small class="text-secondary">Tatuagem • Body Piercing • Estética</small>
            </div>

            <?php if (isset($erro)): ?>
                <div class="alert alert-danger py-2"><?= $erro ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-4">
                    <label class="form-label">Senha</label>
                    <input type="password" name="senha" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-danger w-100 fw-bold">Acessar Conta</button>
            </form>
        </div>
    </div>
</body>
</html>