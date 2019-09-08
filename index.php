<!--Some code referenced from Labs 5 and 6. I did not copy and paste any code, simply
referenced. -->

<?php
  $search_bool = FALSE;
  $display_bool = FALSE;

  //Array for messages to user
  $message = array();

  //Array for states drop down
  $states = array(
    "Alabama", "Alaska", "Arizona", "Arkansas", "California",
    "Colorado", "Connecticut", "Delaware", "District of Columbia", "Florida",
    "Georgia", "Hawaii", "Idaho", "Illinois", "Indiana",
    "Iowa", "Kansas", "Kentucky", "Louisiana", "Maine",
    "Maryland", "Massachusetts", "Michigan", "Minnesota", "Mississippi",
    "Missouri", "Montana", "Nebraska", "Nevada", "New Hampshire",
    "New Jersey", "New Mexico", "New York", "North Carolina", "North Dakota",
    "Ohio", "Oklahoma", "Oregon", "Pennsylvania", "Rhode Island",
    "South Carolina", "South Dakota", "Tennessee", "Texas", "Utah",
    "Vermont", "Virginia", "Washington", "West Virginia", "Wisconsin",
    "Wyoming");

    //Connecting to database
    $db = new PDO('sqlite:resort-db.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    function sql_func($db, $sql, $params) {
          $query = $db->prepare($sql);
            if ($query and $query->execute($params)) {
              $records = $query->fetchAll();
              return $records;
            }
            return NULL;
        }

    //Adding to database
    if (isset($_POST["add_resort"])) {
      $input_resort_name = filter_input(INPUT_POST, 'input_resort_name', FILTER_SANITIZE_STRING);
      $input_resort_city = filter_input(INPUT_POST, 'input_resort_city', FILTER_SANITIZE_STRING);
      $input_resort_state = filter_input(INPUT_POST, 'input_state', FILTER_SANITIZE_STRING);
      $input_avg_snow = filter_input(INPUT_POST, 'input_min_avg_snowfall', FILTER_VALIDATE_INT);
      $input_runs = filter_input(INPUT_POST, 'input_min_total_runs', FILTER_VALIDATE_INT);
      $input_chairlifts = filter_input(INPUT_POST, 'input_min_total_chairlifts', FILTER_VALIDATE_INT);

      $input_resort_name = trim($input_resort_name);
      $input_resort_city = trim($input_resort_city);

      $checkduplicate = TRUE;
      $duplicateparams = array();
      $duplicate = sql_func($db, "SELECT * FROM resorts WHERE resort_name='$input_resort_name' and resort_state='$input_resort_state';", $duplicateparams);
      if ($duplicate != NULL) {
        $checkduplicate = TRUE;
      } else {
        $checkduplicate = FALSE;
      }

      $sql = "INSERT INTO resorts (resort_name, resort_city, resort_state, min_snow, min_runs, min_lifts) VALUES (:input_resort_name, :input_resort_city, :input_resort_state, :input_avg_snow, :input_runs, :input_chairlifts)";
      $params = array(
        ':input_resort_name' => $input_resort_name,
        ':input_resort_city' => $input_resort_city,
        ':input_resort_state' => $input_resort_state,
        ':input_avg_snow' => $input_avg_snow,
        ':input_runs' => $input_runs,
        ':input_chairlifts' => $input_chairlifts
      );
      if (!$checkduplicate) {
        unset($message);
        $message = array();
        array_push($message, "Entry Successfully Added!");
        sql_func($db, $sql, $params);
      } else {
        unset($message);
        $message = array();
        array_push($message, "Could Not Add Entry: Entry Already Exists");
      }
    }

    //Display the entire database
    if (isset($_POST["display_all"])) {
      $displaysql = "SELECT * FROM resorts";
      $display_bool = TRUE;
    }

    //Searching database
    if (isset($_GET["searchval"]) and isset($_GET["category"])){
      $search_bool = TRUE;
      $search = filter_input(INPUT_GET, 'searchval', FILTER_SANITIZE_STRING);
      $category = filter_input(INPUT_GET, 'category', FILTER_SANITIZE_STRING);

      $search = trim($search);
      if ($category == ("min_snow") || $category ==("min_runs") || $category == ("min_lifts")) {
        $searchsql = "SELECT * FROM resorts WHERE $category >= :search";
      } else {
        $searchsql = "SELECT * FROM resorts WHERE $category LIKE '%' || :search || '%'";
      }
    }
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <title>Home</title>
  <link href="main.css" rel="stylesheet"/>
</head>

<body>
<h1 id="header">Resort Finder</h2>
  <h3>Looking for your next skiing destination? Use our search form to find the "sendiest" vacation in the United States!
  <br>If you would like to contribute to our database, please enter any fields that you know below. The only piece of information you <b>must</b> enter is the resort's name!</h3>

  <div id="wrapper">
      <div id="left">
        <!--Searching Database-->
        <h2 id="searchfield">Search</h2>
        <form id="search" action="index.php" method="get">
          <input name="searchval" type="text" required placeholder="Search Term"></input>
          <select id="searchcat" name="category">
            <option value="resort_name">Resort Name</option>
            <option value="resort_city">City</option>
            <option value="resort_state">State</option>
          </select>
          <button id="searchsubmit" type="submit">Search</button>
        </form>
        <h4>Or</h4>
        <form id="search" action="index.php" method="get">
          <input name="searchval" type="number" required placeholder="Search Term" min="0"></input>
          <select id="searchcat" name="category">
            <option value="min_snow">Average Snowfall</option>
            <option value="min_runs">Runs</option>
            <option value="min_lifts">Lifts</option>
          </select>
          <button id="searchsubmit" type="submit">Search</button>
        </form>
        <hr>

        <!--Contributing to Database-->
        <h2 id="addfield">Contribute to Our Database:</h2>
          <form id="addResort" action="index.php" method="post">
              <input type="text" name="input_resort_name" placeholder = "Resort Name" required maxlength="50" pattern="[A-Za-z0-9 ]+" title="Please only enter letters and numbers, no punctuation."></input><br>
              <input name="input_resort_city" type="text" placeholder="City" maxlength="50" pattern="[A-Za-z0-9 ]+" title="Please only enter letters and numbers, no punctuation."></input>
              <select id="input_state" name="input_state">
                <option value="State">State</option>
                <?php
                  foreach ($states as $state) {
                    echo '<option value="'.$state.'">'.$state.'</option>';
                  }
                ?>
              </select>
              <input name="input_min_avg_snowfall" type="number" placeholder="Average Snowfall" min="0"></input>
              <input name="input_min_total_runs" type="number" placeholder="Total Runs" min="0"></input>
              <input name="input_min_total_chairlifts" type="number" placeholder="Total Lifts" min="0"></input><br>
              <button name="add_resort" type="submit">Add Resort</button>
        </form>
        <hr>
        <!--Display All-->
        <form id="display_all" action="index.php" method="post">
            <button name="display_all" type="submit">View All Entries</button>
        </form>
        <hr>
        <!--Messages-->
        <h2 id="addfield">Messages:</h2>
        <div id="messagediv">
          <?php
            foreach($message as $message_touser) {
              echo "<p id='message'>".htmlspecialchars($message_touser)."</p>";
            }
          ?>
        </div>
      </div>

      <div id="right">
        <table>
          <tr>
            <th>Lifts</th>
            <th>Runs</th>
            <th>Average Snowfall</th>
            <th>Resort State</th>
            <th>Resort City</th>
            <th>Resort Name</th>
          </tr>

          <?php
            //Display search results
            if ($search_bool == TRUE) {
              $params = array(
                ':search' => $search
              );
              $displays = sql_func($db, $searchsql, $params);
              echo "<p id='message'>Search Results: </p>";
              foreach ($displays as $display) {
                echo "<tr><td>".htmlspecialchars($display["min_lifts"])."</td><td>".htmlspecialchars($display["min_runs"])."</td><td>".htmlspecialchars($display["min_snow"])."</td><td>".htmlspecialchars($display["resort_state"])."</td><td>".htmlspecialchars($display["resort_city"])."</td><td>".htmlspecialchars($display["resort_name"])."</td></tr>";
              }
            //Display all results
            } else if ($display_bool == TRUE) {

              $params = array();
              $displays = sql_func($db, "SELECT * FROM resorts", $params);
              echo "<p id='message'>All Results: </p>";
              foreach ($displays as $display) {
                echo "<tr><td>".htmlspecialchars($display["min_lifts"])."</td><td>".htmlspecialchars($display["min_runs"])."</td><td>".htmlspecialchars($display["min_snow"])."</td><td>".htmlspecialchars($display["resort_state"])."</td><td>".htmlspecialchars($display["resort_city"])."</td><td>".htmlspecialchars($display["resort_name"])."</td></tr>";
              }

            }
            //Default display all upon page load
            else {
              $sql = "SELECT * FROM resorts";
              $params = array();
              $displays = sql_func($db, $sql, $params);
              echo "<p id='message'>All Results: </p>";
              foreach ($displays as $display) {
                echo "<tr><td>".htmlspecialchars($display["min_lifts"])."</td><td>".htmlspecialchars($display["min_runs"])."</td><td>".htmlspecialchars($display["min_snow"])."</td><td>".htmlspecialchars($display["resort_state"])."</td><td>".htmlspecialchars($display["resort_city"])."</td><td>".htmlspecialchars($display["resort_name"])."</td></tr>";
              }

            }
          ?>
        </table>
      </div>
  </div>

  <footer>Background Image Credit: <a href="http://blog.visme.co/simple-backgrounds/">Blog.Visme.Co</a></footer>

</body>
</html>
