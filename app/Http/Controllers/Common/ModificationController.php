<?php

namespace App\Http\Controllers\Common;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

use Auth;

class ModificationController
{
    /**
     * BASE64 TO IMAGE CONVERT FUNCTION
     */
    static public function base64ToImage($base64_image,$traget_path){
        $filename = null;
            
        if($base64_image!= "" || !is_null($base64_image)){
            if (preg_match('/^data:image\/(\w+);base64,/', $base64_image)) {
                $image_data = substr($base64_image, strpos($base64_image, ',') + 1);
                $image_data = base64_decode($image_data);
                $filename = uniqid().'.png';
                Storage::disk('public')->put($traget_path.'/'.$filename, $image_data);
            }
        }

        return $filename;
    }

    /**
     * SAVE CONTENT
     */
    static public function save_content($obj, $data, $get_last_id=''){
        try{                
            $user_id = Auth::id();
            // return gettype($data);
            if(gettype($data)=='object') $getData = $data->toArray();
            else $getData = $data;

            foreach($getData as $key => $val){
                $obj->$key = $val;
            }
            $obj->created_by        = $user_id;

            if($obj->save()){
                if($get_last_id) return $obj->id;
                
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
    static public function update_content($obj, $data, $req_id){
        try{                
            $user_id = Auth::id();

            $obj = $obj->find($req_id);

            // return gettype($data);
            if(gettype($data)=='object') $getData = $data->toArray();
            else $getData = $data;

            foreach($getData as $key => $val){
                $obj->$key = $val;
            }
            $obj->updated_by = $user_id;

            // return ($obj);

            if($obj->update()){
                $data = [
                    'data'      => $obj,
                    'status'    => true,
                    'code'      => '200',
                    'message'   => '<i class="fa fa-check-circle"></i> Data has been updated successfully.',
                ];

                return response()->json($data, 200);
            }else{
                $data = [
                    'status'  => false,
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
}
?>