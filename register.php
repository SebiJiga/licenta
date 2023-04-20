<!DOCTYPE html>
<html>
<head>
  <title>Registration Form</title>
</head>
<body>
  <h1>Registration Form</h1>
  
  <form method="post" action="register.php">
    <label>Username:</label>
    <input type="text" name="username" required>
    
    <label>Email:</label>
    <input type="email" name="email" required>
    
    <label>Password:</label>
    <input type="password" name="password" required>
    
    <input type="submit" value="Register">
  </form>
</body>
</html>

<?php 
require_once 'db_connection.php';
require_once 'functions.php';


//Get the form data
$username = $_POST['username'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

//Check if username or email aready in use
$stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = :username OR email = :email");
$stmt->execute(array(':username' => $username, ':email' => $email));
if ($stmt->fetchColumn() > 0){
    die("Username or email aready in use");
}

  // Check if all required fields are filled in
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['username']) && !empty($_POST['email']) && !empty($_POST['password']))
    {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

//Insert the user's information into the database
$stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
$stmt->execute([$username, $email, $hashed_password]);

//Redirect to the succesfull register page
header("Location: registration_succes.php");
exit();
    } else {
    echo "Please fill in all required field.";
    }
}

?>
