<?php
require_once 'db_connection.php';
require_once 'functions.php';

// Check if all required fields are filled in
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Get the form data
  $username = $_POST['username'];
  $email = $_POST['email'];
  $password = $_POST['password'];

  // Check if username or email already in use
  $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = :username OR email = :email");
  $stmt->execute(array(':username' => $username, ':email' => $email));
  if ($stmt->fetchColumn() > 0) {
    die("Username or email already in use");
  }

  // Validate and hash the password
  if (!empty($username) && !empty($email) && !empty($password)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert the user's information into the database
    $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email, $hashed_password]);

    // Redirect to the successful register page
    header("Location: registration_succes.php");

    exit();
  } else {
    echo "Please fill in all required fields.";
  }
}
?>


<!DOCTYPE html>
<html>

<head>
  <title>Registration Form</title>
  <link rel="stylesheet" type="text/css" href="styles.css">
</head>

<body style="overflow-y:hidden">
  <header>
    <div class="header-left">
      <a href="login.php" class="text-decoration">TOMAPAN</a>
    </div>

    <div class="header-right">
      <b class="text-signup">Registration form</b>
      <a href="login.php" class="logout-btn">Log in</a>
    </div>
  </header>

  <div class="container-signup">
    <div class="form-container-signup">
      <form method="post" action="register.php">
        <div class="form-group-signup">
          <label>Username:</label>
          <input type="text" name="username" required>
        </div>

        <div class="form-group-signup">
          <label>Email:</label>
          <input type="email" name="email" required>
        </div>

        <div class="form-group-signup">
          <label>Password:</label>
          <input type="password" name="password" required>
        </div>

        <input class="button-signup" type="submit" value="Register">
      </form>
    </div>
  </div>
</body>


</html>