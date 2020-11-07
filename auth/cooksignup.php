<?php
require("../connectdb.php");

if (isset($_POST['action'])) {
   if (!empty($_POST['action']) && ($_POST['action'] == 'Sign Up')) {
      // add this cook into database
      $sql_query = "INSERT INTO users(username, email, password, firstName, lastName, isCook) VALUES ('" . $_POST["username"] . "', '" . $_POST['email'] . "', '" . $_POST["password"] . "', '" . $_POST["firstName"] . "', '" . $_POST["lastName"] . "', 1)";
      $result = mysqli_query($db, $sql_query);

      // add areas of experience
      $sql_foodquery = "INSERT INTO areasOfExperience(username, area) VALUES ('" . $_POST["username"] . "', '" . $_POST['area'] . "')";
      $result = mysqli_query($db, $sql_foodquery);

      // after creating an account, fill in session details
      $_SESSION['uname'] = $_POST['username'];
      // go to mainpage afterwards
      header('Location: ../mainpage.php');
   }
}
?>

<!DOCTYPE html>
<html>

<head>
   <meta charset="utf-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge"> <!-- required to handle IE -->
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <title>Sign Up</title>
   <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" />
   <link rel="stylesheet" href="../custom-style.css" />
</head>

<body>
   <div>
      <form action="" method="post">
         <br />
         <h1>Sign Up As a Cook</h1>
         <div>
            <input type="text" id="username" name="username" placeholder="Username" required />
         </div>
         <div>
            <input type="text" id="password" name="password" placeholder="Password" required />
         </div>
         <div>
            <input type="text" id="firstName" name="firstName" placeholder="First Name" required />
         </div>
         <div>
            <input type="text" id="lastName" name="lastName" placeholder="Last Name" required />
         </div>
         <div>
            <input type="text" id="email" name="email" placeholder="Email" required />
         </div>
         <div>
            <input type="text" id="area" name="area" placeholder="Areas of Experience" required />
         </div>
         <div>
            <input type="submit" value="Sign Up" id="action" name="action" />
         </div>
      </form>
   </div>
</body>

</html>