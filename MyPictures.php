<link rel="stylesheet" href="style.css">
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>My Pictures</title>
        
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
        $InfoComment = "";
        ?>

        <div class="container">
            <h1>My Pictures</h1>
            <form method="post" action="MyPictures.php" class="postForm" id="myForm">
                <div class="myImageContainer">
                    <div class="boxOne">
                        <select name="pictures" id="pictures">
                            <option value="0">Select one...</option>
                            <?php
                            $userId = $_SESSION['UserId'];
                            $sqlFindPicture = 'SELECT Album_Id, Title FROM Album WHERE Owner_Id = ?';
                            $stmtFindPicture = $myPdo->prepare($sqlFindPicture);
                            $stmtFindPicture->execute([$userId]);
                            $allPictureAlbums = $stmtFindPicture->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($allPictureAlbums as $pictureAlbum) {

                                $idSelectedPictureAlbumRow = $pictureAlbum['Album_Id'] ?? null;
                                $albumTitleSelectedPictureAlbumRow = $pictureAlbum['Title'] ?? null;
                                $selected = (isset($_POST['pictures']) && $_POST['pictures'] == $idSelectedPictureAlbumRow) ? 'selected' : '';

                                echo '<option value="' . $idSelectedPictureAlbumRow . '" ' . $selected . '>' . $albumTitleSelectedPictureAlbumRow . '</option>';
                            }
                            ?>
                        </select>

                        <button class="hideButton" id="hideButton" type="submit" name="submitImg" value="submitImg">Submit Image </button>

                        <div class="pictureTitle">
                            <p ><strong><?php echo $imageTitle ?></strong></p>

                        </div>

                        <div class="bigImage" id="largeImageContainer">
                            <!-- Larger image will be displayed here -->
                        </div>

                    </div>
                    <div class="boxTwo">
                        <div class="imageWithComments">
                            <div class="imageAndCommentsContainer">
                                <div class="imageContainer">
                                    <?php
                                    $originalImageId = "";
                                    $AlbumID = isset($_POST['pictures']) ? $_POST['pictures'] : '0';

                                    $sqlPicture = 'SELECT Picture_Id, Album_Id, File_Name, Album_Id, Description, Title FROM picture WHERE Album_Id = :IdAlbum';

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

                                            // Display the image
                                            if (file_exists($imagePath)) {
                                                echo '<div class="imageWithComments">';
                                                // Update the onclick attribute to call the openModal function
                                                echo '<img class="pic" src="' . $imagePath . '" alt="' . $imageFilename . '" onclick="openModal(\'' . $imagePath . '\', \'' . $imageTitle . '\', \'' . $imageDescription . '\', \'' . $imageId . '\', this)">';

                                                // Hide comments initially by adding a class
                                                echo '<div class="commentsContainer comment-' . $imageId . '" style="display:none;">';

                                                // Fetch and display comments for this image
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

                                                echo '</div>'; // Close commentsContainer div
                                                echo '<input type="hidden" name="selectedImageId" class="selectedImageId" value="' . $imageId . '">';
                                                echo '</div>'; // Close imageWithComments div
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

                                // Check if the selectedImageId is set in the form submission
                                if (isset($_POST['selectedImageId'])) {
                                    $imageId = $_POST['selectedImageId'];

                                    // Insert the comment into the database
                                    $stmtComment = $myPdo->prepare("INSERT INTO comment (Author_Id, Picture_Id, Comment_Text) VALUES (?, ?, ?)");
                                    $stmtComment->execute([$_SESSION['UserId'], $imageId, $DescriptionComment]);
                                    $InfoComment = 'The comment is submitted.';
                                }
                            }
                            $DescriptionComment = "";
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

            // On clicking the "Add Comment" button, store selected IDs and refresh the page
            document.querySelector('.submitButton').addEventListener('click', function () {
                storeSelectedIDs();
            });

            // Function to automatically select the previously chosen album and image IDs
            function autoSelectPrevious() {
                var selectedAlbumId = localStorage.getItem('selectedAlbumId');
                var selectedImageId = localStorage.getItem('selectedImageId');

                // Select the previously chosen album
                document.getElementById('pictures').value = selectedAlbumId;

                // Trigger a click on the previously selected image thumbnail
                var selectedImageThumbnail = document.querySelector('.pic[value="' + selectedImageId + '"]');
                if (selectedImageThumbnail) {
                    selectedImageThumbnail.click();
                }
            }

            // Call the function to automatically select previous IDs after the page loads
            window.onload = function () {
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

            // Function to keep track of the selected image ID
            function storeSelectedImageId() {
                var selectedImageId = document.getElementById("selectedImageId").value;
                document.getElementById("selectedImageIdHidden").value = selectedImageId;
            }

            // When submitting the comment, store the selected image ID
            document.querySelector('.submitButton').addEventListener('click', function () {
                storeSelectedImageId();
            });

            function openModal(imagePath, imageTitle, imageDescription, imageId, clickedImage) {
                var largeImageContainer = document.getElementById('largeImageContainer');
                largeImageContainer.innerHTML = ''; // Clear the content

                var img = document.createElement('img');
                img.className = 'largePic';
                img.src = imagePath;

                largeImageContainer.appendChild(img);

                var pictureTitleDiv = document.querySelector('.pictureTitle');
                pictureTitleDiv.innerHTML = '<p><strong>' + imageTitle + '</strong></p>';

                var pictureDescriptionDiv = document.querySelector('.imgDescription');
                pictureDescriptionDiv.innerHTML = '<p>' + imageDescription + '</p>';

                // Update the hidden input field 'selectedImageId' value
                var selectedImageIdInput = document.getElementById('selectedImageId');
                selectedImageIdInput.value = imageId;

                // Display the selected thumbnail ID in the comment section
                displaySelectedImageId();

                // Remove 'selectedThumbnail' class from all thumbnails
                var thumbnails = document.querySelectorAll('.pic');
                thumbnails.forEach(function (thumbnail) {
                    thumbnail.classList.remove('selectedThumbnail');
                });

                // Add 'selectedThumbnail' class to the clicked thumbnail
                clickedImage.classList.add('selectedThumbnail');

                // Show comments for the clicked image
                displayComments(imageId, clickedImage);
            }

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

            function displaySelectedImageId() {
                var selectedImageIdDisplay = document.getElementById("selectedImageIdDisplay");
                var selectedImageId = document.getElementById("selectedImageId").value;

                selectedImageIdDisplay.textContent = selectedImageId;
            }

            // Call the function to display the selected image ID
            displaySelectedImageId();
        </script>
    </body>
</html>
