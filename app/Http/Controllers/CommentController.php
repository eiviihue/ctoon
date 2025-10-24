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

        if (empty($data['chapter_id'])) {
            $data['chapter_id'] = null;
        }

        $comment = $comic->comments()->create($data);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Comment posted',
                'body' => $comment->body,
                'user_name' => $comment->user->name,
                'created_at' => $comment->created_at->toDateTimeString(),
                'chapter_number' => $comment->chapter->number ?? null
            ]);
        }

        if ($request->ajax()) {
            return response()->json([
                'user_name' => $comment->user->name,
                'chapter_number' => $comment->chapter->number,
                'body' => e($comment->body),
            ]);
        }

        return back()->with('success', 'Comment posted');
    }

    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment); // optional policy
        $comment->delete();
        return back()->with('success', 'Comment deleted');
    }
}

