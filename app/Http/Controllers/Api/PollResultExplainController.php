<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\ModificationController as ModificationController;
use App\Http\Resources\PollResultExplainCollection as PollResultExplainResource;

use App\Models\PollResultExplain;
use Illuminate\Http\Request;

class PollResultExplainController extends Controller
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PollResultExplain  $pollResultExplain
     * @return \Illuminate\Http\Response
     */
    public function show(PollResultExplain $obj, Request $request)
    {
        // return $request->all();
        $limit = $request['limit']>0?$request['limit']:10;
        $srch_keyword = $request->has('keyword')?$request['keyword']:'';

        $getData = $obj::select('*')
        ->where('poll_id', $request['poll_id'])
        ->where('poll_option_id', $request['poll_option_id'])
        ->orderBy('id','DESC')
        ->when($srch_keyword, function($q) use($srch_keyword){
            return $q->where('comments','LIKE',"%$srch_keyword%");
        })->paginate($limit);        

        // return response()->json($getData, 200);
        return PollResultExplainResource::collection($getData);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PollResultExplain  $pollResultExplain
     * @return \Illuminate\Http\Response
     */
    public function edit(PollResultExplain $pollResultExplain)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PollResultExplain  $pollResultExplain
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PollResultExplain $pollResultExplain)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PollResultExplain  $pollResultExplain
     * @return \Illuminate\Http\Response
     */
    public function destroy(PollResultExplain $pollResultExplain)
    {
        //
    }
}
