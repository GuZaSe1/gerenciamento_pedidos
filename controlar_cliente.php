<?php   // Modifica e Inclui o Cliente
require 'db.php';

// Verifica se está em modo de edição (se um 'cod_cliente' foi passado na URL)
$cod_cliente_get = $_GET['cod_cliente'] ?? null;
$is_edit = $cod_cliente_get !== null;

$nom_cliente = '';
$mensagem_erro = '';

// Se for edicao, busca os dados do cliente no banco
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

// Processa o formulário enviado (tem que ser POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cod_cliente_post = $_POST['cod_cliente'] ?: null;
    $nom_cliente = $_POST['nom_cliente'] ?? '';

    // Se o nome não está vazio
    if (!empty($nom_cliente)) {
        if ($cod_cliente_post) { // Se tem código, é um UPDATE
            $stmt = $pdo->prepare("UPDATE cliente SET nom_cliente = ? WHERE cod_cliente = ?");
            $stmt->execute([$nom_cliente, $cod_cliente_post]);
        } else { // Se não tem código, é um INSERT
            // Busca o maior 'cod_cliente' e o incrementa
            $stmt_max = $pdo->query("SELECT MAX(cod_cliente) as max_id FROM cliente");
            $max_id = $stmt_max->fetchColumn();
            $novo_id = ($max_id ?? 0) + 1;

            $stmt = $pdo->prepare("INSERT INTO cliente (cod_cliente, nom_cliente) VALUES (?, ?)");
            $stmt->execute([$novo_id, $nom_cliente]);
        }
        header("Location: gerenciar_clientes.php");
        exit;
    } else {
        $mensagem_erro = "O nome do cliente não pode estar vazio.";
    }
}

// Define o título da página e inclui o cabeçalho
$pageTitle = $is_edit ? 'Modificar Cliente' : 'Incluir Novo Cliente';
require 'templates/header.php';
?>
<div class="form-container">
    <div class="easyui-panel" title="<?= htmlspecialchars($pageTitle) ?>" style="width:100%;max-width:500px;padding:30px 60px;">
        <form id="fm-cliente" method="post">
            <input type="hidden" name="cod_cliente" value="<?= htmlspecialchars($cod_cliente_get ?? '') ?>">
            <div style="margin-bottom:20px">

                <input class="easyui-textbox" name="nom_cliente" style="width:100%" data-options="
                    label: 'Nome do Cliente:',
                    labelWidth: 120,
                    required: true,
                    prompt: 'Digite o nome completo do cliente...'
                " value="<?= htmlspecialchars($nom_cliente) ?>">
            </div>

            <?php if ($mensagem_erro): ?>
                <div class="easyui-panel" style="padding:10px;margin-bottom:20px;color:red;border-color:red;">
                    <?= htmlspecialchars($mensagem_erro) ?>
                </div>
            <?php endif; ?>

            <div>
                <button type="submit" class="easyui-linkbutton" data-options="iconCls:'icon-save'">Salvar</button>
                <a href="gerenciar_clientes.php" class="easyui-linkbutton" data-options="iconCls:'icon-cancel'">Cancelar</a>
            </div>
        </form>
    </div>
</div>
<?php require 'templates/footer.php'; ?>