<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\ModificationController as ModificationController;
use App\Http\Controllers\Common\SmsApi as SmsApi;

use App\Models\CommentsReplyInfo;
use Illuminate\Http\Request;

use Mail;
use Auth;

class CommentsReplyInfoController extends Controller
{
    use SmsAPi;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CommentsReplyInfo $obj, Request $request)
    {
        // return $request->all();
        $getData = $request['data'];        

        // return $getData;        
        if($request['reply_media']=='email'){
            $user_name = $getData['user_name'];
            $user_email = $getData['user_email'];
            $domain_id = $getData['domain_id'];
                    
            $getData['html'] = 'Dear, '.$user_name."<br>".$request['comment']."<br><br>".'Thank you for connect with us';        

            Mail::send('email_template', $getData, function($message) use($request, $user_email) {
                $message->to($user_email)->subject($request['subject']);
                $message->from('no-reply@bangladesh.portal.gov.bd', 'Bangladesh National Portal');
            });
        }else if($request['reply_media']=='sms'){
            $mobile_no = ltrim($getData['contact_no'],'+88');
            $text = $request['subject'].",\n".$request['comment'];
            $getResponse = $this->send($mobile_no,$text);
            $getResponse = json_decode($getResponse, true);            

            if(@$getResponse['description']!=='Success') return response()->json(['msg' => $getResponse['description'], 'status' => false], 200);
        }
        
        $submit_data = [];
        $submit_data['comment_id'] = $getData['comment_id'];
        $submit_data['subject'] = $request['subject'];
        $submit_data['comment'] = $request['comment'];
        $submit_data['reply_media'] = $request['reply_media'];

        return ModificationController::save_content($obj, $submit_data);        
    }

    public function SDStore(CommentsReplyInfo $obj, Request $request)
    {
        // return $request->all();
        $getData = $request['data'];

        // return $getData;
        
        if($request['reply_media']=='email'){
            $user_name = $getData['user_name'];
            $user_email = $getData['user_email'];
            $domain_id = $getData['domain_id'];
                    
            $getData['html'] = 'Dear, '.$user_name."<br>".$request['comment']."<br><br>".'Thank you for connect with us';
        
            Mail::send('email_template', $getData, function($message) use($request, $user_email) {
                $message->to($user_email)->subject($request['subject']);
                $message->from('no-reply@bangladesh.portal.gov.bd', 'Bangladesh National Portal');
            });
        }
        
        $obj->comment_id = $request['comment_id'];
        $obj->subject = $request['subject'];
        $obj->reply_media = $request['reply_media'];
        $obj->comment = $request['comment'];

        if($obj->save()){
            $data = [
                'data'      => $obj,
                'status'    => true,
                'code'      => '200',
                'message'   => '<i class="fa fa-check-circle"></i> Data has been saved successfully.',
            ];

            return response()->json($data, 200);
        }else{
            $data = [
                'status'  => false,
                'code'    => '404',
                'message' => '<i class="fa fa-info-circle"></i> Error occurred. Data doesn\'t save.'
            ];

            return response()->json($data, 404);
        }

        // return ModificationController::save_content($obj, $submit_data);
    }
    /**
     * Store review
     */
    public function storeReview(Request $request)
    {
        try {
            if(ReviewRating::where('comment_id',$request->comment_id)->exists()) {
                $data = [
                    'status'        => 'success',
                    'code'          => '200',
                    'message'       => 'Already review the rating.',
                ];
            }else {
                if($request->comment_id != NULL && $request->comment != NULL)
                {
                    $ratingData['comment_id']   = $request->comment_id; 
                    $ratingData['text']         = $request->comment;
                    $ratingData['star_rating']  = 0;
                    $ratingData['created_by']   = Auth::id();
                    DB::table('review_ratings')->updateOrInsert($ratingData);
                    $data = [
                        'status'        => 'success',
                        'code'          => '200',
                        'message'       => 'Data save successfully.',
                    ];
                }
            } 
            return response()->json($data, 200);
        } catch (\Exception $e) {
            $data = [
                'status'  => 'error',
                'code'    => '404',
                'message' => $e->getMessage(),
            ];
            return response()->json($data, 404);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CommentsReplyInfo  $commentsReplyInfo
     * @return \Illuminate\Http\Response
     */
    public function show(CommentsReplyInfo $commentsReplyInfo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CommentsReplyInfo  $commentsReplyInfo
     * @return \Illuminate\Http\Response
     */
    public function edit(CommentsReplyInfo $commentsReplyInfo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CommentsReplyInfo  $commentsReplyInfo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CommentsReplyInfo $commentsReplyInfo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CommentsReplyInfo  $commentsReplyInfo
     * @return \Illuminate\Http\Response
     */
    public function destroy(CommentsReplyInfo $commentsReplyInfo)
    {
        //
    }
}
