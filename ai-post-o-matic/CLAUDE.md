# AI Post-O-Matic — Claude Code Context

## Version
Current version: **2.0.2**
⚠️ **Important:** Increment the version number in the plugin header (`* Version:`) on every build. Use semantic versioning: bump the patch number (2.0.x) for fixes and small additions, minor (2.x.0) for significant new features.

## Overview
WordPress plugin that generates SEO-optimized blog posts using the Anthropic Claude API, with Pexels image search and automatic Yoast SEO field population.

- **Plugin name:** AI Post-O-Matic
- **Author:** UI Labs LLC (uilabs.com)
- **Main file:** `ai-post-o-matic/uilabs-ai-publisher.php`
- **Hosted on:** SiteGround (WordPress.org, not WordPress.com)
- **WordPress admin user:** UilabsAdmin

---

## Source of truth
Canonical source lives in git at `~/Documents/uilabs/ai-post-o-matic` on Callisto. Edit that file — do NOT rebuild the plugin from scratch.
Build the upload zip from the repo root: `zip -r ai-post-o-matic.zip ai-post-o-matic -x '*.DS_Store'`
Install: Plugins → Add New → Upload Plugin → Replace current (keeps saved settings).
Non-200 Anthropic responses surface the API's error message in the UI (e.g. retired model, bad key).

## Features (v2.0.2)
- Post generator: topic in, 600–1000 word HTML post out
- Post editor: editable title, excerpt, content with Preview/HTML tabs
- Category dropdown (from WP categories)
- Publish date/time picker: schedule or backdate; blank = publish now
- Draft / Publish toggle
- SEO panel: slug validator, meta title + description with character counters, focus keyword; stays visible after save
- Yoast auto-fill on save
- Pexels image picker: auto-searches AI image keywords, 6-photo grid, sets featured image
- Business Profile settings feed the system prompt (per-client voice)
- View-post link opens in a new tab

## File Structure
```
ai-post-o-matic/
└── uilabs-ai-publisher.php   # Single-file plugin (all PHP, CSS, JS in one file)
```

---

## WordPress Options (Settings Storage)
| Option Key | Description |
|---|---|
| `uilabs_anthropic_api_key` | Anthropic Claude API key |
| `uilabs_pexels_api_key` | Pexels API key for image search |
| `uilabs_business_name` | Business name (used in AI prompt) |
| `uilabs_business_location` | City, state (used in AI prompt) |
| `uilabs_business_url` | Website URL (used in AI prompt) |
| `uilabs_business_services` | Services/specialties (used in AI prompt) |
| `uilabs_business_tone` | Writing tone e.g. "friendly and professional" |
| `uilabs_business_audience` | Target audience description |

---

## PHP Functions
| Function | Purpose |
|---|---|
| `uilabs_ai_add_menu()` | Registers WP admin menu and submenu |
| `uilabs_ai_admin_styles()` | Outputs CSS in admin head |
| `uilabs_ai_publisher_page()` | Renders main publisher UI (HTML + JS) |
| `uilabs_ai_settings_page()` | Renders settings form |
| `uilabs_ajax_generate_post()` | AJAX: calls Claude API, returns JSON post data |
| `uilabs_ajax_publish_post()` | AJAX: saves post via wp_insert_post(), writes Yoast meta |
| `uilabs_ajax_pexels_search()` | AJAX: searches Pexels API, returns photo array |
| `uilabs_ajax_set_pexels_image()` | AJAX: downloads Pexels photo, sets as featured image |

---

## AJAX Actions (all require nonce `uilabs_ai_nonce`, capability `edit_posts`)
| Action | Handler |
|---|---|
| `uilabs_generate_post` | `uilabs_ajax_generate_post` |
| `uilabs_publish_post` | `uilabs_ajax_publish_post` |
| `uilabs_pexels_search` | `uilabs_ajax_pexels_search` |
| `uilabs_set_pexels_image` | `uilabs_ajax_set_pexels_image` |

---

## JavaScript Globals (localized via inline script)
| Variable | Value |
|---|---|
| `UILABS_AJAX_URL` | `admin-ajax.php` URL |
| `UILABS_NONCE` | WP nonce for AJAX requests |
| `UILABS_HAS_PEXELS` | `true`/`false` — whether Pexels key is configured |

## JavaScript State Variables
| Variable | Purpose |
|---|---|
| `uilabsMode` | `'draft'` or `'publish'` |
| `uilabsContent` | Current post HTML content |
| `uilabsSlug` | Current post slug |
| `uilabsPostId` | Post ID after saving (used for featured image) |
| `uilabsSelectedImg` | Selected Pexels photo `{ url, photographer, photographer_url }` |
| `uilabsImageKeywords` | Array of image keyword strings from AI |

---

## Yoast SEO Integration
When a post is saved, the plugin writes directly to Yoast's post meta fields:
- `_yoast_wpseo_title` — meta title
- `_yoast_wpseo_metadesc` — meta description
- `_yoast_wpseo_focuskw` — focus keyword

---

## AI Model
- **Model:** `claude-sonnet-5-5`
- **Max tokens:** 4000
- **API endpoint:** `https://api.anthropic.com/v1/messages`
- The system prompt is dynamically built from Business Profile settings
- Returns JSON with: `title`, `content`, `excerpt`, `slug`, `meta_title`, `meta_description`, `focus_keyword`, `image_keywords[]`, `tags[]`

---

## Pexels Integration
- **API endpoint:** `https://api.pexels.com/v1/search`
- Returns 6 landscape photos per search
- Selected photo is downloaded server-side via `wp_remote_get()` and attached to post via `wp_insert_attachment()` + `set_post_thumbnail()`

---

## Key Design Notes
- **Single PHP file** — all CSS, JS, and PHP in `uilabs-ai-publisher.php`
- **No loopback HTTP** — all WP operations use native functions (`wp_insert_post`, `update_post_meta`, etc.) not REST API calls, because SiteGround blocks server-to-server loopback requests
- **No external JS libraries** — vanilla JS only
- **Function/option prefix:** `uilabs_` (kept for backward compatibility even after rebrand to AI Post-O-Matic)
- **Menu slug:** `uilabs-ai-publisher` (kept for backward compatibility)
- **Nonce name:** `uilabs_ai_nonce`
- Plugin is designed to be multi-site deployable — Business Profile settings allow it to write for any client's brand

---

## Admin Menu
- **Main page slug:** `uilabs-ai-publisher`
- **Settings page slug:** `uilabs-ai-settings`
- **Menu icon:** `dashicons-edit-large`
- **Position:** 30

---

## SiteGround-Specific Notes
- Server blocks loopback HTTP requests — never use `wp_remote_post` to call your own site's REST API
- Use native WP functions for all post/media operations
- Plugin is installed at: `/home/customer/www/uilabs.com/public_html/wp-content/plugins/ai-post-o-matic/`

---

## Coding Constraints
- The dynamic system prompt uses a PHP heredoc (`<<<PROMPT`) to avoid mixed-quote parse errors. Do not convert it to a double-quoted string.

---

## Known Issues / Deferred
- **Gemini image generation:** explored and shelved. Needs billing on the Google Cloud project (~$0.039/image). Model `gemini-2.5-flash-image` via `generateContent`. Can be re-added once billing is active.
- **API keys:** Anthropic and Gemini keys pasted into old chat sessions were treated as compromised and rotated. Never reuse keys from old transcripts; keys live only in WP options.

---

## Changelog
- **2.0.2** (2026-10-09): model → `claude-sonnet-5-5`; non-200 Anthropic responses show the API's error message instead of "invalid JSON". Source moved into git (github.com/uilabs/ai-post-o-matic).
- **2.0.1**: baseline imported from the earlier session's zip.
