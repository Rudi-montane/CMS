<?php
// Setzen Sie den Header, um eine JSON-Antwort zu senden
header('Content-Type: application/json');

// Konfiguration
$upload_dir = 'uploads/'; // Der Ordner, in den die Dateien hochgeladen werden
$allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov', 'webp'];
$max_size = 20 * 1024 * 1024; // 20 MB maximal pro Datei

// Erstellen Sie eine Antwort-Array
$response = [
    'success' => false,
    'urls' => [],
    'url' => '', // Für einzelne Datei-Uploads
    'error' => ''
];

// Überprüfen, ob der Upload-Ordner existiert und beschreibbar ist
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

if (!is_writable($upload_dir)) {
    $response['error'] = 'Upload-Verzeichnis ist nicht beschreibbar. Bitte überprüfen Sie die Server-Berechtigungen.';
    echo json_encode($response);
    exit;
}

// Funktion zur Verarbeitung einer einzelnen Datei
function process_file($file, $upload_dir, $allowed_types, $max_size) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Fehler beim Upload der Datei: ' . $file['name']];
    }

    if ($file['size'] > $max_size) {
        return ['success' => false, 'error' => 'Datei ' . $file['name'] . ' ist zu gross (Max: 20MB).'];
    }

    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_types)) {
        return ['success' => false, 'error' => 'Dateityp ' . $file_extension . ' ist nicht erlaubt.'];
    }

    $new_filename = uniqid('', true) . '.' . $file_extension;
    $destination = $upload_dir . $new_filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        $file_url = $protocol . "://" . $host . $path . "/" . $destination;
        return ['success' => true, 'url' => $file_url];
    } else {
        return ['success' => false, 'error' => 'Fehler beim Speichern der Datei ' . $file['name']];
    }
}

// Überprüfen, ob mehrere Dateien ('media') gesendet wurden
if (isset($_FILES['media'])) {
    $files = $_FILES['media'];
    $file_count = count($files['name']);

    for ($i = 0; $i < $file_count; $i++) {
        $current_file = [
            'name' => $files['name'][$i],
            'type' => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error' => $files['error'][$i],
            'size' => $files['size'][$i]
        ];
        
        $result = process_file($current_file, $upload_dir, $allowed_types, $max_size);

        if ($result['success']) {
            $response['urls'][] = $result['url'];
            $response['success'] = true; // Setze Erfolg auf true, wenn mindestens eine Datei erfolgreich war
        } else {
            // Wenn ein Fehler auftritt, speichern Sie ihn und fahren Sie fort
            $response['error'] .= $result['error'] . ' ';
        }
    }

} 
// Überprüfen, ob eine einzelne Datei ('imageFile') gesendet wurde
else if (isset($_FILES['imageFile'])) {
    $result = process_file($_FILES['imageFile'], $upload_dir, $allowed_types, $max_size);
    if ($result['success']) {
        $response['url'] = $result['url'];
        $response['success'] = true;
    } else {
        $response['error'] = $result['error'];
    }
} 
// Wenn keine Dateien gefunden wurden
else {
    $response['error'] = 'Keine Dateien zum Hochladen gefunden.';
}

// Die JSON-Antwort an den Editor zurücksenden
echo json_encode($response);
?>