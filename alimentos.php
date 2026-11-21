
<?php
require "conexao.php";
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION["usuario_id"];
$erro = "";
$editando = null;

function h($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

$categorias = [
    "Frutas",
    "Verduras",
    "Legumes",
    "Carnes",
    "Laticínios",
    "Grãos",
    "Outros"
];

$unidades = [
    "kg",
    "g",
    "litro",
    "ml",
    "unidade",
    "dúzia",
    "pacote"
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $acao = $_POST["acao"] ?? "";
    $id = filter_var($_POST["id"] ?? 0, FILTER_VALIDATE_INT);

    if ($acao == "excluir" && $id && $id > 0) {
        $stmt = $conexao->prepare(
            "DELETE FROM alimentos WHERE id = ? AND usuario_id = ?"
        );

        $stmt->bind_param("ii", $id, $usuario_id);
        $stmt->execute();
        $stmt->close();

        header("Location: alimentos.php");
        exit;
    }

    if ($acao == "salvar" || $acao == "editar") {
        $nome = trim($_POST["nome"] ?? "");
        $categoria = trim($_POST["categoria"] ?? "");
        $unidade_medida = trim($_POST["unidade_medida"] ?? "");
        $marca_preferencia = trim($_POST["marca_preferencia"] ?? "");

        if ($nome == "") {
            $erro = "Informe o nome do alimento.";
        } elseif (!in_array($categoria, $categorias, true)) {
            $erro = "Selecione uma categoria válida.";
        } elseif (!in_array($unidade_medida, $unidades, true)) {
            $erro = "Selecione uma unidade de medida válida.";
        } elseif (strlen($nome) > 100) {
            $erro = "O nome deve ter até 100 caracteres.";
        } elseif (strlen($marca_preferencia) > 100) {
            $erro = "A marca deve ter até 100 caracteres.";
        } elseif ($acao == "editar" && (!$id || $id <= 0)) {
            $erro = "Alimento inválido.";
        } else {
            $id_verificar = $acao == "editar" ? $id : 0;

            $stmt = $conexao->prepare(
                "SELECT id FROM alimentos
                WHERE usuario_id = ?
                AND LOWER(TRIM(nome)) = LOWER(?)
                AND id <> ?
                LIMIT 1"
            );

            $stmt->bind_param(
                "isi",
                $usuario_id,
                $nome,
                $id_verificar
            );

            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $erro = "Esse alimento já está cadastrado. Altere a quantidade no estoque.";
            }

            $stmt->close();

            if ($erro == "") {
                if ($acao == "salvar") {
                    $stmt = $conexao->prepare(
                        "INSERT INTO alimentos
                        (usuario_id, nome, categoria, unidade_medida, marca_preferencia)
                        VALUES (?, ?, ?, ?, ?)"
                    );

                    $stmt->bind_param(
                        "issss",
                        $usuario_id,
                        $nome,
                        $categoria,
                        $unidade_medida,
                        $marca_preferencia
                    );

                    if ($stmt->execute()) {
                        $stmt->close();
                        header("Location: alimentos.php");
                        exit;
                    }

                    $erro = "Não foi possível cadastrar o alimento.";
                    $stmt->close();

                } elseif ($acao == "editar") {
                    $stmt = $conexao->prepare(
                        "UPDATE alimentos
                        SET nome = ?, categoria = ?, unidade_medida = ?, marca_preferencia = ?
                        WHERE id = ? AND usuario_id = ?"
                    );

                    $stmt->bind_param(
                        "ssssii",
                        $nome,
                        $categoria,
                        $unidade_medida,
                        $marca_preferencia,
                        $id,
                        $usuario_id
                    );

                    if ($stmt->execute()) {
                        $stmt->close();
                        header("Location: alimentos.php");
                        exit;
                    }

                    $erro = "Não foi possível editar o alimento.";
                    $stmt->close();
                }
            }
        }
    }
}

if (isset($_GET["editar"])) {
    $id_editar = filter_var($_GET["editar"], FILTER_VALIDATE_INT);

    if ($id_editar && $id_editar > 0) {
        $stmt = $conexao->prepare(
            "SELECT id, nome, categoria, unidade_medida, marca_preferencia
            FROM alimentos
            WHERE id = ? AND usuario_id = ?"
        );

        $stmt->bind_param("ii", $id_editar, $usuario_id);
        $stmt->execute();

        $stmt->bind_result(
            $eid,
            $enome,
            $ecategoria,
            $eunidade,
            $emarca
        );

        if ($stmt->fetch()) {
            $editando = [
                "id" => $eid,
                "nome" => $enome,
                "categoria" => $ecategoria,
                "unidade_medida" => $eunidade,
                "marca_preferencia" => $emarca
            ];
        }

        $stmt->close();
    }
}

$stmt = $conexao->prepare(
    "SELECT id, nome, categoria, unidade_medida, marca_preferencia
    FROM alimentos
    WHERE usuario_id = ?
    ORDER BY nome"
);

$stmt->bind_param("i", $usuario_id);
$stmt->execute();

$stmt->bind_result(
    $id,
    $nome,
    $categoria,
    $unidade,
    $marca
);

$alimentos = [];

while ($stmt->fetch()) {
    $alimentos[] = [
        "id" => $id,
        "nome" => $nome,
        "categoria" => $categoria,
        "unidade_medida" => $unidade,
        "marca_preferencia" => $marca
    ];
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alimentos - Fiapp</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
</head>

<body>

    <header class="topo">
        <h1>Fiapp</h1>
        <a href="inicio.php">Início</a>
        <a href="estoque.php">Estoque</a>
        <a href="vencimentos.php">Vencimentos</a>
        <a href="receitas.php">Sugestões de receitas</a>
        <a href="sair.php">Sair</a>
    </header>

    <main class="container">

        <h2>Meus alimentos</h2>

        <?php if ($erro != ""): ?>
            <p class="erro"><?= h($erro) ?></p>
        <?php endif; ?>

        <section class="cartao">

            <h3>
                <?= $editando ? "Editar alimento" : "Cadastrar alimento" ?>
            </h3>

            <form method="POST">

                <input type="hidden" name="acao"
                    value="<?= $editando ? "editar" : "salvar" ?>">

                <?php if ($editando): ?>
                    <input type="hidden" name="id"
                        value="<?= h($editando["id"]) ?>">
                <?php endif; ?>

                <label for="nome">Nome do alimento</label>

                <input
                    type="text"
                    name="nome"
                    id="nome"
                    maxlength="100"
                    required
                    value="<?= h($editando["nome"] ?? "") ?>"
                >

                <label for="categoria">Categoria</label>

                <select name="categoria" id="categoria" required>
                    <option value="">Selecione uma categoria</option>

                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= h($cat) ?>"
                            <?= ($editando["categoria"] ?? "") == $cat ? "selected" : "" ?>>
                            <?= h($cat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="unidade_medida">Unidade de medida</label>

                <select name="unidade_medida" id="unidade_medida" required>
                    <option value="">Selecione uma unidade</option>

                    <?php foreach ($unidades as $unidade): ?>
                        <option value="<?= h($unidade) ?>"
                            <?= ($editando["unidade_medida"] ?? "") == $unidade ? "selected" : "" ?>>
                            <?= h($unidade) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="marca_preferencia">Marca preferida</label>

                <input
                    type="text"
                    name="marca_preferencia"
                    id="marca_preferencia"
                    maxlength="100"
                    value="<?= h($editando["marca_preferencia"] ?? "") ?>"
                >

                <button type="submit">
                    <?= $editando ? "Salvar alterações" : "Cadastrar alimento" ?>
                </button>

                <?php if ($editando): ?>
                    <a class="botao secundario" href="alimentos.php">Cancelar</a>
                <?php endif; ?>

            </form>

        </section>

        <h3>Alimentos cadastrados</h3>

        <?php if (count($alimentos) == 0): ?>

            <p>Você ainda não cadastrou alimentos.</p>

        <?php else: ?>

            <?php foreach ($alimentos as $alimento): ?>

                <section class="cartao">

                    <h3><?= h($alimento["nome"]) ?></h3>

                    <p>
                        Categoria:
                        <?= h($alimento["categoria"] ?: "Não informada") ?>
                    </p>

                    <p>
                        Unidade de medida:
                        <?= h($alimento["unidade_medida"] ?: "Não informada") ?>
                    </p>

                    <p>
                        Marca preferida:
                        <?= h($alimento["marca_preferencia"] ?: "Não informada") ?>
                    </p>

                    <div style="display: flex; flex-direction: row; align-items: center; gap: 12px; margin-top: 20px;">

                        <a class="botao"
                           href="alimentos.php?editar=<?= h($alimento["id"]) ?>"
                           style="width: 112px; height: 52px; display: inline-flex; align-items: center; justify-content: center; margin: 0;">
                            Editar
                        </a>

                        <form method="POST"
                              class="form-excluir"
                              style="display: inline-flex; flex-direction: row; width: auto; margin: 0; padding: 0;">

                            <input type="hidden" name="acao" value="excluir">

                            <input type="hidden" name="id"
                                value="<?= h($alimento["id"]) ?>">

                            <button type="submit" class="perigo"
                                    style="width: 112px; height: 52px; display: inline-flex; align-items: center; justify-content: center; margin: 0;">
                                Excluir
                            </button>

                        </form>

                    </div>

                </section>

            <?php endforeach; ?>

        <?php endif; ?>

    </main>

</body>
</html>
