<?php

include 'config.php';
header('Content-Type: application/json');
$id = (int)($_GET['return_id'] ?? 0);
$res = $conn->query("SELECT * FROM return_item WHERE return_id = $id");
if($row = $res->fetch_assoc()) {
    echo json_encode(['status'=>'success','data'=>$row]);
} else {
    echo json_encode(['status'=>'error','message'=>'Not found']);
}
?>