<?php
session_start();
$pageTitle = 'Gerenciar Itens';
require 'templates/header.php';
?>

<div class="main-container">
    <div class="easyui-panel" title="Gerenciamento de Itens" style="padding:10px;">
        <div style="margin-bottom:10px;">
            <a href="javascript:void(0)" onclick="abrirDialogInclusaoItem()" class="easyui-linkbutton" data-options="iconCls:'icon-add'">Incluir Item</a>
            <a href="gerenciar_pedidos.php" class="easyui-linkbutton" data-options="iconCls:'icon-undo'">Voltar para Pedidos</a>
        </div>

        <table id="dg_itens" style="width:100%; height:400px"></table>
    </div>
</div>

<div id="dlg-item" class="easyui-dialog" style="width:550px; padding: 10px 20px;"
    closed="true" modal="true" buttons="#dlg-item-buttons">
</div>
<div id="dlg-item-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="salvarItem()" style="width:90px">Salvar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:$('#dlg-item').dialog('close')" style="width:90px">Cancelar</a>
</div>

<script>
    $(document).ready(function() {
        $('#dg_itens').datagrid({
            url: 'listar_dados_json.php',
            queryParams: {
                tipo: 'itens'
            },
            method: 'post',
            pagination: true,
            fitColumns: true,
            singleSelect: true,
            pageSize: 10,
            pageList: [10, 20, 50],

            columns: [
                [{
                        field: 'cod_item',
                        title: 'Código',
                        width: 80,
                        align: 'center'
                    },
                    {
                        field: 'den_item',
                        title: 'Descrição do Item',
                        width: 300
                    },
                    {
                        field: 'action',
                        title: 'Ações',
                        width: 150,
                        align: 'center',
                        formatter: formatActionItem
                    }
                ]
            ],

            // Callback executado após o carregamento dos dados
            onLoadSuccess: function() {
                // Renderiza os botões de ação (Modificar/Excluir) dentro do datagrid
                $('#dg_itens').datagrid('getPanel').find('.easyui-linkbutton').linkbutton();
            },
            rowStyler: function(index, row) {
                if (index % 2 == 1) {
                    return 'background-color:rgb(243, 243, 243);';
                }
            }
        });
    });

    // --- Funções Auxiliares ---

    function abrirDialogInclusaoItem() {
        $('#dlg-item').dialog('open').dialog('setTitle', 'Incluir Novo Item');
        $('#dlg-item').load('controlar_item.php?form_only=1', function() {
            $.parser.parse('#dlg-item');
        });
    }

    function abrirDialogModificacaoItem(cod_item) {
        $('#dlg-item').dialog('open').dialog('setTitle', 'Modificar Item');
        $('#dlg-item').load('controlar_item.php?form_only=1&cod_item=' + cod_item, function() {
            $.parser.parse('#dlg-item');
        });
    }

    function salvarItem() {
        var form = $('#fm-item');
        if (!form.form('validate')) {
            return;
        }
        $.ajax({
            url: 'controlar_item.php',
            type: 'post',
            data: form.serialize(),
            dataType: 'json',
            success: function(result) {
                if (result.success) {
                    $('#dlg-item').dialog('close');
                    $('#dg_itens').datagrid('reload');
                    $.messager.show({
                        title: 'Sucesso',
                        msg: 'Item salvo com sucesso.'
                    });
                } else {
                    $.messager.alert('Erro', result.message, 'error');
                }
            },
            error: function() {
                $.messager.alert('Erro Crítico', 'Não foi possível contatar o servidor.', 'error');
            }
        });
    }

    function excluirItem(cod_item) {
        $.messager.confirm('Confirmar Exclusão', 'Tem certeza que deseja excluir este item?', function(r) {
            if (r) {
                $.ajax({
                    url: 'excluir_item.php',
                    type: 'post',
                    data: {
                        cod_item: cod_item
                    },
                    dataType: 'json',
                    success: function(result) {
                        if (result.success) {
                            $('#dg_itens').datagrid('reload');
                            $.messager.show({
                                title: 'Sucesso',
                                msg: result.message
                            });
                        } else {
                            $.messager.alert('Erro na Exclusão', result.message, 'error');
                        }
                    },
                    error: function() {
                        $.messager.alert('Erro Crítico', 'Não foi possível contatar o servidor para exclusão.', 'error');
                    }
                });
            }
        });
    }

    function formatActionItem(value, row) {
        var btnModificar = '<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:\'icon-edit\',plain:true" onclick="abrirDialogModificacaoItem(' + row.cod_item + ')">Modificar</a>';
        var btnExcluir = '<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:\'icon-remove\',plain:true" onclick="excluirItem(' + row.cod_item + ')">Excluir</a>';
        return btnModificar + ' ' + btnExcluir;
    }
</script>

<?php require 'templates/footer.php'; ?>
