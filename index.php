<?php

// Include the config
require_once('config.php');

// Test mode doesn't try to save anything to the database
$testMode = false;
$testModeMessage = "Test mode has been forced active.";

// Connect to the database
$mysqli = @new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);
if($mysqli->connect_errno) {
    $testMode = true;
    $testModeMessage = "Unable to connect to the database using the provided settings:<br />" . htmlentities($mysqli->connect_error);
}

// Determine sorting
$sort = (isset($_GET['sort']) && $_GET['sort'] == 'asc') ? 'asc' : 'desc';

// Initialize error variables
$nameError = null; $emaiError = null; $websiteError = null; $commentError = null;
$error = false;

// Only available outside of test mode
if(!$testMode) {
    // Handle form submission
    if(!empty($_POST)) {
        // Require the name
        $name = isset($_POST['name']) ? trim($_POST['name']) : null;
        if($name == null) {
            $nameError = 'Name is required.';
            $error = true;
        }

        // Handle email address
        $email = isset($_POST['email']) ? trim($_POST['email']) : null;
        if($email == null) {
            $email = null;
        }

        // Handle website
        $website = isset($_POST['website']) ? trim($_POST['website']) : null;
        if($website == null) {
            $website = null;
        }

        // Handle comment
        $comment = isset($_POST['comment']) ? trim($_POST['comment']) : null;
        if($comment == null) {
            $commentError = 'Comment is required.';
            $error = true;
        }

        // Initialize the return value
        $retVal = array(
            'name' => $name,
            'email' => $email,
            'website' => $website,
            'comment' => $comment,
        );

        // Save the comment only when there was no error
        if(!$error) {
            // Determine submission time
            $submitted = date('Y-m-d H:i:s');
            $retVal['submitted'] = $submitted;

            // Prepare the statement and execute
            $stmt = $mysqli->prepare("insert into comment (name, email, website, comment, submitted) values (?, ?, ?, ?, ?)");
            $stmt->bind_param('sssss', $name, $email, $website, $comment, $submitted);
            $stmt->execute();
        }

        // Handle JSON result
        if(isset($_POST['json']) && $_POST['json']) {
            header("Content-Type: text/json");
            print json_encode($retVal);
            exit;
        } elseif(!$error) {
            header("Location: .");
            exit;
        }
    }

    // Get existing comments
    $comments = $mysqli->query("select * from comment order by submitted {$sort}");
    if(!$comments) {
        // Force test mode when there is an error
        $testMode = true;
        $testModeMessage = 'Unable to retrieve existing comments:<br />' . htmlentities($mysqli->error);
    }
} else {
    // No comments
    $comments = null;
}

?><!DOCTYPE html>
<html>
    <head>
        <title>Comment Wall</title>
        <link rel='stylesheet' type='text/css' href="style.css" />
    </head>
    <body>
        <?php if($testMode): ?>
            <div class='testMode'>
                <h1>Test Mode Active</h1>
                <?php print $testModeMessage; ?><br /><br />No changes will be saved and all posted comments
                will disappear when the page is reloaded.  Test mode only works when JavaScript is enabled.
            </div>
        <?php endif; ?>
        <h1>Comments</h1>
        <a href="?sort=<?php print ($sort == 'asc' ? 'desc' : 'asc'); ?>" id="changeSort">Show <?php print $sort == 'asc' ? 'Newest' : 'Oldest'; ?> Comments First</a>
        <div id='commentWall'>
            <?php if($comments && $comments->num_rows): while($row = $comments->fetch_assoc()): ?>
                <div class='comment'>
                    <?php if($row['email'] != null): 
                        $hash = md5(strtolower(trim($row['email']))); ?>
                        <img class='gravatar' src="//www.gravatar.com/avatar/<?php print $hash; ?>"></img>
                    <?php endif; ?>
                    <h2>
                    <?php
                        // Create the link to the website or email address
                        if($row['website'] != null)
                            print '<a target="_blank" href="' . htmlentities($row['website']) . '">';
                        elseif($row['email'] != null)
                            print '<a href="mailto:' . htmlentities($row['email']) .' ">';

                        // Display the user's name
                        print htmlentities($row['name']);

                        // Close links
                        if($row['website'] != null || $row['email'] != null)
                            print '</a>';
                    ?>
                    </h2>
                    <div class='submissionTime'><?php print date('l, F j, Y @ g:i a T', strtotime($row['submitted'])); ?></div>
                    <div class='commentText'><?php print nl2br(htmlentities($row['comment'])); ?></div>
                </div>
            <?php endwhile; else: ?>
                <div class='noComments'>There are no comments to display.</div>
            <?php endif; ?>
        </div>
        <form method='post' id='commentForm'>
            <h1>Post a Comment</h1>
            <table>
                <tr>
                    <th>Name <span class='required'>(Required)</span></th>
                    <td>
                        <div class='error' id='nameError' <?php if(!$nameError) print 'style="display: none;"'; ?>>
                            <?php print $nameError; ?>
                        </div>
                        <input type='text' name='name' class='name' id='nameInput' />
                    </td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>
                        <div class='error' id='emailError' <?php if(!$emailError) print 'style="display: none;"'; ?>>
                            <?php print $emailError; ?>
                        </div>
                        <input type='email' name='email' class='email' id='emailInput' />
                    </td>
                </tr>
                <tr>
                    <th>Website</th>
                    <td>
                        <div class='error' id='websiteError' <?php if(!$websiteError) print 'style="display: none;"'; ?>>
                            <?php print $websiteError; ?>
                        </div>
                        <input type='url' name='website' class='website' id='websiteInput' />
                    </td>
                </tr>
                <tr>
                    <th>Comment <span class='required'>(Required)</span></th>
                    <td>
                        <div class='error' id='commentError' <?php if(!$commentError) print 'style="display: none;"'; ?>>
                            <?php print $commentError; ?>
                        </div>
                        <textarea name='comment' class='comment' id='commentInput'></textarea>
                    </td>  
                </tr>
            </table>
            <input type='submit' class='submit' value='Post Comment' />
        </form>
        <script type='text/javascript' src="jquery-2.1.1.min.js"></script>
        <script type='text/javascript' src="jquery.md5.min.js"></script>
        <script type='text/javascript' src="jquery.formatDateTime.min.js"></script>
        <script type='text/javascript'>
            var testMode = <?php print $testMode ? 'true' : 'false'; ?>;
            var curSort = '<?php print $sort; ?>';
        </script>
        <script type='text/javascript' src="commentwall.js"></script>
    </body>
</html>
