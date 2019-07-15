<?php

namespace GeekCms\PackagesManager\Repository;

use Cache;
use Exception;
use Gcms;
use GeekCms\PackagesManager\Exceptions\ModuleNotFoundException;

class RemoteRepository extends MainRepositoryAbstract
{
    /**
     * {@inheritdoc}
     */
    public function getOfficialPackages()
    {
        if (empty($this->modules[self::PACKAGE_OFFICIAL])) {
            $this->modules[self::PACKAGE_OFFICIAL] = $this->getRepositories()[self::PACKAGE_OFFICIAL];
        }

        return $this->modules[self::PACKAGE_OFFICIAL];
    }

    /**
     * Prepare repositories list.
     *
     * @return array
     * @throws ModuleNotFoundException
     *
     */
    protected function getRepositories()
    {
        $module = $this->findOrFail(module_package_name(static::class));
        if ($module !== null) {
            $authors = $this->getDevelopers($module);
            $this->modules[self::PACKAGE_OFFICIAL] = $this->getMainModules($authors, $module);
        }

        return $this->modules;
    }

    /**
     * Get official developers and groups.
     *
     * @param null $module
     *
     * @return array
     */
    protected function getDevelopers($module = null)
    {
        $authors = [];
        if (!empty($module)) {
            $authors = $module->get('packages-authors', null);
            foreach ($authors as $uid => $author) {
                $authors[$uid] = preg_replace('/\\*name\\*/im', $author, self::REPO_USER_LINK);
            }
        }

        return $authors;
    }

    /**
     * Get official modules and all forks.
     *
     * @param array $authors
     * @param null $module
     * @param bool $local
     * @return array
     * @throws ModuleNotFoundException
     */
    protected function getMainModules($authors = [], $module = null, $local = true)
    {
        $modules = [];
        if (!empty($authors) && !empty($module)) {
            $tag = $module->get('packages-tag', null);

            foreach ($authors as $author) {
                if ($local) {
                    return $this->getLocalData();
                }

                $result = $this->getGitData($author);
                if (!empty($result)) {
                    foreach ($result as $repo) {
                        if (isset($repo['description']) && preg_match('/\#' . $tag . '/', $repo['description'])) {
                            $release = $this->getLastRelease($repo['url'], $repo);
                            $model_info = $this->getModuleInfo($repo['url']);
                            $composer_info = $this->getComposerInfo($repo['url']);

                            $modules[] = [
                                'name' => $repo['name'],
                                'vendor' => $repo['owner']['login'],
                                'description' => preg_replace('/^[\S]+/mu', '', $repo['description']),
                                'release' => $release,
                                'url' => $repo['html_url'],
                                'forks' => $repo['forks'] ? $repo['forks_url'] : null,
                                'module_info' => $model_info,
                                'composer_info' => $composer_info,
                                'is_official' => true,
                                'installed' => false,
                                'enabled' => false
                            ];
                        }
                    }
                }
            }
        }

        return $modules;
    }

    /**
     * Get static data about packages
     *
     * @param string $url
     * @return array
     * @throws ModuleNotFoundException
     */
    protected function getLocalData($url = '')
    {
        $main_list = [];
        $main_info = pathinfo(__DIR__);
        $list = file_get_contents($main_info['dirname'] . DIRECTORY_SEPARATOR . config('modules.paths.repositories'));
        $list = json_decode($list, true);

        if ($list && count($list)) {
            $main_list = $list['official_packages'];
            foreach ($main_list as $module => $value) {
                $find_module = $this->findOrFail($module);
                $find_module = $find_module->getPath() . DIRECTORY_SEPARATOR;
                $find_composer = file_get_contents($find_module . config('modules.paths.main_module_composer'));
                $find_init = file_get_contents($find_module . config('modules.paths.main_module_bundle'));

                $find_composer = json_decode($find_composer, true);
                $find_init = json_decode($find_init, true);

                $main_list[$module]['composer_info'] = ($find_composer && count($find_composer)) ? $find_composer : [];
                $main_list[$module]['module_info'] = ($find_init && count($find_init)) ? $find_init : [];
            }
        }

        return $main_list;
    }

    /**
     * Send curl request to git.
     *
     * @param string $url
     *
     * @return mixed
     */
    protected function getGitData($url = '')
    {
        return Cache::remember(self::CACHED_MODULES_LIST_KEY . '_' . $url, config(Gcms::MAIN_CACHE_TIMEOUT_KEY, 10), static function () use ($url) {
            try {
                $headers = [
                    'Host: api.github.com',
                    'User-Agent: curl/7.52.1',
                    'Accept: */*',
                ];

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                $result = curl_exec($ch);
                curl_close($ch);

                return json_decode($result, true);
            } catch (Exception $e) {
                return [];
            }
        });
    }

    /**
     * Get last module version with info by repo project url.
     *
     * @param null $url
     * @param array $repo_data
     * @return array
     */
    protected function getLastRelease($url = null, $repo_data = [])
    {
        $last_release_date = 0;
        $last_release = [];

        if (!empty($url)) {
            $releases = $this->getGitData($url . self::REPO_MODULE_LINK_RELEASES);

            if (!empty($releases) && !isset($releases['message'])) {
                foreach ($releases as $release) {
                    $release_date = strtotime($release['published_at']);
                    if ($release_date > $last_release_date) {
                        $last_release_date = $release_date;
                        $last_release = [
                            'name' => $release['name'],
                            'version' => $release['tag_name'],
                            'download' => $release['zipball_url'],
                            'date' => $release_date,
                            'url' => $release['html_url'],
                        ];
                    }
                }
            } else {
                $last_release = [
                    'name' => $repo_data['default_branch'],
                    'version' => 0,
                    'download' => $repo_data['url'] . DIRECTORY_SEPARATOR . 'zipball',
                    'date' => strtotime($repo_data['updated_at']),
                    'url' => $repo_data['html_url'],
                ];
            }
        }

        return $last_release;
    }

    /**
     * Get module info by module file
     *
     * @param null $url
     * @return array|mixed
     */
    public function getModuleInfo($url = null)
    {
        return $this->getRepoFileContent($url . self::REPO_MODULE_LINK_MODULE_CONTENT);
    }

    /**
     * Get file json content and decode from remote repo
     *
     * @param $url
     * @return array|mixed
     */
    protected function getRepoFileContent($url)
    {
        $info = [];
        $model = $this->getGitData($url);
        if (isset($model['content'])) {
            $content = base64_decode($model['content']);
            if ($content) {
                $content = json_decode($content, true);
                if ($content && count($content)) {
                    $info = $content;
                }
            }
        }

        return $info;
    }

    /**
     * Get module composer data
     *
     * @param null $url
     * @return array|mixed
     */
    public function getComposerInfo($url = null)
    {
        return $this->getRepoFileContent($url . self::REPO_MODULE_LINK_COMPOSER_CONTENT);
    }

    /**
     * {@inheritdoc}
     */
    public function getUnofficialPackages()
    {
        if (empty($this->modules[self::PACKAGE_UNOFFICIAL])) {
            $modules = $this->getRepositories()[self::PACKAGE_OFFICIAL];
            $this->modules[self::PACKAGE_UNOFFICIAL] = $this->getForksModules($modules);
        }

        return $this->modules[self::PACKAGE_UNOFFICIAL];
    }

    /**
     * Get unofficial packages list.
     *
     * @param array $modules
     *
     * @return array
     */
    protected function getForksModules($modules = [])
    {
        $forks = [];
        if (!empty($modules)) {
            foreach ($modules as $module) {
                if (!empty($module['forks'])) {
                    $forks[] = $module['forks'];
                }
            }
        }

        return $forks;
    }
}
