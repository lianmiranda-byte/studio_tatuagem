<?php
require_once '../config/conexao.php';
require_once '../includes/header.php';

$produtos = $pdo->query("SELECT * FROM produto ORDER BY categoria, nome_produto")->fetchAll();
$movimentacoes = $pdo->query("
    SELECT e.*, p.nome_produto 
    FROM estoque e 
    JOIN produto p ON e.id_produto = p.id_produto 
    ORDER BY e.data_hora DESC LIMIT 15
")->fetchAll();
?>

<h3 class="mb-3"><i class="bi bi-boxes text-danger"></i> Controle de Estoque</h3>

<div class="row g-4">
    <!-- Movimentação Rápida (Entrada/Saída) -->
    <div class="col-md-4">
        <div class="card bg-dark border-secondary p-3 mb-3">
            <h5>Movimentar Estoque</h5>
            <form action="../actions/estoque_movimentar.php" method="POST">
                <input type="hidden" name="acao" value="movimentar">
                <div class="mb-2">
                    <label class="form-label">Produto</label>
                    <select name="id_produto" class="form-select" required>
                        <?php foreach ($produtos as $p): ?>
                            <option value="<?= $p['id_produto'] ?>"><?= htmlspecialchars($p['nome_produto']) ?> (Atual: <?= $p['quantidade_estoque'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label">Tipo</label>
                        <select name="tipo_movimentacao" class="form-select">
                            <option value="ENTRADA">ENTRADA (+)</option>
                            <option value="SAIDA">SAÍDA (-)</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Quantidade</label>
                        <input type="number" name="quantidade" min="1" class="form-control" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Observação</label>
                    <input type="text" name="observacao" class="form-control" placeholder="Ex: Compra fornecedor" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Registrar Movimentação</button>
            </form>
        </div>

        <!-- Cadastrar Novo Produto -->
        <div class="card bg-dark border-secondary p-3">
            <h5>Novo Produto</h5>
            <form action="../actions/estoque_movimentar.php" method="POST">
                <input type="hidden" name="acao" value="novo_produto">
                <div class="mb-2">
                    <input type="text" name="nome_produto" class="form-control" placeholder="Nome do produto" required>
                </div>
                <div class="mb-2">
                    <input type="text" name="marca" class="form-control" placeholder="Marca" required>
                </div>
                <div class="mb-2">
                    <select name="categoria" class="form-select">
                        <option value="Tinta">Tinta</option>
                        <option value="Joia/Piercing">Joia/Piercing</option>
                        <option value="Descartável">Descartável</option>
                        <option value="Pós-Tattoo">Pós-Tattoo</option>
                    </select>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <input type="number" step="0.01" name="preco_unitario" class="form-control" placeholder="Preço R$" required>
                    </div>
                    <div class="col-6">
                        <input type="number" name="estoque_minimo" class="form-control" placeholder="Est. Mínimo" value="5" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-danger w-100">Criar Produto</button>
            </form>
        </div>
    </div>

    <!-- Lista de Produtos e Histórico -->
    <div class="col-md-8">
        <div class="card bg-dark border-secondary mb-4">
            <div class="card-header">Saldo Atual de Produtos</div>
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Categoria</th>
                            <th>Preço Unit.</th>
                            <th>Saldo</th>
                            <th>Mínimo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produtos as $p): ?>
                            <?php $baixo = $p['quantidade_estoque'] <= $p['estoque_minimo']; ?>
                            <tr>
                                <td><?= htmlspecialchars($p['nome_produto']) ?> <small class="text-secondary">(<?= $p['marca'] ?>)</small></td>
                                <td><?= $p['categoria'] ?></td>
                                <td>R$ <?= number_format($p['preco_unitario'], 2, ',', '.') ?></td>
                                <td class="<?= $baixo ? 'text-warning fw-bold' : 'text-success' ?>">
                                    <?= $p['quantidade_estoque'] ?> <?= $baixo ? '⚠️' : '' ?>
                                </td>
                                <td><?= $p['estoque_minimo'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card bg-dark border-secondary">
            <div class="card-header">Últimas Movimentações</div>
            <table class="table table-dark table-sm mb-0">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Produto</th>
                        <th>Tipo</th>
                        <th>Qtd</th>
                        <th>Obs</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($movimentacoes as $m): ?>
                        <tr>
                            <td><?= date('d/m H:i', strtotime($m['data_hora'])) ?></td>
                            <td><?= htmlspecialchars($m['nome_produto']) ?></td>
                            <td><span class="badge <?= $m['tipo_movimentacao'] == 'ENTRADA' ? 'bg-success' : 'bg-danger' ?>"><?= $m['tipo_movimentacao'] ?></span></td>
                            <td><?= $m['quantidade'] ?></td>
                            <td><?= htmlspecialchars($m['observacao']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>