<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserPreferenceResource;
use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserPreferenceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userPreference = UserPreference::where('user_id', Auth::id())->first();

        return $this->returnSuccess(new UserPreferenceResource($userPreference));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //@TODO validate request
        $userPreference = UserPreference::where('user_id', Auth::id())->first();
        $preference = json_encode($request->get('preference'));
        if($userPreference instanceof UserPreference){
            $userPreference->preference = $preference;
            $userPreference->save();
        }else{
            $userPreference = UserPreference::create([
                'user_id' => Auth::id(),
                'preference' => $preference,
            ]);
        }
        if(!$userPreference instanceof UserPreference){
            return $this->returnError([], "Failed to save preference");
        }
        return $this->returnSuccess(new UserPreferenceResource($userPreference));
    }
}
