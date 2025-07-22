<?php
require 'db.php';

// --- LÓGICA DE PROCESSAMENTO (AJAX POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => true, 'message' => ''];
    $cod_cliente = $_POST['cod_cliente'] ?? '';
    $num_pedido_post = $_POST['num_pedido'] ?: null;

    if (empty($cod_cliente)) {
        $response['success'] = false;
        $response['message'] = 'É necessário selecionar um cliente.';
    } else {
        try {
            // Valida se o cliente existe
            $stmt_check = $pdo->prepare("SELECT 1 FROM cliente WHERE cod_cliente = ?");
            $stmt_check->execute([$cod_cliente]);
            if (!$stmt_check->fetch()) {
                throw new Exception("Cliente com código informado não existe.");
            }

            if ($num_pedido_post) { // UPDATE
                $stmt = $pdo->prepare("UPDATE pedido SET cod_cliente = ? WHERE num_pedido = ?");
                $stmt->execute([$cod_cliente, $num_pedido_post]);
            } else { // INSERT
                $stmt_max = $pdo->query("SELECT MAX(num_pedido) as max_id FROM pedido");
                $max_id = $stmt_max->fetchColumn();
                $novo_id = ($max_id ?? 0) + 1;
                $stmt = $pdo->prepare("INSERT INTO pedido (num_pedido, cod_cliente) VALUES (?, ?)");
                $stmt->execute([$novo_id, $cod_cliente]);
            }
        } catch (Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }
    }
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// --- LÓGICA DE EXIBIÇÃO (GET para carregar o form) ---
$is_form_only = isset($_GET['form_only']);
$num_pedido = $_GET['num_pedido'] ?? null;
$cod_cliente = '';
$is_edit = $num_pedido !== null;

// Busca todos os clientes para o combobox
$stmt_clientes = $pdo->query("SELECT cod_cliente, nom_cliente FROM cliente ORDER BY nom_cliente");
$clientes = $stmt_clientes->fetchAll(PDO::FETCH_ASSOC);
$json_clientes = json_encode($clientes);

if ($is_edit) {
    $stmt = $pdo->prepare("SELECT cod_cliente FROM pedido WHERE num_pedido = ?");
    $stmt->execute([$num_pedido]);
    $pedido = $stmt->fetch();
    if ($pedido) {
        $cod_cliente = $pedido['cod_cliente'];
    } else {
        die("Erro: Pedido não encontrado.");
    }
}

if (!$is_form_only) {
    $pageTitle = $is_edit ? 'Modificar Pedido' : 'Incluir Novo Pedido';
    require 'templates/header.php';
}
?>

<form id="fm-pedido" method="post">
    <input type="hidden" name="num_pedido" value="<?= htmlspecialchars($num_pedido ?? '') ?>">
    <div style="margin-bottom:20px; padding-top: 10px;">
        <input class="easyui-combobox" name="cod_cliente" style="width:100%" data-options="
            label: 'Selecione o Cliente:',
            labelWidth: 140,    
            required: true,
            data: <?= htmlspecialchars($json_clientes, ENT_QUOTES, 'UTF-8') ?>,
            valueField: 'cod_cliente',
            textField: 'nom_cliente',
            prompt: 'Digite ou selecione um cliente...'
        " value="<?= htmlspecialchars($cod_cliente) ?>">
    </div>
</form>

<?php
if (!$is_form_only) {
    require 'templates/footer.php';
}
?>
