<?php
/*
 * Project       : Nova主题
 * Description   : 纯PHP二维码生成类（无外部依赖）
 */

if (!defined('ABSPATH')) exit;

class NovaQrcode
{
    const MODE_NUMBER = 1;
    const MODE_ALPHA  = 2;
    const MODE_BYTE   = 4;

    /** 版本1-40的容量与纠错配置 [dataCodewords, ecCodewordsPerBlock, numBlocks1, dataPerBlock1, numBlocks2, dataPerBlock2] */
    private static $capacity = array(
        1  => array(19, 7, 1, 19, 0, 0),
        2  => array(34, 10, 1, 34, 0, 0),
        3  => array(55, 15, 1, 55, 0, 0),
        4  => array(80, 20, 1, 80, 0, 0),
        5  => array(108, 26, 1, 108, 0, 0),
        6  => array(136, 18, 2, 68, 0, 0),
        7  => array(156, 20, 2, 78, 0, 0),
        8  => array(194, 24, 2, 97, 0, 0),
        9  => array(232, 30, 2, 116, 0, 0),
        10 => array(274, 18, 2, 68, 2, 69),
        11 => array(324, 20, 4, 81, 0, 0),
        12 => array(370, 24, 2, 92, 2, 93),
        13 => array(428, 26, 4, 107, 0, 0),
        14 => array(461, 30, 3, 115, 1, 116),
        15 => array(523, 22, 5, 87, 1, 88),
        16 => array(589, 24, 5, 98, 1, 99),
        17 => array(647, 28, 1, 107, 5, 108),
        18 => array(721, 30, 5, 120, 1, 121),
        19 => array(795, 28, 3, 113, 4, 114),
        20 => array(861, 28, 3, 107, 5, 108),
        21 => array(932, 28, 4, 116, 4, 117),
        22 => array(1006, 28, 2, 111, 7, 112),
        23 => array(1093, 30, 4, 121, 5, 122),
        24 => array(1171, 30, 6, 117, 4, 118),
        25 => array(1273, 26, 8, 106, 4, 107),
        26 => array(1367, 28, 10, 114, 2, 115),
        27 => array(1465, 30, 8, 122, 4, 123),
        28 => array(1528, 30, 3, 117, 10, 118),
        29 => array(1628, 30, 7, 116, 7, 117),
        30 => array(1732, 30, 5, 115, 10, 116),
        31 => array(1840, 30, 13, 115, 3, 116),
        32 => array(1952, 30, 17, 115, 0, 0),
        33 => array(2068, 30, 17, 115, 1, 116),
        34 => array(2188, 30, 13, 115, 6, 116),
        35 => array(2303, 30, 12, 121, 7, 122),
        36 => array(2431, 30, 6, 121, 14, 122),
        37 => array(2563, 30, 17, 122, 4, 123),
        38 => array(2699, 30, 4, 122, 18, 123),
        39 => array(2809, 30, 20, 117, 4, 118),
        40 => array(2953, 30, 19, 118, 6, 119),
    );

    /** GF(256) 指数表 */
    private static $exp_table = array();
    private static $log_table = array();

    private static function init_tables()
    {
        if (!empty(self::$exp_table)) return;
        self::$exp_table = array_fill(0, 256, 0);
        self::$log_table = array_fill(0, 256, 0);
        $x = 1;
        for ($i = 0; $i < 255; $i++) {
            self::$exp_table[$i] = $x;
            self::$log_table[$x] = $i;
            $x <<= 1;
            if ($x & 0x100) $x ^= 0x11d;
        }
    }

    private static function gf_mul($a, $b)
    {
        if ($a == 0 || $b == 0) return 0;
        return self::$exp_table[(self::$log_table[$a] + self::$log_table[$b]) % 255];
    }

    /**
     * 生成二维码 PNG
     * @param string $text 内容
     * @param string $outfile 输出文件路径（留空则直接输出）
     * @param int $margin 边距
     * @param int $scale 缩放
     */
    public static function png($text, $outfile = false, $level = 'L', $margin = 4, $scale = 4)
    {
        self::init_tables();
        $data = self::encode($text);
        $matrix = self::build_matrix($data);

        $n = count($matrix);
        $size = ($n + 2 * $margin) * $scale;
        $img = imagecreatetruecolor($size, $size);
        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);
        imagefill($img, 0, 0, $white);

        for ($y = 0; $y < $n; $y++) {
            for ($x = 0; $x < $n; $x++) {
                if ($matrix[$y][$x]) {
                    imagefilledrectangle($img, ($x + $margin) * $scale, ($y + $margin) * $scale, ($x + $margin + 1) * $scale - 1, ($y + $margin + 1) * $scale - 1, $black);
                }
            }
        }

        if ($outfile) {
            imagepng($img, $outfile);
        } else {
            header('Content-Type: image/png');
            imagepng($img);
        }
        imagedestroy($img);
        return true;
    }

    /**
     * 编码为位流
     */
    private static function encode($text)
    {
        $mode = self::MODE_BYTE;
        $bytes = array();
        $len = strlen($text);
        for ($i = 0; $i < $len; $i++) {
            $bytes[] = ord($text[$i]);
        }

        //选择版本
        $version = 1;
        for ($v = 1; $v <= 40; $v++) {
            $cap = self::$capacity[$v];
            $char_bits = ($mode == self::MODE_BYTE) ? 8 : 8;
            $count_bits = ($v <= 9) ? 8 : 16;
            $total_bits = 4 + $count_bits + $len * $char_bits;
            if ($total_bits <= $cap[0] * 8) {
                $version = $v;
                break;
            }
        }

        //模式指示符 + 字符计数 + 数据
        $bits = '';
        $bits .= str_pad(decbin($mode), 4, '0', STR_PAD_LEFT);
        $count_bits = ($version <= 9) ? 8 : 16;
        $bits .= str_pad(decbin($len), $count_bits, '0', STR_PAD_LEFT);
        foreach ($bytes as $b) {
            $bits .= str_pad(decbin($b), 8, '0', STR_PAD_LEFT);
        }

        $cap = self::$capacity[$version];
        $data_codewords = $cap[0];
        $total_bits_needed = $data_codewords * 8;

        //添加终止符
        $rem = $total_bits_needed - strlen($bits);
        $bits .= str_repeat('0', min(4, $rem));

        //补齐到字节边界
        if (strlen($bits) % 8 != 0) {
            $bits .= str_repeat('0', 8 - strlen($bits) % 8);
        }

        //填充字节
        $pad = array(0xec, 0x11);
        $i = 0;
        while (strlen($bits) < $total_bits_needed) {
            $bits .= str_pad(decbin($pad[$i % 2]), 8, '0', STR_PAD_LEFT);
            $i++;
        }

        //转成数据码字
        $data = array();
        for ($i = 0; $i < $data_codewords; $i++) {
            $data[] = bindec(substr($bits, $i * 8, 8));
        }

        //生成纠错码
        $ec = self::generate_ec($data, $version);
        $all = array_merge($data, $ec);

        return array('version' => $version, 'codewords' => $all, 'data_count' => $data_codewords);
    }

    /**
     * 生成纠错码字（Reed-Solomon）
     */
    private static function generate_ec($data, $version)
    {
        $cap = self::$capacity[$version];
        $ec_per_block = $cap[1];
        $num_blocks1  = $cap[2];
        $data_per_block1 = $cap[3];
        $num_blocks2  = $cap[4];
        $data_per_block2 = $cap[5];
        $total_blocks = $num_blocks1 + $num_blocks2;

        //生成多项式
        $gen = self::generator_poly($ec_per_block);

        $blocks_ec = array();
        $pos = 0;
        for ($b = 0; $b < $total_blocks; $b++) {
            $db = ($b < $num_blocks1) ? $data_per_block1 : $data_per_block2;
            $block = array_slice($data, $pos, $db);
            $pos += $db;
            //多项式除法
            $poly = array_merge($block, array_fill(0, $ec_per_block, 0));
            for ($i = 0; $i < $db; $i++) {
                $factor = $poly[$i];
                if ($factor == 0) continue;
                $log_factor = self::$log_table[$factor];
                for ($j = 0; $j < $ec_per_block; $j++) {
                    $g = $gen[$j];
                    $poly[$i + 1 + $j] ^= ($g == 0) ? 0 : self::$exp_table[($log_factor + self::$log_table[$g]) % 255];
                }
            }
            $blocks_ec[] = array_slice($poly, $db);
        }
        return call_user_func_array('array_merge', $blocks_ec);
    }

    /**
     * 生成多项式
     */
    private static function generator_poly($degree)
    {
        $poly = array(1);
        for ($i = 0; $i < $degree; $i++) {
            $next = array_fill(0, count($poly) + 1, 0);
            for ($j = 0; $j < count($poly); $j++) {
                $next[$j] ^= $poly[$j];
                if ($poly[$j] != 0) {
                    $next[$j + 1] ^= self::$exp_table[(self::$log_table[$poly[$j]] + $i) % 255];
                }
            }
            $poly = $next;
        }
        return $poly;
    }

    /**
     * 构建二维码矩阵
     */
    private static function build_matrix($data)
    {
        $version = $data['version'];
        $n = 17 + 4 * $version;
        $matrix = array_fill(0, $n, array_fill(0, $n, false));

        //定位图案（3个角）
        self::place_finder($matrix, 0, 0);
        self::place_finder($matrix, $n - 7, 0);
        self::place_finder($matrix, 0, $n - 7);

        //时序图案
        for ($i = 8; $i < $n - 8; $i++) {
            $matrix[6][$i] = ($i % 2 == 0);
            $matrix[$i][6] = ($i % 2 == 0);
        }

        //校正图形（版本>=2）
        if ($version >= 2) {
            self::place_alignment($matrix, $version, $n);
        }

        //格式信息
        $format = 0b10100110111; // L纠错 + mask 000（先用掩码0，简化实现）
        self::place_format($matrix, $format, $n);

        //数据位放置（之字形）
        $bits_str = '';
        foreach ($data['codewords'] as $cw) {
            $bits_str .= str_pad(decbin($cw), 8, '0', STR_PAD_LEFT);
        }
        $bits = str_split($bits_str);
        $bit_index = 0;
        $upward = true;

        for ($col = $n - 1; $col > 0; $col -= 2) {
            if ($col == 6) $col--; //跳过时序图案列
            for ($k = 0; $k < $n; $k++) {
                $row = $upward ? ($n - 1 - $k) : $k;
                for ($c = 0; $c < 2; $c++) {
                    $x = $col - $c;
                    if ($matrix[$row][$x] !== null && $matrix[$row][$x] !== true && $matrix[$row][$x] !== false) {
                        continue;
                    }
                    //检查是否功能区
                    if (self::is_function_area($row, $x, $n, $version)) {
                        continue;
                    }
                    $bit = false;
                    if ($bit_index < count($bits)) {
                        $bit = ($bits[$bit_index] == '1');
                        $bit_index++;
                    }
                    //应用掩码0：(row+col)%2==0
                    if (($row + $x) % 2 == 0) {
                        $bit = !$bit;
                    }
                    $matrix[$row][$x] = $bit;
                }
            }
            $upward = !$upward;
        }

        return $matrix;
    }

    private static function is_function_area($row, $col, $n, $version)
    {
        //定位图案区域
        if ($row < 9 && $col < 9) return true;
        if ($row < 9 && $col > $n - 9) return true;
        if ($row > $n - 9 && $col < 9) return true;
        //时序图案
        if ($row == 6 || $col == 6) return true;
        //格式信息区域
        if ($row < 9 && ($col == 8 || $col == $n - 8)) return true;
        if ($col < 9 && $row == 8) return true;
        //校正图形
        if ($version >= 2) {
            $positions = self::alignment_positions($version);
            foreach ($positions as $pr) {
                foreach ($positions as $pc) {
                    if (($pr < 9 && $pc < 9) || ($pr < 9 && $pc > $n - 9) || ($pr > $n - 9 && $pc < 9)) continue;
                    if (abs($row - $pr) <= 2 && abs($col - $pc) <= 2) return true;
                }
            }
        }
        return false;
    }

    private static function place_finder(&$matrix, $r, $c)
    {
        for ($i = -1; $i <= 7; $i++) {
            for ($j = -1; $j <= 7; $j++) {
                $rr = $r + $i;
                $cc = $c + $j;
                if ($rr < 0 || $cc < 0 || $rr >= count($matrix) || $cc >= count($matrix)) continue;
                $dist = max(abs($i), abs($j));
                if ($dist == 0 || $dist == 1) {
                    $matrix[$rr][$cc] = true;  //黑
                } elseif ($dist == 2 || $dist == 3) {
                    $matrix[$rr][$cc] = false; //白
                } elseif ($dist == 4 || $dist == 5) {
                    $matrix[$rr][$cc] = true;
                } elseif ($dist == 6) {
                    $matrix[$rr][$cc] = false;
                } else {
                    $matrix[$rr][$cc] = true;
                }
            }
        }
    }

    private static function alignment_positions($version)
    {
        $pos = array(6);
        $count = (int) ($version / 7) + 2;
        $step = ($version == 32) ? 26 : (4 * $version + $count * 2 + 1) / ($count - 1);
        $step = (int) ceil($step / 2) * 2;
        for ($i = 1; $i < $count; $i++) {
            $pos[] = 6 + (int) (($count - 1 - $i) * $step);
        }
        sort($pos);
        return $pos;
    }

    private static function place_alignment(&$matrix, $version, $n)
    {
        $positions = self::alignment_positions($version);
        foreach ($positions as $r) {
            foreach ($positions as $c) {
                if (($r < 9 && $c < 9) || ($r < 9 && $c > $n - 9) || ($r > $n - 9 && $c < 9)) continue;
                for ($i = -2; $i <= 2; $i++) {
                    for ($j = -2; $j <= 2; $j++) {
                        $dist = max(abs($i), abs($j));
                        $matrix[$r + $i][$c + $j] = ($dist != 1);
                    }
                }
            }
        }
    }

    private static function place_format(&$matrix, $format, $n)
    {
        $bits = str_pad(decbin($format), 15, '0', STR_PAD_LEFT);
        //格式信息位置（简化的常见位布置）
        $positions1 = array(array(8,0),array(8,1),array(8,2),array(8,3),array(8,4),array(8,5),array(8,7),array(8,8),array(7,8),array(5,8),array(4,8),array(3,8),array(2,8),array(1,8),array(0,8));
        $positions2 = array(array($n-1,8),array($n-2,8),array($n-3,8),array($n-4,8),array($n-5,8),array($n-6,8),array($n-7,8),array(8,$n-8),array(8,$n-7),array(8,$n-6),array(8,$n-5),array(8,$n-4),array(8,$n-3),array(8,$n-2),array(8,$n-1));
        for ($i = 0; $i < 15; $i++) {
            $v = ($bits[$i] == '1');
            $p1 = $positions1[$i];
            $p2 = $positions2[$i];
            $matrix[$p1[0]][$p1[1]] = $v;
            $matrix[$p2[0]][$p2[1]] = $v;
        }
        //暗模块
        $matrix[4 * $n / 17][8] = true;
    }
}
