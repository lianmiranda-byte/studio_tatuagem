<?php
require_once '../config/conexao.php';

// Processar cadastro rápido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO profissional (nome_profissional, especialidade, telefone_profissional) VALUES (?, ?, ?)");
    $stmt->execute([$_POST['nome'], $_POST['especialidade'], $_POST['telefone']]);
    setFlash('success', 'Profissional cadastrado!');
    header('Location: profissionais.php');
    exit;
}

// Alternar status Ativo/Inativo
if (isset($_GET['toggle'])) {
    $stmt = $pdo->prepare("UPDATE profissional SET ativo = NOT ativo WHERE id_profissional = ?");
    $stmt->execute([(int)$_GET['toggle']]);
    setFlash('info', 'Status do profissional atualizado.');
    header('Location: profissionais.php');
    exit;
}

require_once '../includes/header.php';
$profissionais = $pdo->query("SELECT * FROM profissional ORDER BY ativo DESC, nome_profissional ASC")->fetchAll();
?>

<h3 class="mb-3"><i class="bi bi-person-badge text-danger"></i> Equipe / Profissionais</h3>

<div class="row g-4">
    <div class="col-md-4">
        <form method="POST" class="card bg-dark border-secondary p-3">
            <h5 class="mb-3">Novo Profissional</h5>
            <div class="mb-2">
                <label class="form-label">Nome</label>
                <input type="text" name="nome" class="form-control" required>
            </div>
            <div class="mb-2">
                <label class="form-label">Especialidade</label>
                <input type="text" name="especialidade" class="form-control" placeholder="Ex: Fine Line, Piercing..." required>
            </div>
            <div class="mb-3">
                <label class="form-label">Telefone</label>
                <input type="text" name="telefone" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-danger w-100">Cadastrar</button>
        </form>
    </div>

    <div class="col-md-8">
        <div class="card bg-dark border-secondary">
            <table class="table table-dark table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Especialidade</th>
                        <th>Telefone</th>
                        <th>Status</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($profissionais as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['nome_profissional']) ?></td>
                            <td><?= htmlspecialchars($p['especialidade']) ?></td>
                            <td><?= htmlspecialchars($p['telefone_profissional']) ?></td>
                            <td>
                                <span class="badge <?= $p['ativo'] ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $p['ativo'] ? 'Ativo' : 'Inativo' ?>
                                </span>
                            </td>
                            <td>
                                <a href="?toggle=<?= $p['id_profissional'] ?>" class="btn btn-sm btn-outline-warning">
                                    <?= $p['ativo'] ? 'Desativar' : 'Ativar' ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>