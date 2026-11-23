<?php
require_once '../config/conexao.php';
require_once '../includes/header.php';

$idCliente = (int)$_SESSION['cliente_id'];

// Atualiza ficha de saúde se o cliente enviar o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_saude'])) {
    $stmtUpd = $pdo->prepare("UPDATE cliente SET alergias = ?, observacoes_medicas = ?, telefone_cliente = ?, contato_emergencia = ? WHERE id_cliente = ?");
    $stmtUpd->execute([
        $_POST['alergias'] ?: 'Nenhuma',
        $_POST['observacoes_medicas'] ?: 'Sem observações',
        $_POST['telefone_cliente'],
        $_POST['contato_emergencia'] ?: null,
        $idCliente
    ]);
    setFlash('success', 'Suas informações de saúde e contato foram atualizadas!');
    header('Location: cliente_painel.php');
    exit;
}

$stmtCli = $pdo->prepare("SELECT * FROM cliente WHERE id_cliente = ?");
$stmtCli->execute([$idCliente]);
$cliente = $stmtCli->fetch();

$profissionais = $pdo->query("SELECT * FROM profissional WHERE ativo = 1 ORDER BY nome_profissional")->fetchAll();
$pagamentos    = $pdo->query("SELECT * FROM pagamento ORDER BY forma_pagamento")->fetchAll();

$stmtAg = $pdo->prepare("
    SELECT a.*, p.nome_profissional, p.especialidade, pg.forma_pagamento
    FROM agendamento a
    JOIN profissional p ON a.id_profissional = p.id_profissional
    LEFT JOIN pagamento pg ON a.id_pagamento = pg.id_pagamento
    WHERE a.id_cliente = ?
    ORDER BY a.data_hora DESC
");
$stmtAg->execute([$idCliente]);
$meusAgendamentos = $stmtAg->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-0">Olá, <?= htmlspecialchars($cliente['nome_cliente']) ?>!</h3>
        <small class="text-secondary">Área do Cliente • Verso Sobre Pele</small>
    </div>
    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalNovoAgCliente">
        <i class="bi bi-calendar-plus"></i> Agendar Procedimento
    </button>
</div>

<div class="row g-4">
    <!-- Coluna 1: Ficha de Saúde e Alergias (RF02) -->
    <div class="col-lg-4">
        <div class="card p-3">
            <h5 class="mb-3"><i class="bi bi-heart-pulse text-danger"></i> Minha Ficha de Saúde</h5>
            <form method="POST">
                <input type="hidden" name="atualizar_saude" value="1">
                <div class="mb-2">
                    <label class="form-label">Telefone / WhatsApp</label>
                    <input type="text" name="telefone_cliente" class="form-control" value="<?= htmlspecialchars($cliente['telefone_cliente']) ?>" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Contato de Emergência</label>
                    <input type="text" name="contato_emergencia" class="form-control" value="<?= htmlspecialchars($cliente['contato_emergencia'] ?? '') ?>">
                </div>
                <div class="mb-2">
                    <label class="form-label text-warning">Alergias (Látex, Níquel, Tinta, Iodo...)</label>
                    <input type="text" name="alergias" class="form-control" value="<?= htmlspecialchars($cliente['alergias']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Observações Médicas</label>
                    <textarea name="observacoes_medicas" rows="3" class="form-control"><?= htmlspecialchars($cliente['observacoes_medicas']) ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100">Salvar Ficha de Saúde</button>
            </form>
        </div>
    </div>

    <!-- Coluna 2: Meus Agendamentos (RF04, RF05, RN03) -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><i class="bi bi-calendar-check"></i> Meus Agendamentos & Histórico</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Data / Hora</th>
                            <th>Serviço & Profissional</th>
                            <th>Sala</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($meusAgendamentos)): ?>
                            <tr><td colspan="6" class="text-center text-secondary py-4">Você ainda não possui agendamentos.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($meusAgendamentos as $ag): ?>
                            <?php
                                // Verifica RN03: Retoque gratuito em até 3 meses (90 dias) após tatuagem concluída
                                $diasPassados = (time() - strtotime($ag['data_hora'])) / 86400;
                                $direitoRetoque = ($ag['categoria_servico'] === 'Tatuagem' && $ag['status_agendamento'] === 'Concluído' && $diasPassados >= 0 && $diasPassados <= 90);
                            ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($ag['data_hora'])) ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($ag['procedimento']) ?></strong>
                                    <span class="badge bg-secondary ms-1"><?= $ag['categoria_servico'] ?></span><br>
                                    <small class="text-secondary">Prof: <?= htmlspecialchars($ag['nome_profissional']) ?></small>
                                    <?php if ($direitoRetoque): ?>
                                        <br><span class="badge bg-success mt-1">✨ Direito a Retoque Grátis (RN03)</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($ag['sala_atendimento']) ?></td>
                                <td>
                                    R$ <?= number_format($ag['valor_sessao'], 2, ',', '.') ?>
                                    <?php if ($ag['desconto_aplicado'] > 0): ?>
                                        <br><small class="text-success">-10% à vista aplicado</small>
                                    <?php endif; ?>
                                    <?php if ($ag['taxa_cancelamento'] > 0): ?>
                                        <br><small class="text-danger">+Taxa R$ <?= number_format($ag['taxa_cancelamento'], 2, ',', '.') ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?= $ag['status_agendamento'] ?></span>
                                    <?php if (!empty($ag['justificativa_cancelamento'])): ?>
                                        <br><small class="text-secondary">Motivo: <?= htmlspecialchars($ag['justificativa_cancelamento']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($ag['status_agendamento'] === 'Agendado'): ?>
                                        <button class="btn btn-sm btn-outline-danger" 
                                                onclick="abrirCancelar(<?= $ag['id_agendamento'] ?>)">
                                            Cancelar
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Agendar pelo Cliente -->
<div class="modal fade" id="modalNovoAgCliente" tabindex="-1">
    <div class="modal-dialog">
        <form action="../actions/agendamento_salvar.php" method="POST" class="modal-content">
            <input type="hidden" name="id_cliente" value="<?= $idCliente ?>">
            <input type="hidden" name="origem_cliente" value="1">
            <div class="modal-header">
                <h5 class="modal-title">Solicitar Agendamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Categoria do Serviço *</label>
                    <select name="categoria_servico" class="form-select" required>
                        <option value="Tatuagem">Tatuagem (Inclui retoque grátis em até 3 meses)</option>
                        <option value="Body Piercing">Body Piercing (10% de desconto à vista)</option>
                        <option value="Procedimento Estético">Procedimento Estético</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">O que deseja fazer? *</label>
                    <input type="text" name="procedimento" class="form-control" placeholder="Ex: Fine line no antebraço 10cm / Perfuração Helix" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Profissional *</label>
                    <select name="id_profissional" class="form-select" required>
                        <?php foreach ($profissionais as $p): ?>
                            <option value="<?= $p['id_profissional'] ?>"><?= htmlspecialchars($p['nome_profissional']) ?> — <?= htmlspecialchars($p['especialidade']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Data e Hora *</label>
                        <input type="datetime-local" name="data_hora" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Preferência de Pagamento</label>
                        <select name="id_pagamento" class="form-select">
                            <?php foreach ($pagamentos as $pg): ?>
                                <option value="<?= $pg['id_pagamento'] ?>"><?= htmlspecialchars($pg['forma_pagamento']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="alert alert-warning py-2 small mb-0">
                    • Sábados e domingos exigem reserva antecipada (não agendamos no mesmo dia)[cite: 1, 12].<br>
                    • Cancelamentos com menos de 24h geram taxa de 10%[cite: 1, 12].
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-danger w-100">Confirmar Agendamento</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Cancelamento com Justificativa (RF04 / RN05) -->
<div class="modal fade" id="modalCancelar" tabindex="-1">
    <div class="modal-dialog">
        <form action="../actions/agendamento_cancelar.php" method="POST" class="modal-content">
            <input type="hidden" name="id_agendamento" id="cancel_id_agendamento">
            <div class="modal-header">
                <h5 class="modal-title">Cancelar Agendamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-warning">Atenção: Cancelamentos com menos de 24 horas de antecedência sofrem cobrança de taxa de 10% sobre o procedimento (RN05)[cite: 1, 12].</p>
                <label class="form-label">Motivo / Justificativa do Cancelamento *</label>
                <textarea name="justificativa_cancelamento" class="form-control" rows="3" required placeholder="Informe o motivo do cancelamento..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-danger">Confirmar Cancelamento</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirCancelar(id) {
    document.getElementById('cancel_id_agendamento').value = id;
    new bootstrap.Modal(document.getElementById('modalCancelar')).show();
}
</script>

<?php require_once '../includes/footer.php'; ?>