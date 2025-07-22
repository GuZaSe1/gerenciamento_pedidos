<?php
require 'db.php';

// --- LÓGICA DE PROCESSAMENTO (AJAX POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => true, 'message' => ''];

    $cod_item_post = $_POST['cod_item'] ?: null;
    $den_item = $_POST['den_item'] ?? '';

    if (empty($den_item)) {
        $response['success'] = false;
        $response['message'] = 'A descrição do item não pode estar vazia.';
    } else {
        try {
            if ($cod_item_post) { // UPDATE
                $stmt = $pdo->prepare("UPDATE item SET den_item = ? WHERE cod_item = ?");
                $stmt->execute([$den_item, $cod_item_post]);
            } else { // INSERT
                $stmt_max = $pdo->query("SELECT MAX(cod_item) as max_id FROM item");
                $max_id = $stmt_max->fetchColumn();
                $novo_id = ($max_id ?? 0) + 1;
                
                $stmt = $pdo->prepare("INSERT INTO item (cod_item, den_item) VALUES (?, ?)");
                $stmt->execute([$novo_id, $den_item]);
            }
        } catch (PDOException $e) {
            $response['success'] = false;
            $response['message'] = 'Erro no banco de dados: ' . $e->getMessage();
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// --- LÓGICA DE EXIBIÇÃO (GET para carregar o form no dialog) ---
$is_form_only = isset($_GET['form_only']);
$cod_item_get = $_GET['cod_item'] ?? null;
$is_edit = $cod_item_get !== null;
$den_item = '';

if ($is_edit) {
    $stmt = $pdo->prepare("SELECT den_item FROM item WHERE cod_item = ?");
    $stmt->execute([$cod_item_get]);
    $item = $stmt->fetch();
    if ($item) {
        $den_item = $item['den_item'];
    } else {
        die("Erro: Item não encontrado.");
    }
}

if (!$is_form_only) {
    $pageTitle = $is_edit ? 'Modificar Item' : 'Incluir Novo Item';
    require 'templates/header.php';
}
?>

<form id="fm-item" method="post">
    <input type="hidden" name="cod_item" value="<?= htmlspecialchars($cod_item_get ?? '') ?>">
    <div style="margin-bottom:20px; padding-top: 10px;">
        <input class="easyui-textbox" name="den_item" style="width:100%" data-options="
            label: 'Descrição do Item:',
            labelWidth: 140,
            required: true,
            prompt: 'Digite a descrição...'
        " value="<?= htmlspecialchars($den_item) ?>">
    </div>
</form>

<?php
if (!$is_form_only) {
    require 'templates/footer.php';
}
?>
