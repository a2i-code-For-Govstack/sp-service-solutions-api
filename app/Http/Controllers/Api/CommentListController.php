<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\ModificationController as ModificationController;
use App\Http\Resources\CommentsCollection as CommentsResource;
use App\Http\Controllers\Common\SmsApi as SmsApi;

use App\Models\SsoCredentials;
use App\Models\CommentList;
use App\Models\CommentRelInfo;
use App\Models\CommentsReplyInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use Auth;
use DB;
use Mail;

class CommentListController extends Controller
{
    use SmsAPi;
    protected function getUserIP(){
        // Get real visitor IP behind CloudFlare network
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
                $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
                $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }
        $client  = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote  = $_SERVER['REMOTE_ADDR'];

        if(filter_var($client, FILTER_VALIDATE_IP))
        {
            $ip = $client;
        }
        elseif(filter_var($forward, FILTER_VALIDATE_IP))
        {
            $ip = $forward;
        }
        else
        {
            $ip = $remote;
        }

        return $ip;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function getStatReport(Request $request){
        $requestHost = parse_url($request->headers->get('origin'),  PHP_URL_HOST);
        $user_id = Auth::id();
        if(!$user_id){
            $allowedHostsStr = env('ALLOWED_HOSTS', 'e-participation.bangladesh.gov.bd,localhost,');
            $allowedHosts = explode(',', $allowedHostsStr);                    
            // var_dump($allowedHostsStr);
            // var_dump($allowedHosts);                   

            if(!in_array($requestHost, $allowedHosts)) return response()->json(['msg' => 'Unauthorized','status' => false], 200);
        }

        $getData = (Object) [];
        $req_from = $request->has('req_from')?$request['req_from']:'';
        $getHostName = $req_from?str_replace('www.','',$req_from):'';

        /**
         * For total comments [all types]
         */
        $getRes = CommentRelInfo::select(DB::raw('COUNT(id) AS total_no_comments'))
        ->when($req_from, function($q) use($req_from, $getHostName){            
            $q->where('url','LIKE',"%$req_from%")
                ->orWhere('url','LIKE',"%.$getHostName%");
        })
        ->first();

        if(isset($getRes->total_no_comments)) $getData->total_no_comments = $getRes->total_no_comments;
        else $getData->total_no_comments = 0;
        
        /**
         * For total comments
         */
        $getRes = CommentRelInfo::select(DB::raw('COUNT(id) AS total_comments'))
        ->when($req_from, function($q) use($req_from, $getHostName){            
            $q->where('url','LIKE',"%$req_from%")
                ->orWhere('url','LIKE',"%.$getHostName%");
        })
        ->where('type_id',1)
        ->first();

        if(isset($getRes->total_comments)) $getData->total_comments = $getRes->total_comments;
        else $getData->total_comments = 0;

        /**
         * For total feedbacks
         */
        $getRes = CommentRelInfo::select(DB::raw('COUNT(id) AS total_feedbacks'))
        ->when($req_from, function($q) use($req_from, $getHostName){            
            $q->where('url','LIKE',"%$req_from%")
                ->orWhere('url','LIKE',"%.$getHostName%");
        })
        ->where('type_id',2)
        ->first();

        if(isset($getRes->total_feedbacks)) $getData->total_feedbacks = $getRes->total_feedbacks;
        else $getData->total_feedbacks = 0;

        /**
         * For total suggestion
         */
        $getRes = CommentRelInfo::select(DB::raw('COUNT(id) AS total_suggestions'))
        ->when($req_from, function($q) use($req_from, $getHostName){            
            $q->where('url','LIKE',"%$req_from%")
                ->orWhere('url','LIKE',"%.$getHostName%");
        })
        ->where('type_id',3)
        ->first();

        if(isset($getRes->total_suggestions)) $getData->total_suggestions = $getRes->total_suggestions;
        else $getData->total_suggestions = 0;

        /**
         * For total complains
         */
        $getRes = CommentRelInfo::select(DB::raw('COUNT(id) AS total_complains'))
        ->when($req_from, function($q) use($req_from, $getHostName){            
            $q->where('url','LIKE',"%$req_from%")
                ->orWhere('url','LIKE',"%.$getHostName%");
        })
        ->where('type_id',4)
        ->first();

        if(isset($getRes->total_complains)) $getData->total_complains = $getRes->total_complains;
        else $getData->total_complains = 0;

        /**
         * For total reply
         */
        $getRes = CommentsReplyInfo::select(DB::raw('COUNT(DISTINCT(comments_reply_infos.comment_id)) AS total_replies'))
        ->when($req_from, function($q) use($req_from, $getHostName){
            $q->leftJoin('comment_rel_infos AS cri','cri.comment_id','=','comments_reply_infos.comment_id')
                ->where('cri.url','LIKE',"%$req_from%")
                ->orWhere('cri.url','LIKE',"%.$getHostName%");
        })
        ->first();

        if(isset($getRes->total_replies)) $getData->total_replies = $getRes->total_replies;
        else $getData->total_replies = 0;

        /**
         * For total no answered
         */
        if($getData->total_no_comments>0) $getData->total_no_answers = $getData->total_no_comments - $getData->total_replies;
        else $getData->total_no_answers = 0;
    
        return response()->json(['data' => $getData, 'status' => 2], 200);        
    }

    public function loadSDComments(CommentList $obj, Request $request){
        // return $request->all();
        $limit = $request['limit']>0?$request['limit']:10;
        $req_from = $request->has['req_from']?$request['req_from']:'';

        $getRes = SsoCredentials::select('sso_credentials.id')
        ->leftJoin('domain_groups','domain_groups.id','=','sso_credentials.domain_group_id')
        ->leftJoin('domain_lists','domain_lists.domain_id','=','domain_groups.id')
        ->where(function($q) use($req_from){
            $q->where('domain_lists.sub_domain',$req_from)
            ->orWhere('domain_lists.alias','LIKE',"%$req_from%");
        })
        ->where('sso_credentials.app_id',$request['app_id'])
        ->where('sso_credentials.secret_key',$request['secret_key'])
        ->first();

        if(!empty($getRes)){
            $getData = $obj::select('comment_lists.*')
            ->leftJoin('comment_rel_infos','comment_rel_infos.comment_id','=','comment_lists.id')
            ->leftJoin('domain_lists', 'domain_lists.domain_id', '=', 'comment_rel_infos.domain_id')        
            ->where(function($q) use($req_from){
                $q->where('domain_lists.sub_domain',$req_from)
                ->orWhere('domain_lists.alias','LIKE',"%$req_from%");
            })
            ->with(['CommentRelInfo','CommentReplyInfo'])
            ->orderBy('comment_lists.id','DESC')
            ->paginate($limit);

            return CommentsResource::collection($getData);
        }else return response()->json(['status' => 2], 200);
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
    public function store(CommentList $obj, Request $request)
    {
        $request['ip_addr'] = $this->getUserIP();
        $request['device_info'] = $_SERVER['HTTP_USER_AGENT'];
        // return $request->all();

        return $this->save_content($obj, $request);
    }

    protected function more_featured_management($obj,$data,$req_id,$action){        
        /**
         * Remove comment related data first
         */
        if($action=='update') DB::select('DELETE FROM `comment_rel_infos` WHERE comment_id='.$req_id);

        /**
         * Save to comment related table
         */
        $commentRelQry = 'INSERT INTO `comment_rel_infos`(`comment_id`,`user_name`,`user_email`,`contact_no`,`url`,`domain_id`,`type_id`,`flag_rpt_type_id`) VALUES';
        
        // return $data['comment_rel_info'];
        // $val = $data['comment_rel_info'];
        // $commentRelQry .= '('.$req_id.',"'.addslashes($val['user_name']).'","'.addslashes($val['user_email']).'","'.addslashes($val['contact_no']).'","'.$val['url'].'",'.($val['domain_id']?$val['domain_id']:"NULL").','.$val['type_id'].','.($val['flag_rpt_type_id']?$val['flag_rpt_type_id']:"NULL").')';

        $commentRelQry .= '('.$req_id.',"'.addslashes($data['user_name']).'","'.addslashes($data['user_email']).'","'.addslashes($data['contact_no']).'","'.$data['url'].'",'.($data['domain_id']?$data['domain_id']:"NULL").','.$data['type_id'].','.($data['flag_rpt_type_id']?$data['flag_rpt_type_id']:"NULL").')';
        
        // return $commentRelQry;
        $obj->comment_rel_info =  DB::select($commentRelQry);

        return $obj;
    }

    /**
     * SAVE CONTENT
     */
    protected function save_content($obj, $data){
        // return $data;
        try{                
            if(Auth::check()) $user_id = Auth::id();

            $comment_file = $data['single_file'];
            if(isset($comment_file))
            {
                $extension = $comment_file->getClientOriginalExtension();
                $file_name = date('Y_m_d_h_i_s') . '_' . uniqid() . '.' . $extension;
                $obj->file = $file_name;
                Storage::disk('public')->put('media-gallery/'.$file_name, file_get_contents($comment_file)); 
            }

            $obj->comment       = $data['comment'];
            if(isset($data['device_info']))
            $obj->device_info   = $data['device_info'];
            if(isset($data['ip_addr']))
            $obj->ip_addr       = $data['ip_addr'];
            $obj->status        = $data['status'];            
            if(Auth::check())
            $obj->created_by    = $user_id;
            $obj->created_at    = date('Y-m-d H:i:s');

            if(trim(@$data['comment_rel_info']['user_name'])=='' || (trim(@$data['comment_rel_info']['user_email'])=='' && trim(@$data['comment_rel_info']['contact_no']==''))){
                $data = [
                    'data'      => '',
                    'status'    => 'warning',
                    'code'      => '200',
                    'message'   => '',
                ];

                return response()->json($data, 200);
            }

            if($obj->save()){
                /**
                 * MORE FEATURED MANAGEMENT FUNCTION
                 */
                $obj = $this->more_featured_management($obj,$data,$obj->id,'save');

                $data = [
                    'data'      => $obj,
                    'status'    => 'success',
                    'code'      => '200',
                    'message'   => '<i class="fa fa-check-circle"></i> Data has been saved successfully.',
                ];

                return response()->json($data, 200);
            }else{
                $data = [
                    'status'  => 'error',
                    'code'    => '404',
                    'message' => '<i class="fa fa-info-circle"></i> Error occurred. Data doesn\'t save.'
                ];

                return response()->json($data, 404);
            }
        }catch(\Exception $e){
            $data = [
                'status'  => 'error',
                'code'    => '404',
                'message' => $e->getMessage(),
            ];

            return response()->json($data, 404);
        }
    }
    /**
     * OTP GENERATE
     */
    public function otpRequest(Request $request) 
    {
        try {
            $otp = mt_rand(100000, 999999);
            $text = $otp;

            if($request->contact_no == '' && count($request->contact_no) != 11){
                $data = [
                    'status'  => 'error',
                    'code'    => '404',
                    'message' => 'Invalid mobile number.'
                ];

                return response()->json($data, 404);
            }
            // check email validation with regular expression
            if($request->email != '' && !filter_var($request->email, FILTER_VALIDATE_EMAIL)){
                $data = [
                    'status'  => 'error',
                    'code'    => '404',
                    'message' => 'Invalid email address.'
                ];

                return response()->json($data, 404);
            }
            else {
                $upData['otp']          = $text; 
                $upData['contact_no']   = ($request->contact_no)?$request->contact_no:null;
                $email                  = ($request->email)?$request->email:null;
                DB::table('otp_lists')->updateOrInsert(['contact_no'=>$request->contact_no],$upData);
                $this->send($request->contact_no, $text);
                if($request->email != ''){
                    Mail::send([], [], function ($message) use ($email, $text) {
                        // $message->from('no-reply@bangladesh.portal.gov.bd', 'Bangladesh National Portal');
                        $message->from('no-reply@arprince.me', 'Bangladesh National Portal');
                        $message->to($email);
                        $message->subject('OTP Verification');
                        $message->setBody('Your OTP is: '.$text);
                    });
                }
                $data = [
                    'status'        => 'success',
                    'contact_number'=> $request->contact_no,
                    'email'         => $request->email ?? '',
                    'code'          => '200',
                    'message'       => 'OTP sent successfully.',
                ];

                return response()->json($data, 200);
            }
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
     * OTP otpVerify
     */
    public function otpVerify(Request $request) {
        $enteredOtp     = $request->input('auth_code');
        $contact_number = $request->input('contact_number');
        $checkOtp       = DB::table('otp_lists')
                                ->where('contact_no',$contact_number)
                                ->where('otp',$enteredOtp)->first();
        // if not matched otp then show error invalid otp
        if(empty($checkOtp)){
            $data = [
                'status'  => 0,
                'code'    => '404',
                'message' => 'Invalid OTP.',
            ];
            return response()->json($data, 200);
        }
        if(!empty($checkOtp)){
            $data = [
                'status'    => 1,
                'code'      => '200',
                'message'   => 'OTP verify successfully.',
            ];
            return response()->json($data, 200);
        }else{
            $data = [
                'status'  => 0,
                'code'    => '404',
                'message' => 'Invalid mobile number.',
            ];
            return response()->json($data, 404);
        }

    }

    /**
     * UPDATE CONTENT
     */
    protected function update_content($obj, $data, $req_id){        
        try{                
            $user_id = Auth::id();            

            /**
             * QUERY SETUP
             */
            $obj = $obj->find($req_id);
            
            $obj->comment       = $data['comment'];                        
            $obj->status        = $data['status'];
            $obj->updated_by    = $user_id;
            $obj->updated_at    = date('Y-m-d H:i:s');

            // return $obj;

            if($obj->update()){
                /**
                 * MORE FEATURED MANAGEMENT FUNCTION
                 */
                $obj = $this->more_featured_management($obj,$data,$obj->id,'update');

                $data = [
                    'data'        => $obj,
                    'status'    => 'success',
                    'code'      => '200',
                    'message'   => '<i class="fa fa-check-circle"></i> Data has been updated successfully.',
                ];

                return response()->json($data, 200);
            }else{
                $data = [
                    'status'  => 'error',
                    'code'    => '404',
                    'message' => '<i class="fa fa-info-circle"></i> Error occurred. Data doesn\'t update.'
                ];

                return response()->json($data, 404);
            }
        }catch(\Exception $e){
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
     * @param  \App\Models\CommentList  $obj
     * @return \Illuminate\Http\Response
     */
    public function show(CommentList $obj, Request $request)
    {
        $user_id        = Auth::id();
        $limit          = $request['limit']>0?$request['limit']:10;
        $srch_keyword   = $request->has('keyword')?$request['keyword']:'';
        $own_result     = $request->has('own_result')?$request['own_result']:'';
        $req_from       = $request->has('req_from')?$request['req_from']:'';
        $getHostName    = $req_from?str_replace('www.','',$req_from):'';
        $req_status     = $request->has('req_status')?$request['req_status']:'';
        $reply_status   = $request->has('reply_status')?$request['reply_status']:'';
        $review_status   = $request->has('review_status')?$request['review_status']:'';
        $filter_data_from  = $request->has('filter_data_from')?$request['filter_data_from']:'';
        $filter_data_to  = $request->has('filter_data_to')?$request['filter_data_to']:'';

        if($limit>0) $getData = $obj::select('comment_lists.*')
        ->leftJoin('comment_rel_infos','comment_rel_infos.comment_id','=','comment_lists.id')
        ->when($req_from, function($q) use($req_from, $getHostName){
            // return $q->leftJoin('domain_lists', 'domain_lists.domain_id', '=', 'comment_rel_infos.domain_id')
            // ->where(function($qr) use($req_from){
            //     $qr->where('domain_lists.sub_domain',$req_from)
            //     ->orWhere('domain_lists.alias','LIKE',"%$req_from%");
            // });
            $q->where('comment_rel_infos.url','LIKE',"%$req_from%")
                ->orWhere('comment_rel_infos.url','LIKE',"%.$getHostName%");
        })->when($srch_keyword, function($q) use($srch_keyword){
            return $q->where('comment_lists.comment','LIKE',"%$srch_keyword%")
            ->orWhere('comment_lists.device_info','LIKE',"%$srch_keyword%")
            ->orWhere('comment_rel_infos.user_name','LIKE',"%$srch_keyword%")
            ->orWhere('comment_rel_infos.user_email','LIKE',"%$srch_keyword%")
            ->orWhere('comment_rel_infos.contact_no','LIKE',"%$srch_keyword%")
            ->orWhere('comment_rel_infos.url','LIKE',"%$srch_keyword%");
        })->when($own_result, function($q) use($user_id){
            return $q->where('comment_lists.created_by',$user_id);
        })->when($req_status, function($q) use($req_status){            
            return $q->where('comment_rel_infos.type_id', $req_status);                
        })->when($reply_status, function($q) use($reply_status){            
            if($reply_status=='1'){
                return $q->join('comments_reply_infos','comments_reply_infos.comment_id','=','comment_lists.id')->groupBy('comments_reply_infos.comment_id');
            }elseif($reply_status=='2'){
                return $q->leftJoin('comments_reply_infos','comments_reply_infos.comment_id','=','comment_lists.id')
                ->whereNull('comments_reply_infos.id')
                ->groupBy('comment_lists.id');
            }          
        })->when($review_status, function($q) use($review_status){            
            return $q->join('review_ratings','review_ratings.comment_id','=','comment_lists.id')->where('review',$review_status);          
        })->when($filter_data_from, function($q) use($filter_data_from,$filter_data_to){            
            return $q->whereBetween('comment_lists.created_at',[$filter_data_from,$filter_data_to]);          
        })->with(['CommentRelInfo','CommentReplyInfo','CommentReviewInfo','CommentReviewInfo'])
        ->orderBy('comment_lists.id','DESC')        
        ->paginate($limit);

        // return response()->json($getData, 200);
        return CommentsResource::collection($getData);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CommentList  $obj
     * @return \Illuminate\Http\Response
     */
    public function edit(CommentList $obj, $id)
    {
        $getData = $obj::select('*')->where('id',$id)
        ->with('CommentRelInfo')
        ->first();

        return response()->json($getData, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CommentList  $obj
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CommentList $obj, $req_id)
    {
        // return ModificationController::update_content($obj, $request, $req_id);
        return $this->update_content($obj, $request, $req_id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CommentList  $obj
     * @return \Illuminate\Http\Response
     */
    public function destroy(CommentList $obj, $id)
    {
        $geResult = $obj::find($id)->delete();

        return response()->json($geResult, 200);
    }
}
