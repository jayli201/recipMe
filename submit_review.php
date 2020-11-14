<?php
include "connectdb.php";
include "display_recipes_sql.php";

// Check if user is logged in or not
if (!isset($_SESSION['uname'])) {
   header('Location: auth/login.php');
}

// logout
if (isset($_POST['logout'])) {
   session_destroy();
   header('Location: auth/login.php');
}

$name = $email = $comment = NULL;
$name_msg = $email_msg = $comment_msg = NULL;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   if (empty($_POST['name']))
      $name_msg = "Please enter your name <br />";
   else {
      $name = trim($_POST['name']);
      // You may reset $name_msg and use it to determine
      // when to display an error message  
      // $name_msg = "";     
   }

   if (empty($_POST['emailaddr']))
      $email_msg = "Please enter your email address <br />";
   else {
      $email = trim($_POST['emailaddr']);
      // You may reset $email_msg and use it to determine
      // when to display an error message
      // $email_msg = "";      
   }

   if (empty($_POST['comment']))
      $comment_msg = "Please enter comment <br />";
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
   <title>RecipMe</title>
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
      <h1>Leave your review</h1>

      <form action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
         <label>Name: </label>
         <input type="text" name="name" value="<?php if (isset($_POST['name'])) echo $_POST['name'] ?>" <?php if (empty($_POST['name'])) { ?> autofocus <?php } ?> />
         <span class="msg"><?php if (empty($_POST['name'])) echo $name_msg ?></span>
         <!-- Alternatively, we can check if the error message has something to be displayed -->
         <!-- <span class="msg"><?php if ($name_msg != "") echo $name_msg ?></span> -->

         <br />
         <label>Email:</label>
         <input type="email" name="emailaddr" value="<?php if (isset($_POST['emailaddr'])) echo $_POST['emailaddr'] ?>" <?php if (empty($_POST['emailaddr'])) { ?> autofocus <?php } ?> />
         <span class="msg"><?php if (empty($_POST['emailaddr'])) echo $email_msg ?></span>
         <br />
         <label>Comment: </label>
         <textarea rows="5" cols="40" name="comment" <?php if (empty($_POST['comment'])) { ?> autofocus <?php } ?>><?php if (isset($_POST['comment'])) echo $_POST['comment'] ?></textarea>
         <span class="msg"><?php if (empty($_POST['comment'])) echo $comment_msg ?></span>
         <br />

         <input type="submit" value="Submit" />
      </form>


      <?php
      if ($name != NULL && $email != NULL && $comment != NULL) {
         echo "<br/><hr/><br/>";
         echo "Thanks for this comment, $name <br />";
         echo "<i>$comment</i> <br />";
         echo "We will reply to $email <br /><br /><br />";

         $confirm = "Thanks for this comment, $name \n";
         $confirm .= "$comment \n";
         $confirm .= "We will reply to $email \n";
      }
      ?>
   </div>

   <?php include('footer.html') ?>
   <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
   <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
   <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</body>

</html>