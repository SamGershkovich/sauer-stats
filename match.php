<!DOCTYPE html>
<html lang="en">

<?php
try {
    $dbh = new PDO("mysql:host=localhost;dbname=samgersh", "root", "7y_6V*87$#");
} catch (Exception $e) {
    die("ERROR. Couldn't get DB Connection. " . $e->getMessage());
}
if (isset($_REQUEST['id'])) {
    $id = $_REQUEST['id'];
    try {
        $dbh = new PDO("mysql:host=localhost;dbname=samgersh", "root", "7y_6V*87$#");
    } catch (Exception $e) {
        die("ERROR. Couldn't get DB Connection. " . $e->getMessage());
    }

    $cmd = "select * from sauer_matches where id = ?";
    $stmt = $dbh->prepare($cmd);
    $success = $stmt->execute([$id]);

    $data = null;

    if ($stmt->rowCount() <= 0) {
        echo 'Couldn\'t find match.';
    } else {
        $data = $stmt->fetch();
    }
} else {
    echo 'Couldn\'t find match.';
}
?>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sauer Stats</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <!-- <script src="main.js"></script> -->
    <style>
        #player-select {
            padding: 20px;
            display: block;
            margin: auto;
            margin-top: 20px;
            width: 75%;
        }

        .player-container,
        .gun-container {
            padding: 10px;
            width: fit-content;
            font-size: 18px;
            font-weight: 600;
            text-transform: uppercase;
            text-align: center;
            border: 1px solid gainsboro;
            transition: all 0.2s;
            display: inline-block;
            margin: 10px;
        }

        .player-container:hover,
        .gun-container:hover {
            background: #c1ffd7;
            color: black;
            transform: scaleX(1.05) scaleY(1.05);
            box-shadow: 0px 8px 25px -1px #00000040;
            cursor: pointer;
        }

        #submit-message {
            color: red;
        }

        .grid-container {
            display: grid;
            grid-auto-flow: column;
            width: 75%;
            margin: auto;
            text-transform: capitalize;
        }

        #game-info-grid {
            grid-auto-columns: 1fr 6fr;
            font-size: 20px;
        }

        #gun-grid {
            grid-auto-flow: row;

        }

        #stats-grid {
            grid-auto-flow: column;
        }

        #main {
            margin: auto;
        }

        .lose {
            color: red;
        }

        .win {
            color: green;
        }

        .tie {
            color: gray;
        }

        .selected,
        .selected-gun {
            background: #c1ffd7;
        }

        #map-image {
            width: 300px;
            height: 300px;
            border: 1px solid gainsboro;
        }
    </style>
</head>

<body>
    <div id="main">
        <h1>Cube 2: Sauerbraten - Match <?php echo $id; ?> Stats</h1>

        <div id='game-info-grid' class='grid-container'>
            <div id='map-image'></div>
            <div id='game-info'></div>
        </div>

        <div id='stats-grid' class='grid-container'>
            <div id='player-info'>
                <h2>Player Info - <?php echo $data['player_name']; ?></h2>
                <div id='player-info-grid'>

                </div>
            </div>

        </div>

        <div id='gun-grid' class='grid-container'>
            <div id='gun-select'>
                <h2>Gun Select</h2>
                <div id='gun-select-grid'>
                    <?php
                    $cmd = "select * from sauer_matches LIMIT 1";
                    $stmt = $dbh->prepare($cmd);
                    $success = $stmt->execute([]);

                    $row = $stmt->fetch();
                    $guns = json_decode($row['gun_hits']);

                    foreach ($guns as $gun => $val) {
                        echo '<div class="gun-container">' . $gun . '</div>';
                    }
                    ?>
                </div>
            </div>
            <div id='gun-stats'>
                <h2>Gun Stats</h2>
                <div id='gun-stats-grid'>

                </div>
            </div>
        </div>
    </div>
    <script>
        window.addEventListener("load", function() {
            let matchDate = <?php
                            if ($row == null) {
                                echo 'undefined';
                            } else {
                                echo '"' . date('M d, Y - g:i:s A', strtotime($data['date'])) . '"';
                            }
                            ?>;
            let matchData = <?php
                            if ($row == null) {
                                echo '"error"';
                            } else {
                                echo json_encode($data);
                            }
                            ?>;

            console.log(matchDate);

            selectedPlayer = matchData['player_name'];

            document.querySelector("#game-info").innerHTML = "<div>" +
                matchDate + "</div> <div>" +
                matchData['map'].split('_').join(' ') + "</div> <div>" +
                matchData['gamemode'] + "</div><div>" +
                matchData['win_state'] + "</div>"

            $.post({
                type: "POST",
                url: 'get_player_info.php',
                data: {
                    "player": name,
                },
                success: function(response) {

                    playerMatches = JSON.parse(response);

                    //console.log(playerMatches);
                    let info = {
                        "Kills": 0,
                        "Deaths": 0,
                        "Suicides": 0,
                        "Total Damage": 0,
                        "Total Shots": 0,
                        "Total Hits": 0,
                        "Accuracy": 0,
                        "K/D": 0,
                    }

                    info["Kills"] += parseInt(matchData['kills']);
                    info["Deaths"] += parseInt(matchData['deaths']);
                    info["Suicides"] += parseInt(matchData['suicides']);
                    info["Total Shots"] += parseInt(matchData['total_shots']);
                    info["Total Hits"] += parseInt(matchData['total_hits']);
                    info["Total Damage"] += parseInt(matchData['total_damage']);
                    info["Accuracy"] = parseFloat((info["Total Hits"] / info["Total Shots"] * 100).toFixed(2));
                    info["K/D"] = parseFloat((info["Kills"] / info["Deaths"]).toFixed(2));

                    let output = "";
                    Object.keys(info).map(function(key, index) {
                        output += "<div>" + key + ": " + info[key] + "</div>"
                    });
                    document.querySelector("#player-info-grid").innerHTML = output;

                }
            });


            document.querySelectorAll(".gun-container").forEach(function(el) {
                el.addEventListener("click", function() {

                    try {
                        document.querySelector(".selected-gun").classList.remove("selected-gun");
                    } catch {}
                    el.classList.add("selected-gun");

                    let gun = el.innerHTML;

                    let info = {
                        "Shots": 0,
                        "Hits": 0,
                        "Accuracy": 0,
                    };

                    info["Shots"] += getShots(matchData, gun);
                    info["Hits"] += getHits(matchData, gun);
                    info["Accuracy"] = parseFloat((info["Hits"] / Math.max(info["Shots"], 1) * 100).toFixed(2));

                    let output = "";
                    Object.keys(info).map(function(key, index) {
                        output += "<div>" + key + ": " + info[key] + "</div>"
                    });
                    document.querySelector("#gun-stats-grid").innerHTML = output;
                })
            })

            function getHits(match, gun) {
                gun = gun.toLowerCase()
                let hits = JSON.parse(match['gun_hits']);
                return parseInt(hits[gun]);
            }

            function getShots(match, gun) {
                gun = gun.toLowerCase()
                let shots = JSON.parse(match['gun_shots']);
                return parseInt(shots[gun]);
            }


        })
    </script>
</body>

</html>