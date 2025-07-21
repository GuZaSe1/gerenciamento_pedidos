<?php   // Modifica e Inclui NOVO Pedido
require 'db.php';

// Busca todos os clientes cadastrados para preencher o ComboBox (REVISAR)
$stmt_clientes = $pdo->query("SELECT cod_cliente, nom_cliente FROM cliente ORDER BY nom_cliente");
$clientes = $stmt_clientes->fetchAll(PDO::FETCH_ASSOC);
$json_clientes = json_encode($clientes);

$num_pedido = $_GET['num_pedido'] ?? null;
$cod_cliente = '';
$mensagem_erro = '';
$is_edit = $num_pedido !== null;

// Se esta editando, busca no banco de dados
if ($is_edit) {
    $stmt = $pdo->prepare("SELECT cod_cliente FROM pedido WHERE num_pedido = ?");   // Placeholder
    $stmt->execute([$num_pedido]);  // Faz a consulta que foi preparada, substitui o ? pelo valor num_pedido
    $pedido = $stmt->fetch();   // Se for encontrado, a variavel se tornara um array
    
    if ($pedido) {
        $cod_cliente = $pedido['cod_cliente'];
    } else {
        die("Erro: Pedido não encontrado.");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Tem q ser POST
    $cod_cliente = $_POST['cod_cliente'] ?? ''; // Se nada for enviado, a variavel se torna nula
    $num_pedido_post = $_POST['num_pedido'] ?: null;

    // Validação
    $stmt = $pdo->prepare("SELECT 1 FROM cliente WHERE cod_cliente = ?");
    $stmt->execute([$cod_cliente]); //Prepara uma consulta para ver se realmente existe na tabela
    if ($stmt->fetch()) { // Se funcionar, entra 
        if ($num_pedido_post) { // Update
            $stmt = $pdo->prepare("UPDATE pedido SET cod_cliente = ? WHERE num_pedido = ?");
            $stmt->execute([$cod_cliente, $num_pedido_post]); // Executa uma consulta para atualizar o cliente
        } 
        else { // Para adicionar um novo pedido, incrementa um por um
        $stmt_max = $pdo->query("SELECT MAX(num_pedido) as max_id FROM pedido");
        $max_id = $stmt_max->fetchColumn();
        $novo_id = ($max_id ?? 0) + 1;
        $stmt = $pdo->prepare("INSERT INTO pedido (num_pedido, cod_cliente) VALUES (?, ?)");
        $stmt->execute([$novo_id, $cod_cliente]);
    }
        header("Location: gerenciar_pedidos.php");
        exit;
    } else {
        $mensagem_erro = "Erro: Cliente com código informado não existe.";
    }
}

$pageTitle = $is_edit ? 'Modificar Pedido' : 'Incluir Novo Pedido';
require 'templates/header.php';
?>

<div class="form-container">
    <div class="easyui-panel" title="<?= $is_edit ? 'Modificar Pedido ' . htmlspecialchars($num_pedido) : 'Incluir Novo Pedido' ?>" style="width:100%; max-width:500px; padding:30px 60px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <form id="fm-pedido" method="post">
            <input type="hidden" name="num_pedido" value="<?= htmlspecialchars($num_pedido ?? '') ?>">
            <div style="margin-bottom:20px">
                
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
            
            <?php if ($mensagem_erro): ?>
                <div class="easyui-panel" style="padding:10px;margin-bottom:20px;color:red;border-color:red;">
                    <?= htmlspecialchars($mensagem_erro) ?>
                </div>
            <?php endif; ?>

            <div>
                <button type="submit" class="easyui-linkbutton" data-options="iconCls:'icon-ok'">Salvar</button>
                <a href="gerenciar_pedidos.php" class="easyui-linkbutton" data-options="iconCls:'icon-cancel'">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php require 'templates/footer.php'; ?>