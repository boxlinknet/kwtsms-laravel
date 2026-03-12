<?php

namespace KwtSMS\Laravel\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\View\View;

class HelpController extends Controller
{
    public function index(): View
    {
        return view('kwtsms::admin.help');
    }
}
