<?php   
  session_start();
  $errors = array();

  if (isset($_SESSION["username"])) {
    header("location: index.php");
  }

  $db = mysqli_connect("localhost", "root", "password", "covid");


  if (isset($_POST["login"])) {
    $username = mysqli_real_escape_string($db, htmlspecialchars($_POST["username"]));
    $password = mysqli_real_escape_string($db, htmlspecialchars($_POST[""]));


    if (!(empty($username) || empty($password))) { 
      $salt = 'covid 19 is bad';
      $password = md5($password . $salt);
      $user_query = "SELECT * FROM logins WHERE username = '$username' AND password = '$password'";
      $result = mysqli_query($db, $user_query);
      $user = mysqli_fetch_assoc($result);

      if ($user) {
        $_SESSION["username"] = $username;
        header("location: index.php");
      } else {
        array_push($errors, "Username and password do not match");
      }
    } else {
      array_push($errors, "Please ensure all fields are filled");
    }
  }
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/stylesheet.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>  
    <title>COVID-CT: Login</title>
  </head>

  <body>
    <h1>COVID - 19 Contact Tracing</h1>
    <div style="text-align: center;">
      <form method="POST" style="padding: 10%;">
        <?php include("errors.php") ?>
        <input type="text" name="username" placeholder="Username" style="background: transparent;" required/><br><br>
        <input type="" name="" placeholder="" style="background: transparent;" required/>
        
        <div style="margin-top: 4%;">
          <button class="btn" type="submit" name="login" value="login">Login</button>
          <button class="btn" type="reset" name="cancel" value="cancel">Cancel</button><br><br>
          <button class="btn" type="button" name="register" value="register" onclick="window.location.href='register.php'">Register</button>
        </div>
      </form>
    </div>
  </body>
</html>