<?php   
  session_start();
  $errors = array();

  if (isset($_SESSION['username'])) {
    header('location: index.php');
  }

  $db = mysqli_connect("localhost", "root", "password", "covid");


  if (isset($_POST["register"])) {
    $name = htmlspecialchars($_POST["name"]);
    $surname =  htmlspecialchars($_POST["surname"]);
    $username = htmlspecialchars($_POST["username"]);
    $password = htmlspecialchars($_POST[""]);

    if (!(empty($name) || empty($username) || empty($password))) { 
      if (strlen($password) >= 8) {
        if (preg_match("/[a-z0-9]$/i", $password)) {
          $user_query = "SELECT * FROM logins WHERE username = '$username'";
          $result = mysqli_query($db, $user_query); 
          $user = mysqli_fetch_assoc($result);

          if ($user) {
            array_push($errors, "Username is taken");
          } else {
            $name = mysqli_real_escape_string($db, $name);
            $surname = mysqli_real_escape_string($db, $surname);
            $username = mysqli_real_escape_string($db, $username);
            $password = mysqli_real_escape_string($db, $password);

            $salt = 'covid 19 is bad';
            $password = md5($password . $salt);

            $query = "INSERT INTO logins (name, surname, username, password) VALUES('$name', '$surname', '$username', '$password')";
            mysqli_query($db, $query);
            $_SESSION['username'] = $username;
            header('location: index.php');
          }
        } else {
          array_push($errors, "Password must only consist of uppercase, lowercase and numbers");
        }
      } else {
        array_push($errors, "Password length must be 8 or greater");
      }
    } else {
      array_push($errors, "Please ensure all mandatory fields are filled");
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
    <title>COVID-CT: Registration</title>
  </head>

  <body>
    <h1>COVID - 19 Contact Tracing</h1>
    <div style="text-align: center;">
      <form method="POST" style="padding: 10%;" onsubmit="return validateForm()">
        <?php include("errors.php") ?>
        <input type="text" name="name" pattern="[a-zA-Z]+" placeholder="Name" style="background: transparent;"/><br><br>
        <input type="text" name="surname" pattern="[a-zA-Z]+" placeholder="Surname" style="background: transparent;"/><br><br>
        <input type="text" name="username" pattern="[a-zA-Z0-9]+" placeholder="Username" style="background: transparent;" required/><br><br>
        <input type="" name="" placeholder="" style="background: transparent;" />
        <div style="margin-top: 5%;">
          <button class="btn" type="submit" name="register" value="register">Register</button>
        </div>      
      </form>
    </div>

    <script type="text/javascript">
      const form = document.forms[0];
      function validateForm() {    

        const name = form.name.value;
        const surname = form.surname.value;
        const username = form.username.value;
        const password = form.password.value;

        if (name && username && password) {
          if (password.length >= 8) {
            if (password.match(/[a-z0-9]$/i)) {
              return true;
            } else {
              displayError("Password must only consist of uppercase, lowercase and numbers"); 
              return false;
            }
          } else {
            displayError("Password length must be 8 or greater");
            return false;
          }
        } else {
          displayError("Please ensure all mandatory fields are filled");
          return false;
        }
      }

      function displayError(error) {
        const message = `<span class="error">${error}</span><br><br>`;
        form.innerHTML = message + form.innerHTML;
      }
    </script>
  </body>
</html>