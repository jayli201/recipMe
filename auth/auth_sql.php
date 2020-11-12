<?php
require("../connectdb.php");

function cookSignUp($username, $email, $password, $firstName, $lastName, $isCook, $area)
{
    global $db;

    // add this cook into database
    $stmt = $db->prepare("INSERT INTO users(username, email, password, firstName, lastName, isCook) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $username, $email, $password, $firstName, $lastName, $isCook);
    if (!$stmt->execute()) {
        return "Username already taken. Please try again with a new username.";
    } else {
        // add area of experience
        $expertise = "amateur cook";
        $cookPinCount = 0;
        $area_stmt = $db->prepare("INSERT INTO cookPinCount(username, cookPinCount, expertise, area) VALUES (?, ?, ?, ?)");
        $area_stmt->bind_param("ssss", $username, $cookPinCount, $expertise, $area);
        $area_stmt->execute();
        $area_stmt->close();

        // after creating an account, fill in session details
        $_SESSION['uname'] = $_POST['username'];
        $_SESSION['isCook'] = 1;
        // go to mainpage afterwards
        header('Location: ../mainpage.php');
    }
    $stmt->close();
}

function foodieSignUp($username, $email, $password, $firstName, $lastName, $isCook, $favFood)
{
    global $db;

    // add this foodie into database
    $stmt = $db->prepare("INSERT INTO users(username, email, password, firstName, lastName, isCook) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $username, $email, $password, $firstName, $lastName, $isCook);
    if (!$stmt->execute()) {
        return "Username already taken. Please try again with a new username.";
    } else {
        // add favorite food
        $food_stmt = $db->prepare("INSERT INTO foodies(username, favoriteFood) VALUES (?, ?)");
        $food_stmt->bind_param("ss", $username, $favFood);
        $food_stmt->execute();
        $food_stmt->close();

        // after creating an account, fill in session details
        $_SESSION['uname'] = $_POST['username'];
        $_SESSION['isCook'] = 0;
        // go to mainpage afterwards
        header('Location: ../mainpage.php');
    }
    $stmt->close();
}
