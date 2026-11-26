<?php
// Handles all grocery actions: list, add, update, adjust, delete
require __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$uid    = $_SESSION['user_id'];
$col    = $db->groceries;
$in     = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $_GET['action'] ?? 'list';

function oid($id) { return new MongoDB\BSON\ObjectId($id); }

// Expiry logic: compare expiry date with today's date
function expiryStatus($expiry) {
    if (!$expiry) return ['none', 'No expiry'];
    $days = (int) round((strtotime($expiry) - strtotime(date('Y-m-d'))) / 86400);
    if ($days < 0)  return ['expired', 'Expired'];
    if ($days == 0) return ['today', 'Expires today'];
    if ($days <= 3) return ['soon', "Expires in $days day" . ($days > 1 ? 's' : '')];
    return ['safe', 'Safe'];
}

function cleanFields($in) {
    return [
        'name'        => trim($in['name'] ?? ''),
        'quantity'    => max(0, (float) ($in['quantity'] ?? 0)),
        'unit'        => trim($in['unit'] ?? ''),
        'minQuantity' => max(0, (float) ($in['minQuantity'] ?? 0)),
        'expiryDate'  => trim($in['expiryDate'] ?? ''),
    ];
}

switch ($action) {
    case 'list':
        $items = [];
        foreach ($col->find(['userId' => $uid], ['sort' => ['name' => 1]]) as $d) {
            [$exp, $expText] = expiryStatus($d['expiryDate'] ?? '');
            $low = $d['quantity'] < $d['minQuantity'];
            $items[] = [
                'id' => (string) $d['_id'],
                'name' => $d['name'],
                'quantity' => $d['quantity'],
                'unit' => $d['unit'],
                'minQuantity' => $d['minQuantity'],
                'expiryDate' => $d['expiryDate'] ?? '',
                'expiry' => $exp,
                'expiryText' => $expText,
                'lowStock' => $low,
            ];
        }
        echo json_encode($items);
        break;

    case 'add':
        $f = cleanFields($in);
        if ($f['name'] === '') { http_response_code(400); echo json_encode(['error' => 'Name is required']); break; }
        $f['userId'] = $uid;
        $col->insertOne($f);
        echo json_encode(['ok' => true]);
        break;

    case 'update':
        $f = cleanFields($in);
        $col->updateOne(['_id' => oid($in['id']), 'userId' => $uid], ['$set' => $f]);
        echo json_encode(['ok' => true]);
        break;

    case 'adjust':   // +1 / -1 buttons
        $item = $col->findOne(['_id' => oid($in['id']), 'userId' => $uid]);
        if ($item) {
            $new = max(0, $item['quantity'] + (float) $in['delta']);
            $col->updateOne(['_id' => $item['_id']], ['$set' => ['quantity' => $new]]);
        }
        echo json_encode(['ok' => true]);
        break;

    case 'delete':
        $col->deleteOne(['_id' => oid($in['id']), 'userId' => $uid]);
        echo json_encode(['ok' => true]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Unknown action']);
}
