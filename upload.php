<?php
// upload.php — 업로드된 엑셀 저장 + active.json 갱신 전용
date_default_timezone_set("Asia/Seoul");

header('Content-Type: application/json; charset=utf-8');

// 파일 유효성 검사
if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(["ok"=>false, "error"=>"파일 없음"]);
    exit;
}
$f = $_FILES['file'];
if ($f['error'] !== UPLOAD_ERR_OK) {
    http_response_code(500);
    echo json_encode(["ok"=>false, "error"=>"업로드 실패: ".$f['error']]);
    exit;
}
$ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ["xls","xlsx"])) {
    http_response_code(400);
    echo json_encode(["ok"=>false, "error"=>"xls/xlsx만 허용"]);
    exit;
}

// 저장 경로 준비
$dir = __DIR__ . "/data/batches";
if (!is_dir($dir)) mkdir($dir, 0777, true);

$ts = date("Ymd_His");
$rand = bin2hex(random_bytes(3));
$saveName = "{$ts}_{$rand}_" . basename($f['name']);
$savePath = $dir . "/" . $saveName;

if (!move_uploaded_file($f['tmp_name'], $savePath)) {
    http_response_code(500);
    echo json_encode(["ok"=>false,"error"=>"저장 실패"]);
    exit;
}

// active.json 작성
$meta = [
  "ok" => true,
  "batch_id" => "$ts-$rand",
  "filename" => $f['name'],
  "stored" => "data/batches/" . $saveName,
  "uploaded_at" => date("c"),
  "url" => "data/batches/" . $saveName
];
file_put_contents(__DIR__."/data/active.json", json_encode($meta, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));

echo json_encode($meta, JSON_UNESCAPED_UNICODE);
