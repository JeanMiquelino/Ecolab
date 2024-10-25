<?php
include_once("../../sistema/php/conexao.php");

$user = $_POST['user'];
$pass = $_POST['pass'];

// Prepare a consulta segura
$s1 = $conexao->prepare("SELECT * FROM users WHERE username = ? AND pass = ?");
$s1->bind_param("ss", $user, $pass);
$s1->execute();

$r1 = $s1->get_result();

// Verifica se o usuário foi encontrado
if ($r1->num_rows == 0) {
    header("Location: ./loginpage.php?error=Usuario ou senha invalidos");
    exit();
}

// Obtém o telefone
$telefone = $r1->fetch_assoc()['tel'];

// Inicia a sessão e define o telefone na sessão
session_start();
$_SESSION['tel'] = $telefone;
$_SESSION['id'] = random_int(0, 1000000);

// Redireciona para a página principal
header("Location: ../../sistema/index.php");
?>
