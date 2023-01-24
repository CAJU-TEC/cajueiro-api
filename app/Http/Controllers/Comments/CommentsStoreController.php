<?php

namespace App\Http\Controllers\Comments;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use App\Supports\DatesTimes\DateSupport;
use Exception;
use Illuminate\Http\Request;

class CommentsStoreController extends Controller
{
    public function __construct(private Comment $comment, private Ticket $ticket)
    {
    }

    public function __invoke(Request $request)
    {
        try {

            $ticket = $this->ticket->with(['comments'])->findOrFail($request->get('ticket_id'));
            $collaborator = User::with(['collaborator'])->find(auth()->user()->id);

            $data = [
                'collaborator_id' => $collaborator->collaborator->id ?? NULL,
                'description' => $request->get('description'),
                'status' => $request->get('status'),
            ];

            $comment = $ticket->comments()->create($data);

            if ($request->image) {
                $name = $comment->id . '.' . explode(
                    '/',
                    explode(
                        ':',
                        substr(
                            $request->image,
                            0,
                            strpos($request->image, ';')
                        )
                    )[1]
                )[1];
                $uri = storage_path('app/public/images/') . $name;
                \Image::make($request->image)->save($uri);

                $comment->image()->updateOrCreate([
                    'uri' => $name
                ]);
                return response()->json($comment, 201);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}
