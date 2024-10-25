<?php
  session_start();
  if(!isset($_SESSION['id'])){
      header("Location: ../../index.html");
      exit();
  }
// Defina a URL do endpoint
$url = "http://localhost:2511/ping_monitor";
$ip = $_POST['ip'];
$tel = $_POST['tel'];
$vari = (float)$_POST['vari'];  // Converte para float
$inter = (int)$_POST['inter'];   // Converte para inteiro

// Dados para enviar no corpo da requisição
$data = array(
    "ip" => "$ip",  // Substitua pelo IP que deseja monitorar
    "sms_to" => "$tel",  // Substitua pelo seu número de celular (com o código do país)
    "alert_variation" => $vari,  // Variação de alerta do ping
    "interval" => $inter  // Intervalo de tempo entre os pings
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
echo $response;
header("Location: ../index.php");
?>
