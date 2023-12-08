<?php
session_start();

require_once "config.php";

function DateSwamp($dt)
{
    $now = new DateTime;
    $ago = new DateTime(date("Y-m-d H:i:s", $dt));
    $x = $ago->diff($now);
    $lata = (int)$x->format('%y');
    $mies = (int)$x->format('%m');
    $dni = (int)$x->format('%d');
    $godz = (int)$x->format('%h');
    $min = (int)$x->format('%i');
    $sek = (int)$x->format('%s');


    $format = "";

    if ($lata != 0) {
        if ($lata > 1 && $lata < 5) {
            $format = $lata . " lata";
        } else if ($lata > 5) {
            $format = $lata . " lat";
        } else {
            $format = $lata . " rok";
        }
    } else if ($mies != 0) {
        if ($mies > 1 && $mies < 5) {
            $format = $mies . " miesiące";
        } else if ($mies > 5) {
            $format = $mies . " miesięcy";
        } else {
            $format = $mies . " miesiąc";
        }
    } else if ($dni != 0) {
        if ($dni > 1) {
            $format = $dni . " dni";
        } else {
            $format = $dni . " dzień";
        }
    } else if ($godz != 0) {
        if ($godz > 1 && $godz < 5) {
            $format = $godz . " godziny";
        } else if ($godz > 5) {
            $format = $godz . " godzin";
        } else {
            $format = $godz . " godzina";
        }
    } else if ($min != 0){
        if ($min > 1 && $min < 5){
            $format = $min . " minuty";
        } else if ($min > 4){
            $format = $min . " minut";
        } else{
            $format = $min . " minuta";
        }
    }

    return "$format temu";
}

if (isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] === true) {
    $sql = "SELECT name, Vname, CustomVname, followers, partner, channelIMG FROM channels WHERE email = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $_SESSION["email"]);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $name, $VnameCH, $CustomVname, $followers, $partner, $channelIMG);
                mysqli_stmt_fetch($stmt);
            }
        }
    }
}

?>
<!doctype html>
<html lang="pl">
<head>
    <meta name="author" content="Adrian Rzeszutek">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css"
          integrity="sha512-YWzhKL2whUzgiheMoBFwW8CKV4qpHQAEuvilg9FAn5VJUDwKZZxkJNuGM4XkWuk94WCrrwslk8yWNGmY1EduTA=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <meta name="hilltopads-site-verification" content="fc830c04cdd7ba756513d3a207f481e8cd672eab"/>
    <link rel="icon" type="image/x-icon" href="img/favicon.png">
    <title>Odtwarzacz</title>
</head>
<style>
    <?php include 'MainStyle.css'; ?>
</style>
<?php
$url_components = parse_url($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
parse_str($url_components['query'], $params);
?>
<body>
<nav class="navbar">
    <div class="toggle-btn">
        <span></span>
        <span></span>
        <span></span>
    </div>
    <img src="img/logo.png" class="logo" onclick="window.open('index.php', '_self')">
    <div class="search-box">
        <form class="flex-search" action="<?php echo htmlspecialchars('/players/result.php'); ?>" method="GET">
            <input value="<?php if (!empty(($params['query']))) echo $params["query"] ?>" type="text" id="search"
                   name="query" class="search-bar" placeholder="Szukaj" autocomplete="off">
            <button class="search-btn" id="search-btn"><i class="fa fa-search" aria-hidden="true"></i></button>
        </form>
        <span onclick="startDictation()"><i class="fa fa-microphone icon"></i></span>
    </div>
    <div class="user-options">
        <span onclick="window.open('upload.php','_self')"><i class="fa fa-video icon"></i></span>
        <span><i class="fa fa-server icon"></i></span>
        <span><i class="fa fa-bell icon"></i></span>
        <?php
        if (isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] === true) {
            echo '<div class="user-dp"><img src="' . $channelIMG . '" onclick="Showchannel()" alt=""></div>';
        } else {
            echo '<span onclick="window.open(\'auth/login\', \'_self\')"><i class="fa fa-user icon"></i></span>';
        }
        ?>
    </div>
</nav>
<div class="user-content" id="user-content">
    <div style="padding: 0; margin:0; border:0; flex: none; background: transparent">
        <div class="user-header">
            <div class="user-avatar">
                <img src="<?php echo $channelIMG ?>" height="40" width="40"
                     style="aspect-ratio: auto 40 / 40; border-radius: 50%;">
            </div>
            <div class="user-channel">
                <odt-formatted-string>
                    Adrian
                    <!--TODO: NAZWA KANAŁU Z SQL-->
                </odt-formatted-string>
            </div>
        </div>
        <div class="user-container">
            <div class="user-container-sections">
                <div class="user-section-render">
                    <div class="user-section-items">
                        <div class="user-section-link-render">
                            <a class="section-endpoint" href="">
                                <div class="link-render">
                                    <div class="content-icon">
                                        <div class="compact-link-render">
                                            <svg style="fill: white; pointer-events: none; display: block; width: 100%; height:100%;"
                                                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                                                <path d="M272 304h-96C78.8 304 0 382.8 0 480c0 17.67 14.33 32 32 32h384c17.67 0 32-14.33 32-32C448 382.8 369.2 304 272 304zM48.99 464C56.89 400.9 110.8 352 176 352h96c65.16 0 119.1 48.95 127 112H48.99zM224 256c70.69 0 128-57.31 128-128c0-70.69-57.31-128-128-128S96 57.31 96 128C96 198.7 153.3 256 224 256zM224 48c44.11 0 80 35.89 80 80c0 44.11-35.89 80-80 80S144 172.1 144 128C144 83.89 179.9 48 224 48z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="primary-text-container">
                                        <odt-formatted-string-link>
                                            Twój kanał
                                        </odt-formatted-string-link>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="user-section-link-render">
                            <a class="section-endpoint" href="">
                                <div class="link-render">
                                    <div class="content-icon">
                                        <div class="compact-link-render">
                                            <svg style="fill: white; pointer-events: none; display: block; width: 100%; height:100%;"
                                                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
                                                <path d="M168 336C181.3 336 192 346.7 192 360C192 373.3 181.3 384 168 384H120C106.7 384 96 373.3 96 360C96 346.7 106.7 336 120 336H168zM360 336C373.3 336 384 346.7 384 360C384 373.3 373.3 384 360 384H248C234.7 384 224 373.3 224 360C224 346.7 234.7 336 248 336H360zM512 32C547.3 32 576 60.65 576 96V416C576 451.3 547.3 480 512 480H64C28.65 480 0 451.3 0 416V96C0 60.65 28.65 32 64 32H512zM512 80H64C55.16 80 48 87.16 48 96V128H528V96C528 87.16 520.8 80 512 80zM528 224H48V416C48 424.8 55.16 432 64 432H512C520.8 432 528 424.8 528 416V224z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="primary-text-container">
                                        <odt-formatted-string-link>
                                            Zakupy
                                        </odt-formatted-string-link>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="user-section-link-render">
                            <a class="section-endpoint" href="auth/logout.php">
                                <div class="link-render">
                                    <div class="content-icon">
                                        <div class="compact-link-render">
                                            <svg style="fill: white; pointer-events: none; display: block; width: 100%; height:100%;"
                                                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                                <path d="M416 32h-64c-17.67 0-32 14.33-32 32s14.33 32 32 32h64c17.67 0 32 14.33 32 32v256c0 17.67-14.33 32-32 32h-64c-17.67 0-32 14.33-32 32s14.33 32 32 32h64c53.02 0 96-42.98 96-96V128C512 74.98 469 32 416 32zM342.6 233.4l-128-128c-12.51-12.51-32.76-12.49-45.25 0c-12.5 12.5-12.5 32.75 0 45.25L242.8 224H32C14.31 224 0 238.3 0 256s14.31 32 32 32h210.8l-73.38 73.38c-12.5 12.5-12.5 32.75 0 45.25s32.75 12.5 45.25 0l128-128C355.1 266.1 355.1 245.9 342.6 233.4z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="primary-text-container">
                                        <odt-formatted-string-link>
                                            Wyloguj się
                                        </odt-formatted-string-link>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <!-- style="fill: white; pointer-events: none; display: block; width: 100%; height:100%;" dla SVG ! -->
                <div class="user-section-render">
                    <div class="user-section-items">
                        <div class="user-section-link-render">
                            <a class="section-endpoint" href="">
                                <div class="link-render">
                                    <div class="content-icon">
                                        <div class="compact-link-render">
                                            <svg style="fill: white; pointer-events: none; display: block; width: 100%; height:100%;"
                                                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                                <path d="M421.6 379.9c-.6641 0-1.35 .0625-2.049 .1953c-11.24 2.143-22.37 3.17-33.32 3.17c-94.81 0-174.1-77.14-174.1-175.5c0-63.19 33.79-121.3 88.73-152.6c8.467-4.812 6.339-17.66-3.279-19.44c-11.2-2.078-29.53-3.746-40.9-3.746C132.3 31.1 32 132.2 32 256c0 123.6 100.1 224 223.8 224c69.04 0 132.1-31.45 173.8-82.93C435.3 389.1 429.1 379.9 421.6 379.9zM255.8 432C158.9 432 80 353 80 256c0-76.32 48.77-141.4 116.7-165.8C175.2 125 163.2 165.6 163.2 207.8c0 99.44 65.13 183.9 154.9 212.8C298.5 428.1 277.4 432 255.8 432z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="primary-text-container">
                                        <odt-formatted-string-link>
                                            Wygląd: Ciemny<!--jasny/tryb urządzenia-->
                                        </odt-formatted-string-link>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="user-section-link-render">
                            <a class="section-endpoint" href="">
                                <div class="link-render">
                                    <div class="content-icon">
                                        <div class="compact-link-render">
                                            <svg style="fill: white; pointer-events: none; display: block; width: 100%; height:100%;"
                                                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                                <path d="M495.9 166.6C499.2 175.2 496.4 184.9 489.6 191.2L446.3 230.6C447.4 238.9 448 247.4 448 256C448 264.6 447.4 273.1 446.3 281.4L489.6 320.8C496.4 327.1 499.2 336.8 495.9 345.4C491.5 357.3 486.2 368.8 480.2 379.7L475.5 387.8C468.9 398.8 461.5 409.2 453.4 419.1C447.4 426.2 437.7 428.7 428.9 425.9L373.2 408.1C359.8 418.4 344.1 427 329.2 433.6L316.7 490.7C314.7 499.7 307.7 506.1 298.5 508.5C284.7 510.8 270.5 512 255.1 512C241.5 512 227.3 510.8 213.5 508.5C204.3 506.1 197.3 499.7 195.3 490.7L182.8 433.6C167 427 152.2 418.4 138.8 408.1L83.14 425.9C74.3 428.7 64.55 426.2 58.63 419.1C50.52 409.2 43.12 398.8 36.52 387.8L31.84 379.7C25.77 368.8 20.49 357.3 16.06 345.4C12.82 336.8 15.55 327.1 22.41 320.8L65.67 281.4C64.57 273.1 64 264.6 64 256C64 247.4 64.57 238.9 65.67 230.6L22.41 191.2C15.55 184.9 12.82 175.3 16.06 166.6C20.49 154.7 25.78 143.2 31.84 132.3L36.51 124.2C43.12 113.2 50.52 102.8 58.63 92.95C64.55 85.8 74.3 83.32 83.14 86.14L138.8 103.9C152.2 93.56 167 84.96 182.8 78.43L195.3 21.33C197.3 12.25 204.3 5.04 213.5 3.51C227.3 1.201 241.5 0 256 0C270.5 0 284.7 1.201 298.5 3.51C307.7 5.04 314.7 12.25 316.7 21.33L329.2 78.43C344.1 84.96 359.8 93.56 373.2 103.9L428.9 86.14C437.7 83.32 447.4 85.8 453.4 92.95C461.5 102.8 468.9 113.2 475.5 124.2L480.2 132.3C486.2 143.2 491.5 154.7 495.9 166.6V166.6zM256 336C300.2 336 336 300.2 336 255.1C336 211.8 300.2 175.1 256 175.1C211.8 175.1 176 211.8 176 255.1C176 300.2 211.8 336 256 336z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="primary-text-container">
                                        <odt-formatted-string-link>
                                            Ustawienia
                                        </odt-formatted-string-link>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="user-section-link-render">
                            <a class="section-endpoint" href="">
                                <div class="link-render">
                                    <div class="content-icon">
                                        <div class="compact-link-render">
                                            <svg style="fill: white; pointer-events: none; display: block; width: 100%; height:100%;"
                                                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
                                                <path d="M512 64H64C28.65 64 0 92.65 0 128v256c0 35.35 28.65 64 64 64h448c35.35 0 64-28.65 64-64V128C576 92.65 547.3 64 512 64zM528 384c0 8.822-7.178 16-16 16H64c-8.822 0-16-7.178-16-16V128c0-8.822 7.178-16 16-16h448c8.822 0 16 7.178 16 16V384zM140 152h-24c-6.656 0-12 5.344-12 12v24c0 6.656 5.344 12 12 12h24c6.656 0 12-5.344 12-12v-24C152 157.3 146.7 152 140 152zM196 200h24c6.656 0 12-5.344 12-12v-24c0-6.656-5.344-12-12-12h-24c-6.656 0-12 5.344-12 12v24C184 194.7 189.3 200 196 200zM276 200h24c6.656 0 12-5.344 12-12v-24c0-6.656-5.344-12-12-12h-24c-6.656 0-12 5.344-12 12v24C264 194.7 269.3 200 276 200zM356 200h24c6.656 0 12-5.344 12-12v-24c0-6.656-5.344-12-12-12h-24c-6.656 0-12 5.344-12 12v24C344 194.7 349.3 200 356 200zM460 152h-24c-6.656 0-12 5.344-12 12v24c0 6.656 5.344 12 12 12h24c6.656 0 12-5.344 12-12v-24C472 157.3 466.7 152 460 152zM140 232h-24c-6.656 0-12 5.344-12 12v24c0 6.656 5.344 12 12 12h24c6.656 0 12-5.344 12-12v-24C152 237.3 146.7 232 140 232zM196 280h24c6.656 0 12-5.344 12-12v-24c0-6.656-5.344-12-12-12h-24c-6.656 0-12 5.344-12 12v24C184 274.7 189.3 280 196 280zM276 280h24c6.656 0 12-5.344 12-12v-24c0-6.656-5.344-12-12-12h-24c-6.656 0-12 5.344-12 12v24C264 274.7 269.3 280 276 280zM356 280h24c6.656 0 12-5.344 12-12v-24c0-6.656-5.344-12-12-12h-24c-6.656 0-12 5.344-12 12v24C344 274.7 349.3 280 356 280zM460 232h-24c-6.656 0-12 5.344-12 12v24c0 6.656 5.344 12 12 12h24c6.656 0 12-5.344 12-12v-24C472 237.3 466.7 232 460 232zM400 320h-224C167.1 320 160 327.1 160 336V352c0 8.875 7.125 16 16 16h224c8.875 0 16-7.125 16-16v-16C416 327.1 408.9 320 400 320z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="primary-text-container">
                                        <odt-formatted-string-link>
                                            Skróty klawiszowe
                                        </odt-formatted-string-link>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container">
    <div class="list-container">
        <?php
        // ID UNIQUE, NAME VIDEO,opis, VKEY , DATE, like, unlike, views, author, previewImg (base64), typ, +18
        $count = 0;
        if (!empty($params['query'])) {
            if ($handle = opendir('v/.')) {
                while (false !== ($entry = readdir($handle))) {
                    if ($entry != "." && $entry != "..") {
                        $imageFileType = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
                        $name = str_replace(".mp4", " ", $entry);
                        if ($imageFileType == "mp4") {
                            if (str_contains(strtolower($entry), strtolower($params['query'])) !== false) {
                                $count++;
                                $sql = "SELECT vkey,opis,author,views,data,previewIMG,name FROM video WHERE Vname = '$entry'";
                                $result = $link->query($sql);
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<div class='vid-list' onclick='window.open(\"../players/media/watch.php?query={$row['vkey']}\", \"_self\")'>
                                        <img src='". $row['previewIMG'] ."' class='thumbnail'>";
                                        //$name = $row['name'];
                                        echo "<div class='vid-info'>";
                                        echo "<a>{$row['name']}</a>";
                                        echo "<p>{$row['views']} wyświetleń•" . DateSwamp($row['data']) . "</p>";
                                        echo "<div class='vid-i'><img src='img/user.png'><p>{$row['author']}</p></div>";
                                        echo "</div></div>";
                                    }
                                }
                            }
                        }
                    }
                }
                closedir($handle);
            }
            $sql = "SELECT vkey,opis,author,views,data,previewIMG,Vname,name FROM video WHERE name LIKE '%{$params['query']}%'";
            $result = $link->query($sql);
            if ($result->num_rows > 0) {
                $count++;
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='vid-list' onclick='window.open(\"../players/media/watch.php?query={$row['vkey']}\", \"_self\")'>
                        <img src='". $row['previewIMG'] ."' class='thumbnail'>";
                    echo "<div class='vid-info'>";
                    echo "<a>{$row['name']}</a>";
                    echo "<p>{$row['views']} wyświetleń•" . DateSwamp($row['data']) . "</p>";
                    echo "<div class='vid-i'><img src='img/user.png'><p>{$row['author']}</p></div>";
                    echo "</div></div>";
                }
            }
            /*if (str_contains(strtolower($name), strtolower($params['query']))){
                $sql = "SELECT vkey,opis,author,views,data,previewIMG,name FROM video WHERE Vname = '$entry'";
                $result = $link->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<div class='vid-list' onclick='window.open(\"../players/media/watch.php?query={$row['vkey']}\", \"_self\")'>
                        <img src='". $row['previewIMG'] ."' class='thumbnail'>";
                        $name = $row['name'];
                        echo "<div class='vid-info'>";
                        echo "<a>{$row['name']}</a>";
                        echo "<p>{$row['views']} wyświetleń•" . DateSwamp($row['data']) . "</p>";
                        echo "<div class='vid-i'><img src='img/user.png'><p>{$row['author']}</p></div>";
                        echo "</div></div>";
                    }
                }
            }*/
        }
        if ($count == 0) {
            echo "<div class='vid-list'><p>Nie znaleziono żadnych filmów!</p></div>";
        }
        ?>
    </div>
</div>
</body>
<script>
    document.title = "<?php echo $params["query"]; ?> - Odtwarzacz";

    function startDictation() {

        if (window.hasOwnProperty('webkitSpeechRecognition')) {
            var recognition = new webkitSpeechRecognition();
            recognition.continuous = false;
            recognition.interimResults = false;
            recognition.lang = "pl-PL";
            recognition.start();
            recognition.onresult = function (e) {
                document.getElementById('search').value
                    = e.results[0][0].transcript;
                recognition.stop();
                setTimeout(function () {
                    document.getElementById('search-btn').click();
                }, 2000)
            };
            recognition.onerror = function (e) {
                recognition.stop();
            }
        }
    }

    function hideChannel() {
        if (window.getComputedStyle(document.getElementById('user-content')).display === "flex") {
            document.getElementById('user-content').style.display = "none";
        }
    }

    function Showchannel() {
        if (window.getComputedStyle(document.getElementById('user-content')).display === "none") {
            document.getElementById('user-content').style.display = "flex";
        } else {
            document.getElementById('user-content').style.display = "none";
        }
    }
</script>

