<?php
/*
 * Project       : Nova主题
 * Description   : 文件操作类（上传/删除/缩略图）
 */

if (!defined('ABSPATH')) exit;

class NovaFile
{
    /**
     * 上传文件（处理 base64 或表单文件）
     * @param string $file_input $_FILES 的键名
     * @param string $file_base64 base64 数据
     * @param string $upload_dir 上传目录（wp-content/uploads 下）
     * @return array|WP_Error
     */
    public static function upload($file_input = '', $file_base64 = '', $upload_dir = 'nova-upload')
    {
        if (!function_exists('media_handle_upload')) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
        }

        $uploads = wp_upload_dir();
        $dir     = $uploads['basedir'] . '/' . $upload_dir;

        //创建目录
        if (!file_exists($dir)) {
            wp_mkdir_p($dir);
        }

        //处理 base64 上传
        if ($file_base64) {
            if (preg_match('/^data:image\/(\w+);base64,(.+)$/', $file_base64, $match)) {
                $ext      = $match[1];
                $file_str = base64_decode($match[2]); // phpcs:ignore
                $name     = uniqid('img_') . '.' . $ext;
                $path     = $dir . '/' . $name;
                file_put_contents($path, $file_str);
                return array(
                    'url'  => $uploads['baseurl'] . '/' . $upload_dir . '/' . $name,
                    'path' => $path,
                    'name' => $name,
                );
            }
            return new WP_Error('nova_file_error', '文件格式错误');
        }

        //处理表单上传
        if ($file_input && isset($_FILES[$file_input])) {
            $file = $_FILES[$file_input];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                return new WP_Error('nova_file_error', '上传失败，错误代码：' . $file['error']);
            }
            //校验大小 10MB
            if ($file['size'] > 10 * 1024 * 1024) {
                return new WP_Error('nova_file_error', '文件过大，最大支持10MB');
            }
            //校验类型
            $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf', 'zip', 'rar', '7z', 'doc', 'docx', 'txt', 'mp4');
            $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                return new WP_Error('nova_file_error', '不支持的文件类型：' . $ext);
            }
            $name = uniqid('file_') . '.' . $ext;
            $path = $dir . '/' . $name;
            if (!move_uploaded_file($file['tmp_name'], $path)) {
                return new WP_Error('nova_file_error', '文件保存失败');
            }
            return array(
                'url'  => $uploads['baseurl'] . '/' . $upload_dir . '/' . $name,
                'path' => $path,
                'name' => $name,
            );
        }

        return new WP_Error('nova_file_error', '未接收到文件');
    }

    /**
     * 删除文件
     */
    public static function delete($file_path)
    {
        if (!$file_path) return false;
        //只允许删除上传目录内的文件
        $uploads = wp_upload_dir();
        if (strpos($file_path, $uploads['basedir']) !== 0) {
            return false;
        }
        if (file_exists($file_path)) {
            @unlink($file_path);
            return true;
        }
        return false;
    }

    /**
     * 获取图片主要颜色（用于卡片背景）
     */
    public static function get_dominant_color($image_path)
    {
        if (!file_exists($image_path)) return '';
        if (!function_exists('imagecreatefromjpeg')) return '';
        $ext = strtolower(pathinfo($image_path, PATHINFO_EXTENSION));
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                $img = @imagecreatefromjpeg($image_path);
                break;
            case 'png':
                $img = @imagecreatefrompng($image_path);
                break;
            case 'gif':
                $img = @imagecreatefromgif($image_path);
                break;
            default:
                return '';
        }
        if (!$img) return '';
        $w = imagesx($img);
        $h = imagesy($img);
        $rgb = imagecolorat($img, (int) ($w / 2), (int) ($h / 2));
        imagedestroy($img);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
}
