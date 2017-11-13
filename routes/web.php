<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get("/", function () use ($router) {
    return view("index");
});

$router->group(["prefix" => "test", "namespace" => "test"], function () use ($router) {
    $router->get("transaction", "TestController@transaction");
    $router->get("test", "TestController@test");
});

$router->group(["prefix" => "api", "namespace" => "Api"], function () use ($router) {
    $router->get("applicants", "ApplicantController@index");
    $router->post("applicants", "ApplicantController@store");
    $router->put("applicants/{id}", "ApplicantController@update");
    $router->get("applicants/{user}/{invitation}", "ApplicantController@activate");
    $router->get("categories", "CategoryController@index");
    $router->post("categories", "CategoryController@store");
    $router->put("categories/{id}", "CategoryController@update");
    $router->delete("categories/{id}", "CategoryController@destory");
    $router->get("recommends", "RecommendController@index");
    $router->post("recommends", "RecommendController@store");
    $router->get("recommends/{id}", "RecommendController@show");
    $router->put("recommends/{id}", "RecommendController@update");
    $router->get("users", "UserController@index");
    $router->get("users/{id}", "UserController@show");
    $router->get("users/pull/{id}", "UserController@pull");
    $router->get("users/init/{id}", "UserController@init");
    $router->get("settings/{id}", "SettingController@show");
    $router->put("settings/{id}", "SettingController@update");
    $router->post("translations", "TranslationController@store");
    $router->post("translations/pr", "TranslationController@handlePR");
});


$router->get("{uri:.*?}", function () use ($router) {
    return $router->app->version();
});

/*
// 身份验证
Route::group(['prefix' => 'auth'], function () {
	Route::get('login', 'Auth\LoginController@oAuth');
	Route::get('logout', 'Auth\LoginController@logout');
});

// API 接口
Route::group(['prefix' => 'api', 'namespace' => 'Api'], function () {
	// 用户接口
	Route::group(['middleware' => 'token'], function () {
		// 管理员接口
		Route::group(['middleware' => 'admin'], function () {
			// 获取所有译者申请
			Route::get('applicants', 'ApplicantController@index');
			// 更新译者申请结果
			Route::put('applicants/{id}', 'ApplicantController@update');
			// 获取指定译者申请详情
			Route::get('applicants/{id}', 'ApplicantController@show');
			// 获取所有通知（推荐文章、译者申请及通知总数）
			Route::get('notifications', 'NotificationController@index');
			// 添加文章类别
			Route::post('categories', 'CategoryController@store');
			// 更新指定文章类别信息
			Route::put('categories/{id}', 'CategoryController@update');
			// 删除指定文章类别（不可恢复）
			Route::delete('categories/{id}', 'CategoryController@destory');
			// 获取指定文章类别详情
			Route::get('categories/{id}', 'CategoryController@show');
			// 获取所有试译文章
			Route::get('articles', 'ArticleController@index');
			// 添加试译文章
			Route::post('articles', 'ArticleController@store');
			// 获取指定试译文章详情
			Route::get('articles/{id}', 'ArticleController@show');
			// 更新指定试译文章
			Route::put('articles/{id}', 'ArticleController@update');
			// 删除指定试译文章（状态标记）
			Route::delete('articles/{id}', 'ArticleController@destory');
			// 切换试译文章状态（启用／忽略）
			Route::put('articles/status/{id}', 'ArticleController@toggleStatus');
			// 更新推荐文章结果
			Route::put('recommends/result/{id}', 'RecommendController@result');
			// 修正译文信息（字数、任务时长、积分等）
			Route::put('translations/{id}', 'TranslationController@update');
			// 添加新译文
			Route::post('translations', 'TranslationController@store');
		});
		// 获取当前用户信息
		Route::get('users/pull', 'UserController@pull');
		// 获取当前用户设置信息
		Route::get('usersettings', 'UserSettingController@show');
		// 更新当前用户设置
		Route::post('usersettings', 'UserSettingController@setUserSettings');
		// 校验邀请码
		Route::post('applicants/check', 'ApplicantController@checkInvitation');
		// 添加推荐文章
		Route::post('recommends', 'RecommendController@store');
		// 更新指定推荐文章
		Route::put('recommends/{id}', 'RecommendController@update');
		// 译者更新文章在掘金的分享信息
		Route::patch('translations/{id}', 'TranslationController@post');
		// 认领校对任务
		Route::post('translations/claim/review', 'TranslationController@claimReview');
		// 认领翻译任务
		Route::post('translations/claim/translation', 'TranslationController@claimTranslation');
	});
	// 开放接口
	// 执行用户登录
	Route::get('users', 'UserController@index');
	// 获取指定用户详细信息
	Route::get('users/{id}', 'UserController@show');
	// 随机获取指定类别的试译文章
	Route::get('articles/random/{id}', 'ArticleController@getRandomArticle');
	// 获取文章分类
	Route::get('categories', 'CategoryController@index');
	// 获取所有推荐文章
	Route::get('recommends', 'RecommendController@index');
	// 获取指定推荐文章详情
	Route::get('recommends/{id}', 'RecommendController@show');
	// 获取指定译文详情
	Route::get('translations/show/{id}', 'TranslationController@show');
	// 获取指定类别的译文列表
	Route::get('translations/pull/{status}', 'TranslationController@index');
	// 获取排行榜
	Route::get('statistics', 'StatisticController@index');
	// 获取全站统计信息（翻译总量、译者总数等）
	Route::get('statistics/overview', 'StatisticController@overview');
	// 获取用户排名
	Route::get('statistics/user/rank/{id}', 'StatisticController@userRank');
	// 获取用户参与的校对／翻译任务数量
	Route::get('statistics/user/task/{id}', 'StatisticController@userTask');
	// 提交译者申请
	Route::post('applicants', 'ApplicantController@store');
	// 处理 GitHub WebHooks 请求信息
	Route::post('translations/pr', 'TranslationController@handlePR');
});

// 其他路由
Route::any('{uri}', function($uri)
{
	return view('index', ['store'=>'']);
})->where('uri', '.*?');
*/