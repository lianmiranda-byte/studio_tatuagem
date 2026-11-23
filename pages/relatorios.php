<?php
require_once '../config/conexao.php';
require_once '../includes/header.php';

$inicio   = $_GET['inicio'] ?? '2026-01-01';
$fim      = $_GET['fim']    ?? '2026-12-31';
$comissao = (float)($_GET['comissao'] ?? 60); // % padrão do tatuador

// 1. Faturamento por Profissional (apenas Concluídos)
$stmtProf = $pdo->prepare("
    SELECT 
        p.nome_profissional,
        p.especialidade,
        COUNT(a.id_agendamento) AS qtd_sessoes,
        COALESCE(SUM(a.valor_sessao), 0) AS total_bruto
    FROM profissional p
    LEFT JOIN agendamento a ON p.id_profissional = a.id_profissional 
        AND a.status_agendamento = 'Concluído'
        AND DATE(a.data_hora) BETWEEN ? AND ?
    GROUP BY p.id_profissional
    ORDER BY total_bruto DESC
");
$stmtProf->execute([$inicio, $fim]);
$relProfissionais = $stmtProf->fetchAll();

// 2. Faturamento por Forma de Pagamento
$stmtPgto = $pdo->prepare("
    SELECT 
        COALESCE(pg.forma_pagamento, 'Não Informado') AS forma,
        COUNT(a.id_agendamento) AS qtd,
        SUM(a.valor_sessao) AS total
    FROM agendamento a
    LEFT JOIN pagamento pg ON a.id_pagamento = pg.id_pagamento
    WHERE a.status_agendamento = 'Concluído'
      AND DATE(a.data_hora) BETWEEN ? AND ?
    GROUP BY pg.forma_pagamento
    ORDER BY total DESC
");
$stmtPgto->execute([$inicio, $fim]);
$relPagamentos = $stmtPgto->fetchAll();

$totalGeral = array_sum(array_column($relPagamentos, 'total'));
?>

<h3 class="mb-3"><i class="bi bi-bar-chart-line text-danger"></i> Relatórios Financeiros & Comissões</h3>

<!-- Filtro de Período -->
<form method="GET" class="card bg-dark border-secondary p-3 mb-4 row g-3 align-items-end">
    <div class="col-md-3">
        <label class="form-label">Data Inicial</label>
        <input type="date" name="inicio" class="form-control" value="<?= $inicio ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label">Data Final</label>
        <input type="date" name="fim" class="form-control" value="<?= $fim ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label">Comissão Profissional (%)</label>
        <input type="number" name="comissao" min="0" max="100" class="form-control" value="<?= $comissao ?>">
    </div>
    <div class="col-md-3">
        <button type="submit" class="btn btn-danger w-100"><i class="bi bi-filter"></i> Filtrar Relatório</button>
    </div>
</form>

<div class="row g-4">
    <!-- Tabela por Profissional -->
    <div class="col-lg-8">
        <div class="card bg-dark border-secondary">
            <div class="card-header">Desempenho & Repasse por Profissional (Sessões Concluídas)</div>
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Profissional</th>
                            <th>Sessões</th>
                            <th>Bruto</th>
                            <th>Repasse (<?= $comissao ?>%)</th>
                            <th>Lucro Estúdio (<?= 100 - $comissao ?>%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($relProfissionais as $rp): ?>
                            <?php 
                                $repasse = $rp['total_bruto'] * ($comissao / 100);
                                $estudio = $rp['total_bruto'] - $repasse;
                            ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($rp['nome_profissional']) ?></strong><br>
                                    <small class="text-secondary"><?= htmlspecialchars($rp['especialidade']) ?></small>
                                </td>
                                <td><?= $rp['qtd_sessoes'] ?></td>
                                <td>R$ <?= number_format($rp['total_bruto'], 2, ',', '.') ?></td>
                                <td class="text-info">R$ <?= number_format($repasse, 2, ',', '.') ?></td>
                                <td class="text-success">R$ <?= number_format($estudio, 2, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tabela por Forma de Pagamento -->
    <div class="col-lg-4">
        <div class="card bg-dark border-secondary">
            <div class="card-header">Entradas por Forma de Pagamento</div>
            <ul class="list-group list-group-flush">
                <?php if (empty($relPagamentos)): ?>
                    <li class="list-group-item bg-dark text-secondary">Nenhum faturamento no período.</li>
                <?php endif; ?>
                <?php foreach ($relPagamentos as $rpg): ?>
                    <li class="list-group-item bg-dark d-flex justify-content-between">
                        <span><?= htmlspecialchars($rpg['forma']) ?> (<?= $rpg['qtd'] ?>)</span>
                        <strong>R$ <?= number_format($rpg['total'], 2, ',', '.') ?></strong>
                    </li>
                <?php endforeach; ?>
                <li class="list-group-item bg-black d-flex justify-content-between text-success fs-5">
                    <strong>TOTAL GERAL</strong>
                    <strong>R$ <?= number_format($totalGeral, 2, ',', '.') ?></strong>
                </li>
            </ul>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>