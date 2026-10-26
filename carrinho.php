<?php
session_start();
require_once 'db.php';

$itens_carrinho = [];
$total_pedido = 0;
$mensagem = '';

if (isset($_GET['limpar'])) {
    unset($_SESSION['carrinho']);
    header('Location: carrinho.php');
    exit;
}

if (!empty($_SESSION['carrinho'])) {
    $ids = implode(',', array_map('intval', array_keys($_SESSION['carrinho'])));
    $stmt = $pdo->query("SELECT * FROM produtos WHERE id IN ($ids)");
    $produtos = $stmt->fetchAll();

    foreach ($produtos as $prod) {
        $qtd = $_SESSION['carrinho'][$prod['id']];
        $subtotal = $prod['preco'] * $qtd;
        $total_pedido += $subtotal;
        $itens_carrinho[] = [
            'id' => $prod['id'],
            'nome' => $prod['nome'],
            'preco' => $prod['preco'],
            'qtd' => $qtd,
            'subtotal' => $subtotal
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalizar'])) {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: login.php');
        exit;
    }

    if (empty($itens_carrinho)) {
        $mensagem = "Seu carrinho está vazio.";
    } else {
        try {
            $pdo->beginTransaction();

            foreach ($itens_carrinho as $item) {
                $stmtCheck = $pdo->prepare("SELECT estoque FROM produtos WHERE id = :id FOR UPDATE");
                $stmtCheck->execute([':id' => $item['id']]);
                $estoqueAtual = $stmtCheck->fetchColumn();

                if ($estoqueAtual < $item['qtd']) {
                    throw new Exception("Estoque insuficiente para o produto: " . $item['nome']);
                }

                $stmtUpdate = $pdo->prepare("UPDATE produtos SET estoque = estoque - :qtd WHERE id = :id");
                $stmtUpdate->execute([
                    ':qtd' => $item['qtd'],
                    ':id' => $item['id']
                ]);
            }

            $pdo->commit();
            unset($_SESSION['carrinho']);
            $mensagem = "Pedido finalizado com sucesso! O estoque foi atualizado.";
            $itens_carrinho = [];
            $total_pedido = 0;
        } catch (Exception $e) {
            $pdo->rollBack();
            $mensagem = "Erro ao processar compra: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Meu Carrinho</title>
</head>
<body>
    <h2>Carrinho de Compras</h2>
    <p><a href="index.php">← Continuar Comprando</a></p>

    <?php if ($mensagem): ?>
        <p style="font-weight: bold;"><?= htmlspecialchars($mensagem) ?></p>
    <?php endif; ?>

    <?php if (!empty($itens_carrinho)): ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Preço Unitário</th>
                    <th>Qtd</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($itens_carrinho as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['nome']) ?></td>
                        <td>R$ <?= number_format($item['preco'], 2, ',', '.') ?></td>
                        <td><?= $item['qtd'] ?></td>
                        <td>R$ <?= number_format($item['subtotal'], 2, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3>Total: R$ <?= number_format($total_pedido, 2, ',', '.') ?></h3>

        <form method="POST">
            <button type="submit" name="finalizar">Finalizar Pedido</button>
            <a href="carrinho.php?limpar=1"><button type="button">Esvaziar Carrinho</button></a>
        </form>
    <?php elseif (!$mensagem): ?>
        <p>Seu carrinho está vazio no momento.</p>
    <?php endif; ?>
</body>
</html>