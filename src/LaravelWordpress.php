<?php
declare(strict_types=1);

namespace Vrkansagara\LaravelWordpress;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

/**
 * @copyright  Copyright (c) 2015-2019 Vallabh Kansagara <vrkansagara@gmail.com>
 * @license    https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
class LaravelWordpress
{


    /**
     * Get application default connection.
     * @var
     */
    private $databaseDefaultConnection;


    /**
     * Set database connection for this module.
     * @var
     */
    protected $connection;


    /**
     * The Laravel application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * Normalized Laravel Version
     *
     * @var string
     */
    protected $version;

    /**
     * True when enabled, false disabled an null for still unknown
     *
     * @var bool
     */
    protected $enabled;


    /**
     * @var null
     */
    protected $config;

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return \Illuminate\Foundation\Application
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    public function setApp($app)
    {
        $this->app = $app;
    }


    /**
     * @param Application $app
     */
    public function __construct($app = null)
    {
        if (!$app) {
            $app = app();   //Fallback when $app is not given
        }
        $this->setApp($app);
        $this->setConfig();
        $this->setEnabled();
        $this->setVersion($app->version());
        $this->setConnection();

    }

    /**
     * @return null
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param null $config
     */
    public function setConfig()
    {
        $applicationConfig = $this->app['config'];
        $this->config = $applicationConfig->get('laravel-wordpress');
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @return bool
     */
    public function setEnabled()
    {
        if ($this->enabled === null) {
            $config = $this->config;
            $configEnabled = value($config['enabled']);
            $this->enabled = ($configEnabled && !$this->app->runningInConsole()) ? $configEnabled : false;
        }
        return $this->enabled;
    }


    /**
     * @return mixed
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param mixed $connection
     */
    public function setConnection($connection = null): void
    {
        if (!$this->isEnabled()){
            throw new \Exception('Library Vrkansagara\\LravelWordpress is not enable.');
        }
        $config = $this->getConfig();
        try {
            $this->setDatabaseDefaultConnection(Config::get('database'));
            Config::set('database', $config['database']);
            $connectionName = env('DB_WOREDPRESS_CONNECTION');
            if (
                (isset($config['database']['default']) &&
                    !empty($config['database']['default'])) &&
                array_key_exists($connectionName, $config['database']['connections'])
            ) {
                $connection = DB::connection($connectionName);
            } else {
                throw new \Exception('Default database connection is not set for laravel-wordpress file.');
            }

        } catch (\Exception $e) {
            die("Could not connect to the database.  Please check your configuration. error:" . $e);
        }

        $this->connection = $connection;
    }


    /**
     * @return mixed
     */
    public function getDatabaseDefaultConnection()
    {
        return $this->databaseDefaultConnection;
    }

    /**
     * @param mixed $databaseDefaultConnection
     */
    public function setDatabaseDefaultConnection($databaseDefaultConnection): void
    {
        $this->databaseDefaultConnection = $databaseDefaultConnection;
    }

    /**
     * @param array $status
     * @return \Illuminate\Database\Query\Builder
     */
    public function getPosts(array $status = ['publish'])
    {


        $prefix = 'wp_';
        $wpTerms = $prefix . 'terms';
        $wpTermTaxonomy = $prefix . 'term_taxonomy';
        $wpTermRelationships = $prefix . 'term_relationships';
        $wpPosts = $prefix . 'posts';
        $wpUsers = $prefix . 'users';
        $wpUsermeta = $prefix . 'usermeta';

        return DB::table($wpTerms)
            ->leftJoin($wpTermTaxonomy, "$wpTermTaxonomy.term_id", "=", "$wpTerms.term_id")
            ->leftJoin($wpTermRelationships, "$wpTermRelationships.term_taxonomy_id", "=", "$wpTermTaxonomy.term_taxonomy_id")
            ->leftJoin($wpPosts, "$wpPosts.ID", "=", "$wpTermRelationships.object_id")
            ->leftJoin($wpUsers, "$wpUsers.id", "=", "$wpPosts.post_author")
            ->leftJoin($wpUsermeta, "$wpUsermeta.user_id", "=", "$wpPosts.id")
            ->select(DB::raw("
            $wpUsers.display_name as name ,
            $wpUsermeta.meta_value as description,
            $wpPosts.post_title as title,
            $wpPosts.post_content as content")
            )
            ->whereNotNull("$wpPosts.ID")
            ->whereNotNull("$wpUsermeta.user_id")
            ->whereIn("$wpPosts.post_status", $status)
            ->where("$wpUsermeta.meta_key", "=", 'description')
            ->groupBy("$wpPosts.ID");
    }


    public function getPostByTags($postId = null, array $status = ['publish'])
    {


        $prefix = 'wp_';
        $wpTerms = $prefix . 'terms';
        $wpTermTaxonomy = $prefix . 'term_taxonomy';
        $wpTermRelationships = $prefix . 'term_relationships';
        $wpPosts = $prefix . 'posts';
        $wpUsers = $prefix . 'users';
        $wpUsermeta = $prefix . 'usermeta';

        return DB::table($wpTerms)
            ->leftJoin($wpTermTaxonomy, "$wpTermTaxonomy.term_id", "=", "$wpTerms.term_id")
            ->leftJoin($wpTermRelationships, "$wpTermRelationships.term_taxonomy_id", "=", "$wpTermTaxonomy.term_taxonomy_id")
            ->leftJoin($wpPosts, "$wpPosts.ID", "=", "$wpTermRelationships.object_id")
            ->leftJoin($wpUsers, "$wpUsers.id", "=", "$wpPosts.post_author")
            ->leftJoin($wpUsermeta, "$wpUsermeta.user_id", "=", "$wpPosts.id")
            ->select(DB::raw("
            $wpUsers.display_name as name ,
            $wpUsermeta.meta_value as description,
            $wpPosts.post_title as title,
            $wpPosts.post_content as content"),
                DB::raw("GROUP_CONCAT($wpTerms.name) as tags")
            )
            ->whereNotNull("$wpPosts.ID")
            ->whereNotNull("$wpUsermeta.user_id")
            ->whereIn("$wpPosts.post_status", $status)
            ->where("$wpUsermeta.meta_key", "=", 'description')
            ->where("$wpPosts.ID", "=", $postId)
            ->where("$wpTermTaxonomy.taxonomy", "=", "post_tag")
            ->limit(1)
            ->groupBy("$wpPosts.ID");
    }


    public function getPostByCategories($postId = null, array $status = ['publish'])
    {
        $prefix = 'wp_';
        $wpTerms = $prefix . 'terms';
        $wpTermTaxonomy = $prefix . 'term_taxonomy';
        $wpTermRelationships = $prefix . 'term_relationships';
        $wpPosts = $prefix . 'posts';
        $wpUsers = $prefix . 'users';
        $wpUsermeta = $prefix . 'usermeta';

        return DB::table($wpTerms)
            ->leftJoin($wpTermTaxonomy, "$wpTermTaxonomy.term_id", "=", "$wpTerms.term_id")
            ->leftJoin($wpTermRelationships, "$wpTermRelationships.term_taxonomy_id", "=", "$wpTermTaxonomy.term_taxonomy_id")
            ->leftJoin($wpPosts, "$wpPosts.ID", "=", "$wpTermRelationships.object_id")
            ->leftJoin($wpUsers, "$wpUsers.id", "=", "$wpPosts.post_author")
            ->leftJoin($wpUsermeta, "$wpUsermeta.user_id", "=", "$wpPosts.id")
            ->select(DB::raw("
            $wpUsers.display_name as name ,
            $wpUsermeta.meta_value as description,
            $wpPosts.post_title as title,
            $wpPosts.post_content as content"),
                DB::raw("GROUP_CONCAT($wpTerms.name) as categories")
            )
            ->whereNotNull("$wpPosts.ID")
            ->whereNotNull("$wpUsermeta.user_id")
            ->whereIn("$wpPosts.post_status", $status)
            ->where("$wpUsermeta.meta_key", "=", 'description')
            ->where("$wpPosts.ID", "=", $postId)
            ->where("$wpTermTaxonomy.taxonomy", "=", "category")
            ->limit(1)
            ->groupBy("$wpPosts.ID");

    }

    public function __destruct()
    {
        Config::set('database', $this->getDatabaseDefaultConnection());

    }

}