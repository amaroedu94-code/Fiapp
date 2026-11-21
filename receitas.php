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

function normalizar($valor) {
    return mb_strtolower(trim((string) $valor), "UTF-8");
}

function converter_unidade($quantidade, $unidade) {
    $unidade = normalizar($unidade);

    // grupo de medida => fator para a unidade base (g, ml ou unidade)
    $tabela = [
        "mg" => ["massa", 0.001],
        "g" => ["massa", 1],
        "grama" => ["massa", 1],
        "gramas" => ["massa", 1],
        "kg" => ["massa", 1000],
        "quilo" => ["massa", 1000],
        "quilos" => ["massa", 1000],
        "quilograma" => ["massa", 1000],
        "quilogramas" => ["massa", 1000],

        "ml" => ["volume", 1],
        "mililitro" => ["volume", 1],
        "mililitros" => ["volume", 1],
        "l" => ["volume", 1000],
        "litro" => ["volume", 1000],
        "litros" => ["volume", 1000],
        "colher de chá" => ["volume", 5],
        "colher de cha" => ["volume", 5],
        "colheres de chá" => ["volume", 5],
        "colheres de cha" => ["volume", 5],
        "colher de sopa" => ["volume", 15],
        "colheres de sopa" => ["volume", 15],
        "xícara" => ["volume", 240],
        "xicara" => ["volume", 240],
        "xícaras" => ["volume", 240],
        "xicaras" => ["volume", 240],

        "unidade" => ["unidade", 1],
        "unidades" => ["unidade", 1],
        "un" => ["unidade", 1],
        "und" => ["unidade", 1],
        "dúzia" => ["unidade", 12],
        "duzia" => ["unidade", 12],
        "dúzias" => ["unidade", 12],
        "duzias" => ["unidade", 12]
    ];

    if (isset($tabela[$unidade])) {
        return [$tabela[$unidade][0], (float) $quantidade * $tabela[$unidade][1]];
    }

    // unidade sem conversão conhecida (ex.: pacote): só compara com ela mesma
    return [$unidade, (float) $quantidade];
}

function dias_para_vencer($validade) {
    return (strtotime($validade) - strtotime(date("Y-m-d"))) / 86400;
}

// Peso médio (em gramas) de uma unidade de alguns alimentos. Permite comparar
// receita em "unidade" com estoque em kg/g (e o contrário).
$pesos_medios = [
    "tomate" => 120,
    "maçã" => 150,
    "cebola" => 150,
    "batata" => 150,
    "banana" => 120,
    "ovo" => 50
];

// Converte a quantidade de um lote para o grupo de medida da receita.
// Retorna null se não for possível converter.
function quantidade_no_grupo($lote, $grupo, $chave) {
    global $pesos_medios;

    if ($lote["grupo"] === $grupo) {
        return $lote["quantidade"];
    }

    if (!isset($pesos_medios[$chave])) {
        return null;
    }

    $peso = $pesos_medios[$chave];

    if ($lote["grupo"] === "massa" && $grupo === "unidade") {
        return $lote["quantidade"] / $peso;
    }

    if ($lote["grupo"] === "unidade" && $grupo === "massa") {
        return $lote["quantidade"] * $peso;
    }

    return null;
}

// Usa o estoque na ordem de validade (o que vence primeiro sai primeiro) e
// considera só os lotes que têm unidade compatível (ou conversível) com a da receita.
function consultar_estoque($item, $grupo, $necessario, $chave) {
    $total = 0;
    $validade_usada = null;

    foreach ($item["lotes"] as $lote) {
        $quantidade = quantidade_no_grupo($lote, $grupo, $chave);

        if ($quantidade === null) {
            continue;
        }

        if ($validade_usada === null) {
            $validade_usada = $lote["validade"];
        }

        $total += $quantidade;

        if ($total + 0.0001 >= $necessario) {
            break;
        }
    }

    return [
        "ok" => $validade_usada !== null && $total + 0.0001 >= $necessario,
        "validade" => $validade_usada
    ];
}

$estoque = [];

$stmt = $conexao->prepare(
    "SELECT a.nome, a.unidade_medida, e.quantidade, e.data_validade
    FROM estoque e
    INNER JOIN alimentos a ON a.id = e.alimento_id
    WHERE e.usuario_id = ? AND a.usuario_id = ?
    AND e.data_validade >= CURDATE()
    ORDER BY e.data_validade"
);

$stmt->bind_param("ii", $usuario_id, $usuario_id);
$stmt->execute();

$stmt->bind_result($nome, $unidade, $quantidade, $validade);

while ($stmt->fetch()) {
    $chave = normalizar($nome);

    if (!isset($estoque[$chave])) {
        $estoque[$chave] = [
            "nome" => $nome,
            "lotes" => []
        ];
    }

    list($grupo, $quantidade_base) = converter_unidade($quantidade, $unidade);

    // cada registro do estoque é um lote com sua própria validade
    $estoque[$chave]["lotes"][] = [
        "grupo" => $grupo,
        "quantidade" => $quantidade_base,
        "validade" => $validade
    ];
}

$stmt->close();

$stmt = $conexao->prepare(
    "SELECT id_receita, titulo, modo_preparo,
    tempo_preparo, nivel_dificuldade, categoria, porcoes
    FROM receitas
    ORDER BY id_receita"
);

$stmt->execute();
$stmt->bind_result(
    $id_receita,
    $titulo,
    $modo_preparo,
    $tempo_preparo,
    $dificuldade,
    $categoria,
    $porcoes
);

$receitas = [];

while ($stmt->fetch()) {
    $receitas[] = [
        "id" => $id_receita,
        "titulo" => $titulo,
        "preparo" => $modo_preparo,
        "tempo" => $tempo_preparo,
        "dificuldade" => $dificuldade,
        "categoria" => $categoria,
        "porcoes" => $porcoes
    ];
}

$stmt->close();

foreach ($receitas as &$receita) {
    $stmt = $conexao->prepare(
        "SELECT c.nome, i.quantidade_necessaria,
        i.unidade_medida, i.substitutos_possiveis
        FROM ingredientes i
        INNER JOIN catalogo_alimentos c
        ON c.id = i.alimento_id
        WHERE i.receita_id = ?"
    );

    $stmt->bind_param("i", $receita["id"]);
    $stmt->execute();

    $stmt->bind_result(
        $nome_ingrediente,
        $quantidade_necessaria,
        $unidade_ingrediente,
        $substitutos
    );

    $receita["ingredientes"] = [];
    $receita["faltantes"] = [];
    $receita["substituidos"] = [];
    $receita["proximos_vencimento"] = 0;

    while ($stmt->fetch()) {
        $ingrediente = [
            "nome" => $nome_ingrediente,
            "quantidade" => $quantidade_necessaria,
            "unidade" => $unidade_ingrediente,
            "substitutos" => $substitutos
        ];

        $chave = normalizar($nome_ingrediente);
        $disponivel = false;
        $validade_usada = null;

        if (isset($estoque[$chave])) {
            list($grupo_necessario, $necessario_base) =
                converter_unidade($quantidade_necessaria, $unidade_ingrediente);

            $consulta = consultar_estoque($estoque[$chave], $grupo_necessario, $necessario_base, $chave);

            if ($consulta["ok"]) {
                $disponivel = true;
                $validade_usada = $consulta["validade"];
            }
        }

        if ($disponivel) {
            $receita["ingredientes"][] = [
                "nome" => $nome_ingrediente,
                "quantidade" => $quantidade_necessaria,
                "unidade" => $unidade_ingrediente,
                "status" => "Disponível"
            ];

            $dias = dias_para_vencer($validade_usada);

            if ($dias >= 0 && $dias <= 7) {
                $receita["proximos_vencimento"]++;
            }
        } else {
            $substituto_encontrado = "";
            $validade_substituto = null;

            if (!empty($substitutos)) {
                $lista_substitutos = explode(",", $substitutos);

                foreach ($lista_substitutos as $substituto) {
                    $substituto = trim($substituto);
                    $chave_substituto = normalizar($substituto);

                    if (isset($estoque[$chave_substituto]) && count($estoque[$chave_substituto]["lotes"]) > 0) {
                        $substituto_encontrado = $substituto;
                        // lotes já vêm ordenados: o primeiro é o que vence antes
                        $validade_substituto = $estoque[$chave_substituto]["lotes"][0]["validade"];
                        break;
                    }
                }
            }

            if ($substituto_encontrado != "") {
                $receita["ingredientes"][] = [
                    "nome" => $nome_ingrediente,
                    "quantidade" => $quantidade_necessaria,
                    "unidade" => $unidade_ingrediente,
                    "status" => "Substituído por " . $substituto_encontrado
                ];

                $receita["substituidos"][] = [
                    "nome" => $nome_ingrediente,
                    "substituto" => $substituto_encontrado
                ];

                $dias = dias_para_vencer($validade_substituto);

                if ($dias >= 0 && $dias <= 7) {
                    $receita["proximos_vencimento"]++;
                }
            } else {
                $receita["ingredientes"][] = [
                    "nome" => $nome_ingrediente,
                    "quantidade" => $quantidade_necessaria,
                    "unidade" => $unidade_ingrediente,
                    "status" => "Faltando"
                ];

                $receita["faltantes"][] = $ingrediente;
            }
        }
    }

    $stmt->close();

    $receita["disponivel"] = count($receita["faltantes"]) == 0;
}

unset($receita);

usort($receitas, function ($a, $b) {
    if ($a["disponivel"] != $b["disponivel"]) {
        return $b["disponivel"] <=> $a["disponivel"];
    }

    return $b["proximos_vencimento"] <=> $a["proximos_vencimento"];
});
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receitas - Fiapp</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<header class="topo">
    <h1>FIAPP</h1>
    <a href="inicio.php">Início</a>
    <a href="alimentos.php">Alimentos</a>
    <a href="estoque.php">Estoque</a>
    <a href="vencimentos.php">Vencimentos</a>
    <a href="sair.php">Sair</a>
</header>

<main class="container">

    <h2>Sugestões de receitas</h2>

    <p>Confira receitas que podem ser preparadas com os alimentos do seu estoque.</p>

    <?php if (count($estoque) == 0): ?>
        <section class="cartao">
            <p>Seu estoque está vazio. Adicione alimentos para receber sugestões de receitas.</p>
            <a href="estoque.php" class="botao">Adicionar alimentos</a>
        </section>
    <?php else: ?>

        <div class="receitas-lista">

            <?php foreach ($receitas as $receita): ?>

                <section class="receita-card">

                    <span class="receita-status <?= $receita["disponivel"] ? "status-pronto" : "status-falta" ?>">
                        <?= $receita["disponivel"] ? "Pode preparar" : "Faltam ingredientes" ?>
                    </span>

                    <h3><?= h($receita["titulo"]) ?></h3>

                    <p class="receita-info">
                        <?= h($receita["categoria"] ?? "Receita") ?>
                        <?php if ($receita["tempo"]): ?>
                            | <?= h($receita["tempo"]) ?> minutos
                        <?php endif; ?>
                        <?php if ($receita["dificuldade"]): ?>
                            | <?= h($receita["dificuldade"]) ?>
                        <?php endif; ?>
                    </p>

                    <?php if ($receita["porcoes"]): ?>
                        <p>Porções: <?= h($receita["porcoes"]) ?></p>
                    <?php endif; ?>

                    <?php if ($receita["proximos_vencimento"] > 0): ?>
                        <p class="status-vencimento">
                            Esta receita aproveita <?= $receita["proximos_vencimento"] ?>
                            ingrediente(s) próximo(s) do vencimento!
                        </p>
                    <?php endif; ?>

                    <h4>Ingredientes</h4>

                    <ul class="ingredientes-lista">
                        <?php foreach ($receita["ingredientes"] as $ingrediente): ?>
                            <li>
                                <?= h($ingrediente["nome"]) ?> -
                                <?= h($ingrediente["quantidade"]) ?>
                                <?= h($ingrediente["unidade"]) ?>

                                <?php if ($ingrediente["status"] == "Disponível"): ?>
                                    <span class="ingrediente-ok">✓ Disponível</span>
                                <?php elseif ($ingrediente["status"] == "Faltando"): ?>
                                    <span class="ingrediente-falta">✗ Faltando</span>
                                <?php else: ?>
                                    <span class="ingrediente-ok">
                                        ✓ <?= h($ingrediente["status"]) ?>
                                    </span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <?php if (count($receita["substituidos"]) > 0): ?>
                        <div class="substituicoes">
                            <strong>Substituições disponíveis:</strong>
                            <?php foreach ($receita["substituidos"] as $item): ?>
                                <p>
                                    <?= h($item["nome"]) ?> pode ser substituído por
                                    <?= h($item["substituto"]) ?>.
                                </p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (count($receita["faltantes"]) > 0): ?>
                        <div class="lista-compras">
                            <strong>Você precisa comprar:</strong>
                            <?php foreach ($receita["faltantes"] as $item): ?>
                                <p>
                                    <?= h($item["nome"]) ?> -
                                    <?= h($item["quantidade"]) ?>
                                    <?= h($item["unidade"]) ?>
                                </p>

                                <?php if (!empty($item["substitutos"])): ?>
                                    <p>
                                        Alternativa possível:
                                        <?= h($item["substitutos"]) ?>
                                    </p>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <button
                        type="button"
                        class="botao-preparo"
                        onclick="mostrarPreparo(<?= (int) $receita["id"] ?>)">
                        Ver modo de preparo
                    </button>

                    <div class="texto-preparo" id="preparo-<?= (int) $receita["id"] ?>">
                        <strong>Modo de preparo:</strong>
                        <p><?= nl2br(h($receita["preparo"])) ?></p>
                    </div>

                </section>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</main>

<script>
function mostrarPreparo(id) {
    const elemento = document.getElementById("preparo-" + id);

    if (elemento.style.display === "block") {
        elemento.style.display = "none";
    } else {
        elemento.style.display = "block";
    }
}
</script>

</body>
</html>