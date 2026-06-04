<?php
session_start();
require_once "database.php";

$loginError = "";
$message = "";

function cleanInput($value)
{
    return trim($value ?? "");
}

function showText($value)
{
    return htmlspecialchars($value ?? "", ENT_QUOTES, "UTF-8");
}

if (isset($_POST["login"])) {
    $username = cleanInput($_POST["username"]);
    $password = cleanInput($_POST["password"]);

    if ($username === "admin" && $password === "123456") {
        $_SESSION["logged_in"] = true;
        header("Location: index.php");
        exit;
    } else {
        $loginError = "Sai tên đăng nhập hoặc mật khẩu";
    }
}

if (isset($_GET["logout"])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

$isLoggedIn = !empty($_SESSION["logged_in"]);

if ($isLoggedIn && isset($_POST["save_user"])) {
    $id = (int)($_POST["id"] ?? 0);
    $fullName = cleanInput($_POST["full_name"]);
    $email = cleanInput($_POST["email"]);
    $phone = cleanInput($_POST["phone"]);
    $address = cleanInput($_POST["address"]);

    if ($id > 0) {
        $stmt = mysqli_prepare($conn, "UPDATE users SET full_name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ssssi", $fullName, $email, $phone, $address, $id);
        mysqli_stmt_execute($stmt);
        $message = "Sửa user thành công";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO users (full_name, email, phone, address) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssss", $fullName, $email, $phone, $address);
        mysqli_stmt_execute($stmt);
        $message = "Thêm user thành công";
    }
}

if ($isLoggedIn && isset($_GET["delete"])) {
    $id = (int)$_GET["delete"];
    $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    header("Location: index.php");
    exit;
}

$editUser = null;
if ($isLoggedIn && isset($_GET["edit"])) {
    $id = (int)$_GET["edit"];
    $stmt = mysqli_prepare($conn, "SELECT id, full_name, email, phone, address FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $editUser = mysqli_fetch_assoc($result);
}

$users = [];
if ($isLoggedIn) {
    $result = mysqli_query($conn, "SELECT id, full_name, email, phone, address FROM users ORDER BY id DESC");
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý user</title>
</head>
<body>
<?php if (!$isLoggedIn): ?>
    <h2>Đăng nhập</h2>

    <?php if ($loginError != ""): ?>
        <p style="color: red;"><?php echo showText($loginError); ?></p>
    <?php endif; ?>

    <form method="post">
        <p>
            Tên đăng nhập:
            <input type="text" name="username">
        </p>

        <p>
            Mật khẩu:
            <input type="password" name="password">
        </p>

        <button type="submit" name="login">Đăng nhập</button>
    </form>

    <p>Tài khoản demo: admin / 123456</p>
<?php else: ?>
    <h2>Quản lý user</h2>
    <p><a href="index.php?logout=1">Đăng xuất</a></p>

    <?php if ($message != ""): ?>
        <p style="color: green;"><?php echo showText($message); ?></p>
    <?php endif; ?>

    <h3><?php echo $editUser ? "Sửa user" : "Thêm user"; ?></h3>

    <form method="post">
        <input type="hidden" name="id" value="<?php echo showText($editUser["id"] ?? ""); ?>">

        <p>
            Họ tên:
            <input type="text" name="full_name" value="<?php echo showText($editUser["full_name"] ?? ""); ?>">
        </p>

        <p>
            Email:
            <input type="text" name="email" value="<?php echo showText($editUser["email"] ?? ""); ?>">
        </p>

        <p>
            Số điện thoại:
            <input type="text" name="phone" value="<?php echo showText($editUser["phone"] ?? ""); ?>">
        </p>

        <p>
            Địa chỉ:
            <input type="text" name="address" value="<?php echo showText($editUser["address"] ?? ""); ?>">
        </p>

        <button type="submit" name="save_user">
            <?php echo $editUser ? "Cập nhật" : "Thêm"; ?>
        </button>

        <?php if ($editUser): ?>
            <a href="index.php">Hủy</a>
        <?php endif; ?>
    </form>

    <h3>Danh sách user</h3>

    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Họ tên</th>
            <th>Email</th>
            <th>Số điện thoại</th>
            <th>Địa chỉ</th>
            <th>Thao tác</th>
        </tr>

        <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo showText($user["id"]); ?></td>
                <td><?php echo showText($user["full_name"]); ?></td>
                <td><?php echo showText($user["email"]); ?></td>
                <td><?php echo showText($user["phone"]); ?></td>
                <td><?php echo showText($user["address"]); ?></td>
                <td>
                    <a href="index.php?edit=<?php echo showText($user["id"]); ?>">Sửa</a>
                    |
                    <a href="index.php?delete=<?php echo showText($user["id"]); ?>"
                       onclick="return confirm('Bạn có chắc muốn xóa không?')">Xóa</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
<?php endif; ?>
