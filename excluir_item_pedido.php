<?php
require 'db.php';
$response = ['success' => false, 'message' => 'Requisição inválida.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $num_pedido = $_POST['num_pedido'] ?? null;
    $num_seq_item = $_POST['num_seq_item'] ?? null;

    if ($num_pedido && $num_seq_item) {
        try {
            $stmt = $pdo->prepare("DELETE FROM item_pedido WHERE num_pedido = ? AND num_seq_item = ?");
            $stmt->execute([$num_pedido, $num_seq_item]);
            $response['success'] = true;
            $response['message'] = 'Item do pedido excluído com sucesso.';
        } catch (Exception $e) {
            $response['message'] = 'Erro ao excluir o item do pedido: ' . $e->getMessage();
        }
    } else {
        $response['message'] = 'Dados insuficientes para a exclusão.';
    }
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
