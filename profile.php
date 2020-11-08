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

function updateFavoriteFood($favoriteFood)
{
   global $db;
   $query = "UPDATE foodies SET favoriteFood = ? WHERE username = ?";
   $stmt = $db->prepare($query);
   $stmt->bind_param("ss", $favoriteFood, $_SESSION['uname']);
   $stmt->execute();
   $stmt->close();
}

function displayUserInfo($username)
{
   global $db;

   $query = "SELECT * FROM users WHERE username = '" . $username . "'";
   $result = mysqli_query($db, $query);

   if (mysqli_num_rows($result) > 0) {
      while ($row = mysqli_fetch_assoc($result)) {
         echo "Name: " . $row["firstName"] . " " . $row["lastName"] . "<br>";
         echo "Email: " . $row["email"] . "<br>";
         if (!$row["isCook"]) {
            $favoriteFoodQuery =
               "SELECT foodies.favoriteFood FROM foodies, users WHERE foodies.username = users.username AND foodies.username = '" . $username . "'";
            $favoriteFood = mysqli_query($db, $favoriteFoodQuery);
            if (mysqli_num_rows($favoriteFood) > 0) {
               while ($row = mysqli_fetch_array($favoriteFood)) {
                  echo "Favorite Food: " . $row['favoriteFood'];
               }
               mysqli_free_result($favoriteFood);
            } else {
               echo "0 results";
            }
         }
      }
      mysqli_free_result($query);
   } else {
      echo "0 results";
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
      echo "0 results";
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

      <!-- display foodie info only if not a cook -->
      <?php if (!isCook($_SESSION['uname'])) : ?>
         <form name="mainForm" action="profile.php" method="post">
            <div class="form-group">
               Update Your Favorite Food!
               <input type="text" class="form-control" name="favoriteFood" required />
               <input type="submit" value="Confirm update" name="action" class="button" title="Confirm update favoriteFood" />
            </div>
         </form>

      <?php endif; ?>
   </div>

   <?php include('footer.html') ?>

   <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
   <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
   <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</body>

</html>