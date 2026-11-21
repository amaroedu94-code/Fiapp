<?php
require "conexao.php";
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION["usuario_id"];

function h($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

$stmt = $conexao->prepare(
    "SELECT a.nome, a.unidade_medida, e.quantidade,
    e.marca, e.data_validade, e.observacoes,
    DATEDIFF(e.data_validade, CURDATE()) AS dias_restantes
    FROM estoque e
    INNER JOIN alimentos a ON a.id = e.alimento_id
    WHERE e.usuario_id = ? AND a.usuario_id = ?
    ORDER BY e.data_validade"
);

$stmt->bind_param("ii", $usuario_id, $usuario_id);
$stmt->execute();

$stmt->bind_result(
    $nome,
    $unidade,
    $quantidade,
    $marca,
    $validade,
    $observacoes,
    $dias
);

$vencidos = [];
$vencem_hoje = [];
$proximos = [];
$em_dia = [];

while ($stmt->fetch()) {
    $item = [
        "nome" => $nome,
        "unidade" => $unidade,
        "quantidade" => $quantidade,
        "marca" => $marca,
        "validade" => $validade,
        "observacoes" => $observacoes,
        "dias" => $dias
    ];

    if ($dias < 0) {
        $vencidos[] = $item;
    } elseif ($dias == 0) {
        $vencem_hoje[] = $item;
    } elseif ($dias <= 7) {
        $proximos[] = $item;
    } else {
        $em_dia[] = $item;
    }
}

$stmt->close();

function mostrarItens($itens, $mensagem, $classe) {
    if (count($itens) == 0) {
        echo '<p class="vazio">' . h($mensagem) . '</p>';
        return;
    }

    foreach ($itens as $item) {
        echo '<section class="cartao item-vencimento ' . h($classe) . '">';

        echo '<div class="item-topo">';
        echo '<h3>' . h($item["nome"]) . '</h3>';

        if ($item["dias"] < 0) {
            echo '<span class="status status-vermelho">Vencido</span>';
        } elseif ($item["dias"] == 0) {
            echo '<span class="status status-laranja">Vence hoje</span>';
        } elseif ($item["dias"] <= 7) {
            echo '<span class="status status-amarelo">Próximo do vencimento</span>';
        } else {
            echo '<span class="status status-verde">Dentro do prazo</span>';
        }

        echo '</div>';

        echo '<p><strong>Quantidade:</strong> ' . h($item["quantidade"]) . ' ' . h($item["unidade"]) . '</p>';

        echo '<p><strong>Marca:</strong> ' . h($item["marca"] ?: "Não informada") . '</p>';

        echo '<p><strong>Validade:</strong> ' . date("d/m/Y", strtotime($item["validade"])) . '</p>';

        if ($item["dias"] < 0) {
            echo '<p class="mensagem-status texto-vermelho">Vencido há ' . abs($item["dias"]) . ' dia(s).</p>';
        } elseif ($item["dias"] == 0) {
            echo '<p class="mensagem-status texto-laranja">A validade termina hoje!</p>';
        } else {
            echo '<p class="mensagem-status">Faltam ' . $item["dias"] . ' dia(s) para vencer.</p>';
        }

        echo '<p><strong>Observações:</strong> ' . h($item["observacoes"] ?: "Nenhuma") . '</p>';

        echo '</section>';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vencimentos - Fiapp</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
</head>

<body>

    <header class="topo">
        <h1>FIAPP</h1>
        <a href="inicio.php">Início</a>
        <a href="alimentos.php">Alimentos</a>
        <a href="estoque.php">Estoque</a>
        <a href="receitas.php">Sugestões de receitas</a>
        <a href="sair.php">Sair</a>
    </header>

    <main class="container">

        <h2>Controle de vencimentos</h2>

        <p>Acompanhe a validade dos alimentos e evite desperdícios.</p>

        <div class="resumo-vencimentos">

            <section class="resumo-card resumo-vermelho">
                <p>Alimentos vencidos</p>
                <h3><?= count($vencidos) ?></h3>
            </section>

            <section class="resumo-card resumo-laranja">
                <p>Vencem hoje</p>
                <h3><?= count($vencem_hoje) ?></h3>
            </section>

            <section class="resumo-card resumo-amarelo">
                <p>Próximos 7 dias</p>
                <h3><?= count($proximos) ?></h3>
            </section>

            <section class="resumo-card resumo-verde">
                <p>Dentro do prazo</p>
                <h3><?= count($em_dia) ?></h3>
            </section>

        </div>

        <h3 class="titulo-secao">Alimentos vencidos</h3>

        <?php mostrarItens($vencidos, "Não existem alimentos vencidos.", "item-vermelho"); ?>

        <h3 class="titulo-secao">Vencem hoje</h3>

        <?php mostrarItens($vencem_hoje, "Nenhum alimento vence hoje.", "item-laranja"); ?>

        <h3 class="titulo-secao">Próximos do vencimento</h3>

        <?php mostrarItens($proximos, "Nenhum alimento vence nos próximos 7 dias.", "item-amarelo"); ?>

        <h3 class="titulo-secao">Dentro do prazo</h3>

        <?php mostrarItens($em_dia, "Não existem outros alimentos dentro do prazo.", "item-verde"); ?>

    </main>

</body>
</html>