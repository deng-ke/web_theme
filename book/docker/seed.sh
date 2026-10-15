#!/usr/bin/env bash
# 演示数据种子脚本：创建章节页面层级与示例文章（内容为原生 HTML）
set -e
cd "$(dirname "$0")/.."

wp() { docker compose exec -T cli wp "$@"; }

# ---------- 页面 ----------
WELCOME=$(cat <<'EOF'
<p>本站基于 GitBook 主题搭建，用于展示 GitBook 风格的 WordPress 文档站点。</p>
<h2>主题特色</h2>
<ul><li>左侧章节导航，自动按页面层级生成目录树</li><li>右侧"本页内容"锚点导航，随滚动高亮</li><li>深浅色模式一键切换，自动记忆偏好</li><li>代码块一键复制、阅读进度条、上下篇导航</li></ul>
<h3>快速体验</h3>
<p>点击左侧导航浏览各章节，或按 <kbd>/</kbd> 键快速搜索。</p>
EOF
)

QUICKSTART=$(cat <<'EOF'
<p>本章节介绍如何在几分钟内搭建一个 GitBook 风格的文档站。</p>
<h2>安装主题</h2>
<p>将主题目录放入 <code>wp-content/themes/</code> 并在后台启用，详见下一节。</p>
<h2>配置菜单</h2>
<p>主题支持两种目录生成方式：</p>
<ol><li><strong>自动生成</strong>（默认）：按页面的父子层级自动构建左侧导航树</li><li><strong>自定义菜单</strong>：在"外观 → 菜单"中创建菜单并分配到"侧边栏主菜单"</li></ol>
<h3>切换强调色</h3>
<p>进入"外观 → 自定义 → 颜色"，可更换主题的蓝紫渐变强调色。</p>
EOF
)

INSTALL=$(cat <<'EOF'
<p>本节演示代码块、命令行的排版效果。</p>
<h2>目录结构</h2>
<pre class="wp-block-code"><code>gitbook/
├── style.css          # 主题声明
├── functions.php      # 主题设置与辅助函数
├── header.php         # 左侧导航 + 顶栏
├── footer.php         # 页内目录 + 上下篇
└── assets/
    ├── css/main.css   # 全部样式（含深浅色）
    └── js/main.js     # 交互脚本</code></pre>
<h2>安装步骤</h2>
<pre class="wp-block-code"><code># 1. 复制主题到 WordPress 主题目录
cp -r gitbook /var/www/html/wp-content/themes/

# 2. 进入后台启用主题
# 外观 → 主题 → 启用 GitBook

# 3. （可选）设置固定链接为“文章名”
# 设置 → 固定链接 → 文章名</code></pre>
<blockquote class="wp-block-quote"><p>提示：启用主题后无需任何插件即可使用全部功能。</p></blockquote>
EOF
)

CHAPTERS=$(cat <<'EOF'
<p>主题的左侧导航支持无限层级，但建议保持两级以内，便于阅读。</p>
<h2>创建章节</h2>
<p>在后台"页面 → 新建页面"中创建页面，并为其设置"父页面"即可形成章节。</p>
<h2>排序</h2>
<p>页面按"页面属性 → 排序"字段排列，默认按标题排序。</p>
EOF
)

WRITING=$(cat <<'EOF'
<p>本章汇总写作规范与排版建议，让文档更清晰易读。</p>
<h2>标题层级</h2>
<p>正文使用二级、三级标题，右侧"本页内容"会自动生成锚点目录。</p>
EOF
)

TYPO=$(cat <<'EOF'
<p>以下示例覆盖主题支持的常见排版元素。</p>
<h2>表格</h2>
<figure class="wp-block-table"><table><thead><tr><th>元素</th><th>用途</th><th>样式</th></tr></thead><tbody><tr><td>代码块</td><td>展示命令与代码</td><td>深色圆角卡片，悬停出现复制按钮</td></tr><tr><td>引用块</td><td>提示与说明</td><td>渐变底 + 渐变左边框</td></tr><tr><td>表格</td><td>对比与参数</td><td>圆角 + 悬浮高亮</td></tr></tbody></table></figure>
<h2>引用示例</h2>
<blockquote class="wp-block-quote"><p>GitBook 风格的文档强调"边读边导航"：左侧章节、右侧锚点、底部上下篇，三处导航互不干扰。</p></blockquote>
<h2>列表示例</h2>
<ol><li>先建立章节骨架</li><li>再填充内容</li><li>最后调整排序</li></ol>
EOF
)

ABOUT=$(cat <<'EOF'
<p>本演示站点由 GitBook 主题驱动，全部内容为演示数据。</p>
<h2>技术栈</h2>
<ul><li>WordPress 6.x</li><li>原生 PHP 模板，无构建步骤</li><li>纯 CSS 变量设计系统，支持深浅色双主题</li></ul>
EOF
)

echo "== 创建页面 =="
P_WELCOME=$(wp post create --post_type=page --post_status=publish --post_title="欢迎使用" --post_name="welcome" --post_content="$WELCOME" --porcelain | tail -1)
P_QUICK=$(wp post create --post_type=page --post_status=publish --post_title="快速开始" --post_name="getting-started" --post_content="$QUICKSTART" --porcelain | tail -1)
P_WRITING=$(wp post create --post_type=page --post_status=publish --post_title="写作指南" --post_name="writing-guide" --post_content="$WRITING" --porcelain | tail -1)
P_ABOUT=$(wp post create --post_type=page --post_status=publish --post_title="关于本站" --post_name="about" --post_content="$ABOUT" --porcelain | tail -1)

wp post create --post_type=page --post_status=publish --post_title="安装主题" --post_name="installation" --post_parent="$P_QUICK" --post_content="$INSTALL" --porcelain >/dev/null
wp post create --post_type=page --post_status=publish --post_title="创建章节" --post_name="chapters" --post_parent="$P_QUICK" --post_content="$CHAPTERS" --porcelain >/dev/null
wp post create --post_type=page --post_status=publish --post_title="排版建议" --post_name="typography" --post_parent="$P_WRITING" --post_content="$TYPO" --porcelain >/dev/null

# ---------- 文章（--tags_input 需传标签名称，缺失时自动创建） ----------
POST1=$(cat <<'EOF'
<p>使用 GitBook 主题构建知识库时，合理的内容组织是第一位的。</p>
<h2>为什么选择页面层级</h2>
<p>WordPress 页面原生支持父子关系，天然适合文档的章节结构。</p>
<pre class="wp-block-code"><code>// 主题通过 get_pages 读取层级并生成导航树
$tree = gitbook_page_tree();</code></pre>
<h2>文章与页面的分工</h2>
<figure class="wp-block-table"><table><thead><tr><th>内容类型</th><th>用途</th><th>导航位置</th></tr></thead><tbody><tr><td>页面</td><td>固定章节</td><td>左侧章节树</td></tr><tr><td>文章</td><td>动态内容</td><td>最新文章列表</td></tr></tbody></table></figure>
<h3>建议</h3>
<ol><li>章节结构用页面</li><li>博客、更新日志用文章</li><li>两者都会出现在搜索与上下篇导航中</li></ol>
EOF
)

POST2=$(cat <<'EOF'
<p>主题内置的深浅色模式无需插件即可工作，本文介绍它的实现思路。</p>
<h2>实现原理</h2>
<p>使用 CSS 自定义属性定义两套配色，通过 <code>data-theme</code> 属性切换。</p>
<pre class="wp-block-code"><code>:root {
  --gb-bg: #f6f7fb;
  --gb-text: #1c2130;
}
[data-theme="dark"] {
  --gb-bg: #0e1016;
  --gb-text: #e9ebf4;
}</code></pre>
<h2>记忆偏好</h2>
<p>切换时写入 <code>localStorage</code>，首次访问跟随系统偏好。</p>
<blockquote class="wp-block-quote"><p>试试点击右上角的月亮图标，观察毛玻璃导航的配色过渡动画。</p></blockquote>
EOF
)

POST3=$(cat <<'EOF'
<p>左侧导航树在移动端会收起为抽屉，本文演示响应式断点与交互细节。</p>
<h2>断点设计</h2>
<ol><li><strong>1460px 以下</strong>：隐藏右侧页内目录</li><li><strong>1100px 以下</strong>：左侧导航变为抽屉，顶栏出现菜单按钮</li><li><strong>640px 以下</strong>：上下篇卡片改为纵向堆叠</li></ol>
<pre class="wp-block-code"><code>// 抽屉开合只切换 body class，动画交给 CSS
document.body.classList.toggle('sidebar-open', open);</code></pre>
<h2>手势与遮罩</h2>
<p>遮罩使用毛玻璃效果，点击遮罩或按 <kbd>Esc</kbd> 均可关闭抽屉。</p>
EOF
)

echo "== 创建文章 =="
wp post create --post_status=publish --post_title="如何组织文档内容" --post_name="organizing-content" --post_content="$POST1" --tags_input="WordPress,文档" --porcelain >/dev/null
wp post create --post_status=publish --post_title="深浅色模式实现解析" --post_name="dark-mode" --post_content="$POST2" --tags_input="WordPress" --porcelain >/dev/null
wp post create --post_status=publish --post_title="移动端响应式细节" --post_name="responsive-details" --post_content="$POST3" --tags_input="文档" --porcelain >/dev/null

# ---------- 菜单与首页 ----------
echo "== 配置首页 =="
wp option update show_on_front page >/dev/null
wp option update page_on_front "$P_WELCOME" >/dev/null

echo "✅ 演示数据创建完成"
