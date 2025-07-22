<?php
require 'db.php';
$response = ['success' => false, 'message' => 'Requisição inválida.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $num_pedido = $_POST['num_pedido'] ?? null;
    if ($num_pedido) {
        $pdo->beginTransaction();
        try {
            // Exclui primeiro os itens do pedido
            $stmt_itens = $pdo->prepare("DELETE FROM item_pedido WHERE num_pedido = ?");
            $stmt_itens->execute([$num_pedido]);

            // Depois exclui o pedido principal
            $stmt_pedido = $pdo->prepare("DELETE FROM pedido WHERE num_pedido = ?");
            $stmt_pedido->execute([$num_pedido]);
            
            $pdo->commit();
            $response['success'] = true;
            $response['message'] = 'Pedido excluído com sucesso.';
        } catch (Exception $e) {
            $pdo->rollBack();
            $response['message'] = 'Erro ao excluir o pedido: ' . $e->getMessage();
        }
    } else {
        $response['message'] = 'Número do pedido não fornecido.';
    }
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
