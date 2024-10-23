<?php
// Enable error logging
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

// Start output buffering
ob_start();

try {
    // MySQL connection details for local database
    $host = '127.0.0.1';  // or 'localhost'
    $port = 3306;
    $dbname = 'users';    // make sure this matches your local database name
    $username = 'Midhlaj';  // replace with your MySQL username
    $password = 'Midhlaj@memes';  // replace with your MySQL password

    // Create connection
    $db = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_POST['test_value']) && isset($_POST['user']) && isset($_POST['test_type'])) {
        $test_value = $_POST['test_value'];
        $user_id = $_POST['user'];
        $test_type = $_POST['test_type'];

        $stmt = $db->prepare('UPDATE users SET test = :test WHERE id = :id');
        $stmt->bindValue(':test', $test_value, PDO::PARAM_STR);
        $stmt->bindValue(':id', $user_id, PDO::PARAM_INT);
        $result = $stmt->execute();

        if ($result) {
            $response = ['success' => true, 'message' => 'Database updated successfully', 'test_type' => $test_type];
        } else {
            $response = ['success' => false, 'error' => 'Failed to update database'];
        }
    } else {
        $response = ['success' => false, 'error' => 'Missing test_value, user, or test_type in POST data'];
    }

    // Close the connection
    $db = null;

} catch (PDOException $e) {
    error_log("PDOException: " . $e->getMessage());
    $response = ['success' => false, 'error' => $e->getMessage()];
}

// Capture any output
$debug_output = ob_get_clean();

// Add the debug output to the response
$response['debug'] = $debug_output;

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>