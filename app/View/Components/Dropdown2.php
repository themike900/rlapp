<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Dropdown2 extends Component
{
    public string $name;
    public array $options;
    public $selected;

    /**
     * Create a new component instance.
     *
     * @param string $name
     * @param array $options
     * @param string|null $selected
     */
    public function __construct($name, $options = [], $selected = null)
    {
        $this->name = $name;
        $this->options = $options;
        $this->selected = $selected;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.dropdown2');
    }
}
