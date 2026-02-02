<?php

namespace Database\Seeders;

use App\Models\Node;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use NumberFormatter;

class NodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Node::truncate();

        $nodes = [];

       
        for ($i = 1; $i <= 5; $i++) {
            $nodes[] = Node::create([
                'title' => $this->numberToWords($i, 'en'),
                'parent' => null,
            ]);
        }

        
        for ($i = 6; $i <= 50; $i++) {

            
            $parent = collect($nodes)->random();

            $nodes[] = Node::create([
                'title' => "Node {$i}",
                'parent' => $parent->id,
            ]);
        }
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
