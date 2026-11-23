<?php
require_once '../config/conexao.php';
require_once '../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM cliente WHERE id_cliente = ?");
$stmt->execute([$id]);
$c = $stmt->fetch();

if (!$c) {
    echo "<div class='alert alert-danger'>Cliente não encontrado.</div>";
    require_once '../includes/footer.php';
    exit;
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3><i class="bi bi-pencil-square text-danger"></i> Editar Cliente #<?= $c['id_cliente'] ?></h3>
    <a href="clientes.php" class="btn btn-outline-secondary">Voltar</a>
</div>

<form action="../actions/cliente_atualizar.php" method="POST" class="card bg-dark border-secondary p-4">
    <input type="hidden" name="id_cliente" value="<?= $c['id_cliente'] ?>">
    <div class="row g-3">
        <div class="col-md-8">
            <label class="form-label">Nome Completo *</label>
            <input type="text" name="nome_cliente" class="form-control" value="<?= htmlspecialchars($c['nome_cliente']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">CPF *</label>
            <input type="text" name="cpf_cliente" maxlength="11" class="form-control" value="<?= htmlspecialchars($c['cpf_cliente']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Telefone *</label>
            <input type="text" name="telefone_cliente" class="form-control" value="<?= htmlspecialchars($c['telefone_cliente']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Data de Nascimento *</label>
            <input type="date" name="data_nascimento" class="form-control" value="<?= $c['data_nascimento'] ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Instagram</label>
            <input type="text" name="instagram_cliente" class="form-control" value="<?= htmlspecialchars($c['instagram_cliente']) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">E-mail</label>
            <input type="email" name="email_cliente" class="form-control" value="<?= htmlspecialchars($c['email_cliente']) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label text-warning">Alergias</label>
            <input type="text" name="alergias" class="form-control" value="<?= htmlspecialchars($c['alergias']) ?>" required>
        </div>
        <div class="col-12">
            <label class="form-label">Observações Médicas</label>
            <input type="text" name="observacoes_medicas" class="form-control" value="<?= htmlspecialchars($c['observacoes_medicas']) ?>" required>
        </div>
        <div class="col-12 mt-4">
            <button type="submit" class="btn btn-success"><i class="bi bi-check-lg"></i> Salvar Alterações</button>
        </div>
    </div>
</form>

<?php require_once '../includes/footer.php'; ?>