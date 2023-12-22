<?php
include_once 'EntityClassLib.php';

function getPDO()
{
    $dbConnection = parse_ini_file("Project.ini");
    extract($dbConnection);
    return new PDO($dsn, $scriptUser, $scriptPassword);  
}


function getUserByIdAndPassword($UserId, $Password)
{
    $pdo = getPDO();
    
    $sql = "SELECT UserId FROM user WHERE UserId = '$UserId' AND Password = '$Password'";
    

        
    $resultSet = $pdo->query($sql);
    if ($resultSet)
    {
        $row = $resultSet->fetch(PDO::FETCH_ASSOC);
        if ($row)
        {
            return new UserId($row['UserId']);
        }
        else
        {
            return null;
        }
    }
    else
    {
        throw new Exception("Query failed! SQL statement: $sql");
    }
}

function validateFormData($enteredStudentID, $enteredPassword)
{
    $errors = [];

    if (empty($enteredStudentID)) {
        $errors['StudentId'] = 'Student ID is required.';
    }

    if (empty($enteredPassword)) {
        $errors['Password'] = 'Password is required.';
    }

    return $errors;
}


function handleLoginFormSubmission($connection)
{
    $enteredUserID = $_POST['UserId'];
    $enteredPassword = $_POST['Password'];

    $errors = validateFormData($enteredUserID, $enteredPassword);

    if (empty($errors)) {
        $query = "SELECT UserId, Name, Password FROM user WHERE UserId = :UserId";
        $stmt = $connection->prepare($query);
        $stmt->bindParam(':UserId', $enteredUserID);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($enteredPassword, $user['Password'])) {
                $_SESSION['UserId'] = $enteredUserID;
                $_SESSION['Name'] = $user['Name'];

                if (isset($_SESSION['RequestedPage']) && !empty($_SESSION['RequestedPage'])) {
                    $redirectPage = $_SESSION['RequestedPage'];
                    unset($_SESSION['RequestedPage']); // Clear the 'RequestedPage' session variable
                    header("Location: $redirectPage");
                    exit();
                } else {
                    header("Location: MyAlbums.php");
                    exit();
                }
            } else {
                $errors['login'] = 'Invalid User ID or Password.';
            }
        } else {
            $errors['login'] = 'Invalid User ID or Password.';
        }
    }

    return $errors;
}





function addNewUser($userId, $name, $phone, $password)
{
   $pdo = getPDO();
     
    $sql = "INSERT INTO user (UserId, Name, Phone, Password) VALUES( '$userId', '$name', '$phone', '$password')";
    $pdoStmt = $pdo->query($sql);
}

function createNewUserValidation($UserId, $Name, $Phone, $Password, $confirmPassword, $connection)
{
    $errors = [];

    if (empty($UserId)) {
        $errors['UserId'] = 'User ID is required.';
    }

    if (empty($Name)) {
        $errors['Name'] = 'Name is required.';
    }

    if (!preg_match('/^\d{3}-\d{3}-\d{4}$/', $Phone)) {
        $errors['Phone'] = 'Phone Number must be in the format of nnn-nnn-nnnn.';
    }

    if (!preg_match('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}$/', $Password)) {
        $errors['Password'] = 'Password must be at least 6 characters long and contain at least one uppercase letter, one lowercase letter, and one digit.';
    }

    $query = "SELECT * FROM user WHERE UserId = :UserId";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':UserId', $UserId);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $errors['UserId'] = 'User ID already exists.';
    }

    if ($Password !== $confirmPassword) {
        $errors['confirmPassword'] = 'Passwords do not match.';
    }

    return $errors;
}


function handleNewUserFormSubmission($connection)
{
    $UserId = $_POST['UserId'];
    $Name = $_POST['Name'];
    $Phone = $_POST['Phone'];
    $Password = $_POST['Password'];
    $confirmPassword = $_POST['confirmPassword'];

    $errors = createNewUserValidation($UserId, $Name, $Phone, $Password, $confirmPassword, $connection);
 
    if (empty($errors)) {
        $hashedPassword = password_hash($Password, PASSWORD_DEFAULT);

        $insertQuery = "INSERT INTO user (UserId, Name, Phone, Password) VALUES (:UserId, :Name, :Phone, :Password)";
        $stmt = $connection->prepare($insertQuery);
        $stmt->bindParam(':UserId', $UserId);
        $stmt->bindParam(':Name', $Name);
        $stmt->bindParam(':Phone', $Phone);
        $stmt->bindParam(':Password', $hashedPassword); // Store hashed password
        $stmt->execute();

        $query = "SELECT * FROM user WHERE UserId = :UserId AND Password = :Password";
        $stmt = $connection->prepare($query);
        $stmt->bindParam(':UserId', $UserId);
        $stmt->bindParam(':Password', $hashedPassword); // Use hashed password for comparison
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            session_start();
            $_SESSION['UserId'] = $UserId;
            $_SESSION['Name'] = $Name;
            header('Location: MyAlbums.php');
            die();
        }
    }

    return $errors;
}

function establishDatabaseConnection($config)
{
    $dsn = $config['database connection']['dsn'];
    $scriptUser = $config['database connection']['scriptUser'];
    $scriptPassword = $config['database connection']['scriptPassword'];

    return new PDO($dsn, $scriptUser, $scriptPassword);
}

function getUserAlbums($connection, $userId)
{
    $query = "SELECT album.Album_Id, album.Title, COUNT(picture.Picture_Id) AS num_pictures, accessibility.Description
              FROM album
              LEFT JOIN picture ON album.Album_Id = picture.Album_Id
              INNER JOIN accessibility ON album.Accessibility_Code = accessibility.Accessibility_Code
              WHERE album.Owner_Id = :userId
              GROUP BY album.Album_Id";

    $stmt = $connection->prepare($query);
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAccessibilityOptions($connection)
{
    $query = "SELECT Accessibility_Code, Description FROM accessibility";
    $stmt = $connection->prepare($query);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function handleAlbumDeletion($connection)
{
    if (isset($_GET['delete_album']) && $_GET['delete_album'] === 'true') {
        $albumIdToDelete = $_GET['album_id'];

        $deleteQuery = "DELETE FROM album WHERE Album_Id = :albumId";
        $stmt = $connection->prepare($deleteQuery);
        $stmt->bindParam(':albumId', $albumIdToDelete);
        $stmt->execute();

        header('Location: MyAlbums.php');
        exit();
    }
}

function handleAccessibilityFormSubmission($connection)
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_changes'])) {
        foreach ($_POST['accessibility'] as $albumId => $accessibilityCode) {
            $updateQuery = "UPDATE album SET Accessibility_Code = :accessibilityCode WHERE Album_Id = :albumId";
            $stmt = $connection->prepare($updateQuery);
            $stmt->bindParam(':accessibilityCode', $accessibilityCode);
            $stmt->bindParam(':albumId', $albumId);
            $stmt->execute();
        }

        $_SESSION['success_message'] = "Changes saved successfully!";
        header('Location: MyAlbums.php');
        exit();
    }
}



function save_uploaded_file($destinationPath)
{
    if (!file_exists($destinationPath))
    {
        mkdir($destinationPath);
    }

    $tempFilePath = $_FILES['txtUpload']['tmp_name'];
    $filePath = $destinationPath."/".$_FILES['txtUpload']['name'];

    $pathInfo = pathinfo($filePath);
    $dir = $pathInfo['dirname'];
    $fileName = $pathInfo['filename'];
    $ext = $pathInfo['extension'];

    //make sure not to overwrite existing files 
    $i="";
    while (file_exists($filePath))
    {	
            $i++;
            $filePath = $dir."/".$fileName."_".$i.".".$ext;
    }
    move_uploaded_file($tempFilePath, $filePath);

    return $filePath;
}

function resamplePicture($filePath, $destinationPath, $maxWidth, $maxHeight)
{
    if (!file_exists($destinationPath))
    {
            mkdir($destinationPath);
    }

    $imageDetails = getimagesize($filePath);

    $originalResource = null;
    if ($imageDetails[2] == IMAGETYPE_JPEG) 
    {
            $originalResource = imagecreatefromjpeg($filePath);
    } 
    elseif ($imageDetails[2] == IMAGETYPE_PNG) 
    {
            $originalResource = imagecreatefrompng($filePath);
    } 
    elseif ($imageDetails[2] == IMAGETYPE_GIF) 
    {
            $originalResource = imagecreatefromgif($filePath);
    }
    $widthRatio = $imageDetails[0] / $maxWidth;
    $heightRatio = $imageDetails[1] / $maxHeight;
    $ratio = max($widthRatio, $heightRatio);

    $newWidth = $imageDetails[0] / $ratio;
    $newHeight = $imageDetails[1] / $ratio;

    $newImage = imagecreatetruecolor($newWidth, $newHeight);

    $success = imagecopyresampled($newImage, $originalResource, 0, 0, 0, 0, 
                                    $newWidth, $newHeight, $imageDetails[0], $imageDetails[1]);

    if (!$success)
    {
            imagedestroy(newImage);
            imagedestroy(originalResource);
            return "";
    }
    $pathInfo = pathinfo($filePath);
    $newFilePath = $destinationPath."/".$pathInfo['filename'];
    if ($imageDetails[2] == IMAGETYPE_JPEG) 
    {
            $newFilePath .= ".jpg";
            $success = imagejpeg($newImage, $newFilePath, 100);
    } 
    elseif ($imageDetails[2] == IMAGETYPE_PNG) 
    {
            $newFilePath .= ".png";
            $success = imagepng($newImage, $newFilePath, 0);
    } 
    elseif ($imageDetails[2] == IMAGETYPE_GIF) 
    {
            $newFilePath .= ".gif";
            $success = imagegif($newImage, $newFilePath);
    }

    imagedestroy($newImage);
    imagedestroy($originalResource);

    if (!$success)
    {
            return "";
    }
    else
    {
            return $newFilePath;
    }
}

