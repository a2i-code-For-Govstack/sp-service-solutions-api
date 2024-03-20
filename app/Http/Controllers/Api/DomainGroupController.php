<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\ModificationController as ModificationController;
use App\Http\Resources\DomainGroupsCollection as DomainGroupsResource;

use App\Models\DomainGroup;
use Illuminate\Http\Request;

use Auth;
class DomainGroupController extends Controller
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
    public function store(DomainGroup $obj, Request $request)
    {
        // return $request->all();

        return ModificationController::save_content($obj, $request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DomainGroup  $domainGroup
     * @return \Illuminate\Http\Response
     */
    public function show(DomainGroup $obj, Request $request)
    {
        $user_id = Auth::id();

        // return $request->all();
        $limit = $request['limit']>0?$request['limit']:'';
        $srch_keyword = $request->has('keyword')?$request['keyword']:'';
        $own_result = $request->has('own_result')?$request['own_result']:'';

        if($limit>0) $getData = $obj::select('*')
        ->when($srch_keyword, function($q) use($srch_keyword){
            return $q->where('group_title','LIKE',"%$srch_keyword%");
        })->when($own_result, function($q) use($user_id){
            return $q->where('created_by',$user_id);
        })->paginate($limit);
        else $getData = $obj::select('*')->get();

        // return response()->json($getData, 200);
        return DomainGroupsResource::collection($getData);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DomainGroup  $domainGroup
     * @return \Illuminate\Http\Response
     */
    public function edit(DomainGroup $obj, $id)
    {
        $getData = $obj::select('*')->where('id',$id)->first();

        return response()->json($getData, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DomainGroup  $domainGroup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DomainGroup $obj, $req_id)
    {
        return ModificationController::update_content($obj, $request, $req_id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DomainGroup  $domainGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy(DomainGroup $obj, $id)
    {
        $geResult = $obj::find($id)->delete();

        return response()->json($geResult, 200);
    }

    /**
     * Search tags
     */
    public function search(DomainGroup $obj)
    {
        // return request()->get('term');
        $getData = $obj::select('id','group_title')
        ->where('group_title','LIKE','%'.request()->get('term').'%')
        ->take(request()->get('limit'))->get();

        return response()->json($getData, 200);
    }
}
