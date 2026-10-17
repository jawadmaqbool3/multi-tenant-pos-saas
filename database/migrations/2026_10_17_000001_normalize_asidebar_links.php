<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class NormalizeAsidebarLinks extends Migration
{
    /**
     * Normalise every persisted `settings.asidebar`:
     *
     *  1. Reduce absolute links (they are currently baked to a dev tunnel host)
     *     to site-relative paths so navigation works on any domain.
     *  2. Disambiguate the two entries that were both titled "Accounts".
     *  3. Ensure voucher / voucher-type management is reachable, by appending
     *     those links to the Bank section when it is missing them.
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

            $changed = false;

            foreach ($asidebar as &$section) {
                if (!isset($section['links']) || !is_array($section['links'])) {
                    continue;
                }

                foreach ($section['links'] as &$link) {
                    if (!empty($link['link'])) {
                        $relative = $this->relativeUrl($link['link']);

                        if ($relative !== $link['link']) {
                            $link['link'] = $relative;
                            $changed = true;
                        }
                    }

                    if (($link['title'] ?? '') === 'Accounts') {
                        $link['title'] = ($link['permission'] ?? '') === 'account settings'
                            ? 'Account Settings'
                            : 'Chart of Accounts';
                        $changed = true;
                    }
                }
                unset($link);
            }
            unset($section);

            if ($this->appendVoucherLinks($asidebar)) {
                $changed = true;
            }

            if ($changed) {
                DB::table('settings')->where('id', $setting->id)->update([
                    'asidebar' => json_encode($asidebar),
                ]);
            }
        }
    }

    /**
     * Relative links remain valid on every host, so there is nothing to undo.
     *
     * @return void
     */
    public function down()
    {
        //
    }

    /**
     * @param  string  $url
     * @return string
     */
    protected function relativeUrl($url)
    {
        $path = parse_url($url, PHP_URL_PATH);

        if (!is_string($path) || $path === '') {
            return $url;
        }

        if ($path[0] !== '/') {
            $path = '/' . $path;
        }

        $query = parse_url($url, PHP_URL_QUERY);

        return $path . ($query ? '?' . $query : '');
    }

    /**
     * Append Vouchers / Voucher Types to the Bank section (creating the section
     * when necessary) if they are not already present.
     *
     * @param  array  $asidebar
     * @return bool  true when $asidebar was modified
     */
    protected function appendVoucherLinks(array &$asidebar)
    {
        $bank = null;
        $bankIndex = null;

        foreach ($asidebar as $index => $section) {
            if (($section['name'] ?? null) === 'Bank') {
                $bank = $section;
                $bankIndex = $index;
                break;
            }
        }

        if ($bank === null) {
            $bank = [
                'name' => 'Bank',
                'icon' => 'bi bi-bank',
                'links' => [],
            ];
        }

        $existing = [];
        foreach (($bank['links'] ?? []) as $link) {
            $path = rtrim((string) parse_url($link['link'] ?? '', PHP_URL_PATH), '/');

            if ($path !== '') {
                $existing[] = $path;
            }
        }

        $wanted = [
            'vouchers' => ['title' => 'Vouchers', 'permission' => 'view voucher'],
            'voucher-types' => ['title' => 'Voucher Types', 'permission' => 'view voucher type'],
        ];

        $added = false;

        foreach ($wanted as $slug => $meta) {
            if (in_array('/' . $slug, $existing, true)) {
                continue;
            }

            $bank['links'][] = [
                'title' => $meta['title'],
                'permission' => $meta['permission'],
                'link' => '/' . $slug,
            ];
            $added = true;
        }

        if (!$added) {
            return false;
        }

        if ($bankIndex === null) {
            $asidebar[] = $bank;
        } else {
            $asidebar[$bankIndex] = $bank;
        }

        return true;
    }
}
