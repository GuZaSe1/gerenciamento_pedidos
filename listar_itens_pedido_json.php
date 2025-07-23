<?php
require 'db.php';

header('Content-Type: application/json');
if (!isset($_GET['num_pedido'])) {
    echo json_encode(['total' => 0, 'rows' => [], 'error' => 'Número do pedido não fornecido.']);
    exit;
}

$num_pedido = intval($_GET['num_pedido']);
$response = ['total' => 0, 'rows' => []];

try {
    $sql = "
        SELECT
            ip.num_pedido, -- <-- AQUI ESTÁ A CORREÇÃO! ADICIONAMOS ESTA LINHA.
            ip.num_seq_item,
            i.den_item,
            ip.qtd_solicitada,
            ip.pre_unitario,
            (ip.qtd_solicitada * ip.pre_unitario) AS total_item
        FROM
            item_pedido ip
        JOIN
            item i ON ip.cod_item = i.cod_item
        WHERE
            ip.num_pedido = :num_pedido
        ORDER BY
            ip.num_seq_item
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':num_pedido', $num_pedido, PDO::PARAM_INT);
    $stmt->execute();

    $raw_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $clean_items = [];


    foreach ($raw_items as $item) {
        $item['qtd_solicitada'] = intval($item['qtd_solicitada']);     // Garante que seja um número inteiro
        $item['pre_unitario']   = floatval($item['pre_unitario']);   // Garante que seja um número com casas decimais
        $item['total_item']     = floatval($item['total_item']);     // Garante que seja um número com casas decimais
        $clean_items[] = $item;
    }

    $response['total'] = count($clean_items);
    $response['rows'] = $clean_items;
} catch (PDOException $e) {
    http_response_code(500);
    $response['error'] = 'Erro no Banco de Dados: ' . $e->getMessage();
}

echo json_encode($response);
