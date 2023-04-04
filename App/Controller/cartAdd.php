<?php
session_start();
$vissza=[];
$vissza['kod']=0;
$vissza['adat']=[];
if (isset($_POST['productId'],$_POST['productAr'],$_POST['mennyiseg'],$_POST['meret'],$_SESSION['user_id'])) {
    $id=(int)$_POST['productId'];
    $ar=(int)$_POST['productAr'];
    $db=(int)$_POST['mennyiseg'];
    $meret=$_POST['meret'];
    $mod=$_POST['mod'];
    //ha nem létezik a kosár
    //unset($_SESSION['kosar']);
    if (!isset($_SESSION['kosar'])) {
        $_SESSION['kosar']=[];//üres kosár
    }
    //keresés a kosárban:id+méret
    $n=count($_SESSION['kosar']);
    $i=0;
    $hol=-1;
    while ($i<$n) {
        if ($_SESSION['kosar'][$i]['id']==$id && $_SESSION['kosar'][$i]['meret']==$meret) {
            $hol=$i;
            $i=$n;
        }
        $i++;
    }
    
    if ($hol==-1) { //ha nincs ilyen id+meret
        $_SESSION['kosar'][]=array('id'=>$id,'ar'=>$ar,'meret'=>$meret,'db'=>$db);
    }else { //van a kosárban, indexe hol, db szám módosítás
        if ($mod=='hozzaad') {
            $_SESSION['kosar'][$hol]['db'] += $db;
        }else {
            $_SESSION['kosar'][$hol]['db'] = $db;
        }
        if ($_SESSION['kosar'][$hol]['db']<0) {
            $_SESSION['kosar'][$hol]['db'] = 0;
        }
    }
    //0 ás mennyiségű tételek kiszedése a kosárból
    $kosar=[];
    $n=count($_SESSION['kosar']);
    for ($i=0; $i < $n; $i++) { 
        if ($_SESSION['kosar'][$i]['db']>0) {
            $kosar[]=$_SESSION['kosar'][$i];
        }
    }
    $_SESSION['kosar']=$kosar;
    //kosár értéke
    $n=count($_SESSION['kosar']);
    $osszeg=0;
    for ($i=0; $i < $n; $i++) { 
        $osszeg+=$_SESSION['kosar'][$i]['db']*$_SESSION['kosar'][$i]['ar'];
    }
    $osszegStr=number_format($osszeg,0,',',' ');
    $vissza['adat']=array('tetel'=>$n,'osszeg'=>$osszegStr,'osszegNum'=>$osszeg);
}
else {
    $vissza['kod']=100;
}
echo json_encode($vissza);
?>