<?php

namespace App\Http\Controllers;

use App\Models\Comic;
use App\Models\Comment;
use App\Http\Requests\StoreCommentRequest;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request, Comic $comic)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $comic->comments()->create($data);
        return back()->with('success', 'Comment posted');
    }

    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment); // optional policy
        $comment->delete();
        return back()->with('success', 'Comment deleted');
    }
}

