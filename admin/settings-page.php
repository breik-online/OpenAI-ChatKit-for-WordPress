<div class="wrap">
    <h1><?php esc_html_e('ChatKit Settings', 'chatkit-wp'); ?></h1>

    <?php if (isset($is_wpml_active) && $is_wpml_active && !empty($available_languages)): ?>
    <div class="notice notice-info" style="margin: 20px 0; padding: 15px;">
        <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
            <div>
                <strong style="font-size: 14px;"><?php esc_html_e('Multilingual Mode', 'chatkit-wp'); ?></strong>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <?php esc_html_e('Currently editing:', 'chatkit-wp'); ?>
                <?php foreach ($available_languages as $lang): ?>
                    <?php 
                    $is_current = ($lang['code'] === $current_language);
                    $is_default = ($lang['code'] === $default_language);
                    $style = $is_current 
                        ? 'background: #2271b1; color: white; padding: 4px 10px; border-radius: 4px; font-weight: bold;' 
                        : 'background: #f0f0f1; color: #50575e; padding: 4px 10px; border-radius: 4px; cursor: pointer;';
                    ?>
                    <span style="<?php echo esc_attr($style); ?>" <?php if (!$is_current): ?>title="<?php esc_attr_e('Switch to this language using the admin bar', 'chatkit-wp'); ?>"<?php endif; ?>>
                        <?php if (!empty($lang['flag'])): ?>
                            <img src="<?php echo esc_url($lang['flag']); ?>" alt="" style="height: 12px; margin-right: 4px; vertical-align: middle;">
                        <?php endif; ?>
                        <?php echo esc_html($lang['name']); ?>
                        <?php if ($is_default): ?>
                            <small>(<?php esc_html_e('default', 'chatkit-wp'); ?>)</small>
                        <?php endif; ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
        <p class="description" style="margin-top: 10px; margin-bottom: 0;">
            <?php if ($current_language === $default_language): ?>
                <?php esc_html_e('You are editing the DEFAULT language. These values will be used as fallback for untranslated languages.', 'chatkit-wp'); ?>
            <?php else: ?>
                <?php esc_html_e('You are editing a TRANSLATION. Leave fields empty to use the default language value.', 'chatkit-wp'); ?>
            <?php endif; ?>
            <br>
            <?php esc_html_e('Use the WordPress admin bar language switcher to change language.', 'chatkit-wp'); ?>
        </p>
        <?php if (empty($current_language)): ?>
        <p class="description" style="margin-top: 5px; color: #d63638;">
            <strong><?php esc_html_e('Warning:', 'chatkit-wp'); ?></strong> <?php esc_html_e('Could not detect current language. Make sure WPML admin language switcher is enabled.', 'chatkit-wp'); ?>
        </p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div style="background: #fff; padding: 20px; margin: 20px 0; border-left: 4px solid #2271b1;">
        <h3>📖 <?php esc_html_e('How to use this plugin', 'chatkit-wp'); ?></h3>
        <ol>
            <li><?php esc_html_e('Create a workflow on', 'chatkit-wp'); ?> <a href="https://platform.openai.com/agent-builder" target="_blank">OpenAI Agent Builder</a></li>
            <li><?php esc_html_e('Copy the Workflow ID', 'chatkit-wp'); ?> (<?php esc_html_e('starts with', 'chatkit-wp'); ?> <code>wf_</code>)</li>
            <li><?php esc_html_e('Generate an API Key from', 'chatkit-wp'); ?> <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Dashboard</a></li>
            <li><?php esc_html_e('Register your domain at', 'chatkit-wp'); ?> <a href="https://platform.openai.com/settings/organization/chatkit" target="_blank">OpenAI ChatKit Domain Allowlist</a></li>
            <li><?php esc_html_e('Enter credentials below (including Domain Key)', 'chatkit-wp'); ?></li>
            <li><?php esc_html_e('Add shortcode', 'chatkit-wp'); ?> <code>[openai_chatkit]</code> <?php esc_html_e('OR', 'chatkit-wp'); ?> <code>[chatkit]</code> <?php esc_html_e('OR enable "Show on all pages"', 'chatkit-wp'); ?></li>
        </ol>
    </div>

    <form method="post" action="">
        <?php wp_nonce_field('chatkit_settings_save'); ?>
        <?php if (isset($current_language) && !empty($current_language)): ?>
        <input type="hidden" name="chatkit_save_language" value="<?php echo esc_attr($current_language); ?>">
        <?php endif; ?>

        <h2 class="nav-tab-wrapper">
            <a href="#tab-basic" class="nav-tab nav-tab-active"><?php esc_html_e('Basic Settings', 'chatkit-wp'); ?></a>
            <a href="#tab-appearance" class="nav-tab"><?php esc_html_e('Appearance', 'chatkit-wp'); ?></a>
            <a href="#tab-messages" class="nav-tab"><?php esc_html_e('Messages & Prompts', 'chatkit-wp'); ?></a>
            <a href="#tab-advanced" class="nav-tab"><?php esc_html_e('Advanced', 'chatkit-wp'); ?></a>
        </h2>

        <!-- TAB 1: Basic Settings -->
        <div id="tab-basic" class="tab-content" style="display:block;">
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="chatkit_api_key"><?php esc_html_e('OpenAI API Key', 'chatkit-wp'); ?> *</label></th>
                    <td>
                        <input type="password" id="chatkit_api_key" name="chatkit_api_key"
                               value="<?php echo esc_attr($api_key); ?>"
                               class="regular-text"
                               placeholder="sk-proj-...">
                        <p class="description">
                            ⚠️ <?php esc_html_e('IMPORTANT: For security, add this key to wp-config.php:', 'chatkit-wp'); ?><br>
                            <code>define('CHATKIT_OPENAI_API_KEY', 'sk-proj-...');</code>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="chatkit_workflow_id"><?php esc_html_e('Workflow ID', 'chatkit-wp'); ?> *</label></th>
                    <td>
                        <input type="text" id="chatkit_workflow_id" name="chatkit_workflow_id"
                               value="<?php echo esc_attr($workflow_id); ?>"
                               class="regular-text"
                               placeholder="wf_...">
                        <p class="description">
                            <?php esc_html_e('ID of the workflow created in Agent Builder', 'chatkit-wp'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e('File Upload Configuration', 'chatkit-wp'); ?></th>
                    <td>
                        <div style="background:#d1ecf1; border-left:3px solid #0c5460; padding:10px; margin-bottom:15px;">
                            <strong>ℹ️ <?php esc_html_e('How to enable file uploads:', 'chatkit-wp'); ?></strong><br>
                            1️⃣ <?php esc_html_e('Register your domain at', 'chatkit-wp'); ?> <a href="https://platform.openai.com/settings/organization/chatkit" target="_blank"><?php esc_html_e('OpenAI ChatKit Domain Allowlist', 'chatkit-wp'); ?></a><br>
                            2️⃣ <?php esc_html_e('Add domain:', 'chatkit-wp'); ?> <code><?php echo esc_html(parse_url(home_url(), PHP_URL_HOST)); ?></code> (<?php esc_html_e('no https://, no www', 'chatkit-wp'); ?>)<br>
                            3️⃣ <?php esc_html_e('Wait for VERIFIED status (green)', 'chatkit-wp'); ?><br>
                            4️⃣ <?php esc_html_e('Enable file uploads in Advanced tab', 'chatkit-wp'); ?><br>
                            <br>
                            <em>✅ <?php esc_html_e('With HostedApiConfig (used by this plugin), domain verification is automatic - no keys needed!', 'chatkit-wp'); ?></em>
                        </div>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e('Display Options', 'chatkit-wp'); ?></th>
                    <td>
                        <label style="display:block; margin-bottom:12px;">
                            <input type="checkbox" name="chatkit_show_everywhere"
                                   <?php checked($show_everywhere, true); ?>>
                            <strong><?php esc_html_e('Show widget on ALL pages automatically', 'chatkit-wp'); ?></strong>
                        </label>
                        <p class="description">
                            ⚠️ <?php esc_html_e('If enabled, widget appears on every page (except exclusions below). Otherwise use [openai_chatkit] or [chatkit] shortcode.', 'chatkit-wp'); ?>
                        </p>
                        
                        <div style="margin-top:20px; padding:15px; background:#f9f9f9; border-left:3px solid #2271b1;">
                            <h4 style="margin-top:0;"><?php esc_html_e('Exclusions', 'chatkit-wp'); ?></h4>
                            
                            <p><strong><?php esc_html_e('Exclude Specific Pages/Posts (by ID):', 'chatkit-wp'); ?></strong></p>
                            <input type="text" name="chatkit_exclude_ids"
                                   value="<?php echo esc_attr(get_option('chatkit_exclude_ids', '')); ?>"
                                   class="large-text" placeholder="1, 5, 12, 47">
                            <p class="description">
                                <?php esc_html_e('Comma-separated page/post IDs to exclude. Find ID in URL when editing.', 'chatkit-wp'); ?>
                            </p>
                            
                            <p style="margin-top:15px;"><strong><?php esc_html_e('Exclude Page Types:', 'chatkit-wp'); ?></strong></p>
                            <label style="display:block; margin-bottom:5px;">
                                <input type="checkbox" name="chatkit_exclude_home"
                                       <?php checked(get_option('chatkit_exclude_home', false), true); ?>>
                                <?php esc_html_e('Homepage', 'chatkit-wp'); ?>
                            </label>
                            <label style="display:block; margin-bottom:5px;">
                                <input type="checkbox" name="chatkit_exclude_archive"
                                       <?php checked(get_option('chatkit_exclude_archive', false), true); ?>>
                                <?php esc_html_e('Archives (categories, tags)', 'chatkit-wp'); ?>
                            </label>
                            <label style="display:block; margin-bottom:5px;">
                                <input type="checkbox" name="chatkit_exclude_search"
                                       <?php checked(get_option('chatkit_exclude_search', false), true); ?>>
                                <?php esc_html_e('Search results page', 'chatkit-wp'); ?>
                            </label>
                            <label style="display:block;">
                                <input type="checkbox" name="chatkit_exclude_404"
                                       <?php checked(get_option('chatkit_exclude_404', false), true); ?>>
                                <?php esc_html_e('404 error page', 'chatkit-wp'); ?>
                            </label>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- TAB 2: Appearance -->
        <div id="tab-appearance" class="tab-content" style="display:none;">
            <?php if (isset($is_wpml_active) && $is_wpml_active && !empty($current_language)): ?>
            <div style="background: #fff3cd; border-left: 3px solid #ffc107; padding: 10px 15px; margin-bottom: 20px;">
                <small><span style="color: #856404;">&#127760;</span> <?php esc_html_e('Fields marked with a globe icon can be translated per language.', 'chatkit-wp'); ?></small>
            </div>
            <?php endif; ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="chatkit_button_text">
                            <?php esc_html_e('Button Text', 'chatkit-wp'); ?>
                            <?php if (isset($is_wpml_active) && $is_wpml_active): ?><span title="<?php esc_attr_e('Translatable per language', 'chatkit-wp'); ?>">&#127760;</span><?php endif; ?>
                        </label>
                    </th>
                    <td>
                        <input type="text" id="chatkit_button_text" name="chatkit_button_text"
                               value="<?php echo esc_attr($button_text); ?>"
                               class="regular-text"
                               placeholder="Chat now">
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="chatkit_close_text">
                            <?php esc_html_e('Close Button Text', 'chatkit-wp'); ?>
                            <?php if (isset($is_wpml_active) && $is_wpml_active): ?><span title="<?php esc_attr_e('Translatable per language', 'chatkit-wp'); ?>">&#127760;</span><?php endif; ?>
                        </label>
                    </th>
                    <td>
                        <input type="text" id="chatkit_close_text" name="chatkit_close_text"
                               value="<?php echo esc_attr($close_text); ?>"
                               class="regular-text"
                               placeholder="✕">
                        <p class="description"><?php esc_html_e('Character shown when chat is open (default: ✕)', 'chatkit-wp'); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="chatkit_accent_color"><?php esc_html_e('Accent Color', 'chatkit-wp'); ?></label></th>
                    <td>
                        <input type="color" id="chatkit_accent_color" name="chatkit_accent_color"
                               value="<?php echo esc_attr($accent_color); ?>">
                        <p style="margin-top:8px;"><strong><?php esc_html_e('Intensity Level:', 'chatkit-wp'); ?></strong></p>
                        <select name="chatkit_accent_level">
                            <option value="0" <?php selected($accent_level ?? '2', '0'); ?>>0 - <?php esc_html_e('Subtle', 'chatkit-wp'); ?></option>
                            <option value="1" <?php selected($accent_level ?? '2', '1'); ?>>1 - <?php esc_html_e('Light', 'chatkit-wp'); ?></option>
                            <option value="2" <?php selected($accent_level ?? '2', '2'); ?>>2 - <?php esc_html_e('Normal', 'chatkit-wp'); ?></option>
                            <option value="3" <?php selected($accent_level ?? '2', '3'); ?>>3 - <?php esc_html_e('Bold', 'chatkit-wp'); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e('Controls the intensity of the accent color throughout the UI', 'chatkit-wp'); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="chatkit_theme_mode"><?php esc_html_e('Theme', 'chatkit-wp'); ?></label></th>
                    <td>
                        <select id="chatkit_theme_mode" name="chatkit_theme_mode">
                            <option value="dark" <?php selected($theme_mode, 'dark'); ?>>Dark</option>
                            <option value="light" <?php selected($theme_mode, 'light'); ?>>Light</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="chatkit_button_size"><?php esc_html_e('Button Size', 'chatkit-wp'); ?></label></th>
                    <td>
                        <select id="chatkit_button_size" name="chatkit_button_size">
                            <option value="small" <?php selected($button_size, 'small'); ?>><?php esc_html_e('Small', 'chatkit-wp'); ?></option>
                            <option value="medium" <?php selected($button_size, 'medium'); ?>><?php esc_html_e('Medium', 'chatkit-wp'); ?></option>
                            <option value="large" <?php selected($button_size, 'large'); ?>><?php esc_html_e('Large', 'chatkit-wp'); ?></option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="chatkit_button_position"><?php esc_html_e('Button Position', 'chatkit-wp'); ?></label></th>
                    <td>
                        <select id="chatkit_button_position" name="chatkit_button_position">
                            <option value="bottom-right" <?php selected($button_position, 'bottom-right'); ?>><?php esc_html_e('Bottom Right', 'chatkit-wp'); ?></option>
                            <option value="bottom-left" <?php selected($button_position, 'bottom-left'); ?>><?php esc_html_e('Bottom Left', 'chatkit-wp'); ?></option>
                            <option value="top-right" <?php selected($button_position, 'top-right'); ?>><?php esc_html_e('Top Right', 'chatkit-wp'); ?></option>
                            <option value="top-left" <?php selected($button_position, 'top-left'); ?>><?php esc_html_e('Top Left', 'chatkit-wp'); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e('Choose where the chat button appears on screen', 'chatkit-wp'); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="chatkit_border_radius"><?php esc_html_e('Border Radius', 'chatkit-wp'); ?></label></th>
                    <td>
                        <select id="chatkit_border_radius" name="chatkit_border_radius">
                            <option value="square" <?php selected($border_radius, 'square'); ?>><?php esc_html_e('Square', 'chatkit-wp'); ?></option>
                            <option value="round" <?php selected($border_radius, 'round'); ?>><?php esc_html_e('Round', 'chatkit-wp'); ?></option>
                            <option value="extra-round" <?php selected($border_radius, 'extra-round'); ?>><?php esc_html_e('Extra Round', 'chatkit-wp'); ?></option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="chatkit_shadow_style"><?php esc_html_e('Shadow Style', 'chatkit-wp'); ?></label></th>
                    <td>
                        <select id="chatkit_shadow_style" name="chatkit_shadow_style">
                            <option value="subtle" <?php selected($shadow_style, 'subtle'); ?>><?php esc_html_e('Subtle', 'chatkit-wp'); ?></option>
                            <option value="normal" <?php selected($shadow_style, 'normal'); ?>><?php esc_html_e('Normal', 'chatkit-wp'); ?></option>
                            <option value="bold" <?php selected($shadow_style, 'bold'); ?>><?php esc_html_e('Bold', 'chatkit-wp'); ?></option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="chatkit_density"><?php esc_html_e('UI Density', 'chatkit-wp'); ?></label></th>
                    <td>
                        <select id="chatkit_density" name="chatkit_density">
                            <option value="compact" <?php selected($density ?? 'normal', 'compact'); ?>><?php esc_html_e('Compact', 'chatkit-wp'); ?></option>
                            <option value="normal" <?php selected($density ?? 'normal', 'normal'); ?>><?php esc_html_e('Normal', 'chatkit-wp'); ?></option>
                            <option value="comfortable" <?php selected($density ?? 'normal', 'comfortable'); ?>><?php esc_html_e('Comfortable', 'chatkit-wp'); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e('Controls spacing and padding in the chat UI', 'chatkit-wp'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e('Custom Typography', 'chatkit-wp'); ?></th>
                    <td>
                        <div style="background:#d1ecf1; border-left:3px solid #0c5460; padding:10px; margin-bottom:15px;">
                            <strong>ℹ️ <?php esc_html_e('Tip:', 'chatkit-wp'); ?></strong> <?php esc_html_e('Leave font family empty to use ChatKit default fonts (recommended).', 'chatkit-wp'); ?>
                        </div>
                        
                        <label style="display:block; margin-bottom:12px;">
                            <input type="checkbox" name="chatkit_enable_custom_font"
                                   <?php checked(get_option('chatkit_enable_custom_font', false), true); ?>>
                            <strong><?php esc_html_e('Enable custom font', 'chatkit-wp'); ?></strong>
                        </label>
                        
                        <p><strong><?php esc_html_e('Font Family:', 'chatkit-wp'); ?></strong></p>
                        <input type="text" name="chatkit_font_family"
                               value="<?php echo esc_attr($font_family ?? ''); ?>"
                               class="regular-text"
                               placeholder="'Inter', 'Roboto', sans-serif">
                        <p class="description"><?php esc_html_e('Must be a web-safe font or loaded via theme/plugin', 'chatkit-wp'); ?></p>
                        
                        <p style="margin-top:12px;"><strong><?php esc_html_e('Base Font Size (px):', 'chatkit-wp'); ?></strong></p>
                        <input type="number" name="chatkit_font_size"
                               value="<?php echo esc_attr($font_size ?? '16'); ?>"
                               min="12" max="24" style="width:80px;">
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="chatkit_locale"><?php esc_html_e('Language Locale', 'chatkit-wp'); ?></label></th>
                    <td>
                        <input type="text" id="chatkit_locale" name="chatkit_locale"
                               value="<?php echo esc_attr($locale ?? ''); ?>"
                               class="regular-text" placeholder="en-US">
                        <p class="description">
                            <?php esc_html_e('Examples:', 'chatkit-wp'); ?> <code>en-US</code>, <code>it-IT</code>, <code>de-DE</code>, <code>fr-FR</code><br>
                            <?php if (defined('ICL_SITEPRESS_VERSION')): ?>
                                <strong><?php esc_html_e('WPML:', 'chatkit-wp'); ?></strong> <?php esc_html_e('Leave empty for automatic detection from WPML current language.', 'chatkit-wp'); ?>
                            <?php elseif (function_exists('pll_current_language')): ?>
                                <strong><?php esc_html_e('Polylang:', 'chatkit-wp'); ?></strong> <?php esc_html_e('Leave empty for automatic detection from Polylang current language.', 'chatkit-wp'); ?>
                            <?php else: ?>
                                <?php esc_html_e('Leave empty to use browser default.', 'chatkit-wp'); ?>
                            <?php endif; ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- TAB 3: Messages & Prompts -->
        <div id="tab-messages" class="tab-content" style="display:none;">
            <?php if (isset($is_wpml_active) && $is_wpml_active && !empty($current_language)): ?>
            <div style="background: #e7f3fe; border-left: 3px solid #2196F3; padding: 12px 15px; margin-bottom: 20px;">
                <strong><?php esc_html_e('Per-Language Editing', 'chatkit-wp'); ?>:</strong>
                <?php 
                $lang_name = isset($available_languages[$current_language]) ? $available_languages[$current_language]['name'] : strtoupper($current_language);
                printf(
                    /* translators: %s: Current language name */
                    esc_html__('You are editing texts for %s. Switch language in the admin bar to edit other languages.', 'chatkit-wp'),
                    '<strong>' . esc_html($lang_name) . '</strong>'
                );
                ?>
                <?php if ($current_language !== $default_language): ?>
                <br><small><?php esc_html_e('Leave fields empty to use the default language value.', 'chatkit-wp'); ?></small>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="chatkit_greeting_text"><?php esc_html_e('Greeting Text', 'chatkit-wp'); ?></label></th>
                    <td>
                        <input type="text" id="chatkit_greeting_text" name="chatkit_greeting_text"
                               value="<?php echo esc_attr($greeting_text); ?>"
                               class="regular-text"
                               placeholder="How can I help you today?">
                        <p class="description"><?php esc_html_e('Text shown when chat opens.', 'chatkit-wp'); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="chatkit_placeholder_text"><?php esc_html_e('Input Placeholder', 'chatkit-wp'); ?></label></th>
                    <td>
                        <input type="text" id="chatkit_placeholder_text" name="chatkit_placeholder_text"
                               value="<?php echo esc_attr($placeholder_text); ?>"
                               class="regular-text"
                               placeholder="Send a message...">
                        <p class="description"><?php esc_html_e('Placeholder text in the message input field.', 'chatkit-wp'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <td colspan="2"><hr><h3><?php esc_html_e('Quick Prompts (up to 5)', 'chatkit-wp'); ?></h3></td>
                </tr>

                <?php for ($i = 1; $i <= 5; $i++): ?>
                <tr>
                    <th scope="row"><?php printf(esc_html__('Quick Prompt %d', 'chatkit-wp'), $i); ?></th>
                    <td>
                        <p><strong><?php esc_html_e('Label:', 'chatkit-wp'); ?></strong></p>
                        <input type="text" name="chatkit_default_prompt_<?php echo $i; ?>"
                               value="<?php echo esc_attr(${'default_prompt_' . $i}); ?>"
                               class="regular-text" placeholder="<?php esc_attr_e('Button text', 'chatkit-wp'); ?>">
                        
                        <p style="margin-top:8px;"><strong><?php esc_html_e('Text:', 'chatkit-wp'); ?></strong></p>
                        <input type="text" name="chatkit_default_prompt_<?php echo $i; ?>_text"
                               value="<?php echo esc_attr(${'default_prompt_' . $i . '_text'}); ?>"
                               class="regular-text" placeholder="<?php esc_attr_e('Actual message sent', 'chatkit-wp'); ?>">
                        
                        <p style="margin-top:8px;"><strong><?php esc_html_e('Icon:', 'chatkit-wp'); ?></strong></p>
                        <select name="chatkit_default_prompt_<?php echo $i; ?>_icon">
                            <option value="circle-question" <?php selected(${'default_prompt_' . $i . '_icon'} ?? 'circle-question', 'circle-question'); ?>>❓ <?php esc_html_e('Question', 'chatkit-wp'); ?></option>
                            <option value="search" <?php selected(${'default_prompt_' . $i . '_icon'} ?? 'circle-question', 'search'); ?>>🔍 <?php esc_html_e('Search', 'chatkit-wp'); ?></option>
                            <option value="write" <?php selected(${'default_prompt_' . $i . '_icon'} ?? 'circle-question', 'write'); ?>>✍️ <?php esc_html_e('Write', 'chatkit-wp'); ?></option>
                            <option value="home" <?php selected(${'default_prompt_' . $i . '_icon'} ?? 'circle-question', 'home'); ?>>🏠 <?php esc_html_e('Home', 'chatkit-wp'); ?></option>
                            <option value="info" <?php selected(${'default_prompt_' . $i . '_icon'} ?? 'circle-question', 'info'); ?>>ℹ️ <?php esc_html_e('Info', 'chatkit-wp'); ?></option>
                        </select>
                        <?php if ($i === 1): ?>
                        <p class="description"><?php esc_html_e('Actual message sent when clicking this prompt.', 'chatkit-wp'); ?></p>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endfor; ?>
            </table>
        </div>

        <!-- TAB 4: Advanced -->
        <div id="tab-advanced" class="tab-content" style="display:none;">
            <?php if (isset($is_wpml_active) && $is_wpml_active && !empty($current_language)): ?>
            <div style="background: #fff3cd; border-left: 3px solid #ffc107; padding: 10px 15px; margin-bottom: 20px;">
                <small><span style="color: #856404;">&#127760;</span> <?php esc_html_e('Header Title and Disclaimer text can be translated per language.', 'chatkit-wp'); ?></small>
            </div>
            <?php endif; ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e('File Attachments', 'chatkit-wp'); ?></th>
                    <td>
                        <div style="background:#d1ecf1; border-left:3px solid #0c5460; padding:10px; margin-bottom:15px;">
                            <strong>ℹ️ <?php esc_html_e('Requirements:', 'chatkit-wp'); ?></strong><br>
                            ✅ <?php esc_html_e('Domain registered on OpenAI ChatKit Dashboard', 'chatkit-wp'); ?><br>
                            ✅ <?php esc_html_e('Domain Key configured in Basic Settings', 'chatkit-wp'); ?><br>
                            ✅ <?php esc_html_e('Workflow supports file attachments (automatic for most workflows)', 'chatkit-wp'); ?>
                        </div>
                        
                        <label style="display:block; margin-bottom:12px;">
                            <input type="checkbox" name="chatkit_enable_attachments"
                                   <?php checked($enable_attachments, true); ?>>
                            <strong><?php esc_html_e('Enable file uploads', 'chatkit-wp'); ?></strong>
                        </label>
                        
                        <p style="margin-top:15px;"><strong><?php esc_html_e('Max file size (MB):', 'chatkit-wp'); ?></strong></p>
                        <input type="number" name="chatkit_attachment_max_size"
                               value="<?php echo esc_attr($attachment_max_size ?? '20'); ?>"
                               min="1" max="100" style="width:80px;"> MB
                        
                        <p style="margin-top:12px;"><strong><?php esc_html_e('Max files per message:', 'chatkit-wp'); ?></strong></p>
                        <input type="number" name="chatkit_attachment_max_count"
                               value="<?php echo esc_attr($attachment_max_count ?? '3'); ?>"
                               min="1" max="10" style="width:80px;"> <?php esc_html_e('files', 'chatkit-wp'); ?>
                        
                        <p class="description" style="margin-top:8px;">
                            <?php esc_html_e('Supported: PDF, Images (PNG, JPG, GIF, WebP), TXT', 'chatkit-wp'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e('Advanced Features', 'chatkit-wp'); ?></th>
                    <td>
                        <label style="display:block;">
                            <input type="checkbox" name="chatkit_persistent_sessions"
                                   <?php checked($persistent_sessions, true); ?>>
                            <?php esc_html_e('Keep conversation history (via cookie)', 'chatkit-wp'); ?>
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e('UI Regions', 'chatkit-wp'); ?></th>
                    <td>
                        <label style="display:block; margin-bottom:8px;">
                            <input type="checkbox" name="chatkit_show_header"
                                   <?php checked($show_header ?? true, true); ?>>
                            <?php esc_html_e('Show header', 'chatkit-wp'); ?>
                        </label>
                        <label style="display:block;">
                            <input type="checkbox" name="chatkit_show_history"
                                   <?php checked($show_history ?? true, true); ?>>
                            <?php esc_html_e('Show conversation history', 'chatkit-wp'); ?>
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e('Header Buttons', 'chatkit-wp'); ?></th>
                    <td>
                        <div style="background:#fff3cd; border-left:3px solid #ffc107; padding:10px; margin-bottom:15px;">
                            <strong>⚠️ <?php esc_html_e('Validated Icons Only:', 'chatkit-wp'); ?></strong> home, settings-cog, menu
                        </div>
                        
                        <div style="margin-bottom:20px;">
                            <p>
                                <strong><?php esc_html_e('Header Title (optional):', 'chatkit-wp'); ?></strong>
                                <?php if (isset($is_wpml_active) && $is_wpml_active): ?><span title="<?php esc_attr_e('Translatable per language', 'chatkit-wp'); ?>">&#127760;</span><?php endif; ?>
                            </p>
                            <input type="text" name="chatkit_header_title_text"
                                   value="<?php echo esc_attr($header_title_text ?? ''); ?>"
                                   class="regular-text" placeholder="<?php esc_attr_e('Support Chat', 'chatkit-wp'); ?>">
                            <p class="description"><?php esc_html_e('Custom static title. Leave empty to show thread titles.', 'chatkit-wp'); ?></p>
                        </div>
                        
                        <div style="margin-bottom:20px;">
                            <p><strong><?php esc_html_e('Left Button:', 'chatkit-wp'); ?></strong></p>
                            <select name="chatkit_header_left_icon" style="margin-bottom:8px;">
                                <option value=""><?php esc_html_e('None', 'chatkit-wp'); ?></option>
                                <option value="menu" <?php selected($header_left_icon ?? '', 'menu'); ?>><?php esc_html_e('☰ Menu', 'chatkit-wp'); ?></option>
                                <option value="settings-cog" <?php selected($header_left_icon ?? '', 'settings-cog'); ?>><?php esc_html_e('⚙️ Settings', 'chatkit-wp'); ?></option>
                                <option value="home" <?php selected($header_left_icon ?? '', 'home'); ?>><?php esc_html_e('🏠 Home', 'chatkit-wp'); ?></option>
                            </select>
                            <input type="url" name="chatkit_header_left_url"
                                   value="<?php echo esc_attr($header_left_url ?? ''); ?>"
                                   class="regular-text" placeholder="https://example.com">
                        </div>
                        
                        <div>
                            <p><strong><?php esc_html_e('Right Button:', 'chatkit-wp'); ?></strong></p>
                            <select name="chatkit_header_right_icon" style="margin-bottom:8px;">
                                <option value=""><?php esc_html_e('None', 'chatkit-wp'); ?></option>
                                <option value="home" <?php selected($header_right_icon ?? '', 'home'); ?>><?php esc_html_e('🏠 Home', 'chatkit-wp'); ?></option>
                                <option value="settings-cog" <?php selected($header_right_icon ?? '', 'settings-cog'); ?>><?php esc_html_e('⚙️ Settings', 'chatkit-wp'); ?></option>
                                <option value="menu" <?php selected($header_right_icon ?? '', 'menu'); ?>><?php esc_html_e('☰ Menu', 'chatkit-wp'); ?></option>
                            </select>
                            <input type="url" name="chatkit_header_right_url"
                                   value="<?php echo esc_attr($header_right_url ?? ''); ?>"
                                   class="regular-text" placeholder="https://example.com">
                        </div>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <?php esc_html_e('Disclaimer', 'chatkit-wp'); ?>
                        <?php if (isset($is_wpml_active) && $is_wpml_active): ?><span title="<?php esc_attr_e('Translatable per language', 'chatkit-wp'); ?>">&#127760;</span><?php endif; ?>
                    </th>
                    <td>
                        <textarea name="chatkit_disclaimer_text" rows="3" class="large-text"
                                  placeholder="<?php esc_attr_e('AI can make mistakes. Check important info.', 'chatkit-wp'); ?>"><?php echo esc_textarea($disclaimer_text ?? ''); ?></textarea>
                        <p class="description"><?php esc_html_e('Markdown text displayed below the composer. Leave empty to disable.', 'chatkit-wp'); ?></p>
                        
                        <label style="display:block; margin-top:10px;">
                            <input type="checkbox" name="chatkit_disclaimer_high_contrast"
                                   <?php checked($disclaimer_high_contrast ?? false, true); ?>>
                            <?php esc_html_e('High contrast (more visible)', 'chatkit-wp'); ?>
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="chatkit_initial_thread_id"><?php esc_html_e('Initial Thread ID', 'chatkit-wp'); ?></label></th>
                    <td>
                        <input type="text" id="chatkit_initial_thread_id" name="chatkit_initial_thread_id"
                               value="<?php echo esc_attr($initial_thread_id ?? ''); ?>"
                               class="regular-text" placeholder="thread_...">
                        <p class="description">
                            <?php esc_html_e('Optional: Load a specific thread when chat opens. Leave empty for new thread.', 'chatkit-wp'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>

        <?php submit_button(__('Save Settings', 'chatkit-wp'), 'primary', 'chatkit_save_settings'); ?>
    </form>

    <hr>

    <h2><?php esc_html_e('Test Configuration', 'chatkit-wp'); ?></h2>
    <div id="chatkit-test-result" style="margin-top: 15px;"></div>
    <button type="button" class="button button-secondary" id="chatkit-test-btn">
        🔍 <?php esc_html_e('Test API Connection', 'chatkit-wp'); ?>
    </button>

    <style>
    .tab-content { background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-top: none; }
    .form-table th { width: 200px; }
    </style>

    <script>
    jQuery(document).ready(function($) {
        $('.nav-tab').on('click', function(e) {
            e.preventDefault();
            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            $('.tab-content').hide();
            $($(this).attr('href')).show();
        });
    });

    document.getElementById('chatkit-test-btn').addEventListener('click', async () => {
        const resultDiv = document.getElementById('chatkit-test-result');
        const btn = document.getElementById('chatkit-test-btn');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = '⏳ <?php echo esc_js(__('Testing...', 'chatkit-wp')); ?>';

        try {
            const response = await fetch(<?php echo wp_json_encode(rest_url('chatkit/v1/test')); ?>, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': <?php echo wp_json_encode(wp_create_nonce('wp_rest')); ?>
                }
            });

            const data = await response.json();

            if (response.ok) {
                resultDiv.innerHTML = '<div class="notice notice-success inline"><p>✅ ' + data.message + '</p></div>';
            } else {
                resultDiv.innerHTML = '<div class="notice notice-error inline"><p>❌ ' + (data.message || 'Unknown error') + '</p></div>';
            }
        } catch (error) {
            resultDiv.innerHTML = '<div class="notice notice-error inline"><p>❌ Error: ' + error.message + '</p></div>';
        } finally {
            btn.disabled = false;
            btn.textContent = originalText;
        }
    });
    </script>
</div>