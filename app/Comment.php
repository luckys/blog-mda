<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['article_id', 'username', 'body'];

    public function article()
    {
    	return $this->belongsTo(Article::class, 'article_id');
    }

    public function assignToArticle($id)
    {
    	$article = Article::find($id);

    	$this->article()->associate($article);
    }
}
