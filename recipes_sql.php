<?php
require("connectdb.php");

//insert recipe
function addRecipe($username, $recipeName, $instructions, $instructionCount, $country, $cookingTime, $recipePinCount,$ingredient,$category,$allergen,$restriction)
{
    global $db;
    // add recipe into db
    $stamt = $db->prepare("INSERT INTO recipes(username, recipeName, instructions, instructionCount, country, cookingTime, recipePinCount) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stamt->bind_param("sssisii", $username, $recipeName, $instructions, $instructionCount, $country, $cookingTime, $recipePinCount);
    $stamt->execute();
    $stamt->close();

   //id for recipe
   $id = '';
   $getID = "SELECT recipeID FROM recipes WHERE recipeName = '" . $recipeName . "'";
   $result = mysqli_query($db, $getID);
   $resultCheck = mysqli_num_rows($result);

   if ($resultCheck > 0) {
      while ($row = mysqli_fetch_assoc($result)){
         $id= $row['recipeID'];
      }
   }


    //add ingredients into db
   $ingredients = explode(",", $ingredient);
   $ingred_array = array_map('trim', $ingredients);
   foreach($ingred_array as $item){
      $ingred = $db->prepare("INSERT INTO ingredients(recipeID, username, ingredient) VALUES (?, ?, ?)");
      $ingred->bind_param("iss", $id, $username, $item);
      $ingred->execute();
      $ingred->close();
   }
    //add categories to db
    $categories = explode(",", $category);
    $categ_array = array_map('trim', $categories);
    foreach($categ_array as $item){
       $categ = $db->prepare("INSERT INTO categories(recipeID, username, category) VALUES (?, ?, ?)");
       $categ->bind_param("iss", $id, $username, $item);
       $categ->execute();
       $categ->close();
    }
          
    //add allergens to db

    $allergens = explode(",", $allergen);
    $allerg_array = array_map('trim', $allergens);
    foreach($allerg_array as $item){
       $allerg = $db->prepare("INSERT INTO allergens(recipeID, username, allergen) VALUES (?, ?, ?)");
       $allerg->bind_param("iss", $id, $username, $item);
       $allerg->execute();
       $allerg->close();
    }

    //add dietary restrictions to db
    $restrictions = explode(",", $restriction);
    $diet_array = array_map('trim', $restrictions);
    foreach($diet_array as $item){
       $diet = $db->prepare("INSERT INTO dietaryRestrictions(recipeID, username, restriction) VALUES (?, ?, ?)");
       $diet->bind_param("iss", $id, $username, $item);
       $diet->execute();
       $diet->close();
    }

}