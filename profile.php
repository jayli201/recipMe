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

// foodies: update favoriteFood
if (isset($_POST['update'])) {
   if (!empty($_POST['update']) && ($_POST['update'] == 'Confirm update')) {
      updateFavoriteFood($_POST['favoriteFood']);
   }
}

// cooks: update area of experience
if (isset($_POST['updateAreas'])) {
   if (!empty($_POST['updateAreas']) && ($_POST['updateAreas'] == 'Confirm update')) {
      updateAreasOfExperience($_POST['area']);
   }
}

// delete a recipe
elseif (isset($_POST['delete'])) {
   if (!empty($_POST['delete']) && ($_POST['delete'] == 'Delete')) {
      deleteRecipe($_POST['recipeID']);
   }
}

// delete a review
elseif (isset($_POST['deleteReview'])) {
   if (!empty($_POST['deleteReview']) && ($_POST['deleteReview'] == 'Delete')) {
      deleteReview($_POST['recipeID'], $_POST['cookUsername'], $_POST['review'], $_SESSION['uname']);
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

// cooks can update their areas of expertise
function updateAreasOfExperience($area)
{
   global $db;
   $query = "UPDATE cookPinCount SET area = ? WHERE username = ?";
   $stmt = $db->prepare($query);
   $stmt->bind_param("ss", $area, $_SESSION['uname']);
   $stmt->execute();
   $stmt->close();
}

function deleteRecipe($recipeID)
{
   global $db;
   $query = "DELETE FROM recipes WHERE recipeID = ?";
   $stmt = $db->prepare($query);
   $stmt->bind_param("i", $recipeID);
   $stmt->execute();
   $stmt->close();
}

function displayAreasOfExperience($username)
{
   global $db;

   $query = "SELECT * FROM users, areasOfExperience WHERE users.username = areasOfExperience.username AND areasOfExperience.username = '" . $username . "'";
   $result = mysqli_query($db, $query);

   if (mysqli_num_rows($result) > 0) {
      while ($row = mysqli_fetch_array($result)) {
         echo "Areas of Experience: " . $row['area'] . "<br>";
      }
      mysqli_free_result($query);
   } else {
      echo "Areas of Experience: N/A <br>";
   }

   return $result;
}

function displayOwnReviews($username)
{
   global $db;

   // get all the reviews from this user
   $query = "SELECT * FROM reviews WHERE reviewerUsername = '" . $username . "'";
   $result = mysqli_query($db, $query);
   if (mysqli_num_rows($result) > 0) {
      while ($row = mysqli_fetch_assoc($result)) {

         // get the info of the review, including recipe name
         $recipeQuery =
            "SELECT * FROM reviews, recipes WHERE reviews.recipeID = recipes.recipeID AND reviews.reviewerUsername = '" . $username . "'";

         $result = mysqli_query($db, $recipeQuery);
         if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
               $cookUsername = $row['username'];
               $review = $row['reviews'];
               $recipeName = $row['recipeName'];
               $recipeID = $row['recipeID'];
               $reviewerUsername = $row['reviewerUsername'];
               echo '
               <div class="card" style="width: 60%; border-color: #5cb85c">
                  <div class="card-body" style="width: 100%;">
                     <h4 class="card-subtitle mb-2 text-muted">Review for: ' . $cookUsername . '\'s ' . $recipeName  . '</h4>
                     ' . $review . '<br>
                  </div>
               </div>
               ';
               echo "<form action='' method='post'>
                  <div class='form-group'>
                     <input type='hidden' name='recipeID' value='$recipeID'/>
                     <input type='hidden' name='review' value='$review'/>
                     <input type='hidden' name='cookUsername' value='$cookUsername'/>
                     <input type='submit' id='delete' value='Delete' name='deleteReview'></input>
                  </div>
               </form>";
            }
         }
      }
      mysqli_free_result($query);
   } else {
      echo "<em>You have not submitted any reviews yet!</em>";
   }
   return $result;
}

function deleteReview($recipeID, $cookUsername, $review, $reviewerUsername)
{
   // delete review from db
   global $db;
   $query = "DELETE FROM reviews WHERE recipeID = ? AND username = ? AND reviews = ? AND reviewerUsername = ?";
   $stmt = $db->prepare($query);
   $stmt->bind_param("ssss", $recipeID, $cookUsername, $review, $reviewerUsername);
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
         echo "<em>Name</em>: " . $row["firstName"] . " " . $row["lastName"] . "<br>";
         echo "<em>Email</em>: " . $row["email"] . "<br>";
         // if cook, also display cookPinCount and expertise
         if ($row["isCook"]) {
            $cookQuery =
               "SELECT * FROM cookPinCount, users WHERE cookPinCount.username = users.username AND cookPinCount.username = '" . $username . "'";
            $cookResult = mysqli_query($db, $cookQuery);
            if (mysqli_num_rows($cookResult) > 0) {
               while ($row = mysqli_fetch_array($cookResult)) {
                  echo "<em>Cook Pin Count</em>: " . $row['cookPinCount'] . "<br>";
                  echo "<em>Expertise</em>: " . $row['expertise'] . "<br>";
                  echo "<em>Area of Experience</em>: " . $row['area'] . "<br>";
                  // displayAreasOfExperience($username);
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
                  echo "<em>Favorite Food</em>: " . $row['favoriteFood'];
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
   if (isCook($_SESSION['uname'])) {
      include('cook_header.html');
   } else {
      include('foodie_header.html');
   }
   ?>

   <div>
      <br />
      <h1 class="display-4" style="color: #5cb85c;"><strong>Welcome back, <?php echo $_SESSION['uname']; ?>!</strong></h1>
      <br>
      <p><?php displayUserInfo($_SESSION['uname']) ?></p>

      <!-- display cook info for cooks: submitted recipes, cookPinCount, expertise-->
      <?php if (isCook($_SESSION['uname'])) : ?>
         <form action="profile.php" method="post">
            <div>
               <input type="text" name="area" placeholder='Update your area of experience!' required />
               <input type="submit" value="Confirm update" name="updateAreas" />
            </div>
         </form>
         <br>
         <br>
         <h1 class="display-4" style="color: #5cb85c;"><strong>Your Recipes</strong></h1>
         <br>
         <?php $recipes = displayAllRecipes($_SESSION['uname']); ?>

         <!-- display foodie info for foodies: favoriteFood -->
      <?php else : ?>
         <form action="profile.php" method="post">
            <div>
               <input type="text" name="favoriteFood" placeholder='Update your favorite food!' required />
               <input type="submit" value="Confirm update" name="update" />
            </div>
         </form>
      <?php endif; ?>
      </br>
      </br>

      <h1 class="display-4" style="color: #5cb85c;"><strong>Your Reviews</strong></h1>

      </br>
      <?php displayOwnReviews($_SESSION['uname']); ?>

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