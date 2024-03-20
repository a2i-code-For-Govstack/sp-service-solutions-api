<?php
namespace App\Http\Controllers\Common;

trait SmsApi
{
	public $successStatus = 200;
    protected $api_base_url = 'http://bulkmsg.teletalk.com.bd/api/sendSMS';
    protected $sms_api_user = 'BangladeshNP';
    protected $sms_api_pwd = 'A2ist2#0155';
    protected $sms_acode = '1005120';
    protected $sms_masking = '16345';
    protected $sms_data = [];
  
    public function send($phone,$message){

        // $json = array();
        // $json['auth'] = array("username"=>"BangladeshNP",
        //     "password"=>"A2ist2#0155",
        //     "acode"=>"1005120");
        // $json["smsInfo"] = array(
        //     "message" => $message,
        //     "masking" => "16345",
        //     "msisdn"  => [$phone]);

        $this->sms_data = [
            "auth" => [
                "username" => $this->sms_api_user,
                "password" => $this->sms_api_pwd,
                "acode" => $this->sms_acode
            ],
            "smsInfo" => [
                "message" => $message,
                "masking" => $this->sms_masking,
                "msisdn"  => [$phone]
            ]
        ];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://bulkmsg.teletalk.com.bd/api/sendSMS",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($this->sms_data,JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER => array(
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        
        return $response;
    }
}