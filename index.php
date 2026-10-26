<?php
session_start();
require_once 'db.php';

$stmt = $pdo->query("SELECT * FROM produtos ORDER BY id ASC");
$produtos = $stmt->fetchAll();

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_id'])) {
    $id = (int)$_POST['adicionar_id'];
    
    $stmtEstoque = $pdo->prepare("SELECT estoque FROM produtos WHERE id = :id");
    $stmtEstoque->execute([':id' => $id]);
    $item = $stmtEstoque->fetch();

    $qtd_atual_carrinho = $_SESSION['carrinho'][$id] ?? 0;

    if ($item && $item['estoque'] > $qtd_atual_carrinho) {
        $_SESSION['carrinho'][$id] = $qtd_atual_carrinho + 1;
        header('Location: index.php');
        exit;
    } else {
        $msg_alerta = "Quantidade indisponível em estoque!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Vitrine - E-Commerce</title>
</head>
<body>
    <header>
        <?php if (isset($_SESSION['usuario_id'])): ?>
            <p>Olá, <strong><?= htmlspecialchars($_SESSION['usuario_nome']) ?></strong>! | <a href="logout.php">Sair</a></p>
        <?php else: ?>
            <p><a href="login.php">Entrar com sua conta</a></p>
        <?php endif; ?>
        <p><a href="carrinho.php">Ver Carrinho (<?= array_sum($_SESSION['carrinho']) ?> itens)</a></p>
    </header>

    <hr>
    <h2>Produtos Disponíveis</h2>

    <?php if (!empty($msg_alerta)): ?>
        <p style="color:red;"><?= htmlspecialchars($msg_alerta) ?></p>
    <?php endif; ?>

    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>Produto</th>
                <th>Preço</th>
                <th>Estoque</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produtos as $prod): ?>
                <tr>
                    <td><?= htmlspecialchars($prod['nome']) ?></td>
                    <td>R$ <?= number_format($prod['preco'], 2, ',', '.') ?></td>
                    <td><?= $prod['estoque'] ?></td>
                    <td>
                        <?php if ($prod['estoque'] > 0): ?>
                            <form method="POST">
                                <input type="hidden" name="adicionar_id" value="<?= $prod['id'] ?>">
                                <button type="submit">Adicionar ao Carrinho</button>
                            </form>
                        <?php else: ?>
                            <span style="color:gray;">Esgotado</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>