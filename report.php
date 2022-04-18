<?php   
  session_start();
  $errors = array();

  $db = mysqli_connect("localhost", "root", "password", "covid");

  if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $user_query = "SELECT * FROM logins WHERE username = '$username'";
    $result = mysqli_query($db, $user_query);
    $user = mysqli_fetch_assoc($result);

    if (isset($_POST["report"])) {
      $id = $user["user_id"];
      $date = mysqli_real_escape_string($db, htmlspecialchars($_POST["date"]));
      $time = mysqli_real_escape_string($db, htmlspecialchars($_POST["time"]));
      
      if (!(empty($date) || empty($time))) { 
        $date = date("Y-m-d", strtotime($date));
        $time = date("H:i:s", strtotime($time));
        $query = "INSERT INTO infections (user_id, date, time) VALUES($id, '$date', '$time')";
        mysqli_query($db, $query);

        $visits_query = "SELECT * FROM visits WHERE user_id = $id";
        $visits_result = mysqli_query($db, $visits_query);
        while($row = mysqli_fetch_array($visits_result)) {
          $url = "http://ml-lab-7b3a1aae-e63e-46ec-90c4-4e430b434198.ukwest.cloudapp.azure.com:60999/report";
          $data = array("x" => $row["x"], "y" => $row["y"], "date" => $row["date"], "time" => $row["time"], "duration" => $row["duration"]);

          $options = array(
            'http' => array(
              'header' => "Connection: close\r\n".
              "Content-Length: ".strlen($query)."\r\n".
              "Content-Type: application/json\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
            )
          );
          $context  = stream_context_create($options);
          @file_get_contents($url, false, $context);
          header('location: report.php');
        }
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
    <h1 style="margin-left: 10%;">COVID - 19 Contact Tracing</h1>
    <div class="sidenav">
      <a href="index.php">Home</a>
      <a href="overview.php">Overview</a>
      <a href="add_visit.php">Add Visit</a>
      <a class="selected" href="report.php">Report</a>
      <a href="settings.php">Settings</a>
      <a href="logout.php">Logout</a>
    </div>

    <div class="main">
      <h2>Report an Infection</h2>
      <h3>Please report the date and time when you were tested positive for COVID - 19.</h3>
      <div style="text-align: center; margin-top: 4%;">
        <form method="POST" id="form">
          <?php include("errors.php") ?>
          <input class="visit" type="date" name="date" placeholder="Date" required style="width: 40%;" max=<?php echo date('Y-m-d');?>><br><br>
          <input class="visit" type="time" name="time" placeholder="Time" required style="width: 40%;"><br><br>
          
          <div style="margin-top: 6%;">
            <button class="btn" type="submit" name="report" value="report" style="float: left; margin-left: 3%;">Report</button>
            <button class="btn" type="reset" name="cancel" value="cancel" style="float: right; margin-right: 3%;">Cancel</button><br><br>
          </div>
        </form>
      </div>
    </div>
  </body>
</html>