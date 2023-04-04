<?php namespace App\Controller;

use App\Model\ProductDao;
use App\Model\UserDao;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ProductController
{
    public function ferfiTermekekListazas($kategoria_id)
    {
       
        $data = ProductDao::ferfiTermekek($kategoria_id);
        $logedin = UserDao::logedinUser();
        $twig = (new ProductController())->setTwigEnvironment(); 
        echo $twig->render('products/ferfi_oldal.html.twig', ['termek'=>$data["termek"], 'kategoria'=>$data["kategoria"], 'aktualisKategoria'=>$kategoria_id, 'logedin'=>$logedin]);
    }

    public function noiTermekekListazas($kategoria_id)
    {
        $data = ProductDao::noiTermekek($kategoria_id);
        $logedin = UserDao::logedinUser();
        $twig = (new ProductController())->setTwigEnvironment(); 
        echo $twig->render('products/noi_oldal.html.twig', ['termek'=>$data["termek"], 'kategoria'=>$data["kategoria"], 'aktualisKategoria'=>$kategoria_id, 'logedin'=>$logedin]);
    }

    public function gyermekTermekekListazas($kategoria_id)
    {
        $data = ProductDao::gyermekTermekek($kategoria_id);
        $logedin = UserDao::logedinUser();
        $twig = (new ProductController())->setTwigEnvironment(); 
        echo $twig->render('products/gyermek_oldal.html.twig', ['termek'=>$data["termek"], 'kategoria'=>$data["kategoria"], 'aktualisKategoria'=>$kategoria_id, 'logedin'=>$logedin]);
    }

    public function list()
    {
        $data = ProductDao::ajanlottTermekek();
        $logedin = UserDao::logedinUser();
        $twig = (new ProductController())->setTwigEnvironment();
        echo $twig->render('products/products.html.twig', ['termek'=>$data, 'logedin'=>$logedin]);

    }

    public function productById(int $id)
    { 
        $teszt="Session: ".print_r($_SESSION,true);
        $userData = UserDao::userData();
        $twig = (new ProductController())->setTwigEnvironment();
        $productData = ProductDao::productById($id);
        $logedin = UserDao::logedinUser();
        if ($productData && !is_int($userData))
        {
            echo $twig->render('products/product.html.twig', ['product'=>$productData["termek"], 'meret'=>$productData["meret"], 'kepek'=>$productData["kepek"], 'logedin'=>$logedin, 'user'=>$userData]);
        } elseif ($productData) {
            echo $twig->render('products/product.html.twig', ['product'=>$productData["termek"], 'meret'=>$productData["meret"], 'kepek'=>$productData["kepek"], 'logedin'=>$logedin]);
        }
         else
        {
            echo $twig->render('404.html.twig');
        }
    }

    public function search()
    {
        $data = ProductDao::search();
        if ($data == "Nincs ilyen") {
            echo "<script>alert('Nincs ilyen.');document.location='/products';</script>";
        } else {
            $logedin = UserDao::logedinUser();
            $twig = (new ProductController())->setTwigEnvironment(); 
            echo $twig->render('products/search_product.html.twig', ['termek'=>$data, 'logedin'=>$logedin]);
        }
        
    }

    public function cartItems()
    {
        $userData = UserDao::userData();
        $twig = (new UserController())->setTwigEnvironment();
        $logedin = UserDao::logedinUser();
        if (!is_int($userData)) {
            echo $twig->render('cart/cartItems.html.twig', ['user'=>$userData, 'logedin'=>$logedin]);
        } else {
            echo $twig->render('404.html.twig');
        }
    }

    public function cart()
    {
        if (isset($_POST['submit'])) {
            ProductDao::cart();
        } else {
            header("Location:/products");
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