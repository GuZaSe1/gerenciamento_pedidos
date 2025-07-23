<?php
require 'db.php';

header('Content-Type: application/json');

$response = ['total' => 0, 'rows' => []];

try {
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
    $offset = ($page - 1) * $rows;

    $stmt_total = $pdo->query("SELECT count(*) FROM pedido");
    $total = $stmt_total->fetchColumn();

    $sql_rows = "
        SELECT
            p.num_pedido,
            c.nom_cliente,
            COALESCE(SUM(ip.qtd_solicitada * ip.pre_unitario), 0) AS vlr_total
        FROM
            pedido p
        JOIN
            cliente c ON p.cod_cliente = c.cod_cliente
        LEFT JOIN
            item_pedido ip ON p.num_pedido = ip.num_pedido
        GROUP BY
            p.num_pedido, c.nom_cliente
        ORDER BY
            p.num_pedido DESC
        LIMIT :offset, :rows
    ";

    $stmt_rows = $pdo->prepare($sql_rows);
    $stmt_rows->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt_rows->bindParam(':rows', $rows, PDO::PARAM_INT);
    $stmt_rows->execute();
    
    $pedidos = $stmt_rows->fetchAll(PDO::FETCH_ASSOC);

    $response = ["total" => (int)$total, "rows" => $pedidos];

} catch (PDOException $e) {
    http_response_code(500);
    $response['error'] = 'Erro no Banco de Dados: ' . $e->getMessage();
}

echo json_encode($response);
