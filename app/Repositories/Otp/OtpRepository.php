<?php

namespace Repository\Otp;


use App\Models\OtpList;
use Repository\BaseRepository;

class OtpRepository extends BaseRepository
{

    public function model()
    {
        return OtpList::class;
    }

}