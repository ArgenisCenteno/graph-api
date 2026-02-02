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

#[OA\Info(title: "Graph API", version: "1.0.0")]
#[OA\Schema(
    schema: "Node",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "integer"),
        new OA\Property(property: "title", type: "string")
    ]
)]
class NodeController extends Controller
{


    #[OA\Get(
        path: "/api/v1/nodes",
        summary: "Lista nodos raíz",
        tags: ["Nodes"],
        parameters: [
            new OA\Parameter(
                name: "depth",
                in: "query",
                description: "Profundidad de hijos",
                required: false,
                schema: new OA\Schema(type: "integer", default: 1)
            ),
            new OA\Parameter(
                name: "Accept-Language",
                in: "header",
                description: "Idioma (en, es o ca)",
                required: false,
                schema: new OA\Schema(type: "string", default: "en")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de nodos",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(ref: "#/components/schemas/Node")
                )
            )
        ]
    )]

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

    #[OA\Post(
        path: "/api/v1/nodes",
        operationId: "storeNode",
        tags: ["Nodes"],
        summary: "Crear un nodo",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "parent", type: "integer", nullable: true)
                ]
            )
        ),
        parameters: [
            new OA\Parameter(
                name: "Accept-Language",
                in: "header",
                description: "Idioma (en, es o ca)",
                required: false,
                schema: new OA\Schema(type: "string", default: "en")
            )
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: "Nodo creado",
                content: new OA\JsonContent(ref: "#/components/schemas/Node")
            ),
            new OA\Response(
                response: 422,
                description: "Datos inválidos"
            )
        ]
    )]

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
    #[OA\Get(
        path: "/api/v1/nodes/{id}",
        operationId: "showNode",
        tags: ["Nodes"],
        summary: "Muestra un nodo por id",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID del nodo",
                schema: new OA\Schema(type: "integer")
            ),
            new OA\Parameter(
                name: "depth",
                in: "query",
                required: false,
                description: "Profundidad de hijos",
                schema: new OA\Schema(type: "integer", default: 1)
            ),
            new OA\Parameter(
                name: "Accept-Language",
                in: "header",
                required: false,
                description: "Idioma (en o ca)",
                schema: new OA\Schema(type: "string", default: "en")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Nodo encontrado",
                content: new OA\JsonContent(ref: "#/components/schemas/Node")
            ),
            new OA\Response(response: 404, description: "Nodo no encontrado")
        ]
    )]

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
    #[OA\Delete(
        path: "/api/v1/nodes/{id}",
        operationId: "deleteNode",
        tags: ["Nodes"],
        summary: "Elimina un nodo",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            ),
            new OA\Parameter(
                name: "Accept-Language",
                in: "header",
                required: false,
                schema: new OA\Schema(type: "string", default: "en")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Nodo eliminado",
                content: new OA\JsonContent(ref: "#/components/schemas/Node")
            ),
            new OA\Response(response: 404, description: "Nodo no encontrado"),
            new OA\Response(response: 400, description: "No se puede eliminar nodo con hijos")
        ]
    )]
    public function destroy(Request $request, string $id): JsonResponse
    {
        $node = Node::with('children')->find($id);
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
