<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AddMySubscriptionMenuLink extends Migration
{
    /**
     * Adds the tenant-facing "My Subscription" link (backed by the
     * `manage company subscription` permission) to every setting's Billing
     * section, creating the section when absent. Existing companies and the
     * platform setting are both handled here; new companies are covered by
     * AsideSettingService.
     *
     * @return void
     */
    public function up()
    {
        $title = 'My Subscription';
        $permission = 'manage company subscription';

        // Tenant-only link: the platform (company_id = null) setting keeps just
        // the platform billing surfaces.
        foreach (DB::table('settings')->whereNotNull('company_id')->get() as $setting) {
            $asidebar = json_decode($setting->asidebar, true);

            if (!is_array($asidebar)) {
                continue;
            }

            $host = $this->hostFor($asidebar);
            $link = [
                'link' => $host . '/subscription',
                'title' => $title,
                'permission' => $permission,
            ];

            $found = false;

            foreach ($asidebar as $i => $section) {
                if (($section['name'] ?? null) !== 'Billing') {
                    continue;
                }

                $found = true;
                $titles = array_map(fn ($l) => $l['title'] ?? null, $section['links'] ?? []);

                if (!in_array($title, $titles, true)) {
                    $asidebar[$i]['links'][] = $link;
                }
            }

            if (!$found) {
                $asidebar[] = [
                    'name' => 'Billing',
                    'icon' => 'bi bi-credit-card',
                    'links' => [
                        $link,
                    ],
                ];
            }

            if (json_encode($asidebar) === $setting->asidebar) {
                continue;
            }

            DB::table('settings')->where('id', $setting->id)->update([
                'asidebar' => json_encode($asidebar),
            ]);
        }
    }

    /**
     * Derive the site base URL from the first absolute link in the menu so a
     * tunnel host (ngrok) is preserved instead of being reset to localhost.
     *
     * @param  array  $asidebar
     * @return string
     */
    protected function hostFor(array $asidebar): string
    {
        foreach ($asidebar as $section) {
            foreach ($section['links'] ?? [] as $link) {
                $url = $link['link'] ?? '';
                $parts = parse_url($url);

                if (!empty($parts['scheme']) && !empty($parts['host'])) {
                    return $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
                }
            }
        }

        return rtrim(config('app.url'), '/');
    }

    /**
     * Not reversible — the sidebar layout is owned by AsideSettingService.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
