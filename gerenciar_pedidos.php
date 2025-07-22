<?php 
require 'db.php';

// Busca de dados
$sql_pedidos = "SELECT p.num_pedido, c.nom_cliente 
FROM pedido p 
JOIN cliente c 
ON p.cod_cliente = c.cod_cliente 
ORDER BY p.num_pedido";
$pedidos = $pdo->query($sql_pedidos)->fetchAll();

$itens_por_pedido = [];
if (!empty($pedidos)) {
    $id_pedidos = array_column($pedidos, 'num_pedido');
    $placeholders = implode(',', array_fill(0, count($id_pedidos), '?'));

    $sql_itens = "SELECT ip.num_pedido, ip.num_seq_item, i.den_item, ip.qtd_solicitada, ip.pre_unitario,
               (ip.qtd_solicitada * ip.pre_unitario) AS total_item
        FROM item_pedido ip
        JOIN item i ON ip.cod_item = i.cod_item
        WHERE ip.num_pedido IN ($placeholders)
        ORDER BY ip.num_seq_item";

    $stmt_itens = $pdo->prepare($sql_itens);
    $stmt_itens->execute($id_pedidos);
    while ($item = $stmt_itens->fetch()) {
        $itens_por_pedido[$item['num_pedido']][] = $item;
    }
}

$pageTitle = 'Gerenciar Pedidos';
require 'templates/header.php';
?>

<style>
    .pedido { border: 1px solid #ccc; padding: 10px; margin-bottom: 20px; }
    .pedido-header { margin-bottom: 10px; }
    .acoes-pedido { display: flex; gap: 10px; align-items: center; margin-top: 0px; }
    .acoes-pedido form { margin: 0; display: inline-block; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    .item-par { background-color: #f9f9f9; }
    .item-impar { background-color: #fff; }
    .total-geral { text-align: right; font-weight: bold; }
</style>

<div class="main-container" style="max-width: 1200px;">
    <div class="easyui-panel" title="Gerenciamento de Pedidos" style="padding:10px;">
        <div style="margin-bottom:10px;">
            <a href="javascript:void(0)" onclick="abrirDialogInclusaoPedido()" class="easyui-linkbutton" data-options="iconCls:'icon-add'">Incluir Pedido</a>
            <a href="gerenciar_clientes.php" class="easyui-linkbutton" data-options="iconCls:'icon-man'">Gerenciar Clientes</a>
            <a href="gerenciar_itens.php" class="easyui-linkbutton" data-options="iconCls:'icon-tip'">Gerenciar Itens</a>
        </div>

    <?php foreach ($pedidos as $pedido): ?>
        <div class="easyui-panel pedido-panel" title="Pedido: <?= htmlspecialchars($pedido['num_pedido']) ?> | Cliente: <?= htmlspecialchars($pedido['nom_cliente']) ?>" data-options="collapsible:true" style="margin-bottom: 10px;">
            <div class="acoes-pedido">
                <a href="javascript:void(0)" onclick="abrirDialogInclusaoItemPedido(<?= $pedido['num_pedido'] ?>)" class="easyui-linkbutton" data-options="iconCls:'icon-add', plain:true">Incluir Item</a>
                <a href="javascript:void(0)" onclick="abrirDialogModificacaoPedido(<?= $pedido['num_pedido'] ?>)" class="easyui-linkbutton" data-options="iconCls:'icon-edit', plain:true">Modificar Pedido</a>
                <a href="javascript:void(0)" onclick="excluirPedido(<?= $pedido['num_pedido'] ?>)" class="easyui-linkbutton" data-options="iconCls:'icon-remove', plain:true">Excluir Pedido</a>
            </div>
        
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantidade</th>
                        <th>Preço Unitário</th>
                        <th>Total</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $itens_do_pedido_atual = $itens_por_pedido[$pedido['num_pedido']] ?? [];
                    $soma_total_pedido = 0;
                    foreach ($itens_do_pedido_atual as $item):
                        $soma_total_pedido += $item['total_item'];
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($item['den_item']) ?></td>
                            <td><?= number_format($item['qtd_solicitada'], 0, ',', '.') ?></td>
                            <td>R$ <?= number_format($item['pre_unitario'], 2, ',', '.') ?></td>
                            <td>R$ <?= number_format($item['total_item'], 2, ',', '.') ?></td>
                            <td class="acoes-pedido">
                                <a href="javascript:void(0)" onclick="abrirDialogModificacaoItemPedido(<?= $item['num_pedido'] ?>, <?= $item['num_seq_item'] ?>)" class="easyui-linkbutton" data-options="iconCls:'icon-edit', plain:true">Modificar Item</a>
                                <a href="javascript:void(0)" onclick="excluirItemDoPedido(<?= $item['num_pedido'] ?>, <?= $item['num_seq_item'] ?>)" class="easyui-linkbutton" data-options="iconCls:'icon-remove', plain:true">Excluir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="total-geral">TOTAL DO PEDIDO:</td>
                        <td colspan="2"><strong>R$ <?= number_format($soma_total_pedido, 2, ',', '.') ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php endforeach; ?>
    </div>
</div>

<!-- DIALOGS E LÓGICA AJAX -->

<!-- Dialog para Incluir/Modificar PEDIDO -->
<div id="dlg-pedido" class="easyui-dialog" style="width:550px; padding: 10px 20px;"
        closed="true" modal="true" buttons="#dlg-pedido-buttons">
</div>
<div id="dlg-pedido-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="salvarPedido()" style="width:90px">Salvar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:$('#dlg-pedido').dialog('close')" style="width:90px">Cancelar</a>
</div>

<!-- Dialog para Incluir/Modificar ITEM NO PEDIDO -->
<div id="dlg-item-pedido" class="easyui-dialog" style="width:550px; padding: 10px 20px;"
        closed="true" modal="true" buttons="#dlg-item-pedido-buttons">
</div>
<div id="dlg-item-pedido-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="salvarItemPedido()" style="width:90px">Salvar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:$('#dlg-item-pedido').dialog('close')" style="width:90px">Cancelar</a>
</div>

<script>
// --- Funções para Gerenciar PEDIDOS ---
function abrirDialogInclusaoPedido() {
    $('#dlg-pedido').dialog('open').dialog('setTitle', 'Incluir Novo Pedido');
    $('#dlg-pedido').load('controlar_pedido.php?form_only=1', () => $.parser.parse('#dlg-pedido'));
}

function abrirDialogModificacaoPedido(num_pedido) {
    $('#dlg-pedido').dialog('open').dialog('setTitle', 'Modificar Pedido');
    $('#dlg-pedido').load(`controlar_pedido.php?form_only=1&num_pedido=${num_pedido}`, () => $.parser.parse('#dlg-pedido'));
}

function salvarPedido() {
    var form = $('#fm-pedido');
    if (!form.form('validate')) return;
    $.ajax({
        url: 'controlar_pedido.php', type: 'post', data: form.serialize(), dataType: 'json',
        success: function(result) {
            if (result.success) {
                $('#dlg-pedido').dialog('close');
                $.messager.show({ title: 'Sucesso', msg: 'Pedido salvo com sucesso. Atualizando página...' });
                setTimeout(() => location.reload(), 1500); // Recarrega para mostrar as alterações
            } else {
                $.messager.alert('Erro', result.message, 'error');
            }
        },
        error: () => $.messager.alert('Erro Crítico', 'Não foi possível contatar o servidor.', 'error')
    });
}

function excluirPedido(num_pedido) {
    $.messager.confirm('Confirmar Exclusão', 'Tem certeza que deseja excluir este pedido e todos os seus itens?', function(r) {
        if (r) {
            $.post('excluir_pedido.php', { num_pedido: num_pedido }, function(result) {
                if (result.success) {
                    $.messager.show({ title: 'Sucesso', msg: 'Pedido excluído. Atualizando página...' });
                    setTimeout(() => location.reload(), 1500);
                } else {
                    $.messager.alert('Erro', result.message, 'error');
                }
            }, 'json').fail(() => $.messager.alert('Erro Crítico', 'Falha na comunicação com o servidor.', 'error'));
        }
    });
}

// --- Funções para Gerenciar ITENS DENTRO DE UM PEDIDO ---
function abrirDialogInclusaoItemPedido(num_pedido) {
    $('#dlg-item-pedido').dialog('open').dialog('setTitle', `Incluir Item no Pedido Nº ${num_pedido}`);
    $('#dlg-item-pedido').load(`controlar_item_pedido.php?form_only=1&num_pedido=${num_pedido}`, () => $.parser.parse('#dlg-item-pedido'));
}

function abrirDialogModificacaoItemPedido(num_pedido, num_seq_item) {
    $('#dlg-item-pedido').dialog('open').dialog('setTitle', `Modificar Item no Pedido Nº ${num_pedido}`);
    $('#dlg-item-pedido').load(`controlar_item_pedido.php?form_only=1&num_pedido=${num_pedido}&num_seq_item=${num_seq_item}`, () => $.parser.parse('#dlg-item-pedido'));
}

function salvarItemPedido() {
    var form = $('#fm-item-pedido');
    if (!form.form('validate')) return;
    $.ajax({
        url: 'controlar_item_pedido.php', type: 'post', data: form.serialize(), dataType: 'json',
        success: function(result) {
            if (result.success) {
                $('#dlg-item-pedido').dialog('close');
                $.messager.show({ title: 'Sucesso', msg: 'Item salvo. Atualizando página...' });
                setTimeout(() => location.reload(), 1500);
            } else {
                $.messager.alert('Erro', result.message, 'error');
            }
        },
        error: () => $.messager.alert('Erro Crítico', 'Não foi possível contatar o servidor.', 'error')
    });
}

function excluirItemDoPedido(num_pedido, num_seq_item) {
    $.messager.confirm('Confirmar Exclusão', 'Tem certeza que deseja excluir este item do pedido?', function(r) {
        if (r) {
            $.post('excluir_item_pedido.php', { num_pedido: num_pedido, num_seq_item: num_seq_item }, function(result) {
                if (result.success) {
                    $.messager.show({ title: 'Sucesso', msg: 'Item excluído. Atualizando página...' });
                    setTimeout(() => location.reload(), 1500);
                } else {
                    $.messager.alert('Erro', result.message, 'error');
                }
            }, 'json').fail(() => $.messager.alert('Erro Crítico', 'Falha na comunicação com o servidor.', 'error'));
        }
    });
}
</script>

<?php require 'templates/footer.php'; ?>
