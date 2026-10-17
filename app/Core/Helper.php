<?php

namespace App\Core;

use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManagerStatic as Image;


class Helper
{

    public static function _uid()
    {
        return md5(date('Y-m-d') . microtime() . rand());
    }
    public static function normalize_hyphened($string)
    {
        return ucwords(str_replace('-', ' ', $string));
    }

    public static function hyphened($string)
    {
        return strtolower(str_replace(' ', '-', $string));
    }
    public static function snaked($string)
    {
        return str_replace(' ', '_', strtolower($string));
    }

    public static function logo($variant = 'small')
    {
        $directories = [
            'large' => '/assets/media/large/logo/',
            'medium' => '/assets/media/medium/logo/',
            'small' => '/assets/media/small/logo/',
            'icon' => '/assets/media/icons/logo/',
        ];
        $defaults = [
            'large' => '/assets/media/logos/logo-large.jpeg',
            'medium' => '/assets/media/logos/logo.jpeg',
            'small' => '/assets/media/logos/logo.jpeg',
            'icon' => '/assets/media/logos/favicon.jpeg',
        ];

        $user = auth()->user();
        if ($user && $user->company_id) {
            $setting = $user->setting();
            if ($setting && $setting->logo) {
                $fallbacks = array_unique(array_merge(
                    [$variant],
                    ['medium', 'small', 'icon', 'large']
                ));
                foreach ($fallbacks as $v) {
                    if (!isset($directories[$v])) {
                        continue;
                    }
                    if (file_exists(public_path(ltrim($directories[$v] . $setting->logo, '/')))) {
                        return asset($directories[$v] . $setting->logo);
                    }
                }
            }
        }

        return asset($defaults[$variant] ?? $defaults['small']);
    }

    public static function uploadFile($settings)
    {
        $file = $settings['file'] ?? null;
        $width = $settings['width'] ?? null;
        $height = $settings['height'] ?? null;
        $field = $settings['field'] ?? null;

        if (!$file instanceof \Illuminate\Http\UploadedFile || !$file->isValid()) {
            throw new \App\Exceptions\ImageUploadFailedException(
                'The selected file could not be uploaded. Please choose a valid image.',
                $field
            );
        }

        try {
            if (!empty($settings['filename'])) {
                $filename = $settings['filename'];
            } else {
                $filename = round(microtime(true)) . rand(100, 999) . '.' . $file->getClientOriginalExtension();
            }

            $destinationPath = public_path($settings['path']);

            if (!is_dir($destinationPath)) {
                @mkdir($destinationPath, 0775, true);
            }

            $image_complete_path = $destinationPath . $filename;

            if ($width || $height) {
                Image::make($file->getRealPath())->resize($width, $height)->save($image_complete_path);
            } else {
                $file->move($destinationPath, $filename);
            }
        } catch (\Throwable $e) {
            throw new \App\Exceptions\ImageUploadFailedException(
                'The image could not be saved. Please check the storage permissions and try again.',
                $field,
                $e
            );
        }

        return $filename;
    }


    public static function deleteExcept($settings)
    {
        $files = $settings['files'];
        $exceptions = $settings['exceptions'];
        $path = $settings['path'];
        foreach ($files as $file) {
            if (array_search($file, $exceptions, true) === false) {
                $file = public_path($path) . '/' . $file;
                File::delete($file);
            }
        }
    }

    public static function convert_number_to_words($number)
    {

        $hyphen      = '-';
        $conjunction = ' and ';
        $separator   = ', ';
        $negative    = 'negative ';
        $decimal     = ' point ';
        $dictionary  = array(
            0                   => 'zero',
            1                   => 'one',
            2                   => 'two',
            3                   => 'three',
            4                   => 'four',
            5                   => 'five',
            6                   => 'six',
            7                   => 'seven',
            8                   => 'eight',
            9                   => 'nine',
            10                  => 'ten',
            11                  => 'eleven',
            12                  => 'twelve',
            13                  => 'thirteen',
            14                  => 'fourteen',
            15                  => 'fifteen',
            16                  => 'sixteen',
            17                  => 'seventeen',
            18                  => 'eighteen',
            19                  => 'nineteen',
            20                  => 'twenty',
            30                  => 'thirty',
            40                  => 'fourty',
            50                  => 'fifty',
            60                  => 'sixty',
            70                  => 'seventy',
            80                  => 'eighty',
            90                  => 'ninety',
            100                 => 'hundred',
            1000                => 'thousand',
            1000000             => 'million',
            1000000000          => 'billion',
            1000000000000       => 'trillion',
            1000000000000000    => 'quadrillion',
            1000000000000000000 => 'quintillion'
        );

        if (!is_numeric($number)) {
            return false;
        }

        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
            // overflow
            trigger_error(
                'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
                E_USER_WARNING
            );
            return false;
        }

        if ($number < 0) {
            return $negative . Self::convert_number_to_words(abs($number));
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens   = ((int) ($number / 10)) * 10;
                $units  = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds  = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    $string .= $conjunction . Self::convert_number_to_words($remainder);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = Self::convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= Self::convert_number_to_words($remainder);
                }
                break;
        }

        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            $words = array();
            foreach (str_split((string) $fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }

        return $string;
    }
}
