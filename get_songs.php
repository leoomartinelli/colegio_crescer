<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$musicDir = __DIR__ . '/musicas';
$songs = [];

if (is_dir($musicDir)) {
    $files = scandir($musicDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'mp3') {
            // Remove extension for the display title
            $title = pathinfo($file, PATHINFO_FILENAME);
            
            // Clean up titles (replace hyphens/underscores with spaces)
            $title = str_replace(['-', '_'], ' ', $title);
            $title = ucwords(trim($title));
            
            $songs[] = [
                'title' => $title,
                'artist' => 'Colégio Crescer',
                'src' => 'musicas/' . rawurlencode($file)
            ];
        }
    }
}

echo json_encode($songs);
