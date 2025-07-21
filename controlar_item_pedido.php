<?php   // Modifica e Inclui Item no Pedido
require 'db.php';

// Busca todos os itens cadastrados para preencher o ComboBox (REVISAR)
$stmt_itens_lista = $pdo->query("SELECT cod_item, den_item FROM item ORDER BY den_item");
$itens_lista = $stmt_itens_lista->fetchAll(PDO::FETCH_ASSOC);
$json_itens = json_encode($itens_lista);

$num_pedido = $_GET['num_pedido'] ?? null;
$num_seq_item = $_GET['num_seq_item'] ?? null;
$is_edit = $num_seq_item !== null;

$cod_item = '';
$qtd_solicitada = '';
$pre_unitario = '';
$mensagem_erro = '';

// Caso especial: se o usuario nao digitar nada
if ($num_pedido === null) {
    die("Número do pedido é obrigatório.");
}

// Se for edicao, busca os dados do item do pedido no banco de dados
if ($is_edit) {
    // Consulta para um item expecifico
    $stmt = $pdo->prepare("SELECT * FROM item_pedido WHERE num_pedido = ? AND num_seq_item = ?");
    // Consulta com os IDs recebidos
    $stmt->execute([$num_pedido, $num_seq_item]);
    $item_data = $stmt->fetch();

    // Se for encontrado, executa
    if ($item_data) {
        $cod_item = $item_data['cod_item'];
        $qtd_solicitada = $item_data['qtd_solicitada'];
        $pre_unitario = $item_data['pre_unitario'];
    } else {    // Caso especial 2: se o numero digitado não existir
        die("Item do pedido não encontrado.");
    }
}
// Salva dados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coleta os dados (o ComboBox enviará o 'cod_item' correto)
    $cod_item = $_POST['cod_item'] ?? '';
    $qtd_solicitada = $_POST['qtd_solicitada'] ?? '';
    $pre_unitario = $_POST['pre_unitario'] ?? '';
    $num_pedido_post = $_POST['num_pedido'] ?? null;
    $num_seq_item_post = $_POST['num_seq_item'] ?: null;

    // Valida se o codigo do item existe na tabela
    $stmt = $pdo->prepare("SELECT 1 FROM item WHERE cod_item = ?");
    $stmt->execute([$cod_item]);


    // Valida se o código do item existe
    if ($stmt->fetch()) {
        // Se entrou é valido
        if ($num_seq_item_post) {   // UPDATE pq um numero de sequencia já foi enviado

            $stmt_update = $pdo->prepare("UPDATE item_pedido SET cod_item = ?, qtd_solicitada = ?, pre_unitario = ? WHERE num_pedido = ? AND num_seq_item = ?");
            $stmt_update->execute([$cod_item, $qtd_solicitada, $pre_unitario, $num_pedido_post, $num_seq_item_post]);
        } else { // INSERT pq não tem número de sequência

            $stmt_seq = $pdo->prepare("SELECT MAX(num_seq_item) as max_seq FROM item_pedido WHERE num_pedido = ?"); // Acha o maior numero de sequencia
            $stmt_seq->execute([$num_pedido_post]);

            $novo_seq = ($stmt_seq->fetchColumn() ?: 0) + 1; // Calcula o próximo número de sequência

            // Insere o novo item no banco de dados
            $stmt_insert = $pdo->prepare("INSERT INTO item_pedido (num_pedido, num_seq_item, cod_item, qtd_solicitada, pre_unitario) VALUES (?, ?, ?, ?, ?)");
            $stmt_insert->execute([$num_pedido_post, $novo_seq, $cod_item, $qtd_solicitada, $pre_unitario]);
        }

        header("Location: gerenciar_pedidos.php");
        exit;
    } else {
        $mensagem_erro = "Erro: Código do item informado não existe ou não foi selecionado.";
    }
}

$pageTitle = $is_edit ? 'Modificar Item no Pedido' : 'Incluir Item no Pedido';
require 'templates/header.php';
?>
<div class="form-container">
    <div class="easyui-panel" title="<?= $is_edit ? 'Modificar Item' : 'Incluir Item' ?> no Pedido Nº <?= htmlspecialchars($num_pedido) ?>" style="width:100%; max-width:500px; padding:30px 60px;">
        <form method="post">
            <input type="hidden" name="num_pedido" value="<?= htmlspecialchars($num_pedido) ?>">
            <input type="hidden" name="num_seq_item" value="<?= htmlspecialchars($num_seq_item ?? '') ?>">
            
            <div style="margin-bottom:20px">
                <input class="easyui-combobox" name="cod_item" style="width:100%" data-options="
                    label: 'Item:',
                    labelWidth: 120,
                    required: true,
                    data: <?= htmlspecialchars($json_itens, ENT_QUOTES, 'UTF-8') ?>,
                    valueField: 'cod_item',
                    textField: 'den_item',
                    prompt: 'Digite ou selecione um item...'
                " value="<?= htmlspecialchars($cod_item) ?>">
            </div>
            <div style="margin-bottom:20px">
                <input class="easyui-numberbox" name="qtd_solicitada" style="width:100%" data-options="
                    label: 'Quantidade:',
                    labelWidth: 120,
                    required: true
                " value="<?= $qtd_solicitada ?>">
            </div>
            <div style="margin-bottom:20px">
                <input class="easyui-numberbox" name="pre_unitario" style="width:100%" data-options="
                    label: 'Preço Unitário:',
                    labelWidth: 120,
                    required: true,
                    precision: 2,
                    groupSeparator: '.',
                    decimalSeparator: ','
                " value="<?= $pre_unitario ?>">
            </div>

            <?php if ($mensagem_erro): ?>
                <div class="easyui-panel" style="padding:10px;margin-bottom:20px;color:red;border-color:red;"><?= htmlspecialchars($mensagem_erro) ?></div>
            <?php endif; ?>

            <div>
                <button type="submit" class="easyui-linkbutton" data-options="iconCls:'icon-save'">Salvar</button>
                <a href="gerenciar_pedidos.php" class="easyui-linkbutton" data-options="iconCls: 'icon-cancel'">Cancelar</a>
            </div>
        </form>
    </div>
</div>
<?php require 'templates/footer.php'; ?>