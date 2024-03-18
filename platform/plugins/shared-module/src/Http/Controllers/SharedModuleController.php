<?php

namespace Botble\SharedModule\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Location\Http\Resources\StateResource;
use Botble\SharedModule\Trait\ViettelPostLoginTrait;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SharedModuleController extends BaseController
{
    use ViettelPostLoginTrait;

    public function ajaxGetProvincesVietelPost(Request $request)
    {
        $data = DB::table('viettel_province')
            ->orderBy('viettel_name')
            ->get();

        return response()->json($data);
    }
    public function ajaxGetDistrictsVietelPost(Request $request)
    {
        $stateID = $request->input('state_id');

        if ($stateID && $stateID != 'null') {
            $data = DB::table('viettel_district')
            ->select('viettel_id as id', 'viettel_name as name')
            ->orderBy('viettel_name')
            ->where('viettel_provice_id', $stateID)
            ->get();
            $response = [
                'data' => $data,
                'error' => false,
                'message' => '',
            ];
            return response()->json($response);
        }
    }
    public function ajaxGetWardsVietelPost(Request $request)
    {
        $districtID = $request->input('district_id');

        if ($districtID && $districtID != 'null') {
            $data = DB::table('viettel_wards')
            ->orderBy('viettel_name')
            ->where('viettel_district_id', $districtID)
            ->get();
            $response = [
                'data' => $data,
                'error' => false,
                'message' => '',
            ];
            return response()->json($response);
        }

    }
    public function ajaxGetAddressAllShowroomViettelPost(Request $request)
    {
        $token = $this->getTokenVietelPost();
        $url = 'https://partner.viettelpost.vn/v2/user/listInventory';
        $client = new Client(['headers' => ['Content-Type' => 'application/json', 'Token' => $token]]);
        $response = $client->get($url);
        $resData = json_decode((string)$response->getBody(), true);
        if($resData['status'] == 200 && $resData['error'] == false){
            return response()->json($resData['data']);
        }
    }
}
