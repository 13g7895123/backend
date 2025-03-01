<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->options('(:any)', function() {
    return '';
});

$routes->group('api', ['namespace' => 'App\Controllers\Jiachu'], function($routes) {
    /* 推廣 */
    $routes->group('promotion', ['namespace' => 'App\Controllers\Promotion'], function ($routes){
        // 前台
        $routes->match(['post'], 'server', 'Server::getServer');            // 取得伺服器資料
        $routes->match(['post'], 'player/submit', 'Player::submit');        // 驗證頁提交資料
        $routes->match(['post'], 'player/info', 'Player::getPlayerInfo');         // 取得使用者資訊

        $routes->match(['get'], 'all', 'Promotion::index');                 // 取得推廣項目資料
        $routes->match(['get'], 'detail/(:num)', 'PromotionItem::index/$1');    // 取得推廣項目資料(id)
        $routes->match(['put'], 'detail/update/(:num)', 'PromotionItem::update/$1');    // 更新推廣項目資料
        
        
        $routes->match(['post'], 'file', 'FileController::upload');         // 上傳檔案
        $routes->match(['post'], '/', 'Promotion::create');                 // 建立推廣資料
        $routes->match(['delete'], '(:num)', 'Promotion::delete/$1');       // 刪除推廣資料
        $routes->match(['post'], 'items', 'PromotionItem::create');         // 建立推廣資料
        $routes->match(['get'], 'user/info/(:num)', 'User::getUserId/$1');  // 取得User Id(測試用)
        $routes->match(['get'], 'user/test', 'User::test');                 // 測試
        $routes->match(['post'], 'line/state/save', 'User::saveState');
        $routes->match(['get'], 'line/callback', 'User::callback');
        
        $routes->match(['post'], 'user/email', 'User::updateEmailNotify');  // 更新信箱通知
        $routes->match(['post'], 'user/line', 'User::updateLineNotify');    // 更新Line通知

        // 後台
        $routes->match(['get'], 'user', 'User::index');                   // 取得使用者資料
        $routes->match(['get'], 'manager', 'User::getManager');             // 取得管理者資料
        $routes->match(['get'], 'player', 'Player::index');                 // 取得玩家資料
        $routes->match(['post'], 'user/create', 'User::create');            // 新增使用者
        $routes->match(['post'], 'user/condition', 'User::condition');            // 新增使用者
        $routes->match(['post'], 'manager/create', 'User::create');         // 新增管理者
    });
});
