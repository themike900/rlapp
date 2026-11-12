<?php

namespace Tests\Unit;

use App\Support\SafeTemplate;
use Tests\TestCase;

class SafeTemplateTest extends TestCase
{
    public function test_replaces_simple_placeholders_and_leaves_php_as_text(): void
    {
        $out = SafeTemplate::render(
            'Hallo {{ $firstname }}, {{$action_name}} {!! $captain !!} @php echo "PWN"; @endphp {{ system("id") }}',
            [
                'firstname' => 'Anna <b>',
                'action_name' => 'Fahrt & mehr',
                'captain' => 'Kai',
            ]
        );

        $this->assertSame(
            'Hallo Anna &lt;b&gt;, Fahrt &amp; mehr Kai @php echo "PWN"; @endphp {{ system("id") }}',
            $out
        );
    }

    public function test_keeps_unknown_placeholders(): void
    {
        $this->assertSame(
            'Wert {{ $unbekannt }}',
            SafeTemplate::render('Wert {{ $unbekannt }}', ['firstname' => 'Anna'])
        );
    }
}
