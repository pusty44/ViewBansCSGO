<?php
/**
 * Created by PhpStorm.
 * User: pusty
 * Date: 01.06.2018
 * Time: 01:04
 */
// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}
// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

}
try {
    $dbh = new PDO("mysql:host=localhost:3306;dbname=bans", 'bans', 'pwd');
    // set the PDO error mode to exception
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->exec("set names utf8");
    if($_SERVER['REQUEST_METHOD'] == 'GET'){
        $query = $dbh->prepare('SELECT * FROM sb_bans WHERE authid=:steamid');
        $query->bindValue(':steamid',$_GET['steamid'],PDO::PARAM_STR);
        $query->execute();
        if($query->rowCount()){
            $rows = $query->fetchAll();
            ?>
            <div class="row text-center">
                <div class="col-sm-1"></div>
                <div class="col-sm-2"><strong>DATA BANA</strong></div>
                <div class="col-sm-6"><strong>NICK GRACZA / STEAM ID</strong></div>
                <div class="col-sm-3"><strong>POWÓD</strong></div>
            </div>
            <?php
            foreach($rows as $row){
                ?>
                <div class="row text-center">
                    <div class="col-sm-1">
                        <?php
                        $datetime1 = new DateTime(date("Y-m-d H:i:s",$row['created']));//start time
                        $datetime2 = new DateTime(date("Y-m-d H:i:s",$row['ends']));//end timez
                        $interval = $datetime1->diff($datetime2);
                        $diff = (int)$interval->format("%r%a");
                        if($row['created'] == $row['ends']) echo '<div class="badge badge-danger">PERMANENT</div>';
                        else if(!$diff){
                            echo '<div class="badge badge-success">WYGASŁ</div>';
                        }
                        else  {
                            if($row['length'] < 3600) echo '<div class="badge badge-danger">'.date("i",$row['length']).' min.</div>';
                            else if($row['length'] >=3600 && $row['length'] < 86400) echo '<div class="badge badge-danger">'.date("G",$row['length']).' godz.</div>';
                            else echo '<div class="badge badge-danger">'.date("j",$row['length'])." dni</div>";
                        }
                        ?>
                    </div>
                    <div class="col-sm-2"><?php echo date("H:i d-m-Y",$row['created']); ?></div>
                    <div class="col-sm-6">
                        <a href="#" data-toggle="tooltip" data-placement="top" title="<span style='font-size: 15px;'><?php echo $row['reason']; ?></span>">
                            <span class="badge badge-info"><?php if($row['type'] == 1) echo '<i class="fa fa-volume-off"></i>'; else echo '<i class="fa fa-edit"></i>'; ?></span>
                            <?php echo $row['name'].' SID:'.$row['authid']; ?>
                        </a>
                    </div>
                    <div class="col-sm-3">
                        <?php echo $row['reason']; ?>
                    </div>
                </div>
                <?php
            }
        } else { ?>
        <div class="row"><h3 class="green_label">BRAK BANÓW</h3></div>
        <?php }
    } else echo 'BAD REQUEST';
}
catch(PDOException $e)
{
    echo "Connection failed: " . $e->getMessage();
}
?>