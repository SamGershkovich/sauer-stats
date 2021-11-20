<!DOCTYPE html>
<html lang="en">

<?php
try {
    $dbh = new PDO("mysql:host=localhost;dbname=samgersh", "root", "7y_6V*87$#");
} catch (Exception $e) {
    die("ERROR. Couldn't get DB Connection. " . $e->getMessage());
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

        #stats-grid {
            display: grid;
            grid-auto-flow: column;
            width: 75%;
            margin: auto;
            text-transform: capitalize;
        }

        #gun-grid {
            display: grid;
            width: 75%;
            margin: auto;
            text-transform: capitalize;
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
    </style>
</head>

<body>
    <div id="main">
        <h1>Cube 2: Sauerbraten - Player Stats</h1>

        <input type="file" name="inputfile" id="inputfile">
        <input type="button" id="submitMatch" value="Submit Match">
        <p id='submit-message'></p>

        <div id='player-select'>
            <h2>Players</h2>

            <?php
            $cmd = "select player_name from sauer_matches group by player_name";
            $stmt = $dbh->prepare($cmd);
            $success = $stmt->execute([]);

            while ($row = $stmt->fetch()) {
                echo '<div class="player-container">' . $row["player_name"] . '</div>';
            }

            ?>
        </div>

        <div id='stats-grid'>
            <div id='player-info'>
                <h2>Player Info</h2>
                <div id='player-info-grid'>

                </div>
            </div>
            <div id='player-matches'>
                <h2>Player's Matches</h2>
                <div id='player-match-grid'>

                </div>
            </div>
        </div>

        <div id='gun-grid'>
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
            let matchData;
            let date;
            let gunShots;
            let gunHits;
            let selectedPlayer = "";
            let selectedGun = undefined;
            let playerMatches = null;

            document.getElementById('inputfile').addEventListener('change', function() {
                var fr = new FileReader();
                fr.onload = function() {
                    matchData = JSON.parse(fr.result)[0];
                    gunShots = {
                        "chainsaw": matchData['Chainsaw']["Shots"],
                        "shotgun": matchData['Shotgun']["Shots"],
                        "minigun": matchData['Minigun']["Shots"],
                        "rocket": matchData['Rocket']["Shots"],
                        "sniper": matchData['Sniper']["Shots"],
                        "grenade": matchData['GL']["Shots"],
                        "pistol": matchData['Pistol']["Shots"],
                    };
                    gunHits = {
                        "chainsaw": matchData['Chainsaw']["Hits"],
                        "shotgun": matchData['Shotgun']["Hits"],
                        "minigun": matchData['Minigun']["Hits"],
                        "rocket": matchData['Rocket']["Hits"],
                        "sniper": matchData['Sniper']["Hits"],
                        "grenade": matchData['GL']["Hits"],
                        "pistol": matchData['Pistol']["Hits"],
                    };
                }
                fr.readAsText(this.files[0]);

                let filename = this.files[0].name;
                date = filename.split('.')[0];
                date = new Date(parseInt(date.substring(6) + "000"));
                date = date.toString().split('(')[0];
            });

            document.getElementById('submitMatch').addEventListener('click', function() {
                if (matchData !== undefined) {
                    $.post({
                        type: "POST",
                        url: 'upload_match.php',
                        data: {
                            "date": date,
                            "player": matchData['Player Name'],
                            "map": matchData['Map'],
                            "gamemode": matchData['Gamemode'],
                            "winState": matchData['WinState'],
                            "kills": matchData['Kills'],
                            "deaths": matchData['Deaths'],
                            "suicides": matchData['Suicides'],
                            "maxDamage": matchData['Max Damage'],
                            "totalDamage": matchData['Total Damage'],
                            "totalShots": matchData['Total Shots'],
                            "totalHits": matchData['Total Hits'],
                            "accuracy": matchData["Accuracy"],
                            "gunShots": JSON.stringify(gunShots),
                            "gunHits": JSON.stringify(gunHits)
                        },
                        success: function(response) {
                            document.querySelector("#submit-message").innerHTML = response;
                        }
                    })
                } else document.querySelector("#submit-message").innerHTML = "error: no file uploaded";

            });

            document.querySelectorAll(".player-container").forEach(function(el) {
                el.addEventListener("click", function() {

                    try {
                        document.querySelector(".selected").classList.remove("selected");
                    } catch {}
                    el.classList.add("selected");

                    let name = el.innerHTML.trim();
                    selectedPlayer = name;
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
                                "Total Games": 0,
                                "Total Wins": 0,
                                "Total Losses": 0,
                                "Total Ties": 0,
                                "Total Kills": 0,
                                "Total Deaths": 0,
                                "Total Suicides": 0,
                                "Total Damage": 0,
                                "Total Shots": 0,
                                "Total Hits": 0,
                                "Accuracy": 0,
                                "K/D": 0,
                                "Average K/D": 0,
                                "Average Kills": 0,
                                "Average Deaths": 0,
                                "Average Suicides": 0,
                                "Average Accuracy": 0,
                            }
                            info["Total Games"] = playerMatches.length;

                            playerMatches.map(match => (info["Total Wins"] += match['win_state'] == "win" ? 1 : 0));
                            playerMatches.map(match => (info["Total Losses"] += match['win_state'] == "lose" ? 1 : 0));
                            playerMatches.map(match => (info["Total Ties"] += match['win_state'] == "tie" ? 1 : 0));

                            playerMatches.map(match => info["Total Kills"] += parseInt(match['kills']));
                            playerMatches.map(match => info["Total Deaths"] += parseInt(match['deaths']));
                            playerMatches.map(match => info["Total Suicides"] += parseInt(match['suicides']));
                            playerMatches.map(match => info["Total Shots"] += parseInt(match['total_shots']));
                            playerMatches.map(match => info["Total Hits"] += parseInt(match['total_hits']));
                            playerMatches.map(match => info["Total Damage"] += parseInt(match['total_damage']));

                            info["Accuracy"] = parseFloat((info["Total Hits"] / info["Total Shots"] * 100).toFixed(2));

                            info["K/D"] = parseFloat((info["Total Kills"] / info["Total Deaths"]).toFixed(2));

                            playerMatches.map(match => info["Average K/D"] += (parseFloat((parseInt(match['kills']) / Math.max(parseInt(match['deaths']), 1)).toFixed(2))));
                            info["Average K/D"] = parseFloat((info["Average K/D"] / playerMatches.length).toFixed(2));

                            info["Average Kills"] = parseFloat((info["Total Kills"] / playerMatches.length).toFixed(2));
                            info["Average Deaths"] = parseFloat((info["Total Deaths"] / playerMatches.length).toFixed(2));
                            info["Average Suicides"] = parseFloat((info["Total Suicides"] / playerMatches.length).toFixed(2));

                            playerMatches.map(match => info["Average Accuracy"] += parseInt(match["accuracy"]));
                            info["Average Accuracy"] = parseFloat((info["Average Accuracy"] / playerMatches.length).toFixed(2));

                            let output = "";
                            Object.keys(info).map(function(key, index) {
                                output += "<div>" + key + ": " + info[key] + "</div>"
                            });
                            document.querySelector("#player-info-grid").innerHTML = output;

                            output = "";
                            playerMatches.map(match => output += "<a href='/sauer/match.php?id=" + match['id'] + "' class='" + match['win_state'] + "'>Match ID: " + match['id'] + " - " + match['win_state'] + "</a><br>");
                            document.querySelector("#player-match-grid").innerHTML = output;

                            if (selectedGun !== undefined)
                                getGunStats(selectedGun);
                            //TODO: Make match view - player matches will be clickable
                            //TODO: Add images for maps and guns
                            //TODO: Add cool graphs :)
                            //TODO: sexify

                            //console.log(info);
                        }
                    });
                })
            })

            document.querySelectorAll(".gun-container").forEach(function(el) {
                el.addEventListener("click", function() {

                    try {
                        document.querySelector(".selected-gun").classList.remove("selected-gun");
                    } catch {}
                    el.classList.add("selected-gun");

                    let gun = el.innerHTML;
                    selectedGun = gun;

                    getGunStats();
                })
            })

            function getGunStats() {
                let info = {
                    "Total Shots": 0,
                    "Total Hits": 0,
                    "Accuracy": 0,
                    "Average Shots": 0,
                    "Average Hits": 0,
                    "Average Accuracy": 0,
                }


                playerMatches.map(match => info["Total Shots"] += getShots(match));
                playerMatches.map(match => info["Total Hits"] += getHits(match));

                info["Accuracy"] = parseFloat((info["Total Hits"] / Math.max(info["Total Shots"], 1) * 100).toFixed(2));
                info["Average Shots"] = parseFloat((info["Total Shots"] / playerMatches.length).toFixed(2));
                info["Average Hits"] = parseFloat((info["Total Hits"] / playerMatches.length).toFixed(2));

                playerMatches.map(match => info["Average Accuracy"] += parseInt((getHits(match) * 100) / Math.max(getShots(match), 1)));
                info["Average Accuracy"] = parseFloat((info["Average Accuracy"] / playerMatches.length).toFixed(2));

                let output = "";
                Object.keys(info).map(function(key, index) {
                    output += "<div>" + key + ": " + info[key] + "</div>"
                });
                document.querySelector("#gun-stats-grid").innerHTML = output;
            }

            function getHits(match) {
                selectedGun = selectedGun.toLowerCase()
                let hits = JSON.parse(match['gun_hits']);
                return parseInt(hits[selectedGun]);
            }

            function getShots(match) {
                selectedGun = selectedGun.toLowerCase()
                let shots = JSON.parse(match['gun_shots']);
                return parseInt(shots[selectedGun]);
            }


        })
    </script>
</body>

</html>