<?php
include "connectdb.php";
include "display_recipes_sql.php";

// Check if user is logged in or not
if (!isset($_SESSION['uname'])) {
   header('Location: auth/welcome.php');
}

// logout
if (isset($_POST['logout'])) {
   session_destroy();
   header('Location: auth/welcome.php');
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

// sort by recipe popularity
if (isset($_POST['up_and_coming'])) {
   $query = "SELECT * FROM recipes WHERE username != '" . $_SESSION['uname'] . "' AND recipePinCount BETWEEN 0 AND 10";
} elseif (isset($_POST['rising_star'])) {
   $query = "SELECT * FROM recipes WHERE username != '" . $_SESSION['uname'] . "' AND recipePinCount BETWEEN 11 AND 20";
} elseif (isset($_POST['big_hit'])) {
   $query = "SELECT * FROM recipes WHERE username != '" . $_SESSION['uname'] . "' AND recipePinCount > 20";
} else {
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
      <h1 class="display-4" style="color: #5cb85c;"><strong>All Recipes</strong></h1>
      <br />
      <form action='' method='post'>
         <div class='form-group'>
            <h3>Filter by recipe popularity:</h3>
            <div class='btn-group' style='width: 100%;'>
               <button class="btn btn-success" type='submit' name='up_and_coming'>Up and Coming</button>
               <button class="btn btn-success" type='submit' name='rising_star'>Rising Star</button>
               <button class="btn btn-success" type='submit' name='big_hit'>Big Hit</button>
            </div>
            <button class="btn btn-danger" id='delete' type='submit' name='reset'>Reset</button>
         </div>
      </form>
      <br />
      <p><?php displaySomeRecipe($query) ?></p>
      <br>
      <br>
   </div>

   <?php include('footer.html') ?>

   <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
   <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
   <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</body>

</html>