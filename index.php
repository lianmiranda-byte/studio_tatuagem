<?php
declare(strict_types=1);
require_once 'config/conexao.php';
require_once 'includes/header.php';

$faturamento = (float)$pdo->query("SELECT COALESCE(SUM(valor_sessao), 0) FROM agendamento WHERE status_agendamento = 'Concluído'")->fetchColumn();
$agendados   = (int)$pdo->query("SELECT COUNT(*) FROM agendamento WHERE status_agendamento = 'Agendado'")->fetchColumn();
$clientes    = (int)$pdo->query("SELECT COUNT(*) FROM cliente")->fetchColumn();
$alertasEst  = (int)$pdo->query("SELECT COUNT(*) FROM produto WHERE quantidade_estoque <= estoque_minimo")->fetchColumn();

$proximos = $pdo->query("
    SELECT a.*, c.nome_cliente, c.alergias, p.nome_profissional 
    FROM agendamento a
    JOIN cliente c ON a.id_cliente = c.id_cliente
    JOIN profissional p ON a.id_profissional = p.id_profissional
    WHERE a.status_agendamento = 'Agendado'
    ORDER BY a.data_hora ASC LIMIT 8
")->fetchAll();

$criticos = $pdo->query("SELECT * FROM produto WHERE quantidade_estoque <= estoque_minimo ORDER BY quantidade_estoque ASC")->fetchAll();
?>

<header class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="page-title">Visão Geral do Estúdio</h1>
        <p class="mb-0 small" style="color: var(--text-muted);">Acompanhamento em tempo real de agenda, clientes e insumos</p>
    </div>
    <a href="pages/agendamentos.php" class="btn btn-brand">
        <i class="bi bi-plus-lg"></i> Novo Agendamento
    </a>
</header>

<!-- Bento KPIs -->
<section class="bento-kpis" aria-label="Indicadores Principais">
    <article class="kpi-card">
        <div class="kpi-label"><span>Faturamento Concluído</span> <i class="bi bi-currency-dollar text-success fs-5"></i></div>
        <div class="kpi-value">R$ <?= number_format($faturamento, 2, ',', '.') ?></div>
    </article>
    <article class="kpi-card">
        <div class="kpi-label"><span>Sessões Agendadas</span> <i class="bi bi-calendar-check fs-5" style="color: var(--brand-glow);"></i></div>
        <div class="kpi-value"><?= $agendados ?></div>
    </article>
    <article class="kpi-card">
        <div class="kpi-label"><span>Base de Clientes</span> <i class="bi bi-people fs-5" style="color: var(--brand-glow);"></i></div>
        <div class="kpi-value"><?= $clientes ?></div>
    </article>
    <article class="kpi-card">
        <div class="kpi-label"><span>Alertas de Estoque</span> <i class="bi bi-exclamation-triangle text-warning fs-5"></i></div>
        <div class="kpi-value"><?= $alertasEst ?> itens</div>
    </article>
</section>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span><i class="bi bi-clock-history me-1"></i> Próximos Atendimentos</span>
                <input type="search" class="form-control form-control-sm" style="max-width: 230px;" 
                       placeholder="Filtrar cliente ou sala..." data-table-search="#tabela-dashboard">
            </div>
            <div class="table-responsive">
                <table class="table table-hover" id="tabela-dashboard">
                    <thead>
                        <tr>
                            <th>Horário</th>
                            <th>Cliente</th>
                            <th>Profissional</th>
                            <th>Procedimento</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($proximos as $ag): ?>
                            <tr>
                                <td class="fw-semibold"><?= date('d/m • H:i', strtotime($ag['data_hora'])) ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="avatar-circle" style="width:30px;height:30px;font-size:0.72rem;"><?= e(iniciais($ag['nome_cliente'])) ?></span>
                                        <div>
                                            <div class="fw-semibold text-white"><?= e($ag['nome_cliente']) ?></div>
                                            <?php if ($ag['alergias'] !== 'Nenhuma'): ?>
                                                <span class="badge bg-danger" style="font-size:0.68rem;">Alergia: <?= e($ag['alergias']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?= e($ag['nome_profissional']) ?></td>
                                <td>
                                    <?= e($ag['procedimento']) ?><br>
                                    <small style="color: var(--brand-glow);"><?= e($ag['sala_atendimento']) ?></small>
                                </td>
                                <td>
                                    <a href="pages/sessao_detalhes.php?id=<?= (int)$ag['id_agendamento'] ?>" class="btn btn-sm btn-brand">Abrir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header text-warning">
                <i class="bi bi-box-seam me-1"></i> Reposição Necessária
            </div>
            <ul class="list-group list-group-flush">
                <?php if (empty($criticos)): ?>
                    <li class="list-group-item py-4 text-center" style="color: var(--text-muted);">Todos os insumos estão acima do mínimo.</li>
                <?php endif; ?>
                <?php foreach ($criticos as $c): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <div>
                            <div class="fw-semibold text-white"><?= e($c['nome_produto']) ?></div>
                            <small style="color: var(--text-muted);"><?= e($c['marca']) ?></small>
                        </div>
                        <span class="status-pill pill-cancelado"><?= (int)$c['quantidade_estoque'] ?> un.</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>