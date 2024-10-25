<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/main.css">
    <link rel="icon" href="../Logos/Logo 3.jpg" type="image/png">
    <title>Ecolab</title>
</head>

<body>
  <?php
  session_start();
    if(!isset($_SESSION['id'])){
        header("Location: ../index.html");
        exit();
    }
    //Acessando o BD
    include_once("./php/conexao.php");
    //Buscando os tipos
    $s1 = "select * from active_hosts;";
    $r1 = mysqli_query($conexao,$s1);
  ?>
  <div class ="overlayer"></div>
    <nav class="menu">
        <img class="logo" src="../Logos/Logo 3.jpg" alt="">
        <p class="ecolab">Ecolab</p>
            <ul class="menu-opcoes">
                <li><a href="./php/pingdefine.php" class="menu-item">Monitorar</a></li>
                <li><a href="./html/scandefnet.html" class="menu-item">Escanear</a></li>
                <li><a href="./php/limpar.php" class="menu-item">Limpar</a></li>
            </ul>
            <a href="./php/sair.php" class="logout-button">Desconectar</a>
    </nav>
    <main class="content">
        <div class="container">
            <div class="dashboardnet">
            <h1 class="title">Rede</h1>
                <?php
                if(mysqli_num_rows($r1) > 0){
                    while($disp = mysqli_fetch_assoc($r1)){
                        $ip = $disp['ip'];
                        $mac = $disp['mac'];
                        $os = $disp['os'];
                        $scantime = $disp['scan_time'];
                        echo '
                        <table class="carddisp">
                        <tbody>
                            <tr>
                                <td colspan="2">';
                                switch ($os) {
                                    case 'Windows':
                                        echo '<img class="logosis" src="../images/sistemas/windows.png" alt=""> ';
                                        break;
                                    case 'Linux-Unix':
                                        echo '<img class="logosis" src="../images/sistemas/linux.png" alt=""> ';
                                        break;
                                    default:
                                        echo '<img class="logosis" src="../images/sistemas/indeterminado.png" alt=""> ';
                                        break;
                                }
                                echo '
                                    <p>Sistema: '.$os.'</p>
                                    <p>MAC: '.$mac.'</p>
                                    <p>IP: '.$ip.'</p>
                                </td>
                            </tr>
                            <tr class="buttonspanel">
                                <td class="custom-button"><a href="./php/servicesscan.php?ip='.$ip.'&os='.$os.'&mac='.$mac.'">Serviços</a></td>
                                <td class="custom-button"><a href="./php/pingdefine.php?ip='.$ip.'">Monitorar</a></td>
                            </tr>
                        </tbody>
                    </table>
                    ';
                    }
                }
                ?>
            </div>
        </div>
    </main>
    <footer>
        &copy; 2024 Ecolab - Com apoio de <a href="https://www.programmers.com.br">Programmers - Beyond IT</a> e <a href="https://www.processtelecom.com.br">Process Telecom</a>.
    </footer>
</body>

</html>
