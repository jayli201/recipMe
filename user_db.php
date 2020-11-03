<?php 

// Prepared statement (or parameterized statement) happens in 2 phases:
//   1. prepare() sends a template to the server, the server analyzes the syntax
//                and initialize the internal structure.
//   2. bind value (if applicable) and execute
//      bindValue() fills in the template (~fill in the blanks.
//                For example, bindValue(':name', $name);
//                the server will locate the missing part signified by a colon
//                (in this example, :name) in the template
//                and replaces it with the actual value from $name.
//                Thus, be sure to match the name; a mismatch is ignored.
//      execute() actually executes the SQL statement


function getAllUsers()
{
  global $db;

  $query = "SELECT * FROM users";
  $statement = $db->prepare($query);  // prepare compiles to make query an executable version
  $statement->execute();

  $results = $statement->fetchAll();  // returns of array of rows
	
  $statement->closeCursor();
  
  return $results;    // whoever calls this function will get to use $results
}

function addUser($username, $email, $password, $firstName, $lastName, $isCook)
{
  // global allows us to connect to instance of db in connectdb.db
  global $db;

  // good way to write (prepare)
  $query = "INSERT INTO users VALUES(:username, :email, :password, :firstName, :lastName, :isCook)";  
      // use : (colon) for template
  $statement = $db->prepare($query);
  $statement->bindValue(':username', $username);
  $statement->bindValue(':email', $email);
  $statement->bindValue(':password', $password);
  $statement->bindValue(':firstName', $firstName);
  $statement->bindValue(':lastName', $lastName);
  $statement->bindValue(':isCook', $isCook);
  $statement->execute();      // run query
  $statement->closeCursor();  // release hold on this connection

}
   
function getUserInfo_by_username($username)
{
  // global allows us to connect to instance of db in connectdb.db
  global $db;

  // bad way to write
      // . is used for concat
  $query = "SELECT * FROM users WHERE name = '" . $username . "'";  
  $statement = $db->query($query);  
      // executes the quesry
      // use -> to call function

  $results = $statement->fetch();

  $statement->clousecursor();
  
  return $results;

}

function updateUser($username, $email, $password, $firstName, $lastName, $isCook)
{
	global $db;
	
	$query = "UPDATE users SET email=:email, password=:password, firstName=:firstName, lastName=:lastName, isCook=:isCook WHERE username=:username";
	$statement = $db->prepare($query);
  $statement->bindValue(':username', $username);
  $statement->bindValue(':email', $email);
  $statement->bindValue(':password', $password);
  $statement->bindValue(':firstName', $firstName);
  $statement->bindValue(':lastName', $lastName);
  $statement->bindValue(':isCook', $isCook);
  $statement->execute();        // run query
	$statement->closeCursor();    // release hold on this connection
}


function deleteUser($username) 
{
  global $db;
  
	$query = "DELETE FROM users WHERE username=:username";  
      // use : (colon) for template
  $statement = $db->prepare($query);  // prepare statement compiles
  $statement->bindValue(':username', $username);
  $statement->execute();      // run query
  $statement->closeCursor();  // release hold on this connection
	
}
?>
