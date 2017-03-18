<?php

namespace Laraish\WpSupport\Model;

use WP_User;
use Illuminate\Support\Collection;

class Author extends BaseModel
{
    /**
     * @type integer
     */
    protected $id;

    /**
     * @type \WP_User
     */
    protected $wpUser;

    /**
     * Author constructor.
     *
     * @param null|integer $id
     */
    public function __construct($id = null)
    {
        global $post;
        $queriedObject = get_queried_object();

        if ( ! is_null($id)) {
            if ($id instanceof WP_User) {
                $id     = $id->ID;
                $wpUser = $id;
            } else {
                $id     = (int)$id;
                $wpUser = new WP_User($id);
            }
        } else {
            if ($queriedObject instanceof WP_User) {
                $id     = $queriedObject->ID;
                $wpUser = $queriedObject;
            } else {
                $id     = $post->post_author;
                $wpUser = new WP_User($id);
            }
        }
        
        $this->id     = $id;
        $this->wpUser = $wpUser;
    }

    public function id()
    {
        return $this->setAttribute(__METHOD__, $this->id);
    }

    public function wpUser()
    {
        return $this->setAttribute(__METHOD__, $this->wpUser);
    }

    public function url()
    {
        $url = $this->wpUser->get('user_url');

        return $this->setAttribute(__METHOD__, $url);
    }

    public function postsUrl()
    {
        $postsUrl = get_author_posts_url($this->id);

        return $this->setAttribute(__METHOD__, $postsUrl);
    }

    public function displayName()
    {
        $displayName = $this->wpUser->get('display_name');

        return $this->setAttribute(__METHOD__, $displayName);
    }

    public function nickname()
    {
        $nickname = $this->wpUser->get('nickname');

        return $this->setAttribute(__METHOD__, $nickname);
    }

    public function firstName()
    {
        $firstName = $this->wpUser->get('first_name');

        return $this->setAttribute(__METHOD__, $firstName);
    }

    public function lastName()
    {
        $lastName = $this->wpUser->get('last_name');

        return $this->setAttribute(__METHOD__, $lastName);
    }

    public function description()
    {
        $description = $this->wpUser->get('description');

        return $this->setAttribute(__METHOD__, $description);
    }

    public function email()
    {
        $email = $this->wpUser->get('user_email');

        return $this->setAttribute(__METHOD__, $email);
    }

    public function avatarUrl($options = null)
    {
        $avatarUrl = get_avatar_url($this->id, $options);

        return $this->setAttribute(__METHOD__, $avatarUrl);
    }

    /**
     * Get all authors.
     *
     * @param array $query
     *
     * @return array|Collection
     */
    public static function all(array $query = [])
    {
        $limit = -1;

        return static::query(array_merge($query, ['number' => $limit]));
    }

    /**
     * Find author by using the given query parameter.
     *
     * @param array $query
     *
     * @return array|Collection
     */
    public static function query(array $query)
    {
        $users           = [];
        $query['fields'] = 'ID';

        foreach (get_users($query) as $user_id) {
            $users[] = new static($user_id);
        }

        return count($users) ? new Collection($users) : [];
    }
}