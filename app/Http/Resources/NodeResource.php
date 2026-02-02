<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use NumberFormatter;

class NodeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request)
    {
        $timezone = $request->header('Time-Zone', 'UTC');

        // Si viene 'lang' en additional, úsalo; si no, usa Accept-Language
        $lang = $this->additional['lang'] ?? $request->header('Accept-Language', 'en');

        return [
            'id' => $this->id,
            'parent' => $this->parent,
            'title' => $this->numberToWords($this->id, $lang),
            'created_at' => $this->created_at ? $this->created_at->setTimezone($timezone)->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->setTimezone($timezone)->toDateTimeString() : null,
        ];
    }

    private function numberToWords($number, $lang = 'en')
    {
        $formatter = new NumberFormatter($lang === 'es' ? 'es' : ($lang === 'ca' ? 'ca' : 'en'), \NumberFormatter::SPELLOUT);
        return $formatter->format($number);
    }

    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

}
