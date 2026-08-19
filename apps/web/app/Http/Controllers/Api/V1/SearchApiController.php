<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchApiController extends Controller
{
    public function __construct(
        protected SearchService $searchService
    ) {}

    /**
     * Search endpoint: GET /api/v1/search
     */
    public function search(Request $request): JsonResponse
    {
        $params = $request->validate([
            'q' => 'required|string|max:500',
            'category' => 'nullable|string|in:all,images,news,videos,tech,code',
            'page' => 'nullable|integer|min:1',
            'perPage' => 'nullable|integer|min:1|max:100',
            'lang' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:10',
            'safeSearch' => 'nullable|string|in:strict,moderate,off',
        ]);

        $results = $this->searchService->search($params);

        return response()->json($results);
    }

    /**
     * Suggest endpoint: GET /api/v1/suggest
     */
    public function suggest(Request $request): JsonResponse
    {
        $q = $request->input('q', '');
        $suggestions = $this->searchService->suggest($q);

        return response()->json([
            'query' => $q,
            'suggestions' => $suggestions,
        ]);
    }
}
