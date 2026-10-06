<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$posterId = isset($_GET['poster_id']) ? (int) $_GET['poster_id'] : 0;
if ($posterId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Poster ID required.']);
    exit;
}

try {
    $pdo = requireDatabase();
    $poster = $pdo->prepare('SELECT * FROM ar_posters WHERE id = :id LIMIT 1');
    $poster->execute(['id' => $posterId]);
    $posterData = $poster->fetch();

    if (!$posterData) {
        echo json_encode(['success' => false, 'message' => 'Poster not found.']);
        exit;
    }

    $hotspotStmt = $pdo->prepare('SELECT h.*, a.name AS attraction_name, a.short_description AS attraction_short_description, a.main_image AS attraction_image, a.google_maps_url FROM ar_hotspots h LEFT JOIN attractions a ON a.id = h.attraction_id WHERE h.poster_id = :poster_id ORDER BY h.z_index ASC');
    $hotspotStmt->execute(['poster_id' => $posterId]);
    $hotspots = $hotspotStmt->fetchAll();

    echo json_encode([
        'success' => true,
        'poster' => [
            'id' => (int) $posterData['id'],
            'name' => $posterData['name'],
            'description' => $posterData['description'],
            'target_file' => $posterData['target_file'],
            'target_status' => $posterData['target_status'],
        ],
        'hotspots' => array_map(function ($spot) {
            return [
                'id' => (int) $spot['id'],
                'label' => $spot['label'],
                'x' => (float) $spot['x'],
                'y' => (float) $spot['y'],
                'width' => (float) $spot['width'],
                'height' => (float) $spot['height'],
                'z_index' => (int) $spot['z_index'],
                'video_url' => $spot['video_url'],
                'content_type' => $spot['content_type'],
                'attraction_name' => $spot['attraction_name'],
                'attraction_short_description' => $spot['attraction_short_description'],
                'attraction_image' => $spot['attraction_image'] ? appUrl('assets/uploads/gallery/' . $spot['attraction_image']) : appUrl('assets/images/placeholder-landmark.svg'),
                'google_maps_url' => $spot['google_maps_url'],
            ];
        }, $hotspots)
    ]);
} catch (Throwable $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Unable to load poster data.']);
}
