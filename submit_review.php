<?php
include "connectdb.php";
include "display_reviews_sql.php";

// Check if user is logged in or not
if (!isset($_SESSION['uname'])) {
   header('Location: auth/login.php');
}

// logout
if (isset($_POST['logout'])) {
   session_destroy();
   header('Location: auth/login.php');
}


//insert the review
if (isset($_POST['action'])) {
   if (!empty($_POST['action']) && ($_POST['action'] == "Submit")) {

      $recipeID = htmlspecialchars($_GET['recipeID']);
      $cookID = htmlspecialchars($_GET['cookID']);
      addReview($recipeID, $cookID, $_POST['comment'], $_SESSION['uname']);
   }
}

$comment = NULL;
$comment_msg = NULL;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

   if (empty($_POST['comment']))
      $comment_msg = "<em>Please enter comment </em> <br />";
   else {
      $comment = trim($_POST['comment']);
      // You may reset $comment_msg and use it to determine
      // when to display an error message
      // $comment_msg = "";      
   }
}

?>


<!DOCTYPE html>
<html>

<head>
   <meta charset="utf-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge"> <!-- required to handle IE -->
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <title>Submit a Review</title>
   <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" />
   <link rel="stylesheet" href="custom-style.css" />
</head>

<body>

   <?php
   if (isCook($_SESSION['uname'])) {
      include('cook_header.html');
   } else {
      include('foodie_header.html');
   }
   ?>

   <div>
      <br />
      <h1 class="display-4" style="color: #5cb85c;"><strong>Leave your review</strong></h1>

      <form action="<?php $_SERVER['PHP_SELF'] ?>" method="post">

         <label>Comment: </label>
         <textarea rows="5" cols="40" name="comment" <?php if (empty($_POST['comment'])) { ?> autofocus <?php } ?>><?php if (isset($_POST['comment'])) echo $_POST['comment'] ?></textarea>
         <br>
         <span style="color:#cc0000;" class="msg"><?php if (empty($_POST['comment'])) echo $comment_msg ?></span>
         <br />

         <input type="submit" value="Submit" id="action" name="action" />
      </form>

      <?php
      $recipeID = $_SESSION['recipeID'];
      $cookID = $_SESSION['cookID'];
      ?>
      <input type="submit" value="Go back to <?php echo $cookID; ?>'s reviews" onClick="window.location.href='reviews.php?recipeID=<?php echo $recipeID; ?>&cookID=<?php echo $cookID; ?>'" />
      <?php
      if ($comment != NULL) {
         echo "<br/><hr/><br/>";
         echo "Thanks for this comment, " . $_SESSION['uname'] . " <br />";
         echo "<i>$comment</i> <br />";

         $confirm = "Thanks for this comment, " . $_SESSION['uname'] . "\n";
         $confirm .= "$comment \n";
         header("Location: reviews.php?recipeID=" . $recipeID . "&cookID=" . $cookID);
      }
      ?>
   </div>
   </br>
   </br>
   </br>


   <?php include('footer.html') ?>
   <script src=" https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
   <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
   <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</body>

</html>