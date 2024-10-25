<?php
  session_start();
  if(!isset($_SESSION['id'])){
      header("Location: ../../index.html");
      exit();
  }
include_once("conexao.php");
mysqli_query($conexao,"truncate table active_hosts;");
header("Location: ../index.php");
?>