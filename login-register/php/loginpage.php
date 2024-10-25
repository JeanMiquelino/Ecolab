<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/main.css">
    <link rel="icon" href="../../Logos/Logo3.jpg" type="image/png">
    <title>Ecolab</title>
</head>
<body>
    <form action="./login.php" method="post">
        <img src="../../Logos/Logo 3.jpg" alt="">
        <h1>login</h1>
        <p class="error">
        <?php
            if(isset($_GET['error'])){
                echo $_GET['error'];
            }
        ?>
        </p>
        <label for="user">Usuário:</label>
        <input type="text" name="user" id="user">
        <label for="pass">Senha:</label>
        <input type="password" name="pass" id="pass">
        <input type="submit" value="Entrar">
    </form>
</body>
</html>