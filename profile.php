<?php
include "connectdb.php";

// Check if user is logged in or not
if (!isset($_SESSION['uname'])) {
   header('Location: auth/login.php');
}

// logout
if (isset($_POST['logout'])) {
   session_destroy();
   header('Location: auth/login.php');
}

// update favoriteFood
if (isset($_POST['action'])) {
   if (!empty($_POST['action']) && ($_POST['action'] == 'Confirm update')) {
      updateFavoriteFood($_POST['favoriteFood']);
   }
}

// foodies can update their favorite food
function updateFavoriteFood($favoriteFood)
{
   global $db;
   $query = "UPDATE foodies SET favoriteFood = ? WHERE username = ?";
   $stmt = $db->prepare($query);
   $stmt->bind_param("ss", $favoriteFood, $_SESSION['uname']);
   $stmt->execute();
   $stmt->close();
}

// displays all user information, differentiating between cooks and foodies
function displayUserInfo($username)
{
   global $db;

   $query = "SELECT * FROM users WHERE username = '" . $username . "'";
   $result = mysqli_query($db, $query);

   if (mysqli_num_rows($result) > 0) {
      while ($row = mysqli_fetch_assoc($result)) {
         echo "Name: " . $row["firstName"] . " " . $row["lastName"] . "<br>";
         echo "Email: " . $row["email"] . "<br>";

         // if cook, also display cookPinCount and expertise
         if ($row["isCook"]) {
            $cookQuery =
               "SELECT * FROM cookPinCount, users WHERE cookPinCount.username = users.username AND cookPinCount.username = '" . $username . "'";
            $cookResult = mysqli_query($db, $cookQuery);
            if (mysqli_num_rows($cookResult) > 0) {
               while ($row = mysqli_fetch_array($cookResult)) {
                  echo "Cook Pin Count: " . $row['cookPinCount'] . "<br>";
                  echo "Expertise: " . $row['expertise'] . "<br>";
               }
               mysqli_free_result($cookQuery);
            } else {
               echo "0 results from displayCookInfo()";
            }
         }

         // else if foodie, also display favoriteFood
         else {
            $foodieQuery =
               "SELECT foodies.favoriteFood FROM foodies, users WHERE foodies.username = users.username AND foodies.username = '" . $username . "'";
            $foodieResult = mysqli_query($db, $foodieQuery);
            if (mysqli_num_rows($foodieResult) > 0) {
               while ($row = mysqli_fetch_array($foodieResult)) {
                  echo "Favorite Food: " . $row['favoriteFood'];
               }
               mysqli_free_result($foodieQuery);
            } else {
               echo "0 results from displayFoodieInfo()";
            }
         }
      }
      mysqli_free_result($query);
   } else {
      echo "0 results from displayUserInfo()";
   }
   return $result;
}

// gets all the recipes that this user submitted
function getAllRecipes($username)
{
   global $db;

   $query =
      "SELECT * FROM recipes, users WHERE recipes.username = users.username AND users.username = '" . $username . "'";

   $result = mysqli_query($db, $query);

   if (mysqli_num_rows($result) > 0) {
      mysqli_free_result($query);
   } else {
      echo "You haven't submitted any recipes yet!";
   }
   return $result;
}

function isCook($username)
{
   global $db;
   $query = "SELECT isCook FROM users WHERE username = '" . $username . "'";
   $result = mysqli_query($db, $query);
   if (mysqli_num_rows($result) > 0) {
      while ($row = mysqli_fetch_assoc($result)) {
         return ($row["isCook"]);
      }
      mysqli_free_result($query);
   } else {
      echo "0 results from isCook";
   }
   return $result;
}

?>

<!DOCTYPE html>
<html>

<head>
   <meta charset="utf-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge"> <!-- required to handle IE -->
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <title>My Profile</title>
   <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" />
   <link rel="stylesheet" href="custom-style.css" />
</head>

<body>

   <?php
   include('header.html')
   ?>

   <div>
      <br />
      <h1>Welcome back, <?php echo $_SESSION['uname']; ?>!</h1>
      <p><?php displayUserInfo($_SESSION['uname']) ?></p>

      <!-- display cook info for cooks: submitted recipes, cookPinCount, expertise-->
      <?php if (isCook($_SESSION['uname'])) : ?>
         <?php $recipes = getAllRecipes($_SESSION['uname']); ?>
         <h2>Your Recipes</h2>
         <?php foreach ($recipes as $recipe) : ?>
            <table class="w3-table w3-bordered w3-card-4 center" style="width:100%">
               <thead>
                  <tr style="background-color:#B0B0B0">
                     <th width="10%">Recipe Name</th>
                     <th width="50%">Instructions</th>
                     <th width="25%">Country</th>
                     <th width="10%">Cooking Time</th>
                     <th width="10%">Pin Count</th>
                  </tr>
               </thead>
               <tr>
                  <td><?php echo $recipe['recipeName']; ?></td>
                  <td><?php echo $recipe['instructions']; ?></td>
                  <td><?php echo $recipe['country']; ?></td>
                  <td><?php echo $recipe['cookingTime']; ?></td>
                  <td><?php echo $recipe['recipePinCount']; ?></td>

               </tr>
            </table>
         <?php endforeach; ?>

         <!-- display foodie info for foodies: favoriteFood -->
      <?php else : ?>
         <form name="mainForm" action="profile.php" method="post">
            <div class="form-group">
               Update Your Favorite Food!
               <input type="text" class="form-control" name="favoriteFood" required />
               <input type="submit" value="Confirm update" name="action" class="button" title="Confirm update favoriteFood" />
            </div>
         </form>
      <?php endif; ?>

   </div>

   <br />
   <br />
   <br />

   <?php include('footer.html') ?>

   <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
   <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
   <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</body>

</html>