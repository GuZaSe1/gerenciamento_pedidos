<?php
session_start();
$pageTitle = 'Gerenciar Clientes';
require 'templates/header.php';
?>

<div class="main-container">

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="easyui-panel" title="Sucesso" style="padding:10px;margin-bottom:10px;border-color:green;color:green;">
            <?php
            echo htmlspecialchars($_SESSION['success_message']);
            unset($_SESSION['success_message']); // Limpa a mensagem
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="easyui-panel" title="Erro" style="padding:10px;margin-bottom:10px;border-color:red;color:red;">
            <?php
            echo htmlspecialchars($_SESSION['error_message']);
            unset($_SESSION['error_message']); // Limpa a mensagem
            ?>
        </div>
    <?php endif; ?>

    <div class="easyui-panel" title="Gerenciamento de Clientes" style="padding:10px;">
        <div style="margin-bottom:10px;">
            <a href="controlar_cliente.php" class="easyui-linkbutton" data-options="iconCls:'icon-add'">Incluir Cliente</a>
            <a href="gerenciar_pedidos.php" class="easyui-linkbutton" data-options="iconCls:'icon-undo'">Voltar para Pedidos</a>
        </div>

        <table id="dg_itens" class="easyui-datagrid" style="width:100%; height:400px"
            data-options="url:'listar_clientes_json.php',
                             method:'post',
                             pagination:true,
                             fitColumns:true,
                             singleSelect:true,
                             pageSize:10,
                             pageList:[10,20,50]">
            <thead>
                <tr>
                    <th data-options="field:'cod_cliente',width:80">Código</th>
                    <th data-options="field:'nom_cliente',width:300">Nome do Cliente</th>
                    <th data-options="field:'action',width:150,align:'center',formatter:formatAction">Ações</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<script>
    // FormatAction para os botões
    function formatAction(value, row) {
        var editUrl = 'controlar_cliente.php?cod_cliente=' + row.cod_cliente;
        var deleteForm = '<form method="POST" action="excluir_cliente.php" onsubmit="return confirm(\'Tem certeza que deseja excluir este cliente?\');" style="display:inline-block; margin:0;">' +
            '<input type="hidden" name="cod_cliente" value="' + row.cod_cliente + '">' +
            '<button type="submit" class="easyui-linkbutton" data-options="iconCls:\'icon-remove\',plain:true">Excluir</button>' +
            '</form>';
        var editButton = '<a href="' + editUrl + '" class="easyui-linkbutton" data-options="iconCls:\'icon-edit\',plain:true">Modificar</a>';
        return editButton + ' ' + deleteForm;
    }

    // O script para inicializar o datagrid precisa ser ajustado para renderizar
    // os botões após os dados serem carregados via AJAX
    $(function() {
        $('#dg_itens').datagrid({
            onLoadSuccess: function(data) {
                // Re-renderiza os botões de ação cada vez que uma nova página de dados é carregada
                $('.easyui-linkbutton').linkbutton();
            }
        });
    });
</script>

<?php require 'templates/footer.php'; ?>