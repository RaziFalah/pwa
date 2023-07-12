<?php
include 'main.php';
if (!is_loggedin($pdo)) {
    exit('error');
}
$stmt = $pdo->prepare('UPDATE accounts SET status = "Waiting" WHERE id = ?');
$stmt->execute([ $_SESSION['account_id'] ]);
$stmt = $pdo->prepare('SELECT * FROM conversations WHERE (account_sender_id = ? OR account_receiver_id = ?) AND submit_date > date_sub(?, interval 1 minute)');
$stmt->execute([ $_SESSION['account_id'], $_SESSION['account_id'], date('Y-m-d H:i:s') ]);
$conversation = $stmt->fetch(PDO::FETCH_ASSOC);
if ($conversation) {
    exit($conversation['id']);  
}
if ($_SESSION['account_role'] == 'Operator') {
    $stmt = $pdo->prepare('SELECT * FROM accounts WHERE role != "Operator" AND status = "Waiting" AND last_seen > date_sub(?, interval 1 minute)');
} else {
    $stmt = $pdo->prepare('SELECT * FROM accounts WHERE role = "Operator" AND status = "Waiting" AND last_seen > date_sub(?, interval 1 minute)');
}
$stmt->execute([ date('Y-m-d H:i:s') ]);
$account = $stmt->fetch(PDO::FETCH_ASSOC);
if ($account) {
    $stmt = $pdo->prepare('SELECT * FROM conversations WHERE (account_sender_id = ? OR account_receiver_id = ?) AND (account_sender_id = ? OR account_receiver_id = ?)');
    $stmt->execute([ $_SESSION['account_id'], $_SESSION['account_id'], $account['id'], $account['id'] ]);
    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        $stmt = $pdo->prepare('INSERT INTO conversations (account_sender_id,account_receiver_id,submit_date) VALUES (?,?,?)');
        $stmt->execute([ $_SESSION['account_id'], $account['id'], date('Y-m-d H:i:s')]);
        
        exit($pdo->lastInsertId());       
    }   else {
                $stmt = $pdo->prepare('INSERT INTO conversations (account_sender_id,account_receiver_id,submit_date) VALUES (?,?,?)');
                $stmt->execute([ $_SESSION['account_id'], $account['id'], date('Y-m-d H:i:s')]);
                exit($pdo->lastInsertId());  
    }
}
exit('error');
?>