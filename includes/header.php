<?php
$paginaAtiva = basename($_SERVER['PHP_SELF']);
$nomeLogado  = $_SESSION['usuario_nome'] ?? $_SESSION['cliente_nome'] ?? 'Visitante';
$perfilLabel = $_SESSION['usuario_nivel'] ?? 'Cliente';
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verso Sobre Pele — Estúdio & Gestão</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/studio_tatuagem/assets/css/style.css" rel="stylesheet">
</head>
<body>

<div class="app-shell">
    <!-- Navegação Lateral Semântica -->
    <aside class="app-sidebar" aria-label="Navegação Principal">
        <a class="brand-box" href="<?= isset($_SESSION['cliente_id']) ? '/studio_tatuagem/pages/cliente_painel.php' : '/studio_tatuagem/index.php' ?>">
            <div class="brand-icon"><i class="bi bi-droplet-half"></i></div>
            <div>
                <div class="brand-title">VERSO SOBRE PELE</div>
                <div class="brand-sub">Ink & Piercing Studio</div>
            </div>
        </a>

        <nav class="nav-menu">
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <a class="nav-item-link <?= $paginaAtiva === 'index.php' ? 'active' : '' ?>" href="/studio_tatuagem/index.php">
                    <i class="bi bi-grid-1x2"></i> <span>Dashboard</span>
                </a>
                <a class="nav-item-link <?= in_array($paginaAtiva, ['agendamentos.php', 'sessao_detalhes.php'], true) ? 'active' : '' ?>" href="/studio_tatuagem/pages/agendamentos.php">
                    <i class="bi bi-calendar2-week"></i> <span>Agenda</span>
                </a>
                <a class="nav-item-link <?= in_array($paginaAtiva, ['clientes.php', 'cliente_editar.php'], true) ? 'active' : '' ?>" href="/studio_tatuagem/pages/clientes.php">
                    <i class="bi bi-people"></i> <span>Clientes</span>
                </a>
                <a class="nav-item-link <?= $paginaAtiva === 'estoque.php' ? 'active' : '' ?>" href="/studio_tatuagem/pages/estoque.php">
                    <i class="bi bi-box-seam"></i> <span>Estoque</span>
                </a>
                <?php if ($perfilLabel === 'Gerente'): ?>
                    <a class="nav-item-link <?= $paginaAtiva === 'profissionais.php' ? 'active' : '' ?>" href="/studio_tatuagem/pages/profissionais.php">
                        <i class="bi bi-person-badge"></i> <span>Equipe</span>
                    </a>
                    <a class="nav-item-link <?= $paginaAtiva === 'relatorios.php' ? 'active' : '' ?>" href="/studio_tatuagem/pages/relatorios.php">
                        <i class="bi bi-graph-up-arrow"></i> <span>Relatórios</span>
                    </a>
                <?php endif; ?>
            <?php else: ?>
                <a class="nav-item-link active" href="/studio_tatuagem/pages/cliente_painel.php">
                    <i class="bi bi-calendar-heart"></i> <span>Minha Área</span>
                </a>
            <?php endif; ?>
            <a class="nav-item-link text-danger d-lg-none" href="/studio_tatuagem/actions/logout.php">
                <i class="bi bi-box-arrow-right"></i> <span>Sair</span>
            </a>
        </nav>

        <div class="sidebar-user">
            <div class="d-flex align-items-center gap-2 overflow-hidden">
                <div class="avatar-circle"><?= e(iniciais($nomeLogado)) ?></div>
                <div class="text-truncate">
                    <div class="fw-semibold text-white small text-truncate"><?= e($nomeLogado) ?></div>
                    <div class="small" style="color: var(--brand-glow); font-size: 0.72rem;"><?= e($perfilLabel) ?></div>
                </div>
            </div>
            <a href="/studio_tatuagem/actions/logout.php" class="btn btn-sm btn-outline-secondary" title="Encerrar sessão">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </aside>

    <!-- Conteúdo Principal -->
    <main class="app-main">
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert alert-<?= e($_SESSION['flash']['tipo']) ?> alert-dismissible fade show shadow" role="alert">
                <?= e($_SESSION['flash']['mensagem']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>