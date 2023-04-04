<?php namespace App\Model;

use App\Lib\Database;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
  
class ProductDao
{
    public static function ferfiTermekek($kategoria_id)
    {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();
        if ($kategoria_id == 0) {
            $felt = "";
        } else {
            $felt = " AND tk.id = '$kategoria_id'";
        }
        $sql = "SELECT DISTINCT t.id,t.nev,t.cikkszam,t.leiras,tn.megnevezes,t.kep_nev,t.ar 
                FROM termek as t 
                INNER JOIN nem as tn ON t.nem_id=tn.id 
                INNER JOIN termek_termek_kategoria as ttk on ttk.termek_id = t.id
                INNER JOIN termek_kategoria as tk on tk.id = ttk.kategoria_id
                WHERE tn.megnevezes = 'férfi' $felt;";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $termek = $statement->fetchAll();
        $sql = "SELECT DISTINCT tk.id, tk.nev
        FROM termek_kategoria as tk 
        INNER JOIN termek_termek_kategoria as ttk on ttk.kategoria_id = tk.id
        INNER JOIN termek as t on t.id = ttk.termek_id
        WHERE t.nem_id = 2";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $kategoria = $statement->fetchAll();
        $ret = [];
        $ret ["termek"] = $termek;
        $ret ["kategoria"] = $kategoria;
        return $ret;
    }

    public static function noiTermekek($kategoria_id)
    {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();
        if ($kategoria_id == 0) {
            $felt = "";
        } else {
            $felt = " AND tk.id = '$kategoria_id'";
        }
        $sql = "SELECT DISTINCT t.id,t.nev,t.cikkszam,t.leiras,tn.megnevezes,t.kep_nev,t.ar 
                FROM termek as t 
                INNER JOIN nem as tn ON t.nem_id=tn.id 
                INNER JOIN termek_termek_kategoria as ttk on ttk.termek_id = t.id
                INNER JOIN termek_kategoria as tk on tk.id = ttk.kategoria_id
                WHERE tn.megnevezes = 'női' $felt;";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $termek = $statement->fetchAll();
        $sql = "SELECT DISTINCT tk.id, tk.nev
        FROM termek_kategoria as tk 
        INNER JOIN termek_termek_kategoria as ttk on ttk.kategoria_id = tk.id
        INNER JOIN termek as t on t.id = ttk.termek_id
        WHERE t.nem_id = 3";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $kategoria = $statement->fetchAll();
        $ret = [];
        $ret ["termek"] = $termek;
        $ret ["kategoria"] = $kategoria;
        return $ret;
    }

    public static function gyermekTermekek($kategoria_id)
    {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();
        if ($kategoria_id == 0) {
            $felt = "";
        } else {
            $felt = " AND tk.id = '$kategoria_id'";
        }
        $sql = "SELECT DISTINCT t.id,t.nev,t.cikkszam,t.leiras,tn.megnevezes,t.kep_nev,t.ar 
                FROM termek as t 
                INNER JOIN nem as tn ON t.nem_id=tn.id 
                INNER JOIN termek_termek_kategoria as ttk on ttk.termek_id = t.id
                INNER JOIN termek_kategoria as tk on tk.id = ttk.kategoria_id
                WHERE tn.megnevezes = 'gyermek' $felt;";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $termek = $statement->fetchAll();
        $sql = "SELECT DISTINCT tk.id, tk.nev
                FROM termek_kategoria as tk 
                INNER JOIN termek_termek_kategoria as ttk on ttk.kategoria_id = tk.id
                INNER JOIN termek as t on t.id = ttk.termek_id
                WHERE t.nem_id = 4";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $kategoria = $statement->fetchAll();
        $ret = [];
        $ret ["termek"] = $termek;
        $ret ["kategoria"] = $kategoria;
        return $ret;
    }

    public static function ajanlottTermekek()
    {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $sql = "SELECT * FROM `termek` WHERE `nem_id`=2 OR `nem_id`=3
                ORDER BY RAND() LIMIT 7;";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        return $statement->fetchAll();
    }

    public static function productById(int $id)
    {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $statement = $conn->prepare("SELECT * FROM termek WHERE id =:id;");
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute([
            'id'=>$id,
        ]);
        $termek = $statement->fetch();
        $kepek = [];
        $kepek[0] = $termek->kep_nev;
        $sql = "SELECT kep_nev FROM termek_kepek WHERE termek_id = '$id' ORDER BY '$id';";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $sv = $statement->fetchAll();
        for ($i=0; $i < count($sv); $i++) { 
            $kepek[$i+1] = $sv[$i]->kep_nev;
        }
        $sql = "SELECT m.megnevezes, m.id
                FROM meretek as m 
                INNER JOIN termek_meretek as tm on tm.meret_id = m.id
                WHERE tm.termek_id = '$id' 
                ORDER BY m.sorrend;";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $meret = $statement->fetchAll();
        $ret = [];
        $ret ["termek"] = $termek;
        $ret ["meret"] = $meret;
        $ret ["kepek"] = $kepek;
        return $ret;
    }

    public static function search()
    {
        try {
            $dbObj = new Database();
            $conn = $dbObj->getConnection();

            $query = $_POST["query"];
            $statement = $conn->prepare("SELECT * FROM termek WHERE nev LIKE :query");
            $statement->bindValue(':query', '%' . $query . '%');
            $statement->execute();
            if ($statement->rowCount() > 0) {  
                $statement = $conn->prepare("SELECT * FROM termek WHERE nev LIKE :query");
                $statement->bindValue(':query', '%' . $query . '%');
                $statement->execute(); 
                return $statement->fetchAll();
            } else {
                return "Nincs ilyen"; 
            }
        } catch(PDOException $e) {
            echo "Error: ".$e->getMessage();
        }
    }

    public static function cart(){

        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $id = $_POST['id'];
        $productId = $_POST['productId'];
        $ar = $_POST['ar'];
        $meret = $_POST['meret'];
        $mennyiseg = $_POST['mennyiseg'];
        $stmt = $conn->prepare("INSERT INTO kosar (termek_id, felhasznalo_id, ar, meret, mennyiseg) VALUES ('$productId', '$id', '$ar', '$meret', '$mennyiseg')");
        $stmt->execute();
        echo "<script>window.history.back();</script>";
    }
}