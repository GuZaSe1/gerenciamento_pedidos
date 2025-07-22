<?php
require 'db.php';

// Prepara a resposta JSON padrão
$response = ['success' => false, 'message' => 'Requisição inválida.'];

// Aceita apenas requisições POST para segurança
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cod_item = $_POST['cod_item'] ?? null;

    if ($cod_item) {
        try {
            // Antes de excluir, verificamos se o item tem pedidos associados
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM item_pedido WHERE cod_item = ?");
            $stmt_check->execute([$cod_item]);
            $count = $stmt_check->fetchColumn();

            if ($count > 0) {
                // Se a contagem for maior que 0, o item está em "uso"
                $response['message'] = "Erro: O item (código: " . htmlspecialchars($cod_item) . ") não pode ser excluído, pois está registrado em " . $count . " pedido(s).";
            } else {
                // Se a contagem for 0, o item não tem pedidos e é seguro apagar
                $stmt_delete = $pdo->prepare("DELETE FROM item WHERE cod_item = ?");
                $stmt_delete->execute([$cod_item]);
                
                // Se a exclusão funcionar: Mensagem de sucesso
                $response['success'] = true;
                $response['message'] = "Item (código: " . htmlspecialchars($cod_item) . ") foi excluído com sucesso!";
            }
        } catch (PDOException $e) {
            $response['message'] = "Ocorreu um erro inesperado ao tentar excluir o item: " . $e->getMessage();
        }
    } else {
        // Caso o código do item não tenha sido enviado no formulário
        $response['message'] = "Nenhum código de item foi fornecido para a exclusão.";
    }
}

// Define o cabeçalho como JSON e imprime a resposta.
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>
