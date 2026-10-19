<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MoveSuppliersMenuToInventory extends Migration
{
    /**
     * The Suppliers CRUD lives with inventory now, so the HR -> Suppliers
     * sidebar link moves to the Inventory section. Rewrite every company's
     * persisted `settings.asidebar` JSON: pull the Suppliers link out of
     * whichever section currently holds it (dropping a section such as HR that
     * becomes empty) and append it to Inventory, creating that section when
     * necessary. New companies are handled by AsideSettingService.
     *
     * @return void
     */
    public function up()
    {
        foreach (DB::table('settings')->get() as $setting) {
            $asidebar = json_decode($setting->asidebar, true);

            if (!is_array($asidebar)) {
                continue;
            }

            $original = $asidebar;
            $supplier = null;
            $sections = [];

            foreach ($asidebar as $section) {
                if (!isset($section['links']) || !is_array($section['links'])) {
                    $sections[] = $section;
                    continue;
                }

                $links = [];

                foreach ($section['links'] as $link) {
                    if ($this->isSupplierLink($link)) {
                        $supplier = $supplier ?: $link;
                        continue;
                    }

                    $links[] = $link;
                }

                if (empty($links)) {
                    // Drop a section (e.g. HR) that only held Suppliers.
                    continue;
                }

                $section['links'] = $links;
                $sections[] = $section;
            }

            // Fall back to the canonical link for companies that never had a
            // Suppliers entry in their stored menu.
            if ($supplier === null) {
                $supplier = [
                    'title' => 'Suppliers',
                    'permission' => 'view supplier',
                    'link' => '/suppliers',
                ];
            }

            $sections = $this->appendToInventory($sections, $supplier);

            if (json_encode($sections) === json_encode($original)) {
                continue;
            }

            DB::table('settings')->where('id', $setting->id)->update([
                'asidebar' => json_encode($sections),
            ]);
        }
    }

    /**
     * Not reversible — the sidebar layout is owned by AsideSettingService, and
     * re-deriving the previous HR placement would be lossy.
     *
     * @return void
     */
    public function down()
    {
        //
    }

    /**
     * A link is the Suppliers entry when its title is "Suppliers" or its path
     * ends with "/suppliers".
     *
     * @param  array  $link
     * @return bool
     */
    protected function isSupplierLink($link)
    {
        if (($link['title'] ?? null) === 'Suppliers') {
            return true;
        }

        if (!isset($link['link'])) {
            return false;
        }

        return Str::endsWith(rtrim($link['link'], '/'), '/suppliers');
    }

    /**
     * Append the Suppliers link to the Inventory section (creating the section
     * when it is missing).
     *
     * @param  array  $sections
     * @param  array  $supplier
     * @return array
     */
    protected function appendToInventory(array $sections, array $supplier)
    {
        foreach ($sections as $index => $section) {
            if (($section['name'] ?? null) !== 'Inventory') {
                continue;
            }

            $sections[$index]['links'][] = $supplier;

            return $sections;
        }

        $sections[] = [
            'name' => 'Inventory',
            'icon' => 'bi bi-shop',
            'links' => [$supplier],
        ];

        return $sections;
    }
}
