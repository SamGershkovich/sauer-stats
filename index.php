<?php

include "secrets.php"

?>
<!DOCTYPE html>
<html lang="en">

<?php
try {
    $dbh = new PDO("mysql:host=localhost;dbname=" . $dbname, $username, $password);
} catch (Exception $e) {
    die("ERROR. Couldn't get DB Connection. " . $e->getMessage());
}

$cmd = "select * from sauer_matches LIMIT 1";
$stmt = $dbh->prepare($cmd);
$success = $stmt->execute([]);

$row = $stmt->fetch();
$guns = json_decode($row['gun_hits']);
$gunList = [];
foreach ($guns as $gun => $val) {
    //echo '<div class="gun-slide" id="'.$gun.'-view"></div>';
    $gunList[] = $gun;
}
?>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sauer Stats</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
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

        .tie {
            color: gray;
        }

        .selected,
        .selected-gun {
            background: #c1ffd7;
        }

        .hidden {
            display: none !important;
        }
    </style>

    <!-- THREE.js -->
    <script src="assets/three.min.js"></script>
    <script src="assets/GLTFLoader.js"></script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400;1,500;1,600&family=Press+Start+2P&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/utilities.css">

</head>

<body>

    <header class="flex align-center">
        <div>
            <img src="assets/cube2logo.png" style="width: 280px; margin-right: 2rem" />
        </div>
        <div>
            <br />
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
                <img src="assets/imgs/curvy_castle.png" style="width: 100%; border-radius: 6px; margin-bottom: 1rem;" />
                <img src="assets/imgs/curvy_castle.png" style="width: 100%; border-radius: 6px; margin-bottom: 1rem;" />
                <img src="assets/imgs/curvy_castle.png" style="width: 100%; border-radius: 6px; margin-bottom: 1rem;" />
                <img src="assets/imgs/curvy_castle.png" style="width: 100%; border-radius: 6px; margin-bottom: 1rem;" />
            </div>
            <div id="player1" style=" border-width: 0px 2px 0px 2px; border-color: white; border-style: solid; width: 40%; padding: 1rem;" ondrop="player_dropped(event)" ondragover="allow_drop(event)">
                <h4 id="player1-name"> Drag & Drop a Player</h4>
                <div id="player1-info" class="flex flex-wrap justify-between player-info">
                    <div id="player1-total-stats"> </div>
                    <div id="player1-total-games"> </div>
                    <div id="player1-total-guns">
                        <div id='player1-gun-slider' class='gun-slider hidden'>
                            <div id='player1-gun-view' class='gun-view'>
                                <div id='player1-back-gun' class='back-gun slide-button' onclick="backGun('player1')">Back</div>
                                <div id='player1-gun-slide' class='gun-slide'></div>
                                <div id='player1-next-gun' class='next-gun slide-button' onclick="nextGun('player1')">Next</div>
                            </div>
                            <div id='player1-gun-stats' class='gun-stats'></div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="player2" style="width: 40%; padding: 1rem;" ondrop="player_dropped(event)" ondragover="allow_drop(event)">
                <h4 id="player2-name"> Drag & Drop a Player</h4>
                <div id="player2-info" class="flex flex-wrap justify-between player-info">
                    <div id="player2-total-stats"> </div>
                    <div id="player2-total-games"> </div>
                    <div id="player2-total-guns">
                        <div id='player2-gun-slider' class='gun-slider hidden'>
                            <div id='player2-gun-view' class='gun-view'>
                                <div id='player2-back-gun' class='back-gun slide-button' onclick="backGun('player2')">Back</div>
                                <div id='player2-gun-slide' class='gun-slide'></div>
                                <div id='player2-next-gun' class='next-gun slide-button' onclick="nextGun('player2')">Next</div>
                            </div>
                            <div id='player2-gun-stats' class='gun-stats'></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>


    </div>
    <script>
        const state = {
            player_dragged: undefined,
        }

        let matchData;
        let date;
        let gunShots;
        let gunHits;
        let guns = <?php echo json_encode($gunList); ?>;
        let selecteGunP1 = 0;
        let selecteGunP2 = 0;
        let matchesP1, matchesP2;

        let sceneP1, cameraP1, rendererP1, modelP1, loaderP1;
        let sceneP2, cameraP2, rendererP2, modelP2, loaderP2;

        function init() {
            // PLAYER 1
            sceneP1 = new THREE.Scene();
            cameraP1 = new THREE.PerspectiveCamera(40, window.innerWidth / window.innerHeight, 1, 5000);
            //cameraP1.rotation.y = 40 / 180 * Math.PI;
            //cameraP1.position.x = 800;
            cameraP1.position.y = 50;
            cameraP1.position.z = 1000;

            hlight = new THREE.AmbientLight(0x404040, 100);
            sceneP1.add(hlight);
            directionalLight = new THREE.DirectionalLight(0xffffff, 30);
            directionalLight.position.set(-200, 1000, 50);
            directionalLight.castShadow = true;
            sceneP1.add(directionalLight);
            rendererP1 = new THREE.WebGLRenderer({
                antialias: true,
                alpha: true
            });
            rendererP1.setSize(400, 200);
            document.querySelector((`#player1-gun-slide`)).appendChild(rendererP1.domElement);
            loaderP1 = new THREE.GLTFLoader();
            loaderP1.load('assets/chainsaw.gltf', function(gltf) {
                modelP1 = gltf.scene.children[0];
                modelP1.scale.set(150, 150, 175);

                sceneP1.add(gltf.scene);
                animate();
            });

            // PLAYER 2
            sceneP2 = new THREE.Scene();
            cameraP2 = new THREE.PerspectiveCamera(40, window.innerWidth / window.innerHeight, 1, 5000);
            //cameraP2.rotation.y = 40 / 180 * Math.PI;
            //cameraP2.position.x = 800;
            cameraP2.position.y = 50;
            cameraP2.position.z = 1000;

            hlight = new THREE.AmbientLight(0x404040, 100);
            sceneP2.add(hlight);
            directionalLight = new THREE.DirectionalLight(0xffffff, 30);
            directionalLight.position.set(-200, 1000, 50);
            directionalLight.castShadow = true;
            sceneP2.add(directionalLight);
            rendererP2 = new THREE.WebGLRenderer({
                antialias: true,
                alpha: true
            });
            rendererP2.setSize(400, 200);
            document.querySelector((`#player2-gun-slide`)).appendChild(rendererP2.domElement);
            loaderP2 = new THREE.GLTFLoader();
            loaderP2.load('assets/chainsaw.gltf', function(gltf) {
                modelP2 = gltf.scene.children[0];
                modelP2.scale.set(150, 150, 175);

                sceneP2.add(gltf.scene);
                animate();
            });

        }


        function animate() {
            try {
                rendererP1.render(sceneP1, cameraP1);
                modelP1.rotation.z += 0.01;
            } catch {}
            try {
                rendererP2.render(sceneP2, cameraP2);
                modelP2.rotation.z += 0.01;
            } catch {}
            requestAnimationFrame(animate);
        }



        window.addEventListener("load", function() {
            init();
            console.log(guns);
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

        function allow_drop(event) {
            event.preventDefault();
        }

        function player_dropped(event) {
            event.preventDefault();
            console.log("Dropped!", event.target);


            let players = ["player1", "player2"];
            let element = event.target;
            while (players.includes(element.id) === false) {
                element = element.parentElement;
            }
            const player_container = element.id; // Will be either "player1" or "player2"

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

                    if (player_container == 'player1') {
                        matchesP1 = playerMatches;
                    } else {
                        matchesP2 = playerMatches;
                    }
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
                    playerMatches.map(match => output += "<a href='/sauer/match.php?id=" + match['id'] + "' class='" + match['win_state'] + "'>Match ID: " + match['id'] + " - " + match['win_state'] + "</a><br>");
                    document.querySelector(`#${player_container}-total-games`).innerHTML = output;

                    let selectedGun = player_container == 'player1' ? (selecteGunP1 = 0) : (selecteGunP2 = 0);

                    document.querySelector(`#${player_container}-gun-slider`).classList.remove("hidden");

                    renderGunSlide(player_container);

                    //TODO: Make match view - player matches will be clickable
                    //TODO: Add individual gun stats
                    //TODO: Add images for maps and guns
                    //TODO: Add cool graphs :)
                    //TODO: sexify

                    //console.log(info);
                }
            })
        }

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

        function renderGunSlide(player) {
            if (player == 'player1') {
                console.log("assets/" + guns[selecteGunP1] + '.gltf')
                //document.querySelector(`#${player}-gun-slide`).innerHTML = guns[selecteGunP1];
                sceneP1.remove(sceneP1.children[2]);
                loaderP1.load("assets/" + guns[selecteGunP1] + '.gltf', function(gltf) {
                    modelP1 = gltf.scene.children[0];
                    modelP1.scale.set(100, 100, 150);

                    sceneP1.add(gltf.scene);

                });
            } else {
                // document.querySelector(`#${player}-gun-slide`).innerHTML = guns[selecteGunP2];
                sceneP2.remove(sceneP2.children[2]);
                loaderP2.load("assets/" + guns[selecteGunP2] + '.gltf', function(gltf) {
                    modelP2 = gltf.scene.children[0];
                    modelP2.scale.set(100, 100, 150);

                    sceneP2.add(gltf.scene);
                });
            }
            getGunStats(player);
        }

        function backGun(player) {
            if (player == 'player1') {
                selecteGunP1 = (selecteGunP1 == 0 ? guns.length - 1 : selecteGunP1 - 1);
            } else {
                selecteGunP2 = (selecteGunP2 == 0 ? guns.length - 1 : selecteGunP2 - 1);
            }
            renderGunSlide(player);

        }

        function nextGun(player) {
            if (player == 'player1') {
                selecteGunP1 = (selecteGunP1 == guns.length - 1 ? 0 : selecteGunP1 + 1);
            } else {
                selecteGunP2 = (selecteGunP2 == guns.length - 1 ? 0 : selecteGunP2 + 1);
            }
            renderGunSlide(player);

        }


        function getGunStats(player) {
            let playerMatches = matchesP1;
            let gun = guns[selecteGunP1];

            if (player == 'player2') {
                playerMatches = matchesP2;
                gun = guns[selecteGunP2];
            }

            let info = {
                "Total Shots": 0,
                "Total Hits": 0,
                "Accuracy": 0,
                "Average Shots": 0,
                "Average Hits": 0,
                "Average Accuracy": 0,
            }


            playerMatches.map(match => info["Total Shots"] += getShots(match, gun));
            playerMatches.map(match => info["Total Hits"] += getHits(match, gun));

            info["Accuracy"] = parseFloat((info["Total Hits"] / Math.max(info["Total Shots"], 1) * 100).toFixed(2));
            info["Average Shots"] = parseFloat((info["Total Shots"] / playerMatches.length).toFixed(2));
            info["Average Hits"] = parseFloat((info["Total Hits"] / playerMatches.length).toFixed(2));

            playerMatches.map(match => info["Average Accuracy"] += parseInt((getHits(match, gun) * 100) / Math.max(getShots(match, gun), 1)));
            info["Average Accuracy"] = parseFloat((info["Average Accuracy"] / playerMatches.length).toFixed(2));

            let output = "";
            Object.keys(info).map(function(key, index) {
                output += "<div>" + key + ": " + info[key] + "</div>"
            });
            document.querySelector(`#${player}-gun-stats`).innerHTML = output;
        }

        function getHits(match, gun) {
            let hits = JSON.parse(match['gun_hits']);
            return parseInt(hits[gun]);
        }

        function getShots(match, gun) {
            let shots = JSON.parse(match['gun_shots']);
            return parseInt(shots[gun]);
        }


        function dragging_player(event) {
            state.player_dragged = event.target.innerHTML.trim();
        }
    </script>
</body>

</html>