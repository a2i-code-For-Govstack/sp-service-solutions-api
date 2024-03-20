<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['middleware' => 'auth:api'] , function() {
    Route::prefix('site-basic-config')->group(function(){
        Route::get('/' , 'Api\BasicConfigController@get');
        Route::post('/' , 'Api\BasicConfigController@store');
    });
    
    Route::prefix('users')->group(function(){
        Route::get('/' , 'Api\UserController@show');
        Route::get('/edit/{id}' , 'Api\UserController@edit');
        Route::get('/delete/{id}' , 'Api\UserController@destroy');
        Route::post('/store' , 'Api\UserController@store');
        Route::post('/update/{id}' , 'Api\UserController@update');        
    });

    Route::prefix('user-role-infos')->group(function(){
        Route::get('/' , 'Api\UserRoleInfosController@show');
        Route::get('/edit/{id}' , 'Api\UserRoleInfosController@edit');
        Route::get('/delete/{id}' , 'Api\UserRoleInfosController@destroy');
        Route::post('/store' , 'Api\UserRoleInfosController@store');
        Route::post('/update/{id}' , 'Api\UserRoleInfosController@update');
    });

    Route::prefix('categories')->group(function(){
        Route::get('/' , 'Api\CategoriesController@show');
        Route::get('/edit/{id}' , 'Api\CategoriesController@edit');
        Route::get('/delete/{id}' , 'Api\CategoriesController@destroy');
        Route::post('/store' , 'Api\CategoriesController@store');
        Route::post('/update/{id}' , 'Api\CategoriesController@update');        
    });

    Route::prefix('opinion-polls')->group(function(){
        Route::get('/' , 'Api\OpinionPollController@show');
        Route::get('/edit/{id}' , 'Api\OpinionPollController@edit');
        Route::get('/delete/{id}' , 'Api\OpinionPollController@destroy');
        Route::post('/store' , 'Api\OpinionPollController@store');
        Route::post('/update/{id}' , 'Api\OpinionPollController@update');        
    });

    Route::prefix('online-surveys')->group(function(){
        Route::get('/' , 'Api\OnlineSurveyController@show');
        Route::get('/edit/{id}' , 'Api\OnlineSurveyController@edit');
        Route::get('/delete/{id}' , 'Api\OnlineSurveyController@destroy');
        Route::post('/store' , 'Api\OnlineSurveyController@store');
        Route::post('/update/{id}' , 'Api\OnlineSurveyController@update');        
    });

    Route::prefix('flag-rpt-types')->group(function(){
        Route::get('/' , 'Api\FlagReportTypesController@show');        
        Route::get('/edit/{id}' , 'Api\FlagReportTypesController@edit');
        Route::get('/delete/{id}' , 'Api\FlagReportTypesController@destroy');
        Route::post('/store' , 'Api\FlagReportTypesController@store');
        Route::post('/update/{id}' , 'Api\FlagReportTypesController@update');        
    });
    
    Route::prefix('comments')->group(function(){
        Route::get('/' , 'Api\CommentListController@show');
        Route::get('/edit/{id}' , 'Api\CommentListController@edit');
        Route::get('/delete/{id}' , 'Api\CommentListController@destroy');
        Route::post('/store' , 'Api\CommentListController@store');        
        Route::post('/update/{id}' , 'Api\CommentListController@update'); 
        Route::post('/reply' , 'Api\CommentsReplyInfoController@store');
        Route::post('/review' , 'Api\CommentsReplyInfoController@storeReview');
    });

    Route::prefix('domain-list')->group(function(){
        Route::get('/' , 'Api\DomainListController@show');
        Route::get('search', 'Api\DomainListController@search');
        Route::get('/edit/{id}' , 'Api\DomainListController@edit');
        Route::get('/delete/{id}' , 'Api\DomainListController@destroy');
        Route::post('/store' , 'Api\DomainListController@store');
        Route::post('/update/{id}' , 'Api\DomainListController@update');        
    });

    Route::prefix('domain-access-credentials')->group(function(){
        Route::get('/' , 'Api\SsoCredentialsController@show');
        Route::get('search', 'Api\SsoCredentialsController@search');
        Route::get('generate', 'Api\SsoCredentialsController@generateUid');
        Route::get('/edit/{id}' , 'Api\SsoCredentialsController@edit');
        Route::get('/delete/{id}' , 'Api\SsoCredentialsController@destroy');
        Route::post('/store' , 'Api\SsoCredentialsController@store');
        Route::post('/update/{id}' , 'Api\SsoCredentialsController@update');
    });

    Route::prefix('domain-groups')->group(function(){
        Route::get('/' , 'Api\DomainGroupController@show');
        Route::get('search', 'Api\DomainGroupController@search');
        Route::get('/edit/{id}' , 'Api\DomainGroupController@edit');
        Route::get('/delete/{id}' , 'Api\DomainGroupController@destroy');
        Route::post('/store' , 'Api\DomainGroupController@store');
        Route::post('/update/{id}' , 'Api\DomainGroupController@update');        
    });

    Route::prefix('media-galleries')->group(function(){
        Route::get('/' , 'Api\MediaGalleryController@show');
        Route::get('search', 'Api\MediaGalleryController@search');
        Route::get('/edit/{id}' , 'Api\MediaGalleryController@edit');
        Route::get('/delete/{id}' , 'Api\MediaGalleryController@destroy');
        Route::post('/store' , 'Api\MediaGalleryController@store');
        Route::post('/update/{id}' , 'Api\MediaGalleryController@update');
    });

    Route::prefix('tags')->group(function(){
        Route::get('/' , 'Api\TagsController@show');
        Route::get('search', 'Api\TagsController@search');
        Route::get('/edit/{id}' , 'Api\TagsController@edit');
        Route::get('/delete/{id}' , 'Api\TagsController@destroy');
        Route::post('/store' , 'Api\TagsController@store');
        Route::post('/update/{id}' , 'Api\TagsController@update');
    });

    Route::prefix('smtp-setup')->group(function(){
        Route::get('/' , 'Api\SmtpInfoController@show');
        Route::get('search', 'Api\SmtpInfoController@search');
        Route::get('/edit/{id}' , 'Api\SmtpInfoController@edit');
        Route::get('/delete/{id}' , 'Api\SmtpInfoController@destroy');
        Route::post('/store' , 'Api\SmtpInfoController@store');
        Route::post('/update/{id}' , 'Api\SmtpInfoController@update');        
    });

    Route::prefix('data-report')->group(function(){
        Route::get('/opinion-poll/stat-report' , 'Api\OpinionPollController@getStatReport');
        Route::get('/online-survey/stat-report' , 'Api\OnlineSurveyController@getStatReport');
        Route::get('/comments/stat-report' , 'Api\CommentListController@getStatReport');
    });
});
