<?php
class AsadooResponseMustacheAdapter {
    private $variables = array();

    public function assign($asadooResponseInstance, $key, $value) {
        $this->variables[$key] = $value;
    }

    /**
     * @param AsadooResponse $asadooResponseInstance
     * @param string $path
     * @param array $vars
     */
    public function render($asadooResponseInstance, $path, $vars = array()) {
        $template =
                file_get_contents('views/head.html') .
                        file_get_contents('views/sidebar.html') .
                        file_get_contents($path) .
                        file_get_contents('views/footer.html');

        $mustache = new Mustache();

        $vars['base'] = trim(AsadooCore::getInstance()->getBaseURL());

        if(!$vars['base']) {
            $vars['base'] = '/';
        }

        $vars = $this->variables + $vars;

        $asadooResponseInstance->write(
            $mustache->render($template, $vars)
        );
    }

    public function show404($asadooResponseInstance) {
        $asadooResponseInstance->code(404);

        $this->render(
            $asadooResponseInstance,
            'views/404.html',
            array(
                'title' => 'Contenido no encontrado'
            )
        );
    }

    public function showBrowserNotSupported($asadooResponseInstance) {
        $this->render(
            $asadooResponseInstance,
            'views/not_supported.html',
            array(
                'title' => 'Navegador no soportado'
            )
        );
    }
}