<?php

namespace App\Http\Controllers;

use App\Http\Resources\NodeDetailResource;
use App\Http\Resources\NodeResource;
use App\Models\Node;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use NumberFormatter;
use OpenApi\Attributes as OA;


class NodeController extends Controller
{


    public function index(Request $request)
    {
        $depth = (int) $request->query('depth', 1);

        $query = Node::whereNull('parent');


        $query->with(
            $this->buildDepth('children', $depth)
        );


        $nodes = $query->get();

        return NodeDetailResource::collection($nodes)
            ->response()
            ->setStatusCode(200);
    }

  
    public function store(Request $request): JsonResponse
    {
        $lang = $request->header('Accept-Language', 'en');

        if (!isset($this->translations[$lang])) {
            $lang = 'en';
        }
        $timezone = $request->header('Time-Zone', 'UTC');

        $validator = Validator::make($request->all(), [
            'parent' => 'nullable|exists:nodes,id',
        ], [
            'parent.exists' => $this->translations[$lang]['parent_exists'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $this->translations[$lang]['invalid_data'],
                'errors' => $validator->errors()
            ], 422);
        }

        $node = new Node();
        $node->parent = $request->parent ?? null;
        $node->save();

        $node->title = $this->numberToWords($node->id, 'en');
        $node->created_at = $node->created_at->setTimezone($timezone)->toDateTimeString();
        $node->updated_at = $node->updated_at->setTimezone($timezone)->toDateTimeString();
        $node->save();

        return (new NodeResource($node))
            ->additional([
                'message' => $this->translations[$lang]['node_created']
            ])
            ->response()
            ->setStatusCode(201);
    }
   
    public function show(Request $request, string $id): JsonResponse
    {
        $depth = (int) $request->query('depth', 1);
        $lang = $request->header('Accept-Language', 'en');

        if (!isset($this->translations[$lang])) {
            $lang = 'en';
        }

        $query = Node::where('id', $id);

        if ($depth > 0) {
            $query->with($this->buildDepth('children', $depth));
        }

        $node = $query->first();

        if (!$node) {
            return response()->json([
                'message' => $this->translations[$lang]['node_not_found']
            ], 404);
        }


        return (new NodeDetailResource($node))

            ->response()
            ->setStatusCode(200);
    }
   
    public function destroy(Request $request, string $id): JsonResponse
    {
        $node = Node::with('children')->find($id);
       // dd($node);
        $lang = $request->header('Accept-Language', 'en');
        if (!isset($this->translations[$lang])) {
            $lang = 'en';
        }
        if (!$node) {
            return response()->json(['message' => $this->translations[$lang]['node_not_found']], 404);
        }

        if ($node->children()->count() > 0) {
            return response()->json([
                'message' => $this->translations[$lang]['node_has_children']
            ], 400);
        }

        $node->delete();

        return response()->json([
            'message' => $this->translations[$lang]['node_deleted']
        ]);
    }

    private $translations = [
        'es' => [
            'invalid_data' => 'Datos inválidos',
            'node_created' => 'Nodo creado exitosamente',
            'node_has_children' => 'No se puede eliminar nodo con hijos',
            'title_required' => 'El campo título es obligatorio',
            'title_string' => 'El campo título debe ser un texto',
            'title_max' => 'El campo título no puede tener más de 255 caracteres',
            'parent_exists' => 'El nodo padre no existe',
            'node_not_found' => 'Nodo no encontrado',
            'node_deleted' => 'Nodo eliminado exitosamente',
        ],
        'en' => [
            'invalid_data' => 'Invalid data',
            'node_created' => 'Node created successfully',
            'node_has_children' => 'Cannot delete node with children',
            'title_required' => 'The title field is required',
            'title_string' => 'The title field must be a string',
            'title_max' => 'The title field may not be greater than 255 characters',
            'parent_exists' => 'The parent node does not exist',
            'node_not_found' => 'Node not found',
            'node_deleted' => 'Node deleted successfully',
        ],
        'ca' => [
            'invalid_data' => 'Dades invàlides',
            'node_created' => 'Node creat correctament',
            'node_has_children' => 'No es pot eliminar el node amb fills',
            'title_required' => 'El camp títol és obligatori',
            'title_string' => 'El camp títol ha de ser un text',
            'title_max' => 'El camp títol no pot tenir més de 255 caràcters',
            'parent_exists' => 'El node pare no existeix',
            'node_not_found' => 'Node no trobat',
            'node_deleted' => 'Node eliminat correctament',
        ]
    ];

    private function numberToWords($number, $lang = 'en')
    {
        $formatter = new NumberFormatter($lang === 'es' ? 'es' : ($lang === 'ca' ? 'ca' : 'en'), NumberFormatter::SPELLOUT);
        return $formatter->format($number);
    }

    private function buildDepth(string $relation, int $depth): array
    {
        if ($depth === 1) {
            return [$relation];
        }

        return [
            $relation => function ($q) use ($relation, $depth) {
                $q->with($this->buildDepth($relation, $depth - 1));
            }
        ];
    }

}
