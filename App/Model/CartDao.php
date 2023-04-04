<?php namespace App\Model;

use App\Lib\Database;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
  
class CartDao
{
    public static function randomTermekek()
    {
        if (isset($_SESSION['kosar']) && count($_SESSION['kosar'])>0) {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();
        $felt='';
        $dbok=[];
        $n=count($_SESSION['kosar']);
        foreach ($_SESSION["kosar"] as $kosar) {
            $id=$kosar["id"];
            $meretId=$kosar["meret"];
            $kulcs=$id.'_'.$meretId;
            $felt.="(tm.termek_id='$id' AND tm.meret_id = '$meretId') OR ";
            $dbok[$kulcs]=$kosar["db"];
        }
        $felt.="0";
        $sql = "SELECT t.*, m.megnevezes AS meret_nev, ma.markanev, m.id AS meret_id
                FROM `termek` AS t
                INNER JOIN `termek_meretek` AS tm ON tm.termek_id=t.id 
                INNER JOIN `meretek` AS m ON m.id = tm.meret_id
                INNER JOIN `markak` AS ma ON ma.id=t.marka_id
                WHERE $felt;";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $ret=[];
        $ret['termek']=$statement->fetchAll();
        $ret['dbok']=$dbok;
        return $ret;
        }else {
            return false;
        }
    }

    public static function cartPay() {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $id = $_SESSION["user_id"];
        $sql = "SELECT * FROM felhasznalo_szallitasi_adatok WHERE felhasznalo_id='$id';";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $szallitasiAdat = $statement->fetch();

        if (empty($szallitasiAdat->szallitasi_cim1)) {
            echo "
                    <script src='
                    https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.all.min.js
                    '></script>
                    <script src='/App/js/jquery-3.6.3.min.js'></script>
                    <script type='text/javascript'>
                    $(document).ready(function(){
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Töltse ki a szállítási adatokat!'
                        }).then((result) => {
                            if(result.isConfirmed) {
                                window.history.back();
                            } else {
                                window.history.back();
                            }
                        })
                    });
                    </script>";
        } else {
            $osszAr = 0;

            if (count($_SESSION["kosar"]) == 0) {
                echo "
                        <script src='
                        https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.all.min.js
                        '></script>
                        <script src='/App/js/jquery-3.6.3.min.js'></script>
                        <script type='text/javascript'>
                        $(document).ready(function(){
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Üres a kosár!'
                            }).then((result) => {
                                if(result.isConfirmed) {
                                    window.history.back();
                                } else {
                                    window.history.back();
                                }
                            })
                        });
                        </script>";
            } else {
                if (count($_SESSION["kosar"]) == 1) {
                    $ar = $_SESSION["kosar"][0]["ar"];
                    $db = $_SESSION["kosar"][0]["db"];
                    $osszAr += $ar*$db;
                } else {
                    for ($i=0; $i < count($_SESSION); $i++) {
                        $ar = $_SESSION["kosar"][$i]["ar"];
                        $db = $_SESSION["kosar"][$i]["db"];
                        $osszAr += $ar*$db;
                    }
                }

                if ($osszAr >= 1000000) {
                    echo "
                            <script src='
                            https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.all.min.js
                            '></script>
                            <script src='/App/js/jquery-3.6.3.min.js'></script>
                            <script type='text/javascript'>
                            $(document).ready(function(){
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: 'A vásárlási összeg nem lehet 1 millió Ft vagy a felett'
                                }).then((result) => {
                                    if(result.isConfirmed) {
                                        window.history.back();
                                    } else {
                                        window.history.back();
                                    }
                                })
                            });
                            </script>";
                } else {
                    require_once('vendor/autoload.php');
                    \Stripe\Stripe::setApiKey('sk_test_51MmCQDKlmNolOX5eJh2USNi7hPiIOdj3gE4bvPDMzWpeA6d6Zhxi124BXllkVXr09pYoaGSgkMFJOJmqzwmGDTbe0098xMpPhK');
        
                    $nevek = $_POST["nevek"];
                    $termekek = array();
        
                    for ($i=0; $i < count($nevek); $i++) {
                        $ar = $_SESSION["kosar"][$i]["ar"];
                        $db = $_SESSION["kosar"][$i]["db"];
                        $termekek[$i] = array(
                            'price_data' => [
                            'currency' => 'HUF',
                            'product_data' => [
                                    'name' => "$nevek[$i]",
                            ],
                            'unit_amount' => $ar*100,
                            ],
                            'quantity' => $db,);
                    }
            
                    $session = \Stripe\Checkout\Session::create([
                    'payment_method_types' => ['card'],
                    'line_items' => [$termekek],
                    'mode' => 'payment',
                    'success_url' => 'http://localhost:8000/cartSuccess',
                    'cancel_url' => 'http://localhost:8000/cartItems',
                    ]);
            
                    echo "
                        <script src='https://js.stripe.com/v3/'></script>
                        <script>
                            var stripe = Stripe('pk_test_51MmCQDKlmNolOX5eIYukC7W5aKsX2WdZLFf7CunT7WT2ucSCIPjJOXJO8fbhgUEqhIZ7AUgMwir6FQWALswcoNjQ008FJodG0k');
                            stripe.redirectToCheckout({
                                sessionId: '$session->id'
                            });
                        </script>
            
                    ";
                }
            }
        }
    }

    public static function cartSuccess() {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $id = $_SESSION["user_id"];
        $sql = "SELECT * FROM felhasznalo_szallitasi_adatok WHERE felhasznalo_id='$id';";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $szallitasiAdat = $statement->fetch();

        $felhasznaloId = $szallitasiAdat->felhasznalo_id;
        $szallitasiCim = $szallitasiAdat->szallitasi_cim1;
        $varos = $szallitasiAdat->varos;
        $iranyitoszam = $szallitasiAdat->iranyitoszam;
        $orszag = $szallitasiAdat->orszag;
        $telefonszam = $szallitasiAdat->telefonszam;

        $sql = "INSERT INTO `megrendeles`(`felhasznalo_id`, `megrendeles_datuma`, `megrendeles_statusz`, `szallitasi_cim`, `varos`, `iranyitoszam`, `orszag`, `telefonszam`) VALUES ('$felhasznaloId',NOW(),'0','$szallitasiCim','$varos','$iranyitoszam','$orszag','$telefonszam');";
        $statement = $conn->prepare($sql);
        $statement->execute();

        date_default_timezone_set("Europe/Budapest");
        $datum = date("Y-m-d");


        $sql = "SELECT id FROM megrendeles WHERE felhasznalo_id='$felhasznaloId' AND megrendeles_datuma='$datum';";
        $statement = $conn->prepare($sql);
        $statement->execute();
        $eredmeny = $statement->fetch();

        foreach ($_SESSION["kosar"] as $kosar) {
            $megrendelesId = $eredmeny["id"];
            $termekId = $kosar["id"];
            $darab = $kosar["db"];
            $ar = $kosar["ar"] * $darab;
            $meretId = $kosar["meret"];

            $sql = "INSERT INTO `megrendelt_termekek`(`megrendeles_id`, `termek_id`, `meret_id`, `mennyiseg`, `ar`) VALUES ('$megrendelesId','$termekId','$meretId','$darab','$ar');";
            $statement = $conn->prepare($sql);
            $statement->execute();
        }

        unset($_SESSION["kosar"]);

        echo "
            <script src='
             https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.all.min.js
            '></script>
            <script src='/App/js/jquery-3.6.3.min.js'></script>
            <script type='text/javascript'>
            $(document).ready(function(){
                Swal.fire('Sikeres vásárlás!', '', 'success').then((result) => {
                    if(result.isConfirmed) {
                        document.location='/products';
                    } else {
                        document.location='/products';
                    }
                })
            });
            </script>";
    }

    public static function deleteItem($itemId, $meretId) {
        for ($i=0; $i < count($_SESSION["kosar"]); $i++) { 
            if ($_SESSION["kosar"][$i]["id"] == $itemId && $_SESSION["kosar"][$i]["meret"] == $meretId) {
                unset($_SESSION["kosar"][$i]);
            }
        }
        $_SESSION["kosar"]  = array_values($_SESSION["kosar"]);
        echo "<script>window.history.back();</script>";
    }
}