<?php
session_start();
$pageTitle = 'Gerenciar Clientes';
require 'templates/header.php';
?>

<div class="main-container">

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="easyui-panel" title="Sucesso" style="padding:10px;margin-bottom:10px;border-color:green;color:green;">
            <?= htmlspecialchars($_SESSION['success_message']);
            unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="easyui-panel" title="Erro" style="padding:10px;margin-bottom:10px;border-color:red;color:red;">
            <?= htmlspecialchars($_SESSION['error_message']);
            unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <div class="easyui-panel" title="Gerenciamento de Clientes" style="padding:10px;">
        <div style="margin-bottom:10px;">
            <a href="javascript:void(0)" onclick="abrirDialogInclusao()" class="easyui-linkbutton" data-options="iconCls:'icon-add'">Incluir Cliente</a>
            <a href="gerenciar_pedidos.php" class="easyui-linkbutton" data-options="iconCls:'icon-undo'">Voltar para Pedidos</a>
        </div>

        <table id="dg_clientes" style="width:100%; height:400px"></table>
    </div>
</div>

<div id="dlg" class="easyui-dialog" style="width:550px; padding: 10px 20px;"
    closed="true" modal="true" buttons="#dlg-buttons">
</div>
<div id="dlg-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="salvarCliente()" style="width:90px">Salvar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:$('#dlg').dialog('close')" style="width:90px">Cancelar</a>
</div>

<script>
    $(document).ready(function() {
        console.log('entrou');

        $('#dg_clientes').datagrid({
            url: 'listar_dados_json.php',
            queryParams: {
                tipo: 'clientes'
            },
            method: 'post',
            pagination: true,
            fitColumns: true,
            singleSelect: true,
            rownumbers: true,
            pageSize: 10,
            pageList: [10, 20, 50],

            columns: [
                [{
                        field: 'cod_cliente',
                        title: 'Código',
                        width: 80,
                        align: 'center'
                    },
                    {
                        field: 'nom_cliente',
                        title: 'Nome do Cliente',
                        width: 300
                    },
                    {
                        field: 'action',
                        title: 'Ações',
                        width: 150,
                        align: 'center',
                        formatter: formatActionCliente
                    }
                ]
            ],

            // Callback executado após o carregamento dos dados
            onLoadSuccess: function() {
                // Renderiza os botões de ação (Modificar/Excluir) dentro do datagrid
                $('#dg_clientes').datagrid('getPanel').find('.easyui-linkbutton').linkbutton();
            },
            rowStyler: function(index, row) {
                if (index % 2 == 1) {
                    return 'background-color:rgb(243, 243, 243);';
                }
            }
        });
    });

    function abrirDialogInclusao() {
        $('#dlg').dialog('open').dialog('setTitle', 'Incluir Novo Cliente');
        $('#dlg').load('controlar_cliente.php?form_only=1', function() {
            $.parser.parse('#dlg');
        });
    }

    function abrirDialogModificacao(cod_cliente) {
        $('#dlg').dialog('open').dialog('setTitle', 'Modificar Cliente');
        $('#dlg').load('controlar_cliente.php?form_only=1&cod_cliente=' + cod_cliente, function() {
            $.parser.parse('#dlg');
        });
    }

    function salvarCliente() {
        var form = $('#fm-cliente');
        if (!form.form('validate')) {
            return;
        }

        $.ajax({
            url: 'controlar_cliente.php',
            type: 'post',
            data: form.serialize(),
            dataType: 'json',
            success: function(result) {
                if (result.success) {
                    $('#dlg').dialog('close');
                    $('#dg_clientes').datagrid('reload');
                    $.messager.show({
                        title: 'Sucesso',
                        msg: 'Cliente salvo com sucesso.'
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

    function excluirCliente(cod_cliente) {
        $.messager.confirm('Confirmar Exclusão', 'Tem certeza que deseja excluir este cliente?', function(r) {
            if (r) {
                $.ajax({
                    url: 'excluir_cliente.php',
                    type: 'post',
                    data: {
                        cod_cliente: cod_cliente
                    },
                    dataType: 'json',
                    success: function(result) {
                        if (result.success) {
                            $('#dg_clientes').datagrid('reload');
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

    function formatActionCliente(value, row) {
        var btnModificar = '<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:\'icon-edit\',plain:true" onclick="abrirDialogModificacao(' + row.cod_cliente + ')">Modificar</a>';
        var btnExcluir = '<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:\'icon-remove\',plain:true" onclick="excluirCliente(' + row.cod_cliente + ')">Excluir</a>';
        return btnModificar + ' ' + btnExcluir;
    }
</script>

<?php require 'templates/footer.php'; ?>
