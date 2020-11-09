<?php
include "connectdb.php";

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

// sort recipes by allergens
function sortByAllergens($allergen)
{
   global $db;

   $query = "SELECT * FROM recipes NATURAL LEFT OUTER JOIN allergens WHERE allergen <> '" . $allergen . "' OR allergen IS NULL";
   $result = mysqli_query($db, $query);

   if (mysqli_num_rows($result) > 0) {
      while ($row = $result->fetch_assoc()) {
         // display all pinned recipes, excluding your recipes
         foreach (displayRecipe($row["recipeID"], $row["cookUsername"], $row["attempted"]) as $recipe_row) {
            $recipe_row;
         }
      }
      mysqli_free_result($query);
   } else {
      echo "<h2> Sorry, there are no recipes that have no </h2> " . $allergen . " allergy. <br>";
   }
   return $result;
}

// given a recipeID and the cook's username, display a recipe
function displayRecipe($recipeID, $cookUsername)
{
   global $db;

   $query = "SELECT * FROM recipes WHERE recipeID = '" . $recipeID . "' AND username = '" . $cookUsername . "'";
   $result = mysqli_query($db, $query);

   if (mysqli_num_rows($result) > 0) {
      while ($row = $result->fetch_assoc()) {
         // display all recipes 
         echo "<h2>" . $row["recipeName"] . "</h2> <br>";
         echo "<h4> By: " . $row["username"] . "</h4> <br>";
         echo "<em>Country</em>: " . $row["country"] . "<br>";
         echo "<em>Ingredients</em>: ";
         foreach (displayIngredients($recipeID, $cookUsername) as $ingredient_row) {
            $ingredient_row;
         }
         echo "<br> <em>Number of instructions</em>: " . $row["instructionCount"] . "<br>";
         echo "<em>Instructions</em>: " . $row["instructions"] . "<br>";
         echo "<em>Total cooking time</em>: " . $row["cookingTime"] . " minutes " . "<br>";
         echo "<em>Categories</em>: ";
         foreach (displayCategories($recipeID, $cookUsername) as $category_row) {
            $category_row;
         }
         echo " <br> <em>Allergens</em>: ";
         foreach (displayAllergens($recipeID, $cookUsername) as $allergen_row) {
            $allergen_row;
         }
         echo " <br> <em>Dietary restrictions</em>: ";
         foreach (displayRestrictions($recipeID, $cookUsername) as $restriction_row) {
            $restriction_row;
         }
         echo "<br>";
         echo "<em>Popularity</em>: ";
         foreach (displayRecipePopularity($recipeID, $cookUsername) as $popularity_row) {
            $popularity_row;
         }
         echo "<br>";
         foreach (displayRecipePinCount($recipeID, $cookUsername) as $pin_count_row) {
            $pin_count_row;
            echo " pins <br>";
         }
         echo hasAttempted($row["attempted"]) . "<br> <br>";

         // if cook is viewing his own recipe, have option to delete (and no option to unpein)
         if ($row["username"] == $cookUsername) {
            echo "<form action='profile.php' method='post'>
            <div class='form-group'>
               <input type='hidden' name='recipeID' value='$recipeID'/>
               <input type='submit' value='Delete' name='delete' class='button' />
            </div>
            </form>";
         }

         // else if cook is not viewing his own recipe, have the option to unpin
         else {
            echo "<form action='' method='post'>
                     <input type='hidden' name='recipeID' value='$recipeID'/>
                     <input type='submit' value='Unpin' name='unpin' />
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
            echo "<h2>" . $row["recipeName"] . "</h2> <br>";
            echo "<h4> By: " . $row["username"] . "</h4> <br>";
            echo "<em>Country</em>: " . $row["country"] . "<br>";
            echo "<em>Ingredients</em>: ";
            foreach (displayIngredients($recipeID, $cookUsername) as $ingredient_row) {
               $ingredient_row;
            }
            echo "<br> <em>Number of instructions</em>: " . $row["instructionCount"] . "<br>";
            echo "<em>Instructions</em>: " . $row["instructions"] . "<br>";
            echo "<em>Total cooking time</em>: " . $row["cookingTime"] . " minutes " . "<br>";
            echo "<em>Categories</em>: ";
            foreach (displayCategories($recipeID, $cookUsername) as $category_row) {
               $category_row;
            }
            echo " <br> <em>Allergens</em>: ";
            foreach (displayAllergens($recipeID, $cookUsername) as $allergen_row) {
               $allergen_row;
            }
            echo " <br> <em>Dietary restrictions</em>: ";
            foreach (displayRestrictions($recipeID, $cookUsername) as $restriction_row) {
               $restriction_row;
            }
            echo "<br>";
            echo "<em>Popularity</em>: ";
            foreach (displayRecipePopularity($recipeID, $cookUsername) as $popularity_row) {
               $popularity_row;
            }
            echo "<br>";
            foreach (displayRecipePinCount($recipeID, $cookUsername) as $pin_count_row) {
               $pin_count_row;
               echo " pins <br>";
            }
            echo hasAttempted($row["attempted"]) . "<br> <br>";
            echo "<form action='' method='post'>
                     <input type='hidden' name='recipeID' value='$recipeID'/>
                     <input type='submit' value='Unpin' name='unpin' />
                  </form>
                  <br> <br>";
         }
      }
      mysqli_free_result($query);
   }
   return $result;
}

// given the session's username, display your pinned recipes, excluding your own recipes
function displayPinnedRecipes($username)
{
   global $db;

   $query = "SELECT * FROM pin WHERE username = '" . $username . "'";
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

// given a recipeID and the cook's username, display the categories of that recipe
function displayCategories($recipeID, $cookUsername)
{
   global $db;

   $query = "SELECT * FROM categories WHERE recipeID = '" . $recipeID . "' AND username = '" . $cookUsername . "'";
   $result = mysqli_query($db, $query);

   if (mysqli_num_rows($result) > 0) {
      while ($row = $result->fetch_assoc()) {
         echo " | " . $row["category"];
      }
      mysqli_free_result($query);
   } else {
      echo "There are no categories.";
   }
   return $result;
}

// given a recipeID and the cook's username, display the ingredients of that recipe
function displayIngredients($recipeID, $cookUsername)
{
   global $db;

   $query = "SELECT * FROM ingredients WHERE recipeID = '" . $recipeID . "' AND username = '" . $cookUsername . "'";
   $result = mysqli_query($db, $query);

   if (mysqli_num_rows($result) > 0) {
      while ($row = $result->fetch_assoc()) {
         echo " | " . $row["ingredient"];
      }
      mysqli_free_result($query);
   } else {
      echo "There are no ingredients.";
   }
   return $result;
}

// given a recipeID and the cook's username, display the allergens of that recipe
function displayAllergens($recipeID, $cookUsername)
{
   global $db;

   $query = "SELECT * FROM allergens WHERE recipeID = '" . $recipeID . "' AND username = '" . $cookUsername . "'";
   $result = mysqli_query($db, $query);

   if (mysqli_num_rows($result) > 0) {
      while ($row = $result->fetch_assoc()) {
         echo " | " . $row["allergen"];
      }
      mysqli_free_result($query);
   } else {
      echo "There are no allergens.";
   }
   return $result;
}

// given a recipeID and the cook's username, display the dietary restrictions of that recipe
function displayRestrictions($recipeID, $cookUsername)
{
   global $db;

   $query = "SELECT * FROM dietaryRestrictions WHERE recipeID = '" . $recipeID . "' AND username = '" . $cookUsername . "'";
   $result = mysqli_query($db, $query);

   if (mysqli_num_rows($result) > 0) {
      while ($row = $result->fetch_assoc()) {
         echo " | " . $row["restriction"];
      }
      mysqli_free_result($query);
   } else {
      echo "There are no dietary restrictions.";
   }
   return $result;
}

// given a recipeID and the cook's username, display the recipe's popularity
function displayRecipePopularity($recipeID, $cookUsername)
{
   global $db;

   $query = "SELECT popularity FROM recipePinCount WHERE recipeID = '" . $recipeID . "' AND username = '" . $cookUsername . "'";
   $result = mysqli_query($db, $query);

   if (mysqli_num_rows($result) > 0) {
      while ($row = $result->fetch_assoc()) {
         echo $row["popularity"];
      }
      mysqli_free_result($query);
   }
   return $result;
}

// given a recipeID and the cook's username, display the recipe's pin count
function displayRecipePinCount($recipeID, $cookUsername)
{
   global $db;

   $query = "SELECT recipePinCount FROM recipePinCount WHERE recipeID = '" . $recipeID . "' AND username = '" . $cookUsername . "'";
   $result = mysqli_query($db, $query);

   if (mysqli_num_rows($result) > 0) {
      while ($row = $result->fetch_assoc()) {
         echo $row["recipePinCount"];
      }
      mysqli_free_result($query);
   }
   return $result;
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
      return "<em>You have not attempted this recipe yet.</em>";
   } else {
      return "<em>You have attempted this recipe!</em>";
   }
}
