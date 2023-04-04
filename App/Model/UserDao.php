<?php namespace App\Model;

use App\Lib\Database;
use App\Model\AdminDao;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
  
class UserDao
{
    public static function register() {
        require 'vendor/autoload.php';

        error_reporting(0);

        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $full_name = $_POST["signup_full_name"];
        $email = $_POST["signup_email"];
        $password = $_POST['signup_password'];
        $cpassword = $_POST['signup_password2'];
        $token = bin2hex(random_bytes(5));
        $status = 0;
        $jogosultsag_id = 2;

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
                    text: 'A két jelszó nem egyezik!'
                }).then((result) => {
                    if(result.isConfirmed) {
                        document.location='/loginPage';
                    } else {
                        document.location='/loginPage';
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
                        document.location='/loginPage';
                    } else {
                        document.location='/loginPage';
                    }
                })
            });
            </script>";
        } else {
            $password = password_hash($_POST['signup_password'], PASSWORD_ARGON2ID);

            $sql = "INSERT INTO felhasznalo (`felhasznalonev`, `email`, `jelszo`, `token`, `sztatusz`, `jogosultsag_id`) VALUES (:full_name, :email, :password, :token, :status, :jogosultsag_id)";

            $statement = $conn->prepare($sql);
            $statement->execute([
            'full_name'=>$full_name,
            'email'=>$email,
            'password'=>$password,
            'token'=>$token,
            'status'=>$status,
            'jogosultsag_id'=>$jogosultsag_id,
            ]);

            if ($statement) {

                $to = $email;
                $subject = "Email visszaigazolás";
                $base_url = "http://localhost:8000/";

                $message = "
                <html>
                <head>
                <title>{$subject}</title>
                </head>
                <body>
                <p><strong>Tisztelt {$full_name}!</strong></p>
                <p>Megerősítő link:</p>
                <p><a href='{$base_url}index.php?token={$token}'>Email megerősítése</a></p>
                </body>
                </html>
                ";

                $mail = new PHPMailer(true);

                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'mester.harmas.webshop@gmail.com';
                    $mail->Password   = 'vibwbeoherfapfuf';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port       = '587';

                    $mail->setFrom('mester.harmas.webshop@gmail.com', 'Webshop');
                    $mail->addAddress($to, $full_name);

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
                                document.location='/loginPage';
                            } else {
                                document.location='/loginPage';
                            }
                        })
                    });
                    </script>";
                } catch (Exception $e) {
                    echo "<script src='
                    https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.all.min.js
                    '></script>
                    <script src='/App/js/jquery-3.6.3.min.js'></script>
                    <script type='text/javascript'>
                    $(document).ready(function(){
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Az email elküldése sikertelen. Próbálja újra!'
                        }).then((result) => {
                            if(result.isConfirmed) {
                                document.location='/loginPage';
                            } else {
                                document.location='/loginPage';
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
                echo "<script src='
                https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.all.min.js
                '></script>
                <script src='/App/js/jquery-3.6.3.min.js'></script>
                <script type='text/javascript'>
                $(document).ready(function(){
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'A regisztráció sikertelen!'
                    }).then((result) => {
                        if(result.isConfirmed) {
                            document.location='/loginPage';
                        } else {
                            document.location='/loginPage';
                        }
                    })
                });
                </script>";
            }
        }
    }

    public static function login() {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $email = $_POST["email"];

        $sql = "SELECT * FROM felhasznalo WHERE email='$email' AND sztatusz='1'";
        $prepare = $conn->prepare($sql);
        $prepare->execute();
        $row = $prepare->fetch(\PDO::FETCH_ASSOC);
        $password = $row['jelszo'];

        $passwordVerify = password_verify($_POST['password'], $password);

        if ($passwordVerify) {
            $_SESSION["user_id"] = $row['id'];
            AdminDao::adminLog($row['id'], 2, "-");
            header("Location: http://localhost:8000/products");
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
                    text: 'Helytelen bejelentkezési adatok. Próbálja újra!'
                }).then((result) => {
                    if(result.isConfirmed) {
                        document.location='/loginPage';
                    } else {
                        document.location='/loginPage';
                    }
                })
            });
            </script>";
        }
    }

    public static function verify_email($token) {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        if (isset($token)) {
            $sql = "UPDATE felhasznalo SET `sztatusz`='1' WHERE token='$token'";
            $statement = $conn->prepare($sql);
            $statement->execute();
    
            $prepare = $conn->prepare("SELECT id FROM felhasznalo WHERE token='$token'");
            $prepare->execute();
            $showUserId = $prepare->fetch(\PDO::FETCH_ASSOC);
            $_SESSION["user_id"] = $showUserId['id'];
            if (!isset($_SESSION["user_id"])) {
                header("Location: /loginPage");
            } else {
                header("Location: /products");
            }
        }
    }

    public static function logout() {
        ?>
        <script src='
            https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.all.min.js
            '></script>
            <script src='/App/js/jquery-3.6.3.min.js'></script>
            <script type='text/javascript'>
            $(document).ready(function(){
                Swal.fire('Sikeresen kijelentkezett!', '', 'success').then((result) => {
                    if (result.isConfirmed) {
                        <?php
                            $id = $_SESSION['user_id'];
                            AdminDao::adminLog($id, 4, "-");
                            session_unset();
                            session_destroy();
                        ?>
                        document.location='/products';
                    } else {
                        document.location='/products';
                    }
                })
            });
        </script>;
        <?php
    }

    public static function forgotPassword() {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        require 'vendor/autoload.php';

        error_reporting(0);


        if (isset($_POST["resetPassword"])) {
            $email = $_POST["email"];

            $sql = "SELECT * FROM felhasznalo WHERE email='$email'";
            $res = $conn->query($sql);
            $check_email = $res->fetchColumn();

            if ($check_email > 0) {
                $prepare = $conn->prepare($sql);
                $prepare->execute();
                $data = $prepare->fetch(\PDO::FETCH_ASSOC);

                $to = $email;
                $subject = "Jelszó megváltoztatása";

                $message = "
                <html>
                <head>
                <title>{$subject}</title>
                </head>
                <body>
                <p><strong>Tisztelt {$data['full_name']}!</strong></p>
                <p>Elfeljtette a jelszavát? Itt tudja megváltoztatni.</p>
                <p><a href='http://localhost:8000/index.php?usertoken={$data['token']}'>Jelszó megváltoztatása</a></p>
                </body>
                </html>
                ";

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

                    $mail->setFrom('mester.harmas.webshop@gmail.com', 'Webshop');
                    $mail->addAddress($email, $data['full_name']);

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
                                document.location='/loginPage';
                            } else {
                                document.location='/loginPage';
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
                            text: 'Az email elküldése sikertelen. Próbálja újra!'
                        }).then((result) => {
                            if(result.isConfirmed) {
                                document.location='/loginPage';
                            } else {
                                document.location='/loginPage';
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
                        text: 'Az email cím nem található!'
                    }).then((result) => {
                        if(result.isConfirmed) {
                            document.location='/loginPage';
                        } else {
                            document.location='/loginPage';
                        }
                    })
                });
                </script>";
            }
        }
    }

    public static function resetPassword() {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        error_reporting(0);

        if (isset($_POST["resetPassword"])) {
            $password = $_POST["new_password"];
            $cpassword = $_POST["cnew_password"];
            if ($password === $cpassword) {
                $password = password_hash($_POST['new_password'], PASSWORD_ARGON2ID);
                $userToken = $_POST['usertoken'];
                $sql = "UPDATE felhasznalo SET jelszo='$password' WHERE token='$userToken'";
                $statement = $conn->prepare($sql);
                $statement->execute();
                header("Location: /loginPage");
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
                        text: 'A jelszó nem egyezik!'
                    }).then((result) => {
                        if(result.isConfirmed) {
                            document.location='/loginPage';
                        } else {
                            document.location='/loginPage';
                        }
                    })
                });
                </script>";
            }
        }
    }

    public static function userToken($usertoken) {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $statement = $conn->prepare("SELECT * FROM felhasznalo WHERE token=:usertoken;");
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute([
            'usertoken'=>$usertoken,
        ]);
        return $statement->fetch();
    }

    public static function logedinUser() {
        if (!isset($_SESSION["user_id"])) {
            return true;
        } else {
            return false;
        }
    }

    public static function userData() {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $id = 0;
        if (isset($_SESSION["user_id"])) {
            $id = $_SESSION["user_id"];
            $statement = $conn->prepare("SELECT * FROM felhasznalo WHERE id='$id' AND sztatusz='1';");
            $statement->setFetchMode(\PDO::FETCH_OBJ);
            $statement->execute();
            return $statement->fetch();
        } else {
            return $id;
        }
    }


    public static function userChange() {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $id = $_POST["id"];
        $felhasznalonev = $_POST["felhasznalonev"];
        $vezeteknev = $_POST["vezeteknev"];
        $keresztnev = $_POST["keresztnev"];
        $telefonszam = $_POST["telefonszam"];

        
        if (is_numeric($telefonszam) && strlen($telefonszam) == 9 && (mb_substr($telefonszam, 0, 2) == 30 || mb_substr($telefonszam, 0, 2) == 20 || mb_substr($telefonszam, 0, 2) == 70)) {
            $sql = "UPDATE felhasznalo SET felhasznalonev='$felhasznalonev', vezeteknev='$vezeteknev', keresztnev='$keresztnev', telefonszam='06$telefonszam' WHERE id='$id'";
            $statement = $conn->prepare($sql);
            $statement->execute();
            echo "
            <script src='
            https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.all.min.js
            '></script>
            <script src='/App/js/jquery-3.6.3.min.js'></script>
            <script type='text/javascript'>
            $(document).ready(function(){
                Swal.fire('Sikeres mentés!', '', 'success').then((result) => {
                    if(result.isConfirmed) {
                        document.location='/welcomePage';
                    } else {
                        document.location='/welcomePage';
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
                    text: 'Helytelen telefonszám!'
                }).then((result) => {
                    if(result.isConfirmed) {
                        document.location='/welcomePage';
                    } else {
                        document.location='/welcomePage';
                    }
                })
            });
            </script>";
        }
    }

    public static function userShippingData($userData) {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $id = $userData->id;

        $statement = $conn->prepare("SELECT * FROM felhasznalo_szallitasi_adatok WHERE felhasznalo_id='$id';");
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();
        return $statement->fetch();
    }

    public static function shippingChange() {
        $dbObj = new Database();
        $conn = $dbObj->getConnection();

        $id = $_POST["id"];
        $szallitasi_cim1 = $_POST["szallitasi_cim1"];
        $szallitasi_cim2 = $_POST["szallitasi_cim2"];
        $varos = $_POST["varos"];
        (int)$iranyitoszam = $_POST["iranyitoszam"];
        $orszag = $_POST["orszag"];
        $telefonszam = $_POST["telefonszam"];
        
        $sql = "SELECT * FROM felhasznalo_szallitasi_adatok WHERE felhasznalo_id='$id'";
        $statement = $conn->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();

        if ($statement->fetchColumn()) {
            if (is_numeric($telefonszam) && strlen($telefonszam) == 9 && (mb_substr($telefonszam, 0, 2) == 30 || mb_substr($telefonszam, 0, 2) == 20 || mb_substr($telefonszam, 0, 2) == 70)) {
                if (is_numeric($iranyitoszam) && strlen($iranyitoszam) == 4) {
                    $sql = "UPDATE felhasznalo_szallitasi_adatok SET szallitasi_cim1='$szallitasi_cim1', szallitasi_cim2='$szallitasi_cim2', varos='$varos', iranyitoszam='$iranyitoszam', orszag='$orszag', telefonszam='06$telefonszam' WHERE felhasznalo_id='$id'";
                    $statement = $conn->prepare($sql);
                    $statement->execute();
                    echo "
                    <script src='
                    https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.all.min.js
                    '></script>
                    <script src='/App/js/jquery-3.6.3.min.js'></script>
                    <script type='text/javascript'>
                            $(document).ready(function(){
                            Swal.fire('Sikeres mentés!', '', 'success').then((result) => {
                                if(result.isConfirmed) {
                                document.location='/shippingPage';
                                } else {
                                    document.location='/shippingPage';
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
                            text: 'Helytelen irányítószám!'
                        }).then((result) => {
                            if(result.isConfirmed) {
                                document.location='/shippingPage';
                            } else {
                                document.location='/shippingPage';
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
                            text: 'Helytelen telefonszám!'
                        }).then((result) => {
                            if(result.isConfirmed) {
                                document.location='/shippingPage';
                            } else {
                                document.location='/shippingPage';
                            }
                        })
                    });
                    </script>";
            } 
        } else {
            if (is_numeric($telefonszam) && strlen($telefonszam) == 9) {
                if (is_numeric($iranyitoszam) && strlen($iranyitoszam) == 4) {
                    $sql = "INSERT INTO felhasznalo_szallitasi_adatok (`felhasznalo_id`, `szallitasi_cim1`, `szallitasi_cim2`, `varos`, `iranyitoszam`, `orszag`, `telefonszam`) VALUES ('$id', '$szallitasi_cim1', '$szallitasi_cim2', '$varos', '$iranyitoszam', '$orszag', '06$telefonszam')";
                    $statement = $conn->prepare($sql);
                    $statement->execute();
                    echo "
                    <script src='
                    https://cdn.jsdelivr.net/npm/sweetalert2@11.7.2/dist/sweetalert2.all.min.js
                    '></script>
                    <script src='/App/js/jquery-3.6.3.min.js'></script>
                    <script type='text/javascript'>
                            $(document).ready(function(){
                            Swal.fire('Sikeres mentés!', '', 'success').then((result) => {
                                if(result.isConfirmed) {
                                document.location='/shippingPage';
                                } else {
                                    document.location='/shippingPage';
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
                            text: 'Helytelen irányítószám!'
                        }).then((result) => {
                            if(result.isConfirmed) {
                                document.location='/shippingPage';
                            } else {
                                document.location='/shippingPage';
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
                            text: 'Helytelen telefonszám!'
                        }).then((result) => {
                            if(result.isConfirmed) {
                                document.location='/shippingPage';
                            } else {
                                document.location='/shippingPage';
                            }
                        })
                    });
                    </script>";
            } 
        }
    }
}