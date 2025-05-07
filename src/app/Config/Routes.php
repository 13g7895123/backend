<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->options('(:any)', function() {
    return '';
});

$routes->group('api', function($routes) {
    /* 推廣 */
    $routes->group('promotion', ['namespace' => 'App\Controllers\Promotion'], function ($routes){
        // 前台
        $routes->match(['post'], 'login', 'User::login');                   // 登入
        $routes->match(['post'], 'server', 'Server::getServer');            // 取得伺服器資料
        $routes->match(['post'], 'player/submit', 'Player::submit');        // 驗證頁提交資料
        $routes->match(['post'], 'player/info', 'Player::getPlayerInfo');   // 取得使用者資訊

        $routes->match(['post'], 'main', 'Promotion::index');                           // 取得推廣項目資料
        $routes->match(['post'], 'main/delete', 'Promotion::delete');                   // 刪除推廣項目資料
        $routes->match(['post'], 'main/batchAudit', 'Promotion::batchAudit');           // 更新推廣項目資料
        $routes->match(['get'], 'detail/(:num)', 'PromotionItem::index/$1');            // 取得推廣項目資料(id)
        $routes->match(['put'], 'detail/update/(:num)', 'PromotionItem::update/$1');    // 更新推廣項目資料
        
        $routes->match(['post'], 'file', 'FileController::upload');         // 上傳檔案
        $routes->match(['get'], 'file/show/(:num)', 'FileController::show/$1');         // 上傳檔案
        $routes->match(['post'], '/', 'Promotion::create');                 // 建立推廣資料
        $routes->match(['delete'], '(:num)', 'Promotion::delete/$1');       // 刪除推廣資料
        $routes->match(['post'], 'items', 'PromotionItem::create');         // 建立推廣資料
        $routes->match(['get'], 'user/info/(:num)', 'User::getUserId/$1');  // 取得User Id(測試用)
        $routes->match(['get'], 'user/test', 'User::test');                 // 測試
        $routes->match(['post'], 'line/state/save', 'Player::saveState');
        $routes->match(['get'], 'line/callback', 'Player::callback');
        
        $routes->match(['post'], 'user/email', 'User::updateEmailNotify');  // 更新信箱通知
        $routes->match(['post'], 'player/line', 'Player::updateLineNotify');    // 更新Line通知

        // 後台
        $routes->match(['get'], 'user', 'User::index');                     // 取得使用者資料
        $routes->match(['post'], 'user', 'User::index');                    // 取得使用者資料
        $routes->match(['post'], 'user/create', 'User::create');            // 新增使用者
        $routes->match(['post'], 'user/update', 'User::update');            // 更新使用者
        $routes->match(['post'], 'user/condition', 'User::condition');      // 新增使用者

        $routes->match(['get'], 'manager', 'User::getManager');             // 取得管理者資料
        $routes->match(['post'], 'manager/create', 'User::create');         // 新增管理者
        $routes->match(['post'], 'manager/update', 'User::update');         // 更新管理者

        $routes->match(['post'], 'player', 'Player::index');                // 取得玩家資料
        $routes->match(['post'], 'player/delete', 'Player::delete');        // 刪除玩家資料
                
        $routes->match(['post'], 'server/single', 'Server::singleById');    
        $routes->match(['post'], 'server/create', 'Server::create');      
        $routes->match(['post'], 'server/update', 'Server::update');      
        $routes->match(['post'], 'server/delete', 'Server::delete');      
        $routes->match(['post'], 'server/database', 'Server::getDatabase');              
        $routes->match(['post'], 'server/database/update', 'Server::updateDatabase');  
        $routes->match(['post'], 'server/award/update', 'Server::updateAward');               
        $routes->match(['post'], 'server/image', 'Server::getImage');              
        $routes->match(['post'], 'server/image/upload', 'Server::uploadImage');              
        $routes->match(['post'], 'server/image/update', 'Server::updateImage');     
        $routes->match(['get'], 'server/fix', 'Server::fix');

        $routes->match(['get'], 'test', 'Test::test');
    });

    // 娛樂城
    $routes->group('casino', ['namespace' => 'App\Controllers\Casino'], function ($routes){

        $routes->match(['get'], "image/upload", 'FileController::upload');
        $routes->match(['get'], "image/show/(:num)", 'FileController::show/$1');

        // 後台
        $routes->group('admin', function ($routes){
            // 使用者
            $routes->group('user', function ($routes){
                $routes->match(['get'], "create", 'User::create');     // 新增使用者
                $routes->match(['post'], "login", 'User::login');      // 登入
            });

            // 電子遊戲試玩
            $routes->group('electronic-game-play', function ($routes){
                $routes->match(['get'], "(:num)", 'ElectronicGamePlay::index/$1');      // 列表
                $routes->match(['get'], "/", 'ElectronicGamePlay::index');              // 列表
                $routes->match(['post'], "/", 'ElectronicGamePlay::create');            // 新增
                $routes->match(['post'], "update", 'ElectronicGamePlay::update');       // 更新
                $routes->match(['post'], "delete", 'ElectronicGamePlay::delete');       // 刪除

                // 詳細資料
                $routes->group('detail', function ($routes){
                    $routes->match(['get'], "(:num)", 'ElectronicGamePlayDetail::index/$1');    // 詳細
                    $routes->match(['post'], "/", 'ElectronicGamePlayDetail::index');
                    $routes->match(['post'], "create", 'ElectronicGamePlayDetail::create');     // 新增
                    $routes->match(['post'], "update", 'ElectronicGamePlayDetail::update');     // 更新
                    $routes->match(['post'], "delete", 'ElectronicGamePlayDetail::delete');     // 刪除
                });
            });

            // 國際榮耀
            $routes->group('international-glory', function ($routes){
            //     // $routes->match(['get'], "(:num)", 'InternationalGlory::index/$1');      // 列表
            //     // $routes->match(['get'], "/", 'InternationalGlory::index');              // 列表
            //     $routes->match(['post'], "", 'InternationalGlory::create');                 // 新增
            //     $routes->match(['post'], "update", 'InternationalGlory::update');           // 更新
            //     $routes->match(['post'], "delete", 'InternationalGlory::delete');           // 刪除
            });

            // 文章
            $routes->group('article', function ($routes){
                $routes->match(['get'], "/", 'Article::index');
                $routes->match(['get'], "show/(:num)", 'Article::show/$1');
                $routes->match(['post'], "/", 'Article::create');
                $routes->match(['post'], "image/upload", 'Article::uploadImage');
                $routes->match(['get'], "image/show/(:num)", 'Article::showFile/$1');
                $routes->match(['post'], "search", 'Article::search');
            });

            // 體育比分
            $routes->group('sports-scores', function ($routes){
                $routes->match(['get'], "/", 'SportsScores::index');            // 列表
                $routes->match(['get'], "(:num)", 'SportsScores::index/$1');    // 單筆查詢
                $routes->match(['post'], "/", 'SportsScores::create');          // 新增
                $routes->match(['post'], "update", 'SportsScores::update');     // 更新
                $routes->match(['post'], "delete", 'SportsScores::delete');     // 刪除
                $routes->match(['post'], "upload", 'SportsScores::upload');     // 上傳
                $routes->match(['post'], "sort", 'SportsScores::sort');         // 排序
            });

            // 彩票彩球
            $routes->group('lottery-draw', function ($routes){
                $routes->match(['get'], "/", 'LotteryDraw::index');            // 列表
                $routes->match(['get'], "(:num)", 'LotteryDraw::index/$1');    // 單筆查詢
                $routes->match(['post'], "/", 'LotteryDraw::create');          // 新增
                $routes->match(['post'], "update", 'LotteryDraw::update');     // 更新
                $routes->match(['post'], "delete", 'LotteryDraw::delete');     // 刪除
                $routes->match(['post'], "upload", 'LotteryDraw::upload');     // 上傳
                $routes->match(['post'], "sort", 'LotteryDraw::sort');         // 排序
            });
        });
    });

    // 後台
    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function ($routes){
        $adminPrefix = 'admin/';                 // 後臺

        $prefix = "{$adminPrefix}cases/";         // 案件
        $routes->match(['post'], "{$prefix}create", 'Cases::create');     // 新增
    });
});
