<?php
session_start();

$url_components = parse_url($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
parse_str($url_components['query'], $params);

$titles = array();
$authors = array();

require_once "../config.php";

function DateSwamp($dt)
{
    $dat = date('Y-m-d', $dt);
    $date = new DateTime($dat);
    $lata = (int)$date->format('Y');
    $mies = (int)$date->format('m');
    $dni = (int)$date->format('d');
    $format = $dni . " " . getMonth($mies) . " " . $lata;

    return $format;
}

function TimeSwamp($dt)
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
    } else if ($min != 0) {
        if ($min > 1 && $min < 5) {
            $format = $min . " minuty";
        } else if ($min > 4) {
            $format = $min . " minut";
        } else {
            $format = $min . " minuta";
        }
    }

    return "$format temu";
}

function getMonth($var)
{
    switch ($var) {
        case 1:
            return "sty";
        case 2:
            return "lut";
        case 3:
            return "mar";
        case 4:
            return "kwi";
        case 5:
            return "maj";
        case 6:
            return "cze";
        case 7:
            return "lip";
        case 8:
            return "sie";
        case 9:
            return "wrz";
        case 10:
            return "paź";
        case 11:
            return "lis";
        case 12:
            return "gru";
    }
}

if (isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] === true) {
    if (!empty($params['query'])) {
        $sql = "SELECT views FROM video WHERE vkey = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $params['query']);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $views);
                    if (mysqli_stmt_fetch($stmt)) {
                        $views = (int)$views + 1;
                        $sql = "UPDATE video SET views= {$views} WHERE vkey = ?";
                        if ($stmt = mysqli_prepare($link, $sql)) {
                            mysqli_stmt_bind_param($stmt, "s", $params['query']);
                            mysqli_stmt_execute($stmt);
                        }
                    }
                } else {
                    echo "Błąd";
                }
            } else {
                echo "Błąd";
            }

            mysqli_stmt_close($stmt);
        }
    }
}

if (!empty($params['query'])) {
    $sql = "SELECT Vname,opis,author,views,data,previewIMG,name,likes,unlikes FROM video WHERE vkey = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $params['query']);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $Vname, $opis, $author, $views, $data, $previewIMG, $name, $likes, $unlikes);
                if (mysqli_stmt_fetch($stmt)) {
                    array_push($titles, $name);
                    array_push($authors, $author);
                    echo '<script>document.title = "' . $name . ' - Odtwarzacz";</script>';
                }
            }
        } else {
            echo "Błąd";
        }

        mysqli_stmt_close($stmt);
    }
} else {
    header("Location: ../index.php");
}

$vids = [];
if ($handle = opendir('../v/.')) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
            $imageFileType = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
            //$name = str_replace(".mp4", " ", $entry);
            if ($imageFileType == "mp4") {
                if (str_contains(strtolower($entry), strtolower($Vname)) === false) {
                    $sql = "SELECT vkey,opis,author,views,data,previewIMG,name, Vname FROM video WHERE Vname = '$entry'";
                    $result = $link->query($sql);
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            array_push($vids, array($row['author'], $row['name'], $row['vkey'], $row['previewIMG'], $row['views'], $row['data'],  $row['Vname']));
                            //                             0               1             2             3                   4              5             7
                        }
                    }
                }
            }
        }
    }
}

if (!empty($author)) {
    $sql = "SELECT email, Vname, CustomVname, followers, partner, channelIMG FROM channels WHERE name = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $author);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $email, $VnameCH, $CustomVname, $followers, $partner, $channelIMG);
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
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.0/css/all.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <meta name="hilltopads-site-verification" content="fc830c04cdd7ba756513d3a207f481e8cd672eab"/>
    <link rel="icon" type="image/x-icon" href="../img/favicon.png">
    <title>Odtwarzacz</title>
    <!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. -->
</head>
<style>
    <?php include '../MainStyle.css'; ?>
</style>
<body>
<nav class="navbar">
    <div class="toggle-btn">
        <span></span>
        <span></span>
        <span></span>
    </div>
    <img src="../img/logo.png" class="logo" onclick="window.open('../index.php', '_self')">
    <div class="search-box">
        <form class="flex-search" action="<?php echo htmlspecialchars('/players/result.php'); ?>" method="GET">
            <input type="text" id="search" name="query" class="search-bar" placeholder="Szukaj" autocomplete="off">
            <button class="search-btn" id="search-btn"><i class="fa fa-search" aria-hidden="true"></i></button>
        </form>
        <span onclick="startDictation()"><i class="fa fa-microphone icon"></i></span>
    </div>
    <div class="user-options">
        <span onclick="window.open('../upload.php','_self')"><i class="fa fa-video icon"></i></span>
        <span><i class="fa fa-server icon"></i></span>
        <span><i class="fa fa-bell icon"></i></span>
        <?php
        if (isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] === true) {
            echo '<div class="user-dp"><img src="' . $channelIMG . '" onclick="Showchannel()" alt=""></div>';
        } else {
            echo '<span onclick="window.open(\'../auth/login\', \'_self\')"><i class="fa fa-user icon"></i></span>';
        }
        ?>
    </div>
</nav>
<div class="user-content" id="user-content">
    <div style="padding: 0; margin:0; border:0; flex: none; background: transparent">
        <div class="user-header">
            <div class="user-avatar">
                <img  src="<?php echo $channelIMG ?>" height="40" width="40"
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
                            <a class="section-endpoint" href="../auth/logout.php">
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
<div class="watch-container">
    <div class="row">
        <div class="play-video">
            <div class="c-video"><!-- 655 / 360 -- 1325 853/480 -->
                <?php
                echo '<video autoplay class="video" src="../v/' . $Vname . '"  style="left: 0;"></video>';
                ?>
                <div class="video-ended-grid" style="display: none; background-color: unset">
                    <div class="video-ended-grid-content">
                        <a class="video-ended-wall" href="watch.php?query=<?php echo $vids[0][2] ?>"
                           style="left: 0px; top: 0px; width: 218px; height: 121px; transition-delay: 0s;">
                            <div class="video-ended-wall-img"
                                 style="background-image: url(<?php echo $vids[0][3] ?>)"></div>
                            <span class="video-ended-wall-info">
                                <span class="video-ended-wall-info-bg">
                                    <span class="video-ended-wall-info-content" style="will-change: opacity;">
                                        <span class="video-ended-wall-info-title">
                                            <?php echo $vids[0][1] ?>
                                        </span>
                                        <span class="video-ended-wall-info-author">
                                            <?php echo $vids[0][0] . " • " . $vids[0][4] ?>
                                        </span>
                                        <span class="video-ended-wall-info-duration">
                                            <?php //TODO SYF ?>
                                        </span>
                                    </span>
                                </span>
                            </span>
                        </a>
                        <a class="video-ended-wall" href="watch.php?query=<?php echo $vids[1][2] ?>"
                           style="left: 222px; top: 0px; width: 219px; height: 121px; transition-delay: 0.1s;">
                            <div class="video-ended-wall-img"
                                 style="background-image: url(<?php echo $vids[1][3] ?>)"></div>
                            <span class="video-ended-wall-info">
                                <span class="video-ended-wall-info-bg">
                                    <span class="video-ended-wall-info-content" style="will-change: opacity;">
                                        <span class="video-ended-wall-info-title">
                                            <?php echo $vids[1][1] ?>
                                        </span>
                                        <span class="video-ended-wall-info-author">
                                            <?php echo $vids[1][0] . " • " . $vids[1][4] ?>
                                        </span>
                                        <span class="video-ended-wall-info-duration">
                                            <?php //TODO DO ZROBIENIA SYF ?>
                                        </span>
                                    </span>
                                </span>
                            </span>
                        </a>
                        <a class="video-ended-wall" href="watch.php?query=<?php echo $vids[2][2] ?>"
                           style="left: 445px; top: 0px; width: 218px; height: 121px; transition-delay: 0.2s;">
                            <div class="video-ended-wall-img"
                                 style="background-image: url(<?php echo $vids[2][3] ?>)"></div>
                            <span class="video-ended-wall-info">
                                <span class="video-ended-wall-info-bg">
                                    <span class="video-ended-wall-info-content" style="will-change: opacity;">
                                        <span class="video-ended-wall-info-title">
                                            <?php echo $vids[2][1] ?>
                                        </span>
                                        <span class="video-ended-wall-info-author">
                                            <?php echo $vids[2][0] . " • " . $vids[2][4] ?>
                                        </span>
                                        <span class="video-ended-wall-info-duration">
                                            <?php //TODO DO ZROBIENIA SYF ?>
                                        </span>
                                    </span>
                                </span>
                            </span>
                        </a>
                        <a class="video-ended-wall" href="watch.php?query=<?php echo $vids[3][2] ?>"
                           style="left: 667px; top: 0px; width: 219px; height: 121px; transition-delay: 0.3s;">
                            <div class="video-ended-wall-img"
                                 style="background-image: url(<?php echo $vids[3][3] ?>)"></div>
                            <span class="video-ended-wall-info">
                                <span class="video-ended-wall-info-bg">
                                    <span class="video-ended-wall-info-content" style="will-change: opacity;">
                                        <span class="video-ended-wall-info-title">
                                            <?php echo $vids[3][1] ?>
                                        </span>
                                        <span class="video-ended-wall-info-author">
                                            <?php echo $vids[3][0] . " • " . $vids[3][4] ?>
                                        </span>
                                        <span class="video-ended-wall-info-duration">
                                            <?php //TODO DO ZROBIENIA SYF ?>
                                        </span>
                                    </span>
                                </span>
                            </span>
                        </a>
                        <a class="video-ended-wall" href="watch.php?query=<?php echo $vids[4][2] ?>"
                           style="left: 0px; top: 125px; width: 218px; height: 122px; transition-delay: 0.1s;">
                            <div class="video-ended-wall-img"
                                 style="background-image: url(<?php echo $vids[4][3] ?>)"></div>
                            <span class="video-ended-wall-info">
                                <span class="video-ended-wall-info-bg">
                                    <span class="video-ended-wall-info-content" style="will-change: opacity;">
                                        <span class="video-ended-wall-info-title">
                                            <?php echo $vids[4][1] ?>
                                        </span>
                                        <span class="video-ended-wall-info-author">
                                            <?php echo $vids[4][0] . " • " . $vids[4][4] ?>
                                        </span>
                                        <span class="video-ended-wall-info-duration">
                                            <?php //TODO DO ZROBIENIA SYF ?>
                                        </span>
                                    </span>
                                </span>
                            </span>
                        </a>
                        <a class="video-ended-wall" href="watch.php?query=<?php echo $vids[5][2] ?>"
                           style="left: 222px; top: 125px; width: 219px; height: 122px; transition-delay: 0.2s;">
                            <div class="video-ended-wall-img"
                                 style="background-image: url(<?php echo $vids[5][3] ?>)"></div>
                            <span class="video-ended-wall-info">
                                <span class="video-ended-wall-info-bg">
                                    <span class="video-ended-wall-info-content" style="will-change: opacity;">
                                        <span class="video-ended-wall-info-title">
                                            <?php echo $vids[5][1] ?>
                                        </span>
                                        <span class="video-ended-wall-info-author">
                                            <?php echo $vids[5][0] . " • " . $vids[5][4] ?>
                                        </span>
                                        <span class="video-ended-wall-info-duration">
                                            <?php //TODO DO ZROBIENIA SYF ?>
                                        </span>
                                    </span>
                                </span>
                            </span>
                        </a>
                        <a class="video-ended-wall" href="watch.php?query=<?php echo $vids[5][2] ?>"
                           style="left: 445px; top: 125px; width: 218px; height: 122px; transition-delay: 0.3s;">
                            <div class="video-ended-wall-img"
                                 style="background-image: url(<?php echo $vids[6][3] ?>)"></div>
                            <span class="video-ended-wall-info">
                                <span class="video-ended-wall-info-bg">
                                    <span class="video-ended-wall-info-content" style="will-change: opacity;">
                                        <span class="video-ended-wall-info-title">
                                            <?php echo $vids[6][1] ?>
                                        </span>
                                        <span class="video-ended-wall-info-author">
                                            <?php echo $vids[6][0] . " • " . $vids[6][4] ?>
                                        </span>
                                        <span class="video-ended-wall-info-duration">
                                            <?php //TODO DO ZROBIENIA SYF ?>
                                        </span>
                                    </span>
                                </span>
                            </span>
                        </a>
                        <a class="video-ended-wall" href="watch.php?query=<?php echo $vids[6][2] ?>"
                           style="left: 667px; top: 125px; width: 219px; height: 122px; transition-delay: 0.4s;">
                            <div class="video-ended-wall-img"
                                 style="background-image: url(<?php echo $vids[6][3] ?>)"></div>
                            <span class="video-ended-wall-info">
                                <span class="video-ended-wall-info-bg">
                                    <span class="video-ended-wall-info-content" style="will-change: opacity;">
                                        <span class="video-ended-wall-info-title">
                                            <?php echo $vids[6][1] ?>
                                        </span>
                                        <span class="video-ended-wall-info-author">
                                            <?php echo $vids[6][0] . " • " . $vids[6][4] ?>
                                        </span>
                                        <span class="video-ended-wall-info-duration">
                                            <?php //TODO DO ZROBIENIA SYF ?>
                                        </span>
                                    </span>
                                </span>
                            </span>
                        </a>
                        <a class="video-ended-wall" href="watch.php?query=<?php echo $vids[7][2] ?>"
                           style="left: 0px; top: 251px; width: 218px; height: 122px; transition-delay: 0.2s;">
                            <div class="video-ended-wall-img"
                                 style="background-image: url(<?php echo $vids[7][3] ?>)"></div>
                            <span class="video-ended-wall-info">
                                <span class="video-ended-wall-info-bg">
                                    <span class="video-ended-wall-info-content" style="will-change: opacity;">
                                        <span class="video-ended-wall-info-title">
                                            <?php echo $vids[7][1] ?>
                                        </span>
                                        <span class="video-ended-wall-info-author">
                                            <?php echo $vids[7][0] . " • " . $vids[7][4] ?>
                                        </span>
                                        <span class="video-ended-wall-info-duration">
                                            <?php //TODO DO ZROBIENIA SYF ?>
                                        </span>
                                    </span>
                                </span>
                            </span>
                        </a>
                    </div>
                </div>
                <div class="video-ended" style="background-color: unset; display: none">
                    <div class="video-ended-video">
                        <div class="video-ended-text-next">
                            Następny za
                            <span class="video-ended-text-span">10</span>
                        </div>
                        <a class="video-ended-link" href="watch.php?query=<?php echo $vids[0][2] ?>" target>
                            <div class="video-ended-thumbnail"
                                 style="background-image: url(<?php echo $vids[0][3] ?>)"></div>
                            <div class="video-ended-next">
                                <div class="video-ended-text-name">
                                    <?php
                                    echo $vids[0][1];
                                    ?>
                                </div>
                                <div class="video-ended-text-author">
                                    <?php
                                    echo $vids[0][0];
                                    ?>
                                </div>
                                <div class="video-ended-text-viewsdate">
                                    <?php
                                    echo $vids[0][4] . " wyświetleń • " . TimeSwamp($vids[0][5]);
                                    ?>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="video-ended-button">
                        <button class="button-next2" aria-label="Anuluj autoodtwarzanie">Anuluj</button>
                        <a class="button-next" aria-label="Odtwórz następny film"
                           href="watch.php?query=<?php echo $vids[0][2]; ?>">Odtwórz teraz</a>
                    </div>
                </div>
                <div class="controls">
                    <div class="orange-bar">
                        <div class="orange-juice"></div>
                    </div>
                    <div class="buttons">
                        <button id="play-pause" title="play"></button>
                        <span class="current" style="color: white">0:00</span> <span style="color: white">/</span> <span
                                style="color: white" class="duration">0:00</span>
                        <button class="volum" onmouseover="volume.style.setProperty('display', 'inline-block')"
                                style="margin-top: 15px; "><i id="vol"
                                                              class="fa fa-volume-high"
                                                              aria-hidden="true"
                                                              style="color: white"></i>
                        </button>
                        <input type="range" id="volume" onmouseout="volume.style.setProperty('display', 'none')"
                               style="display: none; width: 128px; "
                               min="0" max="1" step="0.01" value="1">
                        <!--<button class="volum" onmouseover="volume.style.setProperty('display', 'block')"><i class="fa fa-volume-down" aria-hidden="true" style="color: white;"></i></button> -->
                        <!--<input type="range" id="volume" min="0" max="1" step="0.01" value="1">-->
                        <button aria-label="Odtwarzaj w pętli" title="Odtwarzaj w pętli" class="loop"
                                style="color: white">
                            <i
                                    title="Odtwarzaj w pętli" id="loop" class="fa fa-toggle-off"></i></button>
                        <button class="fullscreen" style="color:white;"><i class="fas fa-expand"></i></button>
                    </div>
                </div>
            </div>
            <h1 class="title" id="test"><?php echo $name ?></h1>
            <div style="display: flex; justify-content: flex-end;">
                <div class="copied">
                    Skopiowano!
                </div>
            </div>
            <div class="play-video-info">
                <p> <?php echo $views; ?> wyświetleń • <?php
                    echo DateSwamp($data); ?></p>
                <div><!--TODO Z REGULAR NA SOLID PODCZAS LIKE-->
                    <a style="cursor: pointer; text-transform: uppercase"><i
                                class="fa-regular fa-thumbs-up" style="color: white;"></i> <?php echo $likes ?></a>
                    <a style="cursor: pointer; text-transform: uppercase"><i
                                class="fa-regular fa-thumbs-down" style="color: white;"></i> <?php echo $unlikes ?></a>

                    <a style="cursor: pointer; text-transform: uppercase" onclick="copy()"><i class="fas fa-share"></i>
                        Udostępnij
                    </a>
                </div>
            </div>
            <hr>
            <div class="channel-info">
                <!--                data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAMCAggKCgoICA0ICwgICg0KCAgICAoICAoICAgNCggKCQgICgsICggKCAgICAoICg0KCgsKDQoNDQ0KDQgKCgoBAwQEBgUGCgYGChANCw4PEA8QDw8PDw8PEhANEA8PDxAQDQ0NDw8PDxAPDw0PDw8PDQ0PDw8PDw8PDw0NDQ0NDf/AABEIAFgAWAMBEQACEQEDEQH/xAAcAAADAAMBAQEAAAAAAAAAAAAAAQIDBgcEBQn/xAAyEAABAgQDBAkEAwEAAAAAAAABAAIDESHwEjFBBAZhcQUiMlGBkaHB0Qex4fETcoIU/8QAGgEAAwEBAQEAAAAAAAAAAAAAAQIDAAQFBv/EACoRAAICAQQCAQMDBQAAAAAAAAABESECAxIxQQRRYTJxkYGx8BMiI0Kh/9oADAMBAAIRAxEAPwD8wyV6LVWyhRbd/pQb5sulVkOKCvgT5A3fFCKDUjnd8UzyboDlKBvCd2FqETf4U39TN1IwjXYFMDA5LLLtBaqwaUdyasw7+VklFsaWiS1VicUyTtgb5qb7HdBJJioA1AI9DQAbojljQMfTCaDZuRNCHYvyAFLyRmFYcWMIJNukbgbrvzRh9j0igg1FjLKoJcbvVXdccEZfAFTdlETNLwLIw6wllIZWOapkpQJBBuwP0hBLzLbBwEkGv7aGpcm79AfTSJEYIrg4sImGNof9RHUbn2QHO5Liz8rFVj+T0dHwt63ZcejD0x9O9oacTYbw2U5Y2xKAZg9o8pIrydOPqHy8HJXijVo+xuE5hwlnTu4youpZYvs4MsMlyjA4rsytHN3QFqkuHFFNsJEuKm+bBInC/FBKZNfZWFZqAqYHJM7B9xC79kPaAZYDq8vRK1Csrg7Xo7p9Et85vZscTsxXBrSTRrndmvdM5ZVK8XzNJpf1F/IPpPE1nu2vs7TsXRsJzYrJHFCDi8CmHBSbc6GYeW6SOjl4bb+r9D2dqaaZwv6k4oNJuk9pfBeADPMOY7ITDurxEjWq9zx0s1u/KPC8pbODiS+lf/D5eFxAHipNV+pRP2xEKWPyM+4GQm/YDpAHXms/gExyBWyxYeYE1ZqeDL0ZtnGt0U8pVl9OEr4Ng3d6T/iiw4gn1HtdP+rgeWlFzaiWeLXwejpZ7Wvud+3B3/Y8Ro0R0ptOIGk3vJwt4ksoPwvC1/GeMJHt6eunJyr6ib6mK7/nnibAiPLH59RxkGNOeGdR4SpIL1PH0dn+T+fg8vydZZPYcxLV7jU8HzTjsbgkako5TJko4tyGIQ8N6p8bM1FA0FbbCDbtCw3f3Rbaf2BCE0/iqV/BsWuzJCNKIQosfTfo33cLcGNtLHFmA6dsBzTxZ2sJkuHX1sdM97wvHy1k2Pb92togsIc8hhdUMFJykJn00S46uOcV0Wy8bPCZyNNhu6xvxXa2kqPF/wBrPEQu5Y05PObG4eqk8aKzNsTpqfcIznkUllVdmKAvwRdBV2KS2VuZBCVEzu/wlablg3XRk2W78ELZbSrk2ncVgMVs438LgZsdWReOy0yIGE1qTLvzXHrN7XCldnq+E7SeUM2jeeM9kGKXkOxxKEd5JLqc/JceEPJRR6evnGGWU0c2gDXVexkkqPmVOTZ53Nu/ddVOfRzZKwKg1I1qIJcPS6LLGPsDJ0Dr8lsVFsLtfAAftZ4pBxTSHgR7+BXjwyQ1DbMsFobWkXfBaFFMK3J8H0OisQc3C0PdmGkYgeGHzUMouXB26T2tNKT3dN9LzBY1pYC4F7JukHAEEgOPV5fCTak5VwdOtrSmoieT5THKraXs4cXFcmB67HlByMHNU2naY8xyTh5UU1LVMO3uhm79EXYe6AuCDh2hpGEFyxbJCWOzKxzVE6+Q0+TLB2ktMwSCMiCpvHc2h1m8HIR45cZnPUp0pUGybze5uxRH+mqXIVuqJcL8F119RFugmozTZSU6Jv1Ulw12B2wI/fuqKeZDlEiF+6DSVs3FMcslt/sEOV8gwivp+kFMSFO2iiPbl8pm6sJilfgtMQRi5Mk/P2u6oPJclegB1QfMmuKKc1dGS9kofuxZXfBSeLj5KqFZQbeSk3HI+KUQY3Z1TS01AncsqXd99LvVNCiXaGT9Ilp+eNlS6FmRtcE8JfcNOwxXfJZKXL6ZkyS275Jk74JtQM35VS5J+uSkrlFy++vwnfMQbiz/2Q==-->
                <img src="<?php echo $channelIMG; ?>" style="cursor:pointer;"
                     onclick="window.open('/channel?name=', '_self')">
                <div>
                    <p style="cursor:pointer;"
                       onclick="window.open('/channel?name=', '_self')"><?php echo $author;
                        if ($partner) {
                            echo '<i style="color: #0cffff;" class="fa-solid fa-check"></i>';
                        } ?>
                    </p>
                    <span><?php echo $followers; ?></span>
                </div>
                <!-- style="cursor: not-allowed" -->
                <button <?php if ($email == $_SESSION['email'] || !isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) echo 'style="cursor: not-allowed"' ?>>
                    Subskrybuj
                </button>
            </div>
            <hr>
            <div class="div-comments">
                <div class="comments-no">
                    <odt-formatted-string-link>
                        <span style="line-height: 20px; font-size: 14px;">Tymaczasowo nie można komentować</span>
                    </odt-formatted-string-link>
                </div>
            </div>
        </div>
        <div class="right-sidebar">
            <div class="autoodt-button">
                <div class="odt-button-render">
                    <div class="paper-button">
                        <odt-formatted-string style="font-size: 11px;">
                            Autoodtwarzanie
                            <button aria-label="Autoodtwarzanie" title="Autoodtwarzanie" class="loop">
                                <i title="Autoodtwarzanie" id="next" class="fa-solid fa-toggle-off"></i></button>
                        </odt-formatted-string>
                    </div>
                </div>
            </div>
            <?php
            for ($var = 0; $var < sizeof($vids); $var++) {
                echo "<div class='side-video-list' onclick='window.open(\"watch.php?query={$vids[$var][2]}\", \"_self\")'>
                                            <a class='small-thumbnail'><img style='height:90px' src='" . $vids[$var][3] . "'></a>
                                            <div class='video-info'>
                                                <a>{$vids[$var][1]}</a>
                                                <p>{$vids[$var][0]}</p>
                                                <p>{$vids[$var][4]}</p>
                                                <p>" . TimeSwamp($vids[$var][5]) . "</p>
                                            </div>
                                        </div>";
            }
            ?>
        </div>
    </div>
</div>
</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="../base.js"></script>
<script>

    var wideo = document.querySelector('.video')
    var cvideo = document.querySelector('.c-video')
    var juice = document.querySelector('.orange-juice')
    var bar = document.querySelector('.orange-bar')
    var btn = document.getElementById('play-pause')
    var currentTimeElement = document.querySelector('.current')
    var durationTimeElement = document.querySelector('.duration')
    var volume = document.getElementById('volume')
    var volum = document.querySelector('.volum')
    var fullscreen = document.querySelector('.fullscreen')
    var inloop = document.querySelector('.loop')
    var Next = document.getElementById('next');
    var search = document.getElementById('search')
    var isEnded = false;
    var AllTags = document.body.getElementsByTagName("*");
    var JestWinput = true;

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

    function setQuality(quality) {
        var video = document.querySelector('.video');
        video.src = video.src.replace(/\?.*|$/, '?hd=' + quality);
    }

    if (getCookie("next") == "true") {
        Next.classList.replace('fa-toggle-off', 'fa-toggle-on')
    }
    if (getCookie("volume")) {
        volume.value = getCookie("volume")
        wideo.volume = volume.value
        if (parseFloat(volume.value) == 0) {
            document.getElementById('vol').classList.replace('fa-volume-down', 'fa-volume-xmark')
            document.getElementById('vol').classList.replace('fa-volume-high', 'fa-volume-xmark')
        } else if (parseFloat(volume.value) >= 0.50) {
            document.getElementById('vol').classList.replace('fa-volume-down', 'fa-volume-high')
            document.getElementById('vol').classList.replace('fa-volume-xmark', 'fa-volume-high')
        } else if (parseFloat(volume.value) <= 0.50) {
            document.getElementById('vol').classList.replace('fa-volume-high', 'fa-volume-down')
            document.getElementById('vol').classList.replace('fa-volume-xmark', 'fa-volume-down')
        }
    }

    function getCookie(cname) {
        let name = cname + "=";
        let decodedCookie = decodeURIComponent(document.cookie);
        let ca = decodedCookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }

    volume.addEventListener('input',  () => {
        cookieVolume()
    });

    function cookieVolume(){
        document.cookie = "volume=" + volume.value + "; expires=Fri, 31 Dec 9999 23:59:59 GMT; path=/;";
    }

    Next.addEventListener('click', () => {
        if (Next.classList.contains('fa-toggle-off')) {
            Next.classList.replace('fa-toggle-off', 'fa-toggle-on')
            document.cookie = "next=true; expires=Fri, 31 Dec 9999 23:59:59 GMT; path=/;";
        } else if (Next.classList.contains('fa-toggle-on')) {
            Next.classList.replace('fa-toggle-on', 'fa-toggle-off')
            document.cookie = "next=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        }
    })

    /*document.querySelector('.navbar').addEventListener('click', () => {
        hideChannel()
    })

    document.querySelector('.container').addEventListener('click', () => {
        hideChannel()
    })*/

    search.addEventListener('click', () => {
        JestWinput = false;
    })

    search.addEventListener('blur', () => {
        JestWinput = true;
    })

    function copy() {
        document.querySelector('.copied').style.display = 'block'
        var inter = setInterval(function () {
            document.querySelector('.copied').style.display = 'none'
            clearInterval(inter);
        }, 1000);
        navigator.clipboard.writeText(window.location.href);
    }

    function togglePlayPlaouse() {
        if (wideo.paused) {
            btn.className = 'pause'
            wideo.play()
        } else {
            btn.className = 'play'
            wideo.pause()
        }
    }

    var timeleft = 9
    var downloadTimer

    wideo.addEventListener('ended', () => {
        isEnded = true;
        if (Next.classList.contains('fa-toggle-on')) {
            document.querySelector('.video-ended').style.backgroundColor = 'black'
            document.querySelector('.video-ended').style.display = 'flex'
            downloadTimer = setInterval(function () {
                if (timeleft == 0) {
                    document.querySelector('.controls').remove()
                }
                if (timeleft <= -1) {
                    clearInterval(downloadTimer);
                    document.querySelector('.button-next').click()
                } else {
                    document.querySelector('.video-ended-text-span').textContent = timeleft + "";
                }
                timeleft -= 1;
            }, 1000);
        }
    })

    document.querySelector('.button-next2').addEventListener('click', () => {
        isEnded = false;
        document.querySelector('.video-ended').style.backgroundColor = 'none'
        document.querySelector('.video-ended').style.display = 'none'
        document.querySelector('.video-ended-grid').style.display = 'block'
        document.querySelector('.video-ended-grid').style.backgroundColor = 'black'
        timeleft = 10;
        clearInterval(downloadTimer);
        document.querySelector('.video-ended-text-span').textContent = timeleft + "";
    })

    wideo.addEventListener('playing', () => {
        isEnded = false;
        document.querySelector('.video-ended').style.backgroundColor = 'none'
        document.querySelector('.video-ended').style.display = 'none'
        document.querySelector('.video-ended-grid').style.display = 'none'
        document.querySelector('.video-ended-grid').style.backgroundColor = 'unset'
        timeleft = 10;
        clearInterval(downloadTimer);
        document.querySelector('.video-ended-text-span').textContent = timeleft + "";
    })
    document.documentElement.addEventListener('keydown', function (e) {
        if ((e.keycode || e.which) == 32) {
            e.preventDefault();
        }
        if (e.key == "ArrowUp" || e.key == "ArrowDown" || e.key == "ArrowLeft" || e.key == "ArrowRight") {
            e.preventDefault();
        }
    }, false);
    document.onkeyup = (event) => {
        //console.log(event.key)
        //console.log(event.keyCode)
        if (JestWinput) {
            if (event.key == "Escape") {
                hideChannel();
            }
            if (event.key == "f") {
                fullsc()
            }
            if (!isEnded) {
                if (event.key == "ArrowRight") {
                    wideo.currentTime += 5
                } else if (event.key == "ArrowLeft") {
                    wideo.currentTime -= 5
                }
                if (event.keyCode > 47 && event.keyCode < 58) {
                    wideo.currentTime = parseFloat("." + String.fromCharCode(event.keyCode)) * wideo.duration
                }
            }
            if (event.which == 32 || event.key == "k") {
                togglePlayPlaouse();
            }
            if (event.key == "m") {
                if (document.getElementById('vol').classList.contains('fa-volume-high')) {
                    document.getElementById('vol').classList.replace('fa-volume-high', 'fa-volume-xmark')
                    wideo.volume = 0;
                    volume.value = 0;
                    cookieVolume()
                }
                else if (document.getElementById('vol').classList.contains('fa-volume-down')){
                    document.getElementById('vol').classList.replace('fa-volume-down', 'fa-volume-xmark')
                    wideo.volume = 0;
                    volume.value = 0;
                    cookieVolume()
                }
                else if (document.getElementById('vol').classList.contains('fa-volume-xmark')) {
                    document.getElementById('vol').classList.replace('fa-volume-xmark', 'fa-volume-high')
                    wideo.volume = 1;
                    volume.value = 1;
                    cookieVolume()
                }
            }
            if (event.key == "ArrowUp") {
                volume.value += 0.1;
                wideo.volume = volume.value;
            } else if (event.key == "ArrowDown") {
                volume.value -= 0.1;
                wideo.volume = volume.value;
            }
            if (event.shiftKey && event.which == 78) {
                document.querySelector('.button-next').click()
            }
        }
    }

    inloop.addEventListener('click', () => {
        if (wideo.loop == true) {
            wideo.loop = false
            document.getElementById('loop').classList.replace('fa-toggle-on', 'fa-toggle-off')
        } else {
            wideo.loop = true
            document.getElementById('loop').classList.replace('fa-toggle-off', 'fa-toggle-on')
        }
    })

    btn.onclick = function () {
        togglePlayPlaouse();
    }
    wideo.addEventListener('timeupdate', function () {
        var juicePos = wideo.currentTime / wideo.duration
        juice.style.width = juicePos * 100 + "%"
        if (wideo.ended) {
            btn.className = "play";
        }
    })

    bar.addEventListener('click', function (e) {
        var juicePos = e.offsetX / this.offsetWidth
        wideo.currentTime = juicePos * wideo.duration
    })

    var currentTime = () => {
        let currentMinutes = Math.floor(wideo.currentTime / 60)
        let currentSecond = Math.floor(wideo.currentTime - currentMinutes * 60)
        let durationMinute = Math.floor(wideo.duration / 60)
        let durationSeconds = Math.floor(wideo.duration - durationMinute * 60)

        currentTimeElement.innerHTML = `${currentMinutes}:${currentSecond < 10 ? '0' + currentSecond : currentSecond}`
        durationTimeElement.innerHTML = `${durationMinute}:${durationSeconds < 10 ? '0' + durationSeconds : durationSeconds}`
    }
    wideo.addEventListener('timeupdate', currentTime)
    wideo.addEventListener('dblclick', () => {
        fullsc()
    })
    wideo.addEventListener('click', () => {
        togglePlayPlaouse();
    })
    volume.addEventListener('mousemove', (e) => {
        wideo.volume = e.target.value
        if (parseFloat(e.target.value) == 0) {
            document.getElementById('vol').classList.replace('fa-volume-down', 'fa-volume-xmark')
            document.getElementById('vol').classList.replace('fa-volume-high', 'fa-volume-xmark')
        } else if (parseFloat(e.target.value) >= 0.50) {
            document.getElementById('vol').classList.replace('fa-volume-down', 'fa-volume-high')
            document.getElementById('vol').classList.replace('fa-volume-xmark', 'fa-volume-high')
        } else if (parseFloat(e.target.value) <= 0.50) {
            document.getElementById('vol').classList.replace('fa-volume-high', 'fa-volume-down')
            document.getElementById('vol').classList.replace('fa-volume-xmark', 'fa-volume-down')
        }
    })
    fullscreen.addEventListener('click', () => {
        fullsc()
    })
    volum.addEventListener('click', () => {
        if (document.getElementById('vol').classList.contains('fa-volume-high')) {
                document.getElementById('vol').classList.replace('fa-volume-high', 'fa-volume-xmark')
            wideo.volume = 0;
            volume.value = 0;
            cookieVolume()
        } else if (document.getElementById('vol').classList.contains('fa-volume-down')) {
                document.getElementById('vol').classList.replace('fa-volume-down', 'fa-volume-xmark')
            wideo.volume = 0;
            volume.value = 0;
            cookieVolume()
        } else if (document.getElementById('vol').classList.contains('fa-volume-xmark')) {
            document.getElementById('vol').classList.replace('fa-volume-xmark', 'fa-volume-high')
            wideo.volume = 1;
            volume.value = 1;
            cookieVolume()
        }
    })

    function fullsc() {
        if (document.fullscreenElement ||
            document.webkitFullscreenElement ||
            document.mozFullScreenElement ||
            document.msFullscreenElement) {
            wideo.style.width = "688px"
            wideo.style.height = "360px"
            wideo.style.removeProperty('margin-top')
            document.querySelector('.fa-compress').classList.replace('fa-compress', 'fa-expand')
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
        } else {
            element = document.querySelector('.c-video')
            wideo.style.width = window.innerWidth + "px";
            wideo.style.height = window.innerHeight + "px";
            wideo.style.marginTop = "80px"
            document.querySelector('.fa-expand').classList.replace('fa-expand', 'fa-compress')
            if (element.requestFullscreen) {
                element.requestFullscreen();
            } else if (element.mozRequestFullScreen) {
                element.mozRequestFullScreen();
            } else if (element.webkitRequestFullscreen) {
                element.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
            } else if (element.msRequestFullscreen) {
                element.msRequestFullscreen();
            }
        }
    }
</script>
</html>
