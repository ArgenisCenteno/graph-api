<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use NumberFormatter;

class NodeDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $lang = $request->header('Accept-Language', 'en');
        $timezone = $request->header('Time-Zone', 'UTC');
       
        return [
            'id' => $this->id,
            'parent' => $this->parent,
            'title' => $this->numberToWords($this->id, $lang),
            'created_at' => $this->created_at?->setTimezone($timezone)->toDateTimeString(),
            'updated_at' => $this->updated_at?->setTimezone($timezone)->toDateTimeString(),
 
            'children' => $this->whenLoaded('children', function () use ($request) {
                return NodeDetailResource::collection(
                    $this->children
                )->additional([
                            'meta' => [
                                'lang' => $request->header('Accept-Language', 'en'),
                                'timezone' => $request->header('Time-Zone', 'UTC'),
                            ],
                        ]);
            }),

        ];
    }

    private function numberToWords($number, $lang = 'en')
    {
        $formatter = new NumberFormatter(
            $lang === 'es' ? 'es' : ($lang === 'ca' ? 'ca' : 'en'),
            NumberFormatter::SPELLOUT
        );
       
        return $formatter->format($number);
    }
}