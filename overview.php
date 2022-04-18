<?php   
  session_start();

  $db = mysqli_connect("localhost", "root", "password", "covid");

  if (isset($_SESSION["username"])) {
    $username = $_SESSION["username"];
    $user_query = "SELECT * FROM logins WHERE username = '$username'";
    $result = mysqli_query($db, $user_query);
    $user = mysqli_fetch_assoc($result);
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
    <title>COVID-CT: Visits Overview</title>
  </head>

  <body>
    <h1 style="margin-left: 10%;">COVID - 19 Contact Tracing</h1>
    <div class="sidenav">
      <a  href="index.php">Home</a>
      <a class="selected" href="overview.php">Overview</a>
      <a href="add_visit.php">Add Visit</a>
      <a href="report.php">Report</a>
      <a href="settings.php">Settings</a>
      <a href="logout.php">Logout</a>
    </div>

    <div class="main" style="margin-top: 4%;">
      <table style="width: 50%; margin: 0 auto;">
        <tr>
          <th id="date">Date</th>
          <th id="time">Time</th>
          <th id="duration">Duration</th>
          <th id="y">X</th>
          <th id="y">Y</th>
          <th id="button">&nbsp;</th>
        </tr>
        <?php 
          $id = $user["user_id"];
          $query = "SELECT * FROM visits WHERE user_id = $id";
          $result = mysqli_query($db, $query);
          while($row = mysqli_fetch_array($result)) {
            echo "<tr id=" . $row["visit_id"] . ">";
            echo "<td>" . date("d/m/Y", strtotime($row["date"])) . "</td>";
            echo "<td>" . date("H:i", strtotime($row["time"])) . "</td>";
            echo "<td>" . $row["duration"] . "</td>";
            echo "<td>" . $row["x"] . "</td>";
            echo "<td>" . $row["y"] . "</td>";
        ?>
            <td>
              <button class="cross" id="<?php echo $row["visit_id"] ?>" onclick="remove(<?php echo $row['visit_id'] ?>)">
                <img src="./images/cross.png" style="width: 150%; height: 150%;">
              </button>
            </td>
        <?php
            echo "</tr>";
          }
        ?>
      </table>
    </div>

    <script>
      function remove(id) {
        const xhttp = new XMLHttpRequest();
        xhttp.open("GET", `remove.php?id=${id}`, true); 
        xhttp.onreadystatechange = function() {
          if (this.readyState == 4 && this.status == 200) {
            const row = document.getElementById(String(id));
            row.parentNode.removeChild(row);
          }
        };
        xhttp.send();
      }
    </script>
  </body>
</html>