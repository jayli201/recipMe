<?php
include ("../connectdb.php");
include ("display_recipes_sql.php");
include ("../recipes_sql.php");

// Check if user is logged in or not
if (!isset($_SESSION['uname'])) {
    header('Location: auth/login.php');
}

// Check that user is a cook
// If a foodie, then redirect to mainpage.php 
// foodies cannot submit a recipe
if (!isCook($_SESSION['uname'])) {
    header('Location: mainpage.php');
}

// logout
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: auth/login.php');
}

//insert the recipe
if (isset($_POST['action'])) {
    if (!empty($_POST['action']) && ($_POST['action'] == 'Add')) {
       addRecipe($_SESSION['uname'], $_POST['recipeName'], $_POST['instructions'], $_POST['instructionCount'], $_POST['country'], $_POST['cookingTime'],1);
       //stay on the submit recipe page
       header('Location: submit_recipes.php');
    }
 }


?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge"> <!-- required to handle IE -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Submit a Recipe</title>
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
        <h1>Submit a Recipe</h1>
    </div>

    <form action="" method="post">
         <br />
         <div>
            <input type="text" id="recipeName" name="recipeName" placeholder="Name" required />
         </div>
         <div>
         <textarea type="text" id="instructions" name="instructions" placeholder="Instructions" required></textarea>
         </div>
         <div>
            <input type="number" id="instructionCount" name="instructionCount" placeholder="# of Instructions" min="0" required />
         </div>
         <div>
            <input type="number" id="cookingTime" name="cookingTime" placeholder="Total Time" min="0" required />
         </div>
         <div>
         <input type="text" id="country" name="country" placeholder="Country" required />
         </div>
         <div>
            <input type="submit" value="Add" id="action" name="action" />
         </div>
      </form>

    <?php include('footer.html') ?>

    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</body>

</html>