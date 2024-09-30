<?php

// In ChatController.php
namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Chatify\Facades\ChatifyMessenger as Chatify;
use Chatify\ChatifyMessenger;
use App\Models\User;
class ChatifyController extends ChatifyMessenger
{

    public function index1()
{
    // Define the role name you're looking for
    $roleName = 'therapist';

    // Fetch users with the specified role
    $users = User::whereHas('roles', function ($query) use ($roleName) {
        $query->where('name', $roleName);
    })->select('id', 'name')->get();

    return response()->json($users);
}
    public function sendPredefinedMessage(Request $request)
{
    // Default variables
    $error = (object)[
        'status' => 0,
        'message' => null
    ];
    $attachment = null;
    $attachment_title = null;

    // If there is an attachment [file]
    if ($request->hasFile('file')) {
        // Allowed extensions
        $allowed_images = Chatify::getAllowedImages();
        $allowed_files  = Chatify::getAllowedFiles();
        $allowed        = array_merge($allowed_images, $allowed_files);

        $file = $request->file('file');
        // Check file size
        if ($file->getSize() < Chatify::getMaxUploadSize()) {
            if (in_array(strtolower($file->extension()), $allowed)) {
                // Get attachment name
                $attachment_title = $file->getClientOriginalName();
                // Upload attachment and store the new name
                $attachment = Str::uuid() . "." . $file->extension();
                $file->storeAs(config('chatify.attachments.folder'), $attachment, config('chatify.storage_disk_name'));
            } else {
                $error->status = 1;
                $error->message = "File extension not allowed!";
            }
        } else {
            $error->status = 1;
            $error->message = "File size you are trying to upload is too large!";
        }
    }

    if (!$error->status) {
        $message = Chatify::newMessage([
            'from_id' => Auth::user()->id,
            'to_id' => $request['id'],
            'body' => htmlentities(trim($request['message']), ENT_QUOTES, 'UTF-8'),
            'attachment' => ($attachment) ? json_encode((object)[
                'new_name' => $attachment,
                'old_name' => htmlentities(trim($attachment_title), ENT_QUOTES, 'UTF-8'),
            ]) : null,
        ]);
        $messageData = Chatify::parseMessage($message);
        if (Auth::user()->id != $request['id']) {
            Chatify::push("private-chatify.".$request['id'], 'messaging', [
                'from_id' => Auth::user()->id,
                'to_id' => $request['id'],
                'message' => Chatify::messageCard($messageData, true)
            ]);
        }
    }

    // Send the response
    return Response::json([
        'status' => '200',
        'error' => $error,
        'message' => Chatify::messageCard(@$messageData),
        'tempID' => $request['temporaryMsgId'],
    ]);
}
}
