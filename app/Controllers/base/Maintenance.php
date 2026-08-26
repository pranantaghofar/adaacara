<?php namespace App\Controllers\base;

use CodeIgniter\Controller;

class Maintenance extends Controller
{
    public function index(): string
    {
        return view('maintenance');
    }
}
