<?php   // Modifica e Inclui (NOVO) Item
require 'db.php';

// Verifica se está em modo de edição.
$cod_item_get = $_GET['cod_item'] ?? null;
$is_edit = $cod_item_get !== null;

$den_item = '';
$mensagem_erro = '';

// Se for edição, busca os dados do item no banco
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

// Processa o formulário enviado (tem que ser POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cod_item_post = $_POST['cod_item'] ?: null;
    $den_item = $_POST['den_item'] ?? '';

    // Validações simples
    if (!empty($den_item)) {
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
        header("Location: gerenciar_itens.php");
        exit;
    } else {
        $mensagem_erro = "A descrição do item não pode estar vazia.";
    }
}

$pageTitle = $is_edit ? 'Modificar Item' : 'Incluir Novo Item';
require 'templates/header.php';
?>

<div class="form-container">
    <div class="easyui-panel" title="<?= htmlspecialchars($pageTitle) ?>" style="width:100%; max-width:500px; padding:30px 60px;">
        <form method="post">
            <input type="hidden" name="cod_item" value="<?= htmlspecialchars($cod_item_get ?? '') ?>">
            
            <div style="margin-bottom:20px">
                <input class="easyui-textbox" name="den_item" style="width:100%" data-options="
                    label: 'Descrição do Item:',
                    labelWidth: 140,
                    required: true,
                    prompt: 'Digite a descrição...'
                " value="<?= htmlspecialchars($den_item) ?>">
            </div>

            <?php if ($mensagem_erro): ?>
                <div class="easyui-panel" style="padding:10px;margin-bottom:20px;color:red;border-color:red;"><?= htmlspecialchars($mensagem_erro) ?></div>
            <?php endif; ?>

            <div>
                <button type="submit" class="easyui-linkbutton" data-options="iconCls:'icon-save'">Salvar</button>
                <a href="gerenciar_itens.php" class="easyui-linkbutton" data-options="iconCls:'icon-cancel'">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php require 'templates/footer.php'; ?>