<?php
require 'db.php';

$response = ['success' => false, 'message' => 'Requisição inválida.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cod_cliente = $_POST['cod_cliente'] ?? null;

    if ($cod_cliente) {
        try {
            // Verifica se o cliente tem pedidos associados
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM pedido WHERE cod_cliente = ?");
            $stmt_check->execute([$cod_cliente]);
            $count = $stmt_check->fetchColumn();

            if ($count > 0) {
                // Se tiver pedidos retorna erro
                $response['success'] = false;
                $response['message'] = "O cliente (código: " . htmlspecialchars($cod_cliente) . ") não pode ser excluído, pois está associado a " . $count . " pedido(s).";
            } else {
                // Se não tiver pedidos exclui
                $stmt_delete = $pdo->prepare("DELETE FROM cliente WHERE cod_cliente = ?");
                $deleted = $stmt_delete->execute([$cod_cliente]);

                if ($deleted) {
                    $response['success'] = true;
                    $response['message'] = "Cliente (código: " . htmlspecialchars($cod_cliente) . ") foi excluído com sucesso!";
                } else {
                    $response['success'] = false;
                    $response['message'] = "Ocorreu um erro ao tentar excluir o cliente do banco de dados.";
                }
            }
        } catch (PDOException $e) {
            // Em caso de erro de banco de dados, retorna uma mensagem genérica
            $response['message'] = "Erro no banco de dados ao tentar excluir o cliente.";
        }
    } else {
        // Caso o código do cliente não tenha sido enviado
        $response['message'] = "Nenhum código de cliente foi fornecido para a exclusão.";
    }
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
