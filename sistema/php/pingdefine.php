<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/forms.css">
    <link rel="icon" href="../../Logos/Logo 3.jpg" type="image/png">
    <title>Ecolab</title>
</head>
<body>
    <?php
      session_start();
      if(!isset($_SESSION['id'])){
          header("Location: ../../index.html");
          exit();
      }
        if(!isset($_GET['ip'])){
            $ip = "";
        }else{
            $ip = $_GET['ip'];
        }
        $tel = $_SESSION['tel'];

    ?>
    <div class ="overlayer"></div>
    <nav class="menu">
        <img class="logo" src="../../Logos/Logo 3.jpg" alt="">
        <p class="ecolab">Ecolab</p>
            <ul class="menu-opcoes">
            <li><a href="../index.php" class="menu-item">Home</a></li>
                <li><a href="../html/scandefnet.html" class="menu-item">Escanear</a></li>
            </ul>
            <a href="./sair.php" class="logout-button">Desconectar</a>
    </nav>
    <main>
        <div class="container">
            <form action="./pingmonitor.php" method="post">
                <label for="ip">IP:</label>
                <input type="text" name="ip" id="ip" value="<?php echo $ip ?>">
                <label for="tel">Telefone</label>
                <input type="text" name="tel" id="tel" placeholder="+5511900000000" value="<?php echo $tel ?>">
                <label for="vari">Variação (Ms)</label>
                <input type="number" name="vari" id="vari">
                <label for="inter">Intervalo (Ms)</label>
                <input type="number" name="inter" id="inter">
                <input type="submit" value="Monitorar">
                <p></p>
                <a class="voltar" href="../index.php">Voltar</a>
            </form>
        </div>
    </main>
    <footer>
        &copy; 2024 Ecolab - Com apoio de <a href="https://www.programmers.com.br">Programmers - Beyond IT</a> e <a href="https://www.processtelecom.com.br">Process Telecom</a>.
    </footer>
</body>
</html>