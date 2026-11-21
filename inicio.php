<?php
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Início - Fiapp</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="topo">
        <h1>Fiapp</h1>
        <a href="sair.php">Sair</a>
    </header>

    <main class="container">
        <h2>Olá, <?= htmlspecialchars($_SESSION["usuario_nome"]) ?>!</h2>
        <p>Bem-vindo ao seu organizador de alimentos.</p>

        <section class="cartao">
            <h3>Meus alimentos</h3>
            <p>Cadastre e organize os alimentos da sua casa.</p>
            <div class="menu-botoes">
                <a class="botao" href="alimentos.php">Acessar alimentos</a>
                <a class="botao" href="estoque.php">Acessar estoque</a>
                <a class="botao" href="vencimentos.php">Vencimentos</a>
                <a class="botao" href="receitas.php">Sugestões de receitas</a>
            </div>
        </section>
    </main>
</body>
</html>