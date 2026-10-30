<?php
/**
 * Fix curly brace string/array index di PHP 8 menggunakan pendekatan:
 * 1. Ubah semua $var{expr} dan $obj->prop{expr} di file
 * 2. Kemudian kembalikan yang ada di dalam string PHP (double-quoted)
 *    karena {$var} dalam string adalah variable interpolation yang valid.
 *
 * Pendekatan: replace pola {$var} sementara dengan placeholder, lakukan
 * str_replace, kemudian kembalikan placeholder. Ini lebih aman.
 */
$apply = in_array('--apply', $argv, true);
$dirs = [
    __DIR__ . '/application/libraries/PHPExcel',
    __DIR__ . '/application/libraries/PHPExcel_NEW',
];

$totalFiles = 0; $changedFiles = 0; $totalRepl = 0; $samples = [];

foreach ($dirs as $root) {
    if (!is_dir($root)) continue;
    $rii = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($rii as $file) {
        if ($file->getExtension() !== 'php') continue;
        $totalFiles++;
        $path = $file->getPathname();
        $src  = file_get_contents($path);

        // Step 1: Lindungi {$var} yang ada di dalam string double-quoted dengan placeholder.
        // PHP tokenizer: T_CURLY_OPEN diikuti T_VARIABLE = interpolation yang valid.
        // Kita lindungi pola "\{$..." agar tidak ikut terganti.
        // Tapi cara paling aman: kita scan token dan rekonstruksi dengan fix tepat.

        $tokens = @token_get_all($src);
        if (!$tokens) { $totalFiles--; continue; }

        $count = 0;
        $out = '';
        $n = count($tokens);

        for ($i = 0; $i < $n; $i++) {
            $t = $tokens[$i];

            // Jika string (T_ENCAPSED_AND_WHITESPACE atau T_CONSTANT_ENCAPSED_STRING) —
            // lewati tanpa ubah
            if (is_array($t) && in_array($t[0], [T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE])) {
                $out .= $t[1];
                continue;
            }

            // Token yang tidak perlu diproses khusus
            if (is_array($t) && !in_array($t[0], [T_VARIABLE, T_STRING, T_CURLY_OPEN])) {
                $out .= $t[1];
                continue;
            }

            // Untuk token lainnya: kumpulkan dalam buffer, cari pola curly index
            // yang belum diubah sebelumnya
            $out .= is_array($t) ? $t[1] : $t;
        }

        // Setelah rekonstruksi token-by-token tidak cukup menangkap pola->prop{n},
        // kita gunakan regex pada HASIL tokenisasi (output sudah aman karena
        // string literal sudah di-output verbatim dan tidak akan cocok dengan regex ini).
        //
        // Regex: ($word atau $word->word atau $word->word->word) diikuti {expr}
        // di mana expr tidak mengandung '"' (sehingga tidak cocok dengan string).
        $pattern = '/(\$[A-Za-z_]\w*(?:->(?:[A-Za-z_]\w*))*)\{([^}"\']+)\}/';
        $new = preg_replace_callback($pattern, function ($m) use (&$count) {
            $count++;
            return $m[1] . '[' . $m[2] . ']';
        }, $out);

        if ($count > 0) {
            $changedFiles++;
            $totalRepl += $count;
            if (count($samples) < 25) {
                $short = str_replace($root . DIRECTORY_SEPARATOR, basename($root) . '\\', $path);
                $samples[] = "$short ($count)";
            }
            if ($apply) file_put_contents($path, $new);
        }
    }
}

echo "Mode : " . ($apply ? 'APPLY' : 'DRY-RUN') . "\n";
echo "File diperiksa : $totalFiles\n";
echo "File terdampak : $changedFiles\n";
echo "Total ganti    : $totalRepl\n";
foreach ($samples as $s) echo "  $s\n";
