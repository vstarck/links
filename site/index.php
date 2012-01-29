<?php


require_once 'vendor/asadoo-0.2.php';
require_once 'vendor/Mustache.php';
require_once 'vendor/rb.php';
require_once 'src/AsadooResponseMustacheAdapter.php';
require_once 'src/Link.php';

AsadooResponse::mix(new AsadooResponseMustacheAdapter());

R::setup('mysql:host=localhost;dbname=%DB%','%USER%','%PASS%');

asadoo()->dependences()->register('link', function() {
    return new Link();
});

// IE filter
asadoo()
        ->on(function($request, $response, $dependences) {
            return $request->agent('/(?i)msie [1-9]/');
        })
        ->handle(function($request, $response, $dependences) {
            $response->showBrowserNotSupported();
            $response->end();
        });

asadoo()
        ->handle(function($request, $response, $dependences) {
            $response->assign('recent', $dependences->link->recent(10));
        });

// Home
asadoo()
        ->on('/')
        ->on('/home/')
        ->handle(function($request, $response, $dependences) {
            $response->render('views/home.html', array(
                'page_title' => 'Aijoona - Links',
                'recent' => $dependences->link->recent(10),
                'tags' => $dependences->link->tags()
            ));
            $response->end();
        });

// Link detail
asadoo()
        ->on('/:id/:title')
        ->handle(function($request, $response, $dependences) {
            if(!is_numeric($request->value('id'))) {
                return;
            }

            $link = $dependences->link->byId(
                $request->value('id')
            );

            if($link) {
                $response->render('views/link.html', array(
                    'page_title' => 'Aijoona - Link - ' . $request->value('title'),
                    'link' => $link
                ));
                $response->end();
            }
        });

asadoo()
        ->get('/tag/*', function($request, $response, $dependences) {
            $tags = array();
            $index = 1;

            while($request->segment($index)) {
                $tags[] = $request->segment($index++);
            }

            $links = $dependences->link->byTags($tags);

            $response->render('views/list.html', array(
                'page_title' => 'Aijoona - Links',
                'links' => $links
            ));

            $response->end();
        });

asadoo()
        ->get('/add', function($request, $response, $dependences) {
            $response->render('views/add.html');
            $response->end();
        });

asadoo()
        ->post('/add', function($request, $response, $dependences) {
            $tags = $request->post('tags', '');

            // TODO move to Link
            $result = $dependences->link->create(
                array(
                    'url' => $request->post('url'),
                    'title' => $request->post('title'),
                    'safe' => preg_replace('/[^a-z\d]/', '-', strtolower($request->post('title'))),
                    'description' => $request->post('description'),
                    'date' => date('d-m-Y H:i:s')
                ),
                explode(',', $tags)
            );

            $response->end($result ? 'Ok' : 'Fail');
        });

// Everything else
asadoo()
        ->handle(function($request, $response, $dependences) {
            $response->show404();
        });

asadoo()->start();