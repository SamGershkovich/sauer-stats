<?php

include "secrets.php"

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sauer Stats</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400;1,500;1,600&family=Press+Start+2P&display=swap" rel="stylesheet"> 

    <!-- <script src="main.js"></script> -->
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/utilities.css">
</head>

<body>

    <header class="flex align-center">
        <div>
        <img src="assets/cube2logo.png" style="width: 280px; margin-right: 2rem"/> 
        </div>
        <div>
            <br/>
            <input type="file" name="inputfile" id="inputfile">
            <input type="button" id="submitMatch" value="Submit Match">
            <p id='submit-message'></p>
        </div>
    </header>

    <div id="main">
        <div id='player-select'>
            <h3>Players</h3>
            <div class="flex">
                <?php
                try {
                    $dbh = new PDO("mysql:host=localhost;dbname=".$dbname, $username, $password);
                } catch (Exception $e) {
                    die("ERROR. Couldn't get DB Connection. " . $e->getMessage());
                }
                $cmd = "select player_name from sauer_matches group by player_name";
                $stmt = $dbh->prepare($cmd);
                $success = $stmt->execute([]);

                while ($row = $stmt->fetch()) {
                    echo '<div class="player-container" draggable="true" ondragstart="dragging_player(event)">' . $row["player_name"] . '</div>';
                }
                ?>
            </div>
        </div>

        <div style="display: flex;">
           <div style=" height: calc(100vh - 220px); overflow: auto; width: 20%; padding: 1rem;"> 
                <h4>Maps</h4>
                <img src="assets/imgs/curvy_castle.png" style="width: 100%; border-radius: 6px; margin-bottom: 1rem;"/>
                <img src="assets/imgs/curvy_castle.png" style="width: 100%; border-radius: 6px; margin-bottom: 1rem;"/>
                <img src="assets/imgs/curvy_castle.png" style="width: 100%; border-radius: 6px; margin-bottom: 1rem;"/>
                <img src="assets/imgs/curvy_castle.png" style="width: 100%; border-radius: 6px; margin-bottom: 1rem;"/>
           </div> 
           <div id="player1" style=" border-width: 0px 2px 0px 2px; border-color: white; border-style: solid; width: 40%; padding: 1rem;" ondrop="player_dropped(event)" ondragover="allow_drop(event)"> 
                <h4 id="player1-name"> Drag & Drop a Player</h4>
                <div id="player1-info" class="flex flex-wrap justify-between player-info"> 
                    <div id="player1-total-stats"> </div>
                    <div id="player1-total-games"> </div>
                </div>
           </div> 
           <div id="player2" style="width: 40%; padding: 1rem;" ondrop="player_dropped(event)" ondragover="allow_drop(event)">
                <h4 id="player2-name"> Drag & Drop a Player</h4>
                <div id="player2-info" class="flex flex-wrap justify-between player-info"> 
                    <div id="player2-total-stats"> </div>
                    <div id="player2-total-games"> </div>
                </div>
            </div> 
        </div>

    </div>
    <script>

        const state = {
            player_dragged: undefined,
        }

        window.addEventListener("load", function() {
            let matchData;
            let date;
            let gunShots;
            let gunHits;

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

        })

        function allow_drop(event) { event.preventDefault(); }

        function player_dropped( event ) {
            event.preventDefault(); 
            console.log("Dropped!", event.originalTarget);


            let players = ["player1", "player2"];
            let element = event.originalTarget;
            while ( players.includes( element.id ) === false ) {
                element = element.parentElement;
            }
            const player_container = element.id;   // Will be either "player1" or "player2"

            // Update the Player Container name to the Player thats been dropped in
            document.getElementById(`${player_container}-name`).innerText = state.player_dragged;

            $.post({
                type: "POST",
                url: 'get_player_info.php',
                data: {
                    "player": state.player_dragged,
                },
                success: function(response) {

                    let playerMatches = JSON.parse(response);
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
                    document.querySelector(`#${player_container}-total-stats`).innerHTML = output;

                    output = "";
                    playerMatches.map(match => output += "<div class='" + match['win_state'] + "'>Match ID: " + match['id'] + " - " + match['win_state'] + "</div><br>");
                    document.querySelector(`#${player_container}-total-games`).innerHTML = output;

                    //TODO: Make match view - player matches will be clickable
                    //TODO: Add individual gun stats
                    //TODO: Add images for maps and guns
                    //TODO: Add cool graphs :)
                    //TODO: sexify

                    //console.log(info);
                }
            })
        }

        function dragging_player(event) {
            state.player_dragged = event.originalTarget.innerHTML.trim();
        }
    </script>
</body>

</html>
