<?php
/**
 * Created by PhpStorm.
 * User: pusty
 * Date: 31.05.2018
 * Time: 00:24
 */
try {
    $dbh = new PDO("mysql:host=localhost:3306;dbname=bans", 'bans', 'pwd');
    // set the PDO error mode to exception
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->exec("set names utf8");
}
catch(PDOException $e)
{
    echo "Connection failed: " . $e->getMessage();
}
try {
    $dba = new PDO("mysql:host=localhost:3306;dbname=propa_admins", 'propa_admin', 'pwd');
    // set the PDO error mode to exception
    $dba->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dba->exec("set names utf8");
}
catch(PDOException $e)
{
    echo "Connection failed: " . $e->getMessage();
}
function validate_ip($ip)
{
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
        return false;
    }
    return true;
}
function getUserIP()
{
    $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                // trim for safety measures
                $ip = trim($ip);
                // attempt to validate IP
                if (validate_ip($ip)) {
                    return $ip;
                }
            }
        }
    }
    return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
}

?>
<!DOCTYPE html>
<html lang="en" class="no-js">

<head>
    <meta charset="utf-8">
    <title>GOPROPA.pl - PROPA BANS</title>
    <meta name="description" content="Nowy system banowania i nadawania adminów stworzony przez ITKreatywni.pl & Fabkoo">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="ITKreatywni.pl">

    <!-- ================= Favicons ================== -->
    <link rel="shortcut icon" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAABdElEQVQ4T8VTS04bQRSs6sExEQpuoshIYdPZID5K6D1RpNwg3MC+gW8wyg3CCUxu4JzAXiC2diIlZsU0GxZs3OAfYzP9orFEFghYMIu83XvSK9WrqkcULBbcR3GAS7NrgcxW3enRfTYDY/Wa6/mHWB6bveZH97POgdkyKdkdSnRQgfiq+927W0jMbqOM4N66fiuftY3VAIxGZidkcz/5RV6Y7UYg4wmoM2FnVQW3cfannhirU8y7ywzOJP3P3UUf2q+RYQJlZ0QnCH/w3Ow0h1S1XIxUiAqCnxPupQhuSLsE4FrUtxLCF0WasghS0Jco+krU0eKEMVTsqWpTIUqAjyh6LsQKA15AIELcAihRvBLoKelHoloposN/LlyardoEUXOs2BoFfhcwfoVgNbPcqk4m0dchEY+Fdoqo/sn1Fro8amMuWAXZYIO3bj3pv3ssL0/m4MS8b2+quXtzdlp/FsCx+dDYxMw/lJE7wOJJ/P+/UJTBXz9bmZQct3s8AAAAAElFTkSuQmCC" type="image/png">
    <!-- ============== Resources style ============== -->
    <link rel="stylesheet" type="text/css" href="css/style.css" />

    <!-- Modernizr runs quickly on page load to detect features -->
    <script src="js/modernizr.custom.js"></script>
</head>

<body>

<!-- *** LOADING *** -->

<div id="loading">

    <div class="loader">
        <span class="dots">	.</span>
        <span class="dots">	.</span>
        <span class="dots">	.</span>
        <span class="dots">	.</span>
        <span class="dots">	.</span>
        <span class="dots">	.</span>
        <p class="loader__label">GOPROPA.pl | <span data-words="Karmienie chomika|Zamiatanie pustyni|Wkurzanie Aimera|Tworzenie bugów|Usuwanie nerki"></span></p>
    </div>
</div>

<button id="small-screen-menu">
    <span class="custom-menu"></span>
</button>
<canvas id="constellationel"></canvas>
<div id="constellation"></div>
<div class="custom-overlay"></div>
<a class="brand-logo" href="/">
    <img src="img/logo.png" alt="Our company logo" class="img-fluid" />
</a>
<div id="fullpage">
    <div class="section" id="section0">
        <section class="content-inside-section">
            <div class="container">
                <div class="container-inside">
                    <div class="main-content align-center">
                        <?php
                        $ipaddress = getUserIP();

                        $query = $dbh->prepare('SELECT * FROM sb_bans WHERE ip="'.$ipaddress.'"');
                        $query->execute();
                        if($query->rowCount()){
                            $row = $query->fetch();
                            $datetime1 = new DateTime(date("Y-m-d H:i:s",$row['created']));//start time
                            $datetime2 = new DateTime(date("Y-m-d H:i:s",$row['ends']));//end timez
                            $interval = $datetime1->diff($datetime2);
                            $diff = (int)$interval->format("%r%a");
                            $datediff = date("Y/m/d H:i:s",$row['ends']);
                            if(!$diff){
                                ?>
                                <h1 class="green_label">
                                    Ufff...<br>
                                    Jesteś czysty!
                                </h1>
                                <p class="on-home">Na szczęście nie znaleźliśmy żadnych aktywnych banów dla Twojego adresu IP :)
                                </p>
                                <?php
                            } else {
                                ?>
                                <h1 class="red_label">
                                    GRATULACJE!<br>
                                    Jesteś zbanowany :(
                                </h1>
                        <p class="on-home">Ojej :( wygląda na to, że na Twój adres ip został nałożony ciągle aktywny ban :(</p>
                        <p class="on-home" style="font-size: 28px;">Zostałeś zbanowany na: <?php if($row['created'] == $row['ends']) echo '<span class="badge badge-danger">PERMANENT'; else  {
                            if($row['length'] < 3600) echo '<span class="badge badge-danger">'.date("i",$row['length']).' min.';
                            else if($row['length'] >=3600 && $row['length'] < 86400) echo '<span class="badge badge-danger">'.date("G",$row['length']).' godz.';
                            else echo '<span class="badge badge-danger">'.date("j",$row['length'])." dni";
                        }?></span> z powodu: <strong><?php echo $row['reason']; ?></strong>
                    <br />Twój ban skończy się za: <strong class="badge badge-danger"><span id="getting-started"></span></strong>
                    </p>
                        <?php
                            }
                            ?>

                        <?php
                        } else {
                        ?>
                            <h1 class="green_label">
                                Ufff...<br>
                                Jesteś czysty!
                            </h1>
                            <p class="on-home">Na szczęście nie znaleźliśmy żadnych aktywnych banów dla Twojego adresu IP :)
                            </p>
                        <?php } ?>
                        <br>
                        <div class="command">
                            <a class="trigger light-btn colored">
                                <span id="first-text">Twój adres ip jest tutaj!</span>

                                <span id="second-text">IP: <?php echo $ipaddress; ?></span>
                            </a>
                            <a id="button-more" class="light-btn" href="#banlist">Zobacz najnowsze bany!
                                <span class="ask-to-scroll">
											<span class="arrow"><span></span><span></span></span>
											<span class="arrow"><span></span><span></span></span>
											<span class="arrow"><span></span><span></span></span>
										</span>
                            </a>
                            <div class="clear"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <div class="section" id="section1">
        <section class="content-inside-section">
            <div class="container-fluid">
                <div class="container-inside">
                    <div class="main-content">
                        <span class="section-name">GOPROPA.pl</span>
                        <h2>Lista ostatnich banów / blokad</h2>
                        <span class="separator"></span>
                        <div class="row" id="banlista">

                            <div class="col-12 col-lg-5 ">
                                <span class="section-name">Ostatnie BANY</span>
                                <?php
                                $query = $dbh->prepare('SELECT * FROM sb_bans ORDER BY created DESC LIMIT 10');
                                $query->execute();
                                if($query->rowCount()) $rows = $query->fetchAll();
                                foreach($rows as $row){
                                    ?>
                                    <div class="row">
                                        <div class="col-sm-4"><?php echo date("H:i d-m-Y",$row['created']); ?></div>
                                        <div class="col-sm-6"><a href="#" data-toggle="tooltip" data-placement="top" title="<span style='font-size: 15px;'><?php echo $row['reason']; ?></span>"><?php if(strlen($row['name']) > 20) echo substr($row['name'],0,20).'...'; else echo $row['name']; ?></a></div>
                                        <div class="col-sm-1 text-right"><?php
                                                $datetime1 = new DateTime(date("Y-m-d H:i:s",$row['created']));//start time
                                                $datetime2 = new DateTime(date("Y-m-d H:i:s",$row['ends']));//end timez
                                                $interval = $datetime1->diff($datetime2);
                                                $diff = (int)$interval->format("%r%a");
                                                if($row['created'] == $row['ends']) echo '<div class="badge badge-danger">PERMANENT';
                                                else if(!$diff){
                                                    echo '<div class="badge badge-success">WYGASŁ';
                                                }
                                                else  {
                                                    if($row['length'] < 3600) echo '<div class="badge badge-danger">'.date("i",$row['length']).' min.';
                                                    else if($row['length'] >=3600 && $row['length'] < 86400) echo '<div class="badge badge-danger">'.date("G",$row['length']).' godz.';
                                                    else echo '<div class="badge badge-danger">'.date("j",$row['length'])." dni";
                                                }
                                                ?></div>
                                    </div>
                                    </div>
                                <?php
                                }
                                ?>

                            </div>

                        <div class="col-12 col-lg-5 offset-lg-1">
                            <span class="section-name">Ostatnie WYCISZENIA</span>
                            <?php
                            $query = $dbh->prepare('SELECT * FROM sb_comms ORDER BY created DESC LIMIT 10');
                            $query->execute();
                            if($query->rowCount()) $rows = $query->fetchAll();
                            foreach($rows as $row){
                            ?>
                            <div class="row">
                                <div class="col-sm-4"><?php echo date("H:i d-m-Y",$row['created']); ?></div>
                                <div class="col-sm-6">
                                    <a href="#" data-toggle="tooltip" data-placement="top" title="<span style='font-size: 15px;'><?php echo $row['reason']; ?></span>">
                                        <span class="badge badge-info"><?php if($row['type'] == 1) echo '<i class="fa fa-volume-off"></i>'; else echo '<i class="fa fa-edit"></i>'; ?></span>
                                        <?php if(strlen($row['name']) > 20) echo substr($row['name'],0,20).'...'; else echo $row['name']; ?>
                                    </a>
                                </div>
                                <div class="col-sm-1 text-right"><?php
                                    $datetime1 = new DateTime(date("Y-m-d H:i:s",$row['created']));//start time
                                    $datetime2 = new DateTime(date("Y-m-d H:i:s",$row['ends']));//end timez
                                    $interval = $datetime1->diff($datetime2);
                                    $diff = (int)$interval->format("%r%a");
                                    if($row['created'] == $row['ends']) echo '<div class="badge badge-danger">PERMANENT';
                                    else if(!$diff){
                                        echo '<div class="badge badge-success">WYGASŁ';
                                    }
                                    else  {
                                        if($row['length'] < 3600) echo '<div class="badge badge-danger">'.date("i",$row['length']).' min.';
                                        else if($row['length'] >=3600 && $row['length'] < 86400) echo '<div class="badge badge-danger">'.date("G",$row['length']).' godz.';
                                        else echo '<div class="badge badge-danger">'.date("j",$row['length'])." dni";
                                    }
                                    ?></div></div>
                        </div>
                        <?php
                        }
                        ?>

                    </div>
                            <div class="col-12 text-center">
                                <div class="form-inline">
                                    <input type="text" id="steamid" name="steamid" class="form-control form-inline email srequiredField btn-light-propa" placeholder="Wyszukaj bany po steamid" style="width: calc(100% - 200px); margin-right: 5px;" />
                                    <button type="submit" id="submit" class="btn btn-outline-danger btn-light-propa">Szukaj!</button>
                                </div>
                            </div>
                        </div>
                <div class="row" id="searchbans">
                   <div id="content_of_bans" class="col-12"></div>
                </div>
                    </div>
                </div>
            </div>
        </section>

    </div>
    <div class="section" id="section2">
        <section class="content-inside-section">
            <div class="container-fluid">
                <div class="container-inside">
                    <div class="main-content">
                        <span class="section-name">GOPROPA.pl</span>
                        <h2>LISTA ADMINÓW</h2>
                        <span class="separator"></span>
                        <div class="row">
                            <?php
                            echo '<div class="col-12 col-lg-3">';
                            try{

                                $stmt = 'SELECT * FROM servers';
                                $query = $dba->prepare($stmt);
                                $query->execute();
                                $ile = $query->rowCount();
                                $servers = $query->fetchAll();
                                $count = round($ile/4,0);
                                $i = 1;
                                foreach($servers as $server){
                                    $stmt = 'SELECT * FROM tAdmin WHERE server_id = "'.$server['server_id'].'"';
                                    $query = $dba->prepare($stmt);
                                    $query->execute();
                                    $admins = $query->fetchAll();
                                    foreach($admins as $admin){
                                        ?>
                                        <span class="badge badge-danger"><?php echo $server['server_tag']; ?></span> <?php echo $admin['playername']; ?><br />
                                        <?php
                                    }
                                    if($i == 2) {
                                        $i = 1;
                                        echo '</div><div class="col-12 col-lg-3">';
                                    } else $i++;
                                }
                                echo $count;
                            } catch(PDOException $e) {
                                echo $e->getMessage();
                            }
                            catch(Exception $e) {
                                echo $e->getMessage();
                            }
                            echo '</div>';
                            ?>

                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>
</div>

<footer>
    <div class="line"></div>
    <div class="row">
        <div class="col-12 col-xl-4 footer-nav">
            <ul id="bottomMenu" class="on-left">
                <li data-menuanchor="start" class="active">
                    <a href="#start">START</a>
                </li>

                <li data-menuanchor="banlist">
                    <a href="#banlist">LISTA BANÓW</a>
                </li>

                <li data-menuanchor="adminlist">
                    <a href="#adminlist">LISTA ADMINÓW</a>
                </li>
                <li>
                    <a class="trigger" href="https://forum.gopropa.pl">PRZEJDŹ DO FORUM!</a>
                </li>
            </ul>
        </div>
        <div class="col-12 col-xl-4 footer-copyright">
            <p>Wykonanie: <a href="https://itkreatywni.pl" target="_blank">ITKREATYWNI</a></p>
        </div>
        <div class="col-12 col-xl-4 footer-nav">
            <ul class="on-right">
                <li>
                    <a href="https://www.facebook.com/gopropacommunity" target="_blank"><i class="fab fa-facebook-f"></i></a>
                </li>
            </ul>
        </div>
    </div>
</footer>

<!-- ///////////////////\\\\\\\\\\\\\\\\\\\ -->
<!-- ********** jQuery Resources ********** -->
<!-- \\\\\\\\\\\\\\\\\\\/////////////////// -->

<!-- * Libraries jQuery, Easing and Bootstrap - Be careful to not remove them * -->
<script src="js/jquery.min.js"></script>
<script src="js/jquery.easings.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="js/bootstrap.min.js"></script>

<!-- Countdown plugin -->
<script src="js/jquery.countdown.js"></script>

<!-- FullPage plugin -->
<script src="js/jquery.fullPage.js"></script>

<!-- Constellation plugin -->
<script src="js/constellation.js"></script>

<!-- Contact form plugin -->
<script src="js/contact-me.js"></script>

<!-- Popup Newsletter Form -->
<script src="js/classie.js"></script>
<script src="js/dialogFx.js"></script>

<!-- Newsletter plugin -->
<script src="js/notifyMe.js"></script>

<!-- Gallery plugin -->
<script src="js/jquery.detect_swipe.min.js"></script>
<script src="js/featherlight.js"></script>
<script src="js/featherlight.gallery.js"></script>

<!-- Main JS File -->
<script src="js/main.js"></script>
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip({
            html: true
        })
    });

    $("#getting-started").countdown(<?php echo json_encode($datediff, JSON_HEX_TAG); ?>, function(a) {
        $(this).html(a.strftime('%D dni %H:%M:%S'));
    });
    $("#searchbans").toggle();
</script>
<script>
    $(document).ready(function(){
        function checkUrl(url){
            var request = new XMLHttpRequest;
            request.open('GET', url, true);
            request.send();
            request.onreadystatechange = function(){
                if(request.status==200){
                    console.log("istnieje");
                    return true;
                }else{
                    return false;
                }
            }
        };
        $("#submit").click(function(){
            var steamid = $("#steamid").val();
// Returns successful data submission message when the entered information is stored in database.
            var dataString = 'steamid='+ steamid;
            if(steamid==='')
            {
                alert("Nie podałeś steamid!");
            }
            else
            {
// AJAX Code To Submit Form.
                    $.ajax({
                        type: "GET",
                        url: "https://bany.gopropa.pl/steamsearch.php",
                        data: dataString,
                        cache: false,
                        success: function(result){
                            $("#banlista").toggle();
                            $("#content_of_bans").html(result);
                            $("#searchbans").toggle();

                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            alert(xhr.status);
                            alert(thrownError);
                        }
                    });


            }
            return false;
        });
    });
</script>
</body>

</html>
