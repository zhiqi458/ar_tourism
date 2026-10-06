<?php
require_once __DIR__ . '/config.php';

function getRecentActivities(int $limit = 5): array {
    $stmt = requireDatabase()->prepare('SELECT a.*, u.full_name FROM activity_logs a LEFT JOIN users u ON u.id = a.user_id ORDER BY a.created_at DESC LIMIT :limit');
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function fetchDashboardStats(): array {
    $pdo = requireDatabase();
    $stats = [];
    $queries = [
        'destinations' => 'SELECT COUNT(*) FROM destinations',
        'attractions' => 'SELECT COUNT(*) FROM attractions',
        'ar_posters' => 'SELECT COUNT(*) FROM ar_posters',
        'ar_hotspots' => 'SELECT COUNT(*) FROM ar_hotspots',
        'categories' => 'SELECT COUNT(*) FROM categories',
    ];

    foreach ($queries as $key => $query) {
        $stats[$key] = (int) $pdo->query($query)->fetchColumn();
    }

    return $stats;
}

function fetchDestinations(int $limit = null, ?string $search = null): array {
    $sql = 'SELECT d.*, c.name AS category_name FROM destinations d LEFT JOIN categories c ON c.id = d.category_id WHERE d.status = :status';
    $params = ['status' => 'active'];

    if ($search !== null && $search !== '') {
        $sql .= ' AND (d.name LIKE :search OR d.location LIKE :search OR c.name LIKE :search)';
        $params['search'] = '%' . $search . '%';
    }

    $sql .= ' ORDER BY d.created_at DESC';

    if ($limit !== null) {
        $sql .= ' LIMIT ' . (int) $limit;
    }

    $stmt = requireDatabase()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function fetchAttractions(int $limit = null, ?int $destinationId = null, ?string $search = null): array {
    $sql = 'SELECT a.*, d.name AS destination_name, c.name AS category_name FROM attractions a LEFT JOIN destinations d ON d.id = a.destination_id LEFT JOIN categories c ON c.id = a.category_id WHERE a.status = :status';
    $params = ['status' => 'active'];

    if ($destinationId !== null) {
        $sql .= ' AND a.destination_id = :destination_id';
        $params['destination_id'] = $destinationId;
    }

    if ($search !== null && $search !== '') {
        $sql .= ' AND (a.name LIKE :search OR a.short_description LIKE :search OR d.name LIKE :search)';
        $params['search'] = '%' . $search . '%';
    }

    $sql .= ' ORDER BY a.display_order ASC, a.created_at DESC';

    if ($limit !== null) {
        $sql .= ' LIMIT ' . (int) $limit;
    }

    $stmt = requireDatabase()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function fetchPosterById(int $id): ?array {
    $stmt = requireDatabase()->prepare('SELECT * FROM ar_posters WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $result = $stmt->fetch();
    return $result ?: null;
}

function fetchHotspotsForPoster(int $posterId): array {
    $stmt = requireDatabase()->prepare('SELECT h.*, a.name AS attraction_name, a.short_description AS attraction_short_description, a.main_image AS attraction_image FROM ar_hotspots h LEFT JOIN attractions a ON a.id = h.attraction_id WHERE h.poster_id = :poster_id ORDER BY h.z_index ASC, h.created_at ASC');
    $stmt->execute(['poster_id' => $posterId]);
    return $stmt->fetchAll();
}

function createDemoData(): void {
    $pdo = requireDatabase();
    $queries = [
        'INSERT INTO categories (name, slug, status, created_at, updated_at) VALUES ("Historical", "historical", "active", NOW(), NOW()) ON DUPLICATE KEY UPDATE name = VALUES(name)',
        'INSERT INTO categories (name, slug, status, created_at, updated_at) VALUES ("Nature", "nature", "active", NOW(), NOW()) ON DUPLICATE KEY UPDATE name = VALUES(name)',
        'INSERT INTO categories (name, slug, status, created_at, updated_at) VALUES ("Culture", "culture", "active", NOW(), NOW()) ON DUPLICATE KEY UPDATE name = VALUES(name)',
        'INSERT INTO categories (name, slug, status, created_at, updated_at) VALUES ("Food", "food", "active", NOW(), NOW()) ON DUPLICATE KEY UPDATE name = VALUES(name)',
    ];

    foreach ($queries as $sql) {
        try {
            $pdo->query($sql);
        } catch (PDOException $e) {
            // Ignore repeated inserts.
        }
    }

    $destinations = [
        ['Heritage District', 'heritage-district', 'Explore architecture, stories, and local charm in the old quarter.', 'A walk through the city’s historic district with preserved heritage houses and cultural landmarks.', 'heritage-district.svg', 'Old Town', 3.1405, 101.6958, 'https://maps.google.com/?q=3.1405,101.6958', 1],
        ['Nature Escape', 'nature-escape', 'Fresh air, scenic viewpoints, and eco trails for outdoor lovers.', 'A restorative destination for mountain lovers, garden walks, and refreshing landscapes.', 'nature-escape.svg', 'Hill Range', 3.1602, 101.7110, 'https://maps.google.com/?q=3.1602,101.7110', 2],
        ['Cultural Village', 'cultural-village', 'Traditional crafts, music, and cultural exhibitions in one place.', 'An immersive location where stories, cultures, and heritage traditions merge.', 'cultural-village.svg', 'City Centre', 3.1497, 101.6942, 'https://maps.google.com/?q=3.1497,101.6942', 3],
        ['Coastal Destination', 'coastal-destination', 'Relax by the shore and enjoy beachside activities and sunset views.', 'A lively coastal region with sea views, heritage sites, and lively food spots.', 'coastal-destination.svg', 'Bayfront', 3.1302, 101.6844, 'https://maps.google.com/?q=3.1302,101.6844', 4],
    ];

    foreach ($destinations as $destination) {
        $stmt = $pdo->prepare('INSERT INTO destinations (name, slug, short_description, full_description, cover_image, location, latitude, longitude, map_url, category_id, status, created_at, updated_at) VALUES (:name, :slug, :short_description, :full_description, :cover_image, :location, :latitude, :longitude, :map_url, :category_id, :status, NOW(), NOW()) ON DUPLICATE KEY UPDATE name = VALUES(name)');
        $stmt->execute([
            'name' => $destination[0],
            'slug' => $destination[1],
            'short_description' => $destination[2],
            'full_description' => $destination[3],
            'cover_image' => $destination[4],
            'location' => $destination[5],
            'latitude' => $destination[6],
            'longitude' => $destination[7],
            'map_url' => $destination[8],
            'category_id' => $destination[9],
            'status' => 'active',
        ]);
    }

    $attractions = [
        ['City Heritage Museum', 1, 1, 'A museum celebrating the city’s history, craftsmanship, and cultural roots.', 'Explore the broad story of the region through preserved artefacts, oral histories, and visual exhibitions.', 'museum.svg', 'https://www.youtube.com/watch?v=ScMzIvxBSi4', 'https://example.com/museum', 'https://www.google.com/maps?q=3.1405,101.6958', 3.1405, 101.6958, '09:00 - 18:00', 'Adults RM 10', 1, 1],
        ['Sunrise Viewpoint', 2, 2, 'Experience a scenic morning lookout overlooking the city and valley.', 'Take in a panoramic view and enjoy a calm morning vista with guided ecology information.', 'viewpoint.svg', 'https://www.youtube.com/watch?v=aqz-KE-bpKQ', 'https://example.com/viewpoint', 'https://www.google.com/maps?q=3.1602,101.7110', 3.1602, 101.7110, '06:00 - 19:00', 'Free entry', 2, 2],
        ['Craft Market', 3, 4, 'Browse handcrafted goods, artisan works, and local souvenirs.', 'Discover a destination shaped by creative makers and traditional cultural craftsmanship.', 'craft-market.svg', 'https://www.youtube.com/watch?v=ysz5S6PUM-U', 'https://example.com/market', 'https://www.google.com/maps?q=3.1497,101.6942', 3.1497, 101.6942, '10:00 - 21:00', 'Open daily', 4, 3],
        ['Harbor Walk', 4, 3, 'A seaside pedestrian route with sunset views and hip food stalls.', 'Enjoy the relaxed seaside promenade, refreshment kiosks, and coastline breeze.', 'harbor-walk.svg', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'https://example.com/harbor', 'https://www.google.com/maps?q=3.1302,101.6844', 3.1302, 101.6844, '08:00 - 22:00', 'Free access', 3, 4],
    ];

    foreach ($attractions as $attraction) {
        $stmt = $pdo->prepare('INSERT INTO attractions (name, destination_id, category_id, short_description, full_description, main_image, youtube_url, website_url, google_maps_url, latitude, longitude, opening_hours, entry_information, status, display_order, created_at, updated_at) VALUES (:name, :destination_id, :category_id, :short_description, :full_description, :main_image, :youtube_url, :website_url, :google_maps_url, :latitude, :longitude, :opening_hours, :entry_information, :status, :display_order, NOW(), NOW()) ON DUPLICATE KEY UPDATE name = VALUES(name)');
        $stmt->execute([
            'name' => $attraction[0],
            'destination_id' => $attraction[1],
            'category_id' => $attraction[2],
            'short_description' => $attraction[3],
            'full_description' => $attraction[4],
            'main_image' => $attraction[5],
            'youtube_url' => $attraction[6],
            'website_url' => $attraction[7],
            'google_maps_url' => $attraction[8],
            'latitude' => $attraction[9],
            'longitude' => $attraction[10],
            'opening_hours' => $attraction[11],
            'entry_information' => $attraction[12],
            'status' => 'active',
            'display_order' => $attraction[13],
        ]);
    }

    $posterStmt = $pdo->prepare('INSERT INTO ar_posters (name, description, poster_image, target_file, target_status, status, created_at, updated_at) VALUES (:name, :description, :poster_image, :target_file, :target_status, :status, NOW(), NOW()) ON DUPLICATE KEY UPDATE name = VALUES(name)');
    $posterStmt->execute([
        'name' => 'City Heritage Poster',
        'description' => 'A poster for the heritage district with multiple AR hotspots.',
        'poster_image' => 'poster-heritage.svg',
        'target_file' => '',
        'target_status' => 'NOT_COMPILED',
        'status' => 'active',
    ]);

    $posterId = (int) $pdo->lastInsertId();

    $hotspotInsert = $pdo->prepare('INSERT INTO ar_hotspots (poster_id, label, x, y, width, height, z_index, attraction_id, video_url, content_type, created_at) VALUES (:poster_id, :label, :x, :y, :width, :height, :z_index, :attraction_id, :video_url, :content_type, NOW()) ON DUPLICATE KEY UPDATE label = VALUES(label)');
    $hotspotInsert->execute([
        'poster_id' => $posterId,
        'label' => 'Heritage Museum',
        'x' => 0.18,
        'y' => 0.25,
        'width' => 0.18,
        'height' => 0.14,
        'z_index' => 1,
        'attraction_id' => 1,
        'video_url' => 'https://www.youtube.com/watch?v=ScMzIvxBSi4',
        'content_type' => 'info',
    ]);
    $hotspotInsert->execute([
        'poster_id' => $posterId,
        'label' => 'Sunrise Viewpoint',
        'x' => 0.52,
        'y' => 0.38,
        'width' => 0.19,
        'height' => 0.16,
        'z_index' => 2,
        'attraction_id' => 2,
        'video_url' => 'https://www.youtube.com/watch?v=aqz-KE-bpKQ',
        'content_type' => 'video',
    ]);
}
