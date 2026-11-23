<?php require_once 'config/conexao.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Cliente - Verso Sobre Pele</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/studio_tatuagem/assets/css/style.css" rel="stylesheet">
</head>
<body class="login-page py-5">
<div class="container" style="max-width: 750px;">

    <!-- Abas Login / Cadastro -->
    <ul class="nav nav-pills nav-justified mb-3 bg-dark p-1 rounded-3 border border-secondary">
        <li class="nav-item">
            <a class="nav-link text-light" href="login.php"><i class="bi bi-box-arrow-in-right"></i> Entrar</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active bg-primary" href="cadastro.php"><i class="bi bi-person-plus"></i> Criar Conta (Cliente)</a>
        </li>
    </ul>

    <div class="card login-card p-4">
        <div class="text-center mb-4">
            <i class="bi bi-droplet-half text-danger fs-1"></i>
            <h3 class="mt-2">Verso Sobre Pele</h3>
            <p class="text-secondary mb-0">Crie sua conta para agendar sessões e informar seu histórico de saúde</p>
        </div>

        <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert alert-<?= $_SESSION['flash']['tipo'] ?>">
                <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <form action="actions/cliente_registrar.php" method="POST" class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Nome Completo *</label>
                <input type="text" name="nome_cliente" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">CPF (apenas números) *</label>
                <input type="text" name="cpf_cliente" maxlength="11" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">E-mail (será seu login) *</label>
                <input type="email" name="email_cliente" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Crie uma Senha *</label>
                <input type="password" name="senha_cliente" minlength="6" class="form-control" placeholder="Mínimo 6 caracteres" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Telefone / WhatsApp *</label>
                <input type="text" name="telefone_cliente" class="form-control" placeholder="(11) 99999-9999" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Data de Nascimento *</label>
                <input type="date" name="data_nascimento" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Contato de Emergência</label>
                <input type="text" name="contato_emergencia" class="form-control" placeholder="(11) 98888-8888">
            </div>
            <div class="col-md-4">
                <label class="form-label">Instagram</label>
                <input type="text" name="instagram_cliente" class="form-control" placeholder="@seuuser">
            </div>
            <div class="col-md-8">
                <label class="form-label text-warning">Possui alguma alergia? (Látex, níquel, iodo, pigmentos...)</label>
                <input type="text" name="alergias" class="form-control" value="Nenhuma" required>
            </div>
            <div class="col-12">
                <label class="form-label">Observações Médicas (Queloide, pressão baixa, uso de medicamentos, etc.)</label>
                <textarea name="observacoes_medicas" rows="2" class="form-control" placeholder="Se não houver, deixe em branco"></textarea>
            </div>

            <div class="col-12">
                <div class="alert alert-secondary py-2 small mb-2">
                    <i class="bi bi-info-circle"></i> <strong>Regras do Estúdio (RN02):</strong> Piercings a partir de 13 anos e Tatuagens a partir de 16 anos exigem acompanhamento presencial dos pais com documento[cite: 1, 12].
                </div>
                <button type="submit" class="btn btn-danger w-100 py-2 fw-bold">Finalizar Cadastro e Acessar</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>