
<?php
require "conexao.php";
session_start();

if (isset($_SESSION["usuario_id"])) {
    header("Location: inicio.php");
    exit;
}

$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"] ?? "");
    $senha = $_POST["senha"] ?? "";

    $stmt = $conexao->prepare(
        "SELECT id, nome, senha FROM usuarios WHERE email = ?"
    );
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $nome, $hash);

    if ($stmt->num_rows == 1 && $stmt->fetch()
        && password_verify($senha, $hash)) {
        session_regenerate_id(true);
        $_SESSION["usuario_id"] = $id;
        $_SESSION["usuario_nome"] = $nome;

        header("Location: inicio.php");
        exit;
    } else {
        $erro = "E-mail ou senha incorretos.";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Fiapp</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <main class="container">
        <h1>Fiapp</h1>
        <p>Entre para organizar seus alimentos.</p>

        <?php if ($erro != ""): ?>
            <p class="erro"><?= htmlspecialchars($erro) ?></p>
        <?php endif; ?>

        <form method="POST">
            <label>E-mail</label>
            <input type="email" name="email" required>

            <label>Senha</label>
            <input type="password" name="senha" required>

            <button type="submit">Entrar</button>
        </form>

        <p>Não tem conta? <a href="cadastro.php">Cadastre-se</a></p>
    </main>
</body>
</html>
