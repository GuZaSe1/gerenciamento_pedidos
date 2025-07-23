<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => true, 'message' => ''];

    $cod_cliente_post = $_POST['cod_cliente'] ?: null;
    $nom_cliente = $_POST['nom_cliente'] ?? '';

    if (empty($nom_cliente)) {
        $response['success'] = false;
        $response['message'] = 'O nome do cliente não pode estar vazio.';
    } else {
        try {
            if ($cod_cliente_post) { // UPDATE
                $stmt = $pdo->prepare("UPDATE cliente SET nom_cliente = ? WHERE cod_cliente = ?");
                $stmt->execute([$nom_cliente, $cod_cliente_post]);
            } else { // INSERT
                $stmt_max = $pdo->query("SELECT MAX(cod_cliente) as max_id FROM cliente");
                $max_id = $stmt_max->fetchColumn();
                $novo_id = ($max_id ?? 0) + 1;

                $stmt = $pdo->prepare("INSERT INTO cliente (cod_cliente, nom_cliente) VALUES (?, ?)");
                $stmt->execute([$novo_id, $nom_cliente]);
            }
        } catch (PDOException $e) {
            $response['success'] = false;
            $response['message'] = 'Erro no banco de dados: ' . $e->getMessage();
        }
    }

    // Define o cabeçalho como JSON e imprime a resposta
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Verifica se é para renderizar apenas o formulário ou a página inteira
$is_form_only = isset($_GET['form_only']);

$cod_cliente_get = $_GET['cod_cliente'] ?? null;
$is_edit = $cod_cliente_get !== null;
$nom_cliente = '';

if ($is_edit) {
    $stmt = $pdo->prepare("SELECT nom_cliente FROM cliente WHERE cod_cliente = ?");
    $stmt->execute([$cod_cliente_get]);
    $cliente = $stmt->fetch();
    if ($cliente) {
        $nom_cliente = $cliente['nom_cliente'];
    } else {
        die("Erro: Cliente não encontrado.");
    }
}

// Se não for 'form_only', inclui o cabeçalho da página
if (!$is_form_only) {
    $pageTitle = $is_edit ? 'Modificar Cliente' : 'Incluir Novo Cliente';
    require 'templates/header.php';
}
?>

<form id="fm-cliente" method="post">
    <input type="hidden" name="cod_cliente" value="<?= htmlspecialchars($cod_cliente_get ?? '') ?>">
    <div style="margin-bottom:20px; padding-top: 10px;">
        <input class="easyui-textbox" name="nom_cliente" style="width:100%" data-options="
            label: 'Nome do Cliente:',
            labelWidth: 120,
            required: true,
            prompt: 'Digite o nome completo do cliente...'
        " value="<?= htmlspecialchars($nom_cliente) ?>">
    </div>
</form>

<?php
// Se não for 'form_only', inclui o rodapé
if (!$is_form_only) {
    require 'templates/footer.php';
}
?>
