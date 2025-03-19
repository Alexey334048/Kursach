<?php
// Подключение к базе данных
$servername = 'MySQL-8.2';
$dbname = 'Restaurant';
$username = 'root';
$password = '';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Удаление блюда (с безопасным SQL)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    $stmt = $conn->prepare("DELETE FROM dishes WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
}

// Получение списка блюд
$result = $conn->query("SELECT * FROM dishes");
$dishes = ($result) ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Меню ресторана</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; background-color: #f4f4f4; }
        table { width: 90%; margin: 20px auto; border-collapse: collapse; background: white; }
        th, td { padding: 10px; border: 1px solid #ddd; }
        th { background-color: #007bff; color: white; }
        img { width: 50px; height: 50px; border-radius: 5px; }
        button { padding: 8px 12px; border: none; cursor: pointer; border-radius: 5px; }
        .delete-btn { background-color: #dc3545; color: white; }
        .edit-btn { background-color: #28a745; color: white; }
    </style>
</head>
<body>

<h1>Меню ресторана</h1>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Фото</th>
            <th>Название</th>
            <th>Вес (г)</th>
            <th>Цена (₽)</th>
            <th>Описание</th>
            <th>Состав</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($dishes as $dish): ?>
            <tr>
                <td><?= htmlspecialchars($dish['id']) ?></td>
                <td><img src="<?= htmlspecialchars($dish['image_url']) ?>" alt="Фото"></td>
                <td><?= htmlspecialchars($dish['name']) ?></td>
                <td><?= htmlspecialchars($dish['weight']) ?> г</td>
                <td><?= htmlspecialchars($dish['price']) ?> ₽</td>
                <td><?= htmlspecialchars($dish['description']) ?></td>
                <td><?= htmlspecialchars($dish['composition']) ?></td>
                <td>
                    <form method="post" onsubmit="return confirm('Удалить блюдо?');" style="display:inline;">
                        <input type="hidden" name="delete_id" value="<?= $dish['id'] ?>">
                        <button type="submit" class="delete-btn">Удалить</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
