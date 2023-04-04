<?php namespace App\Model;

use App\Lib\Database;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
  
class AdminDao
{
    public static function adminLogin() {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $email = $_POST["email"];

        $sql = "SELECT * FROM felhasznalo WHERE email='$email' AND sztatusz='1' AND jogosultsag_id='1'";
        $prepare = $conn->prepare($sql);
        $prepare->execute();
        $row = $prepare->fetch(\PDO::FETCH_ASSOC);
        $password = $row['jelszo'];

        $passwordVerify = password_verify($_POST['password'], $password);

        if ($passwordVerify) {
            $_SESSION["user_id"] = $row['id'];
            AdminDao::adminLog($row['id'], 2, "-");
            header("Location: /adminPage");
        } else {
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
                            text: 'Bejelentkezési adatok helytelenek. Próbálja újra!'
                        }).then((result) => {
                            if(result.isConfirmed) {
                                document.location='/admin';
                            } else {
                                document.location='/admin';
                            }
                        })
                    });
                    </script>";
        }
    }

    public static function adminData() {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $id = 0;
        if (isset($_SESSION["user_id"])) {
            $id = $_SESSION["user_id"];
            $statement = $conn->prepare("SELECT * FROM felhasznalo WHERE id='$id' AND jogosultsag_id='1' AND sztatusz='1';");
            $statement->setFetchMode(\PDO::FETCH_OBJ);
            $statement->execute();
            return $statement->fetch();
        } else {
            return $id;
        }
    }

    public static function adminLogout() {
        $id = $_SESSION["user_id"];
        AdminDao::adminLog($id, 4, "-");
        session_unset();
        session_destroy();
        header("Location: /admin");
    }

    public static function adminRegist() {
        require 'vendor/autoload.php';

        error_reporting(0);

        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $username = $_POST["username"];
        $email = $_POST["email"];
        $password = $_POST['password'];
        $cpassword = $_POST['cpassword'];
        $token = bin2hex(random_bytes(5));
        $status = 0;
        $jogosultsag_id = 1;

        $sql = "SELECT email FROM felhasznalo WHERE email='$email'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($password !== $cpassword) {
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
                            text: 'A jelszó nem egyezik!'
                        }).then((result) => {
                            if(result.isConfirmed) {
                                document.location='/admin';
                            } else {
                                document.location='/admin';
                            }
                        })
                    });
                    </script>";
             
        } elseif ($count) {
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
                            text: 'Ez az email cím már létezik!'
                        }).then((result) => {
                            if(result.isConfirmed) {
                                document.location='/admin';
                            } else {
                                document.location='/admin';
                            }
                        })
                    });
                    </script>";
        } else {
            $password = password_hash($_POST['password'], PASSWORD_ARGON2ID);

            $sql = "INSERT INTO felhasznalo (`felhasznalonev`, `email`, `jelszo`, `token`, `sztatusz`, `jogosultsag_id`) VALUES (:username, :email, :password, :token, :status, :jogosultsag_id)";

            $statement = $conn->prepare($sql);
            $statement->execute([
            'username'=>$username,
            'email'=>$email,
            'password'=>$password,
            'token'=>$token,
            'status'=>$status,
            'jogosultsag_id'=>$jogosultsag_id,
            ]);
            if ($statement) {
                $mail = new PHPMailer(true);

                $mail->SMTPDebug = 0;
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'mester.harmas.webshop@gmail.com';
                $mail->Password   = 'vibwbeoherfapfuf';
                $mail->SMTPSecure = 'tls';
                $mail->Port       = '587';

                $sql = "SELECT * FROM felhasznalo WHERE jogosultsag_id='1' AND sztatusz='1'";
                $statement = $conn->prepare($sql);
                $statement->execute();
                $admins = $statement->fetchAll();
                foreach ($admins as $admin) {

                    $to = $admin["email"];
                    $subject = "Admin kérelem";
                    $base_url = "http://localhost:8000/";

                    $message = "
                    <html>
                    <head>
                    <title>{$subject}</title>
                    </head>
                    <body>
                    <p><strong>Tisztelt {$admin["felhasznalonev"]}!</strong></p>
                    <p>Admin kérelmet küldtek önnek '$username' néven:</p>
                    <p><a href='{$base_url}index.php?adminToken={$admin["token"]}&userToken={$token}'>Webshop admin felülethez</a></p>
                    </body>
                    </html>
                    ";

                    $mail->setFrom('mester.harmas.webshop@gmail.com', 'Webshop');
                    $mail->addAddress($to, $admin['felhasznalonev']);

                    $mail->isHTML(true);
                    $mail->CharSet = 'UTF-8';
                    $mail->Subject = $subject;
                    $mail->Body    = $message;

                    $mail->send();
                }

                $mail = new PHPMailer(true);

                try {
                    $mail->SMTPDebug = 0;
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'mester.harmas.webshop@gmail.com';
                    $mail->Password   = 'vibwbeoherfapfuf';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port       = '587';

                    $to = $email;
                    $subject = "Admin kérelem";
                    $base_url = "http://localhost:8000/";

                    $message = "
                    <html>
                    <head>
                    <title>{$subject}</title>
                    </head>
                    <body>
                    <p><strong>Tisztelt {$username}!</strong></p>
                    <p>Admin kérelme felülvizsgálat alatt áll!</p>
                    </body>
                    </html>
                    ";

                    $mail->setFrom('mester.harmas.webshop@gmail.com', 'Webshop');
                    $mail->addAddress($to, $username);

                    $mail->isHTML(true);
                    $mail->CharSet = 'UTF-8';
                    $mail->Subject = $subject;
                    $mail->Body    = $message;

                    $mail->send();

                    echo "
                    <script src='
                    https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.all.min.js
                    '></script>
                    <script src='/App/js/jquery-3.6.3.min.js'></script>
                    <script type='text/javascript'>
                    $(document).ready(function(){
                        Swal.fire('Megerősítő link elküldve az alábbi email címre: $email', '', 'success').then((result) => {
                            if(result.isConfirmed) {
                                document.location='/admin';
                            } else {
                                document.location='/admin';
                            }
                        })
                    });
                    </script>";
                } catch (Exception $e) {
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
                            text: 'Az email nincs elküldve. Próbálja újra!'
                        }).then((result) => {
                            if(result.isConfirmed) {
                                document.location='/admin';
                            } else {
                                document.location='/admin';
                            }
                        })
                    });
                    </script>";
                }


                $sql = "SELECT * FROM felhasznalo WHERE email='$email';";
                $statement = $conn->prepare($sql);
                $statement->setFetchMode(\PDO::FETCH_OBJ);
                $statement->execute();
                $felhasznalo = $statement->fetch();

                AdminDao::adminLog($felhasznalo->id, 1, "-");
            } else {
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
                            text: 'Sikertelen regisztráció!'
                        }).then((result) => {
                            if(result.isConfirmed) {
                                document.location='/admin';
                            } else {
                                document.location='/admin';
                            }
                        })
                    });
                    </script>";
            }
        }
    }

    public static function adminVerify() {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();
        $userId = $_POST['userId'];
        $userStatus = $_POST['userStatus'];

        $_SESSION["user_id"] = $_POST['adminId'];
        $email = $_POST['userEmail'];
        $username = $_POST['username'];

        $sql = "SELECT * FROM felhasznalo WHERE id='$userId';";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $felhasznalo = $statement->fetch();

        if ($userStatus != '1') {
            if (isset($_POST['megerosit'])) {
                $sql = "UPDATE felhasznalo SET sztatusz = '1' WHERE id = $userId";
                $statement = $conn->prepare($sql);
                $statement->execute();

                $mail = new PHPMailer(true);

                try {
                    $mail->SMTPDebug = 0;
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'mester.harmas.webshop@gmail.com';
                    $mail->Password   = 'vibwbeoherfapfuf';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port       = '587';


                    $to = $email;
                    $subject = "Admin kérelem";
                    $base_url = "http://localhost:8000/";

                    $message = "
                    <html>
                    <head>
                    <title>{$subject}</title>
                    </head>
                    <body>
                    <p><strong>Tisztelt {$username}!</strong></p>
                    <p>Admin kérelmét elfogadták.</p>
                    </body>
                    </html>
                    ";

                    $mail->setFrom('mester.harmas.webshop@gmail.com', 'Webshop');
                    $mail->addAddress($to, $username);

                    $mail->isHTML(true);
                    $mail->CharSet = 'UTF-8';
                    $mail->Subject = $subject;
                    $mail->Body    = $message;

                    $mail->send();

                    echo "
                    <script src='
                    https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.all.min.js
                    '></script>
                    <script src='/App/js/jquery-3.6.3.min.js'></script>
                    <script type='text/javascript'>
                    $(document).ready(function(){
                        Swal.fire('Admin fiók sikeresen megerősítve!', '', 'success').then((result) => {
                            if(result.isConfirmed) {
                                document.location='/adminPage';
                            } else {
                                document.location='/adminPage';
                            }
                        })
                    });
                    </script>";
                    
                } catch (Exception $e) {
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
                            text: 'Az email nincs elküldve. Próbálja újra!'
                        }).then((result) => {
                            if(result.isConfirmed) {
                                document.location='/adminPage';
                            } else {
                                document.location='/adminPage';
                            }
                        })
                    });
                    </script>";
                }
                AdminDao::adminLog($_SESSION["user_id"], 14, $felhasznalo->felhasznalonev);
            } elseif (isset($_POST['torol'])) {
                $mail = new PHPMailer(true);

                try {
                    $mail->SMTPDebug = 0;
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'mester.harmas.webshop@gmail.com';
                    $mail->Password   = 'vibwbeoherfapfuf';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port       = '587';

                    $to = $email;
                    $subject = "Admin kérelem";
                    $base_url = "http://localhost:8000/";

                    $message = "
                    <html>
                    <head>
                    <title>{$subject}</title>
                    </head>
                    <body>
                    <p><strong>Tisztelt {$username}!</strong></p>
                    <p>Admin kérelmét elutasították.</p>
                    </body>
                    </html>
                    ";

                    $mail->setFrom('mester.harmas.webshop@gmail.com', 'Webshop');
                    $mail->addAddress($to, $username);

                    $mail->isHTML(true);
                    $mail->CharSet = 'UTF-8';
                    $mail->Subject = $subject;
                    $mail->Body    = $message;

                    $mail->send();

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
                            text: 'Admin fiók elutasítva!'
                        }).then((result) => {
                            if(result.isConfirmed) {
                                document.location='/adminPage';
                            } else {
                                document.location='/adminPage';
                            }
                        })
                    });
                    </script>";
                } catch (Exception $e) {
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
                            text: 'Az email nincs elküldve. Próbálja újra!'
                        }).then((result) => {
                            if(result.isConfirmed) {
                                document.location='/adminPage';
                            } else {
                                document.location='/adminPage';
                            }
                        })
                    });
                    </script>";
                }
                AdminDao::adminLog($_SESSION["user_id"], 15, $felhasznalo->email);

                $sql = "DELETE FROM felhasznalo WHERE id = $userId";
                $statement = $conn->prepare($sql);
                $statement->execute();
            }
        } else {
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
                            text: 'Admin kérelmét már feldolgozták!'
                        }).then((result) => {
                            if(result.isConfirmed) {
                                document.location='/adminPage';
                            } else {
                                document.location='/adminPage';
                            }
                        })
                    });
                    </script>";
        }
    }

    public static function osszesTermek($nem_id, $kategoria_id, $marka_id) {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        if ($marka_id == 0) {
            $felt3 = "";
        } else {
            $felt3 = "AND t.marka_id='$marka_id'";
        }

        if ($kategoria_id == 1) {
            $felt1 = "";
        } else {
            $felt1 = " AND tk.id = '$kategoria_id'";
        }
    
        if ($nem_id == 0) {
            $felt2 = "t.id > '0'";
        } else {
            $felt2 = "t.nem_id='$nem_id'";
        }
        $sql = "SELECT DISTINCT t.id,t.nev,t.cikkszam,tm.markanev,t.leiras,tn.megnevezes,t.kep_nev,t.ar 
                FROM termek as t 
                INNER JOIN nem as tn ON t.nem_id=tn.id
                INNER JOIN markak as tm ON t.marka_id=tm.id 
                INNER JOIN termek_termek_kategoria as ttk on ttk.termek_id = t.id 
                INNER JOIN termek_kategoria as tk on tk.id = ttk.kategoria_id 
                WHERE $felt2 $felt1 $felt3 ORDER BY id;";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $termekek =  $statement->fetchAll();

        $sql = "SELECT * FROM nem;";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $nemek = $statement->fetchAll();

        $sql = "SELECT * FROM markak;";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $markak = $statement->fetchAll();

        $ret = [];
        $ret["termekek"] = $termekek;
        $ret["nemek"] = $nemek;
        $ret["markak"] = $markak;
        $ret["nemId"] = $nem_id;
        $ret["kategoria_id"] = $kategoria_id;
        $ret["markaId"] = $marka_id;
        return $ret;
    }

    public static function searchProduct() {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        try {
            $query = $_POST["query"];
            $statement = $conn->prepare("SELECT * FROM termek WHERE nev LIKE :query OR cikkszam LIKE :query");
            $statement->bindValue(':query', '%' . $query . '%');
            $statement->execute();
            if ($statement->rowCount() > 0) {
                $sql = "SELECT DISTINCT t.id,t.nev,t.cikkszam,tm.markanev,t.leiras,tn.megnevezes,t.kep_nev,t.ar 
                FROM termek as t 
                INNER JOIN nem as tn ON t.nem_id=tn.id
                INNER JOIN markak as tm ON t.marka_id=tm.id 
                INNER JOIN termek_termek_kategoria as ttk on ttk.termek_id = t.id 
                INNER JOIN termek_kategoria as tk on tk.id = ttk.kategoria_id 
                WHERE t.nev LIKE :query OR t.cikkszam LIKE :query ORDER BY id;"; 
                $statement = $conn->prepare($sql);
                $statement->bindValue(':query', '%' . $query . '%');
                $statement->execute(); 
                $termekek = $statement->fetchAll();
            } else {
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
                            text: 'Nincsen ilyen termék!'
                        }).then((result) => {
                            if(result.isConfirmed) {
                                window.history.back();
                            } else {
                                window.history.back();
                            }
                        })
                    });
                    </script>";
                
            }
        } catch(PDOException $e) {
            echo "Error: ".$e->getMessage();
        }
        $sql = "SELECT * FROM nem;";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $nemek = $statement->fetchAll();

        $ret = [];
        $ret["termekek"] = $termekek;
        $ret["nemek"] = $nemek;
        return $ret;
    }

    public static function nemek_marka() {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $sql = "SELECT * FROM nem;";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $nemek = $statement->fetchAll();

        $sql = "SELECT * FROM markak;";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $markak = $statement->fetchAll();
        
        $ret = [];
        $ret["nemek"] = $nemek;
        $ret["marka"] = $markak;

        return $ret;
    }

    public static function termek_kategoria() {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $sql = "SELECT * FROM termek_kategoria WHERE id>'1';";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $termek_kategoria = $statement->fetchAll();

        $sql = "SELECT * FROM termek_termek_kategoria;";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $termek_termek_kategoria = $statement->fetchAll();

        $ret = [];
        $ret["termek_kategoria"] = $termek_kategoria;
        $ret["termek_termek_kategoria"] = $termek_termek_kategoria;
        return $ret;
    }

    public static function productById($id)
    {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $statement = $conn->prepare("SELECT DISTINCT t.id,t.nev,t.cikkszam,tm.markanev,t.kep_nev,t.leiras,tn.megnevezes,t.ar 
        FROM termek as t 
        INNER JOIN nem as tn ON t.nem_id=tn.id
        INNER JOIN markak as tm ON t.marka_id=tm.id WHERE t.id=:id;");
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
        $sql = "SELECT * FROM markak;";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $marka = $statement->fetchAll();

        $sql = "SELECT * FROM nem;";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $nem = $statement->fetchAll();

        $sql = "SELECT * FROM termek_kategoria WHERE id>'1';";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $termek_kategoria = $statement->fetchAll();

        $sql = "SELECT * FROM termek_termek_kategoria;";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $termek_termek_kategoria = $statement->fetchAll();
        $ret = [];
        $ret ["termek"] = $termek;
        $ret ["nem"] = $nem;
        $ret["termek_kategoria"] = $termek_kategoria;
        $ret["termek_termek_kategoria"] = $termek_termek_kategoria;
        $ret ["marka"] = $marka;
        $ret ["kepek"] = $kepek;
        return $ret;
    }

    public static function update() {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $id = $_POST['id'];
        $nev = $_POST['nev'];
        $cikkszam = $_POST['cikkszam'];
        $marka = $_POST['marka'];
        $leiras = $_POST['leiras'];
        $nem = $_POST['nem'];
        $ar = $_POST['ar'];
        $termek_kategoria = $_POST['termek_kategoria'];

        if ($termek_kategoria != 1) {
            $sql = "UPDATE termek_termek_kategoria SET kategoria_id='$termek_kategoria' WHERE termek_id='$id' AND kategoria_id > '1';";
            $statement = $conn->prepare($sql);
            $statement->execute();
        }

        $sql = "UPDATE `termek` SET nev='$nev',cikkszam='$cikkszam',marka_id='$marka',leiras='$leiras',nem_id='$nem',ar='$ar' WHERE id='$id';";
        $statement = $conn->prepare($sql);
        $statement->execute();

        $targetDir = "App/images/Clothes/Men/";
        $kep = basename($_FILES["kep"]["name"]);
        $targetFilePath = $targetDir . $kep;
        $extension = strtolower(pathinfo($kep, PATHINFO_EXTENSION));
        $imgExtArr = ['jpg', 'jpeg', 'png'];
        $sql = "SELECT * FROM termek WHERE id='$id';";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $termek = $statement->fetch();

        $sql = "SELECT * FROM termek WHERE id='$id';";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $termek = $statement->fetch();

        if(!empty($_FILES["kep"]["name"])){
            if(in_array($extension, $imgExtArr)) {
                if (is_dir($targetDir) && is_writable($targetDir)) {
                    if(move_uploaded_file($_FILES["kep"]["tmp_name"], $targetFilePath)){
                        if ($termek->kep_nev != '') {
                            $sql = "INSERT INTO termek_kepek (termek_id, kep_nev) VALUES ('$id','$kep');";
                            $statement = $conn->prepare($sql);
                            $statement->execute();
                        } else {
                            $sql = "UPDATE termek SET kep_nev='$kep' WHERE id='$id';";
                            $statement = $conn->prepare($sql);
                            $statement->execute();
                        }
                        if($statement){
                            AdminDao::adminLog($_SESSION["user_id"], 6, $termek->cikkszam);
                            echo "
                    <script src='
                    https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.all.min.js
                    '></script>
                    <script src='/App/js/jquery-3.6.3.min.js'></script>
                    <script type='text/javascript'>
                    $(document).ready(function(){
                        Swal.fire('A kép sikeresen mentve, feltöltve!', '', 'success').then((result) => {
                            if(result.isConfirmed) {
                                window.history.back();
                            } else {
                                window.history.back();
                            }
                        })
                    });
                    </script>";
                        }else{
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
                                        text: 'A kép feltöltése az adatbázisba sikertelen!'
                                    }).then((result) => {
                                        if(result.isConfirmed) {
                                            window.history.back();
                                        } else {
                                            window.history.back();
                                        }
                                    })
                                });
                                </script>";
                        } 
                    }else{
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
                                        text: 'A kép feltöltése sikertelen!'
                                    }).then((result) => {
                                        if(result.isConfirmed) {
                                            window.history.back();
                                        } else {
                                            window.history.back();
                                        }
                                    })
                                });
                                </script>";
                    }
                } else {
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
                                        text: 'Nincs ilyen mappa!'
                                    }).then((result) => {
                                        if(result.isConfirmed) {
                                            window.history.back();
                                        } else {
                                            window.history.back();
                                        }
                                    })
                                });
                                </script>";
                }
            }else{
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
                            text: 'Csak képet tölthet fel!'
                        }).then((result) => {
                            if(result.isConfirmed) {
                                window.history.back();
                            } else {
                                window.history.back();
                            }
                        })
                    });
                    </script>";
            }
        } else {
            AdminDao::adminLog($_SESSION["user_id"], 6, $termek->cikkszam);
            echo "
                    <script src='
                    https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.all.min.js
                    '></script>
                    <script src='/App/js/jquery-3.6.3.min.js'></script>
                    <script type='text/javascript'>
                    $(document).ready(function(){
                        Swal.fire('Sikeres mentés!', '', 'success').then((result) => {
                            if(result.isConfirmed) {
                                window.history.back();
                            } else {
                                window.history.back();
                            }
                        })
                    });
                    </script>";
        }
    }

    public static function deleteImage($kepTorol) {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $targetDir = "App/images/Clothes/Men/";
        $targetFilePath = $targetDir . $kepTorol;

        $sql = "SELECT * FROM termek WHERE kep_nev='$kepTorol';";
        $statement = $conn->query($sql);
        $count = $statement->fetchColumn();

        $sql = "SELECT * FROM termek WHERE kep_nev='$kepTorol';";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $termek = $statement->fetch();

        if ($count > 0) {
            $sql = "UPDATE termek SET kep_nev='' WHERE kep_nev='$kepTorol';";
            $statement = $conn->prepare($sql);
            $statement->execute();
            if ($statement) {
                unlink($targetFilePath);
                AdminDao::adminLog($_SESSION["user_id"], 6, $termek->cikkszam);
                echo "
                    <script src='
                    https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.all.min.js
                    '></script>
                    <script src='/App/js/jquery-3.6.3.min.js'></script>
                    <script type='text/javascript'>
                    $(document).ready(function(){
                        Swal.fire('A kép sikeresen törölve!', '', 'success').then((result) => {
                            if(result.isConfirmed) {
                                window.history.back();
                            } else {
                                window.history.back();
                            }
                        })
                    });
                    </script>";
            } else {
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
                            text: 'Sikertelen törlés!'
                        }).then((result) => {
                            if(result.isConfirmed) {
                                window.history.back();
                            } else {
                                window.history.back();
                            }
                        })
                    });
                    </script>";
            }
        } else {
            $sql = "DELETE FROM termek_kepek WHERE kep_nev='$kepTorol';";
            $statement = $conn->prepare($sql);
            $statement->execute();
            if ($statement) {
                unlink($targetFilePath);
                AdminDao::adminLog($_SESSION["user_id"], 6, $termek->cikkszam);
                echo "
                    <script src='
                    https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.all.min.js
                    '></script>
                    <script src='/App/js/jquery-3.6.3.min.js'></script>
                    <script type='text/javascript'>
                    $(document).ready(function(){
                        Swal.fire('A kép sikeresen törölve!', '', 'success').then((result) => {
                            if(result.isConfirmed) {
                                window.history.back();
                            } else {
                                window.history.back();
                            }
                        })
                    });
                    </script>";
            } else {
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
                            text: 'Sikertelen törlés!'
                        }).then((result) => {
                            if(result.isConfirmed) {
                                window.history.back();
                            } else {
                                window.history.back();
                            }
                        })
                    });
                    </script>";
            }
        }
    }

    public static function newProduct() {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $nev = $_POST['nev'];
        $cikkszam = $_POST['cikkszam'];
        $marka = $_POST['marka'];
        $leiras = $_POST['leiras'];
        $nem = $_POST['nem'];
        $ar = $_POST['ar'];
        $termek_kategoria = $_POST['termek_kategoria'];

        $targetDir = "App/images/Clothes/Men/";
        $kep = basename($_FILES["kep"]["name"]);
        $targetFilePath = $targetDir . $kep;
        $extension = strtolower(pathinfo($kep, PATHINFO_EXTENSION));
        $imgExtArr = ['jpg', 'jpeg', 'png'];

        $sql = "INSERT INTO `termek`(`nev`, `cikkszam`, `marka_id`, `leiras`, `nem_id`, `ar`) VALUES ('$nev', '$cikkszam', '$marka', '$leiras', '$nem', '$ar');";
        $statement = $conn->prepare($sql);
        $statement->execute();

        $sql = "SELECT * FROM termek WHERE nev='$nev';";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $termek = $statement->fetch();

        $sql ="INSERT INTO `termek_termek_kategoria`(`termek_id`, `kategoria_id`) VALUES ('$termek->id','$termek_kategoria');";
        $statement = $conn->prepare($sql);
        $statement->execute();

        $sql = "INSERT INTO `termek_termek_kategoria`(`termek_id`, `kategoria_id`) VALUES ('$termek->id','1');";
        $statement = $conn->prepare($sql);
        $statement->execute();

        $sql = "SELECT * FROM meretek;";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $meretek = $statement->fetchAll();

        for ($i = 1; $i <= count($meretek); $i++) {
            $sql = "INSERT INTO `termek_meretek`(`termek_id`, `meret_id`) VALUES ('$termek->id','$i');";
            $statement = $conn->prepare($sql);
            $statement->execute();
        }

        if(in_array($extension, $imgExtArr)) {
            if (is_dir($targetDir) && is_writable($targetDir)) {
                if(move_uploaded_file($_FILES["kep"]["tmp_name"], $targetFilePath)){
                    $sql = "UPDATE termek SET kep_nev='$kep' WHERE id='$termek->id';";
                    $statement = $conn->prepare($sql);
                    $statement->execute();
                    if($statement){
                        AdminDao::adminLog($_SESSION["user_id"], 7, $termek->cikkszam);
                        echo "
                    <script src='
                    https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.all.min.js
                    '></script>
                    <script src='/App/js/jquery-3.6.3.min.js'></script>
                    <script type='text/javascript'>
                    $(document).ready(function(){
                        Swal.fire('A kép sikeresen mentve, feltöltve!', '', 'success').then((result) => {
                            if(result.isConfirmed) {
                                window.history.back();
                            } else {
                                window.history.back();
                            }
                        })
                    });
                    </script>";
                    }else{
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
                                        text: 'A kép feltöltése az adatbázisba sikertelen!'
                                    }).then((result) => {
                                        if(result.isConfirmed) {
                                            window.history.back();
                                        } else {
                                            window.history.back();
                                        }
                                    })
                                });
                                </script>";
                    } 
                }else{
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
                                        text: 'A kép feltöltése sikertelen!'
                                    }).then((result) => {
                                        if(result.isConfirmed) {
                                            window.history.back();
                                        } else {
                                            window.history.back();
                                        }
                                    })
                                });
                                </script>";
                }
            } else {
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
                            text: 'Nincsen ilyen mappa!'
                        }).then((result) => {
                            if(result.isConfirmed) {
                                window.history.back();
                            } else {
                                window.history.back();
                            }
                        })
                    });
                    </script>";
            }
        }else{
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
                        text: 'Csak képet tölthet fel!'
                    }).then((result) => {
                        if(result.isConfirmed) {
                            window.history.back();
                        } else {
                            window.history.back();
                        }
                    })
                });
                </script>";
        }
    }

    public static function deleteById($deleteId) {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $sql = "DELETE FROM kosar WHERE termek_id='$deleteId';";
        $statement = $conn->prepare($sql);
        $statement->execute();

        $sql = "DELETE FROM megrendelt_termekek WHERE termek_id='$deleteId';";
        $statement = $conn->prepare($sql);
        $statement->execute();

        $sql = "DELETE FROM termek_meretek WHERE termek_id='$deleteId';";
        $statement = $conn->prepare($sql);
        $statement->execute();

        $sql = "DELETE FROM termek_termek_kategoria WHERE termek_id='$deleteId';";
        $statement = $conn->prepare($sql);
        $statement->execute();

        $sql = "SELECT * FROM termek_kepek WHERE termek_id='$deleteId';";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $termek_kepek = $statement->fetchAll();

        $targetDir = "App/images/Clothes/Men/";

        foreach ($termek_kepek as $kep) {
            $targetFilePath = $targetDir . $kep->kep_nev;
            unlink($targetFilePath);
        }

        $sql = "DELETE FROM termek_kepek WHERE termek_id='$deleteId';";
        $statement = $conn->prepare($sql);
        $statement->execute();

        $sql = "SELECT * FROM termek WHERE id='$deleteId';";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $termek = $statement->fetch();

        AdminDao::adminLog($_SESSION["user_id"], 5, $termek->cikkszam);

        $targetFilePath = $targetDir . $termek->kep_nev;
        unlink($targetFilePath);

        $sql = "DELETE FROM termek WHERE id='$deleteId';";
        $statement = $conn->prepare($sql);
        $statement->execute();

        $id = $_SESSION['user_id'];
        $sql = "SELECT * FROM felhasznalo WHERE id='$id';";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $felhasznalo = $statement->fetch();

        echo "
                    <script src='
                    https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.all.min.js
                    '></script>
                    <script src='/App/js/jquery-3.6.3.min.js'></script>
                    <script type='text/javascript'>
                    $(document).ready(function(){
                        Swal.fire('A termék sikeresen törölve!', '', 'success').then((result) => {
                            if(result.isConfirmed) {
                                window.history.back();
                            } else {
                                window.history.back();
                            }
                        })
                    });
                    </script>";
    }

    public static function adminProductCategories() {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $sql = "SELECT * FROM termek_kategoria WHERE id>'1' ORDER BY id;";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        return $statement->fetchAll();
    }

    public static function newProductCategory() {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $nev = $_POST["nev"];

        $sql = "INSERT INTO `termek_kategoria`(`nev`) VALUES ('$nev');";
        $statement = $conn->prepare($sql);
        $statement->execute();

        $sql = "SELECT * FROM termek_kategoria WHERE nev='$nev';";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $kategoria = $statement->fetch();

        AdminDao::adminLog($_SESSION["user_id"], 10, $kategoria->nev);
        echo "
                    <script src='
                    https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.all.min.js
                    '></script>
                    <script src='/App/js/jquery-3.6.3.min.js'></script>
                    <script type='text/javascript'>
                    $(document).ready(function(){
                        Swal.fire('A kategória sikeresen feltöltve!', '', 'success').then((result) => {
                            if(result.isConfirmed) {
                                window.history.back();
                            } else {
                                window.history.back();
                            }
                        })
                    });
                    </script>";
    }

    public static function deleteCategory($deleteCategoryId) {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $sql = "SELECT * FROM termek_kategoria WHERE id='$deleteCategoryId';";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $kategoria = $statement->fetch();

        AdminDao::adminLog($_SESSION["user_id"], 8, $kategoria->nev);

        $sql = "DELETE FROM termek_termek_kategoria WHERE kategoria_id=$deleteCategoryId;";
        $statement = $conn->prepare($sql);
        $statement->execute();

        $sql = "DELETE FROM termek_kategoria WHERE id=$deleteCategoryId;";
        $statement = $conn->prepare($sql);
        $statement->execute();

        echo "
                    <script src='
                    https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.all.min.js
                    '></script>
                    <script src='/App/js/jquery-3.6.3.min.js'></script>
                    <script type='text/javascript'>
                    $(document).ready(function(){
                        Swal.fire('A kategória sikeresen törölve!', '', 'success').then((result) => {
                            if(result.isConfirmed) {
                                window.history.back();
                            } else {
                                window.history.back();
                            }
                        })
                    });
                    </script>";
    }

    public static function productCategoryChange($productCategoryId) {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $sql = "SELECT * FROM termek_kategoria WHERE id='$productCategoryId';";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        return $statement->fetch();
    }

    public static function updateCategory() {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $id = $_POST["id"];
        $nev = $_POST["nev"];

        $sql = "UPDATE `termek_kategoria` SET `nev`='$nev' WHERE id='$id';";
        $statement = $conn->prepare($sql);
        $statement->execute();

        AdminDao::adminLog($_SESSION["user_id"], $nev);
        echo "
                    <script src='
                    https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.all.min.js
                    '></script>
                    <script src='/App/js/jquery-3.6.3.min.js'></script>
                    <script type='text/javascript'>
                    $(document).ready(function(){
                        Swal.fire('A kategória sikeresen módosítva!', '', 'success').then((result) => {
                            if(result.isConfirmed) {
                                window.history.back();
                            } else {
                                window.history.back();
                            }
                        })
                    });
                    </script>";
    }

    public static function adminBrands() {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $sql = "SELECT * FROM markak;";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        return $statement->fetchAll();
    }

    public static function newBrand() {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $nev = $_POST["nev"];
        AdminDao::adminLog($_SESSION["user_id"], 13, $nev);

        $sql = "INSERT INTO `markak`(`markanev`) VALUES ('$nev');";
        $statement = $conn->prepare($sql);
        $statement->execute();

        echo "
                    <script src='
                    https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.all.min.js
                    '></script>
                    <script src='/App/js/jquery-3.6.3.min.js'></script>
                    <script type='text/javascript'>
                    $(document).ready(function(){
                        Swal.fire('A márka sikeresen feltöltve!', '', 'success').then((result) => {
                            if(result.isConfirmed) {
                                window.history.back();
                            } else {
                                window.history.back();
                            }
                        })
                    });
                    </script>";
    }

    public static function changeBrand($brandId) {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $sql = "SELECT * FROM markak WHERE id='$brandId';";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        return $statement->fetch();
    }

    public static function deleteBrand($deleteBrandId) {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $sql = "SELECT * FROM markak WHERE id='$deleteBrandId';";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $marka = $statement->fetch();

        AdminDao::adminLog($_SESSION["user_id"], 11, $marka->markanev);

        $sql = "DELETE FROM markak WHERE id='$deleteBrandId';";
        $statement = $conn->prepare($sql);
        $statement->execute();

        echo "
                    <script src='
                    https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.all.min.js
                    '></script>
                    <script src='/App/js/jquery-3.6.3.min.js'></script>
                    <script type='text/javascript'>
                    $(document).ready(function(){
                        Swal.fire('A márka sikeresen törölve!', '', 'success').then((result) => {
                            if(result.isConfirmed) {
                                window.history.back();
                            } else {
                                window.history.back();
                            }
                        })
                    });
                    </script>";
    }

    public static function updateBrand() {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $id = $_POST["id"];
        $nev = $_POST["nev"];

        $sql = "UPDATE `markak` SET `markanev`='$nev' WHERE id='$id';";
        $statement = $conn->prepare($sql);
        $statement->execute();

        AdminDao::adminLog($_SESSION["user_id"], 12, $nev);
        echo "
                    <script src='
                    https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.all.min.js
                    '></script>
                    <script src='/App/js/jquery-3.6.3.min.js'></script>
                    <script type='text/javascript'>
                    $(document).ready(function(){
                        Swal.fire('A márka sikeresen módosítva!', '', 'success').then((result) => {
                            if(result.isConfirmed) {
                                window.history.back();
                            } else {
                                window.history.back();
                            }
                        })
                    });
                    </script>";
    }

    public static function users($jogosultsagId) {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        if ($jogosultsagId == 0) {
            $felt = "";
        } else {
            $felt = "WHERE j.id='$jogosultsagId'";
        }

        $sql = "SELECT f.id,f.felhasznalonev,f.vezeteknev,f.keresztnev,f.telefonszam,j.megnevezes,f.email FROM felhasznalo as f
                INNER JOIN jogosultsagok as j on f.jogosultsag_id=j.id $felt ORDER BY f.id;";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        return $statement->fetchAll();
    }

    public static function deleteUser($userId) {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $sql = "SELECT * FROM felhasznalo WHERE id='$userId';";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $userData = $statement->fetch();

        if ($userData->email == "mester.harmas.webshop@gmail.com") {
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
                        text: 'Ezt a felhasználót nem lehet törölni!'
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
            $mail = new PHPMailer(true);

            try {
                $mail->SMTPDebug = 0;
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'mester.harmas.webshop@gmail.com';
                $mail->Password   = 'vibwbeoherfapfuf';
                $mail->SMTPSecure = 'tls';
                $mail->Port       = '587';

                $to = $userData->email;
                $subject = "Webshop fiók";
                $base_url = "http://localhost:8000/";

                $message = "
                <html>
                <head>
                <title>{$subject}</title>
                </head>
                <body>
                <p><strong>Tisztelt {$userData->felhasznalonev}!</strong></p>
                <p>Fiókját megszüntettük!</p>
                </body>
                </html>
                ";

                $mail->setFrom('mester.harmas.webshop@gmail.com', 'Webshop');
                $mail->addAddress($to, $userData->felhasznalonev);

                $mail->isHTML(true);
                $mail->CharSet = 'UTF-8';
                $mail->Subject = $subject;
                $mail->Body    = $message;

                $mail->send();
            } catch (Exception $e) {
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
                            text: 'Az email nincs elküldve. Próbálja újra!'
                        }).then((result) => {
                            if(result.isConfirmed) {
                                document.location='/adminPage'
                            } else {
                                document.location='/adminPage'
                            }
                        })
                    });
                    </script>";
            }

            AdminDao::adminLog($_SESSION["user_id"], 15, $userData->email);

            $sql = "DELETE FROM felhasznalo_szallitasi_adatok WHERE felhasznalo_id='$userId';";
            $statement = $conn->prepare($sql);
            $statement->execute();

            $sql = "DELETE FROM kosar WHERE felhasznalo_id='$userId';";
            $statement = $conn->prepare($sql);
            $statement->execute();

            $sql = "DELETE FROM `log` WHERE felhasznalo_id='$userId';";
            $statement = $conn->prepare($sql);
            $statement->execute();

            $sql = "DELETE FROM megrendeles WHERE felhasznalo_id='$userId';";
            $statement = $conn->prepare($sql);
            $statement->execute();

            $sql = "DELETE FROM felhasznalo WHERE id='$userId';";
            $statement = $conn->prepare($sql);
            $statement->execute();

            echo "
                    <script src='
                    https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.all.min.js
                    '></script>
                    <script src='/App/js/jquery-3.6.3.min.js'></script>
                    <script type='text/javascript'>
                    $(document).ready(function(){
                        Swal.fire('A felhasználó sikeresen törölve!', '', 'success').then((result) => {
                            if(result.isConfirmed) {
                                window.history.back();
                            } else {
                                window.history.back();
                            }
                        })
                    });
                    </script>";
        }
    }

    public static function adminOrders() {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $sql = "SELECT mt.megrendeles_id,mt.mennyiseg,mt.ar,m.megrendeles_datuma,m.megrendeles_statusz,t.nev,t.cikkszam,me.megnevezes FROM `megrendelt_termekek` as mt INNER JOIN megrendeles as m on mt.megrendeles_id=m.id INNER JOIN termek as t on mt.termek_id=t.id INNER JOIN meretek as me on mt.meret_id=me.id ORDER BY m.megrendeles_datuma ASC, m.megrendeles_statusz DESC;";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();

        return $statement->fetchAll();
    }

    public static function orderById($orderId) {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $sql = "SELECT m.megrendeles_datuma,m.megrendeles_statusz,m.szallitasi_cim,m.varos,m.iranyitoszam,m.orszag,m.telefonszam,fe.felhasznalonev,fe.vezeteknev,fe.keresztnev,fe.email FROM megrendeles as m INNER JOIN felhasznalo as fe on m.felhasznalo_id=fe.id WHERE m.id='$orderId';";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();

        return $statement->fetch();
    }

    public static function orderProductById($sendId) {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $sql = "SELECT mt.megrendeles_id,mt.mennyiseg,mt.ar,m.megrendeles_datuma,m.megrendeles_statusz,t.nev,t.cikkszam,me.megnevezes FROM `megrendelt_termekek` as mt INNER JOIN megrendeles as m on mt.megrendeles_id=m.id INNER JOIN termek as t on mt.termek_id=t.id INNER JOIN meretek as me on mt.meret_id=me.id WHERE mt.megrendeles_id='$sendId';";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();

        return $statement->fetchAll();
    }

    public static function orderSend($sendId) {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $order = AdminDao::orderById($sendId);
        $orderProduct = AdminDao::orderProductById($sendId);

        if ($order->megrendeles_statusz == 0) {
                $mail = new PHPMailer(true);

                try {
                    $mail->SMTPDebug = 0;
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'mester.harmas.webshop@gmail.com';
                    $mail->Password   = 'vibwbeoherfapfuf';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port       = '587';

                    $to = $order->email;
                    $subject = "Webshop rendelés";
                    $base_url = "http://localhost:8000/";

                    $message = "
                    <html>
                    <head>
                    <title>{$subject}</title>
                    </head>
                    <body>
                    <p><strong>Tisztelt {$order->felhasznalonev}!</strong></p>
                    <p>Rendelését leadtuk!</p>
                    </body>
                    </html>
                    ";

                    $mail->setFrom('mester.harmas.webshop@gmail.com', 'Webshop');
                    $mail->addAddress($to, $order->felhasznalonev);

                    $mail->isHTML(true);
                    $mail->CharSet = 'UTF-8';
                    $mail->Subject = $subject;
                    $mail->Body    = $message;

                    $mail->send();

                    foreach ($orderProduct as $product) {
                        $mail = new PHPMailer(true);

                        $mail->SMTPDebug = 0;
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.gmail.com';
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'mester.harmas.webshop@gmail.com';
                        $mail->Password   = 'vibwbeoherfapfuf';
                        $mail->SMTPSecure = 'tls';
                        $mail->Port       = '587';

                        $to = "mester.harmas.webshop@gmail.com";
                        $subject = "Webshop rendelés";
                        $base_url = "http://localhost:8000/";

                        $message = "
                        <html>
                        <head>
                        <title>{$subject}</title>
                        </head>
                        <body>
                        <p><strong>Tisztelt Webshop!</strong></p>
                        <p>Új rendelés:</p>
                        <br>
                        <p>Termék: {$product->nev}</p>
                        <p>Cikkszám: {$product->cikkszam}</p>
                        <p>Méret: {$product->megnevezes}</p>
                        <p>Mennyiség: {$product->mennyiseg}</p>
                        <p>Ár: {$product->ar}</p>
                        <br>
                        <p>Szállítási adatok:</p>
                        <br>
                        <p>Vezetéknév: {$order->vezeteknev}</p>
                        <p>Keresztnév: {$order->keresztnev}</p>
                        <p>Szállítási cím: {$order->szallitasi_cim}</p>
                        <p>Település: {$order->varos}</p>
                        <p>Irányítószám: {$order->iranyitoszam}</p>
                        <p>Ország: {$order->orszag}</p>
                        <p>Telefonszam: {$order->telefonszam}</p>
                        </body>
                        </html>
                        ";

                        $mail->setFrom('mester.harmas.webshop@gmail.com', 'Webshop');
                        $mail->addAddress($to, 'Webshop');

                        $mail->isHTML(true);
                        $mail->CharSet = 'UTF-8';
                        $mail->Subject = $subject;
                        $mail->Body    = $message;

                        $mail->send();
                    }
                } catch (Exception $e) {
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
                            text: 'Az email nincs elküldve. Próbálja újra!'
                        }).then((result) => {
                            if(result.isConfirmed) {
                                document.location='/adminPage'
                            } else {
                                document.location='/adminPage'
                            }
                        })
                    });
                    </script>";
                }

            $sql = "UPDATE `megrendeles` SET `megrendeles_statusz`='1' WHERE id='$sendId';";
            $statement = $conn->prepare($sql);
            $statement->execute();

            date_default_timezone_set('Europe/Budapest');
            foreach ($orderProduct as $product) {
                AdminDao::incomeAdd(date("Y-m"), $product->ar);
            }
            AdminDao::adminLog($_SESSION["user_id"], 16, $order->felhasznalonev);
            echo "
                    <script src='
                    https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.all.min.js
                    '></script>
                    <script src='/App/js/jquery-3.6.3.min.js'></script>
                    <script type='text/javascript'>
                    $(document).ready(function(){
                        Swal.fire('A rendelés sikeresen elküldve!', '', 'success').then((result) => {
                            if(result.isConfirmed) {
                                window.history.back();
                            } else {
                                window.history.back();
                            }
                        })
                    });
                    </script>";
        } else {
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
                        text: 'Ezt a rendelést már leadták!'
                    }).then((result) => {
                        if(result.isConfirmed) {
                            window.history.back();
                        } else {
                            window.history.back();
                        }
                    })
                });
                </script>";
        }
    }

    public static function orderDelete($deleteId) {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $order = AdminDao::orderById($deleteId);

        if ($order->megrendeles_statusz == 0) {
            $mail = new PHPMailer(true);

            try {
                $mail->SMTPDebug = 0;
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'mester.harmas.webshop@gmail.com';
                $mail->Password   = 'vibwbeoherfapfuf';
                $mail->SMTPSecure = 'tls';
                $mail->Port       = '587';

                $to = $order->email;
                $subject = "Webshop rendelés";
                $base_url = "http://localhost:8000/";

                $message = "
                <html>
                <head>
                <title>{$subject}</title>
                </head>
                <body>
                <p><strong>Tisztelt {$order->felhasznalonev}!</strong></p>
                <p>Rendelését nem tudjuk teljesíteni, ezért töröltük! Az összeget hamarosan visszautaljuk!</p>
                </body>
                </html>
                ";

                $mail->setFrom('mester.harmas.webshop@gmail.com', 'Webshop');
                $mail->addAddress($to, $order->felhasznalonev);

                $mail->isHTML(true);
                $mail->CharSet = 'UTF-8';
                $mail->Subject = $subject;
                $mail->Body    = $message;

                $mail->send();
            } catch (Exception $e) {
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
                        text: 'Az email nincs elküldve. Próbálja újra!'
                    }).then((result) => {
                        if(result.isConfirmed) {
                            document.location='/adminPage'
                        } else {
                            document.location='/adminPage'
                        }
                    })
                });
                </script>";
            }
        }
        AdminDao::adminLog($_SESSION["user_id"], 17, $order->felhasznalonev);

        $sql = "DELETE FROM `megrendelt_termekek` WHERE megrendeles_id='$deleteId';";
        $statement = $conn->prepare($sql);
        $statement->execute();

        $sql = "DELETE FROM `megrendeles` WHERE id='$deleteId';";
        $statement = $conn->prepare($sql);
        $statement->execute();

        echo "
                    <script src='
                    https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.all.min.js
                    '></script>
                    <script src='/App/js/jquery-3.6.3.min.js'></script>
                    <script type='text/javascript'>
                    $(document).ready(function(){
                        Swal.fire('A rendelés sikeresen törölve!', '', 'success').then((result) => {
                            if(result.isConfirmed) {
                                window.history.back();
                            } else {
                                window.history.back();
                            }
                        })
                    });
                    </script>";
    }

    public static function jogosultsagok() {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $sql = "SELECT * FROM jogosultsagok;";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();

        return $statement->fetchAll();
    }

    public static function adminUserSearch() {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        try {
            $query = $_POST["query"];
            $statement = $conn->prepare("SELECT * FROM felhasznalo WHERE felhasznalonev LIKE :query;");
            $statement->bindValue(':query', '%' . $query . '%');
            $statement->execute();
            if ($statement->rowCount() > 0) {
                $sql = "SELECT f.id,f.felhasznalonev,f.vezeteknev,f.keresztnev,f.telefonszam,j.megnevezes,f.email FROM felhasznalo as f
                INNER JOIN jogosultsagok as j on f.jogosultsag_id=j.id WHERE f.felhasznalonev LIKE :query ORDER BY f.id;";
                $statement = $conn->prepare($sql);
                $statement->bindValue(':query', '%' . $query . '%');
                $statement->execute(); 
                $felhasznalok = $statement->fetchAll();
            } else {
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
                        text: 'Nincs ilyen termék!'
                    }).then((result) => {
                        if(result.isConfirmed) {
                            window.history.back();
                        } else {
                            window.history.back();
                        }
                    })
                });
                </script>";
            }
        } catch(PDOException $e) {
            echo "Error: ".$e->getMessage();
        }
        return $felhasznalok;
    }

    public static function adminLog($felhasznalo_id, $log_kategoria_id, $azonosito) {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $sql = "INSERT INTO `log`(`felhasznalo_id`, `log_kategoria_id`, `azonosito`, `idopont`) VALUES ('$felhasznalo_id', '$log_kategoria_id', '$azonosito', now());";
        $statement = $conn->prepare($sql);
        $statement->execute();
    }

    public static function adminLogs($categoryId) {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        if ($categoryId == 0) {
            $felt = "";
        } else {
            $felt = "WHERE lk.id='$categoryId'";
        }

        $sql = "SELECT l.id,l.azonosito,l.idopont,lk.esemeny,f.felhasznalonev FROM `log` as l INNER JOIN log_kategoriak as lk on lk.id=l.log_kategoria_id INNER JOIN felhasznalo as f on f.id=l.felhasznalo_id $felt ORDER BY l.idopont DESC;";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();

        return $statement->fetchAll();
    }

    public static function adminLogDelete($deleteLogId) {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $sql = "DELETE FROM `log` WHERE id='$deleteLogId';";
        $statement = $conn->prepare($sql);
        $statement->execute();

        echo "
                    <script src='
                    https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.all.min.js
                    '></script>
                    <script src='/App/js/jquery-3.6.3.min.js'></script>
                    <script type='text/javascript'>
                    $(document).ready(function(){
                        Swal.fire('Az esemény sikeresen törölve!', '', 'success').then((result) => {
                            if(result.isConfirmed) {
                                window.history.back();
                            } else {
                                window.history.back();
                            }
                        })
                    });
                    </script>";
    }

    public static function adminLogCategoryDelete($categoryId) {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        if ($categoryId == 0) {
            $felt = "";
        } else {
            $felt = "WHERE log_kategoria_id='$categoryId'";
        }

        $sql = "DELETE FROM `log` $felt;";
        $statement = $conn->prepare($sql);
        $statement->execute();

        echo "
                    <script src='
                    https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.all.min.js
                    '></script>
                    <script src='/App/js/jquery-3.6.3.min.js'></script>
                    <script type='text/javascript'>
                    $(document).ready(function(){
                        Swal.fire('Az események sikeresen törölve!', '', 'success').then((result) => {
                            if(result.isConfirmed) {
                                window.history.back();
                            } else {
                                window.history.back();
                            }
                        })
                    });
                    </script>";
        
    }

    public static function adminLogCategories() {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $sql = "SELECT * FROM log_kategoriak;";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();

        return $statement->fetchAll();
    }

    public static function incomeAdd($datum, $bevetel) {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        date_default_timezone_set('Europe/Budapest');
        
        if (date("m",strtotime($datum)) == "01") {
            $honap = "januar";
        } elseif (date("m",strtotime($datum)) == "02") {
            $honap = "februar";
        } elseif (date("m",strtotime($datum)) == "03") {
            $honap = "marcius";
        } elseif (date("m",strtotime($datum)) == "04") {
            $honap = "aprilis";
        } elseif (date("m",strtotime($datum)) == "05") {
            $honap = "majus";
        } elseif (date("m",strtotime($datum)) == "06") {
            $honap = "junius";
        } elseif (date("m",strtotime($datum)) == "07") {
            $honap = "julius";
        } elseif (date("m",strtotime($datum)) == "08") {
            $honap = "augusztus";
        } elseif (date("m",strtotime($datum)) == "09") {
            $honap = "szeptember";
        } elseif (date("m",strtotime($datum)) == "10") {
            $honap = "oktober";
        } elseif (date("m",strtotime($datum)) == "11") {
            $honap = "november";
        } elseif (date("m",strtotime($datum)) == "12") {
            $honap = "december";
        }

        $sql = "SELECT * FROM evi_bevetel WHERE honap='$honap';";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $jovedelem = $statement->fetch();

        $osszeg = $jovedelem->bevetel;

        $sql = "UPDATE `evi_bevetel` SET `bevetel`='$osszeg'+'$bevetel' WHERE `honap`='$honap'";
        $statement = $conn->prepare($sql);
        $statement->execute();
    }

    public static function incomePerMonth() {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        date_default_timezone_set('Europe/Budapest');

        $sql = "SELECT * FROM evi_bevetel;";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $bevetelek = $statement->fetchAll();
        $ev = $bevetelek[0]->ev;

        if ($ev < date("Y")) {
            $ev = date("Y");
            $sql = "UPDATE `evi_bevetel` SET `ev`='$ev', `bevetel`='0';";
            $statement = $conn->prepare($sql);
            $statement->execute();
        }

        $sql = "SELECT * FROM evi_bevetel;";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        $bevetelek = $statement->fetchAll();

        return $bevetelek;
    }

    public static function adminAndUser() {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $sql = "SELECT * FROM felhasznalo WHERE jogosultsag_id='1';";
        $statement = $conn->prepare($sql);
        $statement->execute();
        $osszesAdmin = $statement->rowCount();

        $sql = "SELECT * FROM felhasznalo WHERE jogosultsag_id='2';";
        $statement = $conn->prepare($sql);
        $statement->execute();
        $osszesVasarlo = $statement->rowCount();

        $felhasznalok = [];
        $felhasznalok["admin"] = $osszesAdmin;
        $felhasznalok["vasarlo"] = $osszesVasarlo;

        return $felhasznalok;
    }
}