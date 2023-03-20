<?php

namespace App\View\Components;

use Illuminate\View\Component;

class MediaUploader extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    private $title;
    private $images;
    private $tagLine;
    private $directory;
    public function __construct($title, $images, $tagLine, $directory)
    {
        $this->title = $title;
        $this->images = $images;
        $this->tagLine = $tagLine;
        $this->directory  = $directory;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $title =  $this->title;
        $images =  $this->images;
        $tagLine =  $this->tagLine;
        $directory =  $this->directory;
        return view('components.media-uploader', compact('title', 'images', 'tagLine', 'directory'));
    }
}
