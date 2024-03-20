<?php

    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
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
    Route::get('basic-config', 'Api\BasicConfigController@show');
    
    /**
     * Admin login requested routes
     */
    Route::get('logout' , 'Api\UserController@Logout');
    Route::prefix('admin')->group(function(){
        Route::post('login' , 'Api\UserController@AdminLogin');
    });

    Route::prefix('oauth-client-check')->group(function(){
        Route::post('/' , 'Api\UserController@OAuthClientCheck');
    });

    Route::get('load-categories', 'Api\CategoriesController@show');

    Route::post('load-opinion-poll', 'Api\OpinionPollController@load');
    Route::post('load-online-survey', 'Api\OnlineSurveyController@load');
    Route::post('submit-online-poll', 'Api\PollResultsController@store');
    Route::get('explain-comment-list', 'Api\PollResultExplainController@show');

    Route::post('load-domain-info', 'Api\DomainListController@load');
    
    Route::get('load-flag-rpt-types', 'Api\FlagReportTypesController@show');
    Route::post('submit-comment', 'Api\CommentListController@store');
    Route::post('otp-request', 'Api\CommentListController@otpRequest');
    Route::post('otp-verify', 'Api\CommentListController@otpVerify');

    Route::post('load-opinion-polls-history' , 'Api\OpinionPollController@loadOpinionPollsHistory');
    Route::post('load-online-survey-history' , 'Api\OnlineSurveyController@loadOnlineSurveyHistory');
    Route::post('load-opinion-polls' , 'Api\OpinionPollController@loadSDOpinionPolls');

    Route::post('load-comments' , 'Api\CommentListController@loadSDComments');
    Route::post('reply-comments' , 'Api\CommentsReplyInfoController@SDStore');
?>