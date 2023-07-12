<?php
include 'main.php';
if (!is_loggedin($pdo)) {
    exit('error');
}
if (!isset($_POST['id'], $_POST['msg'])) {
    exit('error');
}
$stmt = $pdo->prepare('SELECT id FROM conversations WHERE id = ? AND (account_sender_id = ? OR account_receiver_id = ?)');
$stmt->execute([ $_POST['id'], $_SESSION['account_id'], $_SESSION['account_id'] ]);
$conversation = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$conversation) {
    exit('error');
}
$stmt = $pdo->prepare('INSERT INTO messages (conversation_id,account_id,msg,submit_date) VALUES (?,?,?,?)');
$stmt->execute([ $_POST['id'], $_SESSION['account_id'], $_POST['msg'], date('Y-m-d H:i:s') ]);
exit('success');
?>