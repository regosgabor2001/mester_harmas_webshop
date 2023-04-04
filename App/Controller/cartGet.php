<?php
session_start();
$vissza=[];
$vissza['kod']=0;
$vissza['adat']=[];
if (isset($_SESSION['user_id'])) {
    //ha nem létezik a kosár
    if (!isset($_SESSION['kosar'])) {
        $_SESSION['kosar']=[];//üres kosár
    }
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