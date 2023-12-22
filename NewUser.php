<?php 

    include("./common/header.php"); 
   
    include_once "Functions.php";
    include_once "EntityClassLib.php"; 	
    
    $config = parse_ini_file('Project.ini', true);
    
    extract($_POST);
    
    $dsn = $config['database connection']['dsn'];
    $scriptUser = $config['database connection']['scriptUser'];
    $scriptPassword = $config['database connection']['scriptPassword'];

    $connection = new PDO($dsn, $scriptUser, $scriptPassword);
    
    if(isset($regSubmit))
    {
        
        $password = $txtPassword;
        //$password = hash("sha256", $txtPassword);
        try {
            addNewUser($txtId, $txtName, null, $password);
            header("Location: Login.php");
            exit();
        }
        catch (Exception $e)
        {
            die("The system is currently not available, try again later");
        }
        
    }
    
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formErrors = handleNewUserFormSubmission($connection);
    }

?>

<!DOCTYPE html>

<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <div class="container">
    <h1>New User</h1>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <table>
            <tr>
                <th><label for="UserId">User ID</label></th>
                <td>
                    <input type="text" class="form-control" id="UserId" name="UserId"
                           value="<?php echo isset($_POST['UserId']) ? $_POST['UserId'] : ''; ?>">
                </td>
                <td>
                    <?php if(isset($formErrors['UserId'])) echo '<p class="text-danger">'.$formErrors['UserId'].'</p>'; ?>
                </td>
            </tr>
            <tr><td>&nbsp;</td></tr>
            <tr>
                <th><label for="Name">Name</label></th>
                <td>
                    <input type="text" class="form-control" id="Name" name="Name"
                           value="<?php echo isset($_POST['Name']) ? $_POST['Name'] : ''; ?>">
                </td>
                <td>
                    <?php if(isset($formErrors['Name'])) echo '<p class="text-danger">'.$formErrors['Name'].'</p>'; ?>
                </td>
            </tr>
            <tr><td>&nbsp;</td></tr>
            <tr>
                <th><label for="Phone">Phone Number</label></th>
                <td>
                    <input type="text" class="form-control" id="Phone" name="Phone"
                           value="<?php echo isset($_POST['Phone']) ? $_POST['Phone'] : ''; ?>">
                </td>
                <td>
                    <?php if(isset($formErrors['Phone'])) echo '<p class="text-danger">'.$formErrors['Phone'].'</p>'; ?>
                </td>
            </tr>
            <tr><td>&nbsp;</td></tr>
            <tr>
                <th><label for="Password">Password</label></th>
                <td>
                    <input type="Password" class="form-control" id="Password" name="Password"
                           value="<?php echo isset($_POST['Password']) ? $_POST['Password'] : ''; ?>">
                </td>
                <td>
                    <?php if(isset($formErrors['Password'])) echo '<p class="text-danger">'.$formErrors['Password'].'</p>'; ?>
                </td>
            </tr>
            <tr><td>&nbsp;</td></tr>
            <tr>
                <th><label for="confirmPassword">Confirm Password</label></th>
                <td>
                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword"
                           value="<?php echo isset($_POST['confirmPassword']) ? $_POST['confirmPassword'] : ''; ?>">
                </td>
                <td>
                    <?php if(isset($formErrors['confirmPassword'])) echo '<p class="text-danger">'.$formErrors['confirmPassword'].'</p>'; ?>
                </td>
            </tr>
            <tr><td>&nbsp;</td></tr>
            <tr>
                <td style='text-align: center' colspan='3'>
                    <button type="submit" class="btn btn-primary">Submit</button>
                    <button type="button" class="btn btn-primary" onclick="clearForm()">Clear</button>
                </td>
            </tr>
        </table>
    </form>
</div>

<?php include('./common/footer.php'); ?>

<script>
    function clearForm() {
        document.getElementById("UserId").value = "";
        document.getElementById("Name").value = "";
        document.getElementById("Phone").value = "";
        document.getElementById("Password").value = "";
        document.getElementById("confirmPassword").value = "";
    }
</script>