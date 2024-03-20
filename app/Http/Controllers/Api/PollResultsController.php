<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\ModificationController as ModificationController;

use App\Models\DomainList;
use App\Models\PollResults;
use Illuminate\Http\Request;

use DB;
use Session;

class PollResultsController extends Controller
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
    public function store(PollResults $obj, Request $request)
    {
        $key = 'poll_id-'.$request['poll_id'];
        // return $_SESSION[$key];
        // return $request->all();        
        $qryStr = 'INSERT INTO `poll_results`(poll_id,poll_option_id,votes) VALUES('.$request['poll_id'].','.$request['poll_option_id'].',votes+1) ON DUPLICATE KEY UPDATE votes=votes+1';

        DB::beginTransaction();

        try {
            DB::select($qryStr);

            // Session::put($key, true);
            // $_SESSION[$key] = true;

            if($request->has('req_from')){
                $host = $request['req_from'];
                $getDomainInfo = DomainList::select('id')
                ->where('domain_lists.sub_domain',$host)
                ->orWhere('domain_lists.alias','LIKE',"%www.$host")
                ->first();

                if(@$getDomainInfo->id>0){
                    $qryStr = 'INSERT INTO `poll_result_by_domains`(poll_id,domain_id,poll_option_id,votes) VALUES('.$request['poll_id'].','.$getDomainInfo->id.','.$request['poll_option_id'].',votes+1) ON DUPLICATE KEY UPDATE votes=votes+1';

                    DB::update($qryStr);
                }
                // return $getDomainInfo->id;
            }

            if(trim($request['comments'])!==''){
                $qryStr = 'INSERT INTO `poll_result_explains`(poll_id,poll_option_id,comments,created_at) VALUES('.$request['poll_id'].','.$request['poll_option_id'].',"'.addslashes(htmlentities($request['comments'])).'","'.date('Y-m-d H:i:s').'")';

                DB::select($qryStr);
            }

            $data = [                
                'status'    => 'success',
                'code'      => '200',
                'message'   => '<i class="fa fa-check-circle"></i> Data has been submitted successfully.',
            ];

            DB::commit();

            return response()->json($data, 200);
        }catch(\Exception $e){
            $data = [
                'status'  => 'error',
                'code'    => '404',
                'message' => $e->getMessage(),
            ];

            DB::rollback();

            return response()->json($data, 404);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PollResults  $pollResults
     * @return \Illuminate\Http\Response
     */
    public function show(PollResults $pollResults)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PollResults  $pollResults
     * @return \Illuminate\Http\Response
     */
    public function edit(PollResults $pollResults)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PollResults  $pollResults
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PollResults $pollResults)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PollResults  $pollResults
     * @return \Illuminate\Http\Response
     */
    public function destroy(PollResults $pollResults)
    {
        //
    }
}
