<?php
namespace App\Controllers;

use Illuminate\View\Factory as View;

class Example
{
    protected $view;
    
    public function __construct(View $view)
    {
        $this->view = $view;
    }
    
    public function index()
    {
        return $this->view->make('index', ['name' => 'John Doe']);
    }
}