/**
 * Abre um dialog genérico do EasyUI de forma padronizada.
 *
 * @param {object} options - As opções de configuração para o dialog.
 * @param {string} options.url - A URL do formulário a ser carregado dentro do dialog.
 * @param {string} options.title - O título que aparecerá na janela do dialog.
 * @param {function} options.onSave - A função de callback a ser executada quando o botão 'Salvar' for clicado.
 * @param {number} [options.width=550] - A largura do dialog em pixels.
 * @param {number} [options.height=350] - A altura do dialog em pixels.
 */
function abrirDialog(options) {
    // Define valores padrão caso não sejam fornecidos
    const settings = $.extend({
        width: 550,
        height: 350,
        onSave: function() {
            console.error('Função onSave não foi definida para este dialog.');
        }
    }, options);

    // Seleciona o container genérico do dialog
    let $dialog = $('#generic-dialog-container');

    // Abre o dialog com as configurações passadas
    $dialog.dialog({
        title: settings.title,
        href: settings.url, // Carrega o conteúdo da URL
        width: settings.width,
        height: settings.height,
        modal: true,
        closed: false,
        buttons: [{
            text: 'Salvar',
            iconCls: 'icon-save',
            handler: function() {
                // Ao clicar em salvar, chama a função de callback que foi passada
                settings.onSave($dialog);
            }
        }, {
            text: 'Cancelar',
            iconCls: 'icon-cancel',
            handler: function() {
                $dialog.dialog('close');
            }
        }],
        onClose: function() {
            // Limpa o conteúdo do dialog ao fechar para evitar dados residuais
            $(this).dialog('clear');
        }
    });
}

/**
 * Função padrão para submeter um formulário que está dentro de um dialog.
 * Esta função pode ser passada como o callback 'onSave'.
 *
 * @param {object} $dialog - A referência do objeto jQuery do dialog.
 */
function submeterFormularioDialog($dialog) {
    let form = $dialog.find('form');
    if (form.length === 0) {
        console.error('Nenhum formulário encontrado dentro do dialog.');
        return;
    }

    form.form('submit', {
        onSubmit: function() {
            return $(this).form('validate');
        },
        success: function(result) {
            try {
                let res = JSON.parse(result);
                if (res.success) {
                    $dialog.dialog('close');
                    // A forma mais simples de atualizar a tela é recarregá-la
                    location.reload();
                } else {
                    $.messager.alert('Erro', res.message || 'Ocorreu um erro desconhecido.', 'error');
                }
            } catch (e) {
                $.messager.alert('Erro de Resposta', 'A resposta do servidor não é válida. Detalhes: ' + result, 'error');
            }
        }
    });
}
