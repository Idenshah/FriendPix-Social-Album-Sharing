<?php include("./common/header.php"); ?>

<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <?php
        session_start();

        session_destroy();

        header('Location: Index.php');
        exit();
        ?>
    </body>
</html>

<?php include('./common/footer.php'); ?>