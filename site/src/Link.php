<?php


class Link {
    const LINK_ENTITY_NAME = 'link';

    public function byId($id) {
        $link = R::load(self::LINK_ENTITY_NAME, $id);

        if (!$link->id) {
            return null;
        }

        $export = $link->export();
        $export['tags'] = R::tag($link);

        return $export;
    }

    public function byTags($tagList) {
        $links = R::tagged(self::LINK_ENTITY_NAME, $tagList);

        $result = R::exportAll($links);

        foreach ($result as &$link) {
            $link['safe'] = preg_replace('/[^a-z\d]/', '-', strtolower($link['title']));
            $link['tags'] = $link['sharedTag'];
            unset($link['sharedTag']);
        }

        return $result;
    }

    public function recent($quantity) {
        $links = R::find(
            self::LINK_ENTITY_NAME,
            ' 1 ORDER BY date DESC LIMIT :limit',
            array(':limit' => $quantity)
        );

        $result = R::exportAll($links);

        foreach ($result as &$link) {
            $link['safe'] = preg_replace('/[^a-z\d]/', '-', strtolower($link['title']));
        }

        return $result;
    }

    public function create($fields, $tags) {
        $link = R::dispense(self::LINK_ENTITY_NAME);

        foreach ($fields as $key => $value) {
            $link->{$key} = $value;
        }

        R::tag($link, $tags);

        return !!R::store($link);
    }

    public function tags() {
        $tags = R::find('tag');
        $titles = array();

        $result = R::exportAll($tags);

        foreach ($result as &$tag) {
            $tag['quantity'] = count($tag['sharedLink']);
            unset($tag['sharedLink']);
            $titles[] = $tag['title'];
        }

        array_multisort($titles, $result);

        return $result;
    }
}
