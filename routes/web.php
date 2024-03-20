<?php
use Illuminate\Support\Facades\Artisan;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('get-session', function(){
    // \Session::put('raj', 'mukul vai');
    // \Session::save();

    // return \Session::get('raj');
});

// Route::get('load-opinion-poll', 'Api\OpinionPollController@load');
Route::get('/opinion-poll/{id}', 'Api\OpinionPollController@getOpinionPoll');

Route::prefix('stat-report')->middleware('cors_domain')->group(function(){
    Route::get('/opinion-poll' , 'Api\OpinionPollController@getStatReport');
    Route::get('/online-survey' , 'Api\OnlineSurveyController@getStatReport');
    Route::get('/comments' , 'Api\CommentListController@getStatReport');
});

Route::get('/clear-cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
 
    return "Cache cleared successfully";
 });