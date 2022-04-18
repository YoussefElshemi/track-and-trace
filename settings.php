<?php   
  session_start();
  $errors = array();

  $db = mysqli_connect("localhost", "root", "password", "covid");

  if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $user_query = "SELECT * FROM logins WHERE username = '$username'";
    $result = mysqli_query($db, $user_query);
    $user = mysqli_fetch_assoc($result);

    $windows = ["1" => "One Week", "2" => "Two Weeks", "3" => "Three Weeks", "4" => "Four Weeks"]; 

    $distance = isset($_COOKIE["distance"]) ? $_COOKIE["distance"] : 500;
    $window = isset($_COOKIE["window"]) ? $windows[$_COOKIE["window"]] : "One Week";

    if (isset($_POST["save"])) {
      $distance = htmlspecialchars($_POST["distance"]);
      $window = htmlspecialchars($_POST["window"]);

      if (!(empty($distance) || empty($window))) { 
        setcookie("distance", $distance, time() + 60 * 60 * 24 * 365, "/");
        setcookie("window", $window, time() + 60 * 60 * 24 * 365, "/");
        header('location: settings.php');
      } else {
        array_push($errors, "Please ensure all fields are filled");
      }
    }
  } else {
    header('location: login.php');  
  }
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/stylesheet.css">
    <title>COVID-CT: Settings</title>
  </head>

  <body>
    <h1 style="margin-left: 10%;">COVID - 19 Contact Tracing</h1>
    <div class="sidenav">
      <a href="index.php">Home</a>
      <a href="overview.php">Overview</a>
      <a href="add_visit.php">Add Visit</a>
      <a href="report.php">Report</a>
      <a class="selected" href="settings.php">Settings</a>
      <a href="logout.php">Logout</a>
    </div>

    <div class="main">
      <h2>Alert Settings</h2>
      <h3>Here you may change the alert distance and the time span for which the contact tracing will be performed.</h3>
      <div style="text-align: center; margin-top: 6%;">
        <form method="POST" id="form">
          <?php include("errors.php") ?>
          <label for="window">window</label>
          <select name="window" id="window" style="width: 41.3%; height: 64px;">
            <option value="1" <?php echo $window === "One Week" ? "selected" : "" ?>>One Week</option>
            <option value="2" <?php echo $window === "Two Weeks" ? "selected" : "" ?>>Two Weeks</option>
            <option value="3" <?php echo $window === "Three Weeks" ? "selected" : "" ?>>Three Weeks</option>
            <option value="4" <?php echo $window === "Four Weeks" ? "selected" : "" ?>>Four Weeks</option>
          </select><br><br>
          <label for="distance">distance</label>
          <input class="visit" type="number" name="distance" value="<?php echo $distance?>" min=1 max=500 required style="width: 40%;"><br><br>
          
          <div style="margin-top:7%;">
            <button class="btn" type="submit" name="save" value="save">Save</button>
          </div>
        </form>
      </div>
    </div>
  </body>
</html>