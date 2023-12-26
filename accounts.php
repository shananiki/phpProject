<?php
include('views/accounts.html');

?>

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

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_database", $db_username, $db_password);

    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query to fetch all accounts
    $stmt = $pdo->prepare("SELECT * FROM accounts");
    $stmt->execute();
    $current_url = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]";
    // Check if there are any rows
    if ($stmt->rowCount() > 0) {
        echo '<center>';
        echo '<table id="table">';
        echo '<tr><th>Account ID</th><th>Account Name</th><th>Balance</th><th>Action</th></tr>';

        // Fetch each row and display in the table
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<tr>';
            if($row['balance'] > 0){
                echo '<td style="background-color: #00FF00">' . $row['account_id'] . '</td>';
            }else{
                echo '<td style="background-color: #FF0000">' . $row['account_id'] . '</td>';
            }
            
            echo '<td>' . $row['account_name'] . '</td>';
            echo '<td>' . $row['balance'] . '</td>';
            $viewUrl = $current_url . "/finance/listAccount.php?account=" . urlencode($row['account_id']);
            echo '<td><button id="btn-send" onclick="window.location.href=\'' . $viewUrl . '\'">View</button></td>';
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


if ($_SERVER["REQUEST_METHOD"] == "POST") {


    // Retrieve form data
    $account_name = $_POST["account_name"];
    $balance = $_POST["balance"];

    try {
        // Create a new PDO instance
        $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_database", $db_username, $db_password);

        // Set the PDO error mode to exception
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Prepare and execute the SQL statement to insert a new entry
        $stmt = $pdo->prepare("INSERT INTO accounts (account_name, balance) VALUES (:account_name, :balance)");
        $stmt->bindParam(':account_name', $account_name);
        $stmt->bindParam(':balance', $balance);
        $stmt->execute();

        echo "New entry added successfully.";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    // Close the database connection
    $pdo = null;
    require_once("core.php");
    Core::F5Fix();
}

?>
