<link rel="stylesheet" href="style.css"/>

<?php
$dbConnection = parse_ini_file("Project.ini");
extract($dbConnection);
$myPdo = new PDO($dsn, $scriptUser, $scriptPassword);

include("./common/Header.php");

if (!isset($_SESSION['UserId'])) {

    $_SESSION['RequestedPage'] = $_SERVER['REQUEST_URI'];

    header('Location: Login.php');

    exit();
}

$selectedAccessability = isset($_POST['accessibilityType']) ? $_POST['accessibilityType'] : '0';

$Title = "";
$Description = "";

$errorMessageTitle = "";
$errorMessageAcceess = "";
$errorMessageDescription = "";

$titleRegex = '/^.{1,256}$/';
$descriptionRegex = '/^.{1,3000}$/';

if (isset($_POST["submit"])) {

    $Title = trim($_POST["inputTitle"]);
    if (empty($Title)) {
        $errorMessageTitle = "Title is required.";
    } elseif (!preg_match($titleRegex, $Title)) {
        $errorMessageTitle = "Entered title is not valid.";
    } else {
        $errorMessageTitle = "";
        $_SESSION["inputTitle"] = $Title;
    }


    $descriptionSelectedAccessibilityRow = trim($_POST["accessibilityType"]);
    if (empty($descriptionSelectedAccessibilityRow)) {
        $errorMessageAcceess = "Type of the accesibility should be selected.";
    } else {
        $errorMessageAcceess = "";
        $_SESSION["accessibilityType"] = $descriptionSelectedAccessibilityRow;
    }




    $Description = trim($_POST["inputDescription"]);
    if (empty($Description)) {
        $errorMessageDescription = "Description is required.";
    } elseif (!preg_match($descriptionRegex, $Description)) {
        $errorMessageDescriptione = "Entered description is not valid.";
    } else {
        $errorMessageDescription = "";
        $_SESSION["inputDescription"] = $Description;
    }
}


if (isset($_POST["submit"]) && empty($errorMessageTitle) && empty($errorMessageAcceess) && empty($errorMessageDescription)) {
    $statement = $myPdo->prepare("INSERT INTO album (Title, Description, Owner_Id, Accessibility_Code) VALUES (?, ?, ?, ?)");
    $statement->execute([$Title, $Description, $_SESSION['UserId'], $descriptionSelectedAccessibilityRow]);
}
?>

<html>
    <head>
        <meta charset="UTF-8">
        <title>Add Album</title>
    </head>
    <body>
        <div class="container">
            <h1>Create New Album</h1>
            <p>Welcome <strong><?php echo $_SESSION['Name']; ?> </strong> (Not you? Change user(<a href="Login.php"> here</a>)</p>

            <form method="post" action="AddAlbum.php" class="postForm" id="myForm" >

                <div class="part">
                    <label class="title">Title:</label>
                    <input class="inputTitle" type="text" name="inputTitle" value="<?php echo $Title; ?>" />
                    <div class="error">
                        <?php echo $errorMessageTitle; ?>
                    </div>
                </div>
                <div class="part">
                    <label class="accessibility" for="accessibility">Accessibility:</label>    

                    <select name="accessibilityType" id="accessibilityType">
                        <option value="0">Select one...</option>
                        <?php
                        $sqlselectedAccessibility = 'SELECT Accessibility_Code, Description FROM accessibility';
                        $stmtselectedAccessibility = $myPdo->prepare($sqlselectedAccessibility);
                        $stmtselectedAccessibility->execute();
                        $allAccessibilities = $stmtselectedAccessibility->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($allAccessibilities as $accessibility) {
                            $codeSelectedAccessibilityRow = $accessibility['Accessibility_Code'] ?? null;
                            $descriptionSelectedAccessibilityRow = $accessibility['Description'] ?? null;


                            $selected = (isset($_POST['accessibilityType']) && $_POST['accessibilityType'] == $codeSelectedAccessibilityRow) ? 'selected' : '';

                            echo '<option value="' . $codeSelectedAccessibilityRow . '" ' . $selected . '>' . $descriptionSelectedAccessibilityRow . '</option>';
                        }
                        ?>
                    </select>

                    <div class="error">
                        <?php echo $errorMessageAcceess; ?>
                    </div>
                </div>

                <div class = "part">
                    <label class = "lableDescription"  >Description:</label>
                    <textarea class="inputDescription" style="vertical-align: top;" name="inputDescription"><?php echo $Description; ?></textarea>
                    <div class="error">
                        <?php echo $errorMessageDescription; ?>
                    </div>
                </div>

                <div class="addAlbumButton">
                    <div class="button">
                        <button class="submitButton" type="submit" name="submit" value="submit">Submit </button>
                    </div>
                    <div class="button">
                        <button class="submitButton" type="submit" name="clear" value="clear">Clear</button>
                    </div>
                </div>
            </form>
        </div>
    </body>
</html>

<?php include('./common/footer.php'); ?>