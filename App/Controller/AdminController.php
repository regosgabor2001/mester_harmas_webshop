<?php namespace App\Controller;

use App\Model\AdminDao;
use App\Model\UserDao;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class AdminController
{
    public function adminLoginPage()
    {
        $twig = (new AdminController())->setTwigEnvironment();
        echo $twig->render('admin/adminLogin.html.twig');
    }

    public function adminRegisterPage()
    {
        $twig = (new AdminController())->setTwigEnvironment();
        echo $twig->render('admin/adminRegister.html.twig');
    }

    public function adminLogin()
    {
        if (isset($_POST['login']))
        {
            AdminDao::adminLogin();
        } else {
            header("Location: /admin");
        }
    }

    public function adminPage()
    {
        $adminData = AdminDao::adminData();
        $honapJovedelem = AdminDao::incomePerMonth();
        $adminUserCount = AdminDao::adminAndUser();
        $twig = (new AdminController())->setTwigEnvironment();
        if (!is_int($adminData)) {
            echo $twig->render('admin/index.html.twig', ['admin'=>$adminData, 'bevetelek'=>$honapJovedelem, 'adminSzam'=>$adminUserCount["admin"], 'vasarloSzam'=>$adminUserCount["vasarlo"]]);
        } else {
            echo $twig->render('404.html.twig');
        }
    }

    public function adminLogout() {
        AdminDao::adminLogout();
    }

    public function adminRegist()
    {
        if (isset($_POST['regist']))
        {
            AdminDao::adminRegist();
        } else {
            header("Location: /admin");
        }
    }

    public function verify_admin($adminToken, $userToken)
    {
        if (isset($adminToken)) {
            $adminData = UserDao::userToken($adminToken);
            $userData = UserDao::userToken($userToken);
            $twig = (new AdminController())->setTwigEnvironment();
            echo $twig->render('admin/admin_verify.html.twig', ['admin'=>$adminData, 'user'=>$userData]);
        } else {
            header("Location: /admin");
        }
    }

    public function adminVerify() {
        if (isset($_POST["megerosit"]) || isset($_POST["torol"])) {
            AdminDao::adminVerify();
        } else {
            header("Location:/admin");
        }
    }

    public function adminProducts($nemId, $kategoriaId, $markaId) {
        $twig = (new AdminController())->setTwigEnvironment();
        $adminData = AdminDao::adminData();
        $kategoriak = AdminDao::termek_kategoria();
        $productData = AdminDao::osszesTermek($nemId, $kategoriaId, $markaId);
        if (!is_int($adminData)) {
            echo $twig->render('admin/admin_products.html.twig', ['admin'=>$adminData, 'markaId'=>$productData["markaId"], 'kategoria_id'=>$productData["kategoria_id"], 'termekek'=>$productData["termekek"], 'nemek'=>$productData["nemek"], 'markak'=>$productData["markak"], 'nemId'=>$productData["nemId"], 'termek_kategoria'=>$kategoriak["termek_kategoria"], 'termek_termek_kategoria'=>$kategoriak["termek_termek_kategoria"]]);
        } else {
            echo $twig->render('404.html.twig');
        }
    }

    public function adminProductSearch() {
        $twig = (new AdminController())->setTwigEnvironment();
        $adminData = AdminDao::adminData();
        $kategoriak = AdminDao::termek_kategoria();
        $productData = AdminDao::searchProduct();
        if (!is_int($adminData)) {
            echo $twig->render('admin/admin_products.html.twig', ['admin'=>$adminData, 'termekek'=>$productData["termekek"], 'nemek'=>$productData["nemek"], 'termek_kategoria'=>$kategoriak["termek_kategoria"], 'termek_termek_kategoria'=>$kategoriak["termek_termek_kategoria"]]);
        } else {
            echo $twig->render('404.html.twig');
        }
    }

    public function productChange($productId) {
        $adminData = AdminDao::adminData();
        $productData = AdminDao::productById($productId);
        $twig = (new AdminController())->setTwigEnvironment();
        if (!is_int($adminData)) {
            echo $twig->render('admin/admin_product.html.twig', ['admin'=>$adminData, 'product'=>$productData["termek"], 'kepek'=>$productData["kepek"], 'nem'=>$productData["nem"], 'marka'=>$productData["marka"], 'termek_kategoria'=>$productData["termek_kategoria"], 'termek_termek_kategoria'=>$productData["termek_termek_kategoria"]]);
        } else {
            echo $twig->render('404.html.twig');
        }
    }

    public function adminNewProduct() {
        $adminData = AdminDao::adminData();
        $kategoriak = AdminDao::termek_kategoria();
        $nemek_markak = AdminDao::nemek_marka();
        $twig = (new AdminController())->setTwigEnvironment();
        if (!is_int($adminData)) {
            echo $twig->render('admin/admin_new_product.html.twig', ['admin'=>$adminData, 'termek_kategoria'=>$kategoriak["termek_kategoria"], 'nem'=>$nemek_markak["nemek"], 'marka'=>$nemek_markak["marka"]]);
        } else {
            echo $twig->render('404.html.twig');
        }
    }

    public function newProduct() {
        if (isset($_POST["kuld"])) {
            AdminDao::newProduct();
        } else {
            header("Location:/admin");
        }
    }

    public function seeImage($kep) {
        $adminData = AdminDao::adminData();
        $twig = (new AdminController())->setTwigEnvironment();
        if (!is_int($adminData)) {
            echo $twig->render('admin/admin_product_seeImage.html.twig', ['admin'=>$adminData, 'kep'=>$kep]);
        } else {
            echo $twig->render('404.html.twig');
        }
    }

    public function update() {
        if (isset($_POST['submit'])) {
            AdminDao::update();
        } else {
            header("Location:/admin");
        }
    }

    public function deleteImage($kepTorol) {
        $adminData = AdminDao::adminData();
        if (!is_int($adminData)) {
            AdminDao::deleteImage($kepTorol);
        } else {
            header("Location:/admin");
        }
    }

    public function delete($deleteId) {
        $adminData = AdminDao::adminData();
        if (!is_int($adminData)) {
            AdminDao::deleteById($deleteId);
        } else {
            header("Location:/admin");
        }
    }

    public function adminProductCategories() {
        $adminData = AdminDao::adminData();
        $termek_kategoriak = AdminDao::adminProductCategories();
        $twig = (new AdminController())->setTwigEnvironment();
        if (!is_int($adminData)) {
            echo $twig->render('admin/admin_product_category.html.twig', ['admin'=>$adminData, 'termek_kategoriak'=>$termek_kategoriak]);
        } else {
            echo $twig->render('404.html.twig');
        }
    }

    public function adminNewProductCategory() {
        $adminData = AdminDao::adminData();
        $twig = (new AdminController())->setTwigEnvironment();
        if (!is_int($adminData)) {
            echo $twig->render('admin/admin_new_product_category.html.twig', ['admin'=>$adminData]);
        } else {
            echo $twig->render('404.html.twig');
        }
    }

    public function newProductCategory() {
        if (isset($_POST["kuld"])) {
            AdminDao::newProductCategory();
        } else {
            header("Location:/admin");
        }
    }

    public function deleteCategory($deleteCategoryId) {
        $adminData = AdminDao::adminData();
        if (!is_int($adminData)) {
            AdminDao::deleteCategory($deleteCategoryId);
        } else {
            header("Location:/admin");
        }
    }

    public function productCategoryChange($productCategoryId) {
        $adminData = AdminDao::adminData();
        $categoryData = AdminDao::productCategoryChange($productCategoryId);
        $twig = (new AdminController())->setTwigEnvironment();
        if (!is_int($adminData)) {
            echo $twig->render('admin/admin_product_category_change.html.twig', ['admin'=>$adminData, 'category'=>$categoryData]);
        } else {
            echo $twig->render('404.html.twig');
        }
    }

    public function updateCategory() {
        if (isset($_POST["kuld"])) {
            AdminDao::updateCategory();
        } else {
            header("Location:/admin");
        }
    }

    public function adminBrands() {
        $adminData = AdminDao::adminData();
        $brandData = AdminDao::adminBrands();
        $twig = (new AdminController())->setTwigEnvironment();
        if (!is_int($adminData)) {
            echo $twig->render('admin/admin_brands.html.twig', ['admin'=>$adminData, 'markak'=>$brandData]);
        } else {
            echo $twig->render('404.html.twig');
        }
    }

    public function adminNewBrand() {
        $adminData = AdminDao::adminData();
        $twig = (new AdminController())->setTwigEnvironment();
        if (!is_int($adminData)) {
            echo $twig->render('admin/admin_new_brand.html.twig', ['admin'=>$adminData]);
        } else {
            echo $twig->render('404.html.twig');
        }
    }

    public function newBrand() {
        if (isset($_POST["kuld"])) {
            AdminDao::newBrand();
        } else {
            header("Location:/admin");
        }
    }

    public function deleteBrand($deleteBrandId) {
        $adminData = AdminDao::adminData();
        if (!is_int($adminData)) {
            AdminDao::deleteBrand($deleteBrandId);
        } else {
            header("Location:/admin");
        }
    }

    public function changeBrand($brandId) {
        $adminData = AdminDao::adminData();
        $brandData = AdminDao::changeBrand($brandId);
        $twig = (new AdminController())->setTwigEnvironment();
        if (!is_int($adminData)) {
            echo $twig->render('admin/admin_brand_change.html.twig', ['admin'=>$adminData, 'marka'=>$brandData]);
        } else {
            echo $twig->render('404.html.twig');
        }
    }

    public function updateBrand() {
        if (isset($_POST["kuld"])) {
            AdminDao::updateBrand();
        } else {
            header("Location:/admin");
        }
    }

    public function adminUsers($jogosultsagId) {
        $adminData = AdminDao::adminData();
        $users = AdminDao::users($jogosultsagId);
        $jogosultsagok = AdminDao::jogosultsagok();
        $twig = (new AdminController())->setTwigEnvironment();
        if (!is_int($adminData)) {
            echo $twig->render('admin/admin_users.html.twig', ['admin'=>$adminData, 'users'=>$users, 'jogosultsagok'=>$jogosultsagok, 'jogosultsagId'=>$jogosultsagId]);
        } else {
            echo $twig->render('404.html.twig');
        }
    }

    public function adminUserSearch() {
        $adminData = AdminDao::adminData();
        $users = AdminDao::adminUserSearch();
        $jogosultsagok = AdminDao::jogosultsagok();
        $twig = (new AdminController())->setTwigEnvironment();
        if (!is_int($adminData)) {
            echo $twig->render('admin/admin_users.html.twig', ['admin'=>$adminData, 'users'=>$users, 'jogosultsagok'=>$jogosultsagok, 'jogosultsagId'=>0]);
        } else {
            echo $twig->render('404.html.twig');
        }
    }

    public function deleteUser($userId) {
        $adminData = AdminDao::adminData();
        if (!is_int($adminData)) {
            AdminDao::deleteUser($userId);
        } else {
            header("Location:/admin");
        }
    }

    public function adminOrders() {
        $adminData = AdminDao::adminData();
        $orders = AdminDao::adminOrders();
        $twig = (new AdminController())->setTwigEnvironment();
        if (!is_int($adminData)) {
            echo $twig->render('admin/admin_orders.html.twig', ['admin'=>$adminData, 'orders'=>$orders]);
        } else {
            echo $twig->render('404.html.twig');
        }
    }

    public function orderById($orderId) {
        $adminData = AdminDao::adminData();
        $order = AdminDao::orderById($orderId);
        $twig = (new AdminController())->setTwigEnvironment();
        if (!is_int($adminData)) {
            echo $twig->render('admin/admin_orderById.html.twig', ['admin'=>$adminData, 'order'=>$order]);
        } else {
            echo $twig->render('404.html.twig');
        }
    }

    public function orderSend($sendId){
        $adminData = AdminDao::adminData();
        if (!is_int($adminData)) {
            AdminDao::orderSend($sendId);
        } else {
            header("Location:/admin");
        }
    }

    public function orderDelete($deleteId) {
        $adminData = AdminDao::adminData();
        if (!is_int($adminData)) {
            AdminDao::orderDelete($deleteId);
        } else {
            header("Location:/admin");
        }
    }

    public function adminLog($categoryId) {
        $adminData = AdminDao::adminData();
        $logs = AdminDao::adminLogs($categoryId);
        $logCategories = AdminDao::adminLogCategories();
        $twig = (new AdminController())->setTwigEnvironment();
        if (!is_int($adminData)) {
            echo $twig->render('admin/admin_log.html.twig', ['admin'=>$adminData, 'logs'=>$logs, 'kategoriak'=>$logCategories, 'kategoriaId'=>$categoryId]);
        } else {
            echo $twig->render('404.html.twig');
        }
    }

    public function adminLogDelete($deleteLogId) {
        $adminData = AdminDao::adminData();
        if (!is_int($adminData)) {
            AdminDao::adminLogDelete($deleteLogId);
        } else {
            header("Location:/admin");
        }
    }

    public function adminLogCategoryDelete($deleteLogCategoryId) {
        $adminData = AdminDao::adminData();
        if (!is_int($adminData)) {
            AdminDao::adminLogCategoryDelete($deleteLogCategoryId);
        } else {
            header("Location:/admin");
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