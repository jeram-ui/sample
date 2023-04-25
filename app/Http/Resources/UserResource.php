<?php

namespace App\Http\Resources;

use App\Laravue\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'admin' => $this->admin,
            'avatar' => 'http://20.20.70.168:8000/lgu_back/public/images/client/' . $this->image_path,
            'dtls' => array()
            // 'dtls' => $this->getDetails()
        ];
    }
    public function getDetails()
    {
        $dtls = db::table('humanresource.employee_information')
            ->where('PPID', $this->Employee_id)->first();
        return $dtls;
    }
}
