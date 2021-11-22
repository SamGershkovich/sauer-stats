<?php
include "secrets.php";

try {
    $dbh = new PDO("mysql:host=localhost;dbname=".$dbname, $username, $password);
} catch (Exception $e) {
    die("ERROR. Couldn't get DB Connection. " . $e->getMessage());
}
date_default_timezone_set('America/Toronto');

$date = date('Y-m-d H:i:s', strtotime($_POST['date']));
$player = $_POST['player'];
$map = $_POST['map'];
$gamemode = $_POST['gamemode'];
$win_state = $_POST['winState'];
$kills = $_POST['kills'];
$deaths = $_POST['deaths'];
$suicides = $_POST['suicides'];
$max_damage = $_POST['maxDamage'];
$total_damage = $_POST['totalDamage'];
$total_shots = $_POST['totalShots'];
$total_hits = $_POST['totalHits'];
$accuracy = $_POST['accuracy'];
$gun_shots = json_decode($_POST['gunShots']);
$gun_hits = json_decode($_POST['gunHits']);

$cmd = "select * from sauer_matches where date = ? and player_name = ?";
$stmt = $dbh->prepare($cmd);
$success = $stmt->execute([$date, $player]);

if ($stmt->rowCount() > 0) {
    echo 'failed: match already exists';
} else {
    $cmd = "INSERT INTO `sauer_matches`(`date`, `player_name`, `map`, `gamemode`, `win_state`, `kills`, `deaths`, `suicides`, `max_damage`, `total_damage`, `total_shots`, `total_hits`, `accuracy`, `gun_shots`, `gun_hits`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmt = $dbh->prepare($cmd);
    $success = $stmt->execute([$date, $player, $map, $gamemode, $win_state, $kills, $deaths, $suicides, $max_damage, $total_damage, $total_shots, $total_hits, $accuracy, json_encode($gun_shots), json_encode($gun_hits)]);

    if ($success === false) {
        echo "failed";
    } else {
        echo "success";
    }
}
