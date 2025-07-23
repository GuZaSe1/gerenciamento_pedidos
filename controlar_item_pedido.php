<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => true, 'message' => ''];

    $cod_item = $_POST['cod_item'] ?? '';
    $qtd_solicitada = $_POST['qtd_solicitada'] ?? '';
    $pre_unitario = $_POST['pre_unitario'] ?? '';
    $num_pedido_post = $_POST['num_pedido'] ?? null;
    $num_seq_item_post = $_POST['num_seq_item'] ?: null;

    try {
        if (empty($cod_item) || empty($qtd_solicitada) || empty($pre_unitario) || empty($num_pedido_post)) {
            throw new Exception("Todos os campos são obrigatórios.");
        }

        $stmt_check = $pdo->prepare("SELECT 1 FROM item WHERE cod_item = ?");
        $stmt_check->execute([$cod_item]);
        if (!$stmt_check->fetch()) {
            throw new Exception("Erro: Código do item informado não existe ou não foi selecionado.");
        }

        if ($num_seq_item_post) { // UPDATE
            $stmt_update = $pdo->prepare("UPDATE item_pedido SET cod_item = ?, qtd_solicitada = ?, pre_unitario = ? WHERE num_pedido = ? AND num_seq_item = ?");
            $stmt_update->execute([$cod_item, $qtd_solicitada, $pre_unitario, $num_pedido_post, $num_seq_item_post]);
        } else { // INSERT
            $stmt_seq = $pdo->prepare("SELECT MAX(num_seq_item) as max_seq FROM item_pedido WHERE num_pedido = ?");
            $stmt_seq->execute([$num_pedido_post]);
            $novo_seq = ($stmt_seq->fetchColumn() ?: 0) + 1;

            $stmt_insert = $pdo->prepare("INSERT INTO item_pedido (num_pedido, num_seq_item, cod_item, qtd_solicitada, pre_unitario) VALUES (?, ?, ?, ?, ?)");
            $stmt_insert->execute([$num_pedido_post, $novo_seq, $cod_item, $qtd_solicitada, $pre_unitario]);
        }
    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = $e->getMessage();
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

$is_form_only = isset($_GET['form_only']);
$num_pedido = $_GET['num_pedido'] ?? null;
$num_seq_item = $_GET['num_seq_item'] ?? null;
$is_edit = $num_seq_item !== null;

if ($num_pedido === null) die("Número do pedido é obrigatório.");

// Busca de dados para o formulário
$stmt_itens_lista = $pdo->query("SELECT cod_item, den_item FROM item ORDER BY den_item");
$itens_lista = $stmt_itens_lista->fetchAll(PDO::FETCH_ASSOC);
$json_itens = json_encode($itens_lista);

$cod_item = '';
$qtd_solicitada = '';
$pre_unitario = '';

if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM item_pedido WHERE num_pedido = ? AND num_seq_item = ?");
    $stmt->execute([$num_pedido, $num_seq_item]);
    $item_data = $stmt->fetch();
    if ($item_data) {
        $cod_item = $item_data['cod_item'];
        $qtd_solicitada = $item_data['qtd_solicitada'];
        $pre_unitario = $item_data['pre_unitario'];
    } else {
        die("Item do pedido não encontrado.");
    }
}

if (!$is_form_only) {
    $pageTitle = $is_edit ? 'Modificar Item no Pedido' : 'Incluir Item no Pedido';
    require 'templates/header.php';
}
?>

<form id="fm-item-pedido" method="post">
    <input type="hidden" name="num_pedido" value="<?= htmlspecialchars($num_pedido) ?>">
    <input type="hidden" name="num_seq_item" value="<?= htmlspecialchars($num_seq_item ?? '') ?>">

    <div style="margin-bottom:20px; padding-top:10px;">
        <input class="easyui-combobox" name="cod_item" style="width:100%" data-options="
            label: 'Item:', labelWidth: 120, required: true,
            data: <?= htmlspecialchars($json_itens, ENT_QUOTES, 'UTF-8') ?>,
            valueField: 'cod_item', textField: 'den_item',
            prompt: 'Digite ou selecione um item...'
        " value="<?= htmlspecialchars($cod_item) ?>">
    </div>
    <div style="margin-bottom:20px">
        <input class="easyui-numberbox" name="qtd_solicitada" style="width:100%" data-options="
            label: 'Quantidade:', labelWidth: 120, required: true
        " value="<?= $qtd_solicitada ?>">
    </div>
    <div style="margin-bottom:20px">
        <input class="easyui-numberbox" name="pre_unitario" style="width:100%" data-options="
            label: 'Preço Unitário:', labelWidth: 120, required: true,
            precision: 2, groupSeparator: '.', decimalSeparator: ','
        " value="<?= $pre_unitario ?>">
    </div>
</form>

<?php
if (!$is_form_only) {
    require 'templates/footer.php';
}
?>
