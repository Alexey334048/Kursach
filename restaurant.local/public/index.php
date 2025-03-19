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
        button {
            padding: 10px 15px;
            margin-right: 10px;
            border: none;
            border-radius: 5px;
            background-color: #3D3D3D;
            color: #FFDAB2;
            cursor: pointer;
            align-items: right;
        }
        
        button:hover {
            background-color:rgb(86, 86, 86);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        table th, table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        
        table th {
            background-color: #f8f9fa;
        }
        
        footer {
            text-align: center;
            padding: 10px;
            background-color: #333;
            color: #fff;
            position: fixed;
            bottom: 0;
            width: 100%;
        }


        .dropbtn {
    background-color: #1e1e1e;
    color: #FFDAB2;
    width: 120px;
    font-size: 10px;
    border: none;
    cursor: pointer;
}

.dropbtn:hover, .dropbtn:focus {
    background-color:rgb(86, 86, 86);
}

.dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-content {
    display: none;
    position: absolute;
    background-color: #f1f1f1;
    min-width: 160px;
    overflow: auto;
    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
    z-index: 1;
}

.dropdown-content a {
    color: black;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
}

.dropdown a:hover {background-color: #ddd;}

.show {display: block;}

.modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
            padding-top: 50px;
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            border-radius: 10px;
            width: 50%;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }

        .modal input {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .close-btn {
            float: right;
            font-size: 20px;
            cursor: pointer;
        }

        .close-btn:hover {
            color: red;
        }

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
                        <button class="edit-btn" onclick="openEditForm(<?= $dish['id'] ?>, '<?= $dish['name'] ?>', <?= $dish['weight'] ?>, <?= $dish['price'] ?>, '<?= addslashes($dish['description']) ?>', '<?= addslashes($dish['composition']) ?>', '<?= $dish['image_url'] ?>')">Редактировать</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Модальное окно редактирования -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h2>Редактирование блюда</h2>
            <form method="post">
                <input type="hidden" name="edit_id" id="edit_id">
                <label>Название: <input type="text" name="name" id="edit_name" required></label><br>
                <label>Вес (г): <input type="number" name="weight" id="edit_weight" required></label><br>
                <label>Цена (₽): <input type="number" step="0.01" name="price" id="edit_price" required></label><br>
                <label>Описание: <textarea name="description" id="edit_description"></textarea></label><br>
                <label>Состав: <textarea name="composition" id="edit_composition"></textarea></label><br>
                <label>Фото URL: <input type="text" name="image_url" id="edit_image_url"></label><br>
                <button type="submit">Сохранить</button>
                <button type="button" onclick="closeEditForm()">Отмена</button>
            </form>
        </div>
    </div>

    <script>
        function openEditForm(id, name, weight, price, description, composition, image) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_weight').value = weight;
            document.getElementById('edit_price').value = price;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_composition').value = composition;
            document.getElementById('edit_image_url').value = image;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditForm() {
            document.getElementById('editModal').style.display = 'none';
        }
    </script>

</body>
</html>
