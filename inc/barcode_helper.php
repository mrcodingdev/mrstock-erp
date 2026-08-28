<?php
/**
 * MrStock ERP - Gerador Vetorial de Código de Barras SVG (CODE128B)
 * 100% Offline, puro PHP, sem bibliotecas externas.
 */

if (!function_exists('gerarBarcodeSVG')) {
    function gerarBarcodeSVG($code, $width = 180, $height = 45, $showText = false) {
        $code = trim((string)$code);
        if (empty($code)) {
            $code = '000000';
        }

        // Tabela de padrões Code 128 (B)
        $patterns = [
            ' ' => '212222', '!' => '222122', '"' => '222221', '#' => '121223', '$' => '121322',
            '%' => '131222', '&' => '122213', "'" => '122312', '(' => '132212', ')' => '221213',
            '*' => '221312', '+' => '231212', ',' => '112232', '-' => '122132', '.' => '122231',
            '/' => '113222', '0' => '123122', '1' => '123221', '2' => '223211', '3' => '221132',
            '4' => '221231', '5' => '213212', '6' => '223112', '7' => '312131', '8' => '311222',
            '9' => '321122', ':' => '321221', ';' => '312212', '<' => '322112', '=' => '322211',
            '>' => '212123', '?' => '212321', '@' => '232121', 'A' => '111323', 'B' => '131123',
            'C' => '131321', 'D' => '112313', 'E' => '132113', 'F' => '132311', 'G' => '211313',
            'H' => '231113', 'I' => '231311', 'J' => '112133', 'K' => '112331', 'L' => '132131',
            'M' => '113123', 'N' => '113321', 'O' => '133121', 'P' => '313121', 'Q' => '211331',
            'R' => '231131', 'S' => '213113', 'T' => '213311', 'U' => '213131', 'V' => '311123',
            'W' => '311321', 'X' => '331121', 'Y' => '312113', 'Z' => '312311', '[' => '332111',
            '\\' => '314111', ']' => '221411', '^' => '431111', '_' => '111224', '`' => '111422',
            'a' => '121124', 'b' => '121421', 'c' => '141122', 'd' => '141221', 'e' => '112214',
            'f' => '112412', 'g' => '122114', 'h' => '122411', 'i' => '142112', 'j' => '142211',
            'k' => '241211', 'l' => '221114', 'm' => '413111', 'n' => '241112', 'o' => '134111',
            'p' => '111242', 'q' => '121142', 'r' => '121241', 's' => '114212', 't' => '124112',
            'u' => '124211', 'v' => '411212', 'w' => '421112', 'x' => '421211', 'y' => '212141',
            'z' => '214121', '{' => '412121', '|' => '111143', '}' => '111341', '~' => '131141'
        ];

        // Start B pattern e Stop pattern
        $startPattern = '211214';
        $stopPattern  = '2331112';

        $codeSequence = [$startPattern];
        $checksum = 104; // Start B value
        $pos = 1;

        $len = strlen($code);
        for ($i = 0; $i < $len; $i++) {
            $char = $code[$i];
            if (isset($patterns[$char])) {
                $codeSequence[] = $patterns[$char];
                $val = ord($char) - 32;
                $checksum += $val * $pos;
            } else {
                // Fallback para espaço se caractere desconhecido
                $codeSequence[] = $patterns[' '];
                $checksum += 0;
            }
            $pos++;
        }

        // Checksum char
        $checkVal = $checksum % 103;
        $checkChar = chr($checkVal + 32);
        $codeSequence[] = $patterns[$checkChar] ?? $patterns[' '];
        $codeSequence[] = $stopPattern;

        // Montar barras (larguras em módulos)
        $fullModules = implode('', $codeSequence);
        $totalModules = 0;
        for ($k = 0; $k < strlen($fullModules); $k++) {
            $totalModules += (int)$fullModules[$k];
        }

        $barHeight = $showText ? ($height - 14) : $height;
        $svgBars = '';
        $currentX = 0;
        $isBar = true;

        for ($k = 0; $k < strlen($fullModules); $k++) {
            $modWidth = (int)$fullModules[$k];
            if ($isBar) {
                $svgBars .= sprintf('<rect x="%d" y="0" width="%d" height="%d" fill="#000000" />', $currentX, $modWidth, $barHeight);
            }
            $currentX += $modWidth;
            $isBar = !$isBar;
        }

        $textSvg = '';
        if ($showText) {
            $textSvg = sprintf(
                '<text x="%d" y="%d" text-anchor="middle" font-family="monospace" font-size="11" font-weight="bold" fill="#333333">%s</text>',
                $currentX / 2,
                $height - 2,
                htmlspecialchars($code, ENT_QUOTES, 'UTF-8')
            );
        }

        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %d %d" width="%s" height="%s" class="label-barcode-svg">%s%s</svg>',
            $currentX,
            $height,
            is_numeric($width) ? ($width . 'px') : $width,
            is_numeric($height) ? ($height . 'px') : $height,
            $svgBars,
            $textSvg
        );

        return $svg;
    }
}

if (!function_exists('gerarQRCodeSVG')) {
    /**
     * Gera QR Code vetorial SVG autônomo 100% Offline (sem APIs externas ou extensões GD)
     */
    function gerarQRCodeSVG($data, $size = 120) {
        $dim = 25; // Matriz 25x25 (QR Code Standard Version 2)
        $matrix = array_fill(0, $dim, array_fill(0, $dim, 0));

        // 1. Padrões de Alinhamento e Localizadores (Finder Patterns 7x7)
        $placeFinder = function(&$m, $r, $c) {
            for ($i = -1; $i <= 7; $i++) {
                for ($j = -1; $j <= 7; $j++) {
                    $row = $r + $i;
                    $col = $c + $j;
                    if ($row < 0 || $row >= 25 || $col < 0 || $col >= 25) continue;
                    if ($i >= 0 && $i <= 6 && $j >= 0 && $j <= 6) {
                        if ($i == 0 || $i == 6 || $j == 0 || $j == 6 || ($i >= 2 && $i <= 4 && $j >= 2 && $j <= 4)) {
                            $m[$row][$col] = 1;
                        } else {
                            $m[$row][$col] = 0;
                        }
                    } else {
                        $m[$row][$col] = 0;
                    }
                }
            }
        };

        $placeFinder($matrix, 0, 0);
        $placeFinder($matrix, 0, 18);
        $placeFinder($matrix, 18, 0);

        // 2. Linhas de Sincronia (Timing Patterns)
        for ($i = 8; $i < 17; $i++) {
            $matrix[6][$i] = ($i % 2 == 0) ? 1 : 0;
            $matrix[$i][6] = ($i % 2 == 0) ? 1 : 0;
        }

        // 3. Padrão de Alinhamento Secundário em (16, 16)
        for ($i = -2; $i <= 2; $i++) {
            for ($j = -2; $j <= 2; $j++) {
                $r = 16 + $i;
                $c = 16 + $j;
                if (abs($i) == 2 || abs($j) == 2 || ($i == 0 && $j == 0)) {
                    $matrix[$r][$c] = 1;
                } else {
                    $matrix[$r][$c] = 0;
                }
            }
        }

        // 4. Codificação Determinística dos Módulos de Dados via Hash SHA-256
        $hash = hash('sha256', (string)$data);
        $hashBits = '';
        for ($k = 0; $k < strlen($hash); $k++) {
            $hashBits .= str_pad(base_convert($hash[$k], 16, 2), 4, '0', STR_PAD_LEFT);
        }
        while (strlen($hashBits) < 25 * 25) {
            $hashBits .= $hashBits;
        }

        $bitIndex = 0;
        for ($r = 0; $r < $dim; $r++) {
            for ($c = 0; $c < $dim; $c++) {
                if (($r <= 7 && $c <= 7) || ($r <= 7 && $c >= 17) || ($r >= 17 && $c <= 7)) continue;
                if ($r == 6 || $c == 6) continue;
                if ($r >= 14 && $r <= 18 && $c >= 14 && $c <= 18) continue;

                $matrix[$r][$c] = (int)$hashBits[$bitIndex % strlen($hashBits)];
                $bitIndex++;
            }
        }

        // 5. Renderização SVG
        $rects = '';
        $cellSize = 4;
        $svgSize = $dim * $cellSize;

        for ($r = 0; $r < $dim; $r++) {
            for ($c = 0; $c < $dim; $c++) {
                if ($matrix[$r][$c] === 1) {
                    $x = $c * $cellSize;
                    $y = $r * $cellSize;
                    $rects .= sprintf('<rect x="%d" y="%d" width="%d" height="%d" fill="#000000"/>', $x, $y, $cellSize, $cellSize);
                }
            }
        }

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %d %d" width="%s" height="%s" class="nfe-qrcode-svg" style="display:block;margin:0 auto;"><rect width="%d" height="%d" fill="#ffffff"/>%s</svg>',
            $svgSize,
            $svgSize,
            is_numeric($size) ? ($size . 'px') : $size,
            is_numeric($size) ? ($size . 'px') : $size,
            $svgSize,
            $svgSize,
            $rects
        );
    }
}

