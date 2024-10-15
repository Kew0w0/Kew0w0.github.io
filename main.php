<?php
// เริ่มต้น session
session_start();

// ข้อมูลการเชื่อมต่อฐานข้อมูล MySQL
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "food_list";

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ค่าเริ่มต้น
$selected_food_type = '';
$selected_main_ingredients = '';
$food_name = '';
$food_type = '';
$main_ingredients = '';
$result_display = false; // Flag to show/hide the result
$not_found_message = ''; // Variable to store the not found message

// ตรวจสอบว่ามีการส่งข้อมูลแบบ POST หรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // นำค่าที่รับมาจากฟอร์ม (ใช้ isset เพื่อตรวจสอบการมีอยู่ของคีย์)
    $selected_food_type = isset($_POST["food_type"]) ? $_POST["food_type"] : '';
    $selected_main_ingredients = isset($_POST["main_ingredients"]) ? $_POST["main_ingredients"] : '';

    // สร้างเงื่อนไข WHERE ใน SQL query
    $where_conditions = array();
    if (!empty($selected_food_type)) {
        $where_conditions[] = "food_type = '$selected_food_type'";
    }
    if (!empty($selected_main_ingredients)) {
        $where_conditions[] = "main_ingredients LIKE '%$selected_main_ingredients%'";
    }

    // สร้าง SQL query
    $sql = "SELECT * FROM food_list";
    if (!empty($where_conditions)) {
        $sql .= " WHERE " . implode(" AND ", $where_conditions);
    }
    $sql .= " ORDER BY RAND() LIMIT 1";

    // สุ่มอาหาร
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $food_name = $row["food_name"];
        $food_type = $row["food_type"];
        $main_ingredients = $row["main_ingredients"];
        
        // เก็บผลสุ่มใน session
        $_SESSION['food_name'] = $food_name;
        $_SESSION['food_type'] = $food_type;
        $_SESSION['main_ingredients'] = $main_ingredients;
        $result_display = true; // Set flag to true to display the result
    } else {
        // หากไม่พบผลลัพธ์
        $not_found_message = "ไม่พบผลลัพธ์"; // Set the not found message
        $result_display = true; // Still set to true to display the message
    }
}

// แยกส่วนผสมหลักที่ไม่ซ้ำกันจากฐานข้อมูล
$sql_main_ingredients = "SELECT DISTINCT main_ingredients FROM food_list";
$result_main_ingredients = $conn->query($sql_main_ingredients);

$unique_ingredients = [];

// ตรวจสอบผลลัพธ์
if ($result_main_ingredients->num_rows > 0) {
    while ($row = $result_main_ingredients->fetch_assoc()) {
        $parts = array_map('trim', explode(',', $row["main_ingredients"]));
        foreach ($parts as $part) {
            if (!in_array($part, $unique_ingredients)) {
                $unique_ingredients[] = $part;
            }
        }
    }
}

// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();

// ตรวจสอบค่าที่อยู่ใน session
if (isset($_SESSION['food_name'])) {
    $food_name = $_SESSION['food_name'];
    $food_type = $_SESSION['food_type'];
    $main_ingredients = $_SESSION['main_ingredients'];
    $result_display = true; // แสดงผลจาก session
}

// ตรวจสอบข้อความ "ไม่พบผลลัพธ์"
if (!empty($not_found_message)) {
    $food_name = $not_found_message; // ใช้ข้อความนี้ในการแสดงผล
    $food_type = '';
    $main_ingredients = '';
}

// ตรวจสอบไฟล์ภาพในโฟลเดอร์ food_img
$image_path = "food_img/" . strtolower(str_replace(' ', '_', $food_name)) . ".jpg"; // เปลี่ยนชื่อให้ตรงกับชื่อไฟล์
$image_exists = file_exists($image_path); //  ตรวจสอบ ว่าไฟล์ภาพมีอยู่หรือไม่
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lueak Hai</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <style>
    /* CSS Styles */
    body {
        font-family: 'Arial', sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f4f4f4;
    }
    .pacifico-font {
        font-family: 'Pacifico', cursive;
    }
    .container {
        max-width: 600px;
        margin: 20px auto;
        padding: 20px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    h1{
        text-align: center;
        color: #333;
    }
    form {
        margin-bottom: 20px;
        text-align: center;
    }
    form label {
        display: block;
        margin-bottom: 5px;
        color: #555;
    }
    form select {
        width: 100%;
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 16px;
    }
    form input[type="submit"] {
        width: 100%;
        padding: 10px;
        background-color: #007bff;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        transition: background-color 0.3s;
    }
    form input[type="submit"]:hover {
        background-color: #0056b3;
    }
    .modal {
        display: none; /* Hidden by default */
        position: fixed; /* Stay in place */
        z-index: 1; /* Sit on top */
        left: 0;
        top: 0;
        width: 100%; /* Full width */
        height: 100%; /* Full height */
        overflow: auto; /* Enable scroll if needed */
        background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
        padding-top: 60px;
        animation: fadeIn 0.5s;
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    .modal-content {
        background-color: #fefefe;
        margin: 5% auto; /* 15% from the top and centered */
        padding: 20px;
        border: 1px solid #888;
        width: 40%; /* Could be more or less, depending on screen size */
        border-radius: 8px;
        animation: zoomIn 0.5s;
    }
    @keyframes zoomIn {
        from { transform: scale(0.5); }
        to { transform: scale(1); }
    }
    .randomize-button {
        margin-top: 20px;
        padding: 10px;
        background-color: #28a745;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s;
        width: 100%;
        font-size: 16px;
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }
    @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
    }
</style>

    <script>
        // ฟังก์ชันเพื่อแสดง modal ด้วยอนิเมชั่น
        function openModal() {
            document.getElementById("resultModal").style.display = "block";
            document.getElementById("resultModal").style.animation = "fadeIn 0.5s";
        }
        
        // ฟังก์ชันเพื่อปิด modal ด้วยอนิเมชั่น
        function closeModal() {
            document.getElementById("resultModal").style.animation = "fadeOut 0.5s";
            setTimeout(function() {
                document.getElementById("resultModal").style.display = "none";
            }, 500);
        }
        
        // ฟังก์ชันเพื่อแสดง modal เมื่อโหลดหน้า
        window.onload = function() {
            <?php if ($result_display): ?>
                openModal();
            <?php endif; ?>
        };
    </script>
</head>
<body>
    <div class="container">
    <h1 class="pacifico-font">Lueak Hai</h1>
        <form method="post">
            <label for="food_type">ประเภท:</label>
            <select name="food_type" id="food_type" class="form-control">
                <option value="">เลือกประเภท</option>
                <?php
                // สร้างการเชื่อมต่อ
                $conn = new mysqli($servername, $username, $password, $dbname);
                $sql_food_types = "SELECT DISTINCT food_type FROM food_list";
                $result_food_types = $conn->query($sql_food_types);
                if ($result_food_types->num_rows > 0) {
                    while ($row = $result_food_types->fetch_assoc()) {
                        $selected = ($row["food_type"] == $selected_food_type) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($row["food_type"]) . "' $selected>" . htmlspecialchars($row["food_type"]) . "</option>";
                    }
                }
                // ปิดการเชื่อมต่อ
                $conn->close();
                ?>
            </select>
            <br>
            <label for="main_ingredients">ส่วนผสมหลัก:</label>
            <select name="main_ingredients" id="main_ingredients" class="form-control">
                <option value="">เลือกส่วนผสมหลัก</option>
                <?php
                foreach ($unique_ingredients as $ingredient) {
                    $selected = ($ingredient == $selected_main_ingredients) ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($ingredient) . "' $selected>" . htmlspecialchars($ingredient) . "</option>";
                }
                ?>
            </select>
            <br>
            <br>
            <input type="submit" value="สุ่ม" class="btn btn-primary">
        </form>
        
        <!-- Modal -->
        <div id="resultModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h2>ผลลัพธ์การสุ่มอาหาร</h2>
                <p>ชื่ออาหาร: <strong><?php echo htmlspecialchars($food_name); ?></strong></p>
                <p>ประเภท: <strong><?php echo htmlspecialchars($food_type); ?></strong></p>
                <p>ส่วนผสมหลัก: <strong><?php echo htmlspecialchars($main_ingredients); ?></strong></p>
                
                <!-- แสดงรูปภาพ -->
                <?php if ($result_display && $image_exists): ?>
                    <img src="<?php echo htmlspecialchars($image_path); ?>" alt="<?php echo htmlspecialchars($food_name); ?>" class="food-image">
                <?php else: ?>
                    <p>ไม่มีภาพสำหรับอาหารนี้</p>
                <?php endif; ?>

                <button class="randomize-button" onclick="location.reload();">สุ่มใหม่</button>
            </div>
        </div>
    </div>
</body>
</html>