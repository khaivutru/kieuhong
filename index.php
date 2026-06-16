<?php
session_start();
include "database.php";

$validation_errors = [];
$form_user = null;

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

if (isset($_POST["login"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    if ($username == "admin" && $password == "123456") {
        $_SESSION["login"] = true;
        header("Location: index.php");
    } else {
        $error = "Sai username hoac password";
    }
}

if (isset($_GET["logout"])) {
    session_destroy();
    header("Location: index.php");
}

if (isset($_POST["save"])) {
    $id = trim($_POST["id"]);
    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $address = trim($_POST["address"]);

    $form_user = [
        "id" => $id,
        "full_name" => $full_name,
        "email" => $email,
        "phone" => $phone,
        "address" => $address
    ];

    if (!preg_match('/^[\p{L}\s]+$/u', $full_name)) {
        $validation_errors[] = "Ho ten chi duoc chua chu cai va khoang trang.";
    }

    if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
        $validation_errors[] = "Email khong dung dinh dang.";
    }

    if (!preg_match('/^(0|\+84)(3|5|7|8|9)[0-9]{8}$/', $phone)) {
        $validation_errors[] = "Phone phai la so dien thoai Viet Nam hop le.";
    }

    if (empty($validation_errors)) {
        $full_name = mysqli_real_escape_string($conn, $full_name);
        $email = mysqli_real_escape_string($conn, $email);
        $phone = mysqli_real_escape_string($conn, $phone);
        $address = mysqli_real_escape_string($conn, $address);

        if ($id == "") {
            mysqli_query($conn, "INSERT INTO users(full_name, email, phone, address)
                                 VALUES('$full_name', '$email', '$phone', '$address')");
        } else {
            $id = (int)$id;
            mysqli_query($conn, "UPDATE users
                                 SET full_name='$full_name', email='$email', phone='$phone', address='$address'
                                 WHERE id=$id");
        }

        header("Location: index.php");
        exit;
    }
}

if (isset($_GET["delete"])) {
    $id = (int)$_GET["delete"];
    mysqli_query($conn, "DELETE FROM users WHERE id=$id");
    header("Location: index.php");
    exit;
}

$edit_user = null;
if (isset($_GET["edit"])) {
    $id = (int)$_GET["edit"];
    $result = mysqli_query($conn, "SELECT * FROM users WHERE id=$id");
    $edit_user = mysqli_fetch_assoc($result);
}

if ($form_user === null) {
    $form_user = $edit_user;
}

$users = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Quan ly user</title>
</head>
<body>

<?php if (!isset($_SESSION["login"])) { ?>

    <h2>Dang nhap</h2>

    <?php if (isset($error)) { ?>
        <p><?php echo $error; ?></p>
    <?php } ?>

    <form method="post">
        <p>Username: <input type="text" name="username"></p>
        <p>Password: <input type="password" name="password"></p>
        <button type="submit" name="login">Dang nhap</button>
    </form>

    <p>Username: admin</p>
    <p>Password: 123456</p>

<?php } else { ?>

    <h2>Quan ly user</h2>
    <p><a href="index.php?logout=1">Dang xuat</a></p>

    <h3><?php echo $edit_user ? "Sua user" : "Them user"; ?></h3>

    <?php if (!empty($validation_errors)) { ?>
        <ul>
            <?php foreach ($validation_errors as $message) { ?>
                <li><?php echo e($message); ?></li>
            <?php } ?>
        </ul>
    <?php } ?>

    <form method="post">
        <input type="hidden" name="id" value="<?php echo e($form_user["id"] ?? ""); ?>">

        <p>Ho ten: <input type="text" name="full_name" value="<?php echo e($form_user["full_name"] ?? ""); ?>" required pattern="[\p{L}\s]+" title="Chi nhap chu cai va khoang trang"></p>
        <p>Email: <input type="text" name="email" value="<?php echo e($form_user["email"] ?? ""); ?>" required pattern="[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}" title="Nhap email dung dinh dang, vi du: user@gmail.com"></p>
        <p>Phone: <input type="text" name="phone" value="<?php echo e($form_user["phone"] ?? ""); ?>" required pattern="(0|\+84)(3|5|7|8|9)[0-9]{8}" title="Nhap so dien thoai Viet Nam, vi du: 0387069928 hoac +84387069928"></p>
        <p>Address: <input type="text" name="address" value="<?php echo e($form_user["address"] ?? ""); ?>"></p>

        <button type="submit" name="save">
            <?php echo $edit_user ? "Cap nhat" : "Them"; ?>
        </button>

        <?php if ($edit_user) { ?>
            <a href="index.php">Huy</a>
        <?php } ?>
    </form>

    <h3>Danh sach user</h3>

    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Ho ten</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Address</th>
            <th>Thao tac</th>
        </tr>

        <?php while ($row = mysqli_fetch_assoc($users)) { ?>
            <tr>
                <td><?php echo $row["id"]; ?></td>
                <td><?php echo e($row["full_name"]); ?></td>
                <td><?php echo e($row["email"]); ?></td>
                <td><?php echo e($row["phone"]); ?></td>
                <td><?php echo e($row["address"]); ?></td>
                <td>
                    <a href="index.php?edit=<?php echo $row["id"]; ?>">Sua</a>
                    |
                    <a href="index.php?delete=<?php echo $row["id"]; ?>">Xoa</a>
                </td>
            </tr>
        <?php } ?>
    </table>

<?php } ?>

</body>
</html>
