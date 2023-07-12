<?php
include 'main.php';
if (!is_loggedin($pdo)) {
    exit('error');
}
if (!isset($_GET['id'])) {
    exit('error');
}
$stmt = $pdo->prepare('UPDATE accounts SET status = "Occupied" WHERE id = ?');
$stmt->execute([ $_SESSION['account_id'] ]);
$stmt = $pdo->prepare('SELECT c.*, m.msg, a.full_name AS account_sender_full_name, a2.full_name AS account_receiver_full_name FROM conversations c JOIN accounts a ON a.id = c.account_sender_id JOIN accounts a2 ON a2.id = c.account_receiver_id LEFT JOIN messages m ON m.conversation_id = c.id WHERE c.id = ? AND (c.account_sender_id = ? OR c.account_receiver_id = ?)');
$stmt->execute([ $_GET['id'], $_SESSION['account_id'], $_SESSION['account_id'] ]);
$conversation = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$conversation) {
    exit('error');
}
$stmt = $pdo->prepare('SELECT * FROM messages WHERE conversation_id = ? ORDER BY submit_date ASC');
$stmt->execute([ $_GET['id'] ]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
$messages = [];
foreach ($results as $result) {
    $messages[date('y/m/d', strtotime($result['submit_date']))][] = $result;
}
?>
<div class="chat-widget-messages">
    <p class="date">You're now chatting with <?=htmlspecialchars($_SESSION['account_id']==$conversation['account_sender_id']?$conversation['account_receiver_full_name']:$conversation['account_sender_full_name'], ENT_QUOTES)?>!</p>
    <?php foreach ($messages as $date => $array): ?>
    <p class="date"><?=$date==date('y/m/d')?'Today':$date?></p>
    <?php foreach ($array as $message): ?>
    <div class="chat-widget-message<?=$_SESSION['account_id']==$message['account_id']?'':' alt'?>" title="<?=date('H:i\p\m', strtotime($message['submit_date']))?>"><?=htmlspecialchars($message['msg'], ENT_QUOTES)?></div>
    <?php endforeach; ?>
    <?php endforeach; ?>
</div>
<form action="post_message.php" method="post" class="chat-widget-input-message" autocomplete="off">
    <input style="color:black;" type="text" name="msg" placeholder="Message">
    <input type="hidden" name="id" value="<?=$conversation['id']?>">
</form>