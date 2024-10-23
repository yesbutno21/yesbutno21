

<?php
// Enable error logging
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

// Log received POST data for debugging
error_log("Received POST data: " . print_r($_POST, true));

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

    // Existing code to update the mark
    if (isset($_POST['score']) && isset($_POST['userId']) && isset($_POST['testType'])) {
        $score = $_POST['score'];
        $user_id = $_POST['userId'];
        $test_type = $_POST['testType'];

        if ($test_type == 'HTML') {
            $stmt = $db->prepare('UPDATE users SET htmlmark = :score WHERE id = :id');
        } elseif ($test_type == 'SQL') {
            $stmt = $db->prepare('UPDATE users SET sqlmark = :score WHERE id = :id');
        } elseif ($test_type == 'Python') {
            $stmt = $db->prepare('UPDATE users SET pythonmark = :score WHERE id = :id');
        } else {
            throw new Exception("Invalid test type");
        }

        $stmt->bindValue(':score', $score, PDO::PARAM_INT);
        $stmt->bindValue(':id', $user_id, PDO::PARAM_INT);
        $result = $stmt->execute();

        if ($result) {
            $response = ['success' => true, 'message' => 'Database updated successfully', 'test_type' => $test_type, 'score' => $score];
        } else {
            $response = ['success' => false, 'error' => 'Failed to update database'];
        }

        // New code to retrieve data
        $query = "SELECT htmlmark FROM users WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
    
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user_data) {
            $response['user_data'] = $user_data;
        } else {
            $response['user_data'] = null;
            $response['error'] = 'User not found';
        }

        error_log('user data: ' . print_r($user_data, true));
    } else {
        // Fetch the current data without updating
        $user_id = 1; // Default user ID, adjust as needed
        $query = "SELECT htmlmark, sqlmark, pythonmark FROM users WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($user_data) {
            $response = [
                'success' => true,
                'chartData' => $user_data
            ];
        } else {
            $response = ['success' => false, 'error' => 'User not found'];
        }
    }

    

    // Close the connection
    $db = null;

} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    $response = ['success' => false, 'error' => $e->getMessage()];
}

// Capture any output
$debug_output = ob_get_clean();

// Near the end of mark.php, before echo json_encode($response);
$chartData = [
    'htmlmark' => $user_data['htmlmark'] ?? 0,
    'sqlmark' => $user_data['sqlmark'] ?? 0,
    'pythonmark' => $user_data['pythonmark'] ?? 0
];

$response['chartData'] = $chartData;

// Add the debug output to the response
$response['debug'] = $debug_output;

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>