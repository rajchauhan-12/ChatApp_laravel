<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;



use App\Models\Chat;
use App\Models\User;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class SendUserMessage implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    /**
     * Create a new event instance.
     *
     * @param Chat $message
     * @return void
     */
    public function __construct(Chat $message)
    {
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel
     */
    public function broadcastOn()
    {
        return new Channel('admin-messages'); // Channel to broadcast on
    }

    /**
     * Prepare the data to be broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        // Assuming sender_id is a user ID
        $user = User::find($this->message->sender_id); // Replace User with the actual model name if different

        if ($user) {
            return [
                'message' => $this->message->message,
                'receiver_id' => $this->message->receiver_id,
                'sender_id' => $this->message->sender_id,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'image' => asset('storage/' . $user->picture), // Assuming 'picture' is the image field
                ],
                'created_at' => $this->message->created_at,
            ];
        } else {
            return [
                'message' => $this->message->message,
                'receiver_id' => $this->message->receiver_id,
                'sender_id' => $this->message->sender_id,
                'user' => [
                    'id' => null,
                    'name' => 'Unknown User',
                    'image' => asset('storage/default-avatar.png'), // Default image if user is not found
                ],
                'created_at' => $this->message->created_at,
            ];
        }
    }

    /**
     * The name of the event to broadcast.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'user-message'; // Event name to broadcast as
    }
}							


