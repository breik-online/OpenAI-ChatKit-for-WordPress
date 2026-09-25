<?php
/**
 * Updates from the GitHub repository's releases, through WordPress's own update screens.
 *
 * The repository is public, so the release check and the download work without any credential.
 * A fine-grained GitHub token (read-only Contents on this one repository) is optional: it lifts
 * the anonymous rate limit and keeps updates working should the repository ever go private. It
 * comes from the CHATKIT_WP_GITHUB_TOKEN constant, else from the settings screen; each site can
 * carry its own, so one is revoked without touching the others.
 */

if (!defined('ABSPATH')) exit;

class ChatKit_WP_Updater {

    const REPO      = 'breik-online/OpenAI-ChatKit-for-WordPress';
    const ASSET     = 'chatkit-wp.zip';
    const CACHE     = 'chatkit_wp_release';
    const CACHE_TTL = HOUR_IN_SECONDS;
    const ERROR_TTL = 15 * MINUTE_IN_SECONDS;

    public static function token() {
        if (defined('CHATKIT_WP_GITHUB_TOKEN') && CHATKIT_WP_GITHUB_TOKEN) {
            return (string) CHATKIT_WP_GITHUB_TOKEN;
        }
        return (string) get_option('chatkit_github_token', '');
    }

    public static function token_source() {
        if (defined('CHATKIT_WP_GITHUB_TOKEN') && CHATKIT_WP_GITHUB_TOKEN) {
            return 'constant';
        }
        return '' !== (string) get_option('chatkit_github_token', '') ? 'setting' : 'none';
    }

    /**
     * Whether this site can update itself: the latest release GitHub gave it or why it could not
     * ask, and whether WordPress auto-updates the plugin. Never the token itself.
     */
    public static function status() {
        $release = self::release();
        $error   = is_wp_error($release) ? $release->get_error_message() : null;
        $latest  = is_array($release) ? $release['version'] : null;
        return [
            'token'       => self::token_source(),
            'installed'   => CHATKIT_WP_VERSION,
            'latest'      => $latest,
            'available'   => null !== $latest && version_compare($latest, CHATKIT_WP_VERSION, '>'),
            'error'       => $error,
            'auto_update' => in_array(self::basename(), (array) get_site_option('auto_update_plugins', []), true),
        ];
    }

    public static function basename() {
        return plugin_basename(CHATKIT_WP_PLUGIN_FILE);
    }

    public static function version_from_tag($tag) {
        return ltrim(trim((string) $tag), 'vV');
    }

    public static function forget() {
        delete_site_transient(self::CACHE);
    }

    public function hooks() {
        add_filter('pre_set_site_transient_update_plugins', [$this, 'inject']);
        add_filter('plugins_api', [$this, 'details'], 10, 3);
        add_filter('upgrader_pre_download', [$this, 'download'], 10, 3);
        add_filter('upgrader_source_selection', [$this, 'rename_source'], 10, 4);
        add_action('upgrader_process_complete', [self::class, 'forget']);
        add_action('update_option_chatkit_github_token', [self::class, 'forget']);
    }

    /**
     * The latest release as {version, tag, asset_api_url, body, published, html_url}, or a WP_Error.
     * Cached; failures are cached for a shorter while so an outage does not hit GitHub on every page.
     */
    public static function release($fresh = false) {
        if (!$fresh) {
            $cached = get_site_transient(self::CACHE);
            if (is_array($cached)) {
                return isset($cached['error']) ? new WP_Error('chatkit_wp_update', $cached['error']) : $cached;
            }
        }

        $release = self::fetch();
        if (is_wp_error($release)) {
            set_site_transient(self::CACHE, ['error' => $release->get_error_message(), 'at' => time()], self::ERROR_TTL);
            return $release;
        }
        set_site_transient(self::CACHE, $release, self::CACHE_TTL);
        return $release;
    }

    private static function fetch() {
        $response = wp_remote_get(
            'https://api.github.com/repos/' . self::REPO . '/releases/latest',
            [
                'timeout' => 10,
                'headers' => self::headers('application/vnd.github+json'),
            ]
        );
        if (is_wp_error($response)) {
            return new WP_Error('chatkit_wp_update', 'GitHub unreachable: ' . $response->get_error_message());
        }
        $code = (int) wp_remote_retrieve_response_code($response);
        if (200 !== $code) {
            $hint = 401 === $code ? ' (token invalid or expired)'
                : (403 === $code ? ' (rate limit reached; a GitHub token lifts it)'
                : (404 === $code ? ' (no release yet, or the token has no access to ' . self::REPO . ')' : ''));
            return new WP_Error('chatkit_wp_update', 'GitHub answered HTTP ' . $code . $hint . '.');
        }

        $data  = json_decode(wp_remote_retrieve_body($response), true);
        $asset = null;
        foreach ((array) ($data['assets'] ?? []) as $candidate) {
            if (self::ASSET === ($candidate['name'] ?? '')) {
                $asset = $candidate;
                break;
            }
        }
        if (!is_array($data) || empty($data['tag_name']) || !$asset) {
            return new WP_Error('chatkit_wp_update', 'The latest release has no ' . self::ASSET . ' attached.');
        }

        return [
            'version'       => self::version_from_tag($data['tag_name']),
            'tag'           => (string) $data['tag_name'],
            'asset_api_url' => (string) $asset['url'],
            'body'          => (string) ($data['body'] ?? ''),
            'published'     => (string) ($data['published_at'] ?? ''),
            'html_url'      => (string) ($data['html_url'] ?? ''),
        ];
    }

    /** Request headers; the Authorization header only when there is a token to send. */
    private static function headers($accept) {
        $headers = [
            'Accept'               => $accept,
            'X-GitHub-Api-Version' => '2022-11-28',
            'User-Agent'           => 'chatkit-wp/' . CHATKIT_WP_VERSION . '; ' . home_url(),
        ];
        $token = self::token();
        if ('' !== $token) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }
        return $headers;
    }

    private static function item(array $release) {
        return (object) [
            'id'           => 'github.com/' . self::REPO,
            'slug'         => dirname(self::basename()),
            'plugin'       => self::basename(),
            'new_version'  => $release['version'],
            'url'          => 'https://github.com/' . self::REPO,
            'package'      => $release['asset_api_url'],
            'requires'     => '5.8',
            'requires_php' => '7.4',
            'icons'        => [],
            'banners'      => [],
        ];
    }

    /** Puts the release into WordPress's plugin update list (or its no-update list, which enables the auto-update toggle). */
    public function inject($transient) {
        if (!is_object($transient)) {
            return $transient;
        }
        // "Check again" on Dashboard → Updates should really check again.
        $release = self::release(!empty($_GET['force-check'])); // phpcs:ignore WordPress.Security.NonceVerification
        if (is_wp_error($release)) {
            return $transient;
        }

        $item = self::item($release);
        if (version_compare($release['version'], CHATKIT_WP_VERSION, '>')) {
            $transient->response[self::basename()] = $item;
            unset($transient->no_update[self::basename()]);
        } else {
            $transient->no_update[self::basename()] = $item;
            unset($transient->response[self::basename()]);
        }
        return $transient;
    }

    /** The "View details" modal. */
    public function details($result, $action, $args) {
        if ('plugin_information' !== $action || !isset($args->slug) || dirname(self::basename()) !== $args->slug) {
            return $result;
        }
        $release = self::release();
        if (is_wp_error($release)) {
            return $result;
        }
        return (object) [
            'name'          => 'OpenAI ChatKit for WordPress',
            'slug'          => $args->slug,
            'version'       => $release['version'],
            'author'        => '<a href="https://github.com/' . self::REPO . '">breik</a>',
            'homepage'      => 'https://github.com/' . self::REPO,
            'requires'      => '5.8',
            'requires_php'  => '7.4',
            'last_updated'  => $release['published'],
            'download_link' => $release['asset_api_url'],
            'sections'      => [
                'changelog' => '<h4>' . esc_html($release['tag']) . '</h4>' . wpautop(esc_html($release['body'])),
            ],
        ];
    }

    /**
     * Downloads the release asset. The asset API answers with a redirect to a storage URL that
     * rejects a second (Authorization) credential, so the redirect is followed without it.
     */
    public function download($reply, $package, $upgrader) {
        $prefix = 'https://api.github.com/repos/' . self::REPO . '/releases/assets/';
        if (false !== $reply || !is_string($package) || 0 !== strpos($package, $prefix)) {
            return $reply;
        }

        $response = wp_remote_get(
            $package,
            [
                'timeout'     => 15,
                'redirection' => 0,
                'headers'     => self::headers('application/octet-stream'),
            ]
        );
        if (is_wp_error($response)) {
            return $response;
        }
        $location = wp_remote_retrieve_header($response, 'location');
        if (!$location) {
            return new WP_Error('chatkit_wp_update', 'GitHub did not hand out the release download (HTTP ' . (int) wp_remote_retrieve_response_code($response) . ').');
        }

        if (isset($upgrader->skin)) {
            $upgrader->skin->feedback('downloading_package', 'GitHub release');
        }
        return download_url(is_array($location) ? end($location) : $location, 300);
    }

    /**
     * The zip unpacks to `chatkit-wp/`; a site that installed the plugin under another folder name
     * (a GitHub download unpacks to `OpenAI-ChatKit-for-WordPress-main/`) keeps its folder, or the
     * plugin would come back inactive under a new path.
     */
    public function rename_source($source, $remote_source, $upgrader, $hook_extra) {
        if (empty($hook_extra['plugin']) || self::basename() !== $hook_extra['plugin']) {
            return $source;
        }
        $wanted = dirname(self::basename());
        $actual = basename(untrailingslashit($source));
        if ('.' === $wanted || $wanted === $actual) {
            return $source;
        }
        $target = trailingslashit($remote_source) . $wanted . '/';
        global $wp_filesystem;
        if ($wp_filesystem && $wp_filesystem->move(untrailingslashit($source), untrailingslashit($target), true)) {
            return $target;
        }
        return $source;
    }
}
