<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use DB;
use Auth;

class BasicConfigController extends Controller
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
    public function store(Request $request){        
        Storage::disk('public')->put('json/site-basic-config.json', response()->json($request));
        
        return response()->json(['status' => true], 200);
    }
    
    public function get(){
        $path = storage_path('app/public') . "/json/site-basic-config.json";
        $getContents = file_get_contents($path);
        
        preg_match("/\{(.*)\}/s", $getContents, $matches);

        $data = json_decode($matches[0]);
        
        return response()->json(['data' => $data, 'status' => true], 200);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(){
        /**
         * Get site basic configuration
         */
        $path = storage_path('app/public') . "/json/site-basic-config.json";
        $getContents = file_get_contents($path);
        
        preg_match("/\{(.*)\}/s", $getContents, $matches);

        $siteBasicConfigData = json_decode($matches[0]);
        
        /**
         * Get Default Setting Objects
         */
        $getArrObj = (object)[
            // Site basic config data
            'site_basic_config_data' => $siteBasicConfigData
        ];
        
        return response()->json(['data' => $getArrObj], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\OrderDeliveryPersonInfo  $obj
     * @return \Illuminate\Http\Response
     */
    public function edit(OrderDeliveryPersonInfo $obj)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OrderDeliveryPersonInfo  $obj
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OrderDeliveryPersonInfo $obj)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OrderDeliveryPersonInfo  $obj
     * @return \Illuminate\Http\Response
     */
    public function destroy(OrderDeliveryPersonInfo $obj)
    {
        //
    }
}
