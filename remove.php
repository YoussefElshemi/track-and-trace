<?php   
  session_start();

  $db = mysqli_connect("localhost", "root", "password", "covid");

  if (isset($_SESSION["username"])) {
    if (isset($_GET["id"])) {
      $visit_id = mysqli_real_escape_string($db, htmlspecialchars($_GET["id"]));
      $query = "DELETE FROM visits WHERE visit_id = $visit_id";
      mysqli_query($db, $query);
      echo "1";
    } else {
      header("location: index.php");
    }
  } else {
    header("location: login.php");  
  }
?>