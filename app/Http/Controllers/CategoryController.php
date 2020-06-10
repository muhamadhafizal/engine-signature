<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;

class CategoryController extends Controller
{
    public function index(){
        return response()->json(['status'=>'success','value'=>'category engine']);
    }

    public function all(){
        
        $category = Category::all();
        return response()->json(['status'=>'success','value'=>$category]);

    }
}
