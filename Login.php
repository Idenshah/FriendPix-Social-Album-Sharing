<?php 

    include("./common/header.php"); 

    include_once 'Functions.php';
    include_once 'EntityClassLib.php'; 

    $config = parse_ini_file('Project.ini', true);
    
    extract($_POST);
    
    $loginErrorMsg = '';
    
    $dsn = $config['database connection']['dsn'];
    $scriptUser = $config['database connection']['scriptUser'];
    $scriptPassword = $config['database connection']['scriptPassword'];

    $connection = new PDO($dsn, $scriptUser, $scriptPassword);
    
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $formErrors = handleLoginFormSubmission($connection);
    }
?>

<!DOCTYPE html>
<html lang="en">
    
    <head>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.6/dist/css/bootstrap.min.css">
        <title>Login</title>
    </head>
    <body>        
        <div class="container">
        <h1>Login</h1>

        <p>You need to <a href='NewUser.php'>sign up</a> if you are a new user</p>

        <?php if (isset($formErrors['login'])) : ?>
            <p class="text-danger"><?php echo $formErrors['login']; ?></p>
        <?php endif; ?>

        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <table>
                <tr>
                    <th><label for="UserId">User ID</label></th>
                    <td>
                        <input type="text" class="form-control" id="UserId" name="UserId">
                    </td>
                    <td>
                        <?php if (isset($formErrors['UserId'])) echo '<p class="text-danger">' . $formErrors['UserId'] . '</p>'; ?>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <th><label for="Password">Password</label></th>
                    <td>
                        <input type="Password" class="form-control" id="Password" name="Password">
                    </td>
                    <td>
                        <?php if (isset($formErrors['Password'])) echo '<p class="text-danger">' . $formErrors['Password'] . '</p>'; ?>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="3">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </td>
                </tr>
            </table>
        </form>
    </div>
    </body>
</html>

<?php include('./common/footer.php'); ?>