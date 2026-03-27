<?php
header('Content-Type: application/json');

define('DATA_DIR', __DIR__ . '/../data');
define('RSVP_FILE', DATA_DIR . '/birthday-rsvps.json');
define('VALID_HOUSES', ['gryffindor', 'slytherin', 'ravenclaw', 'hufflepuff']);

$action = $_GET['action'] ?? '';

// GET - Return guest list (name + house only, for public display)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'guests') {
    $rsvps = loadRsvps();

    $guests = [];
    foreach ($rsvps as $rsvp) {
        if (!empty($rsvp['house'])) {
            $guests[] = [
                'name' => $rsvp['name'],
                'house' => $rsvp['house']
            ];
        }
    }

    echo json_encode($guests);
    exit;
}

// POST - Save an RSVP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'rsvp') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON body']);
        exit;
    }

    // Validate name (required, max 100)
    $name = isset($input['name']) ? trim(strip_tags($input['name'])) : '';
    if ($name === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Name is required']);
        exit;
    }
    if (strlen($name) > 100) {
        http_response_code(400);
        echo json_encode(['error' => 'Name must be 100 characters or less']);
        exit;
    }

    // Validate attending (required, boolean)
    if (!isset($input['attending']) || !is_bool($input['attending'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Attending is required and must be a boolean']);
        exit;
    }
    $attending = $input['attending'];

    // Validate allergies (optional, max 500)
    $allergies = isset($input['allergies']) ? trim(strip_tags($input['allergies'])) : '';
    if (strlen($allergies) > 500) {
        http_response_code(400);
        echo json_encode(['error' => 'Allergies must be 500 characters or less']);
        exit;
    }

    // Validate phone (optional, max 20)
    $phone = isset($input['phone']) ? trim(strip_tags($input['phone'])) : '';
    if (strlen($phone) > 20) {
        http_response_code(400);
        echo json_encode(['error' => 'Phone must be 20 characters or less']);
        exit;
    }

    // Validate house (optional, must be in whitelist or empty)
    $house = isset($input['house']) ? trim(strip_tags($input['house'])) : '';
    if ($house !== '' && !in_array(strtolower($house), VALID_HOUSES, true)) {
        http_response_code(400);
        echo json_encode(['error' => 'House must be one of: gryffindor, slytherin, ravenclaw, hufflepuff']);
        exit;
    }
    $house = strtolower($house);

    // Build RSVP record
    $rsvp = [
        'name' => $name,
        'attending' => $attending,
        'allergies' => $allergies,
        'phone' => $phone,
        'house' => $house,
        'timestamp' => gmdate('Y-m-d\TH:i:s\Z')
    ];

    // Load existing RSVPs, append, and save
    $rsvps = loadRsvps();

    if (count($rsvps) >= 200) {
        http_response_code(400);
        echo json_encode(['error' => 'RSVPs are full! Please contact us directly.']);
        exit;
    }

    $rsvps[] = $rsvp;

    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0755, true);
    }

    if (file_put_contents(RSVP_FILE, json_encode($rsvps, JSON_PRETTY_PRINT), LOCK_EX) === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save RSVP']);
        exit;
    }

    echo json_encode(['success' => true]);
    exit;
}

// Invalid action or method
if ($_SERVER['REQUEST_METHOD'] === 'GET' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid action. Use ?action=guests (GET) or ?action=rsvp (POST)']);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
exit;

/**
 * Load RSVPs from the JSON file.
 * Returns an empty array if the file doesn't exist or is invalid.
 */
function loadRsvps() {
    if (!file_exists(RSVP_FILE)) {
        return [];
    }

    $data = json_decode(file_get_contents(RSVP_FILE), true);
    return is_array($data) ? $data : [];
}
