
<?php
include("./common/header.php");

if (!isset($_SESSION['UserId'])) {
    $_SESSION['RequestedPage'] = $_SERVER['REQUEST_URI'];
    header('Location: Login.php');
    exit();
}

$dbConnection = parse_ini_file("Project.ini");
extract($dbConnection);
$myPdo = new PDO($dsn, $scriptUser, $scriptPassword);



include_once 'Functions.php';
include_once 'EntityClassLib.php';




$TitleAlbum = "";
$DescriptionAlbum = "";

$errorMessageAlbum = "";
$errorMessageTitleAlbum = "";
$errorMessageDescriptionAlbum = "";
$errorMessageUpload = "";

$titleAlbumRegex = '/^.{1,256}$/';
$descriptionAlbumRegex = '/^.{1,3000}$/';
$allowedFileTypes = ['image/jpeg', 'image/png', 'image/gif'];

$uploadedFileName = "";
$uploadedFileType = "";
$uploadFilePaths = array(); // Initialize an array to store paths
$uploadDirectory = './common/img/';

$uploadedFiles = isset($_FILES['uploadFile']) ? $_FILES['uploadFile'] : null;


if ($uploadedFiles && is_array($uploadedFiles['name']) && is_array($uploadedFiles['tmp_name'])) {
    $totalFiles = count($uploadedFiles['name']);

for ($i = 0; $i < $totalFiles; $i++) {
    $uploadedFileName = $uploadedFiles['name'][$i];
    $uploadedFileType = $uploadedFiles['type'][$i];
    $uploadFilePaths[] = $uploadDirectory . $uploadedFileName; 

    if (in_array($uploadedFileType, $allowedFileTypes)) {
        if ($uploadedFileType === 'image/jpeg') {
            $sourceImage = imagecreatefromjpeg($uploadedFiles['tmp_name'][$i]);
        } elseif ($uploadedFileType === 'image/png') {
            $sourceImage = imagecreatefrompng($uploadedFiles['tmp_name'][$i]);
        } elseif ($uploadedFileType === 'image/gif') {
            $sourceImage = imagecreatefromgif($uploadedFiles['tmp_name'][$i]);
        }

        $thumbnail = imagecreatetruecolor(100, 100);
        imagecopyresampled($thumbnail, $sourceImage, 0, 0, 0, 0, 100, 100, imagesx($sourceImage), imagesy($sourceImage));

        $thumbnailFilePath = $uploadDirectory . 'thumbnails/' . 'thumb_' . $uploadedFileName; 
        imagejpeg($thumbnail, $thumbnailFilePath); 

        imagedestroy($sourceImage);
        imagedestroy($thumbnail);
    }

}

}

if (isset($_POST["submit"])) {

    $TitleAlbum = trim($_POST["inputAlbumTitle"]);
    if (empty($TitleAlbum)) {
        $errorMessageTitleAlbum = "Title is required.";
    } elseif (!preg_match($titleAlbumRegex, $TitleAlbum)) {
        $errorMessageTitleAlbum = "Entered title is not valid.";
    } else {
        $errorMessageTitleAlbum = "";
        $_SESSION["inputAlbumTitle"] = $TitleAlbum;
    }

    if (!in_array($uploadedFileType, $allowedFileTypes)) {
        $errorMessageUpload = "Invalid file type. Only JPG (JPEG), GIF, and PNG are allowed.";
    } else {
        $errorMessageUpload = "";
    }


    $titleSelectedAlbumRow = trim($_POST["albumName"]);
    if (empty($titleSelectedAlbumRow)) {
        $errorMessageAlbum = "Select your Album.";
    } else {
        $errorMessageAlbum = "";
        $_SESSION["albumName"] = $titleSelectedAlbumRow;
    }

    $DescriptionAlbum = trim($_POST["inputAlbumDescription"]);
    if (empty($DescriptionAlbum)) {
        $errorMessageDescriptionAlbum = "Album description is required.";
    } elseif (!preg_match($descriptionAlbumRegex, $DescriptionAlbum)) {
        $errorMessageDescriptionAlbum = "Entered Album description is not valid.";
    } else {
        $errorMessageDescriptionAlbum = "";
        $_SESSION["inputAlbumDescription"] = $DescriptionAlbum;
    }
    
    if (isset($_POST["submit"]) && empty($errorMessageTitleAlbum) && empty($errorMessageUpload) && empty($errorMessageAlbum) && empty($errorMessageDescriptionAlbum)) {
        // Initialize $i before the loop
        $i = 0;

        for ($i = 0; $i < $totalFiles; $i++) {
            $uploadedFileName = $uploadedFiles['name'][$i];
            $uploadedFileType = $uploadedFiles['type'][$i];

            $TitleAlbum = trim($_POST["inputAlbumTitle"]);
            $titleSelectedAlbumRow = trim($_POST["albumName"]);
            $DescriptionAlbum = trim($_POST["inputAlbumDescription"]);

            if (move_uploaded_file($uploadedFiles['tmp_name'][$i], $uploadFilePaths[$i])) {
                $statement = $myPdo->prepare("INSERT INTO picture (Album_Id, File_Name, Title, Description) VALUES (?, ?, ?, ?)");
                $statement->execute([$titleSelectedAlbumRow, $uploadedFileName, $TitleAlbum, $DescriptionAlbum]);
            } else {
                $errorMessageUpload = "File upload failed. Please try again.";
            }
        }
    }


}





?>



<html>
    <head>
        <meta charset="UTF-8">
        <title>Upload Pictures</title>
        <link rel="stylesheet" href="style.css"/>

    </head>
    <body>
        <div class="container">
            <h1>Upload Pictures</h1>

            <p class="text">Accepted picture types: JPG (JPEG), GIF and PNG.</p>
            <p class="text">You can upload multiple picture at a time by pressing the shift key while selecting pictures.</p>
            <p class="text">When uploading multiple pictures, the title and description fields will be applied to all pictures.</p>

            <form method="post" action="UploadPictures.php" class="postForm" id="myForm" enctype="multipart/form-data">

                <div class="part">
                    <label class="album" for="album">Upload To Album:</label>    

                    <select name="albumName" id="albumName">
                        <option value="0">Select one...</option>
                        <?php
                        $userId = $_SESSION['UserId'];

                        $sqlselectedAlbum = 'SELECT Album_Id, Title FROM Album WHERE Owner_Id = ?';
                        $stmtselectedAlbum = $myPdo->prepare($sqlselectedAlbum);
                        $stmtselectedAlbum->execute([$userId]); // Bind the User_Id from the session
                        $allAlbums = $stmtselectedAlbum->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($allAlbums as $album) {
                            $albumIdSelectedAlbumRow = $album['Album_Id'] ?? null;
                            $titleSelectedAlbumRow = $album['Title'] ?? null;

                            $selected = (isset($_POST['albumName']) && $_POST['albumName'] == $albumIdSelectedAlbumRow) ? 'selected' : '';

                            echo '<option value="' . $albumIdSelectedAlbumRow . '" ' . $selected . '>' . $titleSelectedAlbumRow . '</option>';
                        }

                        ?>
                    </select>

                    <div class="error">
                        <?php echo $errorMessageAlbum; ?>
                    </div>
                </div>

                <div class="part">
                    <label class="title">File To Upload:</label>
                    <input class="uploadFile" type="file" name="uploadFile[]" accept="image/jpeg, image/png, image/gif" multiple />

                    <div class="error">
                        <?php echo $errorMessageUpload; ?>
                    </div>
                </div>

                <div class="part">
                    <label class="title">Title:</label>
                    <input class="inputAlbumTitle" type="text" name="inputAlbumTitle" value="<?php echo $TitleAlbum; ?>" />
                    <div class="error">
                        <?php echo $errorMessageTitleAlbum; ?>
                    </div>
                </div>

                <div class = "part">
                    <label class = "albumDescription"  >Description:</label>
                    <textarea class="inputAlbumDescription" style="vertical-align: top;" name="inputAlbumDescription"><?php echo $DescriptionAlbum; ?></textarea>
                    <div class="error">
                        <?php echo $errorMessageDescriptionAlbum; ?>
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