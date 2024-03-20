<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\ModificationController as ModificationController;
use App\Http\Resources\OnlineSurveysCollection as OnlineSurveysResource;

use App\Models\SsoCredentials;
use App\Models\OnlineSurvey;
use App\Models\OnlineSurveyDomainAccess;
use App\Models\DomainList;
use Illuminate\Http\Request;

use Auth;
use DB;

class OnlineSurveyController extends Controller
{
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

        $user_id = Auth::id();
        if(!$user_id){
            $allowedHostsStr = env('ALLOWED_HOSTS', 'e-participation.bangladesh.gov.bd,localhost,');
            $allowedHosts = explode(',', $allowedHostsStr);        
            $requestHost = parse_url($request->headers->get('origin'),  PHP_URL_HOST);

            if(!in_array($requestHost, $allowedHosts)) return response()->json(['msg' => 'Unauthorized','status' => false], 200);
        }

        $getData = (Object) [];
        $req_from = $request->has('req_from')?$request['req_from']:'';
        $getHostName = $req_from?str_replace('www.','',$req_from):'';

        /**
         * For total surveys
         */
        $getRes = OnlineSurvey::select(DB::raw('COUNT(online_surveys.id) AS total'))
        ->when($req_from, function($q) use($req_from){
            $q->leftJoin('online_survey_domain_accesses','online_survey_domain_accesses.survey_id','=','online_surveys.id')
            ->leftJoin('domain_groups', 'domain_groups.id', '=', 'online_survey_domain_accesses.domain_group_id')
            ->leftJoin('domain_lists', function($join){
                $join->on('domain_lists.domain_group_id', '=', 'domain_groups.id');
                $join->orOn('domain_lists.id', '=', 'online_survey_domain_accesses.domain_id');
            })
            ->where(function($qr) use($req_from){
                $qr->where('domain_lists.sub_domain',$req_from)
                ->orWhere('domain_lists.alias','LIKE',"%www.$req_from%");
            });
        })
        ->first();

        if(isset($getRes->total)) $getData->total = $getRes->total;
        else $getData->total = 0;
        
        /**
         * For total ongoing surveys
         */
        $getRes = OnlineSurvey::select(DB::raw('COUNT(online_surveys.id) AS total_ongoing'))
        ->when($req_from, function($q) use($req_from){
            $q->leftJoin('online_survey_domain_accesses','online_survey_domain_accesses.survey_id','=','online_surveys.id')
            ->leftJoin('domain_groups', 'domain_groups.id', '=', 'online_survey_domain_accesses.domain_group_id')
            ->leftJoin('domain_lists', function($join){
                $join->on('domain_lists.domain_group_id', '=', 'domain_groups.id');
                $join->orOn('domain_lists.id', '=', 'online_survey_domain_accesses.domain_id');
            })
            ->where(function($qr) use($req_from){
                $qr->where('domain_lists.sub_domain',$req_from)
                ->orWhere('domain_lists.alias','LIKE',"%www.$req_from%");
            });
        })
        ->where(function($q){
            $q->where('type', 0)
            ->orWhere(function($q){
                return $q->where('type', 1)
                ->where('end_time','>=',date('Y-m-d H:i:s'));
            });
        })
        ->first();

        if(isset($getRes->total_ongoing)) $getData->total_ongoing = $getRes->total_ongoing;
        else $getData->total_ongoing = 0;

        /**
         * For total completed surveys
         */
        $getRes = OnlineSurvey::select(DB::raw('COUNT(online_surveys.id) AS total_completed'))
        ->when($req_from, function($q) use($req_from){
            $q->leftJoin('online_survey_domain_accesses','online_survey_domain_accesses.survey_id','=','online_surveys.id')
            ->leftJoin('domain_groups', 'domain_groups.id', '=', 'online_survey_domain_accesses.domain_group_id')
            ->leftJoin('domain_lists', function($join){
                $join->on('domain_lists.domain_group_id', '=', 'domain_groups.id');
                $join->orOn('domain_lists.id', '=', 'online_survey_domain_accesses.domain_id');
            })
            ->where(function($qr) use($req_from){
                $qr->where('domain_lists.sub_domain',$req_from)
                ->orWhere('domain_lists.alias','LIKE',"%www.$req_from%");
            });
        })
        ->where(function($q){
            $q->where('type', 1)
            ->where('end_time','<',date('Y-m-d H:i:s'));
        })
        ->first();

        if(isset($getRes->total_completed)) $getData->total_completed = $getRes->total_completed;
        else $getData->total_completed = 0;

        /**
         * For total organization using surveys
         */
        $getRes = OnlineSurvey::select(DB::raw('COUNT(DISTINCT(domain_lists.id)) AS total_organization'))        
        ->leftJoin('online_survey_domain_accesses','online_survey_domain_accesses.survey_id','=','online_surveys.id')
        ->leftJoin('domain_groups', 'domain_groups.id', '=', 'online_survey_domain_accesses.domain_group_id')
        ->leftJoin('domain_lists', function($join){
            $join->on('domain_lists.domain_group_id', '=', 'domain_groups.id');
            $join->orOn('domain_lists.id', '=', 'online_survey_domain_accesses.domain_id');
        })
        ->when($req_from, function($q) use($req_from){
            $q->where(function($qr) use($req_from){
                $qr->where('domain_lists.sub_domain',$req_from)
                ->orWhere('domain_lists.alias','LIKE',"%www.$req_from%");
            });
        })
        ->first();

        if(isset($getRes->total_organization)) $getData->total_organization = $getRes->total_organization;
        else $getData->total_organization = 0;        
    
        return response()->json(['data' => $getData, 'status' => 2], 200);        
    }

    public function loadSDOnlineSurveys(OnlineSurvey $obj, Request $request){
        // return $request->all();
        $limit = $request['limit']>0?$request['limit']:10;
        $req_from = $request->has('req_from')?$request['req_from']:'';

        $getRes = SsoCredentials::select('sso_credentials.id')
        ->leftJoin('domain_groups','domain_groups.id','=','sso_credentials.domain_group_id')
        ->leftJoin('domain_lists','domain_lists.id','=','domain_groups.id')
        ->where(function($q) use($req_from){
            $q->where('domain_lists.sub_domain',$req_from)
            ->orWhere('domain_lists.alias','LIKE',"%www.$req_from%");
        })
        ->where('sso_credentials.app_id',$request['app_id'])
        ->where('sso_credentials.secret_key',$request['secret_key'])
        ->first();

        if(!empty($getRes)){
            $getData = $obj::select('online_surveys.*')
            ->leftJoin('online_survey_domain_accesses','online_survey_domain_accesses.survey_id','=','online_surveys.id')
            ->leftJoin('domain_lists', 'domain_lists.id', '=', 'online_survey_domain_accesses.domain_id')        
            ->where(function($q) use($req_from){
                $q->where('domain_lists.sub_domain',$req_from)
                ->orWhere('domain_lists.alias','LIKE',"%www.$req_from%");
            })
            ->orderBy('online_surveys.id','DESC')
            ->paginate($limit);

            // return response()->json($getData, 200);
            return OnlineSurveysResource::collection($getData);
        }else return response()->json(['status' => 2], 200);
    }

    /**
     * Display a listing from request data
     */
    public function load(OnlineSurvey $obj, Request $request){

        // return $request->all();
        $cur_date_time = date('Y-m-d H:i:s');
        $req_from = $request->has('req_from')?$request['req_from']:'';

        $getData = $obj::select('online_surveys.*')
        ->leftJoin('online_survey_domain_accesses','online_survey_domain_accesses.survey_id','=','online_surveys.id')        
        ->leftJoin('domain_groups', 'domain_groups.id', '=', 'online_survey_domain_accesses.domain_group_id')
        // ->leftJoin('domain_lists', 'domain_lists.id', '=', 'online_survey_domain_accesses.domain_id')
        ->leftJoin('domain_lists', function($join){
            $join->on('domain_lists.domain_group_id', '=', 'domain_groups.id');
            $join->orOn('domain_lists.id', '=', 'online_survey_domain_accesses.domain_id');
        })
        ->where(function($q) use($cur_date_time){
            $q->where('type', 0);
            $q->orWhere(function($sq) use($cur_date_time){
                $sq->where('type', 1);
                $sq->where('start_time', '<=', $cur_date_time);
                $sq->where('end_time', '>=', $cur_date_time);
            });
        })
        ->where(function($q) use($req_from){
            $q->where('domain_lists.sub_domain',$req_from)
            ->orWhere('domain_lists.alias','LIKE',"%www.$req_from%");
        })
        ->where('online_surveys.status',1)        
        ->orderBy('online_surveys.id','DESC')
        ->paginate(1);

        // return view('services.opinion_survey')->with('data', $getData);

        // return response()->json($getData, 200);
        // return new OnlineSurveysResource($getData);
        return OnlineSurveysResource::collection($getData);
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
    public function store(OnlineSurvey $obj, Request $request)
    {
        // return $request->all();

        return $this->save_content($obj, $request);
    }

    protected function more_featured_management($obj,$data,$req_id,$action){
        /**
         * Remove domains data first
         */
        if($action=='update') DB::select('DELETE FROM `online_survey_domain_accesses` WHERE survey_id='.$req_id);

        if(!empty($data['group_ids'])){

            $surveyDomainQry = 'INSERT INTO `online_survey_domain_accesses`(domain_group_id,survey_id) VALUES';
            
            $co=0; foreach($data['group_ids'] as $key => $val){
                if($co++>0) $surveyDomainQry .= ',';
                $surveyDomainQry .= '('.$val.','.$req_id.')';
            }
            $obj->survey_domain_ids =  DB::select($surveyDomainQry);
        }

        /**
         * Save to survey options table
         */
        elseif(!empty($data['domain_ids'])){
            $surveyDomainQry = 'INSERT INTO `online_survey_domain_accesses`(domain_id,survey_id) VALUES';
            
            $co=0; foreach($data['domain_ids'] as $key => $val){
                if($co++>0) $surveyDomainQry .= ',';
                $surveyDomainQry .= '('.$val.','.$req_id.')';
            }
            $obj->survey_domain_ids =  DB::select($surveyDomainQry);
        }

        return $obj;
    }

    /**
     * SAVE CONTENT
     */
    protected function save_content($obj, $data){
        // return $data;
        try{                
            $user_id = Auth::id();

            $obj->survey_title    = $data['survey_title'];
            if(@$data['description'])
            $obj->description   = $data['description'];
            $obj->embed_code    = $data['embed_code'];
            $obj->cat_id        = $data['cat_id'];
            $obj->type          = $data['type'];
            if(@$data['start_time'])
            $obj->start_time    = date('Y-m-d H:i:s', strtotime($data['start_time']));
            if(@$data['end_time'])
            $obj->end_time      = date('Y-m-d H:i:s', strtotime($data['end_time']));
            $obj->status        = $data['status'];            
            $obj->created_by    = $user_id;
            $obj->created_at    = date('Y-m-d H:i:s');

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
     * UPDATE CONTENT
     */
    protected function update_content($obj, $data, $req_id){
        try{                
            $user_id = Auth::id();            

            /**
             * QUERY SETUP
             */
            $obj = $obj->find($req_id);
            
            $obj->survey_title    = $data['survey_title'];
            if(@$data['description'])
            $obj->description   = $data['description'];
            $obj->embed_code    = $data['embed_code'];
            $obj->cat_id        = $data['cat_id'];
            $obj->type          = $data['type'];
            if(@$data['start_time'])
            $obj->start_time    = date('Y-m-d H:i:s', strtotime($data['start_time']));
            if(@$data['end_time'])
            $obj->end_time      = date('Y-m-d H:i:s', strtotime($data['end_time']));
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
     * @param  \App\Models\OnlineSurvey  $obj
     * @return \Illuminate\Http\Response
     */
    public function show(OnlineSurvey $obj, Request $request)
    {
        $user_id = Auth::id();
        
        // return $request->all();
        $limit = $request['limit']>0?$request['limit']:10;
        $srch_keyword = $request->has('keyword')?$request['keyword']:'';
        $own_result = $request->has('own_result')?$request['own_result']:'';
        $req_from = $request->has('req_from')?$request['req_from']:'';
        $req_status = $request->has('req_status')?$request['req_status']:'';

        if($limit>0){
            $getData = $obj::select('online_surveys.*')
            ->when($req_from, function($q) use($req_from){
                $q->leftJoin('online_survey_domain_accesses','online_survey_domain_accesses.survey_id','=','online_surveys.id')
                ->leftJoin('domain_lists', 'domain_lists.id', '=', 'online_survey_domain_accesses.domain_id')        
                ->where(function($qr) use($req_from){
                    $qr->where('domain_lists.sub_domain',$req_from)
                    ->orWhere('domain_lists.alias','LIKE',"%www.$req_from%");
                });
            })->when($srch_keyword, function($q) use($srch_keyword){
                return $q->where('online_surveys.survey_title','LIKE',"%$srch_keyword%");
            })->when($own_result, function($q) use($user_id){
                return $q->where('online_surveys.created_by',$user_id);
            })->when($req_status, function($q) use($req_status){
                if($req_status=='1'){
                    return $q->where('online_surveys.type', 0)
                    ->orWhere(function($sq){
                        return $sq->where('online_surveys.type', 1)
                        ->where('online_surveys.start_time','<=',date('Y-m-d H:i:s'))
                        ->where('online_surveys.end_time','>=',date('Y-m-d H:i:s'));
                    });
                }else{
                    return $q->where('online_surveys.type', 1)
                    ->where('online_surveys.end_time','<',date('Y-m-d H:i:s'));
                }
            })->with(['CatInfo','DomainInfo','DomainGroupInfo'])
            ->orderBy('online_surveys.id','DESC')
            ->paginate($limit);
        }

        // return response()->json($getData, 200);
        return OnlineSurveysResource::collection($getData);
    }

    public function loadOnlineSurveysHistory(OnlineSurvey $obj, Request $request)
    {   
        // return $request->all();
        $limit = $request['limit']>0?$request['limit']:10;        
        $req_status = $request->has('req_status')?$request['req_status']:'';

        if(@$request['req_from']=='epi.bangladesh.gov.bd' || @$request['req_from']=='127.0.0.1:8080'){
            
            $getData = $obj::select('online_surveys.*')
            ->when($req_status, function($q) use($req_status){
                if($req_status=='1'){
                    return $q->where('online_surveys.type', 0)
                    ->orWhere(function($sq){
                        return $sq->where('online_surveys.type', 1)
                        ->where('online_surveys.start_time','<=',date('Y-m-d H:i:s'))
                        ->where('online_surveys.end_time','>=',date('Y-m-d H:i:s'));
                    });
                }else{
                    return $q->where('online_surveys.type', 1)
                    ->where('online_surveys.end_time','<',date('Y-m-d H:i:s'));
                }
            })->with(['CatInfo','DomainInfo'])
            ->orderBy('online_surveys.id','DESC')
            ->paginate($limit);        
            
            return OnlineSurveysResource::collection($getData);
        }else return response()->json(['data' => [], 'status' => false], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\OnlineSurvey  $obj
     * @return \Illuminate\Http\Response
     */
    public function edit(OnlineSurvey $obj, $id)
    {
        $getData = $obj::select('*')
        ->where('id',$id)        
        ->first();

        // return response()->json($getData, 200);
        return new OnlineSurveysResource($getData);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OnlineSurvey  $obj
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OnlineSurvey $obj, $req_id)
    {
        // return ModificationController::update_content($obj, $request, $req_id);
        return $this->update_content($obj, $request, $req_id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OnlineSurvey  $obj
     * @return \Illuminate\Http\Response
     */
    public function destroy(OnlineSurvey $obj, $id)
    {
        $geResult = $obj::find($id)->delete();

        return response()->json($geResult, 200);
    }
}
