<?php
require("../connectdb.php");

//insert recipe
function addRecipe($username, $recipeName, $instructions, $instructionCount, $country, $cookingTime, $recipePinCount)
{
    global $db;
    // add recipe into db
    $stamt = $db->prepare("INSERT INTO recipes(username, recipeName, instructions, instructionCount, country, cookingTime) VALUES (?, ?, ?, ?, ?, ?)");
    $stamt->bind_param("ssssss", $username, $recipeName, $instructions, $instructionCount, $country, $cookingTime);
    $stamt->execute();
    $stamt->close();
}
