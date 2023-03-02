<?php

namespace App\Http\Controllers\Comments;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentsStoreController extends Controller
{
    public function __construct(private Comment $comment, private Ticket $ticket)
    {
    }

    public function __invoke(Request $request)
    {
        try {
            DB::beginTransaction();
            $ticket = $this->ticket->with(['comments'])->findOrFail($request->get('ticket_id'));
            $collaborator = User::with(['collaborator'])->find(auth()->user()->id);

            $data = [
                'collaborator_id' => $collaborator->collaborator->id ?? $ticket->collaborator_id,
                'description' => $request->get('description'),
                'status' => $request->get('status'),
            ];

            $ticket->update([
                'status' => $request->status,
                'date_finish_ticket' => now()
            ]);

            throw_if(empty($data['description']), new Exception('Preencha o campo do comentário para interagir'));

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
            }

            DB::commit();
            return response()->json($comment, 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }
}
