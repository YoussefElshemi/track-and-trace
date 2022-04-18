<?php   
  session_start();

  $db = mysqli_connect("localhost", "root", "password", "covid");

  if (isset($_SESSION["username"])) {
    $username = $_SESSION["username"];
    $user_query = "SELECT * FROM logins WHERE username = '$username'";
    $result = mysqli_query($db, $user_query);
    $user = mysqli_fetch_assoc($result);
    $id = $user["user_id"];

    $windows = ["1" => "One Week", "2" => "Two Weeks", "3" => "Three Weeks", "4" => "Four Weeks"]; 
    $window = isset($_COOKIE["window"]) ? $windows[$_COOKIE["window"]] : "One Week";
    $distance_alert = isset($_COOKIE["distance"]) ? $_COOKIE["distance"] : 500;

    $windows = ["One Week" => 7, "Two Weeks" => 14, "Three Weeks" => 21 , "Four Weeks" => 28];
    $ts =  $windows[$window];

    $visits_query = "SELECT * FROM visits WHERE user_id in (SELECT user_id FROM infections WHERE DATEDIFF(CURRENT_DATE, date) < $ts) AND DATEDIFF(CURRENT_DATE, date) < $ts";
    $visits_result = mysqli_query($db, $visits_query);

    $my_visits_query = "SELECT * FROM visits WHERE DATEDIFF(CURRENT_DATE, date) < $ts AND user_id = $id";
    $my_visits_result = mysqli_query($db, $my_visits_query);

    $data = json_decode(@file_get_contents("http://ml-lab-7b3a1aae-e63e-46ec-90c4-4e430b434198.ukwest.cloudapp.azure.com:60999/infections?ts=" . $ts));

    $infected = false;
    $places = array();
    $visits_result_array = array();
    $my_visits_result_array = array();

    while ($row = mysqli_fetch_assoc($visits_result)) { 
      $visits_result_array[] = $row; 
    }

    while ($row = mysqli_fetch_assoc($my_visits_result)) { 
      $my_visits_result_array[] = $row; 
    }

    if (!empty($visits_result_array) || !empty($my_visits_result_array)) {
      foreach ($visits_result_array as $row) {
        foreach ($my_visits_result_array as $row2) {
          array_push($places, [
            "x" => $row2["x"], 
            "y" => $row2["y"],
            "duration" => $row2["duration"], 
            "date" => $row2["date"] . " " . $row2["time"], 
            "visit_id" => count($places), 
            "infected" => false
          ]);

          $personOneStart = strtotime($row["date"] . " " . $row["time"]);
          $personOneEnd = strtotime($row["date"] . " " . $row["time"] . " + " . $row["duration"] . " minute");

          $personTwoStart = strtotime($row2["date"] . " " . $row2["time"]);
          $personTwoEnd = strtotime($row2["date"] . " " . $row2["time"] . " + " . $row2["duration"] . " minute");

          if ((($personOneStart < $personTwoStart) && ($personTwoStart < $personOneEnd)) || (($personTwoStart < $personOneStart) && ($personOneStart < $personTwoEnd))) {
            $distance = sqrt(pow(($row2["x"] - $row["x"]), 2) + pow(($row2["y"] - $row["y"]), 2));
            if ($distance <= $distance_alert) {
              $infected = true;
            }
          }
        }

        array_push($places, [
          "x" => $row["x"],
          "y" => $row["y"], 
          "duration" => $row["duration"], 
          "date" => $row["date"] . " " . $row["time"],
          "visit_id" => count($places), 
          "infected" => true
        ]);
      }
    }

    if (!empty($data) || !empty($my_visits_result_array)) {
      foreach ($data as $row2) {
        $row2 = json_decode(json_encode($row2), true);
        foreach ($my_visits_result_array as $row) {
          $personOneStart = strtotime($row["date"] . " " . $row["time"]);
          $personOneEnd = strtotime($row["date"] . " " . $row["time"] . " + " . $row["duration"] . " minute");

          $personTwoStart = strtotime($row2["date"] . " " . $row2["time"]);
          $personTwoEnd = strtotime($row2["date"] . " " . $row2["time"] . " + " . $row2["duration"] . " minute");
          
          if ((($personOneStart < $personTwoStart) && ($personTwoStart < $personOneEnd)) || (($personTwoStart < $personOneStart) && ($personOneStart < $personTwoEnd))) {
            $distance = sqrt(pow($row["x"] - $row2["x"], 2) + pow($row["y"] - $row2["y"], 2));
            if ($distance <= $distance_alert) {
              $infected = true;
            }
          }
        }

        array_push($places, [
          "x" => $row2["x"], 
          "y" => $row2["y"], 
          "duration" => $row2["duration"], 
          "date" => $row2["date"] . " " . $row2["time"], 
          "visit_id" => count($places), 
          "infected" => true
        ]);
      }
    }

  } else {
    header("location: login.php");  
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
    <title>COVID-CT: Home Page</title>
  </head>

  <body>
    <?php 
      if (count($places) > 0) {
        foreach ($places as $place) {
          $hoverText = "Date: " . $place["date"] . 
          " Duration: " . $place["duration"] . " minutes";
          if ($place["infected"]) {
            echo '<img src="./images/marker_red.png" alt="" title="' . $hoverText . '" class="marker" id="' . $place["visit_id"] . '">';
          } else {
            echo '<img src="./images/marker_black.png" alt="" title="' . $hoverText . '" class="marker" id="' . $place["visit_id"] . '">';
          }
        }
      }
    ?>
    <h1 style="margin-left: 10%;">COVID - 19 Contact Tracing</h1>
    <div class="sidenav">
      <a class="selected" href="index.php">Home</a>
      <a href="overview.php">Overview</a>
      <a href="add_visit.php">Add Visit</a>
      <a href="report.php">Report</a>
      <a href="settings.php">Settings</a>
      <a href="logout.php">Logout</a>
    </div>

    <div class="main">
      <h2>Status</h2>
      <div style="overflow: auto; margin-left: 5%;">
        <img src="./images/exeter.jpg" id="map" style="cursor: default;">
        <h3 style="max-width: 50%;">Hi <?php echo $user["name"]; ?>, you might have had a connection to an infected person at the location showed in red</h3>
        <h3 style="max-width: 50%;"><?php echo $infected ? "Unfortunately, you have come into contact with someone who has tested positive." : "" ?></h3>
        <h3 style="max-width: 50%;">Hover over the marker to see details about the infection</h3>      
        <?php 
          if (count($places) > 0) { ?>
            <script type="text/javascript">;
              function placeMarker(x, y, markerID) {
                const marker = document.getElementById(markerID);
                const map = document.querySelector("#map");

                const posX = map.getBoundingClientRect().x + x - marker.width/2;
                const posY = map.getBoundingClientRect().y + y - marker.height;

                console.log('----------');
                console.log('Received: ', x, y);
                console.log('Calculated: ', posX, posY);

                marker.style.transform = `translate3d(${posX}px, ${posY}px, 0)`;
                marker.style.visibility = "visible";
              }    
            <?php foreach ($places as $place) {
              echo 'placeMarker(' . $place["x"] . ', ' . $place["y"] . ', ' . $place["visit_id"] .');';
            } ?>
           </script>       
          <?php } ?>
      </div>
    </div>
  </body>
</html>