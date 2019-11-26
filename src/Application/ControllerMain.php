<?php
namespace Piaic\Application;

/**
 * Class ControllerMain
 * @package Piaic\Application
 */
class ControllerMain implements Controller
{
    /**
     * ControllerMain constructor.
     * @param Request $request
     * @param array $route
     * @throws PiaicException
     */
    public function __construct(Request $request, array $route)
    {
        $this->request = $request;
        $this->response = new Response();
        $this->route = $route;
        $this->inputData = [];
        $this->outputData = [];
        $this->getInputData();
    }

    /**
     * @param string $handlerName
     */
    public function invokeHandler(string $handlerName)
    {
        $this->$handlerName();
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * @throws PiaicException
     */
    public function setView()
    {
        $view = new View($this->request->appDirPath . 'package/' . $this->request->package, $this->request->language, $this->request->route, true);
        $content = $view->getView('view', $this->outputData, true);
        $this->response->setTextHtml($content, false);
    }

    /**
     * @throws PiaicException
     */
    public function setViewError()
    {
        $view = new View($this->request->appDirPath . 'package/' . $this->request->package, $this->request->language, $this->request->route, true);
        $content = $view->getView('error', $this->outputData, true);
        $this->response->setTextHtml($content, true);
    }

    /**
     * @throws PiaicException
     */
    public function setViewSuccess()
    {
        $view = new View($this->request->appDirPath . 'package/' . $this->request->package, $this->request->language, $this->request->route, true);
        $content = $view->getView('success', $this->outputData, true);
        $this->response->setTextHtml($content, false);
    }

    /**
     * @throws PiaicException
     */
    public function setViewInfo()
    {
        $view = new View($this->request->appDirPath . 'package/' . $this->request->package, $this->request->language, $this->request->route, true);
        $content = $view->getView('info', $this->outputData, true);
        $this->response->setTextHtml($content, false);
    }

    /**
     * @param bool $error
     * @throws PiaicException
     */
    public function setJson(bool $error = false)
    {
        $this->setOutputData();
        $content = json_encode($this->outputData, JSON_UNESCAPED_UNICODE);
        $this->response->setJson($content, $error);
    }

    /**
     * @throws PiaicException
     */
    private function getInputData()
    {
        $inputParameters = $this->route[$this->request->route][$this->request->method][$this->request->handler]['in'] ?? [];
        $inputValidators = [];
        $this->inputData = [];
        foreach ($inputParameters as $name => $inputClassName) {
            if ('' != $inputClassName && !isset($inputValidators[$inputClassName])) {
                $class = '\\Piaic\\Piaic\\Router\\Input\\' . $inputClassName;
                $inputValidators[$inputClassName] = new $class;
            }
            $value = $_POST[$name] ?? $_GET[$name] ?? null;
            if (null === $value) {
                throw new PiaicException('Input parameter missing: ' . $name);
            }
            $this->inputData[] = '' == $inputClassName ? $value : $inputValidators[$inputClassName]->validate($value);
        }
    }

    /**
     * @throws PiaicException
     */
    private function setOutputData()
    {
        $dropKeys = true;
        if (isset($this->route[$this->request->route][$this->request->method][$this->request->handler]['out_array'])) {
            $outputParameters = $this->route[$this->request->route][$this->request->method][$this->request->handler]['out_array'];
        } else {
            $dropKeys = false;
            $outputParameters = $this->route[$this->request->route][$this->request->method][$this->request->handler]['out_json'] ?? [];
        }
        if (count($outputParameters)) {
            $outputValidators = [];
            $tempList = [];
            foreach ($outputParameters as $name => $outputClassName) {
                if ('' != $outputClassName && !isset($inputValidators[$outputClassName])) {
                    $class = '\\Piaic\\Piaic\\Router\\Output\\' . $outputClassName;
                    $outputValidators[$outputClassName] = new $class;
                }
                if (!isset($this->outputData[$name])) {
                    throw new PiaicException('Output parameter missing: ' . $name);
                }
                if ($dropKeys) {
                    $tempList[] = '' == $outputClassName ? $this->outputData[$name] : $outputValidators[$outputClassName]->transform($this->outputData[$name]);
                } else {
                    if ('' != $outputClassName) {
                        $this->outputData[$name] = $outputValidators[$outputClassName]->transform($this->outputData[$name]);
                    }
                }
            }
            if ($dropKeys) {
                $this->outputData = $tempList;
            }
        }
    }

    protected $request;
    protected $response;
    protected $route;
    protected $inputData;
    protected $outputData;
}