<?php namespace App\Controller;

use App\Model\UserDao;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class UserController
{
    public function loginPage()
    {
        $twig = (new UserController())->setTwigEnvironment();
        echo $twig->render('user/index.html.twig');
    }
    
    public function register()
    {
        if (isset($_POST['signup']))
        {
            UserDao::register();
        } else {
            header("Location: /loginPage");
        }
    }

    public function login()
    {
        if (isset($_POST['signin']))
        {
            UserDao::login();
        } else {
            header("Location: /loginPage");
        }
    }

    public function verify_email($token)
    {
        if (isset($token)) {
            UserDao::verify_email($token);
        } else {
            header("Location: /loginPage");
        }
    }

    public function logout() {
        UserDao::logout();
    }

    public function forgot_passwordPage()
    {
        $twig = (new UserController())->setTwigEnvironment();
        echo $twig->render('user/forgot_password.html.twig');
    }

    public function forgot_password()
    {
        if (isset($_POST['resetPassword'])) {
            UserDao::forgotPassword();
        } else {
            header("Location: /loginPage");
        }
    }

    public function reset_passwordPage($userToken)
    {
        $twig = (new UserController())->setTwigEnvironment();
        $userData = UserDao::userToken($userToken);
        if (!is_int($userData))
        {
            echo $twig->render('user/reset_password.html.twig', ['user'=>$userData]);
        } else
        {
            echo $twig->render('404.html.twig');
        }
    }

    public function reset_password()
    {
        if (isset($_POST['resetPassword'])) {
            UserDao::resetPassword();
        } else {
            header("Location: /loginPage");
        }
    }

    public function welcomePage()
    {
        $userData = UserDao::userData();
        $twig = (new UserController())->setTwigEnvironment();
        $logedin = UserDao::logedinUser();
        if (!is_int($userData)) {
            echo $twig->render('user/welcome.html.twig', ['user'=>$userData, 'logedin'=>$logedin]);
        } else {
            echo $twig->render('404.html.twig');
        }
    }

    public function shippingPage()
    {
        $userData = UserDao::userData();
        $userShippingData = UserDao::userShippingData($userData);
        if ($userData->telefonszam == "") {
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
                    text: 'Előbb töltse ki a profil adatokat!'
                }).then((result) => {
                    if(result.isConfirmed) {
                        document.location='/welcomePage';
                    } else {
                        document.location='/welcomePage';
                    }
                })
            });
            </script>";
        } elseif (!is_int($userData)) {
            $twig = (new UserController())->setTwigEnvironment();
            echo $twig->render('user/shipping-information.html.twig', ['userShipping'=>$userShippingData, 'user'=>$userData]);
        } else {
            echo $twig->render('404.html.twig');
        }
    }


    public function shippingChange() {
        if (isset($_POST['submit'])) {
            UserDao::shippingChange();
        } else {
            header("Location: /loginPage");
        }
    }

    public function userChange()
    {
        if (isset($_POST['submit'])) {
            UserDao::userChange();
        } else {
            header("Location: /loginPage");
        }
    }

    public function setTwigEnvironment()
    {
        $loader = new FilesystemLoader(__DIR__ . '\..\View');
        $twig = new \Twig\Environment($loader, [
            'debug' => true,
        ]);
        $twig->addExtension(new \Twig\Extension\DebugExtension()); 
        return $twig;
    }
}