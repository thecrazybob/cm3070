<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateContextRequest;
use App\Http\Requests\UpdateContextRequest;
use App\Http\Requests\CreateProfileAttributeRequest;
use App\Http\Requests\UpdateProfileAttributeRequest;
use App\Models\Context;
use App\Models\ContextProfileValue;
use App\Models\ProfileAttribute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContextController extends Controller
{
    /**
     * Get all contexts for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $contexts = $request->user()->contexts()
            ->with('profileValues.attribute')
            ->get()
            ->map(function ($context) {
                return [
                    'id' => $context->id,
                    'slug' => $context->slug,
                    'name' => $context->name,
                    'description' => $context->description,
                    'is_default' => $context->is_default,
                    'is_active' => $context->is_active,
                    'attributes_count' => $context->profileValues->count(),
                    'created_at' => $context->created_at,
                    'updated_at' => $context->updated_at,
                ];
            });

        return response()->json([
            'contexts' => $contexts,
        ]);
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
        $context = Context::find($contextId);
        
        // Check if context exists and user owns it
        if (!$context || $context->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Context not found'], 404);
        }

        $context->load('profileValues.attribute');

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
        $context = Context::find($contextId);
        
        // Check if context exists and user owns it
        if (!$context || $context->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Context not found'], 404);
        }

        // Ensure only one default context
        if ($request->is_default && !$context->is_default) {
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
        $context = Context::find($contextId);
        
        // Check if context exists and user owns it
        if (!$context || $context->user_id !== $request->user()->id) {
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
        $context = Context::find($contextId);
        
        // Check if context exists and user owns it
        if (!$context || $context->user_id !== $request->user()->id) {
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
        $context = Context::find($contextId);
        
        // Check if context exists and user owns it
        if (!$context || $context->user_id !== $request->user()->id) {
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
        $context = Context::find($contextId);
        $profileValue = ContextProfileValue::find($attributeId);
        
        // Check if context and profile value exist and user owns them
        if (!$context || !$profileValue || 
            $context->user_id !== $request->user()->id || 
            $profileValue->user_id !== $request->user()->id) {
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
        $context = Context::find($contextId);
        $profileValue = ContextProfileValue::find($attributeId);
        
        // Check if context and profile value exist and user owns them
        if (!$context || !$profileValue || 
            $context->user_id !== $request->user()->id || 
            $profileValue->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Attribute not found'], 404);
        }

        $profileValue->delete();

        return response()->json([
            'message' => 'Attribute deleted successfully',
        ]);
    }
}