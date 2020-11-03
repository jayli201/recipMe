<?php
require('connectdb.php');   // depending on where u put connectdb.php, may need to put /path/connectdb.php
require('user_db.php');
$users = getAllUsers();

if($_SERVER['REQUEST_METHOD'] == 'POST') {  
  if (!empty($_POST['action']) && ($_POST['action'] == 'Add')) {
    addUser($_POST['username'], $_POST['email'], $_POST['password'], $_POST['firstName'], $_POST['lastName'], $_POST['isCook']);
    $users = getAllUsers();
  }
  elseif (!empty($_POST['action']) && ($_POST['action'] == 'Delete')) {
    deleteuser($_POST['user_to_delete']);
    $users = getAllUsers();
  } 

  elseif (!empty($_POST['action']) && ($_POST['action'] == 'Confirm update')) {
    updateUser($_POST['username'], $_POST['email'], $_POST['password'], $_POST['firstName'], $_POST['lastName'], $_POST['isCook']);
    $users = getAllUsers();
  } 
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">  
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="author" content="your name">
  <meta name="description" content="include some description about your page">      
  <title>DB interfacing</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
  <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
  <link rel="shortcut icon" href="http://www.cs.virginia.edu/~up3f/cs4750/images/db-icon.png" type="image/ico" />  
</head>

<body>
<div class="container">

<h1>Create Account</h1>

<!-- <form action="formprocessing.php" method="post">  -->
<!-- action tag allows you to specify where to send data to -->
<!-- method tag specifies how to package data
    post: no limitation on size
    get: limited data
-->
<form name="mainForm" action="userform.php" method="post">
  <div class="form-group">
    Username:
    <input type="text" class="form-control" name="username" required />        
  </div>  
  <div class="form-group">
  <div class="form-group">
    Password:
    <input type="text" class="form-control" name="password" required />        
  </div> 
    First name:
    <input type="text" class="form-control" name="firstName" required /> 
  </div>  
  <div class="form-group">
    Last name:
    <input type="text" class="form-control" name="lastName" required />        
  </div> 
  <div class="form-group">
    Email:
    <input type="text" class="form-control" name="email" required />        
  </div> 
   <div class="form-group">
    Is Cook:
    <input type="text" class="form-control" name="isCook" required />        
  </div> 
     
  <input type="submit" value="Add" name="action" class="btn btn-dark" title="Insert a user into a users table" />
  <input type="submit" value="Confirm update" name="action" class="btn btn-dark" title="Confirm update a user" />
  
</form>  

  
<hr/>
<h2>List of Users</h2>
<table class="w3-table w3-bordered w3-card-4 center" style="width:70%">
  <thead>
  <tr style="background-color:#B0B0B0">
    <th width="25%">Username</th>        
    <th width="25%">First name</th>        
    <th width="25%">Last Name</th> 
    <th width="10%">Update ?</th>
    <th width="10%">Delete ?</th> 
  </tr>
  </thead>
  <!-- must have a colon after foreach -->
  <?php foreach ($users as $user): ?>   
  <tr>
    <td><?php echo $user['username']; ?></td>
    <td><?php echo $user['firstName']; ?></td>        
    <td><?php echo $user['lastName']; ?></td>       
    <td>
      <form action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
        <input type="submit" value="Update" name="action" class="btn btn-primary" title="Update the record" />             
        <input type="hidden" name="user_to_update" value="<?php echo $user['username']?>" />
      </form> 
    </td>                        
    <td>
      <form action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
        <input type="submit" value="Delete" name="action" class="btn btn-danger" title="Permanently delete the record" />      
        <input type="hidden" name="user_to_delete" value="<?php echo $user['username']?>" />
      </form>
    </td>                                              
  </tr>
  <?php endforeach; ?>
</table>
        
</div>    
</body>
</html>
  
