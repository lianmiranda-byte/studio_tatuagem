<?php
require_once '../config/conexao.php';
require_once '../includes/header.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT a.*, c.nome_cliente, c.alergias, c.observacoes_medicas, p.nome_profissional 
    FROM agendamento a
    JOIN cliente c ON a.id_cliente = c.id_cliente
    JOIN profissional p ON a.id_profissional = p.id_profissional
    WHERE a.id_agendamento = ?
");
$stmt->execute([$id]);
$ag = $stmt->fetch();

if (!$ag) {
    echo "<div class='alert alert-danger'>Agendamento não encontrado.</div>";
    require_once '../includes/footer.php';
    exit;
}

// Produtos disponíveis em estoque
$produtos = $pdo->query("SELECT * FROM produto WHERE quantidade_estoque > 0 ORDER BY nome_produto")->fetchAll();
$pagamentos = $pdo->query("SELECT * FROM pagamento")->fetchAll();

// Insumos já usados nesta sessão
$usados = $pdo->prepare("
    SELECT ap.*, pr.nome_produto, pr.marca 
    FROM agendamento_produto ap
    JOIN produto pr ON ap.id_produto = pr.id_produto
    WHERE ap.id_agendamento = ?
");
$usados->execute([$id]);
$listaUsados = $usados->fetchAll();

$totalAdicional = array_sum(array_column($listaUsados, 'valor_cobrado'));
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Sessão #<?= $ag['id_agendamento'] ?> - <?= htmlspecialchars($ag['procedimento']) ?></h3>
    <a href="agendamentos.php" class="btn btn-outline-secondary">Voltar para Agenda</a>
</div>

<?php if ($ag['alergias'] !== 'Nenhuma' || $ag['observacoes_medicas'] !== 'Sem observações'): ?>
    <div class="alert alert-danger">
        <strong><i class="bi bi-exclamation-octagon"></i> Atenção Clínica:</strong> 
        Alergias: <u><?= htmlspecialchars($ag['alergias']) ?></u> | 
        Obs: <?= htmlspecialchars($ag['observacoes_medicas']) ?>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Coluna 1: Status e Fechamento -->
    <div class="col-md-5">
        <div class="card bg-dark border-secondary p-3">
            <h5>Resumo Financeiro & Status</h5>
            <hr>
            <p><strong>Cliente:</strong> <?= htmlspecialchars($ag['nome_cliente']) ?></p>
            <p><strong>Profissional:</strong> <?= htmlspecialchars($ag['nome_profissional']) ?></p>
            <p><strong>Data/Sala:</strong> <?= date('d/m/Y H:i', strtotime($ag['data_hora'])) ?> (<?= $ag['sala_atendimento'] ?>)</p>
            <p><strong>Valor Sessão:</strong> R$ <?= number_format($ag['valor_sessao'], 2, ',', '.') ?></p>
            <p><strong>Joias/Extras Cobrados:</strong> R$ <?= number_format($totalAdicional, 2, ',', '.') ?></p>
            <h4 class="text-success">Total Geral: R$ <?= number_format($ag['valor_sessao'] + $totalAdicional, 2, ',', '.') ?></h4>

            <form action="../actions/agendamento_status.php" method="POST" class="mt-3">
                <input type="hidden" name="id_agendamento" value="<?= $ag['id_agendamento'] ?>">
                <div class="mb-2">
                    <label class="form-label">Forma de Pagamento</label>
                    <select name="id_pagamento" class="form-select">
                        <option value="">-- Selecione --</option>
                        <?php foreach ($pagamentos as $pg): ?>
                            <option value="<?= $pg['id_pagamento'] ?>" <?= $ag['id_pagamento'] == $pg['id_pagamento'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($pg['forma_pagamento']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status da Sessão</label>
                    <select name="status_agendamento" class="form-select">
                        <?php foreach (['Agendado', 'Concluído', 'Cancelado', 'Não Compareceu'] as $st): ?>
                            <option value="<?= $st ?>" <?= $ag['status_agendamento'] == $st ? 'selected' : '' ?>><?= $st ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-success w-100">Atualizar Sessão</button>
            </form>
        </div>
    </div>

    <!-- Coluna 2: Baixa de Insumos / Joias -->
    <div class="col-md-7">
        <div class="card bg-dark border-secondary p-3 mb-3">
            <h5>Lançar Material / Joia Utilizada (Baixa Automática)</h5>
            <form action="../actions/sessao_produto.php" method="POST" class="row g-2 mt-1">
                <input type="hidden" name="id_agendamento" value="<?= $ag['id_agendamento'] ?>">
                <div class="col-md-6">
                    <label class="form-label">Produto / Insumo</label>
                    <select name="id_produto" class="form-select" required>
                        <?php foreach ($produtos as $pr): ?>
                            <option value="<?= $pr['id_produto'] ?>">
                                <?= htmlspecialchars($pr['nome_produto']) ?> (Disp: <?= $pr['quantidade_estoque'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Qtd</label>
                    <input type="number" name="quantidade_utilizada" value="1" min="1" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Cobrar Cliente (R$)</label>
                    <input type="number" step="0.01" name="valor_cobrado" value="0.00" class="form-control" title="Deixe 0 para descartáveis inclusos">
                </div>
                <div class="col-12 mt-3">
                    <button type="submit" class="btn btn-danger"><i class="bi bi-plus-circle"></i> Lançar e Baixar Estoque</button>
                </div>
            </form>
        </div>

        <div class="card bg-dark border-secondary">
            <div class="card-header">Materiais Registrados nesta Sessão</div>
            <table class="table table-dark mb-0">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Qtd</th>
                        <th>Valor Cobrado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($listaUsados)): ?>
                        <tr><td colspan="3" class="text-secondary">Nenhum material lançado ainda.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($listaUsados as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['nome_produto']) ?> (<?= htmlspecialchars($u['marca']) ?>)</td>
                            <td><?= $u['quantidade_utilizada'] ?></td>
                            <td>R$ <?= number_format($u['valor_cobrado'], 2, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>