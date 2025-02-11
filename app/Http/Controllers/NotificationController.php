<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function update(Request $request)
    {
        $company = auth()->user();

        $request->validate([
            'notifications_by_email' => 'nullable|boolean',
            'update_reminder_notifications' => 'nullable|boolean',
            'new_projects_notifications_by_email' => 'nullable|boolean',
        ]);

        $notification = Notification::where('company_id', $company->id)->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune notification trouvée pour cette entreprise.',
            ], 404);
        }

        $notification->update([
            'notifications_by_email' => $request->notifications_by_email ?? $notification->notifications_by_email,
            'update_reminder_notifications' => $request->update_reminder_notifications ?? $notification->update_reminder_notifications,
            'new_projects_notifications_by_email' => $request->new_projects_notifications_by_email ?? $notification->new_projects_notifications_by_email,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Les notifications ont été mises à jour avec succès.',
            'data' => $notification,
        ]);
    }

    public function show()
    {
        $company = auth()->user();
        $notification = Notification::where('company_id', $company->id)->first();

        if (!$notification) {
            $notification = Notification::create([
                "company_id" => $company->id
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Les notifications de l\'entreprise ont été récupérées avec succès.',
            'data' => $notification,
        ]);
    }
}
