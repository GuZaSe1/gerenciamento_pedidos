<?php
//Inicia a sessão para usar as variáveis $_SESSION
session_start();

require 'db.php';

// Aceita apenas requisições POST para segurança
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Define uma mensagem de erro na sessão em caso de acesso indevido
    $_SESSION['error_message'] = "Acesso inválido. A exclusão deve ser feita a partir de um formulário.";
    header('Location: gerenciar_clientes.php');
    exit;
}

$cod_cliente = $_POST['cod_cliente'] ?? null;

if ($cod_cliente) {
    // Antes de excluir, verificamos se o cliente tem pedidos associados
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM pedido WHERE cod_cliente = ?");
    $stmt_check->execute([$cod_cliente]);
    $count = $stmt_check->fetchColumn();

    if ($count > 0) {
        // Se a contagem for maior que 0, o cliente está em "uso"
        $_SESSION['error_message'] = "Erro: O cliente (código: " . htmlspecialchars($cod_cliente) . ") não pode ser excluído, pois está registrado em " . $count . " pedido(s).";
    } else {
        // Se a contagem for 0, o cliente não tem pedidos e é seguro apagar
        try {
            $stmt_delete = $pdo->prepare("DELETE FROM cliente WHERE cod_cliente = ?");
            $stmt_delete->execute([$cod_cliente]);
            
            // Se a exclusão funcionar: Mensagem de sucesso
            $_SESSION['success_message'] = "Cliente (código: " . htmlspecialchars($cod_cliente) . ") foi excluído com sucesso!";
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Ocorreu um erro inesperado ao tentar excluir o cliente: " . $e->getMessage();
        }
    }
} else {
    // Caso o código do cliente não tenha sido enviado no formulário
    $_SESSION['error_message'] = "Nenhum código de cliente foi fornecido para a exclusão.";
}

header("Location: gerenciar_clientes.php");
exit;
