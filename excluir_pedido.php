<?php
require 'db.php';
// Aceita apenas requisições POST para segurança
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Acesso inválido.");
}
$num_pedido = $_POST['num_pedido'] ?? null;
$num_seq_item = $_POST['num_seq_item'] ?? null;

if ($num_pedido) {
    // O banco de dados se encarrega de apagar os itens do pedido.
    $stmt_pedido = $pdo->prepare("DELETE FROM item_pedido WHERE num_pedido = ?");
    $stmt_pedido->execute([$num_pedido]);

    $stmt_pedido = $pdo->prepare("DELETE FROM pedido WHERE num_pedido = ?");
    $stmt_pedido->execute([$num_pedido]);
}

header("Location: gerenciar_pedidos.php");
exit;