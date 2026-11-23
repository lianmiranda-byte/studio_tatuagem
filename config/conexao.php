<?php
declare(strict_types=1);
session_start();

$host     = '127.0.0.1';
$dbname   = 'studio_tatuagem_db';
$username = 'root';
$password = '';
$charset  = 'utf8mb4';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=$charset",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die("Erro crítico de conexão com o banco: " . $e->getMessage());
}

// Gera ou recupera Token CSRF da sessão
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') . '">';
}

function e(?string $valor): string {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function iniciais(string $nome): string {
    $partes = explode(' ', trim($nome));
    $iniciais = mb_substr($partes[0], 0, 1);
    if (count($partes) > 1) {
        $iniciais .= mb_substr(end($partes), 0, 1);
    }
    return mb_strtoupper($iniciais);
}

function setFlash(string $tipo, string $mensagem): void {
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensagem' => $mensagem];
}

// Controle de Acesso (RF06)
$paginasPublicas = ['login.php', 'cadastro.php', 'cliente_registrar.php'];
$paginaAtual     = basename($_SERVER['PHP_SELF']);

if (!in_array($paginaAtual, $paginasPublicas, true)) {
    if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['cliente_id'])) {
        header('Location: /studio_tatuagem/login.php');
        exit;
    }

    $rotasAdmin = ['index.php', 'clientes.php', 'cliente_editar.php', 'profissionais.php', 'agendamentos.php', 'sessao_detalhes.php', 'estoque.php', 'relatorios.php'];
    if (isset($_SESSION['cliente_id']) && in_array($paginaAtual, $rotasAdmin, true)) {
        header('Location: /studio_tatuagem/pages/cliente_painel.php');
        exit;
    }
}