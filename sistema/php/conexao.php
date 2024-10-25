<?php

//Parametros de conexão
$servidor = "localhost";
$usuario = "root";
$senha = "root";
$bd = "network_monitoring";

//Conexao com o BD
$conexao = mysqli_connect($servidor, $usuario, $senha, $bd);

if (!$conexao){
	die("Falha na conexão com o BD. ". mysqli_connect_error($conexao));
}

?>

