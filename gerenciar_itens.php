<?php 
session_start();
$pageTitle = 'Gerenciar Itens';
require 'templates/header.php';
?>

<div class="main-container">

    <?php if (isset($_SESSION['success_message'])): // Para exibir mensagens de exclusao ?>
        <div class="easyui-panel" title="Sucesso" style="padding:10px;margin-bottom:10px;border-color:green;color:green;">
            <?php 
                echo htmlspecialchars($_SESSION['success_message']); 
                unset($_SESSION['success_message']); // Limpa a mensagem para não exibir novamente
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="easyui-panel" title="Erro" style="padding:10px;margin-bottom:10px;border-color:red;color:red;">
            <?php 
                echo htmlspecialchars($_SESSION['error_message']); 
                unset($_SESSION['error_message']); // Limpa a mensagem para não exibir novamente
            ?>
        </div>
    <?php endif; ?>

    <div class="easyui-panel" title="Gerenciamento de Itens" style="padding:10px;">
        <div style="margin-bottom:10px;">
            <a href="controlar_item.php" class="easyui-linkbutton" data-options="iconCls:'icon-add'">Incluir Item</a>
            <a href="gerenciar_pedidos.php" class="easyui-linkbutton" data-options="iconCls:'icon-undo'">Voltar para Pedidos</a>
        </div>

        <table id="dg_itens" class="easyui-datagrid" style="width:100%; height:400px"
               data-options="url:'listar_itens_json.php',
                             method:'post',
                             pagination:true,
                             fitColumns:true,
                             singleSelect:true,
                             pageSize:10,
                             pageList:[10,20,50]">
            <thead>
                <tr>
                    <th data-options="field:'cod_item',width:80">Código</th>
                    <th data-options="field:'den_item',width:300">Descrição do Item</th>
                    <th data-options="field:'action',width:150,align:'center',formatter:formatAction">Ações</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<script>
// FormatAction para os botões
function formatAction(value, row) {
    var editUrl = 'controlar_item.php?cod_item=' + row.cod_item;
    var deleteForm = '<form method="POST" action="excluir_item.php" onsubmit="return confirm(\'Tem certeza que deseja excluir este item?\');" style="display:inline-block; margin:0;">' +
                     '<input type="hidden" name="cod_item" value="' + row.cod_item + '">' +
                     '<button type="submit" class="easyui-linkbutton" data-options="iconCls:\'icon-remove\',plain:true">Excluir</button>' +
                     '</form>';
    var editButton = '<a href="' + editUrl + '" class="easyui-linkbutton" data-options="iconCls:\'icon-edit\',plain:true">Modificar</a>';
    return editButton + ' ' + deleteForm;
}

// O script para inicializar o datagrid precisa ser ajustado para renderizar
// os botões após os dados serem carregados via AJAX
$(function(){
    $('#dg_itens').datagrid({
        onLoadSuccess: function() {
            // Re-renderiza os botões de ação cada vez que uma nova página de dados é carregada
            $('.easyui-linkbutton').linkbutton();
        }
    });
});
</script>

<?php 
require 'templates/footer.php'; 
?>