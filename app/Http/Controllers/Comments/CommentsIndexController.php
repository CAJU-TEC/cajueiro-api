<?php

namespace App\Http\Controllers\Comments;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentsIndexController extends Controller
{
    public function __construct(private Comment $comment)
    {
    }

    //
    public function __invoke(Request $request)
    {
        return response()->json(
            $this->comment->with(['image', 'collaborator'])->latest()->get(),
            200
        );
    }
}
