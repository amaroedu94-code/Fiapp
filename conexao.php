<?php
$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "fiapp";
$porta = 3307;

$conexao = new mysqli($servidor, $usuario, $senha, $banco, $porta);

if ($conexao->connect_error) {
    die("Erro na conexão com o banco de dados.");
}

$conexao->set_charset("utf8mb4");
?>
