<?php
require_once '../../config.php';

$name = $surname = $email = $password = $confirm_password = $birth = $gender = "";
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["_name"]))) {
        $error = "wpisz imię";
    } else {
        $name = trim($_POST['_name']);
    }
    if (empty(trim($_POST["_surname"]))) {
        $error = "wpisz nazwisko";
    } else {
        $surname = trim($_POST['_surname']);
    }
    if (empty(trim($_POST["_email"]))) {
        $error = "wpisz email";
    } else {
        $sql = "SELECT id FROM users WHERE email = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_email);

            $param_email = trim($_POST["_email"]);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $error = "taki email posiada konto.";
                } else {
                    $email = trim($_POST["_email"]);
                }
            }
            mysqli_stmt_close($stmt);
        }
    }
    if (empty(trim($_POST["_password"]))) {
        $error = "Wpisz hasło";
    } else {
        $password = trim($_POST["_password"]);
    }

    if (empty(trim($_POST["_passwordConfirm"]))) {
        $error = "Wpisz hasło";
    } else {
        $confirm_password = trim($_POST["_passwordConfirm"]);
        if (($password != $confirm_password)) {
            $error = "Hasła nie są identyczne.";
        }
    }
    if (empty(trim($_POST["_day"])) || empty(trim($_POST["_month"])) || empty(trim($_POST["_year"]))) {
        $error = "Uzupełnij date";
    } else {
        $birth = trim($_POST["_day"]) . "-" . trim($_POST["_month"]) . "-" . trim($_POST["_year"]);
    }
    if (!isset($_POST['_sex'])) {
        $error = "nie wybrano płci";
    } else {
        $gender = $_POST['_sex'];
    }

    if (empty($error)) {
        $sql = "INSERT INTO users (name,surname,email,password,birth,sex,vkey) VALUES (?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "sssssss", $param_name, $param_surname, $param_email, $param_password, $param_birth, $param_sex, $param_vkey);
            $param_name = $name;
            $param_surname = $surname;
            $param_email = $email;
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            $param_birth = $birth;
            $param_sex = $gender;
            $param_vkey = md5(time() . $email);

            if (mysqli_stmt_execute($stmt)) {
                session_start();
                $_SESSION["loggedin"] = true;
                $to = $email;
                $subject = "Weryfikacja emaila";
                $message = "<a href='http://localhost/players/auth/register/verify.php?vkey=$param_vkey'>Potwierdzenie konta</a>";
                $headers = "From:  \r\n";
                $headers .= "MIME-VERSION: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                mail($to, $subject, $message, $headers);
                header('Location: ../../index.php');
            } else {
                echo "Błąd.";
            }

            mysqli_stmt_close($stmt);
        } else {
            echo "Błąd.";
        }
    }
}
?>
<!doctype html>

<html lang="pl">
<head>
    <meta name="google-signin-scope" content="profile email">
    <meta name="author" content="Adrian Rzeszutek">
    <meta name="google-signin-client_id"
          content="849860593529-855b9vhlapflggtr6rf550gkuchid020.apps.googleusercontent.com">
    <script src="https://apis.google.com/js/platform.js" async defer></script>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
          integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="icon" type="image/x-icon" href="../img/favicon.png">
    <title>Rejestracja - Odtwarzacz</title>
</head>
<style>
    .form {
        justify-content: center !important;
        display: flex;
        flex-wrap: wrap;
        margin-left: -15px;
        margin-right: -15px;
        margin-top: 1rem !important;
    }

    .cont {
        margin: auto;
        padding-left: 15px;
        padding-right: 15px;
        width: 100%;
    }

    body {
        position: relative;
        background-color: #383535;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    *:focus {
        outline: none;
    }
</style>
<body>
<div class="cont">
    <div class="form">
        <div class="col-lg-7 col-md-8 p-4" style="background-color: #424242;">
            <h2 class="mb-2">Zakładanie konta</h2>
            <p class="text-muted">Uzupełnij poniższy formularz</p>
            <hr>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <p class="text-white">Dane Osobowe:</p>
                <div class="mb-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-user"></i></span>
                        </div>
                        <input class="form-control" id="name" type="text" name="_name" placeholder="Imię" required>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-id-badge"></i></span>
                        </div>
                        <input class="form-control" id="surname" type="text" name="_surname" placeholder="Nazwisko"
                               required>
                    </div>
                </div>
                <p class="text-white">Dane kontaktowe:</p>
                <div class="mb-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                        </div>
                        <input class="form-control" id="email" type="email" name="_email" placeholder="Email" required>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-lock"></i></span>
                        </div>
                        <input class="form-control" type="password" name="_password" placeholder="Hasło" required>
                        <input class="form-control" type="password" name="_passwordConfirm" placeholder="Powtórz hasło"
                               required>
                    </div>
                </div>
                <p class="text-white">Data Urodzenia:</p>
                <div class="mb-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                        </div>
                        <select class="form-control" aria-label="Dzień" name="_day" title="Dzień">
                            <option value="1" <?php if (date("d") == "1") {
                                echo 'selected';
                            } ?>>1
                            </option>
                            <option value="2"<?php if (date("d") == "2") {
                                echo 'selected';
                            } ?>>2
                            </option>
                            <option value="3"<?php if (date("d") == "3") {
                                echo 'selected';
                            } ?>>3
                            </option>
                            <option value="4"<?php if (date("d") == "4") {
                                echo 'selected';
                            } ?>>4
                            </option>
                            <option value="5"<?php if (date("d") == "5") {
                                echo 'selected';
                            } ?>>5
                            </option>
                            <option value="6"<?php if (date("d") == "6") {
                                echo 'selected';
                            } ?>>6
                            </option>
                            <option value="7"<?php if (date("d") == "7") {
                                echo 'selected';
                            } ?>>7
                            </option>
                            <option value="8"<?php if (date("d") == "8") {
                                echo 'selected';
                            } ?>>8
                            </option>
                            <option value="9"<?php if (date("d") == "9") {
                                echo 'selected';
                            } ?>>9
                            </option>
                            <option value="10"<?php if (date("d") == "10") {
                                echo 'selected';
                            } ?>>10
                            </option>
                            <option value="11"<?php if (date("d") == "11") {
                                echo 'selected';
                            } ?>>11
                            </option>
                            <option value="12"<?php if (date("d") == "12") {
                                echo 'selected';
                            } ?>>12
                            </option>
                            <option value="13"<?php if (date("d") == "13") {
                                echo 'selected';
                            } ?>>13
                            </option>
                            <option value="14"<?php if (date("d") == "14") {
                                echo 'selected';
                            } ?>>14
                            </option>
                            <option value="15"<?php if (date("d") == "15") {
                                echo 'selected';
                            } ?>>15
                            </option>
                            <option value="16"<?php if (date("d") == "16") {
                                echo 'selected';
                            } ?>>16
                            </option>
                            <option value="17"<?php if (date("d") == "17") {
                                echo 'selected';
                            } ?>>17
                            </option>
                            <option value="18"<?php if (date("d") == "18") {
                                echo 'selected';
                            } ?>>18
                            </option>
                            <option value="19"<?php if (date("d") == "19") {
                                echo 'selected';
                            } ?>>19
                            </option>
                            <option value="20"<?php if (date("d") == "20") {
                                echo 'selected';
                            } ?>>20
                            </option>
                            <option value="21"<?php if (date("d") == "21") {
                                echo 'selected';
                            } ?>>21
                            </option>
                            <option value="22"<?php if (date("d") == "22") {
                                echo 'selected';
                            } ?>>22
                            </option>
                            <option value="23"<?php if (date("d") == "23") {
                                echo 'selected';
                            } ?>>23
                            </option>
                            <option value="24"<?php if (date("d") == "24") {
                                echo 'selected';
                            } ?>>24
                            </option>
                            <option value="25"<?php if (date("d") == "25") {
                                echo 'selected';
                            } ?>>25
                            </option>
                            <option value="26"<?php if (date("d") == "26") {
                                echo 'selected';
                            } ?>>26
                            </option>
                            <option value="27"<?php if (date("d") == "27") {
                                echo 'selected';
                            } ?>>27
                            </option>
                            <option value="28"<?php if (date("d") == "28") {
                                echo 'selected';
                            } ?>>28
                            </option>
                            <option value="29"<?php if (date("d") == "29") {
                                echo 'selected';
                            } ?>>29
                            </option>
                            <option value="30"<?php if (date("d") == "30") {
                                echo 'selected';
                            } ?>>30
                            </option>
                            <option value="31"<?php if (date("d") == "31") {
                                echo 'selected';
                            } ?>>31
                            </option>
                        </select>
                        <select class="form-control" aria-label="Miesiąc" name="_month" title="Miesiąc">
                            <option value="1"<?php if (date("m") == "1") {
                                echo 'selected';
                            } ?>>sty
                            </option>
                            <option value="2"<?php if (date("m") == "2") {
                                echo 'selected';
                            } ?>>lut
                            </option>
                            <option value="3"<?php if (date("m") == "3") {
                                echo 'selected';
                            } ?>>mar
                            </option>
                            <option value="4"<?php if (date("m") == "4") {
                                echo 'selected';
                            } ?>>kwi
                            </option>
                            <option value="5"<?php if (date("m") == "5") {
                                echo 'selected';
                            } ?>>maj
                            </option>
                            <option value="6"<?php if (date("m") == "6") {
                                echo 'selected';
                            } ?>>cze
                            </option>
                            <option value="7"<?php if (date("m") == "7") {
                                echo 'selected';
                            } ?>>lip
                            </option>
                            <option value="8"<?php if (date("m") == "8") {
                                echo 'selected';
                            } ?>>sie
                            </option>
                            <option value="9"<?php if (date("m") == "9") {
                                echo 'selected';
                            } ?>>wrz
                            </option>
                            <option value="10"<?php if (date("m") == "10") {
                                echo 'selected';
                            } ?>>paź
                            </option>
                            <option value="11"<?php if (date("m") == "11") {
                                echo 'selected';
                            } ?>>lis
                            </option>
                            <option value="12"<?php if (date("m") == "12") {
                                echo 'selected';
                            } ?>>gru
                            </option>
                        </select>
                        <select class="form-control" aria-label="Rok" name="_year" title="Rok">
                            <option value="2021" selected>2021</option>
                            <option value="2020">2020</option>
                            <option value="2019">2019</option>
                            <option value="2018">2018</option>
                            <option value="2017">2017</option>
                            <option value="2016">2016</option>
                            <option value="2015">2015</option>
                            <option value="2014">2014</option>
                            <option value="2013">2013</option>
                            <option value="2012">2012</option>
                            <option value="2011">2011</option>
                            <option value="2010">2010</option>
                            <option value="2009">2009</option>
                            <option value="2008">2008</option>
                            <option value="2007">2007</option>
                            <option value="2006">2006</option>
                            <option value="2005">2005</option>
                            <option value="2004">2004</option>
                            <option value="2003">2003</option>
                            <option value="2002">2002</option>
                            <option value="2001">2001</option>
                            <option value="2000">2000</option>
                            <option value="1999">1999</option>
                            <option value="1998">1998</option>
                            <option value="1997">1997</option>
                            <option value="1996">1996</option>
                            <option value="1995">1995</option>
                            <option value="1994">1994</option>
                            <option value="1993">1993</option>
                            <option value="1992">1992</option>
                            <option value="1991">1991</option>
                            <option value="1990">1990</option>
                            <option value="1989">1989</option>
                            <option value="1988">1988</option>
                            <option value="1987">1987</option>
                            <option value="1986">1986</option>
                            <option value="1985">1985</option>
                            <option value="1984">1984</option>
                            <option value="1983">1983</option>
                            <option value="1982">1982</option>
                            <option value="1981">1981</option>
                            <option value="1980">1980</option>
                            <option value="1979">1979</option>
                            <option value="1978">1978</option>
                            <option value="1977">1977</option>
                            <option value="1976">1976</option>
                            <option value="1975">1975</option>
                            <option value="1974">1974</option>
                            <option value="1973">1973</option>
                            <option value="1972">1972</option>
                            <option value="1971">1971</option>
                            <option value="1970">1970</option>
                            <option value="1969">1969</option>
                            <option value="1968">1968</option>
                            <option value="1967">1967</option>
                            <option value="1966">1966</option>
                            <option value="1965">1965</option>
                            <option value="1964">1964</option>
                            <option value="1963">1963</option>
                            <option value="1962">1962</option>
                            <option value="1961">1961</option>
                            <option value="1960">1960</option>
                            <option value="1959">1959</option>
                            <option value="1958">1958</option>
                            <option value="1957">1957</option>
                            <option value="1956">1956</option>
                            <option value="1955">1955</option>
                            <option value="1954">1954</option>
                            <option value="1953">1953</option>
                            <option value="1952">1952</option>
                            <option value="1951">1951</option>
                            <option value="1950">1950</option>
                            <option value="1949">1949</option>
                            <option value="1948">1948</option>
                            <option value="1947">1947</option>
                            <option value="1946">1946</option>
                            <option value="1945">1945</option>
                            <option value="1944">1944</option>
                            <option value="1943">1943</option>
                            <option value="1942">1942</option>
                            <option value="1941">1941</option>
                            <option value="1940">1940</option>
                            <option value="1939">1939</option>
                            <option value="1938">1938</option>
                            <option value="1937">1937</option>
                            <option value="1936">1936</option>
                            <option value="1935">1935</option>
                            <option value="1934">1934</option>
                            <option value="1933">1933</option>
                            <option value="1932">1932</option>
                            <option value="1931">1931</option>
                            <option value="1930">1930</option>
                            <option value="1929">1929</option>
                            <option value="1928">1928</option>
                            <option value="1927">1927</option>
                            <option value="1926">1926</option>
                            <option value="1925">1925</option>
                            <option value="1924">1924</option>
                            <option value="1923">1923</option>
                            <option value="1922">1922</option>
                            <option value="1921">1921</option>
                            <option value="1920">1920</option>
                            <option value="1919">1919</option>
                            <option value="1918">1918</option>
                            <option value="1917">1917</option>
                            <option value="1916">1916</option>
                            <option value="1915">1915</option>
                            <option value="1914">1914</option>
                            <option value="1913">1913</option>
                            <option value="1912">1912</option>
                            <option value="1911">1911</option>
                            <option value="1910">1910</option>
                            <option value="1909">1909</option>
                            <option value="1908">1908</option>
                            <option value="1907">1907</option>
                            <option value="1906">1906</option>
                            <option value="1905">1905</option>
                        </select>
                    </div>
                </div>
                <p class="text-white">Płeć:</p>
                <div class="mb-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-venus-mars"></i></span>
                        </div>
                        <span class="form-control">
                            <label>Kobieta</label>
                            <input type="radio" name="_sex" value="1">
                        </span>
                        <span class="form-control">
                            <label>Mężczyzna</label>
                            <input type="radio" name="_sex" value="2">
                        </span>
                    </div>
                </div>
                <div class="font-sm text-muted mb-4 px-1">
                    <div class="mb-1">
                        <p class="text-left"><i class="fa fa-exclamation-triangle"
                                                style="width: 16px; text-align: left"></i>
                            Rejestrując się potwierdzasz, że zapoznałeś się z <a target="_blank"
                                                                                 class="text-white btn-link px-0"
                                                                                 href="/tos">Regulaminem</a> i
                            akceptujesz jego warunki.</p>
                    </div>
                    <div>
                        <p class="text-left"><i class="fa fa-info" style="width: 16px; text-align: left;"></i>
                            Administratorem Twoich danych jest ????.<a target="_blank" class="text-white btn-link px-0"
                                                                       href="/privacy"> Więcej o ochronie danych</a>.
                        </p>
                    </div>
                </div>
                <button type="submit" class="btn btn-block btn-success">Załóż konto</button>
            </form>
            <hr>
            <?php
            if (!empty($error)) {
                echo '<div class="alert-danger alert">Błąd: ' . $error . '</div>';
            }
            ?>
            <div class="g-signin2" data-onsuccess="onSignIn" data-theme="dark"></div>
        </div>
    </div>
</div>
<!--<a href="#" onclick="signOut();">Sign out</a>-->
<script>
    function signOut() {
        var auth2 = gapi.auth2.getAuthInstance();
        auth2.signOut().then(function () {
            console.log('User signed out.');
        });
    }

</script>
<script>
    function onSignIn(googleUser) {
        // Useful data for your client-side scripts:
        var profile = googleUser.getBasicProfile();
        //console.log("ID: " + profile.getId()); // Don't send this directly to your server!
        //console.log('Full Name: ' + profile.getName());
        //console.log('Given Name: ' + profile.getGivenName());
        //console.log('Family Name: ' + profile.getFamilyName());
        //console.log("Image URL: " + profile.getImageUrl());
        //console.log("Email: " + profile.getEmail());
        document.getElementById('name').value = profile.getName()
        document.getElementById('surname').value = profile.getFamilyName()
        document.getElementById('email').value = profile.getEmail()
        // The ID token you need to pass to your backend:
        var id_token = googleUser.getAuthResponse().id_token;
        //console.log("ID Token: " + id_token);
    }
</script>
</body>
</html>
