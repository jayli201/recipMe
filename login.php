<?php
   require("connectdb.php");
   require('user_db.php');

   if (isset($_POST['action'])){
      if (!empty($_POST['action']) && ($_POST['action'] == 'Sign Up')) {
         // add this user into database
         // addUser not working rn
         addUser($_POST['username'], $_POST['email'], $_POST['password'], $_POST['firstName'], $_POST['lastName'], 1);
         // after creating an account, fill in session details
         $_SESSION['uname'] = $_POST['username'];
         // go to mainpage afterwards
         header('Location: mainpage.php');
      }
   }

   if (isset($_POST['login'])){
      // escape special characters for the username and password
      $uname = mysqli_real_escape_string($db,$_POST['uname']);
      $password = mysqli_real_escape_string($db,$_POST['pwd']);
  
      if ($uname != "" && $password != ""){
         $sql_query = "SELECT count(*) AS cntUser FROM users WHERE username='".$uname."' and password='".$password."'";
         $result = mysqli_query($db,$sql_query);
         $row = mysqli_fetch_array($result);
  
         $count = $row['cntUser'];
         // if you found a user...
         if ($count > 0){
            // fill in session details
            $_SESSION['uname'] = $uname;
            // go to mainpage afterwards
            header('Location: mainpage.php');
         } else{
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
   <title>Log In</title> 
   <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" />
   <link rel="stylesheet" href="custom-style.css" />   
</head>
   <div>
      <form method="post" action="">
         <div>
            <h1>Login</h1>
            <div>
               <input type="text" class="textbox" id="uname" name="uname" placeholder="Username" />
            </div>
            <div>
               <input type="password" class="textbox" id="uname" name="pwd" placeholder="Password"/>
            </div>
            <div>
               <input type="submit" value="Login" name="login" id="login" />
            </div>
        </div>
      </form>
      <br />
      <form action="" method="post">
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
            <!-- convert this to a free form -->
            <input type="text" id="areasOfExperience" name="areasOfExperience" placeholder="Areas of Experience" required />
         </div>
         <input type="submit" value="Sign Up" id="action" name="action" />
      </form>  
      <br />
      <!-- finish this for foodies -->
      <form>
         <h1>Sign Up as a Foodie</h1>
      </form>
   </div> 
</html>