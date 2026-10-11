<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-11-11 11:41:45
 * @LastEditTime : 2026-08-25 20:16:00
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

class CFS_Module
{

    public static function footer_tabbar()
    {
        $args = array(
            array(
                'id'      => 'icon_fa',
                'class'   => 'compact',
                'type'    => 'color_group',
                'title'   => '',
                'desc'    => __('自定义颜色请注意日间、夜间模式的适配', 'zib_language'),
                'options' => array(
                    'color' => __('自定义图标颜色', 'zib_language'),
                    'bg'    => __('自定义背景颜色', 'zib_language'),
                ),
            ),
        );
        return $args;
    }

    public static function wp_language_options()
    {
        static $options = null;
    
        if ($options !== null) {
            return $options;
        }
    
        $options = apply_filters('zib_language_options', array(
            // 已安装
            'en_US' => 'English (United States)',
            'zh_CN' => '简体中文',
            // 可用
            'af'              => 'Afrikaans',
            'am'              => 'አማርኛ',
            'arg'             => 'Aragonés',
            'ar'              => 'العربية',
            'ary'             => 'العربية المغربية',
            'as'              => 'অসমীয়া',
            'azb'             => 'گؤنئی آذربایجان',
            'az'              => 'Azərbaycan dili',
            'bel'             => 'Беларуская мова',
            'bg_BG'           => 'Български',
            'bn_BD'           => 'বাংলা',
            'bo'              => 'བོད་ཡིག',
            'bs_BA'           => 'Bosanski',
            'ca'              => 'Català',
            'ceb'             => 'Cebuano',
            'cs_CZ'           => 'Čeština',
            'cy'              => 'Cymraeg',
            'da_DK'           => 'Dansk',
            'de_AT'           => 'Deutsch (Österreich)',
            'de_DE'           => 'Deutsch',
            'de_DE_formal'    => 'Deutsch (Sie)',
            'de_CH'           => 'Deutsch (Schweiz)',
            'de_CH_informal'  => 'Deutsch (Schweiz, Du)',
            'dsb'             => 'Dolnoserbšćina',
            'dzo'             => 'རྫོང་ཁ',
            'el'              => 'Ελληνικά',
            'en_NZ'           => 'English (New Zealand)',
            'en_AU'           => 'English (Australia)',
            'en_CA'           => 'English (Canada)',
            'en_ZA'           => 'English (South Africa)',
            'en_GB'           => 'English (UK)',
            'eo'              => 'Esperanto',
            'es_MX'           => 'Español de México',
            'es_CO'           => 'Español de Colombia',
            'es_ES'           => 'Español',
            'es_PE'           => 'Español de Perú',
            'es_VE'           => 'Español de Venezuela',
            'es_EC'           => 'Español de Ecuador',
            'es_DO'           => 'Español de República Dominicana',
            'es_UY'           => 'Español de Uruguay',
            'es_PR'           => 'Español de Puerto Rico',
            'es_GT'           => 'Español de Guatemala',
            'es_CL'           => 'Español de Chile',
            'es_CR'           => 'Español de Costa Rica',
            'es_AR'           => 'Español de Argentina',
            'et'              => 'Eesti',
            'eu'              => 'Euskara',
            'fa_IR'           => 'فارسی',
            'fa_AF'           => '(فارسی (افغانستان',
            'fi'              => 'Suomi',
            'fr_CA'           => 'Français du Canada',
            'fr_FR'           => 'Français',
            'fr_BE'           => 'Français de Belgique',
            'fur'             => 'Friulian',
            'fy'              => 'Frysk',
            'gd'              => 'Gàidhlig',
            'gl_ES'           => 'Galego',
            'gu'              => 'ગુજરાતી',
            'haz'             => 'هزاره گی',
            'he_IL'           => 'עִבְרִית',
            'hi_IN'           => 'हिन्दी',
            'hr'              => 'Hrvatski',
            'hsb'             => 'Hornjoserbšćina',
            'hu_HU'           => 'Magyar',
            'hy'              => 'Հայերեն',
            'id_ID'           => 'Bahasa Indonesia',
            'is_IS'           => 'Íslenska',
            'it_IT'           => 'Italiano',
            'ja'              => '日本語',
            'jv_ID'           => 'Basa Jawa',
            'ka_GE'           => 'ქართული',
            'kab'             => 'Taqbaylit',
            'kk'              => 'Қазақ тілі',
            'km'              => 'ភាសាខ្មែរ',
            'kn'              => 'ಕನ್ನಡ',
            'ko_KR'           => '한국어',
            'ckb'             => 'كوردی',
            'kir'             => 'Кыргызча',
            'lo'              => 'ພາສາລາວ',
            'lt_LT'           => 'Lietuvių kalba',
            'lv'              => 'Latviešu valoda',
            'mk_MK'           => 'Македонски језик',
            'ml_IN'           => 'മലയാളം',
            'mn'              => 'Монгол',
            'mr'              => 'मराठी',
            'ms_MY'           => 'Bahasa Melayu',
            'my_MM'           => 'ဗမာစာ',
            'nb_NO'           => 'Norsk bokmål',
            'ne_NP'           => 'नेपाली',
            'nl_NL'           => 'Nederlands',
            'nl_NL_formal'    => 'Nederlands (Formeel)',
            'nl_BE'           => 'Nederlands (België)',
            'nn_NO'           => 'Norsk nynorsk',
            'oci'             => 'Occitan',
            'pa_IN'           => 'ਪੰਜਾਬੀ',
            'pl_PL'           => 'Polski',
            'ps'              => 'پښتو',
            'pt_PT_ao90'      => 'Português (AO90)',
            'pt_PT'           => 'Português',
            'pt_BR'           => 'Português do Brasil',
            'pt_AO'           => 'Português de Angola',
            'rhg'             => 'Ruáinga',
            'ro_RO'           => 'Română',
            'ru_RU'           => 'Русский',
            'sah'             => 'Сахалыы',
            'snd'             => 'سنڌي',
            'si_LK'           => 'සිංහල',
            'sk_SK'           => 'Slovenčina',
            'skr'             => 'سرائیکی',
            'sl_SI'           => 'Slovenščina',
            'sq'              => 'Shqip',
            'sr_RS'           => 'Српски језик',
            'sv_SE'           => 'Svenska',
            'sw'              => 'Kiswahili',
            'szl'             => 'Ślōnskŏ gŏdka',
            'ta_IN'           => 'தமிழ்',
            'ta_LK'           => 'தமிழ் (Sri Lanka)',
            'te'              => 'తెలుగు',
            'th'              => 'ไทย',
            'tl'              => 'Tagalog',
            'tr_TR'           => 'Türkçe',
            'tt_RU'           => 'Татар теле',
            'tah'             => 'Reo Tahiti',
            'ug_CN'           => 'ئۇيغۇرچە',
            'uk'              => 'Українська',
            'ur'              => 'اردو',
            'uz_UZ'           => 'O‘zbekcha',
            'vi'              => 'Tiếng Việt',
            'yor'             => 'Yorùbá',
            'zh_TW'           => '繁體中文',
            'zh_HK'           => '香港中文',
        ));
    
        return $options;
    }
    

    public static function float_btn($true = true)
    {
        $args = array(
            array(
                'id'      => 'pc_s',
                'title'   => '',
                'label'   => __('PC端显示', 'zib_language'),
                'type'    => 'switcher',
                'default' => true,
            ),
            array(
                'id'      => 'm_s',
                'class'   => 'compact',
                'title'   => '',
                'label'   => __('移动端显示', 'zib_language'),
                'type'    => 'switcher',
                'default' => true,
            ),
            array(
                'id'      => 'color',
                'class'   => 'compact',
                'type'    => 'color_group',
                'title'   => '',
                'desc'    => __('自定义颜色请注意日间、夜间模式的适配', 'zib_language'),
                'options' => array(
                    'color' => __('自定义图标颜色', 'zib_language'),
                    'bg'    => __('自定义背景颜色', 'zib_language'),
                ),
            ),
        );
        return $args;
    }

    public static function add_slider()
    {
        $f_imgpath = get_template_directory_uri() . '/inc/csf-framework/assets/images/';
        $args      = array();

        $args[] = array(
            'title'   => __('图片背景', 'zib_language'),
            'id'      => 'background',
            'default' => '',
            'preview' => true,
            'library' => 'image',
            'type'    => 'upload',
        );
        $args[] = array(
            'title'   => __('视频背景', 'zib_language') . zib_get_csf_option_new_badge()['7.1'],
            'id'      => 'background_video',
            'default' => '',
            'class'   => 'compact',
            'preview' => false,
            'library' => 'video',
            'type'    => 'upload',
            'desc'    => __('（必填）图片背景、视频背景至少二选一，如果同时设置则PC端视频优先（PC端视频加载失败则显示图片)', 'zib_language') . '<div class="c-yellow">' . __('如果需要在移动端显示，则必须设置图片背景，移动端只显示图片', 'zib_language') . '</div>',
        );
        $args[] = array(
            'title'   => __('显示规则', 'zib_language') . zib_get_csf_option_new_badge()['7.1'],
            'id'      => 'hide',
            'type'    => 'radio',
            'inline'  => true,
            'options' => array(
                ''   => __('全部显示', 'zib_language'),
                'pc' => __('PC端不显示', 'zib_language'),
                'm'  => __('移动端不显示', 'zib_language'),
            ),
            'default' => '',
        );
        $args[] = array(
            'id'           => 'link',
            'type'         => 'link',
            'title'        => __('跳转链接', 'zib_language'),
            'default'      => array(),
            'add_title'    => __('添加链接', 'zib_language'),
            'edit_title'   => __('编辑链接', 'zib_language'),
            'remove_title' => __('删除链接', 'zib_language'),
        );
        $args[] = array(
            'id'                     => 'image_layer',
            'type'                   => 'group',
            'accordion_title_number' => true,
            'accordion_title_auto'   => false,
            'accordion_title_prefix' => __('图层', 'zib_language'),
            'button_title'           => __('添加图层', 'zib_language'),
            'title'                  => __('叠加图层', 'zib_language'),
            'subtitle'               => __('添加更多图层', 'zib_language'),
            'desc'                   => __('添加额外的幻灯片图层，配合图层设置及幻灯片其它设置，可轻松制作出漂亮无比的幻灯片', 'zib_language'),
            'fields'                 => array(
                array(
                    'title'   => __('图层图片', 'zib_language'),
                    'id'      => 'image',
                    'default' => '',
                    'preview' => true,
                    'library' => 'image',
                    'type'    => 'upload',
                ),
                array(
                    'title'   => __('自由尺寸', 'zib_language'),
                    'type'    => 'switcher',
                    'id'      => 'free_size',
                    'class'   => 'compact',
                    'desc'    => __('如果图层的尺寸和背景图的尺寸不一致，可开启此项以自定义图层对齐方向', 'zib_language'),
                    'default' => false,
                    'type'    => 'switcher',
                ),
                array(
                    'dependency' => array('image|free_size', '!=|!=', '|'),
                    'title'      => __('图层对齐', 'zib_language'),
                    'id'         => 'align',
                    'inline'     => true,
                    'type'       => 'radio',
                    'class'      => 'compact',
                    'default'    => 'center',
                    'options'    => array(
                        'left'   => __('靠左显示', 'zib_language'),
                        'center' => __('居中显示', 'zib_language'),
                        'right'  => __('靠右显示', 'zib_language'),
                    ),
                ), array(
                    'dependency' => array('image', '!=', ''),
                    'id'         => 'parallax',
                    'class'      => 'compact',
                    'desc'       => __('提前或延后进入视线，负值为延后，正值为提前，0为关闭[-200~200]', 'zib_language'),
                    'title'      => __('视差滚动', 'zib_language'),
                    'default'    => 0,
                    'max'        => 200,
                    'min'        => -200,
                    'step'       => 5,
                    'unit'       => '%',
                    'type'       => 'spinner',
                ), array(
                    'dependency' => array('image', '!=', ''),
                    'id'         => 'parallax_scale',
                    'desc'       => __('放大或缩小进入视线，原图大小的百分之多少[1~200]', 'zib_language'),
                    'class'      => 'compact',
                    'title'      => __('视差缩放', 'zib_language'),
                    'default'    => 100,
                    'max'        => 200,
                    'min'        => 1,
                    'step'       => 5,
                    'unit'       => '%',
                    'type'       => 'spinner',
                ), array(
                    'dependency' => array('image|parallax', '!=|!=', '|'),
                    'id'         => 'parallax_opacity',
                    'desc'       => __('以百分之多少的透明度进入视线[1~100]', 'zib_language') . '<br>' . __('视差功能对浏览器性能有一定影响，如果图层较多，不建议全部开启', 'zib_language'),
                    'class'      => 'compact',
                    'title'      => __('视差透明', 'zib_language'),
                    'default'    => 100,
                    'max'        => 100,
                    'min'        => 1,
                    'step'       => 5,
                    'unit'       => '%',
                    'type'       => 'spinner',
                ),
            ),
        );
        $args[] = array(
            'id'         => 'text',
            'type'       => 'accordion',
            'title'      => __('叠加文案', 'zib_language'),
            'accordions' => array(
                array(
                    'title'  => __('幻灯片叠加文案', 'zib_language'),
                    'fields' => array(
                        array(
                            'title'      => __('幻灯片文案', 'zib_language'),
                            'subtitle'   => __('幻灯片标题', 'zib_language'),
                            'id'         => 'title',
                            'default'    => '',
                            'attributes' => array(
                                'rows' => 1,
                            ),
                            'type'       => 'textarea',
                        ),
                        array(
                            'dependency' => array('title', '!=', ''),
                            'title'      => __('幻灯片简介', 'zib_language'),
                            'id'         => 'desc',
                            'class'      => 'compact',
                            'default'    => '',
                            'desc'       => __('标题、简介均支持HTML代码，请注意代码规范及标签闭合', 'zib_language'),
                            'attributes' => array(
                                'rows' => 1,
                            ),
                            'type'       => 'textarea',
                        ),
                        array(
                            'dependency' => array('title', '!=', ''),
                            'title'      => __('显示位置', 'zib_language'),
                            'id'         => 'text_align',
                            'type'       => 'image_select',
                            'class'      => 'compact image-miniselect',
                            'default'    => 'left-bottom',
                            'desc'       => __('前景图显示位置及文案位置需合理搭配', 'zib_language'),
                            'options'    => array(
                                'left-bottom'   => $f_imgpath . 'left-bottom.jpg',
                                'left-conter'   => $f_imgpath . 'left-conter.jpg',
                                'conter-conter' => $f_imgpath . 'conter-conter.jpg',
                                'conter-bottom' => $f_imgpath . 'conter-bottom.jpg',
                                'right-conter'  => $f_imgpath . 'right-conter.jpg',
                                'right-bottom'  => $f_imgpath . 'right-bottom.jpg',
                            ),
                        ),
                        array(
                            'dependency' => array('title', '!=', ''),
                            'id'         => 'text_size_pc',
                            'class'      => 'compact',
                            'title'      => __('PC端字体大小', 'zib_language'),
                            'default'    => 30,
                            'max'        => 50,
                            'min'        => 12,
                            'step'       => 1,
                            'unit'       => 'PX',
                            'type'       => 'spinner',
                        ),
                        array(
                            'dependency' => array('title', '!=', ''),
                            'id'         => 'text_size_m',
                            'class'      => 'compact',
                            'title'      => __('移动端字体大小', 'zib_language'),
                            'desc'       => __('在此设置标题的字体大小，简介的大小为标题大小的60%，最小12px', 'zib_language') . '<br>' . __('字体越大，文案周边的间距也越大！字体大小请根据内容合理调整，避免在某些设备显示不全', 'zib_language'),
                            'default'    => 20,
                            'max'        => 50,
                            'min'        => 12,
                            'step'       => 1,
                            'unit'       => 'PX',
                            'type'       => 'spinner',
                        ),
                        array(
                            'dependency' => array('title', '!=', ''),
                            'id'         => 'parallax',
                            'class'      => 'compact',
                            'desc'       => __('视差滚动功能为较背景滚动的时间差，为负数则滚动慢一拍，为正数则滚动快一拍，为0则关闭', 'zib_language'),
                            'title'      => __('文案视差滚动', 'zib_language'),
                            'default'    => 40,
                            'max'        => 200,
                            'min'        => -200,
                            'step'       => 10,
                            'unit'       => '%',
                            'type'       => 'spinner',
                        ),
                    ),
                ),
            ),
        );

        return $args;
    }

    public static function page_type()
    {
        return array(
            'home'        => __('首页', 'zib_language'),
            'topics'      => __('专题页', 'zib_language'),
            'category'    => __('分类页', 'zib_language'),
            'tag'         => __('标签页', 'zib_language'),
            'author'      => __('用户页', 'zib_language'),
            'single'      => __('文章页', 'zib_language'),
            'search'      => __('搜索页', 'zib_language'),
            'forum_home'  => __('[论坛]首页', 'zib_language'),
            'forum_plate' => __('[论坛]板块页面', 'zib_language'),
            'forum_post'  => __('[论坛]帖子页面', 'zib_language'),
            'page'        => __('其它页面', 'zib_language'),
        );
    }

    public static function posts_orderby($orderby = array())
    {
        return array_merge($orderby, array(
            'modified'            => __('更新时间', 'zib_language'),
            'date'                => __('发布时间', 'zib_language'),
            'views'               => __('浏览数量', 'zib_language'),
            'like'                => __('点赞数量', 'zib_language'),
            'comment_count'       => __('评论数量', 'zib_language'),
            'favorite'            => __('收藏数量', 'zib_language'),
            'zibpay_price'        => __('销售价格', 'zib_language'),
            'zibpay_points_price' => __('积分售价', 'zib_language'),
            'sales_volume'        => __('销售数量', 'zib_language'),
            'rand'                => __('随机排序', 'zib_language'),
        ));
    }

    public static function zib_palette($palette = array(), $show = array('c', 'b', 'jb'))
    {
        if (in_array('c', $show)) {
            $palette = array_merge($palette, array(
                'c-red'      => array('rgba(255, 84, 115, .4)'),
                'c-red-2'    => array('rgba(194, 41, 46, 0.4)'),
                'c-yellow'   => array('rgba(255, 111, 6, 0.4)'),
                'c-yellow-2' => array('rgba(179, 103, 8, 0.4)'),
                'c-cyan'     => array('rgba(8, 196, 193, .4)'),
                'c-blue'     => array('rgba(41, 151, 247, .4)'),
                'c-blue-2'   => array('rgba(77, 130, 249, .4)'),
                'c-green'    => array('rgba(18, 185, 40, .4)'),
                'c-green-2'  => array('rgba(72, 135, 24, .4)'),
                'c-purple'   => array('rgba(213, 72, 245, 0.4)'),
                'c-purple-2' => array('rgba(154, 72, 245, 0.4)'),
            ));
        }
        if (in_array('b', $show)) {
            $palette = array_merge($palette, array(
                'b-red'    => array('#f74b3d'),
                'b-yellow' => array('#f3920a'),
                'b-cyan'   => array('#08c4c1'),
                'b-blue'   => array('#0a8cf3'),
                'b-green'  => array('#1fd05a'),
                'b-purple' => array('#c133f5'),
                'b-black'  => array('#121517'),
            ));
        }
        if (in_array('jb', $show)) {
            $palette = array_merge($palette, array(
                'jb-red'    => array('linear-gradient(135deg, #ffbeb4 10%, #f61a1a 100%)'),
                'jb-pink'   => array('linear-gradient(135deg, #ff5e7f 30%, #ff967e 100%)'),
                'jb-yellow' => array('linear-gradient(135deg, #ffd6b2 10%, #ff651c 100%)'),
                'jb-cyan'   => array('linear-gradient(140deg, #039ab3 10%, #58dbcf 90%)'),
                'jb-blue'   => array('linear-gradient(135deg, #b6e6ff 10%, #198aff 100%)'),
                'jb-green'  => array('linear-gradient(135deg, #ccffcd 10%, #52bb51 100%)'),
                'jb-purple' => array('linear-gradient(135deg, #fec2ff 10%, #d000de 100%)'),
                'jb-vip1'   => array('linear-gradient(25deg, #eab869 10%, #fbecd4 60%, #ffe0ae 100%)'),
                'jb-vip2'   => array('linear-gradient(317deg, #4d4c4c 30%, #878787 70%, #5f5c5c 100%)'),
            ));
        }

        if (in_array('text', $show)) {
            $palette = array_merge($palette, array(
                'focus-color'   => array('url(' . get_template_directory_uri() . '/inc/csf-framework/assets/images/skin-color-focus.svg)  no-repeat  center/100% 100%'),
                'key-color'     => array('#333'),
                'muted-color'   => array('#777777'),
                'muted-2-color' => array('#999'),
                'muted-3-color' => array('#b1b1b1'),
                'c-red'         => array('rgba(255, 84, 115)'),
                'c-red-2'       => array('rgba(194, 41, 46)'),
                'c-yellow'      => array('rgba(255, 111, 6)'),
                'c-yellow-2'    => array('rgba(179, 103, 8)'),
                'c-cyan'        => array('rgba(8, 196, 193)'),
                'c-blue'        => array('rgba(41, 151, 247)'),
                'c-blue-2'      => array('rgba(77, 130, 249)'),
                'c-green'       => array('rgba(18, 185, 40)'),
                'c-green-2'     => array('rgba(72, 135, 24)'),
                'c-purple'      => array('rgba(213, 72, 245)'),
                'c-purple-2'    => array('rgba(154, 72, 245)'),
            ));
        }

        if (in_array('cg', $show)) {
            $palette = array_merge($palette, array(
                'cg-gray'     => array('linear-gradient(to right, #373737, #8f8f8f)'),
                'cg-white'    => array('linear-gradient(to right, #fff, #acacac)'),
                'cg-red'      => array('linear-gradient(to right,rgb(249, 160, 164),rgb(212, 0, 85))'),
                'cg-red-2'    => array('linear-gradient(to right, #cb0d0d, #ff8ad3)'),
                'cg-yellow'   => array('linear-gradient(to right, #f5c06d, #dc2114)'),
                'cg-yellow-2' => array('linear-gradient(to right, #b1510c, #a8af2d)'),
                'cg-blue'     => array('linear-gradient(to right, #65b2f5, #115ebe)'),
                'cg-blue-2'   => array('linear-gradient(to right, #2854d8, #8771e9)'),
                'cg-cyan'     => array('linear-gradient(to right, #5cbebc, #0383aa)'),
                'cg-green'    => array('linear-gradient(to right, #70d37e, #54960d)'),
                'cg-green-2'  => array('linear-gradient(to right, #089406, #63caa9)'),
                'cg-purple'   => array('linear-gradient(to right, #de87e5, #7e15dd)'),
                'cg-purple-2' => array('linear-gradient(to right, #bd22dd, #aa82e8)'),
            ));
        }

        return $palette;
    }

    public static function gzh_menu()
    {
        $con = '<div class="options-notice"><div class="explain">
            <p>' . sprintf(__('微信公众号%s启用服务器之后，且微信登录功能正常后，可在此设置微信自定义菜单', 'zib_language'), '<code>' . esc_html__('已认证的服务号', 'zib_language') . '</code>') . '</p>
            <li>' . esc_html__('在下方粘贴公众号自定义菜单的json配置代码后提交即可', 'zib_language') . '</li>
            <li>' . esc_html__('如果失败，会直接显示微信返回的错误码，可按照错误进行分析处理', 'zib_language') . '</li>
            <li>' . esc_html__('设置好成功后会有几分钟的延迟才能生效，请耐心等待', 'zib_language') . '</li>
            <li><a target="_blank" href="https://www.zibll.com/2916.html">' . esc_html__('点此查看官网教程', 'zib_language') . '</a></li>
            <ajaxform class="ajax-form">
                <p><textarea ajax-name="json" row="5" placeholder="' . esc_attr__('请粘贴公众号自定义菜单的json配置代码', 'zib_language') . '" style="width: 100%;height: 299px;"></textarea></p>
                <div class="ajax-notice"></div>
                <p><a href="javascript:;" class="but jb-blue ajax-submit"><i class="fa fa-paper-plane-o"></i> ' . esc_html__('设置自定义菜单', 'zib_language') . '</a>
                <a href="javascript:;" ajax-url="' . zib_get_admin_ajax_url('weixin_gzh_get_menu') . '" class="but mr6 ajax-get">' . esc_html__('获取当前菜单', 'zib_language') . '</a>
                <a href="javascript:;" ajax-url="' . zib_get_admin_ajax_url('weixin_gzh_freepublish_batchget') . '" class="but mr6 ajax-get">' . esc_html__('获取已发布消息列表', 'zib_language') . '</a>
                </p>
                <input type="hidden" ajax-name="action" value="weixin_gzh_menu">
            </ajaxform>
        </div></div>';

        return array(
            'type'    => 'submessage',
            'style'   => 'warning',
            'content' => $con,
        );
    }

    public static function audit_test()
    {
        $test_img = get_template_directory_uri() . '/inc/csf-framework/assets/images/audit_test.jpg';

        $con = '<div class="options-notice">
        <div class="explain">
        <p>' . esc_html__('如果您已经完成了接口配置，可以在此处进行审核测试，只要有审核结果显示则表示接入正常', 'zib_language') . '</p>
        <p><b>· ' . esc_html__('文本审核测试', 'zib_language') . '</b></p>
        <ajaxform class="ajax-form">
        <p><textarea ajax-name="content" placeholder="' . esc_attr__('请输入一些内容进行测试', 'zib_language') . '" style="width: 100%;max-width: 500px;"></textarea></p>
        <div class="ajax-notice"></div>
        <p><a href="javascript:;" class="but jb-yellow ajax-submit"><i class="fa fa-paper-plane-o"></i> ' . esc_html__('文本测试', 'zib_language') . '</a></p>
        <input type="hidden" ajax-name="action" value="text_audit_test">
        </ajaxform>

        <p><b>· ' . esc_html__('使用以下示例图片进行图片审核测试', 'zib_language') . '</b></p>
        <ajaxform class="ajax-form">
        <p><img alt="' . esc_attr__('图片测试', 'zib_language') . '" src="' . $test_img . '" width="99" height="99"></p>
        <div class="ajax-notice"></div>
        <p><a href="javascript:;" class="but jb-yellow ajax-submit"><i class="fa fa-paper-plane-o"></i> ' . esc_html__('图片测试', 'zib_language') . '</a></p>
        <input type="hidden" ajax-name="action" value="img_audit_test">
        </ajaxform>

        </div></div>';

        return array(
            'type'    => 'submessage',
            'style'   => 'warning',
            'content' => $con,
        );
    }

    public static function email_test()
    {
        $con = '<div class="options-notice">
        <div class="explain">
        <p><b>' . esc_html__('您可以在下方测试邮件发送功能是否正常，请输入您的邮箱账号：', 'zib_language') . '</b></p>
        <ajaxform class="ajax-form">
        <div class="flex ac hh"><input class="mt6 mr10" type="text" style="max-width:300px;" ajax-name="email" value="' . get_option('admin_email') . '" placeholder="88888888@qq.com"><a href="javascript:;" class="but jb-yellow ajax-submit mt6"><i class="fa fa-paper-plane-o"></i> ' . esc_html__('发送测试邮件', 'zib_language') . '</a></div>
        <div class="ajax-notice mt6"></div>
        <input type="hidden" ajax-name="action" value="test_send_mail">
        </ajaxform>
        </div></div>';
        return array(
            'type'    => 'submessage',
            'style'   => 'warning',
            'content' => $con,
        );
    }

    public static function wechat_template_msg_args()
    {
        return array(
            //    'shop_auto_delivery_fail'          => [__('自动发货失败通知用户', 'zib_language'), 'status=失败说明&name=商品名称&num=订单号&time=付款时间'],
            'shop_notify_shipping_to_author'   => [__('通知商家发货', 'zib_language'), 'name=商品名称&num=订单号&time=付款时间&desc=发货说明'],
            'shop_express_shipping'            => [__('快递发货通知用户', 'zib_language'), 'name=商品名称&num=订单号&time=发货时间&express=快递公司&number=快递单号'],
            'shop_after_sale_to_author'        => [__('用户申请售后通知商家', 'zib_language'), 'name=商品名称&num=订单号&time=售后申请时间&type=售后类型&user=申请用户'],
            'shop_after_sale_wait_user_return' => [__('用户申请售后等待用户发货通知用户', 'zib_language'), 'name=商品名称&num=订单号&time=售后申请时间&type=售后类型&desc=处理说明'],
            'shop_after_sale_end'              => [__('售后订单处理完成通知用户', 'zib_language'), 'name=商品名称&num=订单号&time=售后申请时间&type=售后类型&end=处理完成时间&status=处理结果说明'],
            'payment_order'                    => [__('新订单通知用户', 'zib_language'), 'name=商品名称&price=金额&time=付款时间&num=订单号'],
            'payment_order_admin'              => [__('新订单通知管理员', 'zib_language'), 'name=商品名称&price=金额&user=购买用户&time=时间&num=订单号'],
            'payment_order_to_income'          => [__('获得创作分成通知作者', 'zib_language'), 'name=收入内容&price=收入金额&time=时间&num=订单号'],
            'payment_order_to_referrer'        => [__('获得推荐佣金通知推荐人', 'zib_language'), 'name=推广内容&price=收入金额&time=时间&num=订单号'],
            'apply_withdraw_admin'             => [__('用户申请提现通知管理员', 'zib_language'), 'user=申请用户&price=申请金额&time=时间'],
            'withdraw_process'                 => [__('提现处理后通知提现用户', 'zib_language'), 'create_time=申请时间&process_time=处理时间&status=处理结果|常量：已处理完成/被拒绝&price=申请金额&received_price=到账金额&service_price=手续费'],
            'auth_apply_admin'                 => [__('用户提交身份认证通知管理员', 'zib_language'), 'name=认证名称&time=认证时间&user=申请用户'],
            'auth_apply_process'               => [__('身份认证处理后通知用户', 'zib_language'), 'status=审核结果|常量：已通过/被拒绝&name=认证名称&desc=认证简介&time=处理时间'],
            'report_user_admin'                => [__('收到举报后通知管理员', 'zib_language'), 'user=被举报用户&time=举报时间&reason=举报原因&desc=举报详情'],
            'report_process'                   => [__('处理用户举报后通知举报人', 'zib_language'), 'reason=举报原因&time=举报时间&desc=处理说明'],
            'bind_phone'                       => [__('绑定或修改手机号通知用户', 'zib_language'), 'name=用户昵称&time=操作时间&num=手机号'],
            'bind_email'                       => [__('绑定或修改邮箱通知用户', 'zib_language'), 'name=用户昵称&time=操作时间&num=邮箱'],
            'comment_to_postauthor'            => [__('新评论通知文章作者', 'zib_language'), 'name=评论用户&time=评论时间&content=评论内容&post=文章标题'],
            'comment_to_parent'                => [__('评论有新回复通知用户', 'zib_language'), 'name=评论用户&time=评论时间&content=评论内容'],
        );
    }

    public static function wechat_template_id()
    {
        $args = array();

        foreach (self::wechat_template_msg_args() as $k => $v) {
            $args[] = array(
                'id'      => $k . '_s',
                'type'    => 'switcher',
                'default' => false,
                'title'   => $v[0],
            );
            $args[] = array(
                'dependency' => array($k . '_s', '!=', ''),
                'id'         => $k,
                'class'      => 'compact',
                'title'      => ' ',
                'subtitle'   => __('模板ID', 'zib_language'),
                'default'    => '',
                'type'       => 'text',
            );

            wp_parse_str($v[1], $keys);
            $o_keys = [];

            foreach ($keys as $id => $title) {
                $title_array = explode('|', $title);

                $o_keys[] = array(
                    'id'       => $id,
                    'type'     => 'text',
                    'class'    => 'compact mini-input',
                    'title'    => $title_array[0] ?? $title,
                    'subtitle' => $title_array[1] ?? '',
                );
            }

            $args[] = array(
                'dependency' => array($k . '_s', '!=', ''),
                'id'         => $k . '_keys',
                'title'      => ' ',
                'subtitle'   => __('模板参数', 'zib_language'),
                'type'       => 'fieldset',
                'class'      => 'mini-flex-repeater compact',
                'fields'     => $o_keys,
            );
        }

        return $args;
    }

    public static function wechat_template_test()
    {
        $type_option = '<option value="">' . esc_html__('请选择需测试的模板', 'zib_language') . '</option>';
        foreach (self::wechat_template_msg_args() as $k => $v) {
            $type_option .= '<option value="' . $k . '"> ' . esc_html($v[0]) . '</option>';
        }

        $con = '<div class="options-notice">
        <div class="explain">
        <p><b>' . esc_html__('微信公众号模板消息测试', 'zib_language') . '</b></p>
        <p>' . esc_html__('测试前请先保存配置，再确保您当前账号已扫码绑定微信公众号', 'zib_language') . '</p>
        <ajaxform class="ajax-form">
        <div class="ajax-notice"></div>
        <p class="flex ac hh"><select ajax-name="type" style="margin-right: 20px;">' . $type_option . '</select><a href="javascript:;" class="but jb-yellow ajax-submit"><i class="fa fa-paper-plane-o"></i> ' . esc_html__('提交测试', 'zib_language') . '</a></p>
        <input type="hidden" ajax-name="action" value="test_wechat_template_test">
        </ajaxform>
        </div></div>';
        return array(
            'type'    => 'submessage',
            'style'   => 'warning',
            'content' => $con,
        );
    }

    public static function vip_product()
    {
        return array(
            array(
                'id'      => 'price',
                'title'   => __('执行价', 'zib_language'),
                'default' => '699',
                'type'    => 'number',
                'unit'    => zibpay_get_currency_unit(),
            ),
            array(
                'id'      => 'show_price',
                'title'   => __('原价', 'zib_language'),
                'desc'    => __('显示在执行价格前面，并划掉', 'zib_language'),
                'default' => '999',
                'type'    => 'number',
                'unit'    => zibpay_get_currency_unit(),
                'class'   => 'compact',
            ),
            array(
                'id'         => 'tag',
                'title'      => __('促销标签', 'zib_language'),
                'class'      => 'compact',
                'desc'       => __('支持HTML，请注意控制长度', 'zib_language'),
                'attributes' => array(
                    'rows' => 1,
                ),
                'type'       => 'textarea',
            ),
            array(
                'dependency' => array('tag', '!=', ''),
                'title'      => __('标签颜色', 'zib_language'),
                'id'         => 'tag_class',
                'class'      => 'compact skin-color',
                'default'    => 'jb-yellow',
                'type'       => 'palette',
                'options'    => CFS_Module::zib_palette(array(), array('b', 'jb')),
            ),

            array(
                'title'   => __('会员有效时间', 'zib_language'),
                'id'      => 'time',
                'class'   => 'compact',
                'desc'    => sprintf(__('开通会员的时长。填%s则为永久会员', 'zib_language'), '<code>0</code>'),
                'default' => 3,
                'max'     => 3600,
                'min'     => 0,
                'step'    => 1,
                'unit'    => '',
                'type'    => 'spinner',
            ),
            array(
                'dependency' => array('time', '!=', ''),
                'title'      => ' ',
                'subtitle'   => __('时长单位', 'zib_language'),
                'id'         => 'unit',
                'class'      => 'compact',
                'type'       => 'radio',
                'default'    => 'month',
                'inline'     => true,
                'options'    => array(
                    'day'   => __('天', 'zib_language'),
                    'month' => __('月', 'zib_language'),
                ),
            ),
            array(
                'dependency' => array('time', '==', 0),
                'type'       => 'submessage',
                'style'      => 'success',
                'content'    => '<strong>' . sprintf(__('会员有效时间已设置为：%s', 'zib_language'), '<code>' . esc_html__('永久会员', 'zib_language') . '</code>') . '</strong>',
            ),
        );
    }

    public static function rebate_type()
    {
        return
        array(
            'all' => __('全部订单', 'zib_language'),
            '1'   => __('付费阅读', 'zib_language'),
            '2'   => __('付费资源', 'zib_language'),
            '4'   => __('购买会员', 'zib_language'),
            '5'   => __('付费图片', 'zib_language'),
            '6'   => __('付费视频', 'zib_language'),
            '9'   => __('购买积分', 'zib_language'),
        );
    }

    public static function slide($hide = array())
    {
        $args = array();
        return array(
            array(
                'id'      => 'direction',
                'default' => 'horizontal',
                'title'   => __('幻灯片方向', 'zib_language'),
                'inline'  => true,
                'type'    => 'radio',
                'options' => array(
                    'horizontal' => __('左右切换', 'zib_language'),
                    'vertical'   => __('上下切换', 'zib_language'),
                ),
            ),
            array(
                'title'   => __('循环切换', 'zib_language'),
                'class'   => 'compact',
                'id'      => 'loop',
                'default' => true,
                'type'    => 'switcher',
            ),
            array(
                'title'   => __('显示翻页按钮', 'zib_language'),
                'class'   => 'compact',
                'id'      => 'button',
                'default' => false,
                'type'    => 'switcher',
            ),
            array(
                'title'   => __('显示指示器', 'zib_language'),
                'type'    => 'switcher',
                'id'      => 'pagination',
                'class'   => 'compact',
                'default' => false,
                'type'    => 'switcher',
            ),
            array(
                'id'      => 'effect',
                'default' => 'slide',
                'class'   => 'compact',
                'title'   => __('切换动画', 'zib_language'),
                'type'    => 'select',
                'options' => array(
                    'slide'     => __('滑动', 'zib_language'),
                    'fade'      => __('淡出淡入', 'zib_language'),
                    'cube'      => __('3D方块', 'zib_language'),
                    'coverflow' => __('3D滑入', 'zib_language'),
                    'flip'      => __('3D翻转', 'zib_language'),
                ),
            ),
            array(
                'dependency' => array(
                    array('direction', '!=', 'vertical'),
                ),
                'title'      => __('按比例自动高度', 'zib_language'),
                'type'       => 'switcher',
                'id'         => 'scale_height',
                'default'    => true,
                'type'       => 'switcher',
            ),
            array(
                'dependency' => array(
                    array('direction', '!=', 'vertical'),
                    array('scale_height', '!=', ''),
                ),
                'id'         => 'scale',
                'class'      => 'compact',
                'title'      => __('长宽比例', 'zib_language'),
                'default'    => 35,
                'max'        => 300,
                'min'        => 10,
                'step'       => 5,
                'unit'       => '%',
                'type'       => 'spinner',
            ),
            array(
                'dependency' => array(
                    array('direction', '!=', 'vertical'),
                    array('scale_height', '!=', ''),
                ),
                'type'       => 'submessage',
                'style'      => 'warning',
                'content'    => '<i class="fa fa-info-circle fa-fw"></i> ' . __('开启按比例自动高度后，幻灯片会按照设置的比例保持高度', 'zib_language') . '<br>' . __('同时下方PC端高度和移动端高端将失效', 'zib_language'),
            ),
            array(
                'dependency' => array(
                    array('direction', '!=', 'vertical'),
                    array('scale_height', '==', ''),
                ),
                'title'      => __('自动高度', 'zib_language'),
                'class'      => 'compact',
                'type'       => 'switcher',
                'id'         => 'auto_height',
                'default'    => false,
                'type'       => 'switcher',
            ),
            array(
                'dependency' => array(
                    array('auto_height', '!=', ''),
                    array('direction', '!=', 'vertical'),
                    array('scale_height', '==', ''),
                ),
                'id'         => 'max_height',
                'class'      => 'compact',
                'title'      => __('最大高度', 'zib_language'),
                'default'    => 500,
                'max'        => 800,
                'min'        => 120,
                'step'       => 20,
                'unit'       => 'PX',
                'type'       => 'spinner',
            ),
            array(
                'dependency' => array(
                    array('auto_height', '!=', ''),
                    array('direction', '!=', 'vertical'),
                    array('scale_height', '==', ''),
                ),
                'id'         => 'min_height',
                'title'      => __('最小高度', 'zib_language'),
                'class'      => 'compact',
                'default'    => 180,
                'max'        => 500,
                'min'        => 100,
                'step'       => 20,
                'unit'       => 'PX',
                'type'       => 'spinner',
            ),
            array(
                'dependency' => array(
                    array('auto_height', '!=', ''),
                    array('direction', '!=', 'vertical'),
                    array('scale_height', '==', ''),
                ),
                'type'       => 'submessage',
                'style'      => 'warning',
                'content'    => '<i class="fa fa-info-circle fa-fw"></i> ' . __('开启自动高度后，会根据幻灯片背景图自动调节每张幻灯片高度', 'zib_language') . '<br>' . __('请注意幻灯片图片的长宽比例不能差距太大，否则会显示不佳！', 'zib_language') . '<br>' . __('请在上方设置最大、最小高度，避免幻灯片过大过小，同时下方的PC端高度和移动端高端将失效', 'zib_language'),
            ),
            array(
                'id'      => 'pc_height',
                'class'   => 'compact',
                'title'   => __('电脑端高度', 'zib_language'),
                'default' => 400,
                'max'     => 800,
                'min'     => 120,
                'step'    => 20,
                'unit'    => 'PX',
                'type'    => 'spinner',
            ),
            array(
                'id'      => 'm_height',
                'title'   => __('移动端高度', 'zib_language'),
                'class'   => 'compact',
                'default' => 200,
                'max'     => 500,
                'min'     => 100,
                'step'    => 20,
                'unit'    => 'PX',
                'type'    => 'spinner',
            ),
            array(
                'id'      => 'spacebetween',
                'title'   => __('幻灯片间距', 'zib_language'),
                'default' => 15,
                'max'     => 500,
                'min'     => 0,
                'step'    => 5,
                'unit'    => 'PX',
                'type'    => 'spinner',
            ),
            array(
                'id'       => 'speed',
                'title'    => __('切换速度', 'zib_language'),
                'subtitle' => __('切换过程的时间(越小越快)', 'zib_language'),
                'desc'     => __('设置为“0”，则为自动模式：根据幻灯片大小自动设置最佳速度', 'zib_language'),
                'class'    => 'compact',
                'default'  => 0,
                'max'      => 3000,
                'min'      => 0,
                'step'     => 100,
                'unit'     => __('毫秒', 'zib_language'),
                'type'     => 'spinner',
            ),
            array(
                'title'   => __('自动播放', 'zib_language'),
                'type'    => 'switcher',
                'id'      => 'autoplay',
                'class'   => 'compact',
                'default' => true,
                'type'    => 'switcher',
            ),
            array(
                'dependency' => array('autoplay', '!=', ''),
                'id'         => 'interval',
                'title'      => __('停顿时间', 'zib_language'),
                'subtitle'   => __('自动切换的时间间隔(越小越快)', 'zib_language'),
                'class'      => 'compact',
                'default'    => 4,
                'max'        => 20,
                'min'        => 0,
                'step'       => 1,
                'unit'       => __('秒', 'zib_language'),
                'type'       => 'spinner',
            ),
        );
    }

    public static function custom_filters_options()
    {}

    public static function orderby()
    {
        return array(
            array(
                'id'          => 'lists',
                'title'       => __('显示排序方式', 'zib_language'),
                'options'     => array(
                    'modified'            => __('更新', 'zib_language'),
                    'date'                => __('发布', 'zib_language'),
                    'views'               => __('浏览', 'zib_language'),
                    'like'                => __('点赞', 'zib_language'),
                    'comment_count'       => __('评论', 'zib_language'),
                    'favorite'            => __('收藏', 'zib_language'),
                    'zibpay_price'        => __('售价', 'zib_language'),
                    'zibpay_points_price' => __('积分', 'zib_language'),
                    'sales_volume'        => __('销量', 'zib_language'),
                    'rand'                => __('随机', 'zib_language'),
                ),
                'type'        => 'select',
                'placeholder' => __('选择需要的排序方式按钮', 'zib_language'),
                'default'     => array('modified', 'views', 'like', 'comment_count'),
                'chosen'      => true,
                'multiple'    => true,
                'sortable'    => true,
            ),
            array(
                'title'   => __('更多排序方式', 'zib_language'),
                'id'      => 'dropdown',
                'class'   => 'compact',
                'default' => false,
                'label'   => __('用下拉框显示全部排序方式', 'zib_language'),
                'type'    => 'switcher',
            ),
        );
    }

    public static function ajax_but($type = '')
    {
        $ajax       = true;
        $query_args = array();
        if ('topics' == $type) {
            $type       = 'tag';
            $query_args = array('taxonomy' => 'topics');
        }

        $desc = __('选择并排序需要显示的按钮', 'zib_language');
        $desc .= 'tags' == $type ? '' : __('，建议选择同级内容，会自动获取二级三级内容', 'zib_language') . '<br/>' . __('所选按钮内没有文章则不会显示', 'zib_language');

        $placeholder = $ajax ? __('输入关键词搜索内容', 'zib_language') : __('选择并排序需要显示的按钮', 'zib_language');
        return array(
            array(
                'id'          => 'lists',
                'title'       => __('按钮列表', 'zib_language'),
                'options'     => $type,
                'query_args'  => $query_args,
                'type'        => 'select',
                'placeholder' => $placeholder,
                'chosen'      => true,
                'desc'        => $desc,
                'multiple'    => true,
                'sortable'    => true,
                'ajax'        => $ajax,
                'settings'    => array(
                    'min_length' => 2,
                ),
            ),
            array(
                'title'   => __('下拉列表', 'zib_language'),
                'id'      => 'dropdown',
                'class'   => 'compact',
                'default' => false,
                'label'   => __('用下拉框显示更多内容', 'zib_language'),
                'type'    => 'switcher',
            ),
            array(
                'dependency'  => array('dropdown', '!=', ''),
                'id'          => 'dropdown_lists',
                'desc'        => __('请勿添加过多，避免显示很难看', 'zib_language'),
                'class'       => 'compact',
                'title'       => __('下拉菜单列表', 'zib_language'),
                'options'     => $type,
                'query_args'  => $query_args,
                'type'        => 'select',
                'placeholder' => $placeholder,
                'chosen'      => true,
                'multiple'    => true,
                'sortable'    => true,
                'ajax'        => $ajax,
                'settings'    => array(
                    'min_length' => 2,
                ),
            ),
        );
    }

    public static function invit_code_reward()
    {
        $args   = array();
        $args[] = array(
            'title'   => __('经验值', 'zib_language'),
            'id'      => 'level_integral',
            'default' => 0,
            'type'    => 'number',
            'unit'    => __('经验值', 'zib_language'),
        );
        $args[] = array(
            'title'   => __('积分', 'zib_language'),
            'id'      => 'points',
            'default' => 0,
            'class'   => 'compact',
            'type'    => 'number',
            'unit'    => __('积分', 'zib_language'),
        );
        $args[] = array(
            'title'   => __('余额', 'zib_language'),
            'id'      => 'balance',
            'default' => 0,
            'class'   => 'compact',
            'type'    => 'number',
            'unit'    => __('余额', 'zib_language'),
        );
        $args[] = array(
            'title'   => __('VIP会员', 'zib_language'),
            'id'      => 'vip',
            'type'    => 'radio',
            'inline'  => true,
            'default' => '',
            'options' => array(
                ''  => __('无', 'zib_language'),
                '1' => _pz('pay_user_vip_1_name'),
                '2' => _pz('pay_user_vip_2_name'),
            ),
        );
        $args[] = array(
            'dependency' => array('vip', '!=', ''),
            'title'      => ' ',
            'subtitle'   => __('会员赠送时长', 'zib_language'),
            'desc'       => sprintf(__('单位为天，填%s为永久', 'zib_language'), '<code>Permanent</code>'),
            'id'         => 'vip_time',
            'default'    => '',
            'class'      => 'compact',
            'type'       => 'text',
        );

        return $args;
    }

    public static function points_free()
    {
        $args   = array();
        $args[] = array(
            'content' => __('设置每个任务可免费获取的积分值', 'zib_language') . '<br><i class="fa fa-fw fa-info-circle fa-fw"></i> ' . __('如果以下对应的功能未开启，请将分值设置为0', 'zib_language'),
            'style'   => 'warning',
            'type'    => 'submessage',
            'class'   => 'text-center',
        );

        $args[] = array(
            'title'   => __('每日上限', 'zib_language'),
            'desc'    => __('一个用户每天最多可获取多少免费积分，请勿低于单项值(包含签到奖励)', 'zib_language'),
            'id'      => 'day_max',
            'default' => 100,
            'max'     => 1000,
            'min'     => 0,
            'step'    => 1,
            'type'    => 'spinner',
        );

        $group_k_2 = null;

        foreach (zib_get_user_integral_add_options() as $k => $v) {
            $group_k = $v[3];
            $args[]  = array(
                'title'   => '[' . $group_k . ']' . $v[0],
                'class'   => $group_k === $group_k_2 ? 'compact' : '',
                'id'      => $k,
                'default' => $v[1],
                'max'     => 1000,
                'min'     => 0,
                'step'    => 1,
                'type'    => 'spinner',
            );
            $group_k_2 = $v[3];
        }

        return $args;
    }

    public static function user_integral()
    {

        //分组
        $args   = array();
        $args[] = array(
            'content' => __('在此设置经验值的获取得分方式', 'zib_language') . '<br><i class="fa fa-fw fa-info-circle fa-fw"></i> ' . __('如果以下对应的功能未开启，请将分值设置为0', 'zib_language'),
            'style'   => 'warning',
            'type'    => 'submessage',
            'class'   => 'text-center',
        );

        $args[] = array(
            'title'   => __('每日上限', 'zib_language'),
            'desc'    => __('一个用户每天最多加多少经验值，请勿低于单项值(包含签到奖励)', 'zib_language'),
            'id'      => 'day_max',
            'default' => 100,
            'max'     => 1000,
            'min'     => 0,
            'step'    => 1,
            'type'    => 'spinner',
        );
        $group_k_2 = null;

        foreach (zib_get_user_integral_add_options() as $k => $v) {
            $group_k = $v[3];
            $args[]  = array(
                'title'   => '[' . $group_k . ']' . $v[0],
                'class'   => $group_k === $group_k_2 ? 'compact' : '',
                'id'      => $k,
                'default' => $v[1],
                'max'     => 1000,
                'min'     => 0,
                'step'    => 1,
                'type'    => 'spinner',
            );
            $group_k_2 = $v[3];
        }
        return $args;
    }

    public static function checkin_reward()
    {
        $tab = array();
        for ($i = 2; $i <= 7; $i++) {
            $tab[] = array(
                'title'  => sprintf(__('第%s天', 'zib_language'), $i),
                'fields' => array(
                    array(
                        'id'      => 'points_' . $i,
                        'title'   => __('奖励积分', 'zib_language'),
                        'default' => $i * 20,
                        'type'    => 'number',
                        'unit'    => __('积分', 'zib_language'),
                    ),
                    array(
                        'id'      => 'integral_' . $i,
                        'title'   => __('奖励经验值', 'zib_language'),
                        'default' => $i * 30,
                        'type'    => 'number',
                        'unit'    => __('经验值', 'zib_language'),
                        'class'   => 'compact',
                    ),
                ),
            );
        }
        return $tab;
    }

    public static function user_level_tab()
    {
        $max = _pz('user_level_max', 10);
        $tab = array();
        for ($i = 1; $i <= $max; $i++) {
            $tab[] = array(
                'title'  => 'Lv ' . $i,
                'fields' => array(
                    array(
                        'title'   => __('等级图标', 'zib_language'),
                        'id'      => 'icon_img_' . $i,
                        'desc'    => __('自定义等级的小图标，显示在昵称后方(建议尺寸120x50)', 'zib_language') . ($i > 10 ? '<br>' . __('主题内置了10个等级图标，如需开启更高等级需要自己制作等级图标', 'zib_language') : ''),
                        'default' => ($i < 11 ? ZIB_TEMPLATE_DIRECTORY_URI . '/img/user-level-' . $i . '.png' : ''),
                        'preview' => true,
                        'library' => 'image',
                        'type'    => 'upload',
                    ),
                    array(
                        'title'   => __('等级名称', 'zib_language'),
                        'id'      => 'name_' . $i,
                        'default' => 'LV' . $i,
                        'type'    => 'text',
                    ),
                    array(
                        'title'   => __('升级经验', 'zib_language'),
                        'class'   => ((1 === $i) ? 'hide' : ''),
                        'desc'    => __('当用户的等级经验值达到多少时，升级到此等级', 'zib_language') . '<div class="c-yellow"><i class="fa fa-fw fa-info-circle fa-fw"></i>' . __('经此验值必须高于上一级的经验值，否则会出现错误', 'zib_language') . '</div>',
                        'id'      => 'upgrade_integral_' . $i,
                        'default' => ($i - 1) * 500 * $i,
                        'min'     => 0,
                        'step'    => 50,
                        'type'    => 'number',
                    ),
                ),
            );
        }

        return $tab;
    }

    public static function user_can_type_options($exclude = array(), $add = array())
    {
        $options = array(
            'default' => __('最小默认值', 'zib_language'),
            'logged'  => __('已登录用户', 'zib_language'),
        );

        $level_max = _pz('user_level_max', 10);
        for ($i = 2; $i <= $level_max; $i++) {
            $options['user_level_' . $i] = sprintf(__('等级达到[%s级]', 'zib_language'), $i);
        }

        $vip_max = 2;
        for ($i = 1; $i <= $vip_max; $i++) {
            $options['vip_level_' . $i] = sprintf(__('VIP会员等级达到[%s级]', 'zib_language'), $i);
        }

        $options['auth']          = __('已认证用户', 'zib_language');
        $options['moderator']     = __('拥有版主身份', 'zib_language');
        $options['plate_author']  = __('拥有超级版主身份', 'zib_language');
        $options['cat_moderator'] = __('拥有分区版主身份', 'zib_language');
        $options['admin']         = __('超级管理员', 'zib_language');

        if ($exclude) {
            foreach ($exclude as $exclude_roles_key) {
                unset($options[$exclude_roles_key]);
            }
        }

        return array_merge($options, $add);
    }

    public static function user_can_user_fields()
    {
        $user_fields        = array();
        $user_fields['all'] = array(
            'title'   => __('所有人', 'zib_language'),
            'default' => false,
            'label'   => __('包含未登录的游客(开启后任何人都拥有此权限)', 'zib_language'),
            'class'   => 'compact mini',
            'id'      => 'all',
            'type'    => 'switcher',
        );
        $user_fields['logged'] = array(
            'title'   => __('已登录用户', 'zib_language'),
            'default' => false,
            'label'   => __('开启后只要用户登录就拥有此权限', 'zib_language'),
            'class'   => 'compact mini',
            'id'      => 'logged',
            'type'    => 'switcher',
        );

        $user_level_max = _pz('user_level_max', 10);
        if (_pz('user_level_s', true)) {
            $user_fields['level'] = array(
                'class'   => 'compact mini',
                'title'   => __('用户等级', 'zib_language'),
                'id'      => 'level',
                'default' => 0,
                'max'     => $user_level_max,
                'min'     => -1,
                'step'    => 1,
                'unit'    => __('级', 'zib_language'),
                'type'    => 'spinner',
            );
        }
        if (_pz('pay_user_vip_1_s', true)) {
            $user_fields['vip'] = array(
                'title'   => __('会员等级', 'zib_language'),
                'id'      => 'vip',
                'default' => 0,
                'max'     => 2,
                'min'     => -1,
                'class'   => 'compact mini',
                'step'    => 1,
                'unit'    => __('级', 'zib_language'),
                'type'    => 'spinner',
            );
        }
        $user_fields['auth'] = array(
            'title'   => __('认证用户', 'zib_language'),
            'default' => false,
            'id'      => 'auth',
            'class'   => 'compact mini',
            'type'    => 'switcher',
        );
        $user_fields['moderator'] = array(
            'title'   => __('版主', 'zib_language'),
            'default' => false,
            'class'   => 'compact mini',
            'id'      => 'moderator',
            'type'    => 'switcher',
        );
        $user_fields['plate_author'] = array(
            'title'   => __('超级版主', 'zib_language'),
            'default' => false,
            'label'   => __('版块作者', 'zib_language'),
            'class'   => 'compact mini',
            'id'      => 'plate_author',
            'type'    => 'switcher',
        );
        $user_fields['cat_moderator'] = array(
            'title'   => __('分区版主', 'zib_language'),
            'default' => false,
            'class'   => 'compact mini',
            'id'      => 'cat_moderator',
            'type'    => 'switcher',
        );
        return $user_fields;
    }

    public static function user_caps()
    {
        $new_badge                     = zib_get_csf_option_new_badge();
        $roles_all                     = array('all', 'logged', 'level', 'vip', 'auth', 'cat_moderator', 'plate_author', 'moderator');
        $user_all_caps                 = array();
        $user_all_caps['用户功能'] = array(
            array(
                'id'      => 'user_report',
                'name'    => __('举报其它用户(举报不良信息)', 'zib_language'),
                'help'    => __('此权限依赖于[用户举报]功能', 'zib_language'),
                'default' => array(
                    'logged' => true,
                ),
            ),
            array(
                'id'            => 'balance_transfer',
                'name'          => __('将[余额]转账给其他用户', 'zib_language') . $new_badge['7.4'],
                'desc'          => sprintf(__('需启用%1$s功能', 'zib_language'), '<a href="' . esc_url(zib_get_admin_csf_url('支付付费/余额充值')) . '">' . __('用户余额以及余额转账', 'zib_language') . '</a>'),
                'exclude_roles' => array('all'),
            ),
            array(
                'id'            => 'points_transfer',
                'name'          => __('将[积分]转账给其他用户', 'zib_language') . $new_badge['7.4'],
                'desc'          => sprintf(__('需启用%1$s功能', 'zib_language'), '<a href="' . esc_url(zib_get_admin_csf_url('用户互动/用户积分')) . '">' . __('用户积分以及积分转账', 'zib_language') . '</a>'),
                'exclude_roles' => array('all'),
            ),
        );
        $user_all_caps['用户操作,管理其他用户'] = array(
            array(
                'id'    => 'set_user_ban',
                'name'  => __('设置用户封禁状态(将其它用户封号、拉入小黑屋)', 'zib_language'),
                'roles' => array('cat_moderator'),
                'help'  => __('默认为管理员权限，论坛管理员也拥有此权限，此权限依赖于[用户封禁]功能', 'zib_language'),
            ),
            array(
                'id'    => 'medal_manually_set',
                'name'  => __('为用户授予徽章', 'zib_language'),
                'roles' => array('cat_moderator'),
                'help'  => __('默认为管理员权限，此权限依赖于[用户徽章]功能', 'zib_language'),
            ),
        );
        $user_all_caps['前台投稿'] = array(
            array(
                'id'      => 'new_post_add',
                'name'    => __('发布新的文章', 'zib_language'),
                'default' => array(
                    'logged' => true,
                ),
            ),
            array(
                'id'            => 'new_post_audit_no',
                'name'          => __('发布投稿无需审核直接发布', 'zib_language'),
                'exclude_roles' => array('all'),
            ),
            array(
                'id'            => 'new_post_audit_no_manual',
                'name'          => __('发布投稿无需[人工审核]直接发布', 'zib_language'),
                'desc'          => sprintf(__('需启用%1$s功能，API审核通过后直接发布', 'zib_language'), '<a href="' . esc_url(zib_get_admin_csf_url('扩展增强/api内容审核')) . '">' . __('api内容审核', 'zib_language') . '</a>'),
                'exclude_roles' => array('all'),
            ),
            array(
                'id'            => 'new_post_upload_img',
                'name'          => __('发布投稿允许在编辑器上传图片', 'zib_language'),
                'desc'          => sprintf(__('启用后在%1$s中设置批量上传和图片大小限制', 'zib_language'), '<a href="' . esc_url(zib_get_admin_csf_url('功能权限/上传权限')) . '">' . __('功能权限/上传权限', 'zib_language') . '</a>'),
                'exclude_roles' => array('all'),
            ),
            array(
                'id'            => 'new_post_upload_video',
                'name'          => __('发布投稿允许在编辑器上传视频', 'zib_language'),
                'desc'          => sprintf(__('启用后在%1$s中设置大小限制', 'zib_language'), '<a href="' . esc_url(zib_get_admin_csf_url('功能权限/上传权限')) . '">' . __('功能权限/上传权限', 'zib_language') . '</a>'),
                'exclude_roles' => array('all'),
            ),
            array(
                'id'            => 'new_post_iframe_video',
                'name'          => __('发布投稿允许在编辑器插入iframe嵌入视频', 'zib_language'),
                'exclude_roles' => array('all'),
                'default'       => array(
                    'logged' => true,
                ),
            ),
            array(
                'id'            => 'new_post_upload_file',
                'name'          => __('发布投稿允许在编辑器上传文件及插入附件下载模块', 'zib_language') . $new_badge['7.0'],
                'exclude_roles' => array('all'),
                'desc'          => sprintf(__('启用后在%1$s中设置大小限制', 'zib_language'), '<a href="' . esc_url(zib_get_admin_csf_url('功能权限/上传权限')) . '">' . __('功能权限/上传权限', 'zib_language') . '</a>'),
            ),
            array(
                'id'      => 'new_post_hide',
                'name'    => __('发布投稿允许在编辑器发布隐藏内容', 'zib_language'),
                'default' => array(
                    'logged' => true,
                ),
            ),
            array(
                'id'            => 'new_post_image_cover',
                'name'          => __('发帖允许设置帖子封面（图片封面）', 'zib_language'),
                'exclude_roles' => array('all'),
                'desc'          => __('设置封面时，用户可以上传内容，如果不想用户上传，请关闭此权限', 'zib_language'),
            ),
            array(
                'id'            => 'new_post_slide_cover',
                'name'          => __('发帖设置封面时候允许设置【幻灯片封面】', 'zib_language'),
                'exclude_roles' => array('all'),
                'desc'          => __('依赖于【发帖允许设置帖子封面（图片封面）】权限', 'zib_language'),
            ),
            array(
                'id'            => 'new_post_video_cover',
                'name'          => __('发帖设置封面时候允许设置【视频封面】', 'zib_language'),
                'exclude_roles' => array('all'),
                'desc'          => __('依赖于【发帖允许设置帖子封面（图片封面）】权限', 'zib_language'),
            ),
            array(
                'id'            => 'new_post_pay',
                'name'          => __('发布投稿允许在设置付费内容', 'zib_language'),
                'desc'          => sprintf(__('此功能建议与%1$s功能配合使用，如果未开启%1$s功能，则用户设置的付费收益全部属于站长', 'zib_language'), '<a href="' . esc_url(zib_get_admin_csf_url('支付付费/创作分成')) . '">' . __('创作分成', 'zib_language') . '</a>'),
                'exclude_roles' => array('all'),
            ),
            array(
                'id'            => 'new_post_pay_download',
                'name'          => __('发布投稿允许在设置【付费下载】', 'zib_language') . $new_badge['7.0'],
                'desc'          => __('依赖于上方的“发布投稿允许在设置付费内容”权限', 'zib_language'),
                'exclude_roles' => array('all'),
            ),
            array(
                'id'            => 'new_post_edit',
                'name'          => __('修改自己发布的投稿', 'zib_language'),
                'exclude_roles' => array('all'),
                'default'       => array(
                    'logged' => true,
                ),
            ),
            array(
                'id'            => 'new_post_delete',
                'name'          => __('删除自己发布的投稿', 'zib_language'),
                'exclude_roles' => array('all'),
            ),
        );

        $user_all_caps['评论'] = array(
            array(
                'id'      => 'comment_view',
                'name'    => __('查看评论', 'zib_language'),
                'default' => array(
                    'all' => true,
                ),
                'desc'    => __('拥有编辑自己文章或他人文章评论权限的用户会直接拥有此权限', 'zib_language'),
            ),
            array(
                'id'            => 'comment_edit',
                'name'          => __('修改自己发布的评论', 'zib_language'),
                'exclude_roles' => array('all'),
                'default'       => array(
                    'logged' => true,
                ),
            ),
            array(
                'id'            => 'comment_delete',
                'name'          => __('删除自己发布的评论', 'zib_language'),
                'exclude_roles' => array('all'),
            ),
            array(
                'id'   => 'comment_audit_no',
                'name' => __('发布评论无需审核直接发布', 'zib_language'),
                'desc' => __('此权限会完全覆盖wp设置-讨论中的审核规则，请谨慎开启', 'zib_language') . '<div class="c-yellow">' . __('拥有所在文章【批准、驳回评论权限】的用户直接拥有此权限', 'zib_language') . '</div>',
            ),
            array(
                'id'   => 'comment_audit_no_manual',
                'name' => __('发布评论无需[人工审核]直接发布', 'zib_language'),
                'desc' => sprintf(__('需启用%1$s功能，API审核通过后直接发布', 'zib_language'), '<a href="' . esc_url(zib_get_admin_csf_url('扩展增强/api内容审核')) . '">' . __('api内容审核', 'zib_language') . '</a>') . '<br/>' . sprintf(__('此审核优先级大于wp默认审核规则，如果API审核未通过，则按照wp默认(%1$s)规则进行判断', 'zib_language'), '<a href="' . esc_url(admin_url('options-discussion.php')) . '">' . __('wp设置-讨论', 'zib_language') . '</a>'),
            ),
        );
        $user_all_caps['评论管理,管理其他人发布的评论'] = array(
            array(
                'id'            => 'comment_set_topping_my_post',
                'name'          => __('设置【自己发布的文章(帖子)】下的评论的评论置顶', 'zib_language') . $new_badge['7.8'],
                'exclude_roles' => array('all'),
                'default'       => array(
                    'moderator'     => true,
                    'plate_author'  => true,
                    'cat_moderator' => true,
                ),
            ),
            array(
                'id'      => 'comment_set_topping_other',
                'name'    => __('设置自己管理下的帖子评论的评论置顶', 'zib_language') . $new_badge['7.8'],
                'roles'   => array('moderator', 'plate_author', 'cat_moderator'),
                'default' => array(
                    'cat_moderator' => true,
                ),
            ),
            array(
                'id'            => 'comment_edit_my_post',
                'name'          => __('修改【自己发布的文章(帖子)】下的评论', 'zib_language'),
                'exclude_roles' => array('all'),
            ),
            array(
                'id'      => 'comment_edit_other',
                'name'    => __('修改自己管理下的帖子评论', 'zib_language'),
                'roles'   => array('moderator', 'plate_author', 'cat_moderator'),
                'default' => array(
                    'cat_moderator' => true,
                ),
            ),
            array(
                'id'            => 'comment_audit_my_post',
                'name'          => __('审核(批准、驳回)【自己发布的文章(帖子)】下的评论', 'zib_language'),
                'exclude_roles' => array('all'),
                'default'       => array(
                    'moderator'     => true,
                    'plate_author'  => true,
                    'cat_moderator' => true,
                ),
            ),
            array(
                'id'      => 'comment_audit_other',
                'name'    => __('审核(批准、驳回)自己管理下的帖子评论', 'zib_language'),
                'roles'   => array('moderator', 'plate_author', 'cat_moderator'),
                'default' => array(
                    'moderator'     => true,
                    'plate_author'  => true,
                    'cat_moderator' => true,
                ),
            ),
            array(
                'id'            => 'comment_delete_my_post',
                'name'          => __('删除[自己发布的文章(帖子)]下的评论', 'zib_language'),
                'exclude_roles' => array('all'),
            ),
            array(
                'id'      => 'comment_delete_other',
                'name'    => __('删除自己管理下的帖子评论', 'zib_language'),
                'roles'   => array('moderator', 'plate_author', 'cat_moderator'),
                'default' => array(
                    'cat_moderator' => true,
                ),
            ),
        );

        return $user_all_caps;
    }

    //用户权限fields
    public static function user_can_fields($caps = array(), $con = '')
    {
        $fields   = array();
        $fields[] = array(
            'content' => $con . '<div style="color:#f97113;"><i class="fa fa-fw fa-info-circle fa-fw"></i>' . __('注意事项：', 'zib_language') . '
            <br/> ' . __('1、每一项用户能力（权限）启用都是按照自上而下的顺序设置，例如 用户评论：开启登录用户，那么只要登录的用户即可拥有此权限，就无需再设置等级或者其它', 'zib_language') . '
            <br/> ' . __('2、部分权限涉及到一些敏感功能，请注意相关风险！', 'zib_language') . '
            <br/> ' . __('3、由于功能权限较多，部分权限逻辑稍微复杂，请一定要仔细查看、设置！', 'zib_language') . '
            <br/> ' . __('4、设置的过程中如果出现混乱，可以重置选区将当前页面的用户权限恢复到初始值', 'zib_language') . '
            <br/><a target="_blank" href="https://www.zibll.com/3090.html">' . __('查看官方教程', 'zib_language') . '</a>
            </div>',
            'style'   => 'warning',
            'type'    => 'submessage',
        );

        $user_fields = self::user_can_user_fields();
        foreach ($caps as $group_key => $group) {
            //分组
            $caps = array();
            foreach ($group as $key => $val) {
                $_fields = $user_fields;
                if (isset($val['roles'])) {
                    $_fields = array();
                    foreach ($val['roles'] as $roles_key) {
                        $_fields[] = $user_fields[$roles_key];
                    }
                } elseif (isset($val['exclude_roles'])) {
                    foreach ($val['exclude_roles'] as $exclude_roles_key) {
                        unset($_fields[$exclude_roles_key]);
                    }
                }

                $caps[] = array(
                    'title'  => $val['name'],
                    'fields' => array(array(
                        'id'      => $val['id'],
                        'default' => isset($val['default']) ? $val['default'] : array(),
                        'desc'    => isset($val['desc']) ? $val['desc'] : '',
                        'help'    => isset($val['help']) ? $val['help'] : '',
                        'type'    => 'fieldset',
                        'fields'  => $_fields,
                    )),
                );
            }

            $group_title     = $group_key;
            $group_subtitle  = '';
            $group_key_array = explode(',', $group_key);
            if (isset($group_key_array[0])) {
                $group_title = __($group_key_array[0], 'zib_language');
            }
            if (isset($group_key_array[1])) {
                $group_subtitle = __($group_key_array[1], 'zib_language');
            }

            $fields[] = array(
                'id'         => 'user_cap',
                'type'       => 'accordion',
                'class'      => 'accordion-mini',
                'title'      => $group_title,
                'subtitle'   => $group_subtitle,
                'accordions' => $caps,
            );
        }

        return $fields;
    }

    public static function translate_language_options($show_auto = true)
    {

        $languages = array(
            'chinese_simplified'  => __('简体中文', 'zib_language'),
            'chinese_traditional' => __('繁体中文', 'zib_language'),
            'english'             => __('英语', 'zib_language'),
            'korean'              => __('韩语', 'zib_language'),
            'japanese'            => __('日语', 'zib_language'),
            'french'              => __('法语', 'zib_language'),
            'italian'             => __('意大利语', 'zib_language'),
            'deutsch'             => __('德语', 'zib_language'),
            'portuguese'          => __('葡萄牙语', 'zib_language'),
            'spanish'             => __('西班牙语', 'zib_language'),
            'russian'             => __('俄语', 'zib_language'),
            'arabic'              => __('阿拉伯语', 'zib_language'),
            'swedish'             => __('瑞典语', 'zib_language'),
            'turkish'             => __('土耳其语', 'zib_language'),
            'ukrainian'           => __('乌克兰语', 'zib_language'),
            'vietnamese'          => __('越南语', 'zib_language'),
            'afrikaans'           => __('南非荷兰语', 'zib_language'),
            'albanian'            => __('阿尔巴尼亚语', 'zib_language'),
            'amharic'             => __('阿姆哈拉语', 'zib_language'),
            'azerbaijani'         => __('阿塞拜疆语', 'zib_language'),
            'bengali'             => __('孟加拉语', 'zib_language'),
            'bosnian'             => __('波斯尼亚语', 'zib_language'),
            'bulgarian'           => __('保加利亚语', 'zib_language'),
            'burmese'             => __('缅甸语', 'zib_language'),
            'catalan'             => __('加泰罗尼亚语', 'zib_language'),
            'croatian'            => __('克罗地亚语', 'zib_language'),
            'czech'               => __('捷克语', 'zib_language'),
            'danish'              => __('丹麦语', 'zib_language'),
            'dutch'               => __('荷兰语', 'zib_language'),
            'estonian'            => __('爱沙尼亚语', 'zib_language'),
            'filipino'            => __('菲律宾语', 'zib_language'),
            'finnish'             => __('芬兰语', 'zib_language'),
            'greek'               => __('希腊语', 'zib_language'),
            'gujarati'            => __('古吉拉特语', 'zib_language'),
            'haitian_creole'      => __('海地克里奥尔语', 'zib_language'),
            'hebrew'              => __('希伯来语', 'zib_language'),
            'hindi'               => __('印地语', 'zib_language'),
            'hungarian'           => __('匈牙利语', 'zib_language'),
            'icelandic'           => __('冰岛语', 'zib_language'),
            'indonesian'          => __('印尼语', 'zib_language'),
            'irish'               => __('爱尔兰语', 'zib_language'),
            'kannada'             => __('卡纳达语', 'zib_language'),
            'khmer'               => __('高棉语', 'zib_language'),
            'lao'                 => __('老挝语', 'zib_language'),
            'latvian'             => __('拉脱维亚语', 'zib_language'),
            'lithuanian'          => __('立陶宛语', 'zib_language'),
            'malagasy'            => __('马尔加什语', 'zib_language'),
            'malay'               => __('马来语', 'zib_language'),
            'malayalam'           => __('马拉雅拉姆语', 'zib_language'),
            'marathi'             => __('马拉地语', 'zib_language'),
            'maltese'             => __('马耳他语', 'zib_language'),
            'nepali'              => __('尼泊尔语', 'zib_language'),
            'norwegian'           => __('挪威语', 'zib_language'),
            'oriya'               => __('奥里亚语', 'zib_language'),
            'persian'             => __('波斯语', 'zib_language'),
            'polish'              => __('波兰语', 'zib_language'),
            'punjabi'             => __('旁遮普语', 'zib_language'),
            'romanian'            => __('罗马尼亚语', 'zib_language'),
            'slovak'              => __('斯洛伐克语', 'zib_language'),
            'slovene'             => __('斯洛文尼亚语', 'zib_language'),
            'swahili'             => __('斯瓦希里语', 'zib_language'),
            'thai'                => __('泰语', 'zib_language'),
            'tamil'               => __('泰米尔语', 'zib_language'),
            'telugu'              => __('泰卢固语', 'zib_language'),
            'urdu'                => __('乌尔都语', 'zib_language'),
            'welsh'               => __('威尔士语', 'zib_language'),
        );

        if ($show_auto) {
            $languages['auto'] = __('自动识别', 'zib_language');
        }

        return $languages;
    }

    public static function vip_tab($level = 1)
    {
        return array(

            array(
                'dependency' => array('pay_user_vip_' . $level . '_s', '!=', '', 'all', 'visible'),
                'id'         => 'pay_user_vip_' . $level . '_desc',
                'title'      => _pz('pay_user_vip_' . $level . '_name') . __('一句话简介', 'zib_language'),
                'default'    => '尊享专属特权',
                'type'       => 'text',
            ),
            array(
                'dependency' => array('pay_user_vip_' . $level . '_s', '!=', '', 'all', 'visible'),
                'id'         => 'pay_user_vip_' . $level . '_equity',
                'title'      => __('会员特权', 'zib_language'),
                'subtitle'   => _pz('pay_user_vip_' . $level . '_name') . __('权益详情', 'zib_language'),
                'default'    => '<li>全站资源折扣购买</li>
<li>部分内容免费阅读</li>
<li>一对一技术指导</li>
<li>VIP用户专属QQ群</li>',
                'help'       => '',
                'attributes' => array(
                    'rows' => 4,
                ),
                'sanitize'   => false,
                'type'       => 'textarea',
            ),
            array(
                'dependency' => array('pay_user_vip_1_s', '!=', '', 'all', 'visible'),
                'id'         => 'vip_' . $level . '_product_s',
                'title'      => __('购买会员', 'zib_language'),
                'label'      => __('关闭后则不能付费购买会员，但可通过积分兑换会员，请确保已开启积分以及积分兑换会员功能', 'zib_language'),
                'default'    => true,
                'type'       => 'switcher',
            ),
            array(
                'dependency'             => array('pay_user_vip_' . $level . '_s|vip_' . $level . '_product_s', '!=|', '|', 'all', 'visible'),
                'id'                     => 'vip_' . $level . '_product',
                'title'                  => __('会员商品', 'zib_language'),
                'subtitle'               => _pz('pay_user_vip_' . $level . '_name') . __('的商品选项', 'zib_language'),
                'type'                   => 'group',
                'accordion_title_prefix' => __('价格：', 'zib_language'),
                'max'                    => 8,
                'button_title'           => __('添加会员商品', 'zib_language'),
                'class'                  => 'compact',
                'default'                => array(
                    array(
                        'price'      => '99',
                        'show_price' => '199',
                        'tag'        => '<i class="fa fa-fw fa-bolt"></i> 限时特惠',
                        'time'       => 3,
                        'unit'       => 'month',
                    ),
                    array(
                        'price'      => '199',
                        'show_price' => '299',
                        'tag'        => '<i class="fa fa-fw fa-bolt"></i> 站长推荐',
                        'time'       => 6,
                        'unit'       => 'month',
                    ),
                ),
                'fields'                 => CFS_Module::vip_product(),
            ),
            array(
                'dependency' => array('pay_user_vip_' . $level . '_s', '!=', '', 'all', 'visible'),
                'title'      => __('会员图标', 'zib_language'),
                'id'         => 'vip_' . $level . '_img_icon',
                'desc'       => sprintf(__('自定义%s的图标，(建议尺寸300x300)', 'zib_language'), _pz('pay_user_vip_' . $level . '_name')),
                'default'    => ZIB_TEMPLATE_DIRECTORY_URI . '/img/vip-' . $level . '.svg',
                'preview'    => true,
                'library'    => 'image', 'type' => 'upload',
            ),
        );
    }

    public static function aut()
    {

        $aut_url = ZibAut::get_aut_url();
        if (ZibAut::is_aut()) {
            $get_aut_time = ZibAut::get_aut_time();
            $remaining    = '';
            $aut_time     = '';
            if ($get_aut_time === '') {
                $get_aut_time = __('永久授权', 'zib_language');
            } elseif ($get_aut_time) {
                //计算剩余时间
                $remaining_time = strtotime($get_aut_time) - current_time('timestamp');
                if ($remaining_time < 0) {
                    $remaining = '<span class="c-red badg">' . esc_html__('已过期', 'zib_language') . '</span>';
                } else {
                    $remaining_days = ceil($remaining_time / 86400);
                    $remaining      = '<span class="c-yellow badg">' . sprintf(__('剩余%s天', 'zib_language'), (string) $remaining_days) . '</span>';
                }
                $get_aut_time = date('Y-m-d', strtotime($get_aut_time));
            }

            if ($get_aut_time) {
                $aut_time = '<p class="flex jc"><span style=" width: 80px; opacity: .7; ">' . esc_html__('授权有效期', 'zib_language') . '</span><span class="c-blue badg">' . esc_html($get_aut_time) . '</span>' . $remaining . '</p>';
            }

            $con = '<div id="authorization_form" class="ajax-form">
            <div class="ok-icon"><svg t="1585712312243" class="icon" style="width: 1em; height: 1em;vertical-align: middle;fill: currentColor;overflow: hidden;" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="3845" data-spm-anchor-id="a313x.7781069.0.i0"><path d="M115.456 0h793.6a51.2 51.2 0 0 1 51.2 51.2v294.4a102.4 102.4 0 0 1-102.4 102.4h-691.2a102.4 102.4 0 0 1-102.4-102.4V51.2a51.2 51.2 0 0 1 51.2-51.2z m0 0" fill="#FF6B5A" p-id="3846"></path><path d="M256 13.056h95.744v402.432H256zM671.488 13.056h95.744v402.432h-95.744z" fill="#FFFFFF" p-id="3847"></path><path d="M89.856 586.752L512 1022.72l421.632-435.2z m0 0" fill="#6DC1E2" p-id="3848"></path><path d="M89.856 586.752l235.52-253.952h372.736l235.52 253.952z m0 0" fill="#ADD9EA" p-id="3849"></path><path d="M301.824 586.752L443.136 332.8h137.216l141.312 253.952z m0 0" fill="#E1F9FF" p-id="3850"></path><path d="M301.824 586.752l209.92 435.2 209.92-435.2z m0 0" fill="#9AE6F7" p-id="3851"></path></svg></div>
            <p style=" color: #0087e8; font-size: 15px; "><svg class="icon" style="width: 1em;height: 1em;vertical-align: -.2em;fill: currentColor;overflow: hidden;font-size: 1.4em;" viewBox="0 0 1024 1024"><path d="M492.224 6.72c11.2-8.96 26.88-8.96 38.016 0l66.432 53.376c64 51.392 152.704 80.768 243.776 80.768 27.52 0 55.104-2.624 81.92-7.872a30.08 30.08 0 0 1 24.96 6.4 30.528 30.528 0 0 1 11.008 23.424V609.28c0 131.84-87.36 253.696-228.288 317.824L523.52 1021.248a30.08 30.08 0 0 1-24.96 0l-206.464-94.08C151.36 862.976 64 741.12 64 609.28V162.944a30.464 30.464 0 0 1 36.16-29.888 425.6 425.6 0 0 0 81.92 7.936c91.008 0 179.84-29.504 243.712-80.768z m19.008 62.528l-47.552 38.208c-75.52 60.8-175.616 94.144-281.6 94.144-19.2 0-38.464-1.024-57.472-3.328V609.28c0 107.84 73.92 208.512 192.768 262.72l193.856 88.384 193.92-88.384c118.912-54.208 192.64-154.88 192.64-262.72V198.272a507.072 507.072 0 0 1-57.344 3.328c-106.176 0-206.144-33.408-281.728-94.08l-47.488-38.272z m132.928 242.944c31.424 0 56.832 25.536 56.832 56.832H564.544v90.944h121.92a56.448 56.448 0 0 1-56.384 56.384H564.48v103.424h150.272a56.832 56.832 0 0 1-56.832 56.832H365.056a56.832 56.832 0 0 1-56.832-56.832h60.608v-144c0-33.92 27.52-61.44 61.44-61.44v205.312h71.68V369.024H324.8c0-31.424 25.472-56.832 56.832-56.832z" p-id="4799"></path></svg> ' . esc_html__('恭喜您! 已完成授权', 'zib_language') . '</p>
                            <p class="flex jc"><span style=" width: 80px; opacity: .7; ">' . esc_html__('授权域名', 'zib_language') . '</span><span class="c-blue badg">' . esc_html($aut_url) . '</span></p>
                            ' . $aut_time . '
            <input type="hidden" ajax-name="action" value="admin_delete_aut">
            <a id="authorization_submit" class="but c-red ajax-submit">' . esc_html__('撤销授权', 'zib_language') . '</a>
            <div class="ajax-notice"></div>
            </div>';
        } else {

            $con = '<div id="authorization_form" class="ajax-form">
            <div class="ok-icon"><svg class="icon" style="font-size: 1.2em;width: 1em; height: 1em;vertical-align: middle;fill: currentColor;overflow: hidden;" viewBox="0 0 1024 1024"><path d="M880 502.3V317.1c0-34.9-24.4-66-60.8-77.4l-80.4-30c-37.8-14.1-73.4-32.9-105.7-55.7l-84.6-60c-19.2-15.2-47.8-15.2-67 0l-84.7 59.9c-32.3 22.8-67.8 41.6-105.7 55.7l-80.4 30c-36.4 11.4-60.8 42.5-60.8 77.4v185.2c0 123.2 63.9 239.2 172.5 313.2l158.5 108c20.2 13.7 47.9 13.7 68.1 0l158.5-108C816.1 741.6 880 625.5 880 502.3z" fill="#0DCEA7" p-id="17337"></path><path d="M150 317.1v3.8c13.4-27.6 30-53.3 49.3-76.7C169.4 258 150 286 150 317.1zM880 317.1c0-34.9-24.4-66-60.8-77.4l-43.5-16.2c57.7 60.6 95.8 140 104.2 228.1l0.1-134.5zM572.8 111.2L548.5 94c-19.2-15.2-47.8-15.2-67 0l-15.3 10.8c10-0.8 20.2-1.2 30.5-1.2 26 0.1 51.5 2.7 76.1 7.6zM496.7 873.9c-39.5 0-77.6-5.9-113.4-17l97.7 66.6c20.2 13.7 47.9 13.7 68.1 0l158.5-108c92.3-62.9 152.3-156.1 168.2-258.3C843.5 737.3 686 873.9 496.7 873.9z" fill="#0DCEA7" p-id="17338"></path><path d="M875.8 557.2c2.8-18.1 4.3-36.4 4.3-54.9v-50.8c-8.5-88.1-46.6-167.4-104.2-228.1L739 209.6c-37.8-14.1-73.4-32.9-105.7-55.7l-60.5-42.7c-24.6-4.9-50-7.5-76.1-7.5-10.3 0-20.4 0.4-30.5 1.2l-58.7 41.5c23.4-5.2 47.7-8 72.7-8 183.6 0 332.4 148.8 332.4 332.4S663.9 803 480.3 803c-170.8 0-311.5-128.9-330.2-294.7 2 121 65.6 234.5 172.4 307.2l60.8 41.4c35.9 11 74 17 113.4 17 189.3 0 346.8-136.6 379.1-316.7zM261.2 220.8l-50.4 18.8c-4 1.3-7.8 2.8-11.5 4.5-19.3 23.4-35.9 49.2-49.3 76.7v112.7c9.4-84.5 50.5-159.4 111.2-212.7z" fill="#1DD49C" p-id="17339"></path><path d="M480.3 803c183.6 0 332.4-148.8 332.4-332.4S663.9 138.3 480.3 138.3c-25 0-49.3 2.8-72.7 8l-10.7 7.6c-32.3 22.8-67.8 41.6-105.7 55.7l-30 11.2C200.5 274.1 159.4 349 150 433.6v68.8c0 2 0 4 0.1 6C168.8 674.1 309.5 803 480.3 803z m-16.4-630c154.4 0 279.6 125.2 279.6 279.6S618.3 732.2 463.9 732.2 184.3 607 184.3 452.6 309.5 173 463.9 173z" fill="#2DDB92" p-id="17340"></path><path d="M463.9 732.2c154.4 0 279.6-125.2 279.6-279.6S618.3 173 463.9 173 184.3 298.2 184.3 452.6s125.2 279.6 279.6 279.6z m-16.4-524.5c125.3 0 226.8 101.5 226.8 226.8S572.8 661.3 447.5 661.3 220.7 559.8 220.7 434.5s101.6-226.8 226.8-226.8z" fill="#3DE188" p-id="17341" data-spm-anchor-id="a313x.7781069.0.i7"></path><path d="M447.5 661.3c125.3 0 226.8-101.5 226.8-226.8S572.8 207.7 447.5 207.7 220.7 309.2 220.7 434.5s101.6 226.8 226.8 226.8z m-16.4-419c96.1 0 174 77.9 174 174s-77.9 174-174 174-174-77.9-174-174 77.9-174 174-174z" fill="#4CE77D" p-id="17342"></path><path d="M431.1 590.4c96.1 0 174-77.9 174-174s-77.9-174-174-174-174 77.9-174 174 77.9 174 174 174zM414.7 277c67 0 121.3 54.3 121.3 121.3s-54.3 121.3-121.3 121.3-121.3-54.3-121.3-121.3S347.8 277 414.7 277z" fill="#5CEE73" p-id="17343"></path><path d="M414.7 398.3m-121.3 0a121.3 121.3 0 1 0 242.6 0 121.3 121.3 0 1 0-242.6 0Z" fill="#6CF468" p-id="17344"></path><path d="M515 100.7c8.3 0 16.2 2.7 22.3 7.5l0.4 0.3 0.4 0.3 84.7 59.9c33.5 23.7 70.5 43.2 109.8 57.9l80.4 30 0.4 0.2 0.5 0.1c28.8 9.1 48.2 33.3 48.2 60.3v185.2c0 28.9-3.7 57.8-11.1 86-7.3 27.8-18.1 54.8-32.2 80.4-14.1 25.6-31.5 49.8-51.7 71.8-20.5 22.4-43.9 42.6-69.6 60.1L539 908.6c-6.8 4.6-15.3 7.2-23.9 7.2s-17.1-2.6-23.9-7.2l-158.5-108c-25.7-17.5-49.1-37.7-69.6-60.1-20.2-22-37.6-46.2-51.7-71.8-14.1-25.6-24.9-52.6-32.2-80.4-7.4-28.1-11.1-57-11.1-86V317.1c0-27 19.4-51.2 48.2-60.3l0.5-0.1 0.4-0.2 80.4-30c39.3-14.7 76.2-34.1 109.8-57.9l84.7-59.9 0.4-0.3 0.4-0.3c5.9-4.8 13.9-7.4 22.1-7.4m0-18c-11.9 0-23.9 3.8-33.5 11.4L396.8 154c-32.3 22.8-67.8 41.6-105.7 55.7l-80.4 30c-36.4 11.4-60.8 42.5-60.8 77.4v185.2c0 123.2 63.9 239.2 172.5 313.2l158.5 108c10.1 6.9 22.1 10.3 34 10.3 12 0 24-3.4 34-10.3l158.5-108c108.6-74 172.5-190 172.5-313.2V317.1c0-34.9-24.4-66-60.8-77.4l-80.4-30c-37.8-14.1-73.4-32.9-105.7-55.7l-84.5-60c-9.6-7.5-21.5-11.3-33.5-11.3z" fill="#0EC69A" p-id="17345"></path><path d="M688.8 496.7V406c0-17.1-11.6-32.3-28.9-37.9l-38.3-14.7c-18-6.9-35-16.1-50.3-27.3L531 296.8c-9.1-7.4-22.8-7.4-31.9 0l-40.3 29.3a218.45 218.45 0 0 1-50.3 27.3l-38.3 14.7c-17.3 5.6-28.9 20.8-28.9 37.9v90.7c0 60.3 30.4 117.1 82.1 153.3l75.5 52.9c9.6 6.7 22.8 6.7 32.4 0l75.5-52.9c51.6-36.2 82-93 82-153.3z" fill="#9CFFBD" p-id="17346"></path><path d="M325.6 287.5c-7.2 0-14.1-4.4-16.8-11.6-3.5-9.3 1.1-19.7 10.4-23.2 68.5-26.2 110.5-60.3 110.9-60.6 7.7-6.3 19-5.2 25.3 2.5s5.2 19-2.5 25.3c-1.9 1.5-47 38.2-120.9 66.4-2.1 0.8-4.2 1.2-6.4 1.2z" fill="#FFFFFF" p-id="17347"></path><path d="M260.2 311.7c-7.3 0-14.2-4.5-16.9-11.7-3.5-9.3 1.3-19.7 10.6-23.1l10.5-3.9c9.3-3.5 19.7 1.3 23.1 10.6 3.5 9.3-1.3 19.7-10.6 23.1l-10.5 3.9c-2.1 0.7-4.2 1.1-6.2 1.1z" fill="#FFFFFF" p-id="17348"></path></svg></div>
            <p style="color:#fd4c73;">' . esc_html__('激动人心的时候到了！即将开启优雅的建站之旅！', 'zib_language') . '</p>
            <div>
                <p class="flex jc"><span style=" width: 80px; opacity: .7; ">' . esc_html__('授权域名', 'zib_language') . '</span><span class="c-blue badg">' . esc_html($aut_url) . '</span></p>
                <input class="regular-text" type="text" ajax-name="aut_code" value="" placeholder="' . esc_attr__('请输入授权码', 'zib_language') . '">
                <input type="hidden" ajax-name="action" value="admin_curl_aut">
            </div>
            <a id="authorization_submit" class="but c-blue ajax-submit curl-aut-submit" data-depend-id="zib_submit_aut">' . esc_html__('立即授权', 'zib_language') . '</a>
            <div class="ajax-notice"></div>
            </div>';
        }
        if (!ZibAut::is_local()) {
            return array(
                'type'    => 'content',
                'content' => $con,
            );
        } else {
            return array(
                'type'    => 'content',
                'style'   => 'info',
                'content' => '<div id="authorization_form">
            <div class="ok-icon"><svg t="1585712312243" class="icon" style="width: 1em; height: 1em;vertical-align: middle;fill: currentColor;overflow: hidden;" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="3845" data-spm-anchor-id="a313x.7781069.0.i0"><path d="M115.456 0h793.6a51.2 51.2 0 0 1 51.2 51.2v294.4a102.4 102.4 0 0 1-102.4 102.4h-691.2a102.4 102.4 0 0 1-102.4-102.4V51.2a51.2 51.2 0 0 1 51.2-51.2z m0 0" fill="#FF6B5A" p-id="3846"></path><path d="M256 13.056h95.744v402.432H256zM671.488 13.056h95.744v402.432h-95.744z" fill="#FFFFFF" p-id="3847"></path><path d="M89.856 586.752L512 1022.72l421.632-435.2z m0 0" fill="#6DC1E2" p-id="3848"></path><path d="M89.856 586.752l235.52-253.952h372.736l235.52 253.952z m0 0" fill="#ADD9EA" p-id="3849"></path><path d="M301.824 586.752L443.136 332.8h137.216l141.312 253.952z m0 0" fill="#E1F9FF" p-id="3850"></path><path d="M301.824 586.752l209.92 435.2 209.92-435.2z m0 0" fill="#9AE6F7" p-id="3851"></path></svg></div>
            <p style="color:#1a7cf3;margin: 30px 1px;">' . esc_html__('您当前正处于本地环境，暂时无需授权！', 'zib_language') . '</p>
            </div>',
            );
        }
    }

    public static function backup()
    {
        $csf            = array();
        $prefix         = 'zibll_options';
        $options        = get_option($prefix . '_backup');
        $lists          = __('暂无备份数据！', 'zib_language');
        $admin_ajax_url = admin_url('admin-ajax.php', 'relative');
        $delete_but     = '';

        $type_names = array(
            '重置全部 自动备份' => __('重置全部 自动备份', 'zib_language'),
            '重置选区 自动备份' => __('重置选区 自动备份', 'zib_language'),
            '更新主题 自动备份' => __('更新主题 自动备份', 'zib_language'),
            '定期自动备份'    => __('定期自动备份', 'zib_language'),
            '手动备份'      => __('手动备份', 'zib_language'),
        );

        if ($options) {
            $lists   = '';
            $options = array_reverse($options);
            $count   = 0;
            foreach ($options as $key => $val) {
                $ajax_url = add_query_arg('key', $key, $admin_ajax_url);
                $del      = '<a href="javascript:;" ajax-url="' . add_query_arg('action', 'options_backup_delete', $ajax_url) . '" data-confirm="' . esc_attr(sprintf(__('确认要删除此备份[%s]？删除后不可恢复！', 'zib_language'), $key)) . '" class="but c-yellow ajax-get ml10">' . esc_html__('删除', 'zib_language') . '</a>';
                $restore  = '<a href="javascript:;" ajax-url="' . add_query_arg('action', 'options_backup_restore', $ajax_url) . '" data-confirm="' . esc_attr(sprintf(__('确认将主题设置恢复到此备份吗？[%s]？', 'zib_language'), $key)) . '" class="but c-blue ajax-get ml10">' . esc_html__('恢复', 'zib_language') . '</a>';
                $lists .= '<div class="backup-item flex ac jsb">';
                $lists .= '<div class="item-left"><div>' . $val['time'] . '</div><div> [' . ($type_names[$val['type']] ?? $val['type']) . ']</div></div>';
                $lists .= '<span class="shrink-0">' . $restore . $del . '</span>';
                $lists .= '</div>';
                $count++;
            }
            if ($count > 3) {
                $delete_but = '<a href="javascript:;" ajax-url="' . add_query_arg(array('action' => 'options_backup_delete_surplus', 'key' => 'all'), $admin_ajax_url) . '" data-confirm="' . esc_attr__('确认要删除多余的备份数据吗？删除后不可恢复！', 'zib_language') . '" class="but jb-red ajax-get">' . esc_html__('删除备份 保留最新三份', 'zib_language') . '</a>';
            }
        }
        $csf[] = array(
            'type'    => 'submessage',
            'style'   => 'warning',
            'content' => '<h3 style="color:#fd4c73;"><i class="csf-tab-icon fa fa-fw fa-copy"></i> ' . esc_html__('备份&恢复', 'zib_language') . '</h3>
            <ajaxform class="ajax-form">
            <div style="margin:10px 0">
            <p>' . esc_html__('系统会在重置、更新等重要操作时自动备份主题设置，您可以此进行恢复备份或手动备份', 'zib_language') . '</p>
            <p>' . esc_html__('恢复备份后，请先保存一次主题设置，然后刷新后再做其它操作！', 'zib_language') . '</p>
            <p class="c-yellow">' . esc_html__('系统最多只能保存20次备份，如需长期保存，请手动下载后存留', 'zib_language') . '</p>
            <p class="c-yellow">' . esc_html__('请注意：恢复非当前网站或非当前主题版本的备份数据，可能会出现异常', 'zib_language') . '</p>
            <p><b>' . esc_html__('备份列表：', 'zib_language') . '</b></p>
            <div class="card-box backup-box">
            ' . $lists . '
            </div>
            </div>
            <a href="javascript:;" ajax-url="' . add_query_arg('action', 'options_backup', $admin_ajax_url) . '" class="but jb-blue ajax-get">' . esc_html__('备份当前配置', 'zib_language') . '</a>
            ' . $delete_but . '
            <div class="ajax-notice" style="margin-top: 10px;"></div>
            </ajaxform>',
        );

        $csf[] = array(
            'type'    => 'submessage',
            'style'   => 'warning',
            'content' => '<h3 style="color:#fd4c73;"><i class="csf-tab-icon fa fa-fw fa-copy"></i> ' . esc_html__('导入&导出', 'zib_language') . '</h3>
            <ajaxform class="ajax-form">
            <div style="margin:10px 0">
            <p>' . esc_html__('您可以在此处将主题配置导出为json文件，同时也可以使用json格式的配置内容进行配置导入，导入时请确保json格式正确', 'zib_language') . '</p>
            <textarea ajax-name="import_data" style="width: 100%;min-height: 200px;" placeholder="' . esc_attr__('粘贴导出的json数据以进行导入', 'zib_language') . '"></textarea>
            </div>
            <input type="hidden" ajax-name="action" value="options_import">
            ' . zib_nonce_field('options_import') . '
            <a href="javascript:;" class="but jb-yellow ajax-submit"><i class="fa fa-paper-plane-o"></i> ' . esc_html__('导入配置', 'zib_language') . '</a>
            <a href="' . add_query_arg(array('action' => 'csf-export', 'unique' => $prefix, 'nonce' => wp_create_nonce('csf_backup_nonce')), $admin_ajax_url) . '" class="but jb-green" target="_blank">' . esc_html__('导出当前配置', 'zib_language') . '</a>
            <div class="ajax-notice" style="margin-top: 10px;"></div>
            </ajaxform>',
        );

        return $csf;
    }

    public static function update($is_update_data = 'null')
    {
        $csf           = array();
        $data          = $is_update_data !== 'null' ? $is_update_data : ZibAut::is_update();
        $theme_data    = wp_get_theme();
        $theme_version = $theme_data['Version'];

        if ($data) {
            $notice = '<div class="ajax-form">
                        <p style="color:#ff2f86"><i class="csf-tab-icon fa fa-cloud-upload fa-2x"></i></p>
                        <p><b>' . sprintf(__('当前主题版本：V%s，可更新到最新版本：%s', 'zib_language'), $theme_version, '<code style="color:#ff1919;background: #fbeeee; font-size: 16px; ">V' . esc_html($data['version']) . '</code>') . '</b></p>'
            . ($data['update_description'] ? '<p>' . $data['update_description'] . '</p>' : '') . '
                        <div>
                            <input type="hidden" ajax-name="action" value="admin_skip_update">
                            <div class="progress"><div class="progress-bar"></div></div>
                            <p class="ajax-notice"></p>
                            <a href="javascript:;" class="but jb-blue mr10 online-update"><i class="fa fa-cloud-download fa-fw"></i> ' . esc_html__('在线更新', 'zib_language') . '</a><a href="javascript:;" class="but c-yellow ajax-submit"><i class="fa fa-ban fa-fw"></i> ' . esc_html__('忽略此次更新', 'zib_language') . '</a>
                        </div>
                        <div style="text-align: right;font-size: 12px;opacity: .5;"><a style="color: inherit;" target="_blank" href="https://www.zibll.com/1411.html">' . esc_html__('遇到问题？点此查看官网教程', 'zib_language') . '</a></div>
                    </div>';

            $log = '<div class="box-theme">';
            $log .= $data['update_content'];
            $log .= '</div><div><a class="but c-blue" target="_blank" href="https://www.zibll.com/375.html">' . esc_html__('查看更多更新日志', 'zib_language') . '</a></div>';
            $csf[] = array(
                'type'    => 'notice',
                'style'   => 'info',
                'content' => $notice,
            );
            $csf[] = array(
                'title'   => __('更新日志', 'zib_language'),
                'type'    => 'content',
                'content' => $log,
            );
        } else {
            $notice = '<div class="ajax-form">
            <h3 class="c-red"><i class="fa fa-thumbs-o-up fa-fw" aria-hidden="true"></i> ' . esc_html__('当前主题已经是最新版啦', 'zib_language') . '</h3>
            <p><b>' . sprintf(__('当前主题版本：V%s', 'zib_language'), wp_get_theme()['Version']) . ' </b></p>
            <p class="ajax-notice"></p>
            <p><a href="javascript:;" class="but jb-blue ajax-submit">' . esc_html__('检测更新', 'zib_language') . '</a></p>
            <input type="hidden" ajax-name="action" value="admin_detect_update">
            </div>';

            $docs = '<div class="flex hh zibll-doscs">';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/375.html">' . esc_html__('Zibll子比主题历史更新日志', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/3025.html">' . esc_html__('网站伪静态及固定链接设置教程-解决404错误问题', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/43082.html">' . esc_html__('Meilisearch搜索配置教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/47233.html">' . esc_html__('多语言智能翻译配置教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/46856.html">' . esc_html__('常见CDN缓存加速配置教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/46784.html">' . esc_html__('论坛付费板块、付费圈子配置教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/15886.html">' . esc_html__('文章高级筛选分类教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/15873.html">' . esc_html__('评论及用户显示IP归属地教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/951.html">' . esc_html__('网址导航页面创建教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/39206.html">' . esc_html__('商城快递查询接口配置教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/39214.html">' . esc_html__('商城首页创建教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/39217.html">' . esc_html__('商品参数继承及配置教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/39221.html">' . esc_html__('商品详情页布局教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/39144.html">' . esc_html__('商城系统入门教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/26764.html">' . esc_html__('商品优惠码配置教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/25892.html">' . esc_html__('网站背景图配置教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/13972.html">' . esc_html__('文件上传格式、大小限制配置教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/11852.html">' . esc_html__('视频封面图集封面配置教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/10537.html">' . esc_html__('用户徽章系统配置教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/8663.html">' . esc_html__('卡密充值到余额功能教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/655.html">' . esc_html__('接入支付宝收款接口教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/7327.html">' . esc_html__('用户发布付费内容参与创作分成教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/18579.html">' . esc_html__('积分转账、余额转账功能教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/7342.html">' . esc_html__('余额充值、余额支付功能教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/7349.html">' . esc_html__('积分、签到功能教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/?s=人机验证">' . esc_html__('人机验证相关功能教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/3090.html">' . esc_html__('用户权限管理系统使用教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/8673.html">' . esc_html__('用户邀请码注册功能使用教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/zibll_word/%e7%a4%be%e5%8c%ba%e8%ae%ba%e5%9d%9b">' . esc_html__('社区论坛系列教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/41904.html">' . esc_html__('论坛帖子推荐指数排序方式详解', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/2997.html">' . esc_html__('API内容审核使用教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/2983.html">' . esc_html__('手机底部TAB栏目配置教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/2976.html">' . esc_html__('用户等级系统配置教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/2956.html">' . esc_html__('用户身份认证功能使用教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/1816.html">' . esc_html__('网站布局设置、模块配置教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/2290.html">' . esc_html__('第三方账号登录：代理登录教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/2234.html">' . esc_html__('主题强大漂亮的代码高亮功能教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/1802.html">' . esc_html__('添加广告位教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/1717.html">' . esc_html__('文章目录树使用教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/?s=短信">' . esc_html__('短信验证码功能相关教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/?s=视频">' . esc_html__('视频功能、视频剧集功能教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/1071.html">' . esc_html__('推广返佣、推荐奖励使用教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/1246.html">' . esc_html__('新版幻灯片使用教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/1244.html">' . esc_html__('消息系统-站内通知-用户私信功能详解', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/1012.html">' . esc_html__('导航菜单添加自定义徽章及多种样式菜单教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/1222.html">' . esc_html__('正确使用自定义代码示例及教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/8681.html">' . esc_html__('微信公众号模板消息推送教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/2916.html">' . esc_html__('微信公众号配置自定义菜单教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/7297.html">' . esc_html__('微信分享有图教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/2206.html">' . esc_html__('主题接入微信登录图文教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/1001.html">' . esc_html__('主题接入Github登录图文教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/979.html">' . esc_html__('主题接入QQ登录图文教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/958.html">' . esc_html__('文章列表显示模式设置教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/886.html">' . esc_html__('海报分享功能详细教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/2195.html">' . esc_html__('古腾堡编辑器-在文章中插入TAB栏目教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/860.html">' . esc_html__('古腾堡编辑器-在文章中插入其他文章卡片教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/853.html">' . esc_html__('古腾堡编辑器-隐藏内容模块使用教程>', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/767.html">' . esc_html__('主题VIP会员系统详细使用教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/720.html">' . esc_html__('邮件SMTP发送邮件教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/689.html">' . esc_html__('编辑器增强-古腾堡编辑器块入门详解', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/683.html">' . esc_html__('强大的图片灯箱功能详解', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/675.html">' . esc_html__('使用古腾堡块在文章中插入幻灯片教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/580.html">' . esc_html__('主题付费阅读、付费资源功能详解', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/529.html">' . esc_html__('主题导航菜单设置教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/519.html">' . esc_html__('主题常用功能设置教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/498.html">' . esc_html__('主题前端显示配置教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/18629.html">' . esc_html__('WordPress换域名教程', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/46.html">' . esc_html__('主题详细安装教程/更新教程/首次配置指南', 'zib_language') . '</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/zibll_word">' . esc_html__('更多主题文档及教程', 'zib_language') . '</a></li>';
            $docs .= '</div>';

            $docs .= '<style>.zibll-doscs>li{  min-width: calc(50% - 14px);margin-left: 14px;}</style>';

            $csf[] = array(
                'type'    => 'notice',
                'style'   => 'info',
                'content' => $notice,
            );

            $db_update = get_option('zibll_new_version'); //不能使用zib_get_option
            if (!empty($db_update['update_content'])) {
                $csf[] = array(
                    'type' => 'tabbed',
                    'id'   => 'theme_text',
                    'tabs' => array(
                        array(
                            'title'  => __('主题文档', 'zib_language'),
                            'icon'   => 'fa fa-file-text-o fa-fw',
                            'fields' => array(
                                array(
                                    'title'   => __('主题文档', 'zib_language'),
                                    'type'    => 'content',
                                    'style'   => 'success',
                                    'content' => $docs,
                                ),
                            ),
                        ),
                        array(
                            'title'  => __('更新日志', 'zib_language'),
                            'icon'   => 'fa fa-cloud-upload fa-fw',
                            'fields' => array(
                                array(
                                    'title'   => __('更新日志', 'zib_language'),
                                    'type'    => 'content',
                                    'style'   => 'success',
                                    'content' => ($db_update['update_description'] ? '<p>' . $db_update['update_description'] . '</p>' : '') . $db_update['update_content'] . '<p><a class="but c-blue" target="_blank" href="https://www.zibll.com/375.html">' . esc_html__('查看更多更新日志', 'zib_language') . '</a></p>',
                                ),
                            ),
                        ),
                    ),
                );
            } else {
                $csf[] = array(
                    'title'   => __('主题文档', 'zib_language'),
                    'type'    => 'content',
                    'style'   => 'success',
                    'content' => $docs,
                );
            }
        }

        $csf[] = array(
            'title'   => __('系统环境', 'zib_language'),
            'type'    => 'content',
            'content' => '<div style="margin-left:14px;"><li><strong>' . esc_html__('操作系统', 'zib_language') . '</strong>： ' . PHP_OS . ' </li>
            <li><strong>' . esc_html__('运行环境', 'zib_language') . '</strong>： ' . $_SERVER['SERVER_SOFTWARE'] . ' </li>
            <li><strong>' . esc_html__('PHP版本', 'zib_language') . '</strong>： ' . PHP_VERSION . ' </li>
            <li><strong>' . esc_html__('PHP上传限制', 'zib_language') . '</strong>： ' . ini_get('upload_max_filesize') . ' ' . esc_html__('（推荐50M及以上）', 'zib_language') . '</li>
            <li><strong>' . esc_html__('PHP内存限制', 'zib_language') . '</strong>： ' . ini_get('memory_limit') . ' ' . esc_html__('（推荐1024M及以上）', 'zib_language') . '</li>
            <li><strong>' . esc_html__('WordPress版本', 'zib_language') . '</strong>： ' . get_bloginfo('version') . '</li>
            <li><strong>' . esc_html__('系统信息', 'zib_language') . '</strong>： ' . php_uname() . ' </li>
            <li><strong>' . esc_html__('服务器时间', 'zib_language') . '</strong>： ' . current_time('mysql') . '</li></div>
            <a class="but c-yellow" href="' . admin_url('site-health.php?tab=debug') . '">' . esc_html__('查看更多系统信息', 'zib_language') . '</a>',
        );
        $csf[] = array(
            'title'   => __('推荐环境', 'zib_language'),
            'type'    => 'content',
            'content' => '<div style="margin-left:14px;"><li><strong>WordPress</strong>：5.0+，' . esc_html__('推荐使用最新版', 'zib_language') . '</li>
            <li><strong>PHP</strong>：' . esc_html__('PHP7.0及以上', 'zib_language') . '</li>
            <li><strong>' . esc_html__('服务器配置', 'zib_language') . '</strong>：' . esc_html__('无要求，根据内容量选择，推荐2H4G5M', 'zib_language') . '</li>
            <li><strong>' . esc_html__('操作系统', 'zib_language') . '</strong>：' . esc_html__('无要求，不推荐使用Windows系统', 'zib_language') . '</li></div>',
        );
        return $csf;
    }
}
