<?php
require("../connectdb.php");

if (isset($_POST['login'])) {
   // escape special characters for the username and password
   $uname = mysqli_real_escape_string($db, $_POST['uname']);
   $password = mysqli_real_escape_string($db, $_POST['pwd']);

   if ($uname != "" && $password != "") {
      $query = "SELECT isCook FROM users WHERE BINARY username = ? AND BINARY password = ?";
      $stmt = $db->prepare($query);
      $stmt->bind_param("ss", $uname, $password);
      $stmt->execute();
      $stmt->bind_result($isCook);
      $stmt->store_result();

      if ($stmt->num_rows() == 1) {
         // fill in session details
         $_SESSION['uname'] = $uname;
         $_SESSION['isCook'] = $isCook;
         // go to mainpage afterwards
         header('Location: ../mainpage.php');
         exit();
      } else {
         $error = "Invalid username or password";
      }
      $stmt->close();
   }
}
?>

<!DOCTYPE html>
<html>

<head>
   <meta charset="utf-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge"> <!-- required to handle IE -->
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <title>Welcome</title>
   <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" />
   <link rel=" stylesheet" href="../custom-style.css" />
   <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
   <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
   <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</head>

<body>
   <?php
   include('main_header.html');
   ?>
   <br />
   <form method="post" action="">
      <div>
         <h1 class="display-4" style="color: #5cb85c;"><strong>Login</strong></h1>
         <br>
         <div>
            <input type="text" class="textbox" id="uname" name="uname" placeholder="Username" required />
         </div>
         <div>
            <input type="password" class="textbox" id="uname" name="pwd" placeholder="Password" required />
         </div>
         <div style="color:#cc0000; text-align:left; margin-top:10px; margin-bottom:10px"><?php echo $error; ?></div>
         <br>
         <div>
            <input type="submit" value="Login" name="login" id="login" />
         </div>
      </div>
   </form>
   <?php include('../footer.html') ?>
</body>

</html>