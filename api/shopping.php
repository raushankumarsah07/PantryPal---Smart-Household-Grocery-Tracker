<?php
// Handles shopping list: list, add, toggle (purchased), delete, clear
require __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$uid    = $_SESSION['user_id'];
$col    = $db->shoppingList;
$in     = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $_GET['action'] ?? 'list';

function oid($id) { return new MongoDB\BSON\ObjectId($id); }

switch ($action) {
    case 'list':
        $items = [];
        foreach ($col->find(['userId' => $uid], ['sort' => ['purchased' => 1]]) as $d) {
            $items[] = [
                'id' => (string) $d['_id'],
                'name' => $d['name'],
                'quantity' => $d['quantity'],
                'purchased' => (bool) $d['purchased'],
            ];
        }
        echo json_encode($items);
        break;

    case 'add':
        $name = trim($in['name'] ?? '');
        if ($name === '') { http_response_code(400); echo json_encode(['error' => 'Name is required']); break; }
        $col->insertOne([
            'userId' => $uid,
            'name' => $name,
            'quantity' => trim($in['quantity'] ?? ''),
            'purchased' => false,
        ]);
        echo json_encode(['ok' => true]);
        break;

    case 'toggle':
        $item = $col->findOne(['_id' => oid($in['id']), 'userId' => $uid]);
        if ($item) {
            $col->updateOne(['_id' => $item['_id']], ['$set' => ['purchased' => !$item['purchased']]]);
        }
        echo json_encode(['ok' => true]);
        break;

    case 'delete':
        $col->deleteOne(['_id' => oid($in['id']), 'userId' => $uid]);
        echo json_encode(['ok' => true]);
        break;

    case 'clear':   // remove all purchased items
        $col->deleteMany(['userId' => $uid, 'purchased' => true]);
        echo json_encode(['ok' => true]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Unknown action']);
}
