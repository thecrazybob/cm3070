<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateContextRequest;
use App\Http\Requests\CreateProfileAttributeRequest;
use App\Http\Requests\UpdateContextRequest;
use App\Http\Requests\UpdateProfileAttributeRequest;
use App\Models\Context;
use App\Models\ContextProfileValue;
use App\Models\ProfileAttribute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContextController extends Controller
{
    /**
     * Get all contexts for the authenticated user with pagination
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 10);
        
        $contexts = $request->user()->contexts()
            ->withCount('profileValues')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
        
        // Transform the data while preserving pagination structure
        $contexts->getCollection()->transform(function ($context) {
            return [
                'id' => $context->id,
                'slug' => $context->slug,
                'name' => $context->name,
                'description' => $context->description,
                'is_default' => $context->is_default,
                'is_active' => $context->is_active,
                'attributes_count' => $context->profile_values_count,
                'created_at' => $context->created_at,
                'updated_at' => $context->updated_at,
            ];
        });
        
        return response()->json($contexts);
    }

    /**
     * Create a new context for the authenticated user
     */
    public function store(CreateContextRequest $request): JsonResponse
    {
        // Ensure only one default context
        if ($request->is_default) {
            $request->user()->contexts()->update(['is_default' => false]);
        }

        $context = $request->user()->contexts()->create([
            'slug' => $request->slug,
            'name' => $request->name,
            'description' => $request->description,
            'is_default' => $request->is_default ?? false,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'message' => 'Context created successfully',
            'context' => [
                'id' => $context->id,
                'slug' => $context->slug,
                'name' => $context->name,
                'description' => $context->description,
                'is_default' => $context->is_default,
                'is_active' => $context->is_active,
            ],
        ], 201);
    }

    /**
     * Get a specific context with its attributes
     */
    public function show(Request $request, int $contextId): JsonResponse
    {
        $context = Context::with('profileValues.attribute')
            ->where('id', $contextId)
            ->where('user_id', $request->user()->id)
            ->first();

        // Check if context exists
        if (! $context) {
            return response()->json(['message' => 'Context not found'], 404);
        }

        $attributes = $context->profileValues->map(function ($profileValue) {
            return [
                'id' => $profileValue->id,
                'attribute' => [
                    'id' => $profileValue->attribute->id,
                    'key_name' => $profileValue->attribute->key_name,
                    'display_name' => $profileValue->attribute->display_name,
                    'data_type' => $profileValue->attribute->data_type,
                ],
                'value' => $profileValue->value,
                'visibility' => $profileValue->visibility,
            ];
        });

        return response()->json([
            'context' => [
                'id' => $context->id,
                'slug' => $context->slug,
                'name' => $context->name,
                'description' => $context->description,
                'is_default' => $context->is_default,
                'is_active' => $context->is_active,
                'attributes' => $attributes,
            ],
        ]);
    }

    /**
     * Update a context
     */
    public function update(UpdateContextRequest $request, int $contextId): JsonResponse
    {
        $context = Context::where('id', $contextId)
            ->where('user_id', $request->user()->id)
            ->first();

        // Check if context exists
        if (! $context) {
            return response()->json(['message' => 'Context not found'], 404);
        }

        // Ensure only one default context
        if ($request->is_default && ! $context->is_default) {
            $request->user()->contexts()->where('id', '!=', $context->id)->update(['is_default' => false]);
        }

        $context->update($request->validated());

        return response()->json([
            'message' => 'Context updated successfully',
            'context' => [
                'id' => $context->id,
                'slug' => $context->slug,
                'name' => $context->name,
                'description' => $context->description,
                'is_default' => $context->is_default,
                'is_active' => $context->is_active,
            ],
        ]);
    }

    /**
     * Delete a context
     */
    public function destroy(Request $request, int $contextId): JsonResponse
    {
        $context = Context::where('id', $contextId)
            ->where('user_id', $request->user()->id)
            ->first();

        // Check if context exists
        if (! $context) {
            return response()->json(['message' => 'Context not found'], 404);
        }

        // Prevent deletion of default context
        if ($context->is_default) {
            return response()->json([
                'message' => 'Cannot delete default context',
            ], 422);
        }

        $context->delete();

        return response()->json([
            'message' => 'Context deleted successfully',
        ]);
    }

    /**
     * Get attributes for a specific context
     */
    public function getAttributes(Request $request, int $contextId): JsonResponse
    {
        $context = Context::where('id', $contextId)
            ->where('user_id', $request->user()->id)
            ->first();

        // Check if context exists
        if (! $context) {
            return response()->json(['message' => 'Context not found'], 404);
        }

        $attributes = $context->profileValues()
            ->with('attribute')
            ->get()
            ->map(function ($profileValue) {
                return [
                    'id' => $profileValue->id,
                    'attribute' => [
                        'id' => $profileValue->attribute->id,
                        'key_name' => $profileValue->attribute->key_name,
                        'display_name' => $profileValue->attribute->display_name,
                        'data_type' => $profileValue->attribute->data_type,
                    ],
                    'value' => $profileValue->value,
                    'visibility' => $profileValue->visibility,
                ];
            });

        return response()->json([
            'attributes' => $attributes,
        ]);
    }

    /**
     * Add an attribute to a context
     */
    public function storeAttribute(CreateProfileAttributeRequest $request, int $contextId): JsonResponse
    {
        $context = Context::where('id', $contextId)
            ->where('user_id', $request->user()->id)
            ->first();

        // Check if context exists
        if (! $context) {
            return response()->json(['message' => 'Context not found'], 404);
        }

        // Get or create the profile attribute
        $attribute = ProfileAttribute::firstOrCreate([
            'key_name' => $request->key_name,
        ], [
            'display_name' => $request->display_name ?? $request->key_name,
            'data_type' => $request->data_type ?? 'string',
            'is_system' => false,
        ]);

        // Check if this attribute already exists for this context
        $existingProfileValue = ContextProfileValue::where([
            'user_id' => $request->user()->id,
            'context_id' => $context->id,
            'attribute_id' => $attribute->id,
        ])->first();

        if ($existingProfileValue) {
            return response()->json([
                'message' => 'Attribute already exists for this context',
            ], 422);
        }

        // Create the profile value
        $profileValue = ContextProfileValue::create([
            'user_id' => $request->user()->id,
            'context_id' => $context->id,
            'attribute_id' => $attribute->id,
            'value' => $request->value,
            'visibility' => $request->visibility ?? 'private',
        ]);

        return response()->json([
            'message' => 'Attribute added successfully',
            'attribute' => [
                'id' => $profileValue->id,
                'attribute' => [
                    'id' => $attribute->id,
                    'key_name' => $attribute->key_name,
                    'display_name' => $attribute->display_name,
                    'data_type' => $attribute->data_type,
                ],
                'value' => $profileValue->value,
                'visibility' => $profileValue->visibility,
            ],
        ], 201);
    }

    /**
     * Update an attribute value in a context
     */
    public function updateAttribute(UpdateProfileAttributeRequest $request, int $contextId, int $attributeId): JsonResponse
    {
        $context = Context::where('id', $contextId)
            ->where('user_id', $request->user()->id)
            ->first();
        
        if (! $context) {
            return response()->json(['message' => 'Context not found'], 404);
        }
        
        $profileValue = ContextProfileValue::where('id', $attributeId)
            ->where('user_id', $request->user()->id)
            ->where('context_id', $contextId)
            ->first();

        if (! $profileValue) {
            return response()->json(['message' => 'Attribute not found'], 404);
        }

        $profileValue->update($request->validated());

        return response()->json([
            'message' => 'Attribute updated successfully',
            'attribute' => [
                'id' => $profileValue->id,
                'value' => $profileValue->value,
                'visibility' => $profileValue->visibility,
            ],
        ]);
    }

    /**
     * Delete an attribute from a context
     */
    public function destroyAttribute(Request $request, int $contextId, int $attributeId): JsonResponse
    {
        $context = Context::where('id', $contextId)
            ->where('user_id', $request->user()->id)
            ->first();
        
        if (! $context) {
            return response()->json(['message' => 'Context not found'], 404);
        }
        
        $profileValue = ContextProfileValue::where('id', $attributeId)
            ->where('user_id', $request->user()->id)
            ->where('context_id', $contextId)
            ->first();

        if (! $profileValue) {
            return response()->json(['message' => 'Attribute not found'], 404);
        }

        $profileValue->delete();

        return response()->json([
            'message' => 'Attribute deleted successfully',
        ]);
    }

    /**
     * Set a context as the default context for the user
     */
    public function setDefault(Request $request, int $contextId): JsonResponse
    {
        $context = Context::where('id', $contextId)
            ->where('user_id', $request->user()->id)
            ->first();
        
        if (! $context) {
            return response()->json(['message' => 'Context not found'], 404);
        }

        // Remove default flag from all other contexts
        Context::where('user_id', $request->user()->id)
            ->where('id', '!=', $contextId)
            ->update(['is_default' => false]);

        // Set this context as default
        $context->is_default = true;
        $context->save();

        return response()->json([
            'message' => 'Default context updated successfully',
            'context' => [
                'id' => $context->id,
                'name' => $context->name,
                'slug' => $context->slug,
                'is_default' => true,
            ],
        ]);
    }
}
