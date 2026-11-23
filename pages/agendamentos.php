<?php
require_once '../config/conexao.php';
require_once '../includes/header.php';

$clientes      = $pdo->query("SELECT id_cliente, nome_cliente FROM cliente ORDER BY nome_cliente")->fetchAll();
$profissionais = $pdo->query("SELECT id_profissional, nome_profissional, especialidade FROM profissional WHERE ativo = 1 ORDER BY nome_profissional")->fetchAll();
$pagamentos    = $pdo->query("SELECT * FROM pagamento ORDER BY forma_pagamento")->fetchAll();

$agendamentos = $pdo->query("
    SELECT a.*, c.nome_cliente, c.alergias, p.nome_profissional, pg.forma_pagamento
    FROM agendamento a
    JOIN cliente c ON a.id_cliente = c.id_cliente
    JOIN profissional p ON a.id_profissional = p.id_profissional
    LEFT JOIN pagamento pg ON a.id_pagamento = pg.id_pagamento
    ORDER BY a.data_hora DESC
")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3><i class="bi bi-calendar3 text-danger"></i> Agenda de Sessões</h3>
    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalAgendamento">
        <i class="bi bi-plus-lg"></i> Novo Agendamento
    </button>
</div>

<div class="card bg-dark border-secondary">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Data / Hora</th>
                    <th>Cliente</th>
                    <th>Profissional</th>
                    <th>Procedimento / Sala</th>
                    <th>Valor Base</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($agendamentos as $ag): ?>
                    <?php
                        $corStatus = match($ag['status_agendamento']) {
                            'Concluído'      => 'bg-success',
                            'Agendado'       => 'bg-primary',
                            'Cancelado'      => 'bg-danger',
                            'Não Compareceu' => 'bg-warning text-dark',
                        };
                    ?>
                    <tr>
                        <td><?= date('d/m/Y H:i', strtotime($ag['data_hora'])) ?></td>
                        <td>
                            <?= htmlspecialchars($ag['nome_cliente']) ?>
                            <?php if ($ag['alergias'] !== 'Nenhuma'): ?>
                                <br><small class="text-danger fw-bold">⚠️ <?= htmlspecialchars($ag['alergias']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($ag['nome_profissional']) ?></td>
                        <td>
                            <?= htmlspecialchars($ag['procedimento']) ?><br>
                            <small class="text-secondary"><?= htmlspecialchars($ag['sala_atendimento']) ?> • <?= htmlspecialchars($ag['forma_pagamento'] ?? 'A definir') ?></small>
                        </td>
                        <td>R$ <?= number_format($ag['valor_sessao'], 2, ',', '.') ?></td>
                        <td><span class="badge <?= $corStatus ?>"><?= $ag['status_agendamento'] ?></span></td>
                        <td>
                            <a href="sessao_detalhes.php?id=<?= $ag['id_agendamento'] ?>" class="btn btn-sm btn-outline-light">
                                <i class="bi bi-box-seam"></i> Sessão / Insumos
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Novo Agendamento -->
<div class="modal fade" id="modalAgendamento" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="../actions/agendamento_salvar.php" method="POST" class="modal-content bg-dark border-secondary">
            <div class="modal-header">
                <h5 class="modal-title">Agendar Sessão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Cliente *</label>
                    <select name="id_cliente" class="form-select" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($clientes as $c): ?>
                            <option value="<?= $c['id_cliente'] ?>"><?= htmlspecialchars($c['nome_cliente']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Profissional *</label>
                    <select name="id_profissional" class="form-select" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($profissionais as $p): ?>
                            <option value="<?= $p['id_profissional'] ?>"><?= htmlspecialchars($p['nome_profissional']) ?> (<?= $p['especialidade'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Data e Hora *</label>
                    <input type="datetime-local" name="data_hora" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Sala *</label>
                    <select name="sala_atendimento" class="form-select" required>
                        <option value="Sala 1">Sala 1</option>
                        <option value="Sala 2">Sala 2</option>
                        <option value="Sala 3">Sala 3</option>
                        <option value="Sala Piercing">Sala Piercing</option>
                        <option value="Sala Laser">Sala Laser</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Valor da Sessão (R$) *</label>
                    <input type="number" step="0.01" name="valor_sessao" class="form-control" required>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Procedimento / Descrição *</label>
                    <input type="text" name="procedimento" class="form-control" placeholder="Ex: Tatuagem Antebraço 12cm" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Forma de Pagamento</label>
                    <select name="id_pagamento" class="form-select">
                        <option value="">Definir depois</option>
                        <?php foreach ($pagamentos as $pg): ?>
                            <option value="<?= $pg['id_pagamento'] ?>"><?= htmlspecialchars($pg['forma_pagamento']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-danger">Confirmar Agendamento</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>