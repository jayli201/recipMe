<?php
include "connectdb.php";
// include "display_reviews_sql.php";

function createRecipeCard($row, $recipeID, $cookUsername)
{
   $recipeName = $row["recipeName"];
   $username = $row["username"];
   $country = $row["country"];
   $ingredients = displayIngredients($recipeID, $cookUsername);
   $instructionCount = $row["instructionCount"];
   $instructions = $row["instructions"];
   $cookingTime = $row["cookingTime"];
   $difficulty = displayDifficulty($recipeID, $cookUsername);
   $categories = displayCategories($recipeID, $cookUsername);
   $allergens = displayAllergens($recipeID, $cookUsername);
   $dietaryRestrictions = displayRestrictions($recipeID, $cookUsername);
   $recipePopularity = displayRecipePopularity($recipeID, $cookUsername);
   $recipePinCount = displayRecipePinCount($recipeID, $cookUsername);
   $attempted = hasAttemptedRecipe($recipeID, $cookUsername);

   echo '
      <div class="card" style="width: 100%; border-color: #5cb85c">
         <div class="card-body" style="width: 100%;">
            <h2 class="card-title">' . $recipeName . '</h2>
            <h4 class="card-subtitle mb-2 text-muted">By: ' . $username . '</h4>
            <em>Country</em>: ' . $country . '<br>
            <em>Ingredients</em>: ' . $ingredients . '<br>
            <em>Number of instructions</em>: ' . $instructionCount  . '<br>
            <em>Instructions</em>: ' . $instructions . '<br>
            <em>Difficulty</em>: ' . $difficulty . '<br>
            <em>Total cooking time</em>: ' . $cookingTime . '<br>
            <em>Categories</em>: ' . $categories . '<br>
            <em>Allergens</em>: ' . $allergens . '<br>
            <em>Dietary restrictions</em>: ' . $dietaryRestrictions . '<br>
            <em>Popularity</em>: ' . $recipePopularity . '<br>
            ' . $recipePinCount . ' pins <br>
            ' . hasAttempted($attempted) . '<br>

            <a href="reviews.php?recipeID=' . $recipeID . '&cookID=' . $cookUsername . '" class="card-link">Click to see reviews</a>
         </div>
      </div>
      ';
}

// tried adding this instead of href, doesn't work
// <form action="reviews.php?recipeID=' . $recipeID . '&cookID=' . $cookUsername . '">
//    <div class="form-group">
//    <form action=""><input type="submit" value="Click to see reviews" /></form>
//    </div>
// </form>

// given a recipeID and the cook's username, display a recipe
function displayRecipe($recipeID, $cookUsername)
{
   global $db;

   $query = "SELECT * FROM recipes WHERE recipeID = '" . $recipeID . "' AND username = '" . $cookUsername . "'";
   $result = mysqli_query($db, $query);

   if (mysqli_num_rows($result) > 0) {
      while ($row = $result->fetch_assoc()) {
         // display all recipes 
         createRecipeCard($row, $recipeID, $cookUsername);

         // if cook is viewing his own recipe, have option to delete (and no option to unpein)
         if ($row["username"] == $cookUsername) {
            echo "<form action='profile.php' method='post'>
            <div class='form-group'>
               <input type='hidden' name='recipeID' value='$recipeID'/>
               <button type='submit' id='delete' value='Delete' name='delete'>Delete</button>
            </div>
            </form>";
         }

         // else if cook is not viewing his own recipe, have the option to unpin
         else {
            echo "<form action='' method='post'>
                     <input type='hidden' name='recipeID' value='$recipeID'/>
                     <input type='submit' id='delete' value='Unpin' name='unpin' />
                  </form>
                  <br> <br>";
         }
      }
      mysqli_free_result($query);
   }
   return $result;
}

// given a recipeID and the cook's username, display a recipe, excluding your own recipe
// this function is for displaying pinned recipes since we don't want to display your own recipe
function displayExcludedRecipe($recipeID, $cookUsername)
{
   global $db;

   $query = "SELECT * FROM recipes WHERE recipeID = '" . $recipeID . "' AND username = '" . $cookUsername . "'";
   $result = mysqli_query($db, $query);

   if (mysqli_num_rows($result) > 0) {
      while ($row = $result->fetch_assoc()) {
         // display all recipes excluding your own recipes
         if (!isOwnRecipe($row['recipeID'])) {
            createRecipeCard($row, $recipeID, $cookUsername);

            // option to mark if recipe has been attempted or not
            if (hasAttemptedRecipe($recipeID, $cookUsername) == 0) {
               echo "<form action='' method='post'>
                  <input type='hidden' name='recipeID' value='$recipeID'/>
                  <input type='hidden' name='cookUsername' value='$cookUsername'/>
                  <input type='submit' value='I have finally tried out this recipe!' name='not_attempted' />
               </form>";
            } else {
               echo "<form action='' method='post'>
                  <input type='hidden' name='recipeID' value='$recipeID'/>
                  <input type='hidden' name='cookUsername' value='$cookUsername'/>
                  <input type='submit' id='delete' value='Actually, I have not tried out this recipe.' name='attempted' />
               </form>";
            }
            echo "<form action='' method='post'>
                     <input type='hidden' name='recipeID' value='$recipeID'/>
                     <input type='submit' id='delete' value='Unpin' name='unpin' />
                  </form>
                  <br> <br>";
         }
      }
      mysqli_free_result($query);
   } else {
      echo "<h2> Sorry, there are currently no pinned recipes! </h2>";
   }
   return $result;
}

// this function is for displaying recipes on the home page, giving option to pin or unpin
function displaySomeRecipe($query)
{
   global $db;

   $result = mysqli_query($db, $query);

   if (mysqli_num_rows($result) > 0) {
      while ($row = $result->fetch_assoc()) {
         // display all recipes excluding your own recipes
         $recipeID = $row['recipeID'];
         $cookUsername = $row['username'];
         createRecipeCard($row, $recipeID, $cookUsername);

         if (isPinned($row['recipeID'])) {
            // option to mark if recipe has been attempted or not
            if (hasAttemptedRecipe($recipeID, $cookUsername) == 0) {
               echo "<form action='' method='post'>
                  <input type='hidden' name='recipeID' value='$recipeID'/>
                  <input type='hidden' name='cookUsername' value='$cookUsername'/>
                  <input type='submit' value='I have finally tried out this recipe!' name='not_attempted' />
               </form>";
            } else {
               echo "<form action='' method='post'>
                  <input type='hidden' name='recipeID' value='$recipeID'/>
                  <input type='hidden' name='cookUsername' value='$cookUsername'/>
                  <input type='submit' id='delete' value='Actually, I have not tried out this recipe.' name='attempted' />
               </form>";
            }
            echo "<form action='' method='post'>
                     <input type='hidden' name='recipeID' value='$recipeID'/>
                     <input type='submit' id='delete' value='Unpin' name='unpin' />
                  </form>
                  <br> <br>";
         } else {
            echo "<form action='' method='post'>
                     <input type='hidden' name='recipeID' value='$recipeID'/>
                     <input type='hidden' name='cookUsername' value='$cookUsername'/>
                     <input type='submit' value='Pin' name='pin' />
                  </form>
                  <br> <br>";
         }
      }
      mysqli_free_result($query);
   } else {
      echo "<h2> Sorry, there are currently no recipes! </h2>";
   }
   return $result;
}

function displayPinnedRecipes($query)
{
   global $db;

   $result = mysqli_query($db, $query);

   if (mysqli_num_rows($result) > 0) {
      while ($row = $result->fetch_assoc()) {
         // display all pinned recipes, excluding your recipes
         foreach (displayExcludedRecipe($row["recipeID"], $row["cookUsername"], $row["attempted"]) as $recipe_row) {
            $recipe_row;
         }
      }
      mysqli_free_result($query);
   } else {
      echo "<h2> You haven't pinned any recipes yet! </h2> <br>";
   }
   return $result;
}

// displays all the recipes that this user submitted
function displayAllRecipes($username)
{
   global $db;
   $query =
      "SELECT * FROM recipes, users WHERE recipes.username = users.username AND users.username = '" . $username . "'";

   $result = mysqli_query($db, $query);

   if (mysqli_num_rows($result) > 0) {
      while ($row = mysqli_fetch_array($result)) {
         displayRecipe($row['recipeID'], $username);
      }
      mysqli_free_result($query);
   } else {
      echo "You haven't submitted any recipes yet!";
   }
   return $result;
}

function displayDifficulty($recipeID, $cookUsername)
{
   global $db;
   $difficulty = null;

   $query = "SELECT * FROM instructions WHERE recipeID = '" . $recipeID . "' AND username = '" . $cookUsername . "'";
   $result = mysqli_query($db, $query);

   if (mysqli_num_rows($result) > 0) {
      while ($row = $result->fetch_assoc()) {
         $difficulty = $row["difficulty"];
      }
      mysqli_free_result($query);
   } else {
      $difficulty .= "There are no categories.";
   }
   return $difficulty;
}

// given a recipeID and the cook's username, display the categories of that recipe
function displayCategories($recipeID, $cookUsername)
{
   global $db;
   $categories = null;

   $query = "SELECT * FROM categories WHERE recipeID = '" . $recipeID . "' AND username = '" . $cookUsername . "'";
   $result = mysqli_query($db, $query);

   if (mysqli_num_rows($result) > 0) {
      while ($row = $result->fetch_assoc()) {
         $categories .= " | " . $row["category"];
      }
      mysqli_free_result($query);
   } else {
      $categories .= "There are no categories.";
   }
   return $categories;
}

// given a recipeID and the cook's username, display the ingredients of that recipe
function displayIngredients($recipeID, $cookUsername)
{
   global $db;
   $ingredients = null;

   $query = "SELECT * FROM ingredients WHERE recipeID = '" . $recipeID . "' AND username = '" . $cookUsername . "'";
   $result = mysqli_query($db, $query);

   if (mysqli_num_rows($result) > 0) {
      while ($row = $result->fetch_assoc()) {
         $ingredients .= " | " . $row["ingredient"];
      }
      mysqli_free_result($query);
   } else {
      $ingredients .= "There are no ingredients.";
   }
   return $ingredients;
}

// given a recipeID and the cook's username, display the allergens of that recipe
function displayAllergens($recipeID, $cookUsername)
{
   global $db;
   $allergens = null;

   $query = "SELECT * FROM allergens WHERE recipeID = '" . $recipeID . "' AND username = '" . $cookUsername . "'";
   $result = mysqli_query($db, $query);

   if (mysqli_num_rows($result) > 0) {
      while ($row = $result->fetch_assoc()) {
         $allergens .= " | " . $row["allergen"];
      }
      mysqli_free_result($query);
   } else {
      $allergens .= "There are no allergens.";
   }
   return $allergens;
}

// given a recipeID and the cook's username, display the dietary restrictions of that recipe
function displayRestrictions($recipeID, $cookUsername)
{
   global $db;
   $dietaryRestrictions = null;

   $query = "SELECT * FROM dietaryRestrictions WHERE recipeID = '" . $recipeID . "' AND username = '" . $cookUsername . "'";
   $result = mysqli_query($db, $query);

   if (mysqli_num_rows($result) > 0) {
      while ($row = $result->fetch_assoc()) {
         $dietaryRestrictions .= " | " . $row["restriction"];
      }
      mysqli_free_result($query);
   } else {
      $dietaryRestrictions .= "There are no dietary restrictions.";
   }
   return $dietaryRestrictions;
}

// given a recipeID and the cook's username, display the recipe's popularity
function displayRecipePopularity($recipeID, $cookUsername)
{
   global $db;
   $recipePopularity = null;

   $query = "SELECT popularity FROM recipePinCount WHERE recipeID = '" . $recipeID . "' AND username = '" . $cookUsername . "'";
   $result = mysqli_query($db, $query);

   if (mysqli_num_rows($result) > 0) {
      while ($row = $result->fetch_assoc()) {
         $recipePopularity .= $row["popularity"];
      }
      mysqli_free_result($query);
   }
   return $recipePopularity;
}

// given a recipeID and the cook's username, display the recipe's pin count
function displayRecipePinCount($recipeID, $cookUsername)
{
   global $db;
   $recipePinCount = null;

   $query = "SELECT recipePinCount FROM recipePinCount WHERE recipeID = '" . $recipeID . "' AND username = '" . $cookUsername . "'";
   $result = mysqli_query($db, $query);

   if (mysqli_num_rows($result) > 0) {
      while ($row = $result->fetch_assoc()) {
         $recipePinCount .= $row["recipePinCount"];
      }
      mysqli_free_result($query);
   }
   return $recipePinCount;
}

// check if a user trying to unpin a recipe is actually the cook who submitted the recipe
// if so, indicate that this recipe should not be unpinned by returning true
function isOwnRecipe($recipeID)
{
   global $db;

   $query = "SELECT * FROM pin WHERE recipeID = '" . $recipeID . "' AND username = '" . $_SESSION['uname'] . "'";
   $result = mysqli_query($db, $query);

   if (mysqli_num_rows($result) > 0) {
      while ($row = $result->fetch_assoc()) {
         if ($row["username"] == $row["cookUsername"]) {
            return true;
         } else {
            return false;
         }
      }
   }
}


// given session username, determines if the user is a cook
// can also use the $_SESSION['isCook'] 
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

// checks if pinnned
function isPinned($recipeID)
{
   global $db;

   $query = "SELECT * FROM pin WHERE recipeID = '" . $recipeID . "' AND username = '" . $_SESSION['uname'] . "'";
   $result = mysqli_query($db, $query);

   if (mysqli_num_rows($result) > 0) {
      return true;
   } else {
      return false;
   }
}

// pin a recipe
function pin($recipeID, $cookUsername)
{
   global $db;

   $attempted = 0;

   $query = "INSERT INTO pin(recipeID, cookUsername, username, attempted) VALUES (?, ?, ?, ?)";
   $stmt = $db->prepare($query);
   $stmt->bind_param("ssss", $recipeID, $cookUsername, $_SESSION['uname'], $attempted);
   $stmt->execute();
   $stmt->close();
}

// unpin a recipe
function unpin($recipeID)
{
   global $db;
   $query = "DELETE FROM pin WHERE recipeID = ? AND username = ?";
   $stmt = $db->prepare($query);
   $stmt->bind_param("ss", $recipeID, $_SESSION['uname']);
   $stmt->execute();
   $stmt->close();
}

// convert hasAttempted into readable text
function hasAttempted($attempted)
{
   if ($attempted == 0) {
      return "<em>You have not tried this recipe yet.</em>";
   } else {
      return "<em>You have tried this recipe!</em>";
   }
}

// check if a certain user has attempted a recipe yet
function hasAttemptedRecipe($recipeID, $cookUsername)
{
   global $db;
   $attempted_query = "SELECT attempted FROM pin WHERE recipeID = '" . $recipeID . "' AND username = '" . $_SESSION['uname'] . "' AND cookUsername = '" . $cookUsername . "'";
   $result = mysqli_query($db, $attempted_query);
   $attempted_row = $result->fetch_assoc();
   return $attempted_row['attempted'];
}

// update attempted in pin table
function updateAttempted($attempted, $recipeID, $cookUsername)
{
   global $db;
   $query = "UPDATE pin SET attempted = ? WHERE recipeID = ? AND cookUsername = ? AND username = ?";
   $stmt = $db->prepare($query);
   $stmt->bind_param("ssss", $attempted, $recipeID, $cookUsername, $_SESSION['uname']);
   $stmt->execute();
   $stmt->close();
}
