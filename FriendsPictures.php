<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Friends Pictures</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php
        $dbConnection = parse_ini_file("Project.ini");
        extract($dbConnection);
        $myPdo = new PDO($dsn, $scriptUser, $scriptPassword);

        include("./common/header.php");

        if (!isset($_SESSION['UserId'])) {
            $_SESSION['RequestedPage'] = $_SERVER['REQUEST_URI'];
            header('Location: Login.php');
            exit();
        }

        include_once 'Functions.php';
        include_once 'EntityClassLib.php';

        $uploadDirectory = './common/img/';
        $errorAddComment = "";
        $DescriptionComment = "";
        $imageTitle = "";
        $imageDescription = "";
        $imageId = "";
        $InfoComment="";


        ?>        

    <div class="container">
        <h1>My Friends Pictures</h1>
        <!-- Your existing HTML form starts here -->
        <form method="post" class="postForm" id="myForm">
        <select name="pictures" id="pictures">
            <option value="0">Select one...</option>
            <?php
            $userId = $_SESSION['UserId'];

            // Fetch the IDs of albums shared by the user's friends
            $sql = 'SELECT DISTINCT a.Album_Id, a.Title 
                    FROM album a
                    INNER JOIN friendship f ON (f.Friend_RequesterId = a.Owner_Id OR f.Friend_RequesteeId = a.Owner_Id)
                    WHERE (f.Friend_RequesterId = :userId OR f.Friend_RequesteeId = :userId) 
                    AND f.Status = "accepted"';

            $stmt = $myPdo->prepare($sql);
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();
            $friendAlbums = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($friendAlbums as $album) {
                $albumId = $album['Album_Id'];
                $albumTitle = $album['Title'];
                echo '<option value="' . $albumId . '">' . $albumTitle . '</option>';
            }
            ?>
        </select>


            <button class="hideButton" id="hideButton" type="submit" name="submitImg" value="submitImg">Submit Image </button>

            <div class="pictureTitle">
            <p ><strong><?php echo $imageTitle ?></strong></p>

            </div>

            <div class="bigImage" id="largeImageContainer">
            </div>
            
            
            <div class="imageWithComments">
                <div class="imageAndCommentsContainer">
                    <div class="imageContainer">
                        <?php
                        $originalImageId = "";
                        $AlbumID = isset($_POST['pictures']) ? $_POST['pictures'] : '0';

                        $sqlPicture = 'SELECT Picture_Id, Album_Id, File_Name, Album_Id, Description, Title 
                                        FROM picture 
                                        WHERE Album_Id = :IdAlbum';
                        
                        $stmtPicture = $myPdo->prepare($sqlPicture);
                        $stmtPicture->bindParam(':IdAlbum', $AlbumID);
                        $stmtPicture->execute();
                        $allPictures = $stmtPicture->fetchAll(PDO::FETCH_ASSOC);

                        if (!empty($allPictures)) {
                            foreach ($allPictures as $picture) {
                                $imageFilename = $picture['File_Name'];
                                $imageTitle = $picture['Title'];
                                $imageDescription = $picture['Description'];
                                $imageId = $picture['Picture_Id'];
                                $imagePath = $uploadDirectory . $imageFilename;
                                $storedImagePath = isset($_SESSION['imagePath']) ? $_SESSION['imagePath'] : '';

                                if (file_exists($imagePath)) {
                                    echo '<div class="imageWithComments">';
                                    echo '<img class="pic" src="' . $imagePath . '" alt="' . $imageFilename . '" onclick="openModal(\'' . $imagePath . '\', \'' . $imageTitle . '\', \'' . $imageDescription . '\', \'' . $imageId . '\', this)">';

                                    echo '<div class="commentsContainer comment-' . $imageId . '" style="display:none;">';

                                    $sqlFindPictureComment = 'SELECT Comment_Id, Author_Id, Picture_Id, Comment_Text FROM comment WHERE Picture_Id = :ImageID ORDER BY Comment_Id DESC';
                                    $stmtFindPictureComment = $myPdo->prepare($sqlFindPictureComment);
                                    $stmtFindPictureComment->bindParam(':ImageID', $imageId);
                                    $stmtFindPictureComment->execute();
                                    $rowComments = $stmtFindPictureComment->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    echo '<p><strong>Comments:</strong></p>';
                                    
                                    foreach ($rowComments as $comment) {
                                        $authorComment = $comment['Author_Id'];
                                        $commentText = $comment['Comment_Text'];
                                        echo '<div class="comment">';
                                        echo '<p><strong><span class="author">' . $authorComment . ':</span></strong> ' . $commentText . '</p>';
                                        echo '</div>';
                                    }

                                    echo '</div>'; 
                                    echo '<input type="hidden" name="selectedImageId" class="selectedImageId" value="' . $imageId . '">';
                                    echo '</div>'; 
                                } else {
                                    echo '<p class="error">Image not found: ' . $imagePath . '</p>';
                                }
                            }
                        }
                        ?>
                    </div>
                    <div class="commentsSection">


                    </div>
                </div>
            </div>
            
            <input type="hidden" name="selectedImageId" id="selectedImageId" value="<?php echo $imageId; ?>">

                    <?php

                    if (isset($_POST["submit"]) && $_POST["submit"] === "submitComment") {
                        $DescriptionComment = trim($_POST["AddComment"]);

                        if (empty($DescriptionComment)) {
                            $errorAddComment = "No comment is written!";
                        } else {
                            $errorAddComment = "";

                            if (isset($_POST['selectedImageId'])) {
                                $imageId = $_POST['selectedImageId'];

                                $stmtComment = $myPdo->prepare("INSERT INTO comment (Author_Id, Picture_Id, Comment_Text) VALUES (?, ?, ?)");
                                $stmtComment->execute([$_SESSION['UserId'], $imageId, $DescriptionComment]);
                                $InfoComment= 'The comment is submitted.';
                            }
                        }
                        $DescriptionComment="";
}
                    ?>
                    <div class="sectionTwo">

                         <p><strong>Description:</strong></p>

                        <div class="part" >
                                <div class="imgDescription"><?php echo $imageDescription; ?></div>
                        </div>

                        <div id="commentSection" style="display: none;">
                            <!-- This span displays the selected image ID -->
                            <span id="selectedImageIdDisplay"><?php echo htmlspecialchars($selectedImageId); ?></span>
                        </div>

                            
                        <div>
                            <div class = "part">
                                <textarea class="addComment" style="vertical-align: top;" name="AddComment" placeholder="Leave a comment..."><?php echo $DescriptionComment; ?></textarea>
                            </div>
                            <div class="error">
                                <?php echo $errorAddComment; ?>
                            </div>
                            <div class="info">
                                <?php echo $InfoComment; ?>
                            </div>
                            <div class="addCommentButton">
                                <div class="button">
                                    <button class="submitButton" type="submit" name="submit" value="submitComment">Add Comment</button>

                                </div>
                            </div>
                        
                        </div>    
                    </div>

        </form>
    </div>

    <?php include('./common/footer.php'); ?>

        <script>
            // Function to store selected album and image IDs in local storage
            function storeSelectedIDs() {
                var selectedAlbumId = document.getElementById('pictures').value;
                var selectedImageId = document.querySelector('.selectedThumbnail').getAttribute('value');

                localStorage.setItem('selectedAlbumId', selectedAlbumId);
                localStorage.setItem('selectedImageId', selectedImageId);
            }



            function autoSelectPrevious() {
                var selectedAlbumId = localStorage.getItem('selectedAlbumId');
                var selectedImageId = localStorage.getItem('selectedImageId');

                document.getElementById('pictures').value = selectedAlbumId;

                var selectedImageThumbnail = document.querySelector('.pic[value="' + selectedImageId + '"]');
                if (selectedImageThumbnail) {
                    selectedImageThumbnail.click();
                }
            }

            window.onload = function() {
                autoSelectPrevious();
            };



                                    function displayComments(imageId, element) {
                                        // Hide all comment sections initially
                                        const allComments = document.querySelectorAll('.commentsContainer');
                                        allComments.forEach(comment => {
                                            comment.style.display = 'none';
                                        });

                                        // Show comments for the clicked image
                                        const selectedComment = document.querySelector('.comment-' + imageId);
                                        selectedComment.style.display = 'block';
                                    }



            var select = document.getElementById('pictures');
            select.addEventListener('change', function () {
                var button = document.getElementById('hideButton');
                button.click();
            });

            function storeSelectedImageId() {
                var selectedImageId = document.getElementById("selectedImageId").value;
                document.getElementById("selectedImageIdHidden").value = selectedImageId;
            }



            function openModal(imagePath, imageTitle, imageDescription, imageId, clickedImage) {
                var largeImageContainer = document.getElementById('largeImageContainer');
                largeImageContainer.innerHTML = '';

                var img = document.createElement('img');
                img.className = 'largePic';
                img.src = imagePath;

                largeImageContainer.appendChild(img);

                var pictureTitleDiv = document.querySelector('.pictureTitle');
                pictureTitleDiv.innerHTML = '<p><strong>' + imageTitle + '</strong></p>';

                var pictureDescriptionDiv = document.querySelector('.imgDescription');
                pictureDescriptionDiv.innerHTML = '<p>' + imageDescription + '</p>';

                var selectedImageIdInput = document.getElementById('selectedImageId');
                selectedImageIdInput.value = imageId;

                displaySelectedImageId();

                var thumbnails = document.querySelectorAll('.pic');
                thumbnails.forEach(function (thumbnail) {
                    thumbnail.classList.remove('selectedThumbnail');
                });

                clickedImage.classList.add('selectedThumbnail');

                displayComments(imageId, clickedImage);
            }

            function displayComments(imageId, element) {
                const allComments = document.querySelectorAll('.commentsContainer');
                allComments.forEach(comment => {
                    comment.style.display = 'none';
                });

                const selectedComment = document.querySelector('.comment-' + imageId);
                selectedComment.style.display = 'block';
            }

            function displaySelectedImageId() {
                var selectedImageIdDisplay = document.getElementById("selectedImageIdDisplay");
                var selectedImageId = document.getElementById("selectedImageId").value;

                selectedImageIdDisplay.textContent = selectedImageId;
            }

            displaySelectedImageId();
        </script>
</body>
</html>
