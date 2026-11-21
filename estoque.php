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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $acao = $_POST["acao"] ?? "";
    $id = filter_var($_POST["id"] ?? 0, FILTER_VALIDATE_INT);

    if ($acao == "excluir" && $id && $id > 0) {
        $stmt = $conexao->prepare(
            "DELETE FROM estoque
            WHERE id_item_estoque = ? AND usuario_id = ?"
        );

        $stmt->bind_param("ii", $id, $usuario_id);
        $stmt->execute();
        $stmt->close();

        header("Location: estoque.php");
        exit;
    }

    if ($acao == "salvar" || $acao == "editar") {
        $alimento_id = filter_var($_POST["alimento_id"] ?? 0, FILTER_VALIDATE_INT);
        $quantidade = filter_var($_POST["quantidade"] ?? "", FILTER_VALIDATE_FLOAT);
        $marca = trim($_POST["marca"] ?? "");
        $data_validade = trim($_POST["data_validade"] ?? "");
        $observacoes = trim($_POST["observacoes"] ?? "");

        $data = DateTime::createFromFormat("Y-m-d", $data_validade);
        $data_valida = $data && $data->format("Y-m-d") === $data_validade;

        if (!$alimento_id || $alimento_id < 1) {
            $erro = "Selecione um alimento válido.";
        } elseif ($quantidade === false || $quantidade <= 0) {
            $erro = "Informe uma quantidade maior que zero.";
        } elseif (strlen($marca) > 100) {
            $erro = "A marca deve ter até 100 caracteres.";
        } elseif (!$data_valida) {
            $erro = "Informe uma data de validade válida.";
        } elseif (strlen($observacoes) > 255) {
            $erro = "As observações devem ter até 255 caracteres.";
        } else {
            $stmt = $conexao->prepare(
                "SELECT id FROM alimentos
                WHERE id = ? AND usuario_id = ?"
            );

            $stmt->bind_param("ii", $alimento_id, $usuario_id);
            $stmt->execute();
            $stmt->store_result();

            $alimento_existe = $stmt->num_rows > 0;
            $stmt->close();

            if (!$alimento_existe) {
                $erro = "O alimento selecionado não pertence à sua conta.";
            } else {
                $id_verificar = $acao == "editar" ? $id : 0;

                $stmt = $conexao->prepare(
                    "SELECT id_item_estoque
                    FROM estoque
                    WHERE usuario_id = ?
                    AND alimento_id = ?
                    AND LOWER(TRIM(COALESCE(marca, ''))) = LOWER(?)
                    AND data_validade = ?
                    AND id_item_estoque <> ?
                    LIMIT 1"
                );

                $stmt->bind_param(
                    "iissi",
                    $usuario_id,
                    $alimento_id,
                    $marca,
                    $data_validade,
                    $id_verificar
                );

                $stmt->execute();
                $stmt->store_result();

                $duplicado = $stmt->num_rows > 0;
                $stmt->close();

                if ($duplicado) {
                    $erro = "Esse alimento com essa marca e validade já está no estoque. Edite a quantidade do item existente.";
                } elseif ($acao == "salvar") {
                    $stmt = $conexao->prepare(
                        "INSERT INTO estoque
                        (usuario_id, alimento_id, quantidade, marca, data_validade, observacoes)
                        VALUES (?, ?, ?, ?, ?, ?)"
                    );

                    $stmt->bind_param(
                        "iidsss",
                        $usuario_id,
                        $alimento_id,
                        $quantidade,
                        $marca,
                        $data_validade,
                        $observacoes
                    );

                    if ($stmt->execute()) {
                        $stmt->close();
                        header("Location: estoque.php");
                        exit;
                    }

                    $erro = "Não foi possível cadastrar o item no estoque.";
                    $stmt->close();

                } elseif ($acao == "editar" && $id && $id > 0) {
                    $stmt = $conexao->prepare(
                        "UPDATE estoque
                        SET alimento_id = ?, quantidade = ?, marca = ?,
                        data_validade = ?, observacoes = ?
                        WHERE id_item_estoque = ? AND usuario_id = ?"
                    );

                    $stmt->bind_param(
                        "idsssii",
                        $alimento_id,
                        $quantidade,
                        $marca,
                        $data_validade,
                        $observacoes,
                        $id,
                        $usuario_id
                    );

                    if ($stmt->execute()) {
                        $stmt->close();
                        header("Location: estoque.php");
                        exit;
                    }

                    $erro = "Não foi possível editar o item do estoque.";
                    $stmt->close();

                } else {
                    $erro = "Não foi possível identificar o item do estoque.";
                }
            }
        }
    }
}

if (isset($_GET["editar"])) {
    $id_editar = filter_var($_GET["editar"], FILTER_VALIDATE_INT);

    if ($id_editar && $id_editar > 0) {
        $stmt = $conexao->prepare(
            "SELECT id_item_estoque, alimento_id, quantidade, marca,
            data_validade, observacoes
            FROM estoque
            WHERE id_item_estoque = ? AND usuario_id = ?"
        );

        $stmt->bind_param("ii", $id_editar, $usuario_id);
        $stmt->execute();

        $stmt->bind_result(
            $eid,
            $ealimento,
            $equantidade,
            $emarca,
            $evalidade,
            $eobservacoes
        );

        if ($stmt->fetch()) {
            $editando = [
                "id" => $eid,
                "alimento_id" => $ealimento,
                "quantidade" => $equantidade,
                "marca" => $emarca,
                "data_validade" => $evalidade,
                "observacoes" => $eobservacoes
            ];
        }

        $stmt->close();
    }
}

$stmt = $conexao->prepare(
    "SELECT id, nome FROM alimentos
    WHERE usuario_id = ?
    ORDER BY nome"
);

$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$stmt->bind_result($fid, $fnome);

$alimentos = [];

while ($stmt->fetch()) {
    $alimentos[] = [
        "id" => $fid,
        "nome" => $fnome
    ];
}

$stmt->close();

$stmt = $conexao->prepare(
    "SELECT e.id_item_estoque, a.nome, e.quantidade,
    a.unidade_medida, e.marca, e.data_validade, e.observacoes
    FROM estoque e
    INNER JOIN alimentos a ON a.id = e.alimento_id
    WHERE e.usuario_id = ? AND a.usuario_id = ?
    ORDER BY e.data_validade"
);

$stmt->bind_param("ii", $usuario_id, $usuario_id);
$stmt->execute();

$stmt->bind_result(
    $eid,
    $enome,
    $equantidade,
    $eunidade,
    $emarca,
    $evalidade,
    $eobservacoes
);

$itens = [];

while ($stmt->fetch()) {
    $itens[] = [
        "id" => $eid,
        "nome" => $enome,
        "quantidade" => $equantidade,
        "unidade" => $eunidade,
        "marca" => $emarca,
        "data_validade" => $evalidade,
        "observacoes" => $eobservacoes
    ];
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estoque - Fiapp</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
</head>

<body>

    <header class="topo">
        <h1>Fiapp</h1>
        <a href="inicio.php">Início</a>
        <a href="alimentos.php">Alimentos</a>
        <a href="vencimentos.php">Vencimentos</a>
        <a href="receitas.php">Sugestões de receitas</a>
        <a href="sair.php">Sair</a>
    </header>

    <main class="container">

        <h2>Meu estoque</h2>

        <?php if ($erro != ""): ?>
            <p class="erro"><?= h($erro) ?></p>
        <?php endif; ?>

        <?php if (count($alimentos) == 0): ?>

            <section class="cartao">
                <p>Você precisa cadastrar um alimento antes de adicioná-lo ao estoque.</p>
                <a class="botao" href="alimentos.php">Cadastrar alimento</a>
            </section>

        <?php else: ?>

            <section class="cartao">

                <h3>
                    <?= $editando ? "Editar item do estoque" : "Adicionar ao estoque" ?>
                </h3>

                <form method="POST">

                    <input type="hidden" name="acao"
                        value="<?= $editando ? "editar" : "salvar" ?>">

                    <?php if ($editando): ?>
                        <input type="hidden" name="id"
                            value="<?= h($editando["id"]) ?>">
                    <?php endif; ?>

                    <label for="alimento_id">Alimento</label>

                    <select name="alimento_id" id="alimento_id" required>
                        <option value="">Selecione um alimento</option>

                        <?php foreach ($alimentos as $alimento): ?>
                            <option value="<?= h($alimento["id"]) ?>"
                                <?= ($editando["alimento_id"] ?? "") == $alimento["id"] ? "selected" : "" ?>>
                                <?= h($alimento["nome"]) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>

                    <label for="quantidade">Quantidade</label>

                    <input
                        type="number"
                        name="quantidade"
                        id="quantidade"
                        min="0.01"
                        step="0.01"
                        required
                        value="<?= h($editando["quantidade"] ?? "") ?>"
                    >

                    <label for="marca">Marca (opcional)</label>

                    <input
                        type="text"
                        name="marca"
                        id="marca"
                        maxlength="100"
                        value="<?= h($editando["marca"] ?? "") ?>"
                    >

                    <label for="data_validade">Data de validade</label>

                    <input
                        type="date"
                        name="data_validade"
                        id="data_validade"
                        required
                        value="<?= h($editando["data_validade"] ?? "") ?>"
                    >

                    <label for="observacoes">Observações (opcional)</label>

                    <textarea
                        name="observacoes"
                        id="observacoes"
                        maxlength="255"
                        rows="3"
                    ><?= h($editando["observacoes"] ?? "") ?></textarea>

                    <button type="submit">
                        <?= $editando ? "Salvar alterações" : "Adicionar ao estoque" ?>
                    </button>

                    <?php if ($editando): ?>
                        <a class="botao secundario" href="estoque.php">Cancelar</a>
                    <?php endif; ?>

                </form>

            </section>

        <?php endif; ?>

        <h3>Itens no estoque</h3>

        <?php if (count($itens) == 0): ?>

            <p>Você ainda não adicionou itens ao estoque.</p>

        <?php else: ?>

            <?php foreach ($itens as $item): ?>

                <section class="cartao">

                    <h3><?= h($item["nome"]) ?></h3>

                    <p>
                        Quantidade:
                        <?= h($item["quantidade"]) ?>
                        <?= h($item["unidade"]) ?>
                    </p>

                    <p>
                        Marca:
                        <?= h($item["marca"] ?: "Não informada") ?>
                    </p>

                    <p>
                        Validade:
                        <?= date("d/m/Y", strtotime($item["data_validade"])) ?>
                    </p>

                    <p>
                        Observações:
                        <?= h($item["observacoes"] ?: "Nenhuma") ?>
                    </p>

                    <div style="display: flex; flex-direction: row; align-items: center; gap: 12px; margin-top: 20px;">

                        <a class="botao"
                           href="estoque.php?editar=<?= h($item["id"]) ?>"
                           style="width: 112px; height: 52px; display: inline-flex; align-items: center; justify-content: center; margin: 0;">
                            Editar
                        </a>

                        <form method="POST"
                              class="form-excluir"
                              style="display: inline-flex; flex-direction: row; width: auto; margin: 0; padding: 0;">

                            <input type="hidden" name="acao" value="excluir">

                            <input type="hidden" name="id"
                                value="<?= h($item["id"]) ?>">

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