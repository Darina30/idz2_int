<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8" />
    <title>Фільтр товарів з AJAX та логуванням</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body>

<h2>Фільтр товарів</h2>

<form id="filterForm">
    <label for="filterType">Оберіть тип фільтра:</label>
    <select id="filterType" name="filterType" required>
        <option value="">-- Виберіть --</option>
        <option value="vendor">Виробник</option>
        <option value="category">Категорія</option>
        <option value="price">Ціна</option>
    </select>

    <div id="vendorSelect" style="display:none;">
        <label for="vendor">Виробник:</label>
        <select id="vendor" name="vendor">
            <option value="">-- Виберіть виробника --</option>
            <?php
            include 'db.php';
            $stmt = $pdo->query("SELECT ID_Vendors, v_name FROM vendors");
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $vendor) {
                echo "<option value='{$vendor['ID_Vendors']}'>{$vendor['v_name']}</option>";
            }
            ?>
        </select>
    </div>

    <div id="categorySelect" style="display:none;">
        <label for="category">Категорія:</label>
        <select id="category" name="category">
            <option value="">-- Виберіть категорію --</option>
            <?php
            $stmt = $pdo->query("SELECT ID_Category, c_name FROM category");
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $cat) {
                echo "<option value='{$cat['ID_Category']}'>{$cat['c_name']}</option>";
            }
            ?>
        </select>
    </div>

    <div id="priceInputs" style="display:none;">
        <label>Ціновий діапазон:</label>
        <input type="number" id="min_price" name="min_price" placeholder="Мінімальна ціна" min="0" />
        <input type="number" id="max_price" name="max_price" placeholder="Максимальна ціна" min="0" />
    </div>

    <button type="submit">Показати товари</button>
</form>

<div id="results"></div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const filterType = document.getElementById('filterType');
    const vendorSelect = document.getElementById('vendorSelect');
    const categorySelect = document.getElementById('categorySelect');
    const priceInputs = document.getElementById('priceInputs');
    const results = document.getElementById('results');

    // Показ/приховування полів залежно від вибору фільтра
    filterType.addEventListener('change', () => {
        vendorSelect.style.display = 'none';
        categorySelect.style.display = 'none';
        priceInputs.style.display = 'none';

        if (filterType.value === 'vendor') vendorSelect.style.display = 'block';
        else if (filterType.value === 'category') categorySelect.style.display = 'block';
        else if (filterType.value === 'price') priceInputs.style.display = 'block';
    });

    // Функція отримання браузерної інформації
    function getBrowserInfo() {
        return navigator.userAgent;
    }

    // Функція отримання координат (обернута у Promise)
    function getCoordinates() {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                resolve({ latitude: null, longitude: null });
            } else {
                navigator.geolocation.getCurrentPosition(
                    pos => resolve({
                        latitude: pos.coords.latitude,
                        longitude: pos.coords.longitude
                    }),
                    err => resolve({ latitude: null, longitude: null }),
                    {timeout: 5000}
                );
            }
        });
    }

    // Обробка форми
    document.getElementById('filterForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        const filter = filterType.value;
        if (!filter) {
            alert('Оберіть тип фільтра');
            return;
        }

        let queryParams = {};
        if (filter === 'vendor') {
            const vendor = document.getElementById('vendor').value;
            if (!vendor) {
                alert('Оберіть виробника');
                return;
            }
            queryParams = { filter: 'vendor', vendor: vendor };
        } else if (filter === 'category') {
            const category = document.getElementById('category').value;
            if (!category) {
                alert('Оберіть категорію');
                return;
            }
            queryParams = { filter: 'category', category: category };
        } else if (filter === 'price') {
            const min_price = document.getElementById('min_price').value;
            const max_price = document.getElementById('max_price').value;
            if (min_price === '' || max_price === '') {
                alert('Вкажіть обидві ціни');
                return;
            }
            if (+min_price > +max_price) {
                alert('Мінімальна ціна не може бути більшою за максимальну');
                return;
            }
            queryParams = { filter: 'price', min_price: min_price, max_price: max_price };
        }

        const browserInfo = getBrowserInfo();
        const coords = await getCoordinates();
        const requestTime = new Date().toISOString();

        // Дані для відправки на сервер (логування + фільтр)
        const postData = {
            filterType: filter,
            queryParams: JSON.stringify(queryParams),
            browserInfo: browserInfo,
            latitude: coords.latitude,
            longitude: coords.longitude,
            requestTime: requestTime
        };

        fetch('fetch.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(postData)
        })
        .then(response => response.text())
        .then(data => {
            results.innerHTML = data;
        })
        .catch(err => {
            results.innerHTML = '<p style="color:red;">Сталася помилка: ' + err + '</p>';
        });
    });
});
</script>

</body>
</html>
