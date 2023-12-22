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

include_once 'Functions.php';
include_once 'EntityClassLib.php';

$ID = "";
$status = "";

$errorMessageID = "";
$MessageId = "";

if (isset($_POST["submit"])) {
    $ID = trim($_POST["inputID"]);
    if (empty($ID)) {
        $errorMessageID = "Please insert the ID.";
        $MessageId = "";
    } else {
        $sql = "SELECT COUNT(*) FROM User WHERE UserId = ?";
        $stmt = $myPdo->prepare($sql);
        $stmt->execute([$ID]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $sqlStatus = "SELECT COUNT(*) FROM Friendship WHERE (Friend_RequesterId = :userSender AND Friend_RequesteeId = :userReceiver AND Status = 'accepted')";
            $stmtStatus = $myPdo->prepare($sqlStatus);
            $stmtStatus->bindParam(':userSender', $_SESSION['UserId']);
            $stmtStatus->bindParam(':userReceiver', $ID);
            $stmtStatus->execute();
            $countAccepted = $stmtStatus->fetchColumn();

            if ($countAccepted > 0) {

                $MessageId = "You are already friends.";
            } else {
                $sqlIdName = "SELECT UserId, Name FROM user WHERE UserId = :userID";
                $stmtIdName = $myPdo->prepare($sqlIdName);
                $stmtIdName->bindParam(':userID', $ID);
                $stmtIdName->execute();
                $rowIdName = $stmtIdName->fetch(PDO::FETCH_ASSOC);
                $_SESSION['friendName'] = $rowIdName['Name'];

                $errorMessageId = "";
                $MessageId = "A friendship request has been sent to " . $_SESSION['friendName'] . " with ID: " . $ID . ". Once " . $_SESSION['friendName'] . " accepts your request, you and " . $_SESSION['friendName'] . " will be friends and be able to view each other's shared albums.";
                $_SESSION["inputID"] = $ID;
                $status = "request";  // Update $status here
            }
        } else {
            $errorMessageID = "Entered ID is not available!";
            $MessageId = "";
        }
    }
}


if (isset($_POST["submit"]) && empty($errorMessageID)) {
    if ($status != "") {


        $checkStatement = $myPdo->prepare("SELECT COUNT(*) FROM friendship WHERE (Friend_RequesterId = ? AND Friend_RequesteeId = ?) ");
        $checkStatement->execute([$ID, $_SESSION['UserId']]);
        $rowCountA = $checkStatement->fetchColumn();

        if ($rowCountA > 0) {
            $updateStatement = $myPdo->prepare("UPDATE friendship SET Status = 'accepted' WHERE (Friend_RequesterId = ? AND Friend_RequesteeId = ?) OR (Friend_RequesterId = ? AND Friend_RequesteeId = ?)");
            $updateStatement->execute([$_SESSION['UserId'], $ID, $ID, $_SESSION['UserId']]);
            $MessageId = "Friendship request accepted!";
        } else {

            $checkStatement = $myPdo->prepare("SELECT COUNT(*) FROM friendship WHERE Friend_RequesterId = ? AND Friend_RequesteeId = ? AND Status = ?");
            $checkStatement->execute([$_SESSION['UserId'], $ID, $status]);
            $rowCountB = $checkStatement->fetchColumn();

            if ($rowCountB == 0) {
                if ($_SESSION ['UserId'] != $ID) {
                    $statementFriensdShip = $myPdo->prepare("INSERT INTO friendship (Friend_RequesterId, Friend_RequesteeId, Status) VALUES (?, ?, ?)");
                    $statementFriensdShip->execute([$_SESSION ['UserId'], $ID, $status]);
                } else {
                    $errorMessageID = "";
                    $MessageId = "You can not add yourself!";
                }
            } else {
                $MessageId = "Friendship request already sent!";
                
            }
        }
    }
}
?>


<html>
    <head>
        <meta charset="UTF-8">
        <title>Add Friend</title>
    </head>
    <body>
        <div class="container">
            <h1>Add Friend</h1>
            <p>Welcome <strong><?php echo $_SESSION['Name']; ?> </strong> (Not you? Change user(<a href="Login.php"> here</a>)</p>
            <p>Enter the ID of the user you want to be friend with</p>
            <form method="post" action="AddFriend.php" class="postForm" id="myForm" >
                <div class="partID">
                    <label class="title">ID:</label>
                    <input class="inputID" type="text" name="inputID" value="<?php echo $ID; ?>" />
                </div>
                <div class="partID">
                    <div class="error">
                        <?php echo $errorMessageID; ?>
                    </div>
                </div>
                <div class="partID">
                    <div class="error">
                        <?php echo $MessageId; ?>
                    </div>
                </div
                <div class="addAlbumButton">
                    <div class="button">
                        <button class="submitButton" type="submit" name="submit" value="submit">Submit </button>
                    </div>
                </div>
            </form>
        </div>
    </body>
</html>


<?php include('./common/footer.php'); ?>
  