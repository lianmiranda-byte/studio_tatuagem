<?php
require_once '../config/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $origemCliente = isset($_POST['origem_cliente']);
    $redirect      = $origemCliente ? '../pages/cliente_painel.php' : '../pages/agendamentos.php';

    try {
        $idCliente  = (int)$_POST['id_cliente'];
        $idProf     = (int)$_POST['id_profissional'];
        $categoria  = $_POST['categoria_servico'] ?? 'Tatuagem';
        $dataHora   = $_POST['data_hora'];
        $idPgto     = !empty($_POST['id_pagamento']) ? (int)$_POST['id_pagamento'] : null;
        $valorBase  = isset($_POST['valor_sessao']) ? (float)$_POST['valor_sessao'] : 150.00; // Valor inicial de avaliação

        // 1. RN01: Sábados (6) e Domingos (0) exigem reserva antecipada (bloqueia agendamento para o mesmo dia)
        $timestampAg = strtotime($dataHora);
        $diaSemana   = (int)date('w', $timestampAg);
        if (($diaSemana === 0 || $diaSemana === 6) && date('Y-m-d', $timestampAg) === date('Y-m-d')) {
            throw new Exception("RN01: Atendimentos aos sábados e domingos exigem reserva antecipada (não permitidos no mesmo dia).");
        }

        // 2. RN02: Validação de Idade Mínima (Tatuagem >= 16 anos, Piercing >= 13 anos)
        $stmtCli = $pdo->prepare("SELECT data_nascimento FROM cliente WHERE id_cliente = ?");
        $stmtCli->execute([$idCliente]);
        $nasc  = $stmtCli->fetchColumn();
        $idade = (new DateTime($nasc))->diff(new DateTime('today'))->y;

        if ($categoria === 'Tatuagem' && $idade < 16) {
            throw new Exception("RN02: Idade mínima para tatuagem é 16 anos (acompanhado pelos pais e documento).");
        }
        if ($categoria === 'Body Piercing' && $idade < 13) {
            throw new Exception("RN02: Idade mínima para body piercing é 13 anos (acompanhado pelos pais).");
        }

        // Define sala padrão caso seja solicitado pelo cliente
        $sala = $_POST['sala_atendimento'] ?? match($categoria) {
            'Body Piercing'         => 'Sala Piercing',
            'Procedimento Estético' => 'Sala Estética',
            default                 => 'Sala 1',
        };

        // Verifica conflito de horário na sala ou com o profissional
        $check = $pdo->prepare("SELECT COUNT(*) FROM agendamento WHERE (sala_atendimento = ? OR id_profissional = ?) AND data_hora = ? AND status_agendamento != 'Cancelado'");
        $check->execute([$sala, $idProf, $dataHora]);
        if ($check->fetchColumn() > 0) {
            throw new Exception("Horário indisponível para este profissional ou sala.");
        }

        // 3. RN04: Desconto automático de 10% em Body Piercing com pagamento à vista (PIX=1, Débito=3, Dinheiro=4)
        $desconto = 0.00;
        if ($categoria === 'Body Piercing' && in_array($idPgto, [1, 3, 4])) {
            $desconto  = $valorBase * 0.10;
            $valorBase = $valorBase - $desconto;
        }

        $stmt = $pdo->prepare("
            INSERT INTO agendamento (id_cliente, id_profissional, id_pagamento, data_hora, sala_atendimento, categoria_servico, procedimento, valor_sessao, desconto_aplicado)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $idCliente, $idProf, $idPgto, $dataHora, $sala, $categoria,
            $_POST['procedimento'], $valorBase, $desconto
        ]);

        $msgExtra = ($idade < 18) ? " Lembrete RN02: Menor de 18 anos deve comparecer acompanhado dos pais com documento." : "";
        setFlash('success', "Agendamento confirmado com sucesso!$msgExtra");

    } catch (Exception $e) {
        setFlash('danger', $e->getMessage());
    }

    header("Location: $redirect");
    exit;
}