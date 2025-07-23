<?php
$pageTitle = 'Gerenciar Pedidos';
require 'templates/header.php';
?>

<script type="text/javascript" src="https://www.jeasyui.com/easyui/datagrid-detailview.js"></script>

<div class="main-container" style="max-width: 1200px; margin: auto;">
    <div class="easyui-panel" title="Gerenciamento de Pedidos" style="padding:10px;">
        
        <div style="margin-bottom:10px;">
            <a href="javascript:void(0)" onclick="abrirDialogInclusaoPedido()" class="easyui-linkbutton" data-options="iconCls:'icon-add'">Incluir Pedido</a>
            <a href="gerenciar_clientes.php" class="easyui-linkbutton" data-options="iconCls:'icon-man'">Gerenciar Clientes</a>
            <a href="gerenciar_itens.php" class="easyui-linkbutton" data-options="iconCls:'icon-tip'">Gerenciar Itens</a>
        </div>

        <table id="dg_pedidos" style="width:100%; height:500px"></table>

    </div>
</div>

<div id="dlg-pedido" class="easyui-dialog" style="width:550px; padding: 10px 20px;" closed="true" modal="true" buttons="#dlg-pedido-buttons"></div>
<div id="dlg-pedido-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="salvarPedido()" style="width:90px">Salvar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:$('#dlg-pedido').dialog('close')" style="width:90px">Cancelar</a>
</div>

<div id="dlg-item-pedido" class="easyui-dialog" style="width:550px; padding: 10px 20px;" closed="true" modal="true" buttons="#dlg-item-pedido-buttons"></div>
<div id="dlg-item-pedido-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="salvarItemPedido()" style="width:90px">Salvar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:$('#dlg-item-pedido').dialog('close')" style="width:90px">Cancelar</a>
</div>


<script type="text/javascript">
    
    $(document).ready(function() {
        $('#dg_pedidos').datagrid({
            url: 'listar_pedidos_json.php',
            method: 'post',
            pagination: true,
            fitColumns: true,
            singleSelect: true,
            rownumbers: true,
            pageSize: 10,
            pageList: [10, 30, 50],
            
            // Definição das Colunas
            columns: [[
                { field: 'num_pedido', title: 'Nº Pedido', width: 80, align: 'center' },
                { field: 'nom_cliente', title: 'Cliente', width: 250 },
                { field: 'vlr_total', title: 'Valor Total', width: 120, align: 'right', formatter: formatCurrency },
                { field: 'action', title: 'Ações', width: 150, align: 'center', formatter: formatActionPedido }
            ]],

            // Configuração da Detail View para mostrar os itens
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px"><table class="ddv"></table></div>';
            },

            // Lógica para quando uma linha de pedido é expandida
            onExpandRow: function(index, rowPedido) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');
                ddv.datagrid({
                    url: 'listar_itens_pedido_json.php?num_pedido=' + rowPedido.num_pedido,
                    // Toolbar dinâmica para adicionar itens ao pedido específico
                    toolbar: [{
                        text: 'Adicionar Item',
                        iconCls: 'icon-add',
                        plain: true,
                        handler: function() {
                            abrirDialogInclusaoItem(rowPedido.num_pedido);
                        }
                    }],
                    fitColumns: true,
                    singleSelect: true,
                    rownumbers: true,
                    loadMsg: '',
                    height: 'auto',
                    columns: [[
                        { field: 'den_item', title: 'Item', width: 200 },
                        { field: 'qtd_solicitada', title: 'Qtd.', width: 50, align: 'center' },
                        { field: 'pre_unitario', title: 'Preço Unit.', width: 100, align: 'right', formatter: formatCurrency },
                        { field: 'total_item', title: 'Total Item', width: 100, align: 'right', formatter: formatCurrency },
                        { field: 'action', title: 'Ações', width: 120, align: 'center', formatter: formatActionItemPedido }
                    ]],
                    onResize: function() {
                        $('#dg_pedidos').datagrid('fixDetailRowHeight', index);
                    },
                    onLoadSuccess: function() {
                        setTimeout(function() {
                            $('#dg_pedidos').datagrid('fixDetailRowHeight', index);
                            // Renderiza os botões de ação (modificar/excluir) dos itens
                            ddv.datagrid('getPanel').find('.easyui-linkbutton').linkbutton();
                        }, 0);
                    }
                });
                $('#dg_pedidos').datagrid('fixDetailRowHeight', index);
            },

            // Callbacks de Sucesso e Erro do Datagrid Principal
            onLoadSuccess: function() {
                // Renderiza os botões de ação dos pedidos
                $('#dg_pedidos').datagrid('getPanel').find('.easyui-linkbutton').linkbutton();
            },
            onLoadError: function(jqXHR) {
                try {
                    var response = JSON.parse(jqXHR.responseText);
                    if (response && response.error) {
                        $.messager.alert('Erro no Servidor', response.error, 'error');
                    } else {
                        $.messager.alert('Erro ao Carregar Dados', 'Falha na comunicação com o servidor.', 'error');
                    }
                } catch (e) {
                    $.messager.alert('Erro Crítico', 'Não foi possível interpretar a resposta do servidor.', 'error');
                }
            }
        });
    });

    // --- Funções Formatadoras ---
    function formatActionPedido(value, row) {
        var btnModificar = `<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit',plain:true" onclick="abrirDialogModificacaoPedido(${row.num_pedido})">Modificar</a>`;
        var btnExcluir = `<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="excluirPedido(${row.num_pedido})">Excluir</a>`;
        return btnModificar + ' ' + btnExcluir;
    }

    function formatActionItemPedido(value, row) {
        var btnModificar = `<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit',plain:true" onclick="abrirDialogModificacaoItem(${row.num_pedido}, ${row.num_seq_item})">Modificar</a>`;
        var btnExcluir = `<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="excluirItemDoPedido(${row.num_pedido}, ${row.num_seq_item})">Excluir</a>`;
        return btnModificar + ' ' + btnExcluir;
    }

    function formatCurrency(value) {
        if (value === null || value === undefined) return '';
        var val = parseFloat(value);
        if (isNaN(val)) return '';
        return val.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }

    // --- Funções de Diálogo e CRUD para Pedidos ---
    function abrirDialogInclusaoPedido() {
        $('#dlg-pedido').dialog('open').dialog('setTitle', 'Incluir Novo Pedido');
        $('#dlg-pedido').load('controlar_pedido.php?form_only=1', () => $.parser.parse('#dlg-pedido'));
    }

    function abrirDialogModificacaoPedido(num_pedido) {
        $('#dlg-pedido').dialog('open').dialog('setTitle', 'Modificar Pedido');
        $('#dlg-pedido').load(`controlar_pedido.php?form_only=1&num_pedido=${num_pedido}`, () => $.parser.parse('#dlg-pedido'));
    }

    function salvarPedido() {
        $('#fm-pedido').form('submit', {
            url: 'controlar_pedido.php',
            onSubmit: function() { return $(this).form('validate'); },
            success: function(result) {
                try {
                    var res = JSON.parse(result);
                    if (res.success) {
                        $('#dlg-pedido').dialog('close');
                        $('#dg_pedidos').datagrid('reload');
                        $.messager.show({ title: 'Sucesso', msg: 'Pedido salvo com sucesso.' });
                    } else {
                        $.messager.alert('Erro', res.message || 'Ocorreu um erro ao salvar.', 'error');
                    }
                } catch (e) {
                    $.messager.alert('Erro de Resposta', 'A resposta do servidor não pôde ser processada.', 'error');
                }
            }
        });
    }

    function excluirPedido(num_pedido) {
        $.messager.confirm('Confirmar Exclusão', 'Tem certeza que deseja excluir este pedido e todos os seus itens?', function(r) {
            if (r) {
                $.post('excluir_pedido.php', { num_pedido: num_pedido }, function(result) {
                    if (result.success) {
                        $('#dg_pedidos').datagrid('reload');
                        $.messager.show({ title: 'Sucesso', msg: 'Pedido excluído.' });
                    } else {
                        $.messager.alert('Erro', result.message, 'error');
                    }
                }, 'json');
            }
        });
    }

    // --- Funções de Diálogo e CRUD para Itens de Pedido ---
    function abrirDialogInclusaoItem(num_pedido) {
        if (!num_pedido) {
            $.messager.alert('Erro', 'Número do pedido inválido.', 'error');
            return;
        }
        $('#dlg-item-pedido').dialog('open').dialog('setTitle', 'Adicionar Novo Item ao Pedido');
        $('#dlg-item-pedido').load(`controlar_item_pedido.php?form_only=1&num_pedido=${num_pedido}`, () => {
            $.parser.parse('#dlg-item-pedido');
        });
    }

    function abrirDialogModificacaoItem(num_pedido, num_seq_item) {
        $('#dlg-item-pedido').dialog('open').dialog('setTitle', 'Modificar Item do Pedido');
        $('#dlg-item-pedido').load(`controlar_item_pedido.php?form_only=1&num_pedido=${num_pedido}&num_seq_item=${num_seq_item}`, () => {
            $.parser.parse('#dlg-item-pedido');
        });
    }

    function salvarItemPedido() {
        $('#fm-item-pedido').form('submit', {
            url: 'controlar_item_pedido.php',
            onSubmit: function() { return $(this).form('validate'); },
            success: function(result) {
                try {
                    var res = JSON.parse(result);
                    if (res.success) {
                        $('#dlg-item-pedido').dialog('close');
                        $('#dg_pedidos').datagrid('reload');
                    } else {
                        $.messager.alert('Erro', res.message, 'error');
                    }
                } catch (e) {
                    $.messager.alert('Erro de Resposta', 'A resposta do servidor não pôde ser processada.', 'error');
                }
            }
        });
    }

    function excluirItemDoPedido(num_pedido, num_seq_item) {
        $.messager.confirm('Confirmar Exclusão', 'Tem certeza que deseja excluir este item do pedido?', function(r) {
            if (r) {
                $.post('excluir_item_pedido.php', { num_pedido: num_pedido, num_seq_item: num_seq_item }, function(result) {
                    if (result.success) {
                        $('#dg_pedidos').datagrid('reload');
                    } else {
                        $.messager.alert('Erro', result.message, 'error');
                    }
                }, 'json');
            }
        });
    }
</script>

<?php
require 'templates/footer.php';
?>
