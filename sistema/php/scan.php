<?php
  session_start();
  if(!isset($_SESSION['id'])){
      header("Location: ../../index.html");
      exit();
  }
// Defina a URL do endpoint
$url = "http://localhost:2511/discover_hosts";
$rede = $_POST['rede'];
// Dados para enviar no corpo da requisição
$data = array(
    "network" => "$rede" // Substitua pela rede que você quer escanear
);

// Inicie o curl
$ch = curl_init($url);

// Configurações da requisição curl
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

// Execute a requisição
$response = curl_exec($ch);

// Feche o curl
curl_close($ch);

// Exiba a resposta
header("Location: ../index.php");

?>
