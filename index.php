<?php
require __DIR__ . '/vendor/autoload.php';

use App\Lib\App;
use App\Lib\Router;
use App\Lib\Request;
use App\Lib\Response;
use App\Controller\Home;
use App\Controller\ProductController;
use App\Controller\CartController;
use App\Controller\UserController;
use App\Controller\AdminController;

session_start();

//A program belépési pontja
Router::get('/', function () {
    return ((new ProductController())->list());
});

Router::get('/products', function () {
    return ((new ProductController())->list());
});

Router::get('/cartItems', function () {
    (new CartController())->randomlistazas();
});

Router::get('/ferfiOldal', function () {
    (new ProductController())->ferfiTermekekListazas(0);
});

Router::get('/ferfiOldal/kategoria/([0-9]*)', function (Request $req, Response $res) {
    (new ProductController())->ferfiTermekekListazas($req->params[0]);
});

Router::get('/noiOldal', function () {
    (new ProductController())->noiTermekekListazas(0);
});

Router::get('/noiOldal/kategoria/([0-9]*)', function (Request $req, Response $res) {
    (new ProductController())->noiTermekekListazas($req->params[0]);
});

Router::get('/gyermekOldal', function () {
    (new ProductController())->gyermekTermekekListazas(0);
});

Router::get('/gyermekOldal/kategoria/([0-9]*)', function (Request $req, Response $res) {
    (new ProductController())->gyermekTermekekListazas($req->params[0]);
});

Router::get('/loginPage', function () {
    (new UserController())->loginPage();
});

Router::post('/register', function () {
    (new UserController())->register();
});

Router::post('/login', function () {
    (new UserController())->login();
});

if (isset($_GET['token'])) {
    (new UserController())->verify_email($_GET['token']);
}

Router::get('/welcomePage', function () {
    (new UserController())->welcomePage();
});

Router::get('/logout', function () {
    (new UserController())->logout();
});

Router::get('/forgot_passwordPage', function () {
    (new UserController())->forgot_passwordPage();
});

Router::post('/forgot_password', function () {
    (new UserController())->forgot_password();
});

if (isset($_GET['usertoken'])) {
    (new UserController())->reset_passwordPage($_GET['usertoken']);
}

Router::post('/reset_password', function () {
    (new UserController())->reset_password();
});

Router::post('/userInfoChange', function () {
    (new UserController())->userChange();
});

Router::get('/shippingPage', function () {
    (new UserController())->shippingPage();
});

Router::post('/shippingInfoChange', function () {
    (new UserController())->shippingChange(0);
});

Router::get('/product/([0-9]*)', function (Request $req, Response $res) {
    (new ProductController())->productById($req->params[0]);
});

Router::post('/search', function () {
    (new ProductController())->search();
});

Router::post('/cart', function () {
    (new ProductController())->cart();
});

Router::get('/admin', function () {
    (new AdminController())->adminLoginPage();
});

Router::get('/adminRegister', function () {
    (new AdminController())->adminRegisterPage();
});

Router::post('/adminLogin', function () {
    (new AdminController())->adminLogin();
});

Router::get('/adminPage', function () {
    (new AdminController())->adminPage();
});

Router::post('/adminRegist', function () {
    (new AdminController())->adminRegist();
});

Router::get('/adminLogout', function () {
    (new AdminController())->adminLogout();
});

if (isset($_GET['adminToken'])) {
    (new AdminController())->verify_admin($_GET['adminToken'], $_GET['userToken']);
}

Router::post('/adminVerify', function () {
    (new AdminController())->adminVerify();
});

Router::get('/adminProducts', function () {
    (new AdminController())->adminProducts(0,1,0);
});

Router::post('/adminProductSearch', function () {
    (new AdminController())->adminProductSearch();
});

if (isset($_GET["nemId"], $_GET["kategoriaId"], $_GET["markaId"])) {
    (new AdminController())->adminProducts($_GET["nemId"], $_GET["kategoriaId"], $_GET["markaId"]);
}

if (isset($_GET['productId'])) {
    (new AdminController())->productChange($_GET['productId']);
}

if (isset($_GET['kep'])) {
    (new AdminController())->seeImage($_GET['kep']);
}

Router::post('/update', function () {
    (new AdminController())->update();
});

if (isset($_GET['kepTorol'])) {
    (new AdminController())->deleteImage($_GET['kepTorol']);
}

Router::get('/adminNewProduct', function () {
    (new AdminController())->adminNewProduct();
});

Router::post('/newProduct', function () {
    (new AdminController())->newProduct();
});

if (isset($_GET['deleteId'])) {
    (new AdminController())->delete($_GET['deleteId']);
}

Router::get('/adminProductCategories', function () {
    (new AdminController())->adminProductCategories();
});

Router::get('/adminNewProductCategory', function () {
    (new AdminController())->adminNewProductCategory();
});

Router::post('/newProductCategory', function () {
    (new AdminController())->newProductCategory();
});

if (isset($_GET['deleteCategoryId'])) {
    (new AdminController())->deleteCategory($_GET['deleteCategoryId']);
}

if (isset($_GET['productCategoryId'])) {
    (new AdminController())->productCategoryChange($_GET['productCategoryId']);
}

Router::post('/updateCategory', function () {
    (new AdminController())->updateCategory();
});

Router::get('/adminBrands', function () {
    (new AdminController())->adminBrands();
});

Router::get('/adminNewBrand', function () {
    (new AdminController())->adminNewBrand();
});

Router::post('/newBrand', function () {
    (new AdminController())->newBrand();
});

if (isset($_GET['deleteBrandId'])) {
    (new AdminController())->deleteBrand($_GET['deleteBrandId']);
}

if (isset($_GET['brandId'])) {
    (new AdminController())->changeBrand($_GET['brandId']);
}

Router::post('/updateBrand', function () {
    (new AdminController())->updateBrand();
});

Router::get('/adminUsers', function () {
    (new AdminController())->adminUsers(0);
});

if (isset($_GET['userDeleteId'])) {
    (new AdminController())->deleteUser($_GET['userDeleteId']);
}

Router::get('/adminOrders', function () {
    (new AdminController())->adminOrders();
});

if (isset($_GET['orderId'])) {
    (new AdminController())->orderById($_GET['orderId']);
}

if (isset($_GET['sendId'])) {
    (new AdminController())->orderSend($_GET['sendId']);
}

if (isset($_GET['deleteOrderId'])) {
    (new AdminController())->orderDelete($_GET['deleteOrderId']);
}

if (isset($_GET['jogosultsagId'])) {
    (new AdminController())->adminUsers($_GET['jogosultsagId']);
}

Router::post('/adminUserSearch', function () {
    (new AdminController())->adminUserSearch();
});

Router::get('/adminLog', function () {
    (new AdminController())->adminLog(0);
});

if (isset($_GET['deleteLogId'])) {
    (new AdminController())->adminLogDelete($_GET['deleteLogId']);
}

if (isset($_GET['deleteLogCategoryId'])) {
    (new AdminController())->adminLogCategoryDelete($_GET['deleteLogCategoryId']);
}

if (isset($_GET['logCategoryId'])) {
    (new AdminController())->adminLog($_GET['logCategoryId']);
}

Router::post('/cartPay', function () {
    (new CartController())->cartPay();
});

if (isset($_GET['deleteCartItem'])) {
    (new CartController())->deleteItem($_GET['deleteCartItem'], $_GET['meretId']);
}

Router::get('/cartSuccess', function () {
    (new CartController())->cartSuccess();
});