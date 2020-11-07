<?php
   require("../connectdb.php");

   if (isset($_POST['login'])){
      // escape special characters for the username and password
      $uname = mysqli_real_escape_string($db,$_POST['uname']);
      $password = mysqli_real_escape_string($db,$_POST['pwd']);
  
      if ($uname != "" && $password != "") {
         $sql_query = "SELECT count(*) AS cntUser FROM users WHERE username='".$uname."' and password='".$password."'";
         $result = mysqli_query($db,$sql_query);
         $row = mysqli_fetch_array($result);
  
         $count = $row['cntUser'];
         // if you found a user...
         if ($count > 0){
            // fill in session details
            $_SESSION['uname'] = $uname;
            // go to mainpage afterwards
            header('Location: ../mainpage.php');
         } else {
            echo "Invalid username and password";
         }
      }
   }
?>

<!DOCTYPE html>
<html>
<head>
   <meta charset="utf-8">   
   <meta http-equiv="X-UA-Compatible" content="IE=edge">  <!-- required to handle IE -->
   <meta name="viewport" content="width=device-width, initial-scale=1">  
   <title>Welcome</title> 
   <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" />
   <link rel="stylesheet" href="../custom-style.css" />   
</head>
   <div>
      <br />
      <h1>Welcome to RecipMe!</h1>
      <h2>Where anyone can submit their own recipes and explore new recipes</h3>
      <form method="post" action="">
         <div>
            <h1>Login</h1>
            <div>
               <input type="text" class="textbox" id="uname" name="uname" placeholder="Username" required/>
            </div>
            <div>
               <input type="password" class="textbox" id="uname" name="pwd" placeholder="Password" required/>
            </div>
            <div>
               <button type="submit" value="Login" name="login" id="login">Login</button>
            </div>
        </div>
      </form>
      <br />
      <h1>Don't have an account yet?</h1>
      <h2>Sign up as a:</h2>
      <div>
         <button type="submit" value="Sign Up As a Cook" id="cook" onClick="window.location.href='cooksignup.php'">Cook</button>
         <button type="submit" value="Sign Up As a Foodie" id="foodie" onClick="window.location.href='foodiesignup.php'">Foodie</button>
      </div>
   </div> 
</html>