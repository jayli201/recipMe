<?php
require("../connectdb.php");

function cookSignUp($username, $email, $password, $firstName, $lastName, $isCook, $area)
{
    global $db;

    // add this cook into database
    $stmt = $db->prepare("INSERT INTO users(username, email, password, firstName, lastName, isCook) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $username, $email, $password, $firstName, $lastName, $isCook);
    $stmt->execute();
    $stmt->close();

    // add area of experience
    $area_stmt = $db->prepare("INSERT INTO areasOfExperience(username, area) VALUES (?, ?)");
    $area_stmt->bind_param("ss", $username, $area);
    $area_stmt->execute();
    $area_stmt->close();
}

function foodieSignUp($username, $email, $password, $firstName, $lastName, $isCook, $favFood)
{
    global $db;

    // add this foodie into database
    $stmt = $db->prepare("INSERT INTO users(username, email, password, firstName, lastName, isCook) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $username, $email, $password, $firstName, $lastName, $isCook);
    $stmt->execute();
    $stmt->close();

    // add favorite food
    $food_stmt = $db->prepare("INSERT INTO foodies(username, favoriteFood) VALUES (?, ?)");
    $food_stmt->bind_param("ss", $username, $favFood);
    $food_stmt->execute();
    $food_stmt->close();
}
