<?php
include "connectdb.php";
include "display_recipes_sql.php";

function findRecipeName($recipeID)
{

  global $db;
  $query =
    "SELECT * FROM reviews, recipes WHERE reviews.recipeID = recipes.recipeID AND recipes.recipeID = '" . $recipeID . "'";

  // "SELECT * FROM cookPinCount, users WHERE cookPinCount.username = users.username AND cookPinCount.username = '" . $username . "'";
  $result = mysqli_query($db, $query);
  if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_array($result)) {
      $recipeName = $row['recipeName'];
    }
    mysqli_free_result($result);
  } else {
    echo "0 results from displayReviews()";
  }
  return $recipeName;
}

function displayReviews($recipeID, $cookUsername)
{
  global $db;
  $query = "SELECT * FROM reviews WHERE recipeID = '" . $recipeID . "' AND username = '" . $cookUsername . "'";
  $result = mysqli_query($db, $query);

  if (mysqli_num_rows($result) > 0) {
    while ($row = $result->fetch_assoc()) {
      // display all reviews 
      $review = $row["reviews"];
      $reviewerUsername = $row["reviewerUsername"];

      echo '
          <div class="card" style="width: 100%;">
            <div class="card-body" style="width: 100%;">
                <h4 class="card-subtitle mb-2 text-muted">Review by: ' . $reviewerUsername . '</h4>
                ' . $review . '<br>

            </div>
          </div>
      ';
    }
    mysqli_free_result($query);
  }
  return $result;
}
