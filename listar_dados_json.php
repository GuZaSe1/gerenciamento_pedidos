<?php

require 'db.php';

$config = [
    'pedidos' => [
        'sql_total' => "SELECT COUNT(*) FROM pedido",
        'sql_rows'  => "SELECT p.num_pedido, c.nom_cliente,
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
                LIMIT :rows OFFSET :offset"
    ],
    'clientes' => [
        'sql_total' => "SELECT COUNT(*) FROM cliente",
        'sql_rows'  => "SELECT cod_cliente, nom_cliente 
                FROM 
                    cliente 
                ORDER BY 
                    nom_cliente 
                LIMIT :rows OFFSET :offset"
    ],
    'itens' => [
        'sql_total' => "SELECT COUNT(*) FROM item",
        'sql_rows'  => "SELECT cod_item, den_item 
                FROM 
                    item 
                ORDER BY 
                    den_item 
                LIMIT :rows OFFSET :offset"
    ],
];

$tipo = $_POST['tipo'] ?? '';

if (!isset($config[$tipo])) {
    echo json_encode(['total' => 0, 'rows' => [], 'error' => 'Tipo de dado inválido.']);
    exit;
}

$cfg = $config[$tipo];

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$offset = ($page - 1) * $rows;

$result = ['total' => 0, 'rows' => []];

try {
    $stmt_total = $pdo->query($cfg['sql_total']);
    $total = $stmt_total->fetchColumn();

    $result['total'] = (int)$total;
    $stmt_rows = $pdo->prepare($cfg['sql_rows']);
    $stmt_rows->bindParam(':rows', $rows, PDO::PARAM_INT);
    $stmt_rows->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt_rows->execute();

    $result['rows'] = $stmt_rows->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $result['error'] = 'Erro no banco de dados: ' . $e->getMessage();
    http_response_code(500);
}

header('Content-Type: application/json');
echo json_encode($result);
