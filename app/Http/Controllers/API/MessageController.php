<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function getUsers()
    {
        $user = User::where('id', '!=', auth()->user()->id)->get();
        return response()->json($user);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required',
            'receiver_id' => 'required|exists:users,id',
        'type' => 'required|in:text,image,video',
        ]);
       //Store Message
       Message::create([
           'sender_id' => auth()->user()->id,
           'receiver_id' => $request->receiver_id,
           'message' => $request->message,
           'type' => $request->type,
       ]);
        return response()->json([
            'success' => 'Message sent successfully',

    ], 200);
    }

    public function getMessages($id)
    {
        $messages = Message::where(function ($query) use ($id) {
            $query->where('sender_id', auth()->user()->id)->where('receiver_id', $id);
        })->orWhere(function ($query) use ($id) {
            $query->where('sender_id', $id)->where('receiver_id', auth()->user()->id);
        })->get();
        $messages = $messages->map(function ($message)   use ($id) {
        $message-> is_me = $message->sender_id == auth()->user()->id;
        return $message;
        })
        ;
        return response()->json($messages , 200);
    }
}
