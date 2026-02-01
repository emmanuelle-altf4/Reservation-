<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Registration</title>
    <link rel="stylesheet" href="css/user.css">
</head>
<body>
<div class="form">
    <form action="process_register.php" method="POST">
        <h1>Register</h1>
        <input type="text" name="customer_name" placeholder="Full Name" required>
        <input type="email" name="customer_email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" class="loginbtn">Register</button>
        
         <div class="registerbtn">
        <p>Already have an account? <a href="login.php">Log in</a></p>
      </div>
    </form>
</div>
</body>
</html>
