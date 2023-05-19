<!DOCTYPE html>
<html>

<head>
  <title>Login Form</title>
</head>

<body>
  <h1>Login Form</h1>

  <form method="post" action="login.php">
    <label>Email/username:</label>
    <input type="text" name="email" required>

    <label>Password:</label>
    <input type="password" name="password" required>

    <input type="submit" value="Login">
  </form>
</body>

</html>


<?php
require_once 'functions.php';
require_once 'db_connection.php';
session_start();

//Check if the user is already logged in
if (is_logged_in()) {
  header('Location:index.php');
  exit();
}

//Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Retrieve the form data
  $email = $_POST['email'];
  $password = $_POST['password'];

  try {

    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->prepare('SELECT * from USERS where (email = :email or username =:username)');
    $stmt->execute(array(':email' => $email, ':username' => $email));
    $user = $stmt->fetch(PDO::FETCH_ASSOC);


    if ($user && password_verify($password, $user['password'])) {
      $_SESSION['id'] = $user['id'];
      $_SESSION['username'] = $user['username'];
      $_SESSION['profile_picture'] = $user['profile_picture'];
      header('Location: index.php');
      exit();
    } else {
      $error_message = "Invalid credentials";
      echo $error_message;
    }
  } catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
    echo $error_message;

  }
}
?>