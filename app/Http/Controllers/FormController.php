<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

class FormController extends Controller
{

    public function index()
    {
        return view('admin.formulaires.index');
    }

}
