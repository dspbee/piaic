<?php
namespace Piaic\Controller;

use Piaic\Exception\PiaicException;
use Piaic\Request\Request;
use Piaic\Response\Response;
use Piaic\Response\View;

/**
 * Class MainController
 * @package Piaic\Controller
 */
class MainController implements Controller
{
    const VIEW = '__view__';
    const VIEW_ERROR = '__error__';
    const VIEW_SUCCESS = '__success__';
    const VIEW_INFO = '__info__';

    protected $request;
    protected $response;
    protected $viewData;

    /**
     * MainController constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->response = new Response();
        $this->viewData['request'] = $this->request;
    }

    /**
     * @param string $handlerName
     */
    public function invokeHandler(string $handlerName)
    {
        $this->$handlerName();
    }

    final public function sendResponse()
    {
        if ($this->response->isNotFound()) {
            $this->response->set404($this->request->packageDirPath(), $this->request->language());
        }
        $this->response->echo();
    }

    /**
     * @param array $data
     */
    protected function addData(array $data)
    {
        $this->viewData = array_replace($this->viewData, $data);
    }

    /**
     * @param string $content
     * @param bool $error
     */
    protected function sendContent(string $content, bool $error = false)
    {
        $this->response->setContent($content, $error);
    }

    /**
     * @param array $content
     * @param bool $error
     */
    protected function sendJSON(array $content, bool $error = false)
    {
        $content = json_encode($content, JSON_UNESCAPED_UNICODE);
        $this->response->setJson($content, $error);
    }

    /**
     * @param string $name
     * @param bool $error
     * @throws PiaicException
     */
    protected function sendView($name = self::VIEW, $error = false)
    {
        if (self::VIEW_ERROR == $name) {
            $error = true;
        }
        $view = new View($this->request->appDirPath() . 'package/' . $this->request->package(), $this->request->language(), $this->request->route(), !LC_DEV);
        $content = $view->getView('view', $this->viewData, true);
        $this->response->setContent($content, $error);
    }
}