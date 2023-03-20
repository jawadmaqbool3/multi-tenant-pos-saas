<?php

namespace App\View\Components;

use App\Models\Setting;
use Illuminate\View\Component;

class AsideMenuLinks extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public $links;

    public function __construct()
    {
        $setting = Setting::select('asidebar')->first();
        $links =  [
            [
                'title' => 'Pages',
                'categories' => json_decode($setting->asidebar, true)
            ],
        ];
        $this->links = $this->filterLinks($links);
    }

    public function filterLinks($links)
    {
        foreach ($links as $x => $menu) {
            foreach ($menu['categories']  as $y => $category) {
                foreach ($category['links']  as $z => $link) {
                    if (auth()->user()->hasPermission($link['permission']) == false) {
                        unset($links[$x]['categories'][$y]['links'][$z]);
                    }
                }
                if (count($links[$x]['categories'][$y]['links']) == 0) {
                    unset($links[$x]['categories'][$y]);
                }
            }
            if (count($links[$x]['categories']) == 0) {
                unset($links[$x]);
            }
        }
        if (count($links) == 0) {
            $links = [];
        }
        return $links;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.aside-menu-links');
    }
}
