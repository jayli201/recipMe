<?php
require("../connectdb.php");
require("auth_sql.php");

if (isset($_POST['action'])) {
   if (!empty($_POST['action']) && ($_POST['action'] == 'Sign Up')) {
      $error = foodieSignUp($_POST['username'], $_POST['email'], $_POST['password'], $_POST['firstName'], $_POST['lastName'], 0, $_POST['favFood']);
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
   <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
   <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
   <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</head>

<body>
   <?php
   include('main_header.html');
   ?>
   <br />
   <h1 class="display-4" style="color: #5cb85c;"><strong>Sign Up As a Foodie</strong></h1>
   <br>
   <form action="" method="post">
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
         <input type="text" id="favFood" name="favFood" placeholder="Favorite Food" />
      </div>
      <div style="color:#cc0000; text-align:left; margin-top:10px; margin-bottom:10px"><?php echo $error; ?></div>
      <br>
      <div>
         <input type="submit" class="btn btn-success" value="Sign Up" id="action" name="action" />
      </div>
      <br>
   </form>
   <br><br>

   <?php include('../footer.html') ?>
</body>

</html>