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

// pin a recipe, currently not working
if (isset($_POST['pin'])) {
   pin($_POST['recipeID'], $_POST['cookUsername']);
}

// unpin a recipe
if (isset($_POST['unpin'])) {
   if (!empty($_POST['unpin']) && ($_POST['unpin'] == 'Unpin') && (!isOwnRecipe($_POST['recipeID']))) {
      unpin($_POST['recipeID']);
   }
}

$query = "SELECT * FROM recipes WHERE username != '" . $_SESSION['uname'] . "'";

if (isset($_POST['sort'])) {
   $query = "SELECT * FROM recipes WHERE username != '" . $_SESSION['uname'] . "' AND country = '" . $_POST['country'] . "'";
} elseif (isset($_POST['reset'])) {
   $query = "SELECT * FROM recipes WHERE username != '" . $_SESSION['uname'] . "'";
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
      <br>
      <h1>All Recipes</h1>
      <form action='' method='post'>
         <div class='form-group'>
            <input type='text' placeholder='Sort by country' name='country' id='country' />
            <button type='submit' name='sort' class='button'>Sort</button>
            <button type='submit' name='reset' class='button'>Reset</button>
         </div>
      </form>
      <p><?php displaySomeRecipe($query) ?></p>
      <br>
   </div>

   <?php include('footer.html') ?>

   <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
   <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
   <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</body>

</html>