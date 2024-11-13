<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use DB;
use App\Models\Award;
use App\Models\AwardCategory;
use App\Enums\QualityStatus;

class ReportController extends Controller
{

    public function index()
    {
        return view('admin.outils.index');
    }

}
