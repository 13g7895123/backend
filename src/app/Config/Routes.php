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
        $routes->match(['post'], 'detail/url/check', 'PromotionItem::checkUrl');         // 確認網址
        
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

            // 娛樂城
            $routes->group('entertainment-city', function ($routes){
                $routes->match(['get'], "(:num)", 'EntertainmentCity::index/$1');       // 單筆查詢
                $routes->match(['get'], "/", 'EntertainmentCity::index');               // 列表
                $routes->match(['post'], "/", 'EntertainmentCity::create');             // 新增
                $routes->match(['post'], "create", 'EntertainmentCity::create');        // 新增
                $routes->match(['post'], "update", 'EntertainmentCity::update');        // 更新
                $routes->match(['post'], "delete", 'EntertainmentCity::delete');        // 刪除
                $routes->match(['post'], "upload", 'EntertainmentCity::upload');        // 上傳
                $routes->match(['post'], "sort", 'EntertainmentCity::sort');            // 排序
            });

            // 遊戲榜
            $routes->group('game-list', function ($routes){
                $routes->match(['get'], "(:num)", 'GameList::index/$1');       // 單筆查詢
                $routes->match(['get'], "/", 'GameList::index');               // 列表
                $routes->match(['post'], "/", 'GameList::create');             // 新增
                $routes->match(['post'], "create", 'GameList::create');        // 新增
                $routes->match(['post'], "update", 'GameList::update');        // 更新
                $routes->match(['post'], "delete", 'GameList::delete');        // 刪除
                $routes->match(['post'], "upload", 'GameList::upload');        // 上傳
                $routes->match(['post'], "sort", 'GameList::sort');            // 排序
            });

            // 百家樂
            $routes->group('baccarat', function ($routes){
                $routes->match(['get'], "(:num)", 'Baccarat::index/$1');       // 單筆查詢
                $routes->match(['get'], "/", 'Baccarat::index');               // 列表
                $routes->match(['post'], "/", 'Baccarat::create');             // 新增
                $routes->match(['post'], "create", 'Baccarat::create');        // 新增
                $routes->match(['post'], "update", 'Baccarat::update');        // 更新
                $routes->match(['post'], "delete", 'Baccarat::delete');        // 刪除
                $routes->match(['post'], "upload", 'Baccarat::upload');        // 上傳
                $routes->match(['post'], "sort", 'Baccarat::sort');            // 排序
            });

            // 棋牌
            $routes->group('chess-and-cards', function ($routes){
                $routes->match(['get'], "(:num)", 'ChessAndCards::index/$1');       // 單筆查詢
                $routes->match(['get'], "/", 'ChessAndCards::index');               // 列表
                $routes->match(['post'], "/", 'ChessAndCards::create');             // 新增
                $routes->match(['post'], "create", 'ChessAndCards::create');        // 新增
                $routes->match(['post'], "update", 'ChessAndCards::update');        // 更新
                $routes->match(['post'], "delete", 'ChessAndCards::delete');        // 刪除
                $routes->match(['post'], "upload", 'ChessAndCards::upload');        // 上傳
                $routes->match(['post'], "sort", 'ChessAndCards::sort');            // 排序
            });

            // 遊戲攻略
            $routes->group('game-guide', function ($routes){
                $routes->group('tag', function ($routes){
                    $routes->match(['get'], "/", 'GameGuideTag::index');            // 列表
                    $routes->match(['get'], "(:num)", 'GameGuideTag::index/$1');    // 單筆查詢
                    $routes->match(['post'], "create", 'GameGuideTag::create');     // 新增
                    $routes->match(['post'], "update", 'GameGuideTag::update');     // 更新
                    $routes->match(['post'], "delete", 'GameGuideTag::delete');     // 刪除
                }); 
            });

            // 電子遊戲試玩
            $routes->group('electronic-game-play', function ($routes){
                $routes->match(['get'], "/", 'ElectronicGamePlay::index');            // 列表
                $routes->match(['get'], "(:num)", 'ElectronicGamePlay::index/$1');    // 單筆查詢
                $routes->match(['post'], "create", 'ElectronicGamePlay::create');     // 新增
                $routes->match(['post'], "update", 'ElectronicGamePlay::update');     // 更新
                $routes->match(['post'], "delete", 'ElectronicGamePlay::delete');     // 刪除
                $routes->match(['post'], "upload", 'ElectronicGamePlay::upload');     // 上傳
                $routes->match(['post'], "sort", 'ElectronicGamePlay::sort');         // 排序

                // 詳細資料
                $routes->group('detail', function ($routes){
                    $routes->match(['get'], "(:num)", 'ElectronicGamePlayDetail::index/$1');         // 詳細
                    $routes->match(['post'], "/", 'ElectronicGamePlayDetail::index');
                    $routes->match(['post'], "create", 'ElectronicGamePlayDetail::create');          // 新增
                    $routes->match(['post'], "update", 'ElectronicGamePlayDetail::update');          // 更新
                    $routes->match(['post'], "delete", 'ElectronicGamePlayDetail::delete');          // 刪除
                    $routes->match(['post'], "type", 'ElectronicGamePlayDetail::fetchTypeData');     // 取特定類型資料
                    $routes->match(['post'], "sort", 'ElectronicGamePlayDetail::sort');              // 排序
                    $routes->match(['post'], "upload", 'ElectronicGamePlayDetail::upload');          // 上傳
                });
            });

            // 捕魚試玩
            $routes->group('fishing-play', function ($routes){
                $routes->match(['get'], "/", 'FishingPlay::index');            // 列表
                $routes->match(['get'], "(:num)", 'FishingPlay::index/$1');    // 單筆查詢
                $routes->match(['post'], "/", 'FishingPlay::create');          // 新增
                $routes->match(['post'], "update", 'FishingPlay::update');     // 更新
                $routes->match(['post'], "delete", 'FishingPlay::delete');     // 刪除
                $routes->match(['post'], "upload", 'FishingPlay::upload');     // 上傳
                $routes->match(['post'], "sort", 'FishingPlay::sort');         // 排序
            });

            // 百家樂試玩
            $routes->group('baccarat-play', function ($routes){
                $routes->match(['get'], "/", 'BaccaratPlay::index');            // 列表
                $routes->match(['get'], "(:num)", 'BaccaratPlay::index/$1');    // 單筆查詢
                $routes->match(['post'], "/", 'BaccaratPlay::create');          // 新增
                $routes->match(['post'], "update", 'BaccaratPlay::update');     // 更新
                $routes->match(['post'], "delete", 'BaccaratPlay::delete');     // 刪除
                $routes->match(['post'], "upload", 'BaccaratPlay::upload');     // 上傳
                $routes->match(['post'], "sort", 'BaccaratPlay::sort');         // 排序
            });

            // 棋牌試玩
            $routes->group('chess-and-cards-play', function ($routes){
                $routes->match(['get'], "/", 'ChessAndCardsPlay::index');            // 列表
                $routes->match(['get'], "(:num)", 'ChessAndCardsPlay::index/$1');    // 單筆查詢
                $routes->match(['post'], "create", 'ChessAndCardsPlay::create');     // 新增
                $routes->match(['post'], "update", 'ChessAndCardsPlay::update');     // 更新
                $routes->match(['post'], "delete", 'ChessAndCardsPlay::delete');     // 刪除
                $routes->match(['post'], "upload", 'ChessAndCardsPlay::upload');     // 上傳
                $routes->match(['post'], "sort", 'ChessAndCardsPlay::sort');         // 排序

                // 詳細資料
                $routes->group('detail', function ($routes){
                    $routes->match(['get'], "(:num)", 'ChessAndCardsPlayDetail::index/$1');         // 詳細
                    $routes->match(['post'], "/", 'ChessAndCardsPlayDetail::index');
                    $routes->match(['post'], "create", 'ChessAndCardsPlayDetail::create');          // 新增
                    $routes->match(['post'], "update", 'ChessAndCardsPlayDetail::update');          // 更新
                    $routes->match(['post'], "delete", 'ChessAndCardsPlayDetail::delete');          // 刪除
                    $routes->match(['post'], "type", 'ChessAndCardsPlayDetail::fetchTypeData');     // 取特定類型資料
                    $routes->match(['post'], "sort", 'ChessAndCardsPlayDetail::sort');              // 排序
                    $routes->match(['post'], "upload", 'ChessAndCardsPlayDetail::upload');          // 上傳
                });
            });

            // 文章(國際榮耀)
            $routes->group('article', function ($routes){
                $routes->match(['post'], "/", 'Article::index');
                $routes->match(['get'], "show/(:num)", 'Article::show/$1');
                $routes->match(['post'], "create", 'Article::create');
                $routes->match(['post'], "update", 'Article::update');
                $routes->match(['post'], "delete", 'Article::delete');
                $routes->match(['post'], "image/upload", 'Article::uploadImage');
                $routes->match(['get'], "image/show/(:num)", 'Article::showFile/$1');
                $routes->match(['post'], "search", 'Article::search');
                $routes->match(['post'], "sort", 'Article::sort');
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

            // 首圖
            $routes->group('top-image', function ($routes){
                $routes->match(['get'], "/", 'TopImage::index');            // 列表
                $routes->match(['get'], "(:num)", 'TopImage::index/$1');    // 單筆查詢
                $routes->match(['post'], "create", 'TopImage::create');     // 新增
                $routes->match(['post'], "update", 'TopImage::update');     // 更新
                $routes->match(['post'], "delete", 'TopImage::delete');     // 刪除
                $routes->match(['post'], "upload", 'TopImage::upload');     // 上傳
                $routes->match(['post'], "sort", 'TopImage::sort');         // 排序
            });

            // 跑馬燈
            $routes->group('marquee', function ($routes){
                $routes->match(['get'], "/", 'Marquee::index');            // 列表
                $routes->match(['get'], "(:num)", 'Marquee::index/$1');    // 單筆查詢
                $routes->match(['post'], "create", 'Marquee::create');     // 新增
                $routes->match(['post'], "update", 'Marquee::update');     // 更新
                $routes->match(['post'], "delete", 'Marquee::delete');     // 刪除
                $routes->match(['post'], "upload", 'Marquee::upload');     // 上傳
                $routes->match(['post'], "sort", 'Marquee::sort');         // 排序
            });

            // 廣告
            $routes->group('advertisement', function ($routes){
                $routes->match(['get'], "/", 'Advertisement::index');            // 列表
                $routes->match(['get'], "(:num)", 'Advertisement::index/$1');    // 單筆查詢
                $routes->match(['post'], "create", 'Advertisement::create');     // 新增
                $routes->match(['post'], "update", 'Advertisement::update');     // 更新
                $routes->match(['post'], "delete", 'Advertisement::delete');     // 刪除
                $routes->match(['post'], "upload", 'Advertisement::upload');     // 上傳
                $routes->match(['post'], "sort", 'Advertisement::sort');         // 排序
            });

            // 常見問題
            $routes->group('questions', function ($routes){
                $routes->match(['get'], "/", 'Questions::index');            // 列表
                $routes->match(['get'], "(:num)", 'Questions::index/$1');    // 單筆查詢
                $routes->match(['post'], "create", 'Questions::create');     // 新增
                $routes->match(['post'], "update", 'Questions::update');     // 更新
                $routes->match(['post'], "delete", 'Questions::delete');     // 刪除
                $routes->match(['post'], "upload", 'Questions::upload');     // 上傳
                $routes->match(['post'], "sort", 'Questions::sort');         // 排序
            });

            // 常見問題
            $routes->group('info', function ($routes){
                $routes->match(['get'], "/", 'Info::index');            // 列表
                $routes->match(['get'], "(:num)", 'Info::index/$1');    // 單筆查詢
                $routes->match(['post'], "create", 'Info::create');     // 新增
                $routes->match(['post'], "update", 'Info::update');     // 更新
                $routes->match(['post'], "delete", 'Info::delete');     // 刪除
                $routes->match(['post'], "upload", 'Info::upload');     // 上傳
                $routes->match(['post'], "sort", 'Info::sort');         // 排序

                // 詳細資料
                $routes->group('detail', function ($routes){
                    $routes->match(['get'], "(:num)", 'InfoDetail::index/$1');         // 詳細
                    $routes->match(['post'], "/", 'InfoDetail::index');
                    $routes->match(['post'], "create", 'InfoDetail::create');          // 新增
                    $routes->match(['post'], "update", 'InfoDetail::update');          // 更新
                    $routes->match(['post'], "delete", 'InfoDetail::delete');          // 刪除
                    $routes->match(['post'], "type", 'InfoDetail::fetchTypeData');     // 取特定類型資料
                    $routes->match(['post'], "sort", 'InfoDetail::sort');              // 排序
                    $routes->match(['post'], "upload", 'InfoDetail::upload');          // 上傳
                });
            });

            // 熱門關鍵字
            $routes->group('hotkeys', function ($routes){
                $routes->match(['get'], "/", 'Hotkeys::index');            // 列表
                $routes->match(['get'], "(:num)", 'Hotkeys::index/$1');    // 單筆查詢
                $routes->match(['post'], "create", 'Hotkeys::create');     // 新增
                $routes->match(['post'], "update", 'Hotkeys::update');     // 更新
                $routes->match(['post'], "delete", 'Hotkeys::delete');     // 刪除
                $routes->match(['post'], "upload", 'Hotkeys::upload');     // 上傳
                $routes->match(['post'], "sort", 'Hotkeys::sort');         // 排序
            });
        });
    });

    // 後台
    $routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function ($routes){
        // 案件
        $routes->group('cases', function ($routes){
            $routes->match(['post'], 'create', 'Cases::create');        // 新增
        });

        $routes->group('line', function ($routes){
            $routes->match(['post'], 'webhook/(:any)', 'LineNew::webhook/$1');          // Line Webhook
            $routes->match(['post'], 'webhook', 'LineNew::webhook');                    // Line Webhook
        });
    });
});
