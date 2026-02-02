#[OA\Info(title: "Graph API", version: "1.0.0")]
#[OA\Schema(
    schema: "Node",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "integer"),
        new OA\Property(property: "title", type: "string")
    ]
)]



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