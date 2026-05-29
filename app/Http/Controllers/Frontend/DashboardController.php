<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
class DashboardController extends Controller
{


        public function dashboard(){
        return view('backend.dashboard.index');
    }


}
