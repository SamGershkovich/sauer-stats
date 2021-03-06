<?php
include "secrets.php";

try {
    $dbh = new PDO("mysql:host=localhost;dbname=".$dbname, $username, $password);
} catch (Exception $e) {
    die("ERROR. Couldn't get DB Connection. " . $e->getMessage());
}

$player = $_POST['player'];

$cmd = "select * from sauer_matches where player_name = ?";
$stmt = $dbh->prepare($cmd);
$success = $stmt->execute([$player]);

$out = [];

while ($row = $stmt->fetch()) {
    $out[] = $row;
}

if ($success === false) {
    echo "failed";
} else {
    echo json_encode($out);
}
