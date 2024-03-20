<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\ModificationController as ModificationController;
use App\Http\Controllers\Common\EncryptionController as EncryptionController;
use App\Http\Controllers\Common\SmsController as SmsController;
use App\Http\Resources\AdminUserListCollection as AdminUserListResource;

use App\User;
use App\Models\UserRoles;
use App\Models\UserInfos;
use App\Models\SsoCredentials;
use Illuminate\Http\Request;
use Session;
use Auth;
use DB;

class UserController extends Controller
{
    private function attemptToLogin($obj, $request){
        // return $request;

        if (Auth::attempt(['email' => $request['login_id'], 'password' => $request['password']])) {
                
            $getUser = $obj::where('email', $request['login_id'])->with(['UserInfo','RoleInfo'])->first();
            $getUser['token'] =  $getUser->createToken('MyApp')-> accessToken;
            
        }

        if(isset($getUser)){
            if($getUser->verified==0){
                return response()->json(['status' => 2] , 200);
            }else{
                return response()->json(['status' => 1, 'user_info' => $getUser] , 200);
            }
            
       }else{
           return response()->json(['status' => 0] , 200);
       }
    }

    public function OAuthClientCheck(User $obj, Request $request){
        $req_from = $request->has('req_from')?$request['req_from']:'';        

        try{
            $getRes = SsoCredentials::select('sso_credentials.id','domain_lists.sub_domain','domain_lists.alias')
            ->leftJoin('domain_groups','domain_groups.id','=','sso_credentials.domain_group_id')
            ->leftJoin('domain_lists','domain_lists.domain_group_id','=','domain_groups.id')
            ->where(function($q) use($req_from){
                $q->where('domain_lists.sub_domain',$req_from)
                ->orWhere('domain_lists.alias','LIKE',"%$req_from%")
                ->orWhere('domain_lists.alias','LIKE',"%www.$req_from%");
            })
            ->where('sso_credentials.app_id',$request['app_id'])
            ->where('sso_credentials.secret_key',$request['secret_key'])
            ->where('domain_lists.status', 1)
            ->first();
        }catch(\Exception $e){
            return $e->getMessage();
        }

        // return $getRes;

        if(!empty($getRes)){

            $getUserData = $obj::select('users.*')
            ->leftJoin('user_roles','user_roles.user_id','=','users.id')
            ->leftJoin('user_role_infos','user_role_infos.id','=','user_roles.role_id')
            ->leftJoin('domain_lists','domain_lists.domain_group_id','=','user_role_infos.domain_group_id')
            ->where(function($q) use($req_from){
                $q->where('domain_lists.sub_domain',$req_from)
                ->orWhere('domain_lists.alias','LIKE',"%$req_from%")
                ->orWhere('domain_lists.alias','LIKE',"%www.$req_from%");
            })
            ->where('domain_lists.status', 1)
            ->with('UserInfo')
            ->first();

            // return $getUserData;

            if(!empty($getUserData)){
                $data = [];
                $data['login_id'] = $getUserData->email;
                $data['password'] = EncryptionController::decode_content($getUserData->UserInfo->pass_code);

                return $this->attemptToLogin($obj, $data);
            }else return response()->json(['status' => 2], 200);

        }else return response()->json(['status' => 2], 200);
    }

    public function UserInfo(Request $request){
        // return Auth::user();
        return response()->json(['user' => Auth::user()]);
    }

    public function SocialUserInfo(Request $request){
        $data = $request['data'];
        $getUser = User::where('email',$data['email'])->first();
        // return $request->all();

        // set default password
        $get_password = '123456';

        if(empty($getUser)){
            $User = new User;
            $User->name = $data['name'];
            $User->email = isset($data['email'])?$data['email']:null;
            $User->password = bcrypt($get_password);
            $User->save();            

            return response()->json(['status' => true , 'user' => $User] , 200);
        } else return response()->json(['status' => true , 'user' => $getUser] , 200);
    }

    public function Login(Request $request){
        // return $request->all();
        // return Auth::user();
        // return $request->session()->all();

        if (Auth::attempt(['email' => $request['login_id'], 'password' => $request['password']])) {

            $getUser = User::where('email', $request['login_id'])->with('userInfo')->first();
            $getUser['token'] =  $getUser->createToken('MyApp')-> accessToken;
            
        }

        if(isset($getUser)){
            if($getUser->verified==0){
                return response()->json(['status' => 2] , 200);
            }else{
                return response()->json(['status' => 1, 'user_info' => $getUser] , 200);
            }
            
       }else{
           return response()->json(['status' => 0] , 200);
       }
    }

    public function AdminLogin(User $obj, Request $request){
        // return $request->all();
        // return Auth::user();
        // return $request->session()->all();
        return $this->attemptToLogin($obj, $request);
    }      

    public function Logout(Request $request) {
        try{
            Auth::logout();
            return response()->json(['status' => true] , 200);
        }catch(Exception $e){
            return $e;
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(User $obj, Request $request)
    {
        $messages = [
            'required' => 'This field is required',
            'unique' => 'This field is unique'
        ];

        $data = [];        
        $data['password'] = bcrypt($request['password']);
        if($request->has('user_type')) $data['user_type'] = $request['user_type'];
        $data['email'] = $request['email'];
        $data['auth_code'] = str_random(12);
        $data['verified'] = $request['verified'];
        $data['status'] = $request['status'];

        $getLastId = ModificationController::save_content($obj, $data, 1);            

        /**
         * User role save
         */
        DB::select('INSERT INTO `user_roles` (`user_id`,`role_id`) VALUES('.$getLastId.','.$request['role_info']['role_id'].')');

        $obj = new UserInfos;
        $userData = [];
        $userData = $request['user_info'];
        if($request->has('password')) $userData['pass_code'] = EncryptionController::encode_content($request['password']);
        $userData['user_id'] = $getLastId;

        return ModificationController::save_content($obj, $userData);
        // return $request->all();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $obj
     * @return \Illuminate\Http\Response
     */
    public function show(User $obj, Request $request)
    {
        $user_id = Auth::id();
        
        $limit = $request->has('limit')?$request['limit']:10;
        $srch_keyword = $request->has('keyword')?$request['keyword']:'';
        $own_result = $request->has('own_result')?$request['own_result']:'';

        if($limit>0) $getData = $obj::select('*')
        ->when($srch_keyword, function($q) use($srch_keyword){
            return $q->where('email','LIKE',"%$srch_keyword%");
        })->when($own_result, function($q) use($user_id){
            return $q->where('created_by',$user_id);
        })->where('user_type',1)
        ->with(['UserInfo','RoleInfo'])
        ->paginate($limit);
        
        else $getData = $obj::select('*')
        ->when($srch_keyword, function($q) use($srch_keyword){
            return $q->where('email','LIKE',"%$srch_keyword%");
        })->when($own_result, function($q) use($user_id){
            return $q->where('created_by',$user_id);
        })->where('user_type',1)
        ->with(['UserInfo','RoleInfo'])
        ->get();

        // return response()->json($getData, 200);
        return AdminUserListResource::collection($getData);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $obj
     * @return \Illuminate\Http\Response
     */
    public function edit(User $obj, $id)
    {
        $getData = $obj::select('*')
        ->where('id',$id)
        ->with(['UserInfo','RoleInfo'])
        ->first();

        return response()->json($getData, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $obj
     * @return \Illuminate\Http\Response
     */
    public function update(User $obj, Request $request, $req_id)
    {
        $data = [];        
        if($request->has('password')) $data['password'] = bcrypt($request['password']);
        $data['email'] = $request['email'];
        $data['status'] = $request['status'];
        $data['verified'] = $request['verified'];

        ModificationController::update_content($obj, $data, $req_id);

        /**
         * User Role delete        
         */
        DB::select('DELETE FROM `user_roles` WHERE `user_id`='.$req_id);

        /**
         * User role save
         */        
        DB::select('INSERT INTO `user_roles` (`user_id`,`role_id`) VALUES('.$req_id.','.$request['role_info']['role_id'].')');

        $obj = new UserInfos;
        $userData = [];
        $userData = $request['user_info'];
        if($request->has('password')) $userData['pass_code'] = EncryptionController::encode_content($request['password']);
        $userData['user_id'] = $req_id;

        return ModificationController::update_content($obj, $userData, $req_id);
        // return $request->all();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $obj
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $obj, $id)
    {
        $geResult = $obj::find($id)->delete();

        return response()->json($geResult, 200);
    }
}