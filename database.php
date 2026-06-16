<?php

$conn = mysqli_connect(
    "127.0.0.1",
    "springstudent",
    "springstudent",
    "demo_db",
    3307
);

if (!$conn) {
    die("Ket noi that bai: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
