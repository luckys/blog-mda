<?php

namespace App\Http\Controllers;


use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Tag;
use Illuminate\Http\Request;

class TagController extends Controller {

    public function __construct() {

        $this->middleware('auth');
        $this->authorize('editor');
    }

    public function store(Request $request) {

        if($request->ajax()) {
            return response()->json([
                'ok' => $this->addTag($request->tag)
            ]);
        }

    }

    private function addTag($tag) {
        $existe = Tag::all()->where('name', $tag)->count();

        if($existe) return null;

        $newTag = new Tag();
        $newTag->name = $tag;
        $newTag->save();
        return $newTag->toArray();
    }

}
