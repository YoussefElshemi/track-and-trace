<?php   
  session_start();
  $errors = array();

  $db = mysqli_connect("localhost", "root", "password", "covid");

  if (isset($_SESSION["username"])) {
    $username = $_SESSION["username"];
    $user_query = "SELECT * FROM logins WHERE username = '$username' LIMIT 1";
    $result = mysqli_query($db, $user_query);
    $user = mysqli_fetch_assoc($result);

    if (isset($_POST["add"])) {
      $id = $user["user_id"];
      $date = mysqli_real_escape_string($db, htmlspecialchars($_POST["date"]));
      $time = mysqli_real_escape_string($db, htmlspecialchars($_POST["time"]));
      $duration = mysqli_real_escape_string($db, htmlspecialchars($_POST["duration"]));
      $x = mysqli_real_escape_string($db, htmlspecialchars($_POST["x"]));
      $y = mysqli_real_escape_string($db, htmlspecialchars($_POST["y"]));     
      
      if (!(empty($date) || empty($time) || empty($duration) || empty($x) || empty($y))) { 
        $date = date("Y-m-d", strtotime($date));
        $time = date("H:i:s", strtotime($time));
        $query = "INSERT INTO visits (user_id, date, time, duration, x, y) VALUES($id, '$date', '$time', '$duration', '$x', '$y')";
        mysqli_query($db, $query);
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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>  
    <title>COVID-CT: Visits Overview</title>
  </head>

  <body>
    <img src="./images/marker_black.png" class="marker">
    <h1 style="margin-left: 10%;">COVID - 19 Contact Tracing</h1>
    <div class="sidenav">
      <a href="index.php">Home</a>
      <a href="overview.php">Overview</a>
      <a class="selected" href="add_visit.php">Add Visit</a>
      <a href="report.php">Report</a>
      <a href="settings.php">Settings</a>
      <a href="logout.php">Logout</a>
    </div>

    <div class="main">
      <h2>Add a new Visit</h2>
      <img src="./images/exeter.jpg" id="map">
      <div style="text-align: center;">
        <form method="POST" id="form">
          <?php include("errors.php") ?>
          <input class="visit" type="date" name="date" placeholder="Date" required style="width: 20%;" max=<?php echo date('Y-m-d');?>><br><br>
          <input class="visit" type="time" name="time" placeholder="Time" required style="width: 20%;"><br><br>
          <input class="visit" type="number" name="duration" placeholder="Duration" min=0 required style="width: 20%;"><br><br>
          <input type="hidden" type="number" name="x" id="x" required>
          <input type="hidden" type="number" name="y" id="y" required>
          
          <div style="margin-top: 8%;">
            <button class="btn" type="submit" name="add" value="add">Add</button><br><br>
            <button class="btn" type="reset" name="cancel" value="cancel">Cancel</button><br><br>
          </div>
        </form>
      </div>
    </div>


    <script>
      const marker = document.querySelector(".marker");
      const map = document.querySelector("#map");
      const clear = document.querySelector("button[name='cancel']");

      map.addEventListener("click", move, false);
      clear.addEventListener("click", () => { marker.style.visibility = "hidden"; }, true);

      function move(e) {
        let posX = 0;
        let posY = 0;

        if (e.clientX || e.clientY) {
          posX = document.body.scrollLeft + document.documentElement.scrollLeft + e.clientX;
          posY = document.body.scrollTop + document.documentElement.scrollTop + e.clientY;
        } else {
          posX = e.pageX;
          posY = e.pageY;
        }

        marker.style.transform = `translate3d(${posX - marker.width/2}px, ${posY - marker.height}px, 0)`;
        marker.style.visibility = "visible";

        posX -= map.offsetLeft;
        posY -= map.offsetTop;

        document.querySelector("#x").value = posX;
        document.querySelector("#y").value = posY;
      }      
    </script>
  </body>
</html>