<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\ModificationController as ModificationController;
use App\Http\Resources\OpinionPollsCollection as OpinionPollsResource;

use App\Models\SsoCredentials;
use App\Models\OpinionPoll;
use App\Models\OpinionPollDomainAccess;
use App\Models\PollResults;
use App\Models\DomainList;
use Illuminate\Http\Request;

use Auth;
use DB;

class OpinionPollController extends Controller
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
        $requestHost = parse_url($request->headers->get('origin'),  PHP_URL_HOST);
        $user_id = Auth::id();
        if(!$user_id){
            $allowedHostsStr = env('ALLOWED_HOSTS', 'e-participation.bangladesh.gov.bd,localhost,');
            $allowedHosts = explode(',', $allowedHostsStr);

            if(!in_array($requestHost, $allowedHosts)) return response()->json(['msg' => 'Unauthorized','status' => false], 200);
        }

        $getData = (Object) [];
        $req_from = $request->has('req_from')?$request['req_from']:'';
        // return response()->json(['data' => config('global.base_host'), 'origin' => $requestHost, 'status' => 2], 200);

        /**
         * For total ongoing polls
         */
        $getRes = OpinionPoll::select(DB::raw('COUNT(opinion_polls.id) AS total_ongoing'))
        ->when($req_from, function($q) use($req_from){
            $q->leftJoin('opinion_poll_domain_accesses','opinion_poll_domain_accesses.poll_id','=','opinion_polls.id')
            ->leftJoin('domain_groups', 'domain_groups.id', '=', 'opinion_poll_domain_accesses.domain_group_id')
            // ->leftJoin('domain_lists', 'domain_lists.id', '=', 'opinion_poll_domain_accesses.domain_id')
            ->leftJoin('domain_lists', function($join){
                $join->on('domain_lists.domain_group_id', '=', 'domain_groups.id');
                $join->orOn('domain_lists.id', '=', 'opinion_poll_domain_accesses.domain_id');
            })
            ->where(function($qr) use($req_from){
                $qr->where('domain_lists.sub_domain',$req_from)
                ->orWhere('domain_lists.alias','LIKE',"%www.$req_from%");
            });
        })
        ->where(function($q){
            $q->where('type', 0)
            ->orWhere(function($qw){
                return $qw->where('type', 1)
                ->where('end_time','>=',date('Y-m-d H:i:s'));
            });
        })        
        ->first();

        if(isset($getRes->total_ongoing)) $getData->total_ongoing = $getRes->total_ongoing;
        else $getData->total_ongoing = 0;

        /**
         * For total completed polls
         */
        $getRes = OpinionPoll::select(DB::raw('COUNT(opinion_polls.id) AS total_completed'))
        ->when($req_from, function($q) use($req_from){
            $q->leftJoin('opinion_poll_domain_accesses','opinion_poll_domain_accesses.poll_id','=','opinion_polls.id')
            ->leftJoin('domain_groups', 'domain_groups.id', '=', 'opinion_poll_domain_accesses.domain_group_id')
            // ->leftJoin('domain_lists', 'domain_lists.id', '=', 'opinion_poll_domain_accesses.domain_id')
            ->leftJoin('domain_lists', function($join){
                $join->on('domain_lists.domain_group_id', '=', 'domain_groups.id');
                $join->orOn('domain_lists.id', '=', 'opinion_poll_domain_accesses.domain_id');
            })
            ->where(function($qr) use($req_from){
                $qr->where('domain_lists.sub_domain',$req_from)
                ->orWhere('domain_lists.alias','LIKE',"%www.$req_from%");
            });
        })
        ->where('type', 1)
        ->where('end_time','<',date('Y-m-d H:i:s'))        
        ->first();

        if(isset($getRes->total_completed)) $getData->total_completed = $getRes->total_completed;
        else $getData->total_completed = 0;

        /**
         * For total organization using polls
         */
        $getRes = OpinionPoll::select(DB::raw('COUNT(DISTINCT(domain_lists.id)) AS total_organization'))
        ->leftJoin('opinion_poll_domain_accesses','opinion_poll_domain_accesses.poll_id','=','opinion_polls.id')
        ->leftJoin('domain_groups', 'domain_groups.id', '=', 'opinion_poll_domain_accesses.domain_group_id')
        // ->leftJoin('domain_lists', 'domain_lists.id', '=', 'opinion_poll_domain_accesses.domain_id')
        ->leftJoin('domain_lists', function($join){
            $join->on('domain_lists.domain_group_id', '=', 'domain_groups.id');
            $join->orOn('domain_lists.id', '=', 'opinion_poll_domain_accesses.domain_id');
        })
        ->when($req_from, function($q) use($req_from){
            $q->where(function($qr) use($req_from){
                $qr->where('domain_lists.sub_domain',$req_from)
                ->orWhere('domain_lists.alias','LIKE',"%.".str_replace("www.","",$req_from));
            });
        })
        ->first();

        if(isset($getRes->total_organization)) $getData->total_organization = $getRes->total_organization;
        else $getData->total_organization = 0;

        /**
         * For total participants polls
         */
        $getRes = PollResults::select(DB::raw('SUM(votes) AS total_participants'))
        ->when($req_from, function($q) use($req_from){
            $q->leftJoin('opinion_poll_domain_accesses','opinion_poll_domain_accesses.poll_id','=','poll_results.poll_id')
            ->leftJoin('domain_groups', 'domain_groups.id', '=', 'opinion_poll_domain_accesses.domain_group_id')
            // ->leftJoin('domain_lists', 'domain_lists.id', '=', 'opinion_poll_domain_accesses.domain_id')
            ->leftJoin('domain_lists', function($join){
                $join->on('domain_lists.domain_group_id', '=', 'domain_groups.id');
                $join->orOn('domain_lists.id', '=', 'opinion_poll_domain_accesses.domain_id');
            })
            ->where(function($qr) use($req_from){
                $qr->where('domain_lists.sub_domain',$req_from)
                ->orWhere('domain_lists.alias','LIKE',"%www.$req_from%");
            });
        })
        ->first();

        if(isset($getRes->total_participants)) $getData->total_participants = $getRes->total_participants;
        else $getData->total_participants = 0;
    
        return response()->json(['data' => $getData, 'status' => 2], 200);
    }

    public function loadSDOpinionPolls(OpinionPoll $obj, Request $request){
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
            $getData = $obj::select('opinion_polls.*')
            ->leftJoin('opinion_poll_domain_accesses','opinion_poll_domain_accesses.poll_id','=','opinion_polls.id')
            ->leftJoin('domain_lists', 'domain_lists.id', '=', 'opinion_poll_domain_accesses.domain_id')        
            ->where(function($q) use($req_from){
                $q->where('domain_lists.sub_domain',$req_from)
                ->orWhere('domain_lists.alias','LIKE',"%www.$req_from%");
            })
            ->with('PollOptions','TotalVotes')        
            ->orderBy('opinion_polls.id','DESC')
            ->paginate($limit);

            // return response()->json($getData, 200);
            return OpinionPollsResource::collection($getData);
        }else return response()->json(['status' => 2], 200);
    }

    /**
     * Display a listing from request data
     */
    public function load(OpinionPoll $obj, Request $request){

        // return $request->all();
        if($request->has('req_from')){
            $cur_date_time = date('Y-m-d H:i:s');
            $req_from = $request->has('req_from')?$request['req_from']:'';
            
            $getData = $obj::select('opinion_polls.*')
            ->leftJoin('opinion_poll_domain_accesses','opinion_poll_domain_accesses.poll_id','=','opinion_polls.id')        
            ->leftJoin('domain_groups', 'domain_groups.id', '=', 'opinion_poll_domain_accesses.domain_group_id')
            // ->leftJoin('domain_lists', 'domain_lists.id', '=', 'opinion_poll_domain_accesses.domain_id')
            ->leftJoin('domain_lists', function($join){
                $join->on('domain_lists.domain_group_id', '=', 'domain_groups.id');
                $join->orOn('domain_lists.id', '=', 'opinion_poll_domain_accesses.domain_id');
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
            ->where('opinion_polls.status',1)
            ->with('PollOptions','TotalVotes')        
            ->orderBy('opinion_polls.id','DESC')
            ->paginate(1);
        }else $getData = [];

        // return view('services.opinion_poll')->with('data', $getData);

        // return response()->json($getData, 200);
        // return new OpinionPollsResource($getData);
        if(count($getData)==0) return response()->json(['data' => $getData], 200);
        else return OpinionPollsResource::collection($getData);
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
    public function store(OpinionPoll $obj, Request $request)
    {
        // return $request->all();

        return $this->save_content($obj, $request);
    }

    protected function more_featured_management($obj,$data,$req_id,$action){        
        /**
         * Remove poll options data first
         */
        if($action=='update') DB::select('DELETE FROM `poll_options` WHERE poll_id='.$req_id);

        /**
         * Save to poll options table
         */
        $pollOptionQry = 'INSERT INTO `poll_options`(id,option_title,poll_id,option_photo_id,req_explain) VALUES';
        
        $co=0; $pollResultData = []; foreach($data['poll_options'] as $key => $val){
            if($co++>0) $pollOptionQry .= ',';
            $pollOptionQry .= '('.($val['id']?$val['id']:'NULL').',"'.$val['option_title'].'",'.$req_id.','.(isset($val['option_photo_info'])?$val['option_photo_info']['id']:"null").",".($val['req_explain']?1:0).')';
            if(@$val['option_result']) $pollResultData[$key] = '('.$req_id.','.$val['id'].','.$val['option_result']['votes'].')';
        }                

        $obj->poll_options =  DB::select($pollOptionQry);

        if(!empty($pollResultData)){
            $pollResultQry = 'INSERT INTO `poll_results`(poll_id,poll_option_id,votes) VALUES'.implode(',',$pollResultData);
            // $obj->poll_options->option_result =  
            DB::select($pollResultQry);
        }
        

        /**
         * Remove domains data first
         */
        if($action=='update') DB::select('DELETE FROM `opinion_poll_domain_accesses` WHERE poll_id='.$req_id);

        if(!empty($data['group_ids'])){

            $pollDomainQry = 'INSERT INTO `opinion_poll_domain_accesses`(domain_group_id,poll_id) VALUES';
            
            $co=0; foreach($data['group_ids'] as $key => $val){
                if($co++>0) $pollDomainQry .= ',';
                $pollDomainQry .= '('.$val.','.$req_id.')';
            }
            $obj->poll_domain_ids =  DB::select($pollDomainQry);
        }

        /**
         * Save to poll options table
         */
        elseif(!empty($data['domain_ids'])){
            $pollDomainQry = 'INSERT INTO `opinion_poll_domain_accesses`(domain_id,poll_id) VALUES';
            
            $co=0; foreach($data['domain_ids'] as $key => $val){
                if($co++>0) $pollDomainQry .= ',';
                $pollDomainQry .= '('.$val.','.$req_id.')';
            }
            $obj->poll_domain_ids =  DB::select($pollDomainQry);
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

            $obj->poll_title    = $data['poll_title'];
            if(@$data['description'])
            $obj->description   = $data['description'];
            $obj->cat_id        = $data['cat_id'];
            $obj->type          = $data['type'];
            if(@$data['start_time'])
            $obj->start_time    = date('Y-m-d H:i:s', strtotime($data['start_time']));
            if(@$data['end_time'])
            $obj->end_time      = date('Y-m-d H:i:s', strtotime($data['end_time']));
            if(@$data['cover_photo'])
            $obj->cover_photo   = $data['cover_photo'];
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
            
            $obj->poll_title    = $data['poll_title'];
            if(@$data['description'])
            $obj->description   = $data['description'];
            $obj->cat_id        = $data['cat_id'];
            $obj->type          = $data['type'];
            if(@$data['start_time'])
            $obj->start_time    = date('Y-m-d H:i:s', strtotime($data['start_time']));
            if(@$data['end_time'])
            $obj->end_time      = date('Y-m-d H:i:s', strtotime($data['end_time']));
            if(@$data['cover_photo'])
            $obj->cover_photo   = $data['cover_photo'];
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
     * @param  \App\Models\OpinionPoll  $obj
     * @return \Illuminate\Http\Response
     */
    public function show(OpinionPoll $obj, Request $request)
    {
        $user_id = Auth::id();
        
        // return $request->all();
        $limit = $request['limit']>0?$request['limit']:10;
        $srch_keyword = $request->has('keyword')?$request['keyword']:'';        
        $req_from = $request->has('req_from')?$request['req_from']:'';
        if($req_from) $own_result = '';
        else $own_result = $request->has('own_result')?$request['own_result']:'';
        $req_status = $request->has('req_status')?$request['req_status']:'';

        if($limit>0){
            $getData = $obj::select('opinion_polls.*')
            ->when($req_from, function($q) use($req_from){
                $q->leftJoin('opinion_poll_domain_accesses','opinion_poll_domain_accesses.poll_id','=','opinion_polls.id')
                ->leftJoin('domain_groups', 'domain_groups.id', '=', 'opinion_poll_domain_accesses.domain_group_id')
                // ->leftJoin('domain_lists', 'domain_lists.id', '=', 'opinion_poll_domain_accesses.domain_id')
                ->leftJoin('domain_lists', function($join){
                    $join->on('domain_lists.domain_group_id', '=', 'domain_groups.id');
                    $join->orOn('domain_lists.id', '=', 'opinion_poll_domain_accesses.domain_id');
                })
                ->where(function($qr) use($req_from){
                    $qr->where('domain_lists.sub_domain',$req_from)
                    ->orWhere('domain_lists.alias','LIKE',"%www.$req_from%");
                });
            })->when($srch_keyword, function($q) use($srch_keyword){
                return $q->where('opinion_polls.poll_title','LIKE',"%$srch_keyword%");
            })->when($own_result, function($q) use($user_id){
                return $q->where('opinion_polls.created_by',$user_id);
            })->when($req_status, function($q) use($req_status){
                if($req_status=='1'){
                    return $q->where('opinion_polls.type', 0)
                    ->orWhere(function($sq){
                        return $sq->where('opinion_polls.type', 1)
                        ->where('opinion_polls.start_time','<=',date('Y-m-d H:i:s'))
                        ->where('opinion_polls.end_time','>=',date('Y-m-d H:i:s'));
                    });
                }else{
                    return $q->where('opinion_polls.type', 1)
                    ->where('opinion_polls.end_time','<',date('Y-m-d H:i:s'));
                }
            })->with(['PollOptions','CatInfo','DomainInfo','DomainGroupInfo','TotalVotes'])
            ->orderBy('opinion_polls.id','DESC')
            ->paginate($limit);
        }

        // return response()->json($getData, 200);
        return OpinionPollsResource::collection($getData);
    }

    public function getOpinionPoll(OpinionPoll $obj, Request $request, $id){
        // return $id;
        // return $request->all();        
        if($id>0 && $request->has('host')){
            $cur_date_time = date('Y-m-d H:i:s');
            $host = $request->has('host')?$request['host']:'';
        
            $getData = $obj::select('opinion_polls.*')
            ->leftJoin('opinion_poll_domain_accesses','opinion_poll_domain_accesses.poll_id','=','opinion_polls.id')        
            ->leftJoin('domain_groups', 'domain_groups.id', '=', 'opinion_poll_domain_accesses.domain_group_id')
            // ->leftJoin('domain_lists', 'domain_lists.id', '=', 'opinion_poll_domain_accesses.domain_id')
            ->leftJoin('domain_lists', function($join){
                $join->on('domain_lists.domain_group_id', '=', 'domain_groups.id');
                $join->orOn('domain_lists.id', '=', 'opinion_poll_domain_accesses.domain_id');
            })
            ->where(function($q) use($cur_date_time){
                $q->where('type', 0);
                $q->orWhere(function($sq) use($cur_date_time){
                    $sq->where('type', 1);
                    $sq->where('start_time', '<=', $cur_date_time);
                    $sq->where('end_time', '>=', $cur_date_time);
                });
            })
            ->where(function($q) use($host){
                $q->where('domain_lists.sub_domain',$host)
                ->orWhere('domain_lists.alias','LIKE',"%www.$host");
            })
            ->where(['opinion_polls.id' => $id,'opinion_polls.status' => 1])
            ->with('PollOptions','TotalVotes')
            ->get();
        }else $getData = [];

        // return view('services.opinion_poll')->with('data', $getData);

        // return response()->json($getData, 200);
        // return new OpinionPollsResource($getData);
        if(count($getData)==0) return response()->json($getData, 200);
        else return OpinionPollsResource::collection($getData);
    }

    public function loadOpinionPollsHistory(OpinionPoll $obj, Request $request)
    {   
        // return $request->all();
        $limit = $request['limit']>0?$request['limit']:10;        
        $req_status = $request->has('req_status')?$request['req_status']:'';

        if(@$request['req_from']=='epi.bangladesh.gov.bd' || @$request['req_from']=='127.0.0.1:8080'){
            
            $getData = $obj::select('opinion_polls.*')
            ->when($req_status, function($q) use($req_status){
                if($req_status=='1'){
                    return $q->where('opinion_polls.type', 0)
                    ->orWhere(function($sq){
                        return $sq->where('opinion_polls.type', 1)
                        ->where('opinion_polls.start_time','<=',date('Y-m-d H:i:s'))
                        ->where('opinion_polls.end_time','>=',date('Y-m-d H:i:s'));
                    });
                }else{
                    return $q->where('opinion_polls.type', 1)
                    ->where('opinion_polls.end_time','<',date('Y-m-d H:i:s'));
                }
            })->with(['PollOptions','CatInfo','DomainInfo','TotalVotes'])
            ->orderBy('opinion_polls.id','DESC')
            ->paginate($limit);        
            
            return OpinionPollsResource::collection($getData);
        }else return response()->json(['data' => [], 'status' => false], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\OpinionPoll  $obj
     * @return \Illuminate\Http\Response
     */
    public function edit(OpinionPoll $obj, $id)
    {
        $getData = $obj::select('*')
        ->where('id',$id)
        ->with('PollOptions')
        ->first();

        // return response()->json($getData, 200);
        return new OpinionPollsResource($getData);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OpinionPoll  $obj
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OpinionPoll $obj, $req_id)
    {
        // return ModificationController::update_content($obj, $request, $req_id);
        return $this->update_content($obj, $request, $req_id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OpinionPoll  $obj
     * @return \Illuminate\Http\Response
     */
    public function destroy(OpinionPoll $obj, $id)
    {
        $geResult = $obj::find($id)->delete();

        return response()->json($geResult, 200);
    }
}
