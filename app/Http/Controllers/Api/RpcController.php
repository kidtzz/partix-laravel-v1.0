<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RpcController extends Controller
{
    public function handle(Request $request)
    {
        $method = $request->input('method');
        $args = $request->input('args', []);

        // Load routing and permissions from config/rpc.php
        $routes = config('rpc.routes');

        if (!is_array($routes) || !array_key_exists($method, $routes)) {
            return response()->json(['message' => "Method $method not implemented in backend."], 501);
        }

        try {
            $callable = $routes[$method];
            $serviceClass = $callable[0];
            $serviceMethod = $callable[1];
            $allowedRoles = $callable[2] ?? ['Admin']; // Default strictly to Admin if not defined

            // Role-Based Access Control (RBAC)
            $userRole = $request->user()->roles->first()->name ?? 'Kasir';
            if (!in_array($userRole, $allowedRoles)) {
                Log::warning("Unauthorized RPC access attempt: User role [$userRole] tried to call [$method]");
                return response()->json(['message' => 'Forbidden: Anda tidak memiliki izin untuk melakukan aksi ini.'], 403);
            }

            // Dependency Injection using Laravel IoC Container
            $serviceInstance = app($serviceClass);
            
            // Call the service method with the provided arguments
            $result = call_user_func_array([$serviceInstance, $serviceMethod], $args);
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            Log::error("RPC Error [$method]: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
