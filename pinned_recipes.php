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

// unpin a recipe, but only if user is not the cook
if (isset($_POST['unpin'])) {
   if (!empty($_POST['unpin']) && ($_POST['unpin'] == 'Unpin') && (!isOwnRecipe($_POST['recipeID']))) {
      unpin($_POST['recipeID']);
   }
}

if (isset($_POST['not_attempted'])) {
   if (!empty($_POST['not_attempted']) && ($_POST['not_attempted'] == 'I have finally tried out this recipe!')) {
      updateAttempted(1, $_POST['recipeID'], $_POST['cookUsername']);
   }
}

if (isset($_POST['attempted'])) {
   if (!empty($_POST['attempted']) && ($_POST['attempted'] == 'Actually, I have not tried out this recipe.')) {
      updateAttempted(0, $_POST['recipeID'], $_POST['cookUsername']);
   }
}

// search for specific recipes
if (isset($_POST['submit'])) {
   $query = "SELECT * FROM pin p JOIN recipes r on p.cookUsername = r.username WHERE p.username = '" . $_SESSION['uname'] . "' AND r.recipeName LIKE '%" . $_POST['search'] . "%'";
   echo $query;
}
// sort by recipe attempted
elseif (isset($_POST['attempted_filter'])) {
   $query = "SELECT * FROM pin WHERE username = '" . $_SESSION['uname'] . "' AND attempted = 1";
} elseif (isset($_POST['not_attempted_filter'])) {
   $query = "SELECT * FROM pin WHERE username = '" . $_SESSION['uname'] . "' AND attempted = 0";
} else {
   $query = "SELECT * FROM pin WHERE username = '" . $_SESSION['uname'] . "'";
}

?>

<!DOCTYPE html>
<html>

<head>
   <meta charset="utf-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge"> <!-- required to handle IE -->
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <title>My Pinned Recipes</title>
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
      <h1 class="display-4" style="color: #5cb85c;"><strong>Your Pinned Recipes</strong></h1>
      <form action='' method='post'>
         <div>
            <input type="text" name="search" placeholder='Look up specific recipes' required />
            <input type="submit" value="Search" name="submit" />
         </div>
      </form>
      <br>
      <form action='' method='post'>
         <div class='form-group'>
            <h3>Filter by which recipes you've tried:</h3>
            <div class='btn-group' style='width: 100%;'>
               <button class="btn btn-success" type='submit' name='attempted_filter'>Have tried!</button>
               <button class="btn btn-success" type='submit' name='not_attempted_filter'>Have not tried</button>
            </div>
            <button class="btn btn-danger" id='delete' type='submit' name='reset'>Reset</button>
         </div>
      </form>
      <br>
      <p><?php displayPinnedRecipes($query) ?></p>
      <br />
      <?php include('scroll.php') ?>
   </div>

   <?php include('footer.html') ?>

   <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
   <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
   <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</body>

</html>