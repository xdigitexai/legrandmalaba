<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file'])) {
        $uploadDir = 'public_html/';
        
        // Create upload directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = basename($_FILES['file']['name']);
        $targetPath = $uploadDir . $fileName;
        
        // Move the uploaded file
        if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
            echo json_encode([
                'status' => 'success',
                'message' => 'File uploaded successfully',
                'file_path' => $targetPath
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to move uploaded file'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'No file received'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
}
?>