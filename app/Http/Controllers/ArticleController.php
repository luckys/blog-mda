<?php

namespace App\Http\Controllers;

use App\Tag;
use Illuminate\Http\Request;
use App\Article;
use App\Http\Requests;
use App\Http\Controllers\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('editor');
        
        $articles = Article::all()->where('user_id', auth()->user()->id);

        return view('articles.index', compact('articles'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //$this->authorize('editor');

        $tags = Tag::all();
        return view('articles.create', compact('tags'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('editor');


        $article = Article::create($request->all());

        if($request->tag_list) $article->tags()->sync($request->input('tag_list'));

        $article->update(['user_id' => auth()->user()->id]);

        /* Imagen*/

        $file = $request->file('file');
        $nombre = $file->getClientOriginalName();

        \Storage::disk('local')->put($nombre,  \File::get($file));

        $article->image = $nombre;
        $article->save();
        return view('articles.show', compact('article'))->with('message', 'Artículo añadido correctamente');
    }

    public function addComment($id, Request $request)
    {

        $article = Article::findOrFail($id);

        return $article->assignComment($request->all());
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $article = Article::findOrFail($id);
        
        return view('articles.show', compact('article'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $article = Article::find($id);
        return view('articles.edit', compact('article'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $article = Article::find($id);
        $article->title = $request->title;
        $article->body = $request->body;
        $article->save();
        return redirect()->route('admin.articles.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($article_id)
    {
        $article = Article::find($article_id);
        if ($this->existImage($article)) Storage::delete($article->image);
        $article->comments()->delete();
        $article->delete();
        return redirect('/admin/articles');
    }

    public function search (Request $request){
         $tag = Tag::where('name', $request->tag)->get()->first();
         return view('articles.search', compact('tag'));
     }

    private function existImage($article)
    {
        return File::exists(Config::get('filesystems.disks.local.root').'/'.$article->image);
    }
}
