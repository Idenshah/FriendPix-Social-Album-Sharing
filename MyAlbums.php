<?php
include("./common/header.php");

if (!isset($_SESSION['UserId'])) {
    $_SESSION['RequestedPage'] = $_SERVER['REQUEST_URI'];
    header('Location: Login.php');
    exit();
}

include_once 'Functions.php';
include_once 'EntityClassLib.php';

$config = parse_ini_file('Project.ini', true);

extract($_POST);

$loginErrorMsg = '';

$connection = establishDatabaseConnection($config);

$userId = $_SESSION['UserId'];
$albums = getUserAlbums($connection, $userId);
$accessibilities = getAccessibilityOptions($connection);

handleAlbumDeletion($connection);

handleAccessibilityFormSubmission($connection);

?>


<html>
    <head>
        <meta charset="UTF-8">
        <title>My Albums</title>
    </head>
    <body>
        <div class="container">
            <h1>My Albums</h1>
            
            <p>Welcome <strong><?php echo $_SESSION['Name']; ?> </strong> (Not you? Change user(<a href="Login.php"> here</a>)</p>
            
            <a href="AddAlbum.php">Create a New Album</a>

            <form method="post" action="MyAlbums.php">
                <table class="table">
                    <tr>
                        <th>Title</th>
                        <th>Number of Pictures</th>
                        <th>Accessibility</th>
                        <th></th>
                    </tr>
                    <?php foreach ($albums as $album): ?>
                        <tr>
                            <td><?php echo $album['Title']; ?></td>
                            <td><?php echo $album['num_pictures']; ?></td>
                            <td>
                                <select name="accessibility[<?php echo $album['Album_Id']; ?>]">
                                    <?php foreach ($accessibilities as $accessibility): ?>
                                        <option value="<?php echo $accessibility['Accessibility_Code']; ?>" 
                                            <?php if ($album['Description'] === $accessibility['Description']): ?>
                                                selected="selected"
                                            <?php endif; ?>>
                                            <?php echo $accessibility['Description']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <a href="#" class="delete-album" onclick="confirmDelete(<?php echo $album['Album_Id']; ?>)">Delete</a>
                                <input type="hidden" name="album_id[]" value="<?php echo $album['Album_Id']; ?>">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                
                <input type="submit" class="btn btn-primary" name="save_changes" value="Save Changes">
            </form>
            <?php
                if (isset($_SESSION['success_message'])) {
                    echo '<div class="success-message">' . $_SESSION['success_message'] . '</div>';
                    unset($_SESSION['success_message']);
                }
            ?>
        </div>
        
        <script>
            function confirmDelete(albumId) {
                var confirmDelete = confirm('Are you sure you want to delete this album and its pictures?');
                if (confirmDelete) {
                    window.location.href = 'MyAlbums.php?delete_album=true&album_id=' + albumId;
                }
            }
        </script>

    </body>
</html>

<?php include('./common/footer.php'); ?>