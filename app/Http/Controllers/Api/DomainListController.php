<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\ModificationController as ModificationController;
use App\Http\Resources\DomainListCollection as DomainListResource;

use App\Models\DomainList;
use Illuminate\Http\Request;

use Auth;
class DomainListController extends Controller
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
     * Display a listing from request data
     */
    public function load(DomainList $obj, Request $request){

        // return $request->all();
        $req_from = $request->has('req_from')?$request['req_from']:'';
        $getData = $obj::select('*')        
        ->where('sub_domain',$req_from)
        ->orWhere('alias','LIKE',"%$req_from%")
        ->first();

        // return view('services.opinion_poll')->with('data', $getData);

        // return response()->json($getData, 200);
        return new DomainListResource($getData);
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
    public function store(DomainList $obj, Request $request)
    {
        // return $request->all();

        return ModificationController::save_content($obj, $request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DomainList  $obj
     * @return \Illuminate\Http\Response
     */
    public function show(DomainList $obj, Request $request)
    {
        $user_id = Auth::id();

        // return $request->all();
        $limit = $request['limit']>0?$request['limit']:10;
        $srch_keyword = $request->has('keyword')?$request['keyword']:'';
        $own_result = $request->has('own_result')?$request['own_result']:'';
        
        if($limit>0) $getData = $obj::select('*')
        ->when($srch_keyword, function($q) use($srch_keyword){
            return $q->where('sitename_bn','LIKE',"%$srch_keyword%")
            ->orWhere('sitename_en','LIKE',"%$srch_keyword%")
            ->orWhere('sub_domain','LIKE',"%$srch_keyword%")
            ->orWhere('alias','LIKE',"%$srch_keyword%");
        })->when($own_result, function($q) use($user_id){
            return $q->where('created_by',$user_id);
        })->with(['DomainGroupInfo'])
        ->paginate($limit);

        // return response()->json($getData, 200);
        return DomainListResource::collection($getData);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DomainList  $obj
     * @return \Illuminate\Http\Response
     */
    public function edit(DomainList $obj, $id)
    {
        $getData = $obj::select('*')->where('id',$id)->first();

        return response()->json($getData, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DomainList  $obj
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DomainList $obj, $req_id)
    {
        return ModificationController::update_content($obj, $request, $req_id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DomainList  $obj
     * @return \Illuminate\Http\Response
     */
    public function destroy(DomainList $obj, $id)
    {
        $geResult = $obj::find($id)->delete();

        return response()->json($geResult, 200);
    }

    /**
     * Search tags
     */
    public function search(DomainList $obj)
    {
        // return request()->get('term');
        $getData = $obj::select('id','domain_id','sub_domain','sitename_bn','sitename_en')
        ->where('sub_domain','LIKE','%'.request()->get('term').'%')
        ->orWhere('sitename_bn','LIKE','%'.request()->get('term').'%')
        ->orWhere('sitename_en','LIKE','%'.request()->get('term').'%')
        ->take(request()->get('limit'))->get();

        return response()->json($getData, 200);
    }
}
