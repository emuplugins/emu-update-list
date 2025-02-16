<?php
if (!defined('ABSPATH')) exit;

class Emu_Update_Core {
    private $api_url;
    private $plugin_slug;
    private $plugin_dir;
    private $plugin_file;

    public function __construct($plugin_slug, $plugin_dir, $plugin_file, $api_url = '') {
        $this->plugin_slug = $plugin_slug;
        $this->plugin_dir  = $plugin_dir;
        $this->plugin_file = $plugin_file;
        $this->api_url    = $api_url ? $api_url : 'https://raw.githubusercontent.com/emuplugins/emu-update-core/main/' . $this->plugin_slug . '/info.json';
    
        add_filter('plugins_api', array($this, 'plugin_info'), 20, 3);
        add_filter('site_transient_update_plugins', array($this, 'check_for_update'));
        add_action('upgrader_process_complete', array($this, 'auto_reactivate_plugin_after_update'), 10, 2);
        add_filter('upgrader_source_selection', array($this, 'fix_plugin_directory'), 10, 4);
        add_action('upgrader_install_package_result', array($this, 'verify_installation'), 10, 2);
    }

    private function sanitize_download_url($url) {
        $parts = parse_url($url);
        if (!isset($parts['path'])) return $url;

        $path_parts = pathinfo($parts['path']);
        $new_path = rtrim($path_parts['dirname'], '/') . '/' . $this->plugin_slug . '.zip';
        
        $parts['path'] = $new_path;
        return $this->build_url($parts);
    }

    private function build_url($parts) {
        $url = '';
        if (isset($parts['scheme'])) $url .= $parts['scheme'] . '://';
        if (isset($parts['host'])) $url .= $parts['host'];
        if (isset($parts['port'])) $url .= ':' . $parts['port'];
        $url .= $parts['path'];
        if (isset($parts['query'])) $url .= '?' . $parts['query'];
        if (isset($parts['fragment'])) $url .= '#' . $parts['fragment'];
        return $url;
    }

    public function fix_plugin_directory($source, $remote_source, $upgrader, $hook_extra) {
        global $wp_filesystem;

        $plugin_basename = $this->plugin_dir . '/' . $this->plugin_file;
        if (!isset($hook_extra['plugin']) || $hook_extra['plugin'] !== $plugin_basename) {
            return $source;
        }

        $temp_dir = basename($source);
        if ($temp_dir === $this->plugin_slug) {
            return $source;
        }

        $new_source = trailingslashit(dirname($source)) . $this->plugin_slug;
        
        if (!$wp_filesystem->move($source, $new_source)) {
            error_log("Falha ao renomear diretório de {$source} para {$new_source}");
            return new WP_Error('rename_failed', 'Falha ao ajustar estrutura do plugin');
        }

        return $new_source;
    }

    public function verify_installation($result, $hook_extra) {
        $plugin_basename = $this->plugin_dir . '/' . $this->plugin_file;
        
        if (!isset($hook_extra['plugin']) || $hook_extra['plugin'] !== $plugin_basename) {
            return $result;
        }

        if (!file_exists(WP_PLUGIN_DIR . '/' . $plugin_basename)) {
            error_log("Arquivo do plugin não encontrado após instalação: " . WP_PLUGIN_DIR . '/' . $plugin_basename);
            return new WP_Error('install_failed', 'Arquivo principal do plugin não encontrado');
        }

        return $result;
    }

    public function plugin_info($res, $action, $args) {
        if ('plugin_information' !== $action || $args->slug !== $this->plugin_slug) {
            return $res;
        }

        $remote = wp_remote_get($this->api_url);
        if (is_wp_error($remote)) return $res;

        $plugin_info = json_decode(wp_remote_retrieve_body($remote));
        if (!$plugin_info) return $res;

        $plugin_info->download_url = $this->sanitize_download_url($plugin_info->download_url);

        $res = new stdClass();
        $res->name = $plugin_info->name;
        $res->slug = $this->plugin_slug;
        $res->version = $plugin_info->version;
        $res->author = '<a href="' . esc_url($plugin_info->author_homepage) . '">' . $plugin_info->author . '</a>';
        $res->download_link = $plugin_info->download_url;
        $res->tested = $plugin_info->tested;
        $res->requires = $plugin_info->requires;
        $res->sections = (array) $plugin_info->sections;

        return $res;
    }

    public function check_for_update($transient) {
        if (empty($transient->checked)) return $transient;
    
        $remote = wp_remote_get($this->api_url);
        if (is_wp_error($remote)) {
            error_log('Erro ao buscar atualização: ' . $remote->get_error_message());
            return $transient;
        }
    
        $plugin_info = json_decode(wp_remote_retrieve_body($remote));
        if (!$plugin_info) return $transient;
    
        $plugin_basename = $this->plugin_dir . '/' . $this->plugin_file;
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_basename);
        $current_version = $plugin_data['Version'];
    
        // Verifica se a versão atual do plugin é menor que a versão remota
        if (version_compare($current_version, $plugin_info->version, '<')) {
            $transient->response[$plugin_basename] = (object) array(
                'slug' => $this->plugin_slug,
                'plugin' => $plugin_basename,
                'new_version' => $plugin_info->version,
                'package' => $plugin_info->download_url,
                'tested' => $plugin_info->tested,
                'requires' => $plugin_info->requires,
            );
        } 
        
        return $transient;
    }



    public function auto_reactivate_plugin_after_update($upgrader_object, $options) {
        // Verifica se a ação é de atualização e o tipo é plugin
        if ('update' === $options['action'] && 'plugin' === $options['type']) {
            // Verifica se a chave 'plugins' existe e é um array
            if (isset($options['plugins']) && is_array($options['plugins'])) {
                $plugin_basename = $this->plugin_dir . '/' . $this->plugin_file;
                
                // Verifica se o plugin atual está na lista de plugins atualizados
                if (in_array($plugin_basename, $options['plugins']) && !is_plugin_active($plugin_basename)) {
                    activate_plugin($plugin_basename);
                }
            }
        }
    }
}



