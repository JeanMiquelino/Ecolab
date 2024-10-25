<?php
  session_start();
  if(!isset($_SESSION['id'])){
      header("Location: ../../index.html");
      exit();
  }
include_once("conexao.php");
$ip = $_GET['ip'];
$os = $_GET['os'];
$mac = $_GET['mac'];
$s1 = "SELECT DATE_FORMAT(MAX(scan_time), '%Y-%m-%d %H:%i:00') AS max_time FROM open_ports WHERE ip = '$ip'";
$result_max_time = mysqli_query($conexao, $s1);
$max_time_row = mysqli_fetch_assoc($result_max_time);
$max_time = $max_time_row['max_time'];

// Buscar todos os serviços com scan_time no mesmo minuto que o max_time
$s2 = "SELECT * FROM open_ports WHERE ip = '$ip' AND DATE_FORMAT(scan_time, '%Y-%m-%d %H:%i:00') = '$max_time'";
$r1 = mysqli_query($conexao, $s2);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ecolab</title>
    <link rel="stylesheet" href="../css/services.css">
    <link rel="icon" href="../../Logos/Logo 3.jpg" type="image/png">
</head>
<div class ="overlayer"></div>
<body>
    <nav class="menu">
        <img class="logo" src="../../Logos/Logo 3.jpg" alt="">
        <p class="ecolab">Ecolab</p>
            <ul class="menu-opcoes">
                <li><a href="../index.php" class="menu-item">Home</a></li>
                <li><a href="./pingdefine.php" class="menu-item">Monitorar</a></li>
                <li><a href="../html/scandefnet.html" class="menu-item">Escanear</a></li>
            </ul>
            <a href="./sair.php" class="logout-button">Desconectar</a>
    </nav>
    <main>
        <div class="container">
        <h2>IP: <?php echo $ip ?></h2>
        <h3>MAC: <?php echo $mac ?></h3>
        <?php
        switch ($os) {
                                    case 'Windows':
                                        echo '<img class="logosisser" src="../../images/sistemas/windows.png" alt=""> ';
                                        break;
                                    case 'Linux-Unix':
                                        echo '<img class="logosisser" src="../../images/sistemas/linux.png" alt=""> ';
                                        break;
                                    default:
                                        echo '<img class="logosisser" src="../../images/sistemas/indeterminado.png" alt=""> ';
                                        break;
                                }
                                ?>
        <h3><?php echo $os ?></h3>
        <h2>Serviços Ativos:</h2>
            <?php
                if(mysqli_num_rows($r1)>0){
                    echo '<ul>';
                    while($disp = mysqli_fetch_assoc($r1)){
                        $port = $disp['port'];
                        $service = $disp['service'];
                        echo '
                        <li>
                            <p>'.$port.' - '.$service.'</p>
                            <img class="iconservice" src="../../images/servicos/'.$service.'.png">
                        </li>
                        ';
                    }
                echo '</ul>';
                }else{
                    echo '<p>Nenhum serviço ativo encontrado.</p>';
                }
            ?>
            
        </div>
    </main>
    <footer>
        &copy; 2024 Ecolab - Com apoio de <a href="https://www.programmers.com.br">Programmers - Beyond IT</a> e <a href="https://www.processtelecom.com.br">Process Telecom</a>.
    </footer>
</body>
</html>