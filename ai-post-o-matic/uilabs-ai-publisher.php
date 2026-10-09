<?php
/**
 * Plugin Name: AI Post-O-Matic
 * Description: AI-powered blog post generator. Generate and publish SEO-optimized posts directly from your WordPress dashboard.
 * Version: 2.0.2
 * Author: UI Labs LLC
 * Author URI: https://uilabs.com
 */

if (!defined('ABSPATH')) exit;

// Add admin menu
add_action('admin_menu', 'uilabs_ai_add_menu');
function uilabs_ai_add_menu() {
    add_menu_page('AI Post-O-Matic', 'AI Post-O-Matic', 'edit_posts', 'uilabs-ai-publisher', 'uilabs_ai_publisher_page', 'dashicons-edit-large', 30);
    add_submenu_page('uilabs-ai-publisher', 'Settings', 'Settings', 'manage_options', 'uilabs-ai-settings', 'uilabs_ai_settings_page');
}

// Admin styles
add_action('admin_head', 'uilabs_ai_admin_styles');
function uilabs_ai_admin_styles() {
    $screen = get_current_screen();
    if (!$screen || strpos($screen->id, 'uilabs') === false) return;
    ?>
    <style>
        #uilabs-ai-wrap { max-width: 920px; margin: 20px 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        #uilabs-ai-wrap h1 { font-size: 22px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .uilabs-card { background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px 24px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .uilabs-card h2 { font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; color: #2c5282; margin: 0 0 16px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
        .uilabs-card h3 { font-size: 15px; font-weight: 600; color: #2d3748; margin: 0 0 4px; }
        .uilabs-field { margin-bottom: 16px; }
        .uilabs-field:last-child { margin-bottom: 0; }
        .uilabs-field label { display: block; font-size: 12px; font-weight: 600; color: #555; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em; }
        .uilabs-field textarea, .uilabs-field input[type="text"], .uilabs-field input[type="datetime-local"] { width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; font-family: inherit; transition: border-color 0.2s; box-sizing: border-box; }
        .uilabs-field textarea:focus, .uilabs-field input:focus { border-color: #2c5282; outline: none; box-shadow: 0 0 0 3px rgba(44,82,130,0.1); }
        .uilabs-field textarea { resize: vertical; line-height: 1.6; }
        .uilabs-field select { width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; font-family: inherit; background: #fff; color: #333; transition: border-color 0.2s; box-sizing: border-box; }
        .uilabs-field select:focus { border-color: #2c5282; outline: none; box-shadow: 0 0 0 3px rgba(44,82,130,0.1); }
        .uilabs-field .hint { font-size: 11px; color: #999; margin-top: 5px; }
        .uilabs-field .char-count { font-size: 11px; margin-top: 4px; }
        .uilabs-field .char-count.ok   { color: #276749; }
        .uilabs-field .char-count.over { color: #c53030; }
        .uilabs-btn { display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px; border-radius: 6px; border: none; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s; font-family: inherit; }
        .uilabs-btn-primary   { background: #2c5282; color: #fff; }
        .uilabs-btn-primary:hover:not(:disabled)   { background: #1a365d; }
        .uilabs-btn-secondary { background: #e2e8f0; color: #2d3748; }
        .uilabs-btn-secondary:hover:not(:disabled) { background: #cbd5e0; }
        .uilabs-btn-success   { background: #276749; color: #fff; }
        .uilabs-btn-success:hover:not(:disabled)   { background: #1c4532; }
        .uilabs-btn:disabled  { opacity: 0.5; cursor: not-allowed; }
        .uilabs-btn-row { display: flex; gap: 10px; align-items: center; margin-top: 16px; flex-wrap: wrap; }
        .uilabs-tabs { display: flex; border-bottom: 2px solid #eee; margin-bottom: 16px; }
        .uilabs-tab { padding: 8px 16px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; color: #999; cursor: pointer; border: none; background: none; border-bottom: 2px solid transparent; margin-bottom: -2px; font-family: inherit; transition: color 0.2s; }
        .uilabs-tab.active { color: #2c5282; border-bottom-color: #2c5282; }
        #uilabs-preview { line-height: 1.8; color: #333; min-height: 150px; }
        #uilabs-preview h2 { font-size: 1.2em; margin: 1.2em 0 0.5em; }
        #uilabs-preview h3 { font-size: 1.05em; margin: 1em 0 0.4em; }
        #uilabs-preview p   { margin-bottom: 0.8em; }
        #uilabs-preview ul, #uilabs-preview ol { padding-left: 1.5em; margin-bottom: 0.8em; }
        #uilabs-html { font-family: monospace; font-size: 12px; color: #276749; background: #f0fff4; min-height: 150px; display: none; }
        .uilabs-mode-row { display: flex; gap: 8px; }
        .uilabs-mode-btn { padding: 8px 16px; border-radius: 6px; border: 1px solid #ddd; font-size: 12px; font-weight: 600; cursor: pointer; background: #fff; color: #666; font-family: inherit; transition: all 0.2s; }
        .uilabs-mode-btn.active-draft   { background: #e2e8f0; color: #2d3748; border-color: #cbd5e0; }
        .uilabs-mode-btn.active-publish { background: #276749; color: #fff; border-color: #276749; }
        .uilabs-status { padding: 10px 14px; border-radius: 6px; font-size: 13px; margin-bottom: 16px; display: none; }
        .uilabs-status.error   { background: #fff5f5; border: 1px solid #feb2b2; color: #c53030; display: block; }
        .uilabs-status.success { background: #f0fff4; border: 1px solid #9ae6b4; color: #276749; display: block; }
        .uilabs-spinner { display: inline-block; width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.4); border-top-color: #fff; border-radius: 50%; animation: uilabs-spin 0.7s linear infinite; }
        .uilabs-spinner.dark { border-color: rgba(0,0,0,0.15); border-top-color: #2c5282; }
        @keyframes uilabs-spin { to { transform: rotate(360deg); } }
        #uilabs-editor-card, #uilabs-seo-card { display: none; }
        .uilabs-success-box { text-align: center; padding: 32px; display: none; }
        .uilabs-success-box .icon { font-size: 40px; margin-bottom: 12px; }
        .uilabs-success-box h3 { font-size: 18px; color: #276749; margin-bottom: 8px; }
        .uilabs-key-hint { font-size: 11px; color: #999; margin-top: 5px; }
        .seo-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .date-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .seo-score { display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 4px; }
        .seo-score.green  { background: #f0fff4; color: #276749; border: 1px solid #9ae6b4; }
        .seo-score.yellow { background: #fffff0; color: #744210; border: 1px solid #f6e05e; }
        .keyword-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
        .keyword-tag { background: #ebf8ff; color: #2c5282; border: 1px solid #90cdf4; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 500; cursor: pointer; transition: all 0.2s; }
        .keyword-tag:hover { background: #2c5282; color: #fff; }
        .keyword-tag.copied { background: #276749; color: #fff; border-color: #276749; }
        .image-search-links { display: flex; gap: 8px; margin-top: 12px; flex-wrap: wrap; }
        .image-search-link { display: inline-flex; align-items: center; gap: 5px; padding: 7px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; border: 1px solid #ddd; color: #555; transition: all 0.2s; }
        .image-search-link:hover { border-color: #2c5282; color: #2c5282; text-decoration: none; }
        .pexels-link   { border-color: #05a081; color: #05a081; }
        .pexels-link:hover   { background: #05a081; color: #fff !important; }
        .unsplash-link { border-color: #111; color: #111; }
        .unsplash-link:hover { background: #111; color: #fff !important; }
        .canva-link    { border-color: #7d2ae8; color: #7d2ae8; }
        .canva-link:hover    { background: #7d2ae8; color: #fff !important; }
        /* Pexels image picker */
        .pexels-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 12px; }
        .pexels-thumb { position: relative; cursor: pointer; border-radius: 6px; overflow: hidden; border: 3px solid transparent; transition: border-color 0.2s, transform 0.15s; aspect-ratio: 16/9; background: #eee; }
        .pexels-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .pexels-thumb:hover { transform: scale(1.02); border-color: #2c5282; }
        .pexels-thumb.selected { border-color: #38a169; box-shadow: 0 0 0 2px #38a169; }
        .pexels-thumb .check { display: none; position: absolute; top: 6px; right: 6px; background: #38a169; color: #fff; border-radius: 50%; width: 22px; height: 22px; font-size: 13px; align-items: center; justify-content: center; }
        .pexels-thumb.selected .check { display: flex; }
        .pexels-credit { font-size: 11px; color: #999; margin-top: 6px; }
        .pexels-credit a { color: #999; }
        #uilabs-pexels-section { margin-top: 20px; border-top: 2px solid #e2e8f0; padding-top: 20px; }
    </style>
    <?php
}

// Main publisher page
function uilabs_ai_publisher_page() {
    $api_key = get_option('uilabs_anthropic_api_key', '');
    if (!$api_key) {
        echo '<div class="notice notice-warning"><p>Please <a href="' . admin_url('admin.php?page=uilabs-ai-settings') . '">add your Anthropic API key</a> to get started.</p></div>';
    }
    ?>
    <div id="uilabs-ai-wrap">
        <h1>✦ AI Post-O-Matic</h1>

        <div id="uilabs-status" class="uilabs-status"></div>

        <!-- Topic Card -->
        <div class="uilabs-card" id="uilabs-topic-card">
            <h2>Post Topic</h2>
            <div class="uilabs-field">
                <label>What should this post be about?</label>
                <textarea id="uilabs-topic" rows="3" placeholder="e.g. Why every Tulsa small business needs a mobile-friendly website in 2026"></textarea>
            </div>
            <div class="uilabs-btn-row">
                <button class="uilabs-btn uilabs-btn-primary" id="uilabs-generate-btn" onclick="uilabsGeneratePost()">✦ Generate Post</button>
                <span id="uilabs-gen-spinner" style="display:none;"><span class="uilabs-spinner dark"></span> Writing post...</span>
            </div>
        </div>

        <!-- Editor Card -->
        <div class="uilabs-card" id="uilabs-editor-card">
            <h2>Post Editor</h2>
            <div class="uilabs-field">
                <label>Title</label>
                <input type="text" id="uilabs-title">
            </div>
            <div class="uilabs-field">
                <label>Excerpt</label>
                <textarea id="uilabs-excerpt" rows="2"></textarea>
            </div>
            <div class="uilabs-field">
                <label>Content</label>
                <div class="uilabs-tabs">
                    <button class="uilabs-tab active" onclick="uilabsSwitchTab('preview', this)">Preview</button>
                    <button class="uilabs-tab" onclick="uilabsSwitchTab('html', this)">HTML</button>
                </div>
                <div id="uilabs-preview"></div>
                <textarea id="uilabs-html" rows="14" oninput="uilabsContent = this.value"></textarea>
            </div>

            <!-- Category -->
            <div class="uilabs-field">
                <label>Category</label>
                <select id="uilabs-category">
                    <?php
                    $cats = get_categories(['hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC']);
                    foreach ($cats as $cat) {
                        echo '<option value="' . esc_attr($cat->term_id) . '">' . esc_html($cat->name) . '</option>';
                    }
                    ?>
                </select>
            </div>

            <!-- Publish Date -->
            <div class="date-row">
                <div class="uilabs-field">
                    <label>Publish Date &amp; Time <span style="font-weight:400;color:#888;font-size:12px;">(optional)</span></label>
                    <input type="datetime-local" id="uilabs-publish-date">
                    <div class="hint">Leave blank to publish immediately</div>
                </div>
                <div class="uilabs-field">
                    <label style="visibility:hidden;">Clear</label>
                    <button class="uilabs-btn uilabs-btn-secondary" style="margin-top:0;width:100%;" onclick="document.getElementById('uilabs-publish-date').value=''">Clear Date</button>
                </div>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:12px;">
                <div class="uilabs-mode-row">
                    <button class="uilabs-mode-btn active-draft" id="mode-draft" onclick="uilabsSetMode('draft')">💾 Draft</button>
                    <button class="uilabs-mode-btn" id="mode-publish" onclick="uilabsSetMode('publish')">🚀 Publish</button>
                </div>
                <div class="uilabs-btn-row" style="margin-top:0;">
                    <span id="uilabs-pub-spinner" style="display:none;"><span class="uilabs-spinner dark"></span></span>
                    <button class="uilabs-btn uilabs-btn-secondary" onclick="uilabsReset()">Start Over</button>
                    <button class="uilabs-btn uilabs-btn-success" id="uilabs-publish-btn" onclick="uilabsPublishPost()">Save as Draft</button>
                </div>
            </div>
        </div>

        <!-- SEO Card -->
        <div class="uilabs-card" id="uilabs-seo-card">
            <h2>🔍 SEO & Images</h2>

            <div class="seo-grid">
                <div class="uilabs-field">
                    <label>Recommended Slug <span id="slug-score" class="seo-score green" style="display:none;"></span></label>
                    <input type="text" id="uilabs-slug" oninput="uilabsSlug = this.value; uilabsCheckSlug()">
                    <div class="hint">URL-friendly version of your title</div>
                </div>
                <div class="uilabs-field">
                    <label>Meta Title <span id="meta-title-count" class="char-count"></span></label>
                    <input type="text" id="uilabs-meta-title" oninput="uilabsCountChars('uilabs-meta-title','meta-title-count',60)">
                    <div class="hint">Ideal: 50–60 characters</div>
                </div>
            </div>

            <div class="uilabs-field">
                <label>Meta Description <span id="meta-desc-count" class="char-count"></span></label>
                <textarea id="uilabs-meta-desc" rows="2" oninput="uilabsCountChars('uilabs-meta-desc','meta-desc-count',160)"></textarea>
                <div class="hint">Ideal: 140–160 characters. Appears in Google search results.</div>
            </div>

            <div class="uilabs-field">
                <label>Focus Keyword</label>
                <input type="text" id="uilabs-focus-keyword">
                <div class="hint">Primary keyword this post should rank for</div>
            </div>

            <div class="uilabs-field">
                <label>📸 Image Keywords</label>
                <div class="hint" style="margin-bottom:8px;">Click a keyword to search Pexels, or use the links to search manually.</div>
                <div class="keyword-tags" id="uilabs-keyword-tags"></div>
                <div class="image-search-links" id="uilabs-image-links" style="display:none;">
                    <a id="pexels-link" href="#" target="_blank" class="image-search-link pexels-link">🖼 Search Pexels</a>
                    <a id="unsplash-link" href="#" target="_blank" class="image-search-link unsplash-link">📷 Search Unsplash</a>
                    <a id="canva-link" href="#" target="_blank" class="image-search-link canva-link">🎨 Create in Canva</a>
                </div>
            </div>

            <!-- Pexels Image Picker -->
            <div id="uilabs-pexels-section" style="display:none;">
                <h3 style="margin:0 0 4px;">🖼 Choose a Featured Image</h3>
                <p style="color:#666; font-size:13px; margin:0 0 10px;">Select an image to use as the featured image for this post.</p>
                <div style="display:flex; gap:8px; align-items:center; margin-bottom:10px;">
                    <input type="text" id="uilabs-pexels-query" placeholder="Search Pexels..." style="flex:1; padding:8px 12px; border:1px solid #ddd; border-radius:6px; font-size:14px;">
                    <button class="uilabs-btn uilabs-btn-primary" style="margin:0;" onclick="uilabsSearchPexels()">Search</button>
                    <span id="uilabs-pexels-spinner" style="display:none;"><span class="uilabs-spinner dark"></span></span>
                </div>
                <div id="uilabs-pexels-grid" class="pexels-grid"></div>
                <div id="uilabs-pexels-credit" class="pexels-credit" style="display:none;">
                    Photos provided by <a href="https://www.pexels.com" target="_blank">Pexels</a>
                </div>
                <div id="uilabs-pexels-actions" style="display:none; margin-top:12px;">
                    <div class="uilabs-btn-row">
                        <span id="uilabs-pexels-set-spinner" style="display:none;"><span class="uilabs-spinner dark"></span></span>
                        <button class="uilabs-btn uilabs-btn-success" id="uilabs-set-featured-btn" onclick="uilabsSetPexelsImage()">⭐ Set as Featured Image</button>
                    </div>
                    <div id="uilabs-pexels-status" style="margin-top:8px; font-size:13px;"></div>
                </div>
                <div id="uilabs-no-pexels-key" style="display:none; background:#fff8e1; border:1px solid #ffe082; border-radius:6px; padding:12px 14px; font-size:13px; color:#7a6000; margin-top:8px;">
                    ⚠️ Pexels API key not configured. Go to <strong>AI Post-O-Matic → Settings</strong> to add it.
                </div>
            </div>

            <div style="background:#f0fff4; border:1px solid #9ae6b4; border-radius:6px; padding:12px 14px; font-size:12px; color:#276749; margin-top:16px;">
                ✅ <strong>Yoast SEO:</strong> Meta title, description, and focus keyword are saved automatically to Yoast when you save or publish.
            </div>
        </div>

        <!-- Success Box -->
        <div class="uilabs-card uilabs-success-box" id="uilabs-success-box">
            <div class="icon">✓</div>
            <h3 id="uilabs-success-title"></h3>
            <p id="uilabs-success-post-title" style="color:#666; font-size:13px; margin-bottom:16px;"></p>
            <a id="uilabs-success-link" href="#" target="_blank" style="color:#2c5282; font-size:13px; display:none; margin-bottom:20px; display:block;">View post →</a>
            <div style="font-size:12px; color:#276749; margin-bottom:20px;">✅ Yoast SEO meta data was saved automatically with this post.</div>
            <button class="uilabs-btn uilabs-btn-primary" onclick="uilabsReset()">Write Another Post</button>
        </div>
    </div>

    <script>
    const UILABS_AJAX_URL  = '<?php echo admin_url('admin-ajax.php'); ?>';
    const UILABS_NONCE     = '<?php echo wp_create_nonce('uilabs_ai_nonce'); ?>';
    const UILABS_HAS_PEXELS = <?php echo get_option('uilabs_pexels_api_key') ? 'true' : 'false'; ?>;

    let uilabsMode       = 'draft';
    let uilabsSlug       = '';
    let uilabsContent    = '';
    let uilabsPostId     = 0;
    let uilabsSelectedImg = null;

    function uilabsShowStatus(msg, type) {
        const el = document.getElementById('uilabs-status');
        el.textContent = msg;
        el.className = 'uilabs-status ' + type;
        el.scrollIntoView({behavior:'smooth', block:'nearest'});
    }
    function uilabsClearStatus() {
        const el = document.getElementById('uilabs-status');
        el.className = 'uilabs-status';
        el.textContent = '';
    }

    function uilabsSetMode(mode) {
        uilabsMode = mode;
        document.getElementById('mode-draft').className   = 'uilabs-mode-btn' + (mode === 'draft'   ? ' active-draft'   : '');
        document.getElementById('mode-publish').className = 'uilabs-mode-btn' + (mode === 'publish' ? ' active-publish' : '');
        document.getElementById('uilabs-publish-btn').textContent = mode === 'publish' ? 'Publish to WordPress' : 'Save as Draft';
    }

    function uilabsSwitchTab(tab, el) {
        document.querySelectorAll('.uilabs-tab').forEach(t => t.classList.remove('active'));
        el.classList.add('active');
        const preview = document.getElementById('uilabs-preview');
        const html    = document.getElementById('uilabs-html');
        if (tab === 'preview') {
            preview.style.display = 'block';
            html.style.display    = 'none';
            preview.innerHTML     = uilabsContent;
        } else {
            preview.style.display = 'none';
            html.style.display    = 'block';
            html.value            = uilabsContent;
        }
    }

    function uilabsCountChars(fieldId, countId, limit) {
        const val = document.getElementById(fieldId).value.length;
        const el  = document.getElementById(countId);
        el.textContent = val + ' / ' + limit + ' chars';
        el.className = val === 0 ? 'char-count' : (val <= limit ? 'char-count ok' : 'char-count over');
    }

    function uilabsCheckSlug() {
        const val   = document.getElementById('uilabs-slug').value;
        uilabsSlug  = val;
        const score = document.getElementById('slug-score');
        if (!val) { score.style.display = 'none'; return; }
        const isGood = /^[a-z0-9-]+$/.test(val) && val.length <= 60;
        score.style.display = 'inline-flex';
        score.textContent   = isGood ? '✓ Good' : '⚠ Use lowercase letters and hyphens only';
        score.className     = 'seo-score ' + (isGood ? 'green' : 'yellow');
    }

    function uilabsRenderKeywords(keywords) {
        const container = document.getElementById('uilabs-keyword-tags');
        const links     = document.getElementById('uilabs-image-links');
        container.innerHTML = '';
        if (!keywords || !keywords.length) return;

        keywords.forEach(kw => {
            const tag = document.createElement('span');
            tag.className   = 'keyword-tag';
            tag.textContent = kw;
            tag.title       = 'Click to copy';
            tag.onclick = function() {
                navigator.clipboard.writeText(kw).then(() => {
                    tag.classList.add('copied');
                    tag.textContent = '✓ ' + kw;
                    uilabsUpdateImageLinks(kw);
                    document.getElementById('uilabs-pexels-query').value = kw;
                    uilabsSearchPexels();
                    setTimeout(() => { tag.classList.remove('copied'); tag.textContent = kw; }, 2000);
                });
            };
            container.appendChild(tag);
        });

        uilabsUpdateImageLinks(keywords[0]);
        links.style.display = 'flex';
    }

    function uilabsUpdateImageLinks(keyword) {
        const q = encodeURIComponent(keyword);
        document.getElementById('pexels-link').href   = 'https://www.pexels.com/search/' + q + '/';
        document.getElementById('unsplash-link').href = 'https://unsplash.com/s/photos/' + q;
        document.getElementById('canva-link').href    = 'https://www.canva.com/search/templates?q=' + q;
    }

    async function uilabsGeneratePost() {
        const topic = document.getElementById('uilabs-topic').value.trim();
        if (!topic) { uilabsShowStatus('Please enter a topic first.', 'error'); return; }

        uilabsClearStatus();
        const btn     = document.getElementById('uilabs-generate-btn');
        const spinner = document.getElementById('uilabs-gen-spinner');
        btn.disabled  = true;
        spinner.style.display = 'inline-flex';

        try {
            const res  = await fetch(UILABS_AJAX_URL, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({ action: 'uilabs_generate_post', nonce: UILABS_NONCE, topic })
            });
            const data = await res.json();
            if (!data.success) throw new Error(data.data || 'Generation failed');

            const post = data.data;
            document.getElementById('uilabs-title').value   = post.title;
            document.getElementById('uilabs-excerpt').value = post.excerpt;
            document.getElementById('uilabs-preview').innerHTML = post.content;
            document.getElementById('uilabs-html').value    = post.content;
            uilabsContent = post.content;
            uilabsSlug    = post.slug;

            document.getElementById('uilabs-slug').value          = post.slug;
            document.getElementById('uilabs-meta-title').value    = post.meta_title || post.title;
            document.getElementById('uilabs-meta-desc').value     = post.meta_description || post.excerpt;
            document.getElementById('uilabs-focus-keyword').value = post.focus_keyword || '';
            uilabsCountChars('uilabs-meta-title', 'meta-title-count', 60);
            uilabsCountChars('uilabs-meta-desc',  'meta-desc-count',  160);
            uilabsCheckSlug();
            uilabsRenderKeywords(post.image_keywords || []);

            // Show Pexels section and auto-search
            document.getElementById('uilabs-pexels-section').style.display = 'block';
            if (!UILABS_HAS_PEXELS) {
                document.getElementById('uilabs-no-pexels-key').style.display = 'block';
            } else if (post.image_keywords && post.image_keywords.length) {
                document.getElementById('uilabs-pexels-query').value = post.image_keywords[0];
                uilabsSearchPexels();
            }

            document.getElementById('uilabs-topic-card').style.display  = 'none';
            document.getElementById('uilabs-editor-card').style.display = 'block';
            document.getElementById('uilabs-seo-card').style.display    = 'block';
            uilabsClearStatus();

        } catch(err) {
            uilabsShowStatus('Failed to generate post: ' + err.message, 'error');
        } finally {
            btn.disabled = false;
            spinner.style.display = 'none';
        }
    }

    async function uilabsPublishPost() {
        const title    = document.getElementById('uilabs-title').value.trim();
        const excerpt  = document.getElementById('uilabs-excerpt').value.trim();
        const content  = document.getElementById('uilabs-html').value || uilabsContent;
        const slug     = document.getElementById('uilabs-slug').value.trim() || uilabsSlug;
        const meta_title = document.getElementById('uilabs-meta-title').value.trim();
        const meta_desc  = document.getElementById('uilabs-meta-desc').value.trim();
        const focus_kw   = document.getElementById('uilabs-focus-keyword').value.trim();
        const category   = document.getElementById('uilabs-category').value;
        const publish_date = document.getElementById('uilabs-publish-date').value;

        uilabsClearStatus();
        const btn     = document.getElementById('uilabs-publish-btn');
        const spinner = document.getElementById('uilabs-pub-spinner');
        btn.disabled  = true;
        spinner.style.display = 'inline-flex';

        try {
            const res  = await fetch(UILABS_AJAX_URL, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({ action: 'uilabs_publish_post', nonce: UILABS_NONCE,
                    title, content, excerpt, slug, status: uilabsMode,
                    category, publish_date, meta_title, meta_desc, focus_kw })
            });
            const data = await res.json();
            if (!data.success) throw new Error(data.data || 'Publish failed');

            const result = data.data;
            uilabsPostId = result.id || 0;
            document.getElementById('uilabs-editor-card').style.display = 'none';
            document.getElementById('uilabs-success-box').style.display = 'block';
            document.getElementById('uilabs-success-title').textContent = uilabsMode === 'publish' ? 'Post Published!' : 'Draft Saved!';
            document.getElementById('uilabs-success-post-title').textContent = title;

            if (result.link) {
                const link = document.getElementById('uilabs-success-link');
                link.href   = result.link;
                link.target = '_blank';
                link.style.display = 'block';
            }

            document.getElementById('uilabs-set-featured-btn').textContent = uilabsPostId ? '⭐ Set as Featured Image' : '⭐ Set as Featured Image (save post first)';

        } catch(err) {
            uilabsShowStatus('Error: ' + err.message, 'error');
        } finally {
            btn.disabled = false;
            uilabsSetMode(uilabsMode);
            spinner.style.display = 'none';
        }
    }

    async function uilabsSearchPexels() {
        if (!UILABS_HAS_PEXELS) {
            document.getElementById('uilabs-no-pexels-key').style.display = 'block';
            return;
        }
        const query   = document.getElementById('uilabs-pexels-query').value.trim();
        const grid    = document.getElementById('uilabs-pexels-grid');
        const spinner = document.getElementById('uilabs-pexels-spinner');
        const credit  = document.getElementById('uilabs-pexels-credit');
        const actions = document.getElementById('uilabs-pexels-actions');
        if (!query) return;

        spinner.style.display = 'inline-flex';
        grid.innerHTML = '';
        credit.style.display = 'none';
        actions.style.display = 'none';
        uilabsSelectedImg = null;

        try {
            const res  = await fetch(UILABS_AJAX_URL, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({ action: 'uilabs_pexels_search', nonce: UILABS_NONCE, query })
            });
            const data = await res.json();
            if (!data.success) throw new Error(data.data || 'Search failed');

            const photos = data.data.photos;
            if (!photos || !photos.length) {
                grid.innerHTML = '<p style="color:#888;font-size:13px;">No photos found. Try a different keyword.</p>';
                return;
            }

            photos.forEach(photo => {
                const thumb = document.createElement('div');
                thumb.className = 'pexels-thumb';
                thumb.innerHTML = `<img src="${photo.src.medium}" alt="${photo.alt || ''}"><span class="check">✓</span>`;
                thumb.onclick = function() {
                    document.querySelectorAll('.pexels-thumb').forEach(t => t.classList.remove('selected'));
                    thumb.classList.add('selected');
                    uilabsSelectedImg = { url: photo.src.large2x, photographer: photo.photographer, photographer_url: photo.photographer_url };
                    actions.style.display = 'block';
                    document.getElementById('uilabs-pexels-status').textContent = '';
                    document.getElementById('uilabs-set-featured-btn').textContent = uilabsPostId ? '⭐ Set as Featured Image' : '⭐ Set as Featured Image (save post first)';
                };
                grid.appendChild(thumb);
            });
            credit.style.display = 'block';

        } catch(err) {
            grid.innerHTML = '<p style="color:#c53030;font-size:13px;">Error: ' + err.message + '</p>';
        } finally {
            spinner.style.display = 'none';
        }
    }

    async function uilabsSetPexelsImage() {
        if (!uilabsSelectedImg) return;
        if (!uilabsPostId) { alert('Please save the post as a draft first, then set the featured image.'); return; }
        const btn      = document.getElementById('uilabs-set-featured-btn');
        const spinner  = document.getElementById('uilabs-pexels-set-spinner');
        const statusEl = document.getElementById('uilabs-pexels-status');
        btn.disabled   = true;
        spinner.style.display = 'inline-flex';
        statusEl.textContent  = '';

        try {
            const res  = await fetch(UILABS_AJAX_URL, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({ action: 'uilabs_set_pexels_image', nonce: UILABS_NONCE,
                    post_id: uilabsPostId, image_url: uilabsSelectedImg.url, photographer: uilabsSelectedImg.photographer })
            });
            const data = await res.json();
            if (!data.success) throw new Error(data.data || 'Failed to set image');
            statusEl.style.color = '#276749';
            statusEl.textContent = '✓ Featured image set! Photo by ' + uilabsSelectedImg.photographer + ' on Pexels.';
            btn.textContent = '✓ Featured Image Set';
        } catch(err) {
            statusEl.style.color = '#c53030';
            statusEl.textContent = 'Error: ' + err.message;
            btn.disabled  = false;
            btn.textContent = '⭐ Set as Featured Image';
        } finally {
            spinner.style.display = 'none';
        }
    }

    function uilabsReset() {
        ['uilabs-topic','uilabs-title','uilabs-excerpt','uilabs-html','uilabs-slug',
         'uilabs-meta-title','uilabs-meta-desc','uilabs-focus-keyword','uilabs-pexels-query'].forEach(id => {
            document.getElementById(id).value = '';
        });
        document.getElementById('uilabs-preview').innerHTML = '';
        document.getElementById('uilabs-keyword-tags').innerHTML = '';
        document.getElementById('uilabs-image-links').style.display = 'none';
        document.getElementById('meta-title-count').textContent = '';
        document.getElementById('meta-desc-count').textContent  = '';
        document.getElementById('slug-score').style.display     = 'none';
        document.getElementById('uilabs-category').selectedIndex = 0;
        document.getElementById('uilabs-publish-date').value = '';
        document.getElementById('uilabs-pexels-section').style.display = 'none';
        document.getElementById('uilabs-pexels-grid').innerHTML = '';
        document.getElementById('uilabs-pexels-credit').style.display = 'none';
        document.getElementById('uilabs-pexels-actions').style.display = 'none';
        document.getElementById('uilabs-pexels-status').textContent = '';
        document.getElementById('uilabs-no-pexels-key').style.display = 'none';
        document.getElementById('uilabs-topic-card').style.display  = 'block';
        document.getElementById('uilabs-editor-card').style.display = 'none';
        document.getElementById('uilabs-seo-card').style.display    = 'none';
        document.getElementById('uilabs-success-box').style.display = 'none';
        uilabsContent     = '';
        uilabsSlug        = '';
        uilabsPostId      = 0;
        uilabsSelectedImg = null;
        uilabsClearStatus();
        uilabsSetMode('draft');
    }
    </script>
    <?php
}

// Settings page
function uilabs_ai_settings_page() {
    if (isset($_POST['uilabs_save_settings']) && check_admin_referer('uilabs_settings_nonce')) {
        update_option('uilabs_anthropic_api_key',  sanitize_text_field($_POST['uilabs_api_key']));
        update_option('uilabs_pexels_api_key',      sanitize_text_field($_POST['uilabs_pexels_api_key']));
        update_option('uilabs_business_name',       sanitize_text_field($_POST['uilabs_business_name']));
        update_option('uilabs_business_location',   sanitize_text_field($_POST['uilabs_business_location']));
        update_option('uilabs_business_url',        esc_url_raw($_POST['uilabs_business_url']));
        update_option('uilabs_business_services',   sanitize_textarea_field($_POST['uilabs_business_services']));
        update_option('uilabs_business_tone',       sanitize_text_field($_POST['uilabs_business_tone']));
        update_option('uilabs_business_audience',   sanitize_text_field($_POST['uilabs_business_audience']));
        echo '<div class="notice notice-success"><p>Settings saved.</p></div>';
    }
    $api_key        = get_option('uilabs_anthropic_api_key', '');
    $pexels_key     = get_option('uilabs_pexels_api_key', '');
    $biz_name       = get_option('uilabs_business_name',     get_bloginfo('name'));
    $biz_location   = get_option('uilabs_business_location', '');
    $biz_url        = get_option('uilabs_business_url',      get_site_url());
    $biz_services   = get_option('uilabs_business_services', '');
    $biz_tone       = get_option('uilabs_business_tone',     'friendly and professional');
    $biz_audience   = get_option('uilabs_business_audience', '');
    ?>
    <div id="uilabs-ai-wrap">
        <h1>✦ AI Post-O-Matic Settings</h1>
        <div class="uilabs-card">
            <form method="post">
                <?php wp_nonce_field('uilabs_settings_nonce'); ?>

                <h2>API Keys</h2>
                <div class="uilabs-field">
                    <label>Anthropic API Key</label>
                    <input type="text" name="uilabs_api_key" value="<?php echo esc_attr($api_key); ?>" placeholder="sk-ant-api03-..." style="font-family:monospace;">
                    <div class="uilabs-key-hint">Get your key at platform.anthropic.com → API Keys</div>
                </div>
                <div class="uilabs-field">
                    <label>Pexels API Key <span style="font-weight:400;color:#888;font-size:12px;">(for image search)</span></label>
                    <input type="text" name="uilabs_pexels_api_key" value="<?php echo esc_attr($pexels_key); ?>" placeholder="Your Pexels API key..." style="font-family:monospace;">
                    <div class="uilabs-key-hint">Get your free key at pexels.com/api</div>
                </div>

                <h2 style="margin-top:28px;">Business Profile</h2>
                <p style="color:#666;font-size:14px;margin-bottom:16px;">This tells the AI who it's writing for. Fill this in so generated content matches your brand and location.</p>

                <div class="seo-grid">
                    <div class="uilabs-field">
                        <label>Business Name</label>
                        <input type="text" name="uilabs_business_name" value="<?php echo esc_attr($biz_name); ?>" placeholder="UI Labs LLC">
                    </div>
                    <div class="uilabs-field">
                        <label>Location <span style="font-weight:400;color:#888;font-size:12px;">(city, state)</span></label>
                        <input type="text" name="uilabs_business_location" value="<?php echo esc_attr($biz_location); ?>" placeholder="Tulsa, Oklahoma">
                    </div>
                </div>

                <div class="uilabs-field">
                    <label>Website URL</label>
                    <input type="text" name="uilabs_business_url" value="<?php echo esc_attr($biz_url); ?>" placeholder="https://uilabs.com">
                </div>

                <div class="uilabs-field">
                    <label>Services / Specialties</label>
                    <textarea name="uilabs_business_services" rows="3" placeholder="Web design, SEO, WordPress maintenance, hosting"><?php echo esc_textarea($biz_services); ?></textarea>
                    <div class="hint">What does this business do? The AI will weave this into content naturally.</div>
                </div>

                <div class="seo-grid">
                    <div class="uilabs-field">
                        <label>Writing Tone</label>
                        <input type="text" name="uilabs_business_tone" value="<?php echo esc_attr($biz_tone); ?>" placeholder="friendly and professional">
                        <div class="hint">e.g. friendly and professional, authoritative, casual</div>
                    </div>
                    <div class="uilabs-field">
                        <label>Target Audience</label>
                        <input type="text" name="uilabs_business_audience" value="<?php echo esc_attr($biz_audience); ?>" placeholder="small business owners in Tulsa">
                    </div>
                </div>

                <div class="uilabs-btn-row">
                    <input type="submit" name="uilabs_save_settings" class="uilabs-btn uilabs-btn-primary" value="Save Settings">
                </div>
            </form>
        </div>
    </div>
    <?php
}

// AJAX: Search Pexels
add_action('wp_ajax_uilabs_pexels_search', 'uilabs_ajax_pexels_search');
function uilabs_ajax_pexels_search() {
    check_ajax_referer('uilabs_ai_nonce', 'nonce');
    if (!current_user_can('edit_posts')) wp_send_json_error('Unauthorized');

    $query      = sanitize_text_field($_POST['query'] ?? '');
    $pexels_key = get_option('uilabs_pexels_api_key', '');

    if (!$query)      wp_send_json_error('Missing search query');
    if (!$pexels_key) wp_send_json_error('Pexels API key not configured');

    $response = wp_remote_get(
        'https://api.pexels.com/v1/search?' . http_build_query(['query' => $query, 'per_page' => 6, 'orientation' => 'landscape']),
        ['timeout' => 15, 'headers' => ['Authorization' => $pexels_key]]
    );

    if (is_wp_error($response)) wp_send_json_error('Request failed: ' . $response->get_error_message());

    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (empty($body['photos'])) wp_send_json_error('No photos found');

    wp_send_json_success(['photos' => $body['photos']]);
}

// AJAX: Download Pexels image and set as featured image
add_action('wp_ajax_uilabs_set_pexels_image', 'uilabs_ajax_set_pexels_image');
function uilabs_ajax_set_pexels_image() {
    check_ajax_referer('uilabs_ai_nonce', 'nonce');
    if (!current_user_can('edit_posts')) wp_send_json_error('Unauthorized');

    $post_id      = intval($_POST['post_id'] ?? 0);
    $image_url    = esc_url_raw($_POST['image_url'] ?? '');
    $photographer = sanitize_text_field($_POST['photographer'] ?? 'Pexels');

    if (!$post_id)   wp_send_json_error('Missing post ID');
    if (!$image_url) wp_send_json_error('Missing image URL');

    $response = wp_remote_get($image_url, ['timeout' => 30]);
    if (is_wp_error($response)) wp_send_json_error('Failed to download image: ' . $response->get_error_message());

    $image_data   = wp_remote_retrieve_body($response);
    $content_type = wp_remote_retrieve_header($response, 'content-type');
    $ext          = (strpos($content_type, 'jpeg') !== false) ? 'jpg' : 'png';
    $filename     = 'pexels-' . sanitize_title($photographer) . '-' . $post_id . '-' . time() . '.' . $ext;

    $upload = wp_upload_bits($filename, null, $image_data);
    if ($upload['error']) wp_send_json_error('Upload error: ' . $upload['error']);

    $attach_id = wp_insert_attachment([
        'post_mime_type' => $content_type,
        'post_title'     => get_the_title($post_id) . ' - Photo by ' . $photographer . ' on Pexels',
        'post_content'   => '',
        'post_status'    => 'inherit',
    ], $upload['file'], $post_id);

    if (is_wp_error($attach_id)) wp_send_json_error($attach_id->get_error_message());

    require_once ABSPATH . 'wp-admin/includes/image.php';
    wp_update_attachment_metadata($attach_id, wp_generate_attachment_metadata($attach_id, $upload['file']));
    set_post_thumbnail($post_id, $attach_id);

    wp_send_json_success(['attachment_id' => $attach_id]);
}

// AJAX: Generate post via Anthropic
add_action('wp_ajax_uilabs_generate_post', 'uilabs_ajax_generate_post');
function uilabs_ajax_generate_post() {
    check_ajax_referer('uilabs_ai_nonce', 'nonce');
    if (!current_user_can('edit_posts')) wp_send_json_error('Unauthorized');

    $topic   = sanitize_text_field($_POST['topic'] ?? '');
    $api_key = get_option('uilabs_anthropic_api_key', '');

    if (!$topic)   wp_send_json_error('Missing topic');
    if (!$api_key) wp_send_json_error('API key not configured. Please go to AI Post-O-Matic → Settings.');

    $biz_name     = get_option('uilabs_business_name',     get_bloginfo('name'));
    $biz_location = get_option('uilabs_business_location', '');
    $biz_url      = get_option('uilabs_business_url',      get_site_url());
    $biz_services = get_option('uilabs_business_services', '');
    $biz_tone     = get_option('uilabs_business_tone',     'friendly and professional');
    $biz_audience = get_option('uilabs_business_audience', '');

    $biz_context  = $biz_name;
    if ($biz_location) $biz_context .= ", based in {$biz_location}";
    if ($biz_url)      $biz_context .= " ({$biz_url})";
    if ($biz_services) $biz_context .= ", specializing in {$biz_services}";
    $audience_line = $biz_audience ? "Target audience: {$biz_audience}." : '';

    $system_prompt = <<<PROMPT
You are a professional content writer and SEO specialist for {$biz_context}.

Write engaging, SEO-optimized blog posts that position this business as a knowledgeable local expert, written in a {$biz_tone} tone with a strong intro, clear sections, and a call to action. {$audience_line} Format content in HTML for WordPress using <h2>, <h3>, <p>, <ul>, <li> tags. Target 600-1000 words.

Return ONLY a valid JSON object with no markdown, no backticks, and no extra text:
{
  "title": "Post title",
  "content": "<p>HTML content...</p>",
  "excerpt": "1-2 sentence summary",
  "slug": "url-friendly-slug-max-60-chars",
  "meta_title": "SEO title under 60 chars - include primary keyword",
  "meta_description": "Compelling meta description 140-160 chars with primary keyword and call to action",
  "focus_keyword": "primary keyword phrase",
  "image_keywords": ["keyword 1", "keyword 2", "keyword 3", "keyword 4", "keyword 5"],
  "tags": ["tag1", "tag2"]
}

For image_keywords: provide 5 specific, descriptive search terms someone could use on Pexels or Unsplash to find a relevant featured image for this post. Be specific and visual (e.g. "tulsa business owner laptop coffee" not just "business").
PROMPT;

    $payload = json_encode([
        'model'      => 'claude-sonnet-5-5',
        'max_tokens' => 4000,
        'system'     => $system_prompt,
        'messages'   => [['role' => 'user', 'content' => 'Write a blog post about: ' . $topic]]
    ]);

    $response = wp_remote_post('https://api.anthropic.com/v1/messages', [
        'timeout' => 60,
        'headers' => [
            'Content-Type'      => 'application/json',
            'x-api-key'         => $api_key,
            'anthropic-version' => '2023-06-01',
        ],
        'body' => $payload,
    ]);

    if (is_wp_error($response)) wp_send_json_error('API request failed: ' . $response->get_error_message());

    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (wp_remote_retrieve_response_code($response) !== 200) {
        wp_send_json_error('Anthropic API error: ' . ($body['error']['message'] ?? 'HTTP ' . wp_remote_retrieve_response_code($response)));
    }
    $text = '';
    foreach (($body['content'] ?? []) as $block) {
        if (isset($block['text'])) $text .= $block['text'];
    }

    $text   = trim(preg_replace('/^```json\s*|\s*```$/m', '', trim($text)));
    $parsed = json_decode($text, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json_error('AI returned invalid JSON. Please try again.');
    }

    wp_send_json_success($parsed);
}

// AJAX: Publish post
add_action('wp_ajax_uilabs_publish_post', 'uilabs_ajax_publish_post');
function uilabs_ajax_publish_post() {
    check_ajax_referer('uilabs_ai_nonce', 'nonce');
    if (!current_user_can('edit_posts')) wp_send_json_error('Unauthorized');

    $title   = sanitize_text_field($_POST['title']   ?? '');
    $content = wp_kses_post($_POST['content'] ?? '');
    $excerpt = sanitize_text_field($_POST['excerpt']  ?? '');
    $slug    = sanitize_title($_POST['slug']    ?? '');
    $status  = in_array($_POST['status'] ?? '', ['draft', 'publish', 'pending']) ? $_POST['status'] : 'draft';
    $category    = intval($_POST['category'] ?? 0);
    $publish_date = sanitize_text_field($_POST['publish_date'] ?? '');

    if (!$title && !$content) wp_send_json_error('Title and content are empty');

    $post_id = wp_insert_post([
        'post_title'    => $title,
        'post_content'  => $content,
        'post_excerpt'  => $excerpt,
        'post_name'     => $slug,
        'post_status'   => $status,
        'post_author'   => get_current_user_id(),
        'post_type'     => 'post',
        'post_category' => $category ? [$category] : [],
        'post_date'     => $publish_date ? date('Y-m-d H:i:s', strtotime($publish_date)) : '',
        'post_date_gmt' => $publish_date ? gmdate('Y-m-d H:i:s', strtotime($publish_date)) : '',
    ], true);

    if (is_wp_error($post_id)) wp_send_json_error($post_id->get_error_message());

    // Write Yoast SEO meta fields
    $meta_title = sanitize_text_field($_POST['meta_title'] ?? '');
    $meta_desc  = sanitize_text_field($_POST['meta_desc']  ?? '');
    $focus_kw   = sanitize_text_field($_POST['focus_kw']   ?? '');
    if ($meta_title) update_post_meta($post_id, '_yoast_wpseo_title',    $meta_title);
    if ($meta_desc)  update_post_meta($post_id, '_yoast_wpseo_metadesc', $meta_desc);
    if ($focus_kw)   update_post_meta($post_id, '_yoast_wpseo_focuskw',  $focus_kw);

    wp_send_json_success([
        'id'     => $post_id,
        'link'   => get_permalink($post_id),
        'status' => $status,
        'title'  => $title,
    ]);
}
