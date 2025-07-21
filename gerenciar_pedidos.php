<?php 
require 'db.php';

// Busca todos os pedidos
$sql_pedidos = "SELECT p.num_pedido, c.nom_cliente 
FROM pedido p 
JOIN cliente c 
ON p.cod_cliente = c.cod_cliente 
ORDER BY p.num_pedido";
$pedidos = $pdo->query($sql_pedidos)->fetchAll(); // query() p consultas estaticas / fetchAll() p buscar todas as linhas

$itens_por_pedido = []; // Array vazio para armazenar os itens de forma organizada
if (!empty($pedidos)) {
    $id_pedidos = array_column($pedidos, 'num_pedido'); // Pega apenas a coluna do numero do pedido
    $placeholders = implode(',', array_fill(0, count($id_pedidos), '?')); // implode() junta os elementos de um array em uma unica string (seguro)

    $sql_itens = "SELECT ip.num_pedido, ip.num_seq_item, i.den_item, ip.qtd_solicitada, ip.pre_unitario,
               (ip.qtd_solicitada * ip.pre_unitario) AS total_item
        FROM item_pedido ip
        JOIN item i ON ip.cod_item = i.cod_item
        WHERE ip.num_pedido IN ($placeholders)
        ORDER BY ip.num_seq_item";

    $stmt_itens = $pdo->prepare($sql_itens); // Prepara a consulta no banco de dados (passo de segurança)
    $stmt_itens->execute($id_pedidos);  // execute() envia os dados que devem preencher os marcadores de posição, evita o SQL inject
    while ($item = $stmt_itens->fetch()) {
        $itens_por_pedido[$item['num_pedido']][] = $item;   // Usa o numero do pedido de cada item como "chave"
    }
}

$pageTitle = 'Gerenciar Pedidos';
require 'templates/header.php';
?>

<style>
    .pedido {
        border: 1px solid #ccc;
        padding: 10px;
        margin-bottom: 20px;
    }

    .pedido-header {
        margin-bottom: 10px;
    }

    .acoes-pedido {
        display: flex;
        gap: 10px;
        align-items: center;
        margin-top: 0px;
    }

    .acoes-pedido form {
        margin: 0;
        display: inline-block;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    th,
    td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    .item-par {
        background-color: #f9f9f9;
    }

    .item-impar {
        background-color: #fff;
    }

    .total-geral {
        text-align: right;
        font-weight: bold;
    }
</style>

<div class="main-container" style="max-width: 1200px;">
    <div class="easyui-panel" title="Gerenciamento de Pedidos" style="padding:10px;">
        <div style="margin-bottom:10px;">
            <a href="controlar_pedido.php" class="easyui-linkbutton" data-options="iconCls:'icon-add'">Incluir Pedido</a>
            <a href="gerenciar_clientes.php" class="easyui-linkbutton" data-options="iconCls:'icon-man'">Gerenciar Clientes</a>
            <a href="gerenciar_itens.php" class="easyui-linkbutton" data-options="iconCls:'icon-tip'">Gerenciar Itens</a>
        </div>

    <?php foreach ($pedidos as $pedido): ?>
        <div class="easyui-panel pedido-panel" title="Pedido: <?= htmlspecialchars($pedido['num_pedido']) 
                ?> | Cliente: <?= htmlspecialchars($pedido['nom_cliente']) ?>" data-options="collapsible:true" style="margin-bottom: 10px;">

                <div class="acoes-pedido">
                    <a href="controlar_item_pedido.php?num_pedido=<?= $pedido['num_pedido'] ?>" class="easyui-linkbutton" data-options="iconCls:'icon-add', plain:true">Incluir Item</a>
                    <a href="controlar_pedido.php?num_pedido=<?= $pedido['num_pedido'] ?>" class="easyui-linkbutton" data-options="iconCls:'icon-edit', plain:true">Modificar Pedido</a>
                    
                    <form method="POST" action="excluir_pedido.php" onsubmit="return confirm('Tem certeza que deseja excluir este pedido e todos os seus itens?');">
                        <input type="hidden" name="num_pedido" value="<?= $pedido['num_pedido'] ?>">
                        <button type="submit" class="easyui-linkbutton" data-options="iconCls:'icon-remove', plain:true">Excluir Pedido</button>
                    </form>
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
                    $par = true;
                    foreach ($itens_do_pedido_atual as $item):
                        $soma_total_pedido += $item['total_item'];
                    ?>
                        <tr class="<?= $par ? 'item-par' : 'item-impar' ?>">
                            <td><?= htmlspecialchars($item['den_item']) ?></td>
                            <td><?= number_format($item['qtd_solicitada'], 0, ',', '.') ?></td>
                            <td>R$ <?= number_format($item['pre_unitario'], 2, ',', '.') ?></td>
                            <td>R$ <?= number_format($item['total_item'], 2, ',', '.') ?></td>
                            <td class="acoes-pedido">
                                <a href="controlar_item_pedido.php?num_pedido=<?= $pedido['num_pedido'] ?>&num_seq_item=<?= $item['num_seq_item'] ?>" class="easyui-linkbutton" data-options="iconCls:'icon-edit', plain:true">Modificar Item</a>

                                <form method="POST" action="excluir_item_pedido.php" onsubmit="return confirm('Tem certeza que deseja excluir este item?');">
                                    <input type="hidden" name="num_pedido" value="<?= $pedido['num_pedido'] ?>">
                                    <input type="hidden" name="num_seq_item" value="<?= $item['num_seq_item'] ?>">
                                    <button type="submit" class="easyui-linkbutton" data-options="iconCls:'icon-remove', plain:true">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php
                        $par = !$par;
                    endforeach;
                    ?>
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

<?php require 'templates/footer.php'; ?>