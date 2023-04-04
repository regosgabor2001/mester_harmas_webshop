<?php namespace App\Controller;

use App\Model\CartDao;
use App\Model\UserDao;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class CartController
{

    public function randomlistazas()
    {
        $data = CartDao::randomTermekek();
        $logedin = UserDao::logedinUser();
        $twig = (new CartController())->setTwigEnvironment();
        if (empty($data)) {
            echo $twig->render('cart/cartItems.html.twig', ['termek'=>0,'logedin'=>$logedin]);
        } else {
            echo $twig->render('cart/cartItems.html.twig', ['termek'=>$data["termek"],'dbok'=>$data["dbok"] ,'logedin'=>$logedin]);
        }
    }

    public function cartPay() {
        CartDao::cartPay();
    }

    public function cartSuccess() {
        CartDao::cartSuccess();
    }

    public function deleteItem($deleteItem, $meretId) {
        CartDao::deleteItem($deleteItem, $meretId);
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