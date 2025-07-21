<?php
session_start();

require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = "Acesso inválido. A exclusão deve ser feita a partir de um formulário.";
    header('Location: gerenciar_itens.php');
    exit;
}

$cod_item = $_POST['cod_item'] ?? null;

if ($cod_item) {
    // Antes de tentar apagar, pergunta ao banco se este item está em algum pedido
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM item_pedido WHERE cod_item = ?");
    $stmt_check->execute([$cod_item]);
    $count = $stmt_check->fetchColumn();

    if ($count > 0) {
        // Se a contagem for maior que 0, o item está em uso
        $_SESSION['error_message'] = "Erro: O item (código: " . htmlspecialchars($cod_item) . ") não pode ser excluído, pois está registrado em " . $count . " pedido(s).";
    } else {
        // Se a contagem for 0, o item não está em uso e é seguro apagar
        try {
            $stmt_delete = $pdo->prepare("DELETE FROM item WHERE cod_item = ?");
            $stmt_delete->execute([$cod_item]);
            
            // Se a exclusao funcionar: Mensagem de sucesso
            $_SESSION['success_message'] = "Item (código: " . htmlspecialchars($cod_item) . ") foi excluído com sucesso!";
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Ocorreu um erro inesperado no banco de dados ao tentar excluir o item: " . $e->getMessage();
        }
    }
} else {
    // Caso o código do item não tenha sido enviado no formulário
    $_SESSION['error_message'] = "Nenhum código de item foi fornecido para a exclusão.";
}
header("Location: gerenciar_itens.php");
exit;

?>