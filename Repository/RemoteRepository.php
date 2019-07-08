<?php

namespace GeekCms\PackagesManager\Repository;

use GeekCms\PackagesManager\Repository\Template\MainRepositoryAbstract;

class RemoteRepository extends MainRepositoryAbstract
{
    protected $cacheCheckKey = 'get_url_cached_modules';

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
     * Prepare repositories list.
     *
     * @throws \Nwidart\Modules\Exceptions\ModuleNotFoundException
     *
     * @return array
     */
    protected function getRepositories()
    {
        $module = $this->findOrFail('PackagesManager');
        if (!empty($module)) {
            $authors = $this->getDevelopers($module);
            $this->modules[self::PACKAGE_OFFICIAL] = $this->getMainModules($authors, $module);
        }

        return $this->modules;
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
        return \Cache::remember($this->cacheCheckKey . '_' . $url, config('cache.settings.minutes', 10), function () use ($url) {
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
            } catch (\Exception $e) {
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
            $releases = $this->getGitData($url.'/releases');

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
                $authors[$uid] = preg_replace('/\\*name\\*/ims', $author, self::REPO_USER_LINK);
            }
        }

        return $authors;
    }

    /**
     * Get module info by module file
     *
     * @param null $url
     * @return array|mixed
     */
    public function getModuleInfo($url = null)
    {
        $info = [];

        $model = $this->getGitData($url.'/contents/module.json');
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
     * Get official modules and all forks.
     *
     * @param array $authors
     * @param null  $module
     *
     * @return array
     */
    protected function getMainModules($authors = [], $module = null)
    {
        $modules = [];
        if (!empty($authors) && !empty($module)) {
            $tag = $module->get('packages-tag', null);

            foreach ($authors as $author) {
                $result = $this->getGitData($author);
                if (!empty($result)) {
                    foreach ($result as $repo) {
                        if (isset($repo['description']) && preg_match('/\#'.$tag.'/', $repo['description'])) {
                            $release = $this->getLastRelease($repo['url'], $repo);
                            $model_info = $this->getModuleInfo($repo['url']);

                            $modules[] = [
                                'name' => $repo['name'],
                                'vendor' => $repo['owner']['login'],
                                'description' => $repo['description'],
                                'release' => $release,
                                'url' => $repo['html_url'],
                                'forks' => ($repo['forks']) ? $repo['forks_url'] : null,
                                'module_info' => $model_info,
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
