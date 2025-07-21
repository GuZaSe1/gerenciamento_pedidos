<?php 
require 'db.php';

// O datagrid envia o número da página e a quantidade de linhas que deseja
$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$offset = ($page - 1) * $rows;

// Primeira consulta: obter o número TOTAL de registros na tabela de itens p saber quantas paginas existem
$stmt_total = $pdo->query("SELECT count(*) FROM item");
$total = $stmt_total->fetchColumn();

// Segunda consulta: busca apenas os registros da página atual. LIMIT p definir a quantidade e OFFSET p pular a pagina
$stmt_rows = $pdo->prepare("SELECT cod_item, den_item FROM item ORDER BY den_item LIMIT :rows OFFSET :offset");
$stmt_rows->bindParam(':rows', $rows, PDO::PARAM_INT);
$stmt_rows->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt_rows->execute();
$items = $stmt_rows->fetchAll(PDO::FETCH_ASSOC);

// Monta o resultado no formato que o EasyUI espera
$result = ["total" => $total, "rows" => $items];

header('Content-Type: application/json');
echo json_encode($result);