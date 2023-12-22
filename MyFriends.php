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


$isChecked = false;

// find friends
$sqlFindFriend = "SELECT
    CONCAT(
        REPLACE(Friendship.Friend_RequesterId, :user, ''),
        REPLACE(Friendship.Friend_RequesteeId, :user, '')
    ) AS CombinedIds, Friendship.Status
  
FROM
    Friendship
JOIN
    User AS Requester ON Friendship.Friend_RequesterId = Requester.UserId
JOIN
    User AS Requestee ON Friendship.Friend_RequesteeId = Requestee.UserId
JOIN
    FriendshipStatus ON Friendship.Status = FriendshipStatus.Status_Code
WHERE
    (:user IN (Requester.UserId, Requestee.UserId)) AND Friendship.Status = 'accepted'";

$stmtFindFriend = $myPdo->prepare($sqlFindFriend);
$stmtFindFriend->bindParam(':user', $_SESSION['UserId']);
$stmtFindFriend->execute();
$allFriends = $stmtFindFriend->fetchAll(PDO::FETCH_ASSOC);

//Remove
if (isset($_POST["submit"]) && $_POST["submit"] == "DefriendButton") {
    if (isset($_POST["defriend"]) && is_array($_POST["defriend"])) {
        foreach ($_POST["defriend"] as $friendToRemove) {
            $sqlDeFriend = "DELETE FROM friendship
                            WHERE
                            (Friend_RequesterId = :user  AND Friend_RequesteeId = :friendToRemove)
                             OR (Friend_RequesterId = :friendToRemove AND Friend_RequesteeId = :user)
                             AND Friendship.Status = 'accepted'";

            $stmtDeFriend = $myPdo->prepare($sqlDeFriend);
            $stmtDeFriend->bindParam(':user', $_SESSION['UserId']);
            $stmtDeFriend->bindParam(':friendToRemove', $friendToRemove);
            $stmtDeFriend->execute();
        }
    }
    header('Location: MyFriends.php');

    exit();
}


//Add
if (isset($_POST["submit"]) && $_POST["submit"] == "accept") {
    if (isset($_POST["reqfriend"]) && is_array($_POST["reqfriend"])) {
        foreach ($_POST["reqfriend"] as $friendToAdd) {
            $sqlAddFriend = "update friendship set Friendship.Status = 'accepted' 
                            WHERE
                            (Friend_RequesterId = :user  AND Friend_RequesteeId = :friendToAdd)
                             OR (Friend_RequesterId = :friendToAdd AND Friend_RequesteeId = :user)
                             AND Friendship.Status = 'request'";

            $stmtAddFriend = $myPdo->prepare($sqlAddFriend);
            $stmtAddFriend->bindParam(':user', $_SESSION['UserId']);
            $stmtAddFriend->bindParam(':friendToAdd', $friendToAdd);
            $stmtAddFriend->execute();
        }
    }
    header('Location: MyFriends.php');

    exit();
}

//Deny
if (isset($_POST["submit"]) && $_POST["submit"] == "deny") {
    if (isset($_POST["reqfriend"]) && is_array($_POST["reqfriend"])) {
        foreach ($_POST["reqfriend"] as $friendToDeny) {
            $sqlDenyFriend = "Delete FROM friendship
                            WHERE
                            (Friend_RequesterId = :user  AND Friend_RequesteeId = :friendToDeny)
                             OR (Friend_RequesterId = :friendToDeny AND Friend_RequesteeId = :user)
                             AND Friendship.Status = 'request'";

            $stmtDenyFriend = $myPdo->prepare($sqlDenyFriend);
            $stmtDenyFriend->bindParam(':user', $_SESSION['UserId']);
            $stmtDenyFriend->bindParam(':friendToDeny', $friendToDeny);
            $stmtDenyFriend->execute();
        }
    }
    header('Location: MyFriends.php');

    exit();
}


?>


<html>
    <head>
        <meta charset="UTF-8">
        <title>My Friends</title>
    </head>
    <body>
        <div class="container">
            <h1>My Friends</h1>
            <p>Welcome <strong><?php echo $_SESSION['Name']; ?> </strong> (Not you? Change user(<a href="Login.php"> here</a>)</p>

            <form method="post" action="MyFriends.php" class="postForm" id="myForm">
                <div class ="part">
                    <p>Friends:</p>  <div class="tagFriend"> <a href="AddFriend.php">Add Friend</a> </div>
                </div>

                <table class="table">
                    <tr>
                        <th>Name</th>
                        <th>Shared Albums</th>
                        <th>Defriend</th>
                        <th></th>
                    </tr>
                    <?php
                    foreach ($allFriends as $friend) {
                        echo '<tr class="tableRow">';
                        echo '<td>' . $friend['CombinedIds'] . '</td>';

                        $sqlFindShared = "SELECT COUNT(accessibility_code) AS sharedCount
                        FROM Album
                        WHERE owner_Id = :friendName AND accessibility_code IS NOT NULL AND accessibility_code != ''";
                        $stmtFindShared = $myPdo->prepare($sqlFindShared);
                        $stmtFindShared->bindParam(':friendName', $friend['CombinedIds']);
                        $stmtFindShared->execute();
                        $sharedCount = $stmtFindShared->fetchColumn();

                        echo '<td>' . $sharedCount . '</td>';
                        echo '<td><input type="checkbox" name="defriend[]" value="' . $friend['CombinedIds'] . '" ' . ($isChecked ? 'checked' : '') . '></td>';
                        echo '</tr>';
                    }
                    ?>
                </table>

                <div class="defriend">
                    <div class="button">
                        <button class="submitButton" type="submit" name="submit" value="DefriendButton" onclick="return confirmDelete()">Defriend Selected </button>
                    </div>

                </div>

                <div class ="part">
                    <p>Friends Request:</p>
                </div>

                <!--                find request-->
                <?php
                $sqlFriendRequest = "select Friend_RequesterId from friendship where(Friend_RequesteeId = :userReq AND Status = 'request')";
                $stmtFriendRequest = $myPdo->prepare($sqlFriendRequest);
                $stmtFriendRequest->bindParam(':userReq', $_SESSION['UserId']);
                $stmtFriendRequest->execute();
                $allFriendRequests = $stmtFriendRequest->fetchAll(PDO::FETCH_ASSOC);
                ?>


                <table class="table">
                    <tr>
                        <th>Name</th>
                        <th>Accept or Deny</th>
                        <th></th>
                    </tr>
                    <?php
                    foreach ($allFriendRequests as $friendRequest) {
                        echo '<tr class="tableRow">';
                        echo '<td>' . $friendRequest['Friend_RequesterId'] . '</td>';
                         echo '<td><input type="checkbox" name="reqfriend[]" value="' . $friendRequest['Friend_RequesterId'] . '" ' . ($isChecked ? 'checked' : '') . '></td>';
                        echo '</tr>';
                    }
                    ?>
                </table>

                <div class="requestButton">
                    <div class="button">
                        <button class="submitButton" type="submit" name="submit" value="accept" >Accept Selected </button>
                    </div>
                    <div class="button">
                        <button class="submitButton" type="submit" name="submit" value="deny" onclick="return confirmDeny()">Deny Selected</button>
                    </div>
                </div>
            </form>
        </div>
        <script src="projectCST8257.js"></script>
    </body>
</html>


<?php include('./common/footer.php'); ?>