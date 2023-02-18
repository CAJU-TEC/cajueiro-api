<?php

namespace App\Http\Controllers\Email;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\EmailTicketNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class EmailTicketController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        //
        $user = User::first();
        $project = [
            'greeting' => 'Olá ' . $user->name . ',',
            'body' => 'Este é um projeto atribuido a você.',
            'thanks' => 'Obrigado por acessar CAJU Tec.',
            'actionText' => 'ACOMPANHAR PROTOCOLO',
            'actionURL' => url('/'),
            'id' => 57
        ];

        Notification::send($user, new EmailTicketNotification($project));

        dd('Notification sent!');
    }
}
