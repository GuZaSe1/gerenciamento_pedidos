<?php
session_start();
$pageTitle = 'Gerenciar Clientes';
require 'templates/header.php';
?>

<div class="main-container">

    <!-- Mensagens de sucesso/erro -->
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
            <a href="javascript:void(0)" onclick="abrirDialogInclusao()" class="easyui-linkbutton" data-options="iconCls:'icon-add'">Incluir Cliente</a>
            <a href="gerenciar_pedidos.php" class="easyui-linkbutton" data-options="iconCls:'icon-undo'">Voltar para Pedidos</a>
        </div>

        <table id="dg_clientes" class="easyui-datagrid" style="width:100%; height:400px"
            data-options="url:'listar_clientes_json.php',
                             method:'post',
                             pagination:true,
                             fitColumns:true,
                             singleSelect:true,
                             pageSize:10,
                             pageList:[10,20,50],
                             onLoadSuccess: function() { 
                                 // Garante que os botões dentro da tabela sejam renderizados corretamente pelo EasyUI
                                 $('.easyui-linkbutton').linkbutton(); 
                             }">
            <thead>
                <tr>
                    <th data-options="field:'cod_cliente',width:80">Código</th>
                    <th data-options="field:'nom_cliente',width:300">Nome do Cliente</th>
                    <th data-options="field:'action',width:150,align:'center',formatter:formatActionCliente">Ações</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- O DIV do Dialog agora não tem mais iframe. Ele será o container direto do formulário. -->
<!-- Adicionamos a propriedade 'buttons' para criar os botões Salvar e Cancelar no rodapé do dialog. -->
<div id="dlg" class="easyui-dialog" style="width:550px; padding: 10px 20px;"
        closed="true" modal="true" buttons="#dlg-buttons">
    <!-- O formulário será carregado aqui via AJAX -->
</div>
<!-- Botões do Dialog -->
<div id="dlg-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="salvarCliente()" style="width:90px">Salvar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:$('#dlg').dialog('close')" style="width:90px">Cancelar</a>
</div>

<script>
    // Função para carregar o formulário de inclusão
    function abrirDialogInclusao() {
        $('#dlg').dialog('open').dialog('setTitle', 'Incluir Novo Cliente');
        // Usamos .load() do jQuery para buscar o HTML de 'controlar_cliente.php' 
        // e colocá-lo dentro do nosso dialog. O parâmetro '?form_only=1' é um truque
        // para dizer ao nosso script PHP que queremos apenas o formulário, e não a página inteira.
        $('#dlg').load('controlar_cliente.php?form_only=1', function() {
            // Após carregar, o EasyUI precisa ser instruído a renderizar os novos componentes.
            $.parser.parse('#dlg');
        });
    }

    // Função para carregar o formulário de modificação
    function abrirDialogModificacao(cod_cliente) {
        $('#dlg').dialog('open').dialog('setTitle', 'Modificar Cliente');
        // Carrega o formulário passando o código do cliente e o parâmetro 'form_only'
        $('#dlg').load('controlar_cliente.php?form_only=1&cod_cliente=' + cod_cliente, function() {
            $.parser.parse('#dlg');
        });
    }

    // A NOVA FUNÇÃO DE SALVAR COM AJAX
    function salvarCliente() {
        // Pega a referência do formulário que está dentro do dialog
        var form = $('#fm-cliente');
        
        // Valida o formulário do EasyUI. Se não for válido, para a execução.
        if (!form.form('validate')) {
            return;
        }

        // Envia os dados do formulário via AJAX para o mesmo script de controle
        $.ajax({
            url: 'controlar_cliente.php',
            type: 'post',
            // .serialize() transforma os campos do formulário em uma string de consulta (ex: nome=teste&id=1)
            data: form.serialize(),
            dataType: 'json', // Esperamos uma resposta em JSON do servidor
            success: function(result) {
                // Esta função é executada se a requisição AJAX for bem-sucedida
                if (result.success) {
                    $('#dlg').dialog('close');      // Fecha o dialog
                    $('#dg_clientes').datagrid('reload'); // Recarrega a tabela
                    $.messager.show({ title: 'Sucesso', msg: 'Cliente salvo com sucesso.' });
                } else {
                    // Mostra a mensagem de erro retornada pelo PHP
                    $.messager.alert('Erro', result.message, 'error');
                }
            },
            error: function() {
                // Esta função é executada se houver um erro de conexão ou no servidor
                $.messager.alert('Erro Crítico', 'Não foi possível contatar o servidor.', 'error');
            }
        });
    }

    // Função para exclusão (pode continuar a mesma, pois já recarrega a página inteira)
    function excluirRegistro(cod_cliente) {
        $.messager.confirm('Confirmar Exclusão', 'Tem certeza que deseja excluir este cliente?', function(r) {
            if (r) {
                // Usamos POST para mais segurança
                $.post('excluir_cliente.php', {cod_cliente: cod_cliente})
                .done(function(){
                    // Após a exclusão, apenas recarregamos a tabela.
                    $('#dg_clientes').datagrid('reload');
                    // E recarregamos a página para exibir as mensagens de sessão (sucesso/erro)
                    // que o script de exclusão gera.
                    location.reload(); 
                })
                .fail(function(){
                    $.messager.alert('Erro', 'Ocorreu um erro ao tentar excluir o cliente.', 'error');
                });
            }
        });
    }

    // Função para formatar os botões (sem alterações)
    function formatActionCliente(value, row) {
        var btnModificar = '<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:\'icon-edit\',plain:true" onclick="abrirDialogModificacao(' + row.cod_cliente + ')">Modificar</a>';
        var btnExcluir = '<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:\'icon-remove\',plain:true" onclick="excluirRegistro(' + row.cod_cliente + ')">Excluir</a>';
        return btnModificar + ' ' + btnExcluir;
    }
</script>

<?php require 'templates/footer.php'; ?>
