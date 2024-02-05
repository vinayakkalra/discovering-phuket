<?php
if (isset($_POST['nft2'])) {
    session_start();
    $_SESSION['nft2'] = $_POST;
    echo json_encode(['status' => 'success']); // Sending a JSON response back to the client
} else {
    echo json_encode(['status' => 'error', 'message' => 'Category and address are required.']);
}
?>