<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AccessLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class GDPRController extends Controller
{
    /**
     * Show the GDPR controls page
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Ensure user has an API token for dashboard API calls
        if (! $request->session()->has('api_token')) {
            $token = $user->createToken('web-session-token')->plainTextToken;
            $request->session()->put('api_token', $token);
        }

        return view('gdpr-controls', [
            'user' => $user,
        ]);
    }

    /**
     * Export all user data (Right to Data Portability)
     */
    public function exportData(Request $request): JsonResponse
    {
        $user = $request->user();

        // Load all related data with relationships
        $user->load([
            'contexts.profileValues.attribute',
            'accessLogs',
        ]);

        /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Context> $contexts */
        $contexts = $user->contexts;
        
        /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\AccessLog> $accessLogs */
        $accessLogs = $user->accessLogs;
        
        $exportData = [
            'export_info' => [
                'exported_at' => now()->toISOString(),
                'user_id' => $user->id,
                'format_version' => '1.0',
            ],
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
            'contexts' => $contexts->map(function ($context) {
                return [
                    'id' => $context->id,
                    'slug' => $context->slug,
                    'name' => $context->name,
                    'description' => $context->description,
                    'is_default' => $context->is_default,
                    'is_active' => $context->is_active,
                    'created_at' => $context->created_at,
                    'updated_at' => $context->updated_at,
                    'profile_values' => $context->profileValues->map(function ($profileValue) {
                        return [
                            'id' => $profileValue->id,
                            'attribute' => [
                                'key_name' => $profileValue->attribute->key_name,
                                'display_name' => $profileValue->attribute->display_name,
                                'data_type' => $profileValue->attribute->data_type,
                            ],
                            'value' => $profileValue->value,
                            'visibility' => $profileValue->visibility,
                            'created_at' => $profileValue->created_at,
                            'updated_at' => $profileValue->updated_at,
                        ];
                    }),
                ];
            }),
            'access_logs' => $accessLogs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'user_id' => $log->user_id,
                    'context_requested' => $log->context_requested,
                    'accessor_type' => $log->accessor_type,
                    'accessor_id' => $log->accessor_id,
                    'attributes_returned' => $log->attributes_returned,
                    'ip_address' => $log->ip_address,
                    'user_agent' => $log->user_agent,
                    'response_code' => $log->response_code,
                    'created_at' => $log->created_at,
                ];
            }),
            'statistics' => [
                'total_contexts' => $user->contexts->count(),
                'total_profile_values' => $user->contexts->sum(function ($context) {
                    return $context->profileValues->count();
                }),
                'total_access_logs' => $user->accessLogs->count(),
                'account_age_days' => $user->created_at->diffInDays(now()),
            ],
        ];

        return response()->json([
            'message' => 'Data export generated successfully',
            'data' => $exportData,
        ]);
    }

    /**
     * Get access audit log for the user
     */
    public function getAuditLog(Request $request): JsonResponse
    {
        $user = $request->user();

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $logs = AccessLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Transform the data while preserving pagination structure
        $logs->getCollection()->transform(function ($log) {
            return [
                'id' => $log->id,
                'created_at' => $log->created_at,
                'context_requested' => $log->context_requested,
                'accessor_type' => $log->accessor_type,
                'attributes_returned' => $log->attributes_returned,
                'ip_address' => $log->ip_address,
                'accessor_id' => $log->accessor_id,
                'response_code' => $log->response_code,
            ];
        });

        return response()->json([
            'access_logs' => $logs->items(),
            'total_logs' => $logs->total(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
                'last_page' => $logs->lastPage(),
            ],
        ]);
    }

    /**
     * Delete user account and all associated data (Right to Erasure)
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
            'confirmation' => ['required', 'accepted'],
        ], [
            'password.required' => 'Please provide your password to confirm account deletion.',
            'confirmation.accepted' => 'Please confirm that you want to permanently delete your account.',
        ]);

        $user = $request->user();

        // Verify password
        if (! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['The provided password is incorrect.'],
            ]);
        }

        // Count data before deletion for confirmation
        $dataCount = [
            'contexts' => $user->contexts()->count(),
            'profile_values' => $user->contexts()->withCount('profileValues')->get()->sum('profile_values_count'),
            'access_logs' => AccessLog::where('accessed_user_id', $user->id)->count(),
        ];

        // Delete all related data (cascade deletion should handle most of this)
        // But we'll be explicit for GDPR compliance

        // Delete access logs
        AccessLog::where('user_id', $user->id)->delete();

        // Delete profile values for all contexts
        foreach ($user->contexts as $context) {
            $context->profileValues()->delete();
        }

        // Delete contexts
        $user->contexts()->delete();

        // Delete all user tokens
        $user->tokens()->delete();

        // Finally delete the user
        $user->delete();

        return response()->json([
            'message' => 'Account deleted successfully',
            'deleted_data' => $dataCount,
            'deleted_at' => now()->toISOString(),
        ]);
    }

    /**
     * Get GDPR compliance information
     */
    public function getGDPRInfo(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'gdpr_rights' => [
                'right_to_access' => [
                    'description' => 'Access all your personal data',
                    'endpoint' => 'GET /api/export-data',
                    'available' => true,
                ],
                'right_to_portability' => [
                    'description' => 'Export your data in a structured format',
                    'endpoint' => 'GET /api/export-data',
                    'available' => true,
                ],
                'right_to_erasure' => [
                    'description' => 'Delete your account and all associated data',
                    'endpoint' => 'DELETE /api/delete-account',
                    'available' => true,
                ],
                'right_to_rectification' => [
                    'description' => 'Correct or update your personal data',
                    'endpoint' => 'PUT /api/profiles/{id}',
                    'available' => true,
                ],
            ],
            'data_processing' => [
                'purposes' => [
                    'Identity Management',
                    'Profile Context Switching',
                    'Access Control',
                    'Audit Logging',
                ],
                'legal_basis' => 'Consent and Legitimate Interest',
                'retention_period' => 'Until account deletion',
                'data_categories' => [
                    'Identity Data (name, email)',
                    'Profile Attributes (custom user data)',
                    'Context Information',
                    'Access Logs (IP, User Agent, timestamps)',
                ],
            ],
            'user_statistics' => [
                'account_created' => $user->created_at->toISOString(),
                'contexts_count' => $user->contexts()->count(),
                'total_profile_attributes' => $user->contexts()->withCount('profileValues')->get()->sum('profile_values_count'),
                'access_logs_count' => AccessLog::where('user_id', $user->id)->count(),
            ],
        ]);
    }
}
