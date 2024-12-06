<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Enable CORS for all origins (or restrict to your specific Unity app domain)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

// Database connection details
$servername = "localhost";
$port = "3307"; // Add the port explicitly
$username = "root";
$password = "ruby";
$dbname = "unityar";

// Establish database connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare the response
header("Content-Type: text/plain");

// Check if the 'search' parameter exists
if (isset($_GET['search'])) {
    $search = $_GET['search'];

    // Check if the search term contains a number or keywords like "lot" or "block"
    if (preg_match('/\d/', $search) || stripos($search, 'lot') !== false || stripos($search, 'block') !== false) {
        // Search only in the 'lot_number' column for numeric or keyword searches
        $stmt = $conn->prepare("SELECT lot_number FROM destinations WHERE lot_number LIKE ? LIMIT 10");
        $likeSearch = "%$search%";
        $stmt->bind_param("s", $likeSearch);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $lotNumbers = [];

            // Fetch results for lot_number
            while ($row = $result->fetch_assoc()) {
                $lotNumbers[] = $row['lot_number'];
            }

            // Return results as newline-separated values for lot_number
            echo implode("\n", $lotNumbers);
        } else {
            echo "Error: Could not execute search query.";
        }
    } else {
        // Search only in the 'name' column for non-numeric and non-keyword searches
        $stmt = $conn->prepare("SELECT name FROM destinations WHERE name LIKE ? LIMIT 10");
        $likeSearch = "%$search%";
        $stmt->bind_param("s", $likeSearch);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $names = [];

            // Fetch results for name
            while ($row = $result->fetch_assoc()) {
                $names[] = $row['name'];
            }

            // Return results as newline-separated values for name
            echo implode("\n", $names);
        } else {
            echo "Error: Could not execute search query.";
        }
    }

    $stmt->close();
}
// Check if the 'destination' parameter exists
elseif (isset($_GET['destination'])) {
    $destination = $_GET['destination'];

    // Use a prepared statement to prevent SQL injection
    // Modify the query to check both 'name' and 'lot_number'
    $stmt = $conn->prepare("SELECT x, y, z FROM destinations WHERE name = ? OR lot_number = ? LIMIT 1");
    $stmt->bind_param("ss", $destination, $destination);

    if ($stmt->execute()) {
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Return coordinates as a comma-separated string
            echo $row['x'] . "," . $row['y'] . "," . $row['z'];
        } else {
            echo "Error: Destination not found.";
        }
    } else {
        echo "Error: Could not execute destination query.";
    }

    $stmt->close();
}

// Close the connection
$conn->close();
?>
