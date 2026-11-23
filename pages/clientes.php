<?php
require_once '../config/conexao.php';
require_once '../includes/header.php';

$clientes = $pdo->query("SELECT * FROM cliente ORDER BY nome_cliente ASC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3><i class="bi bi-people text-danger"></i> Clientes</h3>
    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalCliente">
        <i class="bi bi-plus-lg"></i> Novo Cliente
    </button>
</div>

<div class="card bg-dark border-secondary">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>CPF</th>
                    <th>Telefone / Insta</th>
                    <th>Nascimento</th>
                    <th>Alergias & Obs. Médicas</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientes as $c): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($c['nome_cliente']) ?></strong><br>
                            <small class="text-secondary"><?= htmlspecialchars($c['email_cliente']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($c['cpf_cliente']) ?></td>
                        <td>
                            <?= htmlspecialchars($c['telefone_cliente']) ?><br>
                            <small class="text-info"><?= htmlspecialchars($c['instagram_cliente']) ?></small>
                        </td>
                        <td><?= date('d/m/Y', strtotime($c['data_nascimento'])) ?></td>
                        <td>
                            <?php if ($c['alergias'] !== 'Nenhuma'): ?>
                                <span class="badge bg-danger">Alergia: <?= htmlspecialchars($c['alergias']) ?></span><br>
                            <?php endif; ?>
                            <small class="text-secondary"><?= htmlspecialchars($c['observacoes_medicas']) ?></small>
                        </td>
                        <td>
                            <a href="../actions/cliente_excluir.php?id=<?= $c['id_cliente'] ?>" 
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Excluir este cliente?');">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Novo Cliente -->
<div class="modal fade" id="modalCliente" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="../actions/cliente_salvar.php" method="POST" class="modal-content bg-dark border-secondary">
            <div class="modal-header">
                <h5 class="modal-title">Cadastrar Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-8">
                    <label class="form-label">Nome Completo *</label>
                    <input type="text" name="nome_cliente" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">CPF (11 números) *</label>
                    <input type="text" name="cpf_cliente" maxlength="11" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Telefone *</label>
                    <input type="text" name="telefone_cliente" class="form-control" placeholder="(11) 99999-9999" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Data de Nascimento *</label>
                    <input type="date" name="data_nascimento" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email_cliente" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Instagram</label>
                    <input type="text" name="instagram_cliente" class="form-control" placeholder="@usuario">
                </div>
                <div class="col-md-6">
                    <label class="form-label text-warning">Alergias</label>
                    <input type="text" name="alergias" class="form-control" value="Nenhuma" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Observações Médicas</label>
                    <input type="text" name="observacoes_medicas" class="form-control" value="Sem observações" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-danger">Salvar Cliente</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>