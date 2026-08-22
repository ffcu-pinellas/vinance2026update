<?php

namespace App\Lib;

use App\Models\AdminNotification;
use Illuminate\Support\Facades\Mail;

class Notify {

    public static function sendAdminNotification(array $data) {
        // Save to admin notifications table
        if (isset($data['title'])) {
            $notification = new AdminNotification();
            $notification->title = $data['title'];
            $notification->click_url = $data['click_url'] ?? '#';
            $notification->user_id = $data['user_id'] ?? 0;
            $notification->save();
        }

        // Send email notification
        if (isset($data['message'])) {
            try {
                $adminEmail = config('mail.from.address');
                if ($adminEmail) {
                    Mail::raw($data['message'], function ($message) use ($data, $adminEmail) {
                        $message->to($adminEmail)
                                ->subject($data['title'] ?? 'New Notification');
                    });
                }
            } catch (\Exception $e) {
                \Log::error("Email notification failed: ".$e->getMessage());
            }
        }
    }
}
