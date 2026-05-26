<?php
// hash_users.php

$inputFile = __DIR__ . '/users.csv';
$outputFile = __DIR__ . '/users_hashed.csv';

if (!file_exists($inputFile)) {
    die("Error: ไม่พบไฟล์ users.csv ในโฟลเดอร์หลักของโครงการ กรุณานำไฟล์มาวางก่อนรันสคริปต์นี้ครับ\n");
}

$content = file_get_contents($inputFile);
// แปลงบรรทัดทุกแบบ (\r\n, \r) ให้เป็น \n
$content = str_replace(["\r\n", "\r"], "\n", $content);
$lines = explode("\n", $content);

$outputHandle = fopen($outputFile, 'w');

// เขียน UTF-8 BOM เพื่อให้โปรแกรมอย่าง Excel หรือโปรแกรมอื่นๆ เปิดภาษาไทยได้ถูกต้องทันที
fprintf($outputHandle, chr(0xEF).chr(0xBB).chr(0xBF));

// เขียนหัวคอลัมน์ (Headers) ให้ตรงกับตาราง users ในฐานข้อมูล
fputcsv($outputHandle, ['username', 'name', 'password', 'dept_name', 'role', 'created_at', 'updated_at']);

$rowCount = 0;
$successCount = 0;

foreach ($lines as $line) {
    if (empty(trim($line))) {
        continue;
    }

    $row = str_getcsv($line, ',');
    if (count($row) < 3) {
        continue;
    }

    // แปลงภาษาไทยจาก TIS-620 เป็น UTF-8 โดยใช้ iconv
    $name = iconv('TIS-620', 'UTF-8//IGNORE', trim($row[0]));
    $username = trim($row[1]);
    $plainPassword = trim($row[2]);

    // ข้ามแถวแรกหากเป็นหัวตาราง
    if ($rowCount === 0 && (str_contains(strtolower($name), 'หน่วยงาน') || str_contains(strtolower($name), 'ชื่อหน่วยงาน') || strtolower($username) === 'username')) {
        $rowCount++;
        continue;
    }

    if (empty($name) || empty($username) || empty($plainPassword)) {
        $rowCount++;
        continue;
    }

    // เข้ารหัสผ่านดิบเป็น Bcrypt Hash ด้วยฟังก์ชันของ PHP
    $hashedPassword = password_hash($plainPassword, PASSWORD_BCRYPT);
    $now = date('Y-m-d H:i:s');

    // บันทึกลงไฟล์ผลลัพธ์
    fputcsv($outputHandle, [
        $username,
        $name,
        $hashedPassword,
        $name, // dept_name ใช้ชื่อเดียวกับชื่อหน่วยงาน
        'user', // ตั้งค่าบทบาทเริ่มต้นเป็น user
        $now,
        $now
    ]);

    $successCount++;
    $rowCount++;
}

fclose($outputHandle);

echo "Success: เข้ารหัสข้อมูลสำเร็จทั้งหมด {$successCount} บัญชี (จากทั้งหมดที่พบ {$rowCount} บรรทัด)! ผลลัพธ์ถูกบันทึกที่ไฟล์ users_hashed.csv แล้วครับ\n";
