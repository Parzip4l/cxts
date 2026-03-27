<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class RoutingController extends Controller
{
    public function index(Request $request)
    {
        return view('index');
    }

    /**
     * Display a view based on first route param
     *
     * @return \Illuminate\Http\Response
     */
    public function root(Request $request, $first)
    {
        if (! View::exists($first)) {
            abort(404);
        }

        return view($first);
    }

    /**
     * second level route
     */
    public function secondLevel(Request $request, $first, $second)
    {
        $view = $first . '.' . $second;

        if (! View::exists($view)) {
            abort(404);
        }

        return view($view);
    }

    public function thirdLevel(Request $request, $first, $second, $third)
    {
        $view = $first . '.' . $second . '.' . $third;

        if (! View::exists($view)) {
            abort(404);
        }

        return view($view);
    }
}
