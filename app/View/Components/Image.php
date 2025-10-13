<?php
namespace App\View\Components;

use Illuminate\View\Component;

class Image extends Component
{
    public $path;
    public $alt;
    public $sizes;

    public function __construct($path, $alt = '', $sizes = null)
    {
        $this->path = $path;
        $this->alt = $alt;
        $this->sizes = $sizes;
    }

    public function render()
    {
        return view('components.image');
    }
}
