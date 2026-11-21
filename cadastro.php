
<?php
require "conexao.php";
session_start();

$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST["nome"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $senha = $_POST["senha"] ?? "";
    $confirmacao = $_POST["confirmacao"] ?? "";

    if ($nome == "" || strlen($nome) > 100 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($email) > 150) {
        $erro = "Preencha os dados corretamente.";
    } elseif (strlen($senha) < 8) {
        $erro = "A senha deve ter pelo menos 8 caracteres.";
    } elseif ($senha != $confirmacao) {
        $erro = "As senhas não coincidem.";
    } else {
        $stmt = $conexao->prepare(
            "SELECT id FROM usuarios WHERE email = ?"
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $erro = "Este e-mail já está cadastrado.";
        } else {
            $stmt->close();

            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $conexao->prepare(
                "INSERT INTO usuarios (nome, email, senha)
                 VALUES (?, ?, ?)"
            );
            $stmt->bind_param("sss", $nome, $email, $hash);

            if ($stmt->execute()) {
                $stmt->close();
                header("Location: login.php");
                exit;
            }

            $erro = "Não foi possível realizar o cadastro.";
        }

        if ($stmt) {
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Fiapp</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <main class="container">
        <h1>Crie sua conta no Fiapp</h1>
        <p>Organize seus alimentos de forma simples.</p>

        <?php if ($erro != ""): ?>
            <p class="erro"><?= htmlspecialchars($erro) ?></p>
        <?php endif; ?>

        <form method="POST">
            <label>Nome</label>
            <input type="text" name="nome" maxlength="100" required>

            <label>E-mail</label>
            <input type="email" name="email" maxlength="150" required>

            <label>Senha</label>
            <input type="password" name="senha" minlength="8" required>

            <label>Confirmar senha</label>
            <input type="password" name="confirmacao" minlength="8" required>

            <button type="submit">Cadastrar</button>
        </form>

        <p>Já possui conta? <a href="login.php">Entrar</a></p>
    </main>
</body>
</html>
