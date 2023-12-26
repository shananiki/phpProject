<?php
require_once realpath(__DIR__ . "/vendor/autoload.php");

use Dotenv\Dotenv;

$col_green = "#00FF00";
$col_red = "#FF0000";

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
$db_host = $_ENV["DB_HOST"];
$db_username = $_ENV["DB_USERNAME"];
$db_password = $_ENV["DB_PASSWORD"];
$db_database = $_ENV["DB_DATABASE"];
$db_port = $_ENV["DB_PORT"];

$connection = new mysqli($db_host, $db_username, $db_password, $db_database);

$account_id_selected = isset($_GET["account"]) ? intval($_GET["account"]) : 0;

try {
    $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_database", $db_username, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validate and sanitize form data
    $account_id = isset($_POST["account_id"]) ? intval($_POST["account_id"]) : 0;
    $amount = isset($_POST["amount"]) ? floatval($_POST["amount"]) : 0.0;
    $description = isset($_POST["description"]) ? htmlspecialchars($_POST["description"], ENT_QUOTES, 'UTF-8') : '';
    $transaction_date = isset($_POST["transaction_date"]) ? $_POST["transaction_date"] : '';

    // Insert the transaction into the transactions table
    try {
        $insertQuery = "INSERT INTO transactions (account_id, amount, description, transaction_date) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($insertQuery);
        $stmt->bindParam(1, $account_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $amount, PDO::PARAM_STR);
        $stmt->bindParam(3, $description, PDO::PARAM_STR);
        $stmt->bindParam(4, $transaction_date, PDO::PARAM_STR);
        $stmt->execute();

        $full_url = "http" . (isset($_SERVER['HTTPS']) ? "s" : "") . "://" . $_SERVER['HTTP_HOST'];
        header('Location: ' . $full_url . "/finance/listAccount.php?account=" . $account_id);
    } catch (PDOException $e) {
        // Handle the case where the query fails
        echo "Error: " . $e->getMessage();
    }
    
}

?>

<link rel="stylesheet" href="entry.css">
<center>
<button id="btn-send" onclick="window.location.href='http://divinerpiy.dynv6.net/finance/accounts.php'">View Accounts</button>
<h2 id="label2">New Transaction</h2>

<form action="listAccount.php" method="post">
  <label id="label3" for="account_id">Account:</label>
  <select id="btn-send" name="account_id" id="account_id">
    <?php
    // Fetch account data from the database
    $sql = "SELECT * FROM accounts";
    $result = mysqli_query($connection, $sql);
    
    // Check if there are accounts
    if ($result && mysqli_num_rows($result) > 0) {
      while ($row = mysqli_fetch_assoc($result)) {
        if($row['account_id'] == $account_id_selected){
            echo '<option selected value="' . $row['account_id'] . '">' . $row['account_name'] . '</option>';
        }else{
            echo '<option value="' . $row['account_id'] . '">' . $row['account_name'] . '</option>';
        }
      }
    } else {
      echo '<option value="" disabled>No accounts available</option>';
    }

    // Close the database connection
    mysqli_close($connection);
    ?>
  </select>

  <br>

  <label id="label3" for="amount">Amount:</label>
  <input id="message" type="text" name="amount" id="amount" required>

  <br>

  <label id="label3" for="description">Description:</label>
  <input id="message" type="text" name="description" id="description" required>

  <br>

  <label id="label3" for="transaction_date">Transaction Date:</label>
  <input id="message" type="date" name="transaction_date" id="transaction_date" value="<?php echo date('Y-m-d'); ?>">

  <br>

  <input id="btn-send" type="submit" value="Add Transaction">
</form>
</center>


<?php



try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_database", $db_username, $db_password);

    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query to fetch all accounts
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE account_id = $account_id_selected");
   
    $stmt->execute();
    $listCounter = 0;
    // Check if there are any rows
    if ($stmt->rowCount() > 0) {
        
        echo '<center>';
        echo '<table id="table">';
        echo '<tr><th>Transaction</th><th>Amount</th><th>Description</th><th>Date</th></tr>';

        // Fetch each row and display in the table
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $listCounter = $listCounter + 1;
            echo '<tr>';
            echo '<td>' . $listCounter . '</td>';
            if($row['amount'] > 0){
                echo '<td style="background-color: #00FF00">' . $row['amount'] . '</td>';
            }else{
                echo '<td style="background-color: #FF0000">' . $row['amount'] . '</td>';
            }
            
            echo '<td>' . $row['description'] . '</td>';
            echo '<td>' . $row['transaction_date'] . '</td>';
            echo '<td><button id="btn-send" onclick="deleteAccount(' . $row['account_id'] . ')">Remove</button></td>';
            echo '</tr>';
        }

        echo '</table>';
        echo '</center>';
    } else {
        echo 'No accounts found.';
    }
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}



?>
