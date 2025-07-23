<?php ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">

    <?php ?>
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Sistema de Pedidos' ?></title>

    <link rel="stylesheet" type="text/css" href="https://www.jeasyui.com/easyui/themes/material-teal/easyui.css">
    <link rel="stylesheet" type="text/css" href="https://www.jeasyui.com/easyui/themes/icon.css">
    <script type="text/javascript" src="https://www.jeasyui.com/easyui/jquery.min.js"></script>
    <script type="text/javascript" src="https://www.jeasyui.com/easyui/jquery.easyui.min.js"></script>
    <script type="text/javascript" src="https://www.jeasyui.com/easyui/locale/easyui-lang-pt.js"></script>

    <style>
        body {
            font-family: sans-serif;
        }

        .main-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 10px;
        }

        .form-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 90vh;
            padding: 20px;
        }

        .datagrid-row-alt {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
