<?php
/**
 * Dua tugas:
 * 1. RESTORE: kembalikan [$var['key']] dalam string double-quoted yang
 *    salah diubah dari {$var['key']} oleh script sebelumnya.
 *    Pattern: dalam konteks string PHP, pola "[$token['data']]" seharusnya "{$token['data']}"
 *
 * 2. FIX BARU: ganti $obj->prop{n} dan $var{n} yang masih tersisa
 *    (yang tidak tertangkap regex sebelumnya karena ada ->)
 *    dengan $obj->prop[n] dan $var[n]
 *
 * Menggunakan token_get_all() untuk keamanan.
 */
$apply = in_array('--apply', $argv, true);
$dirs = [
    __DIR__ . '/application/libraries/PHPExcel',
    __DIR__ . '/application/libraries/PHPExcel_NEW',
];

$totalFiles = 0; $changedFiles = 0;
$totalRestore = 0; $totalFix = 0;
$samples = [];

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
        $tokens = @token_get_all($src);
        if (!$tokens) continue;

        $countRestore = 0;
        $countFix = 0;
        $out = '';
        $n = count($tokens);

        for ($i = 0; $i < $n; $i++) {
            $t = $tokens[$i];

            // =====================================================================
            // CASE 1: String double-quoted yang mengandung [$var['key']] yang salah
            // Token: T_DOUBLE_QUOTE_STRING_PART (T_ENCAPSED_AND_WHITESPACE) atau
            //        string yang dibungkus dalam " ... " block
            // =====================================================================
            if (is_array($t) && $t[0] === T_ENCAPSED_AND_WHITESPACE) {
                // Dalam string interpolation, cari pola [$var['...']] atau [$var["..."]]
                // yang seharusnya {$var['...']}
                $fixed = preg_replace_callback(
                    '/\[(\$[A-Za-z_]\w*(?:\[\'[^\']+\'\]|\["[^"]+"\]|\[\$\w+\])*)\]/',
                    function ($m) use (&$countRestore) {
                        // Hanya ganti jika ada nested array access (tanda bahwa ini variable interpolation)
                        if (strpos($m[1], '[') !== false) {
                            $countRestore++;
                            return '{' . $m[1] . '}';
                        }
                        return $m[0];
                    },
                    $t[1]
                );
                $out .= $fixed;
                continue;
            }

            // =====================================================================
            // CASE 2: Di luar string — cari pola curly brace index yang belum difix
            // $obj->prop{n}, $this->formula{$index}, dll
            // =====================================================================
            // Pola yang dicari: T_VARIABLE diikuti (opsional: T_OBJECT_OPERATOR T_STRING)*
            // lalu '{' lalu expr lalu '}'
            if (is_array($t) && $t[0] === T_VARIABLE) {
                // Kumpulkan chain $var->prop->prop...
                $buf = $t[1];
                $j = $i + 1;
                // Skip whitespace
                while ($j < $n && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) {
                    $buf .= $tokens[$j][1]; $j++;
                }
                // Loop -> prop
                while ($j < $n) {
                    $tw = $tokens[$j];
                    if (is_array($tw) && $tw[0] === T_OBJECT_OPERATOR) {
                        $buf .= $tw[1]; $j++;
                        while ($j < $n && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) { $buf .= $tokens[$j][1]; $j++; }
                        if ($j < $n && is_array($tokens[$j]) && in_array($tokens[$j][0], [T_STRING, T_NAME_QUALIFIED])) {
                            $buf .= $tokens[$j][1]; $j++;
                        } else break;
                    } else break;
                }
                // Skip whitespace before {
                $ws = '';
                while ($j < $n && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) { $ws .= $tokens[$j][1]; $j++; }
                // Cek apakah berikutnya adalah '{' (bukan T_CURLY_OPEN)
                if ($j < $n && !is_array($tokens[$j]) && $tokens[$j] === '{') {
                    // Kumpulkan isi {}
                    $k = $j + 1;
                    $inner = ''; $depth = 1;
                    while ($k < $n && $depth > 0) {
                        $tk = $tokens[$k];
                        $s = is_array($tk) ? $tk[1] : $tk;
                        if ($s === '{') $depth++;
                        elseif ($s === '}') { $depth--; if ($depth == 0) { $k++; break; } }
                        $inner .= $s;
                        $k++;
                    }
                    $out .= $buf . $ws . '[' . $inner . ']';
                    $countFix++;
                    $i = $k - 1;
                    continue;
                } else {
                    // Bukan pola curly index, output apa adanya
                    $out .= $buf . $ws;
                    $i = $j - 1; // -1 karena loop akan $i++
                    continue;
                }
            }

            $out .= is_array($t) ? $t[1] : $t;
        }

        if ($countRestore > 0 || $countFix > 0) {
            $changedFiles++;
            $totalRestore += $countRestore;
            $totalFix += $countFix;
            if (count($samples) < 30) {
                $short = str_replace($root, basename($root), $path);
                $samples[] = "$short (restore:$countRestore fix:$countFix)";
            }
            if ($apply) file_put_contents($path, $out);
        }
    }
}

echo "Mode        : " . ($apply ? 'APPLY' : 'DRY-RUN') . "\n";
echo "File periksa: $totalFiles\n";
echo "File ubah   : $changedFiles\n";
echo "Restore (interp salah ubah): $totalRestore\n";
echo "Fix baru (obj->prop curly) : $totalFix\n";
foreach ($samples as $s) echo "  $s\n";
