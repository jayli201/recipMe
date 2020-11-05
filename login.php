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
<body>
  
<!-- include header of the page (presumably, logo and top menu) -->  
<?php include('header.html') ?>
  
<div>  
  <h1>Log In</h1>
  <form action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
    Username: <input type="text" name="name" required /> <br/>
    Password: <input type="password" name="pwd" required /> <br/>
    <button type="submit" value="submit">Log in</button>
  </form>
  
 
<?php 

function authenticate()
{
   global $mainpage;
   
   // Assume there exists a hashed password for a user (username='demo', password='nan')
   // in a database or file and we've retrieved and assigned it to a $hash variable
   $hash = '$2y$10$N3FG5ctr4F2tvmcVtn0X.eIMFBqfdWaU9TsRSjrjCKYMr/N7mjbzS';
   
   if ($_SERVER['REQUEST_METHOD'] == 'POST')       // check for the expected incoming request (i.e., form submission) 
   {
   	// To retrieve the pwd from the form entry, access $_POST['pwd']   	
   	// htmlspecialchars() stops script tags from being able to be executed and renders them as plaintext
   	$pwd = htmlspecialchars($_POST['pwd']);   	
   	
   	echo "password = $pwd <br/><br/>";           // display the string pwd on the screen
   	
   	// password_hash(incoming_password, algo_to_hash) creates a password hash
   	
   	// PASSWORD_DEFAULT indicates the currently algorithm; thus it may be changed as new algo is added to PHP.
   	// current version of PHP, PASSWORD_DEFAULT is PASSWORD_BCRYPT
   	
   	// Display hash password. Let's try creating it multiple times, notice that the results are different.  
   	echo "password_hash (PASSWORD_DEFAULT) =" . password_hash($pwd, PASSWORD_DEFAULT) . "<br/><br/>";
   	echo "password_hash (PASSWORD_BCRYPT) #1 =" . password_hash($pwd, PASSWORD_BCRYPT) . "<br/><br/>";
   	echo "password_hash (PASSWORD_BCRYPT) #2 =" . password_hash($pwd, PASSWORD_BCRYPT) . "<br/>";
   	
   	// md5($pwd)      // md5, the old way to create hash password, always generates the same string and is very weak.
   	
   	// echo "password_hash (PASSWORD_ARGON2I) =" . password_hash($pwd, PASSWORD_ARGON2I);
   	// This algorithm is only available if PHP has been compiled with Argon2 support.
   	
   	// To compare the pwd (from the form entry) with the hash password presumably retrieved from the database, 
   	// use password_verify(form_entry_password, hash_password_from_db) 
  	
   	echo '<br/> password_verify =' . password_verify($pwd, $hash) . " (1 if the passwords match, '' otherwise) <br/>";
   	
   	// password_verify(incoming_password, existing_password) returns
   	//   true ('1') if the incoming_password and existing_password match
   	//   false ('') otherwise
   	
   	if (password_verify($pwd, $hash))
   	{
   		// successfully login, redirect a user to the main page
   		// header("Location: ". $mainpage);        // uncomment this to redirect the user to another page 
   		
   		// Alternatively, we can hardcoard the redirected URL here
   		// header("Location: http://localhost/cs4750/mainpage.php");
   	}
   	else
   		echo "<span class='msg'>Username and password do not match our record</span> <br/>";
   }
}


$mainpage = "mainpage.php";   
authenticate();

?>
</div>

<!-- include footer of the page  (presumably, bottom menu) -->
<?php include('footer.html') ?>
  
<!-- script needed for menu -->  
  <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>  
  
</body>
</html>