<?php

namespace App\Services;

use App\Models\LogActivity;

class LogService
{
    public static function log($action, $module, $description)
    {
        LogActivity::create([
            'user_id' => \Illuminate\Support\Facades\Auth::id() ?? 1,
            'action' => $action,
            'module' => $module,
            'details' => $description
        ]);
    }

    public function getLogActivityAdmin()
    {
        return LogActivity::with('user.roles')->orderBy('created_at', 'desc')->take(500)->get()->map(function($log) {
            $username = $log->user ? $log->user->username : 'System';
            $role = ($log->user && $log->user->roles->isNotEmpty()) ? $log->user->roles->first()->name : 'System';
            return [
                'timestamp' => $log->created_at->format('Y-m-d\TH:i:s.v\Z'),
                'username' => $username,
                'role' => $role,
                'action' => $log->action,
                'module' => $log->module,
                'details' => $log->details
            ];
        })->toArray();
    }
    public function getSystemLogs()
    {
        return \App\Models\SystemLog::orderBy('created_at', 'desc')->take(200)->get()->map(function($log) {
            return [
                'id' => $log->id,
                'level' => $log->level,
                'message' => $log->message,
                'context' => $log->context,
                'user_agent' => $log->user_agent,
                'url' => $log->url,
                'user' => $log->user,
                'timestamp' => $log->created_at->format('Y-m-d H:i:s')
            ];
        })->toArray();
    }

    public function logSystemEvent($payload)
    {
        \App\Models\SystemLog::create([
            'level' => $payload['level'] ?? 'error',
            'message' => $payload['message'] ?? 'Unknown error',
            'context' => $payload['context'] ?? '',
            'user_agent' => $payload['user_agent'] ?? '',
            'url' => $payload['url'] ?? '',
            'user' => $payload['user'] ?? 'Guest'
        ]);
        
        return ['status' => 'success'];
    }
}
